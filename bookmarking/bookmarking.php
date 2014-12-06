<?php
/**
 * name: Bookmarking
 * description: Social Bookmarking base - provides "list" and "post" templates. 
 * version: 0.8
 * folder: bookmarking
 * class: Bookmarking
 * type: base
 * hooks: install_plugin, theme_index_top, header_meta, header_include, navigation, breadcrumbs, theme_index_main, admin_plugin_settings, admin_sidebar_plugin_settings, user_settings_pre_save, user_settings_fill_form, user_settings_extra_settings, pre_show_post, show_post_extra_fields, show_post_title, theme_index_pre_main, profile_navigation, api_call, post_rss_feed_items
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

class Bookmarking
{
        /**
        * Install Submit settings if they don't already exist
        */
        public function install_plugin($h)
        {
                // Default settings 
                $bookmarking_settings = $h->getSerializedSettings();
                if (!isset($bookmarking_settings['posts_per_page'])) { $bookmarking_settings['posts_per_page'] = 10; }
                if (!isset($bookmarking_settings['rss_redirect'])) { $bookmarking_settings['rss_redirect'] = ''; }				
                if (!isset($bookmarking_settings['default_type'])) { $bookmarking_settings['default_type'] = 'news'; }
                if (!isset($bookmarking_settings['default_page'])) { $bookmarking_settings['default_page'] = 'popular'; }
                if (!isset($bookmarking_settings['archive'])) { $bookmarking_settings['archive'] = "no_archive"; }
                if (!isset($bookmarking_settings['sort_bar_dropdown'])) { $bookmarking_settings['sort_bar_dropdown'] = 'checked'; }
                if (!isset($bookmarking_settings['use_alerts'])) { $bookmarking_settings['use_alerts'] = "checked"; }
                if (!isset($bookmarking_settings['alerts_to_bury'])) { $bookmarking_settings['alerts_to_bury'] = 5; }
                if (!isset($bookmarking_settings['physical_delete'])) { $bookmarking_settings['physical_delete'] = ""; }

                $h->updateSetting('bookmarking_settings', serialize($bookmarking_settings));

                // Add "open in new tab" option to the default user settings
                $base_settings = $h->getDefaultSettings('base'); // originals from plugins
                $site_settings = $h->getDefaultSettings('site'); // site defaults updated by admin
                if (!isset($base_settings['new_tab'])) { 
                        $base_settings['new_tab'] = ""; $site_settings['new_tab'] = "";
                        $h->updateDefaultSettings($base_settings, 'base'); 
                        $h->updateDefaultSettings($site_settings, 'site');
                }
                if (!isset($base_settings['link_action'])) { 
                        $base_settings['link_action'] = ""; $site_settings['link_action'] = "";
                        $h->updateDefaultSettings($base_settings, 'base'); 
                        $h->updateDefaultSettings($site_settings, 'site');
                }
        }
    
	/**
	 * theme_index_top
	 */
	public function theme_index_top($h)
	{
            $h->vars['bookmarking_settings'] = $h->getSerializedSettings();
            $h->vars['useAlerts'] = isset($h->vars['bookmarking_settings']['use_alerts']) ? $h->vars['bookmarking_settings']['use_alerts'] : false;
            
            switch ($h->pageName) {
                case 'ajax_bookmarking':                    
                    $fromId = $h->cage->get->testInt('fromId'); 
                    $csrf = $h->cage->get->testAlnum('csrf');
                    
                    //$act_query = $h->getLatestActivity(0, 0, 'query', $fromId);
                    //echo json_encode($act_query);
                    
                    $sql = "SELECT post_votes_up FROM " . TABLE_POSTS . " WHERE post_id = %d";
                    $items = $h->db->get_results($h->db->prepare($sql, 5121)); 
            
                    //$items = $h->db->get_results($act_query);                
                    echo json_encode($items[0]);
                    die();
                default:
                    
                    $this->determinePage($h);
                    // run all other theme_index_top functions except this one
                    $h->pluginHook('theme_index_top', '', array(), array('bookmarking'));
                    $this->finalizePage($h);
                    $this->getUserSettings($h);
                    return "skip";
            }
            
            return false;   // should not reach here
	}
	

	/**
	 * Determine the page
	 */
	private function determinePage($h)
	{
            // check if we're using the sort/filter links
            if ($h->cage->get->keyExists('sort')) {
                    $h->pageName = 'sort';
            }

            // check if we should forward an RSS link to its source
            $this->rssForwarding($h);

            //check if we should set the home page to settings default page
            $this->setHomeDefaultPage($h);

            // check page name and set types and titles
            $this->checkPageName($h);
	}
	
	
	/**
	 * We should now know the pageName for certain, so finish setting up the page
	 */
	private function finalizePage($h)
	{
		// no need to continue for other types of homepage
		$valid_lists = array('popular', 'upcoming', 'latest', 'all');
		if (($h->pageName == $h->home) && (!in_array($h->home, $valid_lists))) { return false; }

		// stop here if not a list or the pageType has been set elsewhere:
		if (!empty($h->pageType) && ($h->pageType != 'list') && ($h->pageType != 'post')) {
			return false; 
		}
		
		// get the BookmarkingFunctions class
		$funcs = $this->getBookmarkingFunctions($h);		

		$posts_per_page = $h->vars['bookmarking_settings']['posts_per_page'];
		
		// if a list, get the posts:
		switch ($h->pageType) {
                    case 'list':
                            $post_count = $funcs->prepareList($h, '', 'count');   // get the number of posts
                            $post_query = $funcs->prepareList($h, '', 'query');   // and the SQL query used				

                            // this query created from bookmarking query
                            // if it was a more generic query we could have in main libs or vote plugin libs, but it is specific to bookmarking query
                            $h->vars['pagedResults'] = $h->pagination($post_query, $post_count, $posts_per_page, 'posts');
                            $funcs->makePostList($h, $h->vars['pagedResults']->items);
                            
                            // this is instead of the join
                            $h->vars['currentUserVotedPosts'] = $funcs->getVotedPostsByThisUser($h);
                            break;
                    case 'post':
                            // if a post is already set (e.g. from the categories plugin), we don't want to
                            // do the default stuff below. We do, however, need the "target", "editorial" stuff after it, though...
                            break;
                    default:
                            // Probably a post, let's check:
                            if (is_numeric($h->pageName)) {
                                // Page name is a number so it must be a post with non-friendly urls
                                // this is instead of the join
                                $h->postList = array($h->pageName);
                                $h->vars['currentUserVotedPosts'] = $funcs->getVotedPostsByThisUser($h);
                                if (!$h->readPost($h->pageName)) {
                                    $h->pageTitle = $h->lang['main_theme_page_not_found'];
                                    return false; 
                                }
                                $h->pageTitle = $h->post->title;
                                $h->pageType = 'post';
                            } elseif ($post_id = $h->isPostUrl($h->pageName)) {
                                // Page name belongs to a story
                                // this is instead of the join
                                $h->postList = array($post_id);
                                $h->vars['currentUserVotedPosts'] = $funcs->getVotedPostsByThisUser($h);
                                $h->readPost($post_id);    // read current post
                                $h->pageTitle = $h->post->title;
                                $h->pageType = 'post';
                            }
                            break;
		}
	}
        
        
        /**
         * user defined settings:
         * 
         * @param type $h
         */
        private function getUserSettings($h)
        {
		// logged out users get the default settings:
		if (!$h->currentUser->settings) {
                    $h->currentUser->settings = $h->getDefaultSettings('site'); 
                }
 
		// open links in a new tab?
		$h->vars['target'] = $h->currentUser->settings['new_tab'] ? 'target="_blank"' : ''; 
		
		// open link to the source or the site post?
                $h->vars['link_action'] = $h->currentUser->settings['link_action'] ? 'source' : ''; 
		
		// editorial (story with an internal link)
		$h->vars['editorial'] = isset($h->post) && strstr($h->post->post_orig_url, BASEURL) ? true: false;
		
		// get settings from Submit Plugin
		if (!isset($h->vars['submit_settings'])) {
			$h->vars['submit_settings'] = $h->getSerializedSettings('submit');
		}
        }
	

	/**
	 * Check if we should forward an RSS link to its source
	 */
	public function rssForwarding($h)
	{
		$h->pluginHook('pre_rss_forward');
		 
		// check if this is an RSS link forwarding to the source
		if (!$h->cage->get->keyExists('forward')) { return false; }
		
		$post_id = $h->cage->get->testInt('forward');
		if ($post_id) { 
                    $post = $h->getPost($post_id);
                }
		
                if (isset($post->post_orig_url)) {
			header("Location:" . urldecode($post->post_orig_url));
			exit;
		}
	}
	
	
	/**
	 * Check if we should set the home page to the settings default page
	 */
	public function setHomeDefaultPage($h)
	{
		$h->pluginHook('pre_set_home');

		// Allow Bookmarking to set the homepage to settings page default unless already set.
		if (!$h->home) {		   
			$h->setHome($h->vars['bookmarking_settings']['default_page'], $h->vars['bookmarking_settings']['default_page']); // and set name to settings page default, too, if not already set.
		}
	}

	/**
	 * Get Bookmarking Functions
	 */
	private function getBookmarkingFunctions($h)
	{
		// include bookmarking_functions class:
		require_once(PLUGINS . 'bookmarking/libs/BookmarkingFunctions.php');
		return new BookmarkingFunctions();
	}
	

	/**
	 * Check page name and set types and titles
	 */
	public function checkPageName($h)
	{
		switch ($h->pageName)
		{
			case 'popular':
				$h->pageType = 'list';
				$h->pageTitle =  ($h->home == 'popular') ? $h->lang["bookmarking_site_name"] : $h->lang["bookmarking_top"];
				break;
			case 'latest':
				$h->pageType = 'list';
				$h->pageTitle = $h->lang["bookmarking_latest"];
				break;
			case 'upcoming':
				$h->pageType = 'list';
				$h->pageTitle = $h->lang["bookmarking_upcoming"];
				break;
			case 'all':
				$h->pageType = 'list';
				$h->pageTitle = $h->lang["bookmarking_all"];
				break;
			case 'sort':
				$sort = $h->cage->get->testPage('sort');
				if ($sort) {
					$h->pageType = 'list';
					$sort_lang = 'bookmarking_' . str_replace('-', '_', $sort);
					$h->pageTitle = $h->lang[$sort_lang];
				}
				break;
			default:
				// no default or we'd mess up anything set by other plugins
		}
		
		// case for paginated pages, but *no pagename*
		if ((!$h->pageName || $h->pageName == 'popular') && $h->cage->get->keyExists('pg')) {
			if (!$h->home) { $h->setHome('popular'); } // query vars previously prevented getPageName returning a name
			$h->pageName = 'popular';
			$h->pageType = 'list';
			$h->pageTitle = $h->lang["bookmarking_top"]; 
		}
	}
	
	
	/**
	 * Match meta tag to a post's description (keywords is done in the Tags plugin)
	 */
	public function header_meta($h)
	{
		if ($h->pageType != 'post') { return false; }
                
		$meta_content = sanitize($h->post->content, 'all');
		$meta_content = truncate($meta_content, 200);
		echo '<meta name="description" content="' . $meta_content . '" />' . "\n";
		
                return true;
	}
	
	
        /**
         * Add "Latest" to the navigation bar
         */
        public function navigation($h)
        {
//            echo '<li class="posts" data-name="posts">';
//                echo '<a href="' . $h->url(array('page' => 'all')) . '">Explore</a>';
//            echo '</li>';
                     
            if ($h->home != 'popular') {
                    // highlight "Top Stories" as active tab
                    if ($h->pageName == 'popular') { $status = "id='navigation_active' class='active'"; } else { $status = ""; }

                    // display the link in the navigation bar
                    echo "<li " . $status . "><a href='" . $h->url(array('page'=>'popular')) . "'>" . $h->lang["bookmarking_top"] . "</a></li>";
            }

            // highlight "Latest" as active tab
            if ($h->pageName == 'latest') { $status = "id='navigation_active' class='active'"; } else { $status = ""; }

            // display the link in the navigation bar
            echo "<li " . $status . "><a href='" . $h->url(array('page'=>'latest')) . "'>" . $h->lang["bookmarking_latest"] . "</a></li>";
        }


        /**
         * Replace the default breadcrumbs in specific circumstances
         */
        public function breadcrumbs($h)
        {
                if ($h->subPage) { return false; } // don't use these breadcrumbs if on a subpage 

                if ($h->pageType == 'post') {
                    return $h->pageTitle . ' ' . $h->rssBreadcrumbsLink('top');
                }

                if ($h->pageName == 'popular') { 
                        $h->pageTitle = $h->lang["bookmarking_top"];
                }

                switch ($h->pageName) {
                        case 'popular':
                                return $h->pageTitle . ' ' . $h->rssBreadcrumbsLink('top');
                                break;
                        case 'latest':
                                return $h->pageTitle . ' ' . $h->rssBreadcrumbsLink('new');
                                break;
                        case 'upcoming':
                                return $h->pageTitle . ' ' . $h->rssBreadcrumbsLink('upcoming');
                                break;
                        case 'all':
                                return $h->pageTitle . ' ' . $h->rssBreadcrumbsLink();
                                break;
                }
        }


    /**
     * Determine which template to show and do preparation of variables, etc.
     */
    public function theme_index_main($h)
    {
            // stop here if not a list or a post
            if (($h->pageType != 'list') && ($h->pageType != 'post')) { return false; }

            // flag status btns only shown on page once, keep them hidden then display with js when clicked
            $h->template('bookmarking_flags', 'bookmarking');

            // necessary settings:
            $h->vars['use_content'] = $h->vars['submit_settings']['content'];
            $h->vars['use_summary'] = $h->vars['submit_settings']['summary'];
            $h->vars['summary_length'] = $h->vars['submit_settings']['summary_length'];

            switch ($h->pageType)
            {
                case 'post':
                    // This post is visible if it's not buried/pending OR if the viewer has edit post permissions...

                    // defaults:
                    $buried = false; $pending = false; $can_edit = false;

                    // check if buried:
                    if ($h->post->status == 'buried') {
                        $buried = true;
                        $h->messages[$h->lang["bookmarking_post_buried"]] = "red";
                    } 

                    // check if pending:
                    if ($h->post->status == 'pending') { 
                        $pending = true;
                        $h->messages[$h->lang["bookmarking_post_pending"]] = "red";
                    }

                    // check if global edit permissions
                    if ($h->currentUser->getPermission('can_edit_posts') == 'yes') { $can_edit = true; }

                    $h->showMessages();

                    // display post or show error message
                    if (!$buried && !$pending){
                        $h->template('bookmarking_post');
                    } elseif ($can_edit) {
                        $h->template('bookmarking_post');
                    } else {
                        // don't show the post
                    }

                    return true;
                    break;

                case 'list':
                    if (isset($h->vars['pagedResults']->items)) {
                        $h->template('bookmarking_list');
                        echo $h->pageBar($h->vars['pagedResults']);
                    } else {
                        $h->template('bookmarking_no_posts');
                    }
                    return true;
            }
    }  
    
    
    // maybe move to finalize page above
    public function pre_show_post($h)
    {        
        if ($h->pageType == 'list') { return false; } // making sure list page keeps running fast
        
        if (($h->pageType == 'post') && ($h->vars['useAlerts'] == "checked")) {
            
             // CHECK TO SEE IF THIS POST IS BEING FLAGGED AND IF SO, ADD IT TO THE DATABASE
            $h->vars['flagged'] = false;
            
            if ($h->cage->get->keyExists('alert') && $h->currentUser->loggedIn) {
                // Check if already flagged by this user
                // TODO is this the right query ? 
                $sql = "SELECT vote_rating FROM " . TABLE_POSTVOTES . " WHERE vote_post_id = %d AND vote_user_id = %d AND vote_rating = %d LIMIT 1";
                $flagged = $h->db->get_var($h->db->prepare($sql, $h->post->id, $h->currentUser->id, -999));
                
                if (!$flagged) {
                    $sql = "INSERT INTO " . TABLE_POSTVOTES . " (vote_post_id, vote_user_id, vote_user_ip, vote_date, vote_type, vote_rating, vote_reason, vote_updateby) VALUES (%d, %d, %s, CURRENT_TIMESTAMP, %s, %d, %d, %d)";
                    $h->db->query($h->db->prepare($sql, $h->post->id, $h->currentUser->id, $h->cage->server->testIp('REMOTE_ADDR'), 'vote', -999, $h->cage->get->testInt('alert'), $h->currentUser->id));
                    $h->pluginHook('bookmarking_flag_insert');
                }
                else
                {
                    $h->messages[$h->lang("bookmarking_alert_already_flagged")] = "red";
                    $h->showMessages();
                }
            }            
           
            // CHECK TO SEE IF THIS POST HAS BEEN FLAGGED AND IF SO, SHOW THE ALERT STATUS
            $h->vars['reasons'] = $h->postGetFlags($h->post->id);

            if ($h->vars['reasons']) {
                $h->vars['flag_count'] = count($h->vars['reasons']);
                
                // Buries or Deletes a post if this new flag sends it over the limit set in Settings
                if ($h->cage->get->keyExists('alert') && $h->vars['flag_count'] >= $h->vars['bookmarking_settings']['alerts_to_bury'])
                {
                    $h->readPost($h->post->id); //make sure we've got all post details
                    
                    if ($h->vars['bookmarking_settings']['physical_delete']) { 
                        $h->deletePost(); // Akismet uses those details to report the post as spam
                    } else {
                        $h->changePostStatus('buried');
                        $h->clearCache('html_cache', false);
                        $h->pluginHook('bookmarking_post_status_buried'); // Akismet hooks in here to report the post as spam
                    }
                    
                    $h->messages[$h->lang("bookmarking_alert_post_buried")] = "red";
                }
                
                $h->vars['flagged'] = true;
            }
        }
            
    }
    
    
    /**
     * List of alert reasons to choose from.
     */
