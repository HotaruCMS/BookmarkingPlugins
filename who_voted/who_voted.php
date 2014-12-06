<?php
/**
 * name: Who Voted
 * description: Show a list of who voted
 * version: 0.5
 * folder: who_voted
 * class: WhoVoted
 * hooks: install_plugin, theme_index_top, header_include, show_post_middle, admin_plugin_settings, admin_sidebar_plugin_settings
 * author: Nick Ramsay
 * authorurl: http://hotarucms.org/member.php?1-Nick
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
 * @author    Hotaru CMS Team
 * @copyright Copyright (c) 2009 - 2013, Hotaru CMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      http://www.hotarucms.org/
 */

class WhoVoted
{
    /**
     * Install settings
     */
    public function install_plugin($h)
    {
        $who_voted_settings = $h->getSerializedSettings();
        
        if (!isset($who_voted_settings['who_voted_num'])) { $who_voted_settings['who_voted_num'] = 0; }
        if (!isset($who_voted_settings['who_voted_avatars'])) { $who_voted_settings['who_voted_avatars'] = ''; }
        if (!isset($who_voted_settings['who_voted_avatar_size'])) { $who_voted_settings['who_voted_avatar_size'] = '16'; }
        if (!isset($who_voted_settings['who_voted_avatar_shape'])) { $who_voted_settings['who_voted_avatar_shape'] = 'square'; }
        if (!isset($who_voted_settings['who_voted_names'])) { $who_voted_settings['who_voted_names'] = 'checked'; }
        if (!isset($who_voted_settings['who_voted_widget_title'])) { $who_voted_settings['who_voted_widget_title'] = 'checked'; }
        
        $h->updateSetting('who_voted_settings', serialize($who_voted_settings));
    }
    
    public function theme_index_top($h)
    {
        $h->vars['who_voted_settings'] = $h->getSerializedSettings();        
    }
    
    /**
     * Show who voted on a post page
     */
    public function show_post_middle($h)
    { 
        if ($h->isPage('submit3')) { return false; }
        
        $this->showWhoVoted($h);
    }

    
    /**
     * Show who voted
     */
    public function showWhoVoted($h)
    {
        $who_voted_settings = $h->vars['who_voted_settings'];
        $limit = $who_voted_settings['who_voted_num'];

        $results = $this->getWhoVoted($h, $limit);
       
        if ($results) 
        {
            $h->vars['who_voted_results'] = $results;
            $h->template('who_voted_list');
        }
        else 
        {
            $h->template('who_voted_none');
        }
        
        return true;
    }
    
    /**
     * Get related results from the database
     *
     * return array|false
     */
    public function getWhoVoted($h, $limit)
    {
        if ($limit) { $limit_text = " LIMIT " . $limit; } else { $limit_text = ''; }
        
        $sql = "SELECT " . TABLE_USERS . ".user_id, " . TABLE_USERS . ".user_username, " . TABLE_POSTVOTES . ".vote_user_id FROM " . TABLE_USERS . ", " . TABLE_POSTVOTES . " WHERE (" . TABLE_USERS . ".user_id = " . TABLE_POSTVOTES . ".vote_user_id) AND (" . TABLE_POSTVOTES . ".vote_rating > %d) AND (" . TABLE_POSTVOTES . ".vote_post_id = %d) ORDER BY " . TABLE_POSTVOTES . ".vote_date ASC" . $limit_text;
        $results = $h->db->get_results($h->db->prepare($sql, 0, $h->post->id));
        
        return $results;
    }

}
?>