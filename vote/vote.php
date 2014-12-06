<?php
/**
 * name: Vote
 * description: Adds voting ability to posted stories.
 * version: 2.5
 * folder: vote
 * class: Vote
 * type: vote
 * requires: submit 1.9, users 1.1
 * hooks: install_plugin, theme_index_top, post_read_post, header_include, pre_show_post, admin_plugin_settings, admin_sidebar_plugin_settings, post_add_post, submit_confirm_pre_trackback, post_delete_post, header_include_raw
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

class Vote
{
    /**
     * Add vote fields to the post table and make a dedicated Votes table.
     */
    public function install_plugin($h)
    {
        // Default settings
        $vote_settings = $h->getSerializedSettings();
        if (!isset($vote_settings['submit_vote'])) { $vote_settings['submit_vote'] = "checked"; }
        if (!isset($vote_settings['submit_vote_value'])) { $vote_settings['submit_vote_value'] = 1; }
        if (!isset($vote_settings['votes_to_promote'])) { $vote_settings['votes_to_promote'] = 5; }
        if (!isset($vote_settings['use_demote'])) { $vote_settings['use_demote'] = ""; }
        
        if (!isset($vote_settings['upcoming_duration'])) { $vote_settings['upcoming_duration'] = 5; }
        if (!isset($vote_settings['no_front_page'])) { $vote_settings['no_front_page'] = 5; }
        if (!isset($vote_settings['posts_widget'])) { $vote_settings['posts_widget'] = 'checked'; }
        if (!isset($vote_settings['vote_on_url_click'])) { $vote_settings['vote_on_url_click'] = ''; }
	if (!isset($vote_settings['vote_anon_vote'])) { $vote_settings['vote_anon_vote'] = ''; }
        
        $h->updateSetting('vote_settings', serialize($vote_settings));
    }  
    
    
    /**
     * Determine if we're using alerts or not
     */
    public function theme_index_top($h)
    {
        $h->vars['vote_settings'] = $h->getSerializedSettings();
        
    }
    
    
    /**
     * Read number of votes if post exists.
     */
    public function post_read_post($h)
    {
        if (!isset($h->post)) { return false; }
        
        // prior to 1.6.6 the votesUp were not set in readPost
        if ($h->version <= '1.6.6') {            
            $h->post->votesUp = $h->post->vars['post_row']->post_votes_up;
        } 
    }
    
    
    

    /**
     * includes for raw data
     */
    public function header_include_raw($h)
    {
        $vote_settings = $h->getSerializedSettings();
    }
    
    
     /**
     * ********************************************************************* 
     * *********************** FUNCTIONS FOR VOTING ************************ 
     * *********************************************************************
     * ****************************************************************** */
     
     
    /**
     * If auto-vote is enabled, the new post is automatically voted for by the person who submitted it.
     */
    public function post_add_post($h)
    {
         //get vote settings
        $vote_settings = $h->getSerializedSettings('vote'); 
        $submit_vote = $vote_settings['submit_vote'];
        $submit_vote_value = $vote_settings['submit_vote_value'];
        
        // Automatically vote for a post when it's submitted...
        if ($submit_vote == 'checked') {
            
            //update the vote count
            $sql = "UPDATE " . TABLE_POSTS . " SET post_votes_up=post_votes_up+%d WHERE post_id = %d";
            $h->db->query($h->db->prepare($sql, $submit_vote_value, $h->post->id));

            //Insert one vote for each of $submit_vote_value;
            for ($i=0; $i<$submit_vote_value; $i++) {
                $sql = "INSERT INTO " . TABLE_POSTVOTES . " (vote_post_id, vote_user_id, vote_user_ip, vote_date, vote_type, vote_rating, vote_updateby) VALUES (%d, %d, %s, CURRENT_TIMESTAMP, %s, %s, %d)";
                $h->db->query($h->db->prepare($sql, $h->post->id, $h->post->author, $h->cage->server->testIp('REMOTE_ADDR'), 'vote', 10, $h->post->author));
            }
        }            
                    
    }
    
    
    /**
     * Check if auto-vote on submission can push the story to the front page
     */
    public function submit_confirm_pre_trackback($h)
    {        
        $h->vars['vote_settings'] = $h->getSerializedSettings();
        // get current vote count and status
        $sql = "SELECT post_votes_up, post_status FROM " . TABLE_POSTS . " WHERE post_id = %d";
        $result = $h->db->get_row($h->db->prepare($sql, $h->post->id));
        
        // check if the automatically added votes are enough to immediately push the story to Top Stories
        // only do this if the status is "new"
        if ((($result->post_votes_up) >= $h->vars['vote_settings']['votes_to_promote']) 
            && $result->post_status == 'new') 
        { 
            $post_status = 'top'; 
            $h->vars['submit_redirect'] = BASEURL; // so we can redirect to the home page instead of Latest
        } else { 
            $post_status = $result->post_status;
        }
        
        //update the post status
        $sql = "UPDATE " . TABLE_POSTS . " SET post_status = %s WHERE post_id = %d";
        $h->db->query($h->db->prepare($sql, $post_status, $h->post->id));
    }
     
    
     /**
     * Displays the vote button.
     */
    public function pre_show_post($h)
    {
        $h->vars['vote_anon_vote'] = $h->vars['vote_settings']['vote_anon_vote'];

        // run check against whether user has voted or not
        $h->vars['voted'] = $h->post->userVoted;
        
        // CHECK TO SEE IF THE CURRENT USER HAS VOTED FOR THIS POST
        // Only doing this for single post. post list checked have been moved to bookmarking functions as they are tied closely to the query for better performance
        // Journals and other types of posts will have a problem so include them in the below as well
//        if (!isset($h->vars['currentUserVotedPosts'])) {
//            if ($h->currentUser->loggedIn) {
//               $sql = "SELECT vote_rating FROM " . TABLE_POSTVOTES . " WHERE vote_post_id = %d AND vote_user_id = %d AND vote_rating != %d LIMIT 1";
//               $h->vars['voted'] = $h->db->get_var($h->db->prepare($sql, $h->post->id, $h->currentUser->id, -999));                        
//            } elseif ($h->vars['vote_settings']['vote_anon_vote']) {	    
//               $user_ip = $h->cage->server->testIp('REMOTE_ADDR');
//               $user_id = 0; 
//               $sql = "SELECT vote_rating FROM " . TABLE_POSTVOTES . " WHERE vote_post_id = %d AND vote_user_id = %d AND vote_user_ip = %s AND vote_rating != %d LIMIT 1";
//               $h->vars['voted'] = $h->db->get_var($h->db->prepare($sql, $h->post->id, $user_id, $user_ip, -999));
//           }
//        }

        // determine where to return the user to after logging in:
        if (!$h->cage->get->keyExists('return')) {
            $host = $h->cage->server->sanitizeTags('HTTP_HOST');
            $uri = $h->cage->server->sanitizeTags('REQUEST_URI');
            $return = 'http://' . $host . $uri;
            $return = urlencode(htmlentities($return,ENT_QUOTES,'UTF-8'));
        } else {
            $return = $h->cage->get->testUri('return'); // use existing return parameter
        }
        
        $h->vars['vote_login_url'] = BASEURL . "index.php?page=login&amp;return=" . $return;
        $h->template('vote_button', 'vote', false);
    }
    
    
    


     
    
    
    /**
     * Delete votes when post deleted
     */
    public function post_delete_post($h)
    {
        $sql = "DELETE FROM " . TABLE_POSTVOTES . " WHERE vote_post_id = %d";
        $h->db->query($h->db->prepare($sql, $h->post->id));
    }
}

?>
