<?php
/**
 * name: Follow
 * description: Basic Follower/Following plugin
 * version: 0.6.1
 * folder: follow
 * class: Follow
 * type: Follow
 * hooks: install_plugin,admin_plugin_settings,admin_sidebar_plugin_settings, profile_navigation, theme_index_top, breadcrumbs, theme_index_main, header_include, show_post_extra_fields, profile_action_buttons, profile_content
 * author: shibuya246
 * authorurl: http://shibuya246.com
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
 * @author    shibuya246
 * @copyright Copyright (c) 2010, shibuya246
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      http://www.hotarucms.org/
 */

class Follow
{
    public $settings = array(
        'follow_show_time_date' => 'checked',
        'follow_show_extra_fields' => ''
    );
        
    /**
     * Add follow settings fields to the db.
     */
    public function install_plugin($h)
    {
        // Default settings
        $follow_settings = $h->getSerializedSettings();

        foreach ($this->settings as $setting => $value) {
            if (!isset($follow_settings[$setting])) { $follow_settings[$setting] = $value; }
        }

        $h->updateSetting('follow_settings', serialize($follow_settings));
    }
    

    /**
     * Profile menu link to "follow"
     */
    public function profile_navigation($h)
    {	
        $follower_count = $h->countFollowers($h->displayUser->id);
        $following_count = $h->countFollowing($h->displayUser->id);    
	    
        if (isset($h->vars['user_profile_tabs']) && $h->vars['user_profile_tabs']) {
            echo "<li><a href='#followers' data-toggle='tab'>" . $h->lang('follow_list_followers') . "&nbsp;<span class='badge'>" . $follower_count. "</span></a></li>\n";         
            echo "<li><a href='#following' data-toggle='tab'>" . $h->lang('follow_list_following') . "&nbsp;<span class='badge'>" . $following_count . "</span></a></li>\n";	 
        } else {
            echo "<li><a href='" . $h->url(array('page'=>'followers', 'user'=>$h->displayUser->name)) . "' >" . $h->lang('follow_list_followers') . "&nbsp;<span class='label label-default pull-right'>" . $follower_count . "</span></a></li>\n";         
            echo "<li><a href='". $h->url(array('page'=>'following', 'user'=>$h->displayUser->name)) . "' >" . $h->lang('follow_list_following') . "&nbsp;<span class='label label-default pull-right'>" . $following_count . "</span></a></li>\n";	          
         }        	 
    }
    
    
    public function profile_content($h)
    {
        //if (isset($h->vars['user']->id) && ($h->currentUser->id != $h->vars['user']->id)) { return false; }			    	  

        // followers
        $h->vars['follow_type'] = 'followers';
        echo '<div class="tab-pane" id="followers">';
            $h->template('follow_followers', '' , false);        
        echo '</div>';                

        // following
        $h->vars['follow_type'] = 'following';
        echo '<div class="tab-pane" id="following">';        
            $h->template('follow_followers', '', false);
        echo '</div>';
    }
    
    
    public function profile_action_buttons($h)
    {
        if ($h->currentUser->loggedIn && $h->displayUser->name != $h->currentUser->name) {
	    // check if already following
	    $follow = $h->isFollowing($h->vars['user']->id);
            if ($follow == 0) {
		 echo "<li><a class='label label-success' href='" . $h->url(array('page'=>'follow', 'user'=>$h->displayUser->name)) . "'>" . $h->lang['follow_follow_user'] . "</a></li>\n";
	    } else {
		 echo "<li><a class='label label-danger' href='" . $h->url(array('page'=>'unfollow', 'user'=>$h->displayUser->name)) . "'>" . $h->lang['follow_unfollow_user'] . "</a></li>\n";
		}
	 }
    }


    /**
     * Determine page and get user details
     */
    public function theme_index_top($h)
    {
        $h->vars['follow_settings'] = $h->getSerializedSettings();
        
        // Leave quickly if list or post page
        if ($h->pageType == 'list' || $h->pageType == 'post') { return false; }
        
        $username = $h->cage->get->testUsername('user');
	
        if (!$username) { $username = $h->currentUser->name; }

	$follow_page = false;

	switch ($h->pageName)
	{ 
	    case 'followers':
		$follow_page = true;
		$h->pageTitle = $h->lang['follow_list_followers'] . "[delimiter]" . $username;
		break;
	    case 'following':
		$follow_page = true;
		$h->pageTitle = $h->lang['follow_list_followers'] . "[delimiter]" . $username;
		break;
	    case 'follow':
	    case 'unfollow':
		$follow_page = true;
		$h->pageTitle = $h->lang['follow_list_followers'] . "[delimiter]" . $username;
		break;
	}

	// set page types & create UserAuth and MessagingFuncs objects
        if ($follow_page) {
	    $h->pageType = 'user';  // this setting hides the posts filter bar
	    $h->subPage = 'user';	    	   

	    // create a user object and fill it with user info (user being viewed)
	    $h->displayUser->set($h, 0, $username);

	    switch ($h->pageName)
	    {
		case 'followers':		    
		    $query = $h->getFollowers($h->displayUser->id, 'query');
		    $h->vars['follow_count'] = $h->countFollowers($h->displayUser->id);
		    $h->vars['follow_list'] = $h->pagination($query, $h->vars['follow_count'], 20);
		    // how to also include the latest actvitiy for this person and a follow/unfollow button
		    break;
		case 'following':
		    $query = $h->getFollowing($h->displayUser->id, 'query');
		    $h->vars['follow_count'] = $h->countFollowing($h->displayUser->id);
		    $h->vars['follow_list'] = $h->pagination($query, $h->vars['follow_count'], 20);
		    break;
		case 'follow':
		    $result = $h->follow($h->displayUser->id);
		    $h->messages[$h->lang['follow_newfollow']] = 'green';
		    $query = $h->getFollowers($h->displayUser->id, 'query');
		    $h->vars['follow_count'] = $h->countFollowers($h->displayUser->id);
		    $h->vars['follow_list'] = $h->pagination($query, $h->vars['follow_count'], 20);
		    break;
		case 'unfollow':
		    $result = $h->unfollow($h->displayUser->id);
		    $h->messages[$h->lang['follow_unfollow']] = 'green';
		    $query = $h->getFollowers($h->displayUser->id, 'query');
		    $h->vars['follow_count'] = $h->countFollowers($h->displayUser->id);
		    $h->vars['follow_list'] = $h->pagination($query, $h->vars['follow_count'], 20);
		    break;
		}
	}
    }

