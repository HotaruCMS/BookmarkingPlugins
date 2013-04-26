<?php
/**
 * name: Akismet
 * description: Checks new users against the Akismet blacklist
 * version: 0.1
 * folder: akismet
 * class: Akismet
 * type: antispam
 * requires: users 1.1
 * hooks: install_plugin, user_register_check_blocked, user_register_check_blocked, users_register_pre_add_user, users_register_post_add_user, users_email_conf_post_role, user_manager_role, user_manager_details, user_manager_pre_submit_button, user_man_killspam_delete, admin_sidebar_plugin_settings, admin_plugin_settings
 * author: shibuya246
 * authorurl: http://hotarucms.org/member.php?shibuya246
 *
 * PHP version 5
 *
 * LICENSE: Hotaru CMS is free software: you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License as 
 * published by the Free Software Foundation, either version 3 of 
 * the License, or (at your option) any later version. 
 *
 * Hotaru CMS is distributed in the hope that it will be useful, but WITHOUT 
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
 * FITNESS FOR A PARTICULAR PURPOSE. 
 *
 * You should have received a copy of the GNU General Public License along 
 * with Hotaru CMS. If not, see http://www.gnu.org/licenses/.
 * 
 * @category  Content Management System
 * @package   HotaruCMS
 * @author    shibuya246 <admin@hotarucms.org>
 * @copyright Copyright (c) 2009, Hotaru CMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      http://www.hotarucms.org/
 */

class Akismet
{
    protected $ssType   =   'go_pending';   // otherwise 'block_reg'
    /**
     * Default settings on install
     */
    public function install_plugin($h)
    {
        // Default settings 
        if (!$h->getSetting('akismet_key')) { $h->updateSetting('akismet_key', ''); }
        if (!$h->getSetting('akismet_type')) { $h->updateSetting('akismet_type', 'go_pending'); }
    }
    
    
    /**
     * Included to cover old versions with old hook
     */
    public function user_signin_register_check_blocked($h)
    {
        $this->user_register_check_blocked($h);
    }
    