//    public function show_post_extras($h)
//    {
//        
//    }
    
    
    /**
     * Add an "alert" link below the story
     */
    public function show_post_extra_fields($h)
    {
        // Only show the Alert link ("Flag it") on new posts, not top stories
        if ($h->currentUser->loggedIn && $h->post->status == "new" && ($h->vars['useAlerts'] == "checked")) {            
            // flag link            
            $h->template('bookmarking_alert_link', 'bookmarking', false);           
        } else {
            //print "not new";
        }
    }
    
    
     /**
     * Displays the flags next to the post title.
     */
    public function show_post_title($h)
    {
        if (!isset($h->vars['flagged']) || !$h->vars['flagged']) { return false; }
        
        $why_list = "";
        foreach ($h->vars['reasons'] as $why) {
            $alert_lang = "bookmarking_alert_reason_" . $why;            
                $why_list .= $h->lang($alert_lang). ", ";            
        }
        $why_list = rstrtrim($why_list, ", ");    // removes trailing comma

        // $h->vars['flag_count'] got from above function
        $h->vars['flag_why'] = $why_list;
        $h->template('bookmarking_alert', 'bookmarking', false);
    }
    
    
    /**
     * User Settings - before saving
     */
    public function user_settings_pre_save($h)
    {
        // Open posts in a new tab?
        $h->vars['settings']['new_tab'] = $h->cage->post->getAlpha('new_tab') == 'yes' ? 'checked' : ''; 
        
        // List links open source url or post page?
        $h->vars['settings']['link_action'] = $h->cage->post->getAlpha('link_action') == 'source' ? 'checked' : ''; 
    }
    
    
    /**
     * User Settings - fill the form
     */
    public function user_settings_fill_form($h)
    {
        if (!isset($h->vars['settings']) || !$h->vars['settings']) { return false; }
        
        if ($h->vars['settings']['new_tab']) { 
            $h->vars['new_tab_yes'] = "checked"; 
            $h->vars['new_tab_no'] = ""; 
        } else { 
            $h->vars['new_tab_yes'] = ""; 
            $h->vars['new_tab_no'] = "checked"; 
        }
        
        if ($h->vars['settings']['link_action']) { 
            $h->vars['link_action_source'] = "checked"; 
            $h->vars['link_action_post'] = ""; 
        } else { 
            $h->vars['link_action_source'] = ""; 
            $h->vars['link_action_post'] = "checked"; 
        }
    }
    
    
    /**
     * User Settings - html for form
     */
    public function user_settings_extra_settings($h)
    {
        if (!isset($h->vars['settings']) || !$h->vars['settings']) { return false; }
        
        
        // OPEN POSTS IN A NEW TAB?
        echo "<div class='form-group'>";
            echo '<label for="inputEmail3" class="col-sm-6">';
                echo $h->lang['bookmarking_users_settings_open_new_tab'];
            echo '</label>'; 
            echo '<div class="col-sm-3">';
                echo "<input type='radio' name='new_tab' value='yes' " . $h->vars['new_tab_yes'] . "> " . $h->lang['users_settings_yes'] . " &nbsp;&nbsp;\n";
            echo '</div>';
            echo '<div class="col-sm-3">';
                echo "<input type='radio' name='new_tab' value='no' " . $h->vars['new_tab_no'] . "> " . $h->lang['users_settings_no'] . "\n";
            echo '</div>';
        echo '</div>';
        
        
        // OPEN POSTS IN A NEW TAB?
        echo "<div class='form-group'>";
            echo '<label for="inputEmail3" class="col-sm-6">';
                echo $h->lang['bookmarking_users_settings_link_action'];
            echo '</label>'; 
            echo '<div class="col-sm-3">';
                echo "<input type='radio' name='link_action' value='source' " . $h->vars['link_action_source'] . "> " . $h->lang['bookmarking_users_settings_source'] . " &nbsp;&nbsp;\n";
            echo '</div>';
            echo '<div class="col-sm-3">';
                echo "<input type='radio' name='link_action' value='post' " . $h->vars['link_action_post'] . "> " . $h->lang['bookmarking_users_settings_post'] . "\n";
            echo '</div>';
        echo '</div>';
    }
    
    
    /** 
     * Add sorting options
     */
    public function submit_post_breadcrumbs($h)
    {
        if ($h->isPage('submit2')) { return false; } // don't show sorting on Submit Confirm
        
        // exit if this isn't a page of type list, user or profile
        $page_type = $h->pageType;
        if ($page_type != 'list' && $page_type != 'user' && $page_type != 'profile') { return false; }
        
        // go set up the links
        $this->setUpSortLinks($h);        
    }
    
    
    /**
     * Profile navigation link
     */
    public function profile_navigation($h)
    {
        echo "<li><a href='" . $h->url(array('page'=>'all', 'user'=>$h->displayUser->name)) . "'>" . $h->lang["users_all_posts"] . "&nbsp;<span class='label label-waring pull-right'>" . number_format($h->postsApproved($h->displayUser->id),0) . "</span></a></li>\n";
    }

    
    /** 
     * Prepare sort links
     */
    public function theme_index_pre_main($h)
    {
        if (substr($h->pageName, 0, 6) == 'submit' || substr($h->pageName, 0, 4) == 'edit' || $h->pageName == 'login' || $h->pageName == 'register') { return false; }
        
        $pagename = $h->pageName;
        
        // check if we're looking at a category
        if ($h->subPage == 'category') { 
            $h->vars['bookmarking']['filterText'] = $h->vars['category_id'];
            $h->vars['bookmarking']['filter'] = 'category';
        } 
        
        // check if we're looking at a tag
        if ($h->subPage == 'tags') { 
            $h->vars['bookmarking']['filterText'] = $h->vars['tag'];
            $h->vars['bookmarking']['filter'] = 'tag';
        } 
        
        // check if we're looking at a media type
        if ($h->cage->get->keyExists('media')) { 
            $h->vars['bookmarking']['filterText'] = $h->cage->get->testAlnumLines('media');
            $h->vars['bookmarking']['filter'] = 'media';
        } 
        
        // check if we're looking at a user
        if ($h->cage->get->keyExists('user')) { 
            $h->vars['bookmarking']['filterText'] = $h->cage->get->testUsername('user');
            $h->vars['bookmarking']['filter'] = 'user';
        } 
        
        // check if we're looking at a sorted page
        if ($h->cage->get->keyExists('sort')) { 
            $h->vars['bookmarking']['filterText'] = $h->cage->get->testAlnumLines('sort');
            $h->vars['bookmarking']['filter'] = 'sort';
        } 
        
        // POPULAR LINK
        if (isset($h->vars['bookmarking']['filter']) || isset($h->vars['bookmarking']['filterText'])) {
            $url = $h->url(array('page'=>'popular', $h->vars['bookmarking']['filter']=>$h->vars['bookmarking']['filterText']));        
         } else { $url = $h->url(array('page'=>'popular',)); } 
        $h->vars['popular_link'] = $url;
         
        // POPULAR ACTIVE OR INACTIVE
        if (($pagename == 'popular') && (!isset($sort)) && $h->pageType != 'profile') { 
            $h->vars['popular_active'] = "active";
        } else { $h->vars['popular_active'] = ""; }
        
        // UPCOMING LINK
        if (isset($h->vars['bookmarking']['filter']) || isset($h->vars['bookmarking']['filterText'])) {
            $url = $h->url(array('page'=>'upcoming', $h->vars['bookmarking']['filter']=>$h->vars['bookmarking']['filterText']));        
         } else { $url = $h->url(array('page'=>'upcoming',)); } 
        $h->vars['upcoming_link'] = $url;
        
        // UPCOMING ACTIVE OR INACTIVE        
        $h->vars['upcoming_active'] = $pagename == 'upcoming' && !isset($sort) ? "active" : '';
        
        // LATEST LINK
        if (isset($h->vars['bookmarking']['filter']) || isset($h->vars['bookmarking']['filterText'])) {
            $url = $h->url(array('page'=>'latest', $h->vars['bookmarking']['filter']=>$h->vars['bookmarking']['filterText']));        
         } else { $url = $h->url(array('page'=>'latest',)); } 
        $h->vars['latest_link'] = $url;               

        // LATEST ACTIVE OR INACTIVE        
        $h->vars['latest_active'] = $pagename == 'latest' && !isset($sort) ? "active" : '';
        
        // ALL LINK
        if (isset($h->vars['bookmarking']['filter']) || isset($h->vars['bookmarking']['filterText'])) {
            $url = $h->url(array('page'=>'all', $h->vars['bookmarking']['filter']=>$h->vars['bookmarking']['filterText']));        
         } else { $url = $h->url(array('page'=>'all',)); } 
        $h->vars['all_link'] = $url; 

        // ALL ACTIVE OR INACTIVE        
        $h->vars['all_active'] = $pagename == 'all' && !isset($sort) ? "active" : '';
        
        // 24 HOURS LINK
        if (isset($category)) { $url = $h->url(array('sort'=>'top-24-hours', 'category'=>$category));
         } elseif (isset($tag)) { $url = $h->url(array('sort'=>'top-24-hours', 'tag'=>$tag));
         } elseif (isset($media)) { $url = $h->url(array('sort'=>'top-24-hours', 'media'=>$media));
         } elseif (isset($user)) { $url = $h->url(array('sort'=>'top-24-hours', 'user'=>$user));
         } else { $url = $h->url(array('sort'=>'top-24-hours')); }
        $h->vars['24_hours_link'] = $url;

        // 24 HOURS ACTIVE OR INACTIVE        
        $h->vars['top_24_hours_active'] = isset($sort) && $sort == 'top-24-hours' ? "active" : '';
        
        // 48 HOURS LINK
        if (isset($category)) { $url = $h->url(array('sort'=>'top-48-hours', 'category'=>$category));
         } elseif (isset($tag)) { $url = $h->url(array('sort'=>'top-48-hours', 'tag'=>$tag));
         } elseif (isset($media)) { $url = $h->url(array('sort'=>'top-48-hours', 'media'=>$media));
         } elseif (isset($user)) { $url = $h->url(array('sort'=>'top-48-hours', 'user'=>$user));
         } else { $url = $h->url(array('sort'=>'top-48-hours')); }
        $h->vars['48_hours_link'] = $url;

        // 48 HOURS ACTIVE OR INACTIVE        
        $h->vars['top_48_hours_active'] = isset($sort) && $sort == 'top-48-hours' ? "active" : '';
        
        // 7 DAYS LINK
        if (isset($category)) { $url = $h->url(array('sort'=>'top-7-days', 'category'=>$category));
         } elseif (isset($tag)) { $url = $h->url(array('sort'=>'top-7-days', 'tag'=>$tag));
         } elseif (isset($media)) { $url = $h->url(array('sort'=>'top-7-days', 'media'=>$media));
         } elseif (isset($user)) { $url = $h->url(array('sort'=>'top-7-days', 'user'=>$user));
         } else { $url = $h->url(array('sort'=>'top-7-days')); }
        $h->vars['7_days_link'] = $url;

        // 7 DAYS ACTIVE OR INACTIVE        
        $h->vars['top_7_days_active'] = isset($sort) && $sort == 'top-7-days' ? "active" : '';
        
        // 30 DAYS LINK
        if (isset($category)) { $url = $h->url(array('sort'=>'top-30-days', 'category'=>$category));
         } elseif (isset($tag)) { $url = $h->url(array('sort'=>'top-30-days', 'tag'=>$tag));
         } elseif (isset($media)) { $url = $h->url(array('sort'=>'top-30-days', 'media'=>$media));
         } elseif (isset($user)) { $url = $h->url(array('sort'=>'top-30-days', 'user'=>$user));
         } else { $url = $h->url(array('sort'=>'top-30-days')); }
        $h->vars['30_days_link'] = $url;

        // 30 DAYS ACTIVE OR INACTIVE        
        $h->vars['top_30_days_active'] = isset($sort) && $sort == 'top-30-days' ? "active" : '';
        
        // 365 DAYS LINK
        if (isset($category)) { $url = $h->url(array('sort'=>'top-365-days', 'category'=>$category));
         } elseif (isset($tag)) { $url = $h->url(array('sort'=>'top-365-days', 'tag'=>$tag));
         } elseif (isset($media)) { $url = $h->url(array('sort'=>'top-365-days', 'media'=>$media));
         } elseif (isset($user)) { $url = $h->url(array('sort'=>'top-365-days', 'user'=>$user));
         } else { $url = $h->url(array('sort'=>'top-365-days')); }
        $h->vars['365_days_link'] = $url;

        // 365 DAYS ACTIVE OR INACTIVE        
        $h->vars['top_365_days_active'] = isset($sort) && $sort == 'top-365-days' ? "active" : '';
        
        // ALL TIME LINK
        if (isset($category)) { $url = $h->url(array('sort'=>'top-all-time', 'category'=>$category));
         } elseif (isset($tag)) { $url = $h->url(array('sort'=>'top-all-time', 'tag'=>$tag));
         } elseif (isset($media)) { $url = $h->url(array('sort'=>'top-all-time', 'media'=>$media));
         } elseif (isset($user)) { $url = $h->url(array('sort'=>'top-all-time', 'user'=>$user));
         } else { $url = $h->url(array('sort'=>'top-all-time')); }
        $h->vars['all_time_link'] = $url;
        
        // ALL TIME ACTIVE OR INACTIVE        
        $h->vars['top_all_time_active'] = isset($sort) && $sort == 'top-all-time' ? "active" : '';
        
        $h->pluginHook('bookmarking_sort_filter'); // allow custom filters
        //
        // display the sort links
        $h->template('bookmarking_sort_filter');
    }
    
    
    /**
     * Return data for REST apiCall
     */
    public function api_call($h, $action)
    {                        
        $h->vars['bookmarking_settings'] = $h->getSerializedSettings('bookmarking');
        
        // check if its a POST, GET, UPDATE or DELETE

        // get the BookmarkingFunctions class
        $funcs = $this->getBookmarkingFunctions($h);
                
        // check for params
        $limit = $h->cage->get->KeyExists('limit') ? $h->cage->get->testInt('limit') : 30;        
        
        switch ($action) {
            case 'get':                
                $post_count = $funcs->prepareList($h, '', 'count');   // get the number of posts
                $post_query = $funcs->prepareList($h, '', 'query');   // and the SQL query used				
                $result = $h->pagination($post_query, $post_count, $limit, 'posts');
                break;
            default:
                return false;
                break;
        }
        
        return $result;
    }
    
    
    public function post_rss_feed_items($h, $args = array())
    {
        $bookmarking_settings = $h->getSerializedSettings('bookmarking');
        
        $result = $args['result'];
        $item = $h->vars['post_rss_item'];
        
        // if RSS redirecting is enabled, append forward=1 to the url
        if (isset($bookmarking_settings['rss_redirect']) && !empty($bookmarking_settings['rss_redirect'])) {
            $item['link'] = $h->url(array('page'=>$result->post_id, 'forward'=>$result->post_id));
        } else {
            $item['link'] = $h->url(array('page'=>$result->post_id));
        }
        
        $h->vars['post_rss_item'] = $item;
    }
}
?>