    /**
     * Breadcrumbs for follow pages
     */
    public function breadcrumbs($h)
    {
        $user = $h->cage->get->testUsername('user');
        if (!$user) { $user = $h->currentUser->name; }

        switch ($h->pageName)
        {
            case 'followers':
                return "<a href='" . $h->url(array('user'=>$user)) . "'>" . $user . "</a> &raquo; " . $h->lang['follow_list_followers'];
                break;
            case 'following':
		return "<a href='" . $h->url(array('user'=>$user)) . "'>" . $user . "</a> &raquo; " . $h->lang['follow_list_following'];
		break;
	    case 'follow':
	    case 'unfollow':
                return "<a href='" . $h->url(array('user'=>$user)) . "'>" . $user . "</a> &raquo; " . $h->lang['follow_list_followers'];
                break;            
        }
    }

    /**
     * Display pages
     */
    public function theme_index_main($h)
    {
        // Leave quickly if list or post page
        if ($h->pageType == 'list' || $h->pageType == 'post') { return false; }
        
        if (isset($h->displayUser->id) && ($h->currentUser->id != $h->displayUser->id)) { return false; }

        if ($h->pageName != 'followers' && $h->pageName != 'following' && $h->pageName != 'follow' && $h->pageName != 'unfollow') { return false; }
            
        $username = $h->cage->get->testUsername('user');	
        if (!$username) { $username = $h->currentUser->name; }

        // create a user object and fill it with user info (user being viewed)
        $h->vars['user'] = $h->newUserAuth();
        $h->vars['user']->getUserBasic($h, 0, $username);

        // get type by url page or if not available then vars set by tab hook
        $page = $h->cage->get->testAlnumLines('page');
        if (!$page && isset($h->vars['follow_type'])) { 
            $page = $h->vars['follow_type'];
        }
        if ($page == "following") {
            // followers  
            $h->vars['follow_type'] = $h->lang["follow_list_following"];
            $query = $h->getFollowing($h->displayUser->id, 'query');
            $h->vars['follow_count'] = $h->countFollowing($h->displayUser->id);
            $h->vars['follow_list'] = $h->pagination($query, $h->vars['follow_count'], 20);
        } else {
            // following
            $h->vars['follow_type'] = $h->lang["follow_list_followers"];
            $query = $h->getFollowers($h->displayUser->id, 'query');
            $h->vars['follow_count'] = $h->countFollowers($h->displayUser->id);
            $h->vars['follow_list'] = $h->pagination($query, $h->vars['follow_count'], 20);
            // how to also include the latest actvitiy for this person and a follow/unfollow button
        }

        $h->vars['follow_settings'] = $h->getSerializedSettings();

        $h->displayTemplate('follow_followers');
        return true;
    }

    /**
     * Display link
     */
    public function show_post_extra_fields($h)
    {      
            // oddly this field is a string not a boolean
            $val = $h->pluginSettings['follow']['follow_show_extra_fields'];
            if ($val == 'false') { return false; }

            if ($h->currentUser->loggedIn && $h->post->author != $h->currentUser->id) {
                // check if already following
                $follow = $h->isFollowing($h->post->author);
                
                if ($h->version <= '1.6.6') {
                    $username = $h->getUserNameFromId($h->post->author);
                } else {
                    $username = $h->post->authorname;
                }
                
                if ($follow == 0) {	
                     echo "<li><a href='" . $h->url(array('page'=>'follow', 'user'=>$username)) . "'>" . $h->lang['follow_follow_user'] . "</a></li>\n";
                } else {
                     echo "<li><a href='" . $h->url(array('page'=>'unfollow', 'user'=>$username)) . "'>" . $h->lang['follow_unfollow_user'] . "</a></li>\n";
                }
             }
    }
}
