<?php
/**
 * name: Activity
 * description: Show recent activity
 * version: 1.6
 * folder: activity
 * class: Activity
 * requires: users 1.1, widgets 0.6
 * hooks: install_plugin, header_include, comment_post_add_comment, comment_update_comment, com_man_approve_all_comments, comment_delete_comment, post_add_post, post_update_post, post_change_status, post_delete_post, userbase_killspam, vote_positive_vote, vote_negative_vote, vote_flag_insert, admin_sidebar_plugin_settings, admin_plugin_settings, theme_index_top, theme_index_main, profile, breadcrumbs, follow_activity, api_call, comment_voting_funcs_positive, comment_voting_funcs_negative
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

class Activity
{
    protected $folder = 'activity';
    
    /**
     *  Add default settings for Sidebar Comments plugin on installation
     */
    public function install_plugin($h)
    {
        // Default settings
        $activity_settings = $h->getSerializedSettings();
        
        if ($h->isActive('avatar')) {
            if (!isset($activity_settings['widget_avatar'])) { $activity_settings['widget_avatar'] = "checked"; }
        } else {
            if (!isset($activity_settings['widget_avatar'])) { $activity_settings['widget_avatar'] = ""; }
        }
        if (!isset($activity_settings['widget_avatar_size'])) { $activity_settings['widget_avatar_size'] = 16; }
        if (!isset($activity_settings['widget_user'])) { $activity_settings['widget_user'] = ''; }
        if (!isset($activity_settings['widget_number'])) { $activity_settings['widget_number'] = 10; }
        if (!isset($activity_settings['number'])) { $activity_settings['number'] = 20; }
        if (!isset($activity_settings['rss_number'])) { $activity_settings['rss_number'] = 20; }
        if (!isset($activity_settings['time'])) { $activity_settings['time'] = "checked"; }
        if (!isset($activity_settings['refresh_button'])) { $activity_settings['refresh_button'] = ""; }
        
        $h->updateSetting('activity_settings', serialize($activity_settings));
        
        // widget
        $h->addWidget('activity', 'activity', '');  // plugin name, function name, optional arguments
    }
    
    
    /**
    * Get Activity Functions
    */
    public function getActivityFunctions()
    {
           // include activity functions class:
           require_once(PLUGINS . 'activity/libs/ActivityFunctions.php');
           return new ActivityFunctions();
    }
    
    
    /**
     * Add activity when new comment posted
     */
    public function comment_post_add_comment($h)
    {
        if ($h->comment->status != "approved") { $status = "hide"; } else { $status = "show"; }

        $args['userid'] = $h->comment->author;
        $args['status'] = $status;
        $args['key'] = 'comment';
        $args['value'] = $h->vars['last_insert_id'];
        $args['key2'] = 'post';
        $args['value2'] = $h->comment->postId;
        
        $h->insertActivity($args);
    }
    
    
    /**
     * Update show/hide status when a comment is edited
     */
    public function comment_update_comment($h)
    {
        if ($h->comment->status != "approved") { $status = "hide"; } else { $status = "show"; }
        
        $args['status'] = $status;
        $args['where']['key'] = 'comment';
        $args['where']['value'] = $h->comment->id;
        
        $h->updateActivity($args);
    }
    
    
    /**
     * Delete comment from activity table
     */
    public function comment_delete_comment($h)
    {
        $args['key'] = 'comment';
        $args['value'] = $h->comment->id;
        
        $h->removeActivity($args);
        
        $h->clearCache('html_cache', false);
    }
    
    
    /**
     * Make all comments "show" when mass-approved in comment manager
     */
    public function com_man_approve_all_comments($h)
    {
        $args['status'] = 'show';
        $args['where']['key'] = 'comment';
        $args['where']['status'] = 'hide';
        
        $h->updateActivity($args);
    }


    /**
     * Add activity when new post submitted
     */
    public function post_add_post($h)
    {
        if ($h->post->status != 'new' && $h->post->status != 'top') { $status = "hide"; } else { $status = "show"; }
        
        $args['userid'] = $h->post->author;
        $args['status'] = $status;
        $args['key'] = 'post';
        $args['value'] = $h->post->vars['last_insert_id'];
        
        $h->insertActivity($args);
    }
    
    
    /**
     * Update activity when post is updated
     */
    public function post_update_post($h)
    {
        if ($h->post->status != 'new' && $h->post->status != 'top') { $status = "hide"; } else { $status = "show"; }
        
        $args['status'] = $status;
        $args['where']['key'] = 'post';
        $args['where']['value'] = $h->post->id;
        
        $h->updateActivity($args);
    }
    
    
    /**
     * Update activity when post status is changed
     */
    public function post_change_status($h)
    {
        $this->post_update_post($h);
    }
    
    
    /**
     * Delete post from activity table
     */
    public function post_delete_post($h)
    {
        $h->removeActivity(array('key'=>'post', 'value'=>$h->post->id));
        $h->removeActivity(array('key'=>'comment', 'value2'=>$h->post->id));
        $h->removeActivity(array('key'=>'vote', 'value2'=>$h->post->id));

        $h->clearCache('html_cache', false);
    }
    
    
    /**
     * Delete activity of killspammed users
     */
    public function userbase_killspam($h, $vars = array())
    {
        $h->removeActivity(array('userid'=>$vars['target_user']));
        
        $h->clearCache('html_cache', false);
    }
    
    
    /**
     * Add activity when voting on a post
     */
    public function vote_positive_vote($h, $vars)
    {
        $user_id = $vars['user'];
        $post_id = $vars['post'];
        
        // if we're voting down something we previously voted up, we should remove the previous vote:
        
        $args['userid'] = $vars['user'];
        $args['key'] = 'vote';
        $args['value'] = 'down';
		$args['key2'] = 'post';
		$args['value2'] = $vars['post'];
            
        $result = $h->removeActivity($args);
        
        // if there wasn't a previous vote, i.e. nothing was found when we tried to delete it, then we can add it as an up vote:
        if (!$result) {

	        $args['userid'] = $vars['user'];
	        $args['key'] = 'vote';
	        $args['value'] = 'up';
	        $args['key2'] = 'post';
	        $args['value2'] = $vars['post'];
	        
	        $h->insertActivity($args);
	        
        } else {
            $h->clearCache('html_cache', false); // clear the html cache in order to update the activity widget after the deletion
        }
    }
    
    
    /**
     * Add activity when voting down or removing a vote from a post
     */
    public function vote_negative_vote($h, $vars)
    {
        // if we're un-voting or voting up something we previously voted down, we should remove the previous vote:
        
        $args['userid'] = $vars['user'];
        $args['key'] = 'vote';
        $args['value'] = 'up';
		$args['key2'] = 'post';
		$args['value2'] = $vars['post'];
            
        $result = $h->removeActivity($args);
        
        // if there wasn't a previous vote, i.e. nothing was found when we tried to delete it, then we can add it as a down vote:
        if (!$result) {
            
	        $args['userid'] = $vars['user'];
	        $args['key'] = 'vote';
	        $args['value'] = 'down';
	        $args['key2'] = 'post';
	        $args['value2'] = $vars['post'];
	        
	        $h->insertActivity($args);
            
        } else {
            $h->clearCache('html_cache', false); // clear the html cache in order to update the activity widget after the deletion
        }
    }
    
    
    /**
     * Add activity when flagging a post
     */
    public function vote_flag_insert($h)
    {
        // we don't need the status because if the post wasn't visible, it couldn't be voted for.

        $args['key'] = 'vote';
        $args['value'] = 'flag';
		$args['key2'] = 'post';
		$args['value2'] = $h->post->id;
            
        $h->insertActivity($args);
    }
    
    
    /**
     * Add activity when voting on a comment
     */
    public function comment_voting_funcs_positive($h, $vars)
    {
        // if we're voting down something we previously voted up, we should remove the previous vote:
        
        $args['userid'] = $vars['user'];
        $args['key'] = 'vote';
        $args['value'] = 'down';		
	$args['key2'] = 'comment';
	$args['value2'] = $vars['comment'];
            
        $result = $h->removeActivity($args);
         
        // if there wasn't a previous vote, i.e. nothing was found when we tried to delete it, then we can add it as an up vote:
        if (!$result) {
	        $args['value'] = 'up';
	        $h->insertActivity($args);
        } else { 
            $h->clearCache('html_cache', false); // clear the html cache in order to update the activity widget after the deletion
        }
    }
    
    
    /**
     * Add activity when voting down or removing a vote from a comment
     */
    public function comment_voting_funcs_negative($h, $vars)
    {
        // if we're un-voting or voting up something we previously voted down, we should remove the previous vote:
        
        $args['userid'] = $vars['user'];
        $args['key'] = 'vote';
        $args['value'] = 'up';		
        $args['key2'] = 'comment';
        $args['value2'] = $vars['comment'];
 
        $result = $h->removeActivity($args);
        
        // if there wasn't a previous vote, i.e. nothing was found when we tried to delete it, then we can add it as a down vote:
        if (!$result) {
	        $args['value'] = 'down';
                $h->insertActivity($args);
        } else {
            $h->clearCache('html_cache', false); // clear the html cache in order to update the activity widget after the deletion
        }
    }
    
    
    /**
     * Actitivy details of latest actitivty for follow plugin - profile display
     */
    public function follow_activity($h, $params = array())
    {    		                    			     			     
        $latestActivity = $h->getLatestActivity(1, $params[0]);
        
        $action = $latestActivity[0];	
                       
        return $action;			 
    }
    
    
    /**
     * Display the latest activity in a widget block
     */
    public function widget_activity($h)
    {       
        // Get settings from database if they exist...
        $activity_settings = $h->getSerializedSettings('activity');
                        
        // Get latest activity
        $activity = $h->getLatestActivity($activity_settings['widget_number']);
        
        $h->template('activity_widget');                 
    }
    
    
    /**
     * Get sidebar activity items
     *
     * @param array $activity 
     * @param array $activity_settings
     * return string $output
     */
    public function getWidgetActivityItems($h, $activity = array(), $cache = false)
    {
        $need_cache = false;
        $label = 'sb_act';
        
        if ($cache) {
            // check for a cached version and use it if no recent update:
            $output = $h->smartCache('html', 'useractivity', 10, '', $label);
            if ($output) {
                return $output;
            } else {
                $need_cache = true;
            }
        }
                
        if (!$activity) { return false; }
        
        $actFuncs = $this->getActivityFunctions();
        $output = $actFuncs->getActivityItems($h, $activity);
        
        if ($need_cache) {
            $h->smartCache('html', 'useractivity', 10, $output, $label); // make or rewrite the cache file
        }
        
        return $output;
    }
    
    
    /**
     * Redirect to Activity RSS
     *
     * @return bool
     */
    public function theme_index_top($h)
    {
        switch ($h->pageName)
        {
            case 'ajax_activity':
                
                $fromId = $h->cage->get->testInt('fromId'); 
                $csrf = $h->cage->get->testAlnum('csrf');
                $act_query = $h->getLatestActivity(0, 0, 'query', $fromId);
                $items = $h->db->get_results($act_query);
                              
                $h->vars['pagedResults'] = new stdClass();
                $h->vars['pagedResults']->items = $items;                
                $h->template('activity');
                die();
            case 'rss_activity':
                $this->rssFeed($h);
                return true;
        }
        
        return false;        
    }

    
    /**
     * Display All Activity page
     */
    public function theme_index_main($h)
    {
        switch ($h->pageName)
        {
            // Submit Step 1
            //case 'submit':
            case 'activity':
                $this->activityPage($h);
                return true;
                exit;
            
        }
        
        return false;
    }
    
    
    /**
     * Display All Activity page
     */
    public function activityPage($h)
    {   
        // Get settings from database if they exist...
        $activity_settings = $h->getSerializedSettings('activity');
        
        // gets query and total count for pagination
        $act_query = $h->getLatestActivity(0, 0, 'query');
        $act_count = $h->getLatestActivity(0, 0, 'count');
        
        // pagination 
        $h->vars['pagedResults'] = $h->pagination($act_query, $act_count, $activity_settings['number'], 'useractivity');
        
        if (isset($activity_settings['refresh_button']) && $activity_settings['refresh_button'])
            echo "<button class='btn btn-primary' type='button' id='activity_refresh'>Refresh</button>";
        
        echo "<div id='activity'><ul class='activity_items' id='activity_items_list'>";
        $h->template('activity');
        echo "</ul></div>";
        
        if ($h->vars['pagedResults']) { echo $h->pageBar($h->vars['pagedResults']); }


    }
    
    
    /**
     * Display activity on Profile page
     */
    public function profile($h)
    {        
        $user = $h->cage->get->testUsername('user');
        $userid = $h->getUserIdFromName($user);
        $h->vars['user_name'] = $user;
                
        // Get settings from database if they exist...
        $activity_settings = $h->getSerializedSettings('activity');

        // gets query and total count for pagination
        $act_query = $h->getLatestActivity(0, $userid, 'query');
        $act_count = $h->getLatestActivity(0, $userid, 'count');

        // pagination 
        $h->vars['pagedResults'] = $h->pagination($act_query, $act_count, $activity_settings['number'], 'useractivity');
        
        $h->template('activity_profile');                
    }
    
    
    /**
     * Add Activity RSS link to breadcrumbs
     */
    public function breadcrumbs($h)
    {
        if ($h->pageName != 'activity') { return false; }
        
        $crumbs = $h->pageTitle;
        $crumbs .= "<a href='" . $h->url(array('page'=>'rss_activity')) . "'>";
        $crumbs .= " <img src='" . BASEURL . "content/themes/" . THEME . "images/rss_10.png' alt='" . $h->pageTitle . " RSS' /></a>\n";
        
        return $crumbs;
    }
    
    
    /**
     * Return data for REST apiCall
     */
    public function api_call($h, $action)
    {        
        // Get settings from database if they exist...
        $activity_settings = $h->getSerializedSettings('activity');
        
        // check if its a POST, GET, UPDATE or DELETE

        // check for params
        $limit = $h->cage->get->KeyExists('limit') ? $h->cage->get->testInt('limit') : $activity_settings['number'];
        $userid = 0;
        
        switch ($action) {
            case 'getLatest':
                // call query
                $act_count = $h->getLatestActivity(0, $userid, 'count');
                $act_query = $h->getLatestActivity(0, $userid, 'query');
                $result = $h->pagination($act_query, $act_count, $limit, 'useractivity');
                break;

            default:
                return false;
                break;
        }
        
        return $result;
    }
    
        
    /**
     * Publish content as an RSS feed
     * Uses the 3rd party RSS Writer class.
     */    
    public function rssFeed($h)
    {
            $limit = $h->cage->get->getInt('limit');
            $user = $h->cage->get->testUsername('user');

            $userid = ($user) ? $h->getUserIdFromName($user) : 0;

            // Get settings from database if they exist...
            $activity_settings = $h->getSerializedSettings('activity');

            if (!$limit) { $limit = $activity_settings['rss_number']; }

            // get latest activity
            $activity = $h->getLatestActivity($limit, $userid);

            $items = array();

            if ($activity) {
                    $actFuncs = $this->getActivityFunctions();
                    foreach ($activity as $act) 
                    {
                            // Post used in Hotaru's url function
                            if ($act->useract_key == 'post') {
                                    $h->readPost($act->useract_value);
                            } elseif  ($act->useract_key2 == 'post') {
                                    $h->readPost($act->useract_value2);
                            }

                            if ($act->useract_userid == 0) {
                                $name = $h->lang['activity_anonymous'];
                            } else {
                                $name = $h->getUserNameFromId($act->useract_userid);
                            }
                            $post_title = stripslashes(html_entity_decode(urldecode($h->post->title), ENT_QUOTES,'UTF-8'));
                            $title_link = $h->url(array('page'=>$h->post->id));

                            $result = $actFuncs->activitySwitch($h, $act);

                            $item['title'] = $name . " " . $result['output'] . " \"" . $post_title . "\"";
                            $item['link'] = $h->url(array('page'=>$h->post->id)) . $result['cid'];
                            $item['date'] = $act->useract_date;
                            array_push($items, $item);
                    }
            }

            if ($user) { 
                    $description = $h->lang["activity_rss_latest_from_user"] . " " . $user; 
            } else {
                    $description = $h->lang["activity_rss_latest"] . SITE_NAME;
            }

            $h->rss(SITE_NAME, BASEURL, $description, $items);
            exit;
    }

}
?>