    /**
     * Checks user against the Akismet blacklist
     */
    public function user_register_check_blocked($h)
    {
        $this->ssType = $h->getSetting('akismet_type');
        
        $key = $h->getSetting('akismet_key', 'akismet');
        if (!$key) { return false; } // can't use this plugin without an API key from Akismet
        
        // get user info:
        $username = $h->currentUser->name;
        $email = $h->currentUser->email;
        $ip = $h->cage->server->testIp('REMOTE_ADDR');    
        
        // If any variable is empty or the IP is "localhost", skip using this plugin.
        if (!$username || !$email || !$ip || ($ip == '127.0.0.1')) { return false; }
        
        // Include our Akismet class:
        require_once(PLUGINS . 'akismet/libs/Akismet.php');
        $akismet = new AkismetFunctions($h, SITEURL ,$key);
        $akismet->setCommentAuthor($username);
        $akismet->setCommentAuthorEmail($email);
        //$akismet->setCommentAuthorURL($url);
        //$akismet->setCommentContent($comment);
        //$akismet->setPermalink('http://www.example.com/blog/alex/someurl/');               
        
        if($akismet->isCommentSpam())
        { 
            // store flags - used when type is "go_pending"
            $h->vars['reg_flags'] = 'akismet';
            
            // if type is "block_reg", provide a way to tell the Users plugin:
            if ($this->ssType == 'block_reg') {
                $h->vars['block'] = true;
            }
            // TODO 
            // Should we save a count of akismets acitivity ?
            //mail($to, "akismet spam", "spam found by akismet for email: " . $email . ", username: " . $username);
        } 
        else 
        { 
            // safe user, do nothing...            
        }

    }
    
    
    /**
     * Set a spammer's role to "pending"
     */
    public function users_register_pre_add_user($h)
    {
        if ($h->vars['reg_flags']) {
            $h->currentUser->role = 'pending';
        }
    }
    
    
    /**
     * Adds any spam details to the usermeta table
     *
     * @param array $vars - contains the last insert id
     */
    public function users_register_post_add_user($h, $vars)
    {
        $last_insert_id = $vars[0];
        
        if ($h->currentUser->vars['reg_flags']) {
            $sql = "INSERT INTO " . TABLE_USERMETA . " (usermeta_userid, usermeta_key, usermeta_value, usermeta_updateby) VALUES(%d, %s, %s, %d)";
            $h->db->query($h->db->prepare($sql, $last_insert_id, 'akismet_flags', serialize($h->currentUser->vars['reg_flags']), $last_insert_id));
        }
        
        /* Registration continues as normal, so the user may have to validate their email address. */
    }
    
    
    /**
     * This function is called after the email confirmation function assigns the user a new role.
     * We want to override the role, forcing the user to be "pending";
     */
    public function users_email_conf_post_role($h)
    {
        // Check to see if this user has any akismet_flags:
        $sql = "SELECT usermeta_value FROM " . TABLE_USERMETA . " WHERE usermeta_userid = %d AND usermeta_key = %s";
        $flags = $h->db->get_var($h->db->prepare($sql, $h->currentUser->id, 'akismet_flags'));
        
        if ($flags) {  $h->currentUser->role = 'pending'; }
    }
    
    
    /**
     * Adds an icon in User Manager about the user being flagged
     */
    public function user_manager_role($h)
    {
        list ($icons, $user_role, $user) = $h->vars['user_manager_role'];
        
        // TODO
        // Change direct SQL query to hotaru lib query
        //$flags = $h->models->usermeta->findFlags($user->user_id, 'akismet_flags');
        
        // Check to see if this user has any akismet_flags:
        $sql = "SELECT usermeta_value FROM " . TABLE_USERMETA . " WHERE usermeta_userid = %d AND usermeta_key = %s";
        $flags = $h->db->get_var($h->db->prepare($sql, $user->user_id, 'akismet_flags'));
        $h->vars['akismet_flags'] = $flags;
        
        if ($flags) {
            $flags = unserialize($flags);
            $title = $h->lang['akismet_flagged_reasons'];
            foreach ($flags as $flag) {
                $title .= $flag . ", ";
            }
            $title = rstrtrim($title, ", ");
            $icons .= " <img src = '" . BASEURL . "content/plugins/user_manager/images/flag_red.png' title='" . $title . "'>";
            $h->vars['user_manager_role'] = array($icons, $user_role, $user);
        }
    }
    
    
    /**
     * Adds a note in User Manager about the user being flagged
     */
    public function user_manager_details($h)
    {
        list ($output, $user) = $h->vars['user_manager_details'];
        
        // Check to see if this user has any akismet_flags:
        $sql = "SELECT usermeta_value FROM " . TABLE_USERMETA . " WHERE usermeta_userid = %d AND usermeta_key = %s";
        
        if (!isset($h->vars['akismet_flags'])) {
            $flags = $h->db->get_var($h->db->prepare($sql, $user->user_id, 'akismet_flags'));
        } else {
            $flags = $h->vars['akismet_flags']; // retrieve from memory
        }
        
        if ($flags) {
            $flags = unserialize($flags);  
            $output .= "<br /><b>" . $h->lang['akismet_flagged_reasons'] . "</b><span style='color: red;'>";
            foreach ($flags as $flag) {
                $output .= $flag . ", ";
            }
            $output = rstrtrim($output, ", ");
            $output .= "</span>";
            $h->vars['user_manager_details'] = array($output);
        }
    }
    
    
    /**
     * Option to add deleted or killspammed users to the Akismet database
     */
    public function user_manager_pre_submit_button($h)
    {
        echo "&nbsp;&nbsp;&nbsp;&nbsp; <input type='checkbox' name='Akismet'> ";
        echo $h->lang['akismet_add_database'] . "<br />";
    }
    
    /**
     * Add deleted or killspammed user to the Akismet database
     */
    public function user_man_killspam_delete($h, $vars)
    {
        if (!$h->cage->get->keyExists('Akismet')) { return false; }
        
        $key = $h->getSetting('akismet_key', 'akismet'); // used for reporting spammers
        
        if (!$key) { return false; } // can't use this plugin without an API key from Akismet
        
        $user = $vars[0];
        
        // Include our Akismet class:
        require_once(PLUGINS . 'akismet/libs/Akismet.php');
        $akismet = new AkismetFunctions($h, SITEURL , $key);
        $akismet->setCommentAuthor($user->name);
        $akismet->setCommentAuthorEmail($user->email);
        $akismet->submitSpam();
       
    }

}

?>