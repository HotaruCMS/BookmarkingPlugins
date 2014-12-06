<?php
/**
 * name: Users
 * description: Provides profile, settings and permission pages
 * version: 2.3
 * folder: users
 * type: users
 * class: Users
 * hooks: install_plugin, pagehandling_getpagename, theme_index_top, navigation, header_include, bookmarking_functions_preparelist, breadcrumbs, theme_index_main, users_edit_profile_save, user_settings_save, admin_theme_main_stats, header_meta, post_rss_feed
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

//namespace Plugins;

class Users
{
    /**
    * Install Post Images
    */
    public function install_plugin($h)
    {
                // include following checks in case the folder has been deleted
                // we actually include a default images/user folder with some default images in the core zip
                $folder = BASE . '/content/images/user/';
		if(!is_dir($folder)){
			if(mkdir($folder,0777,true)) 
                            $msg =  "Users image folder was created";
			else 
                            $msg = "A folder for images at " . $folder . " could not be created. Please try creating manually";
		}
		else if(!is_writable($folder)){
			if(chmod("/somedir/somefile", 777)) 
                            $msg = "Folder found and made writable";
			else 
                            $msg = "Could not change the folders permissions, please make it writable manually";
		}
		else {
			$msg = "Image folder exists and is writeable";
                }
                
                $h->messages[$msg] = 'alert-info';               
    }
    
        
    /**
     * Check if we're looking at a user page
     */
    public function pagehandling_getpagename($h, $query_vars)
    {
        // we already know that there's no "page" parameter, so...
        if ($h->cage->get->keyExists('user')) {
            return 'profile'; // sets $h->pageName to "profile"
        }
    }
    
    
    /**
     * Determine what page we're looking at
     */
    public function theme_index_top($h)
    {        
        $username = $h->cage->get->testUsername('user');
        if ($username) {
            $h->subPage = 'user';
        }
        
        switch ($h->pageName)
        {
            case 'profile':
                $h->pageTitle = $h->lang["users_profile"] . '[delimiter]' . $username;
                $h->pageType = 'user';
                break;
            case 'account':
                $h->pageTitle = $h->lang["users_account"] . '[delimiter]' . $username;
                $h->pageType = 'user';
                break;
            case 'edit-profile':
                $h->pageTitle = $h->lang["users_profile_edit"] . '[delimiter]' . $username;
                $h->pageType = 'user';
                break;
            case 'user-settings':
                $h->pageTitle = $h->lang["users_settings"] . '[delimiter]' . $username;
                $h->pageType = 'user';
                break;
            case 'user-logins':
                $h->pageTitle = $h->lang["users_logins"] . '[delimiter]' . $username;
                $h->pageType = 'user';
                break;
            case 'permissions':
                if (!$username) { // when the permissions form is submitted
                    $userid = $h->cage->post->testInt('userid');
                    $username = $h->getUserNameFromId($userid);
                }
                $h->pageTitle = $h->lang["users_permissions"] . '[delimiter]' . $username;
                $h->pageType = 'user';
                break;
            case 'popular':
                if ($h->subPage == 'user') { $h->pageTitle = $h->lang["bookmarking_top"] . '[delimiter]' . $username . '[delimiter]' . SITE_NAME; }
                break;
            case 'latest':
                if ($h->subPage == 'user') { $h->pageTitle = $h->lang["bookmarking_latest"] . '[delimiter]' . $username; }
                break;
            case 'upcoming':
                if ($h->subPage == 'user') { $h->pageTitle = $h->lang["bookmarking_upcoming"] . '[delimiter]' . $username; }
                break;
            case 'all':
                if ($h->subPage == 'user') { $h->pageTitle = $h->lang["bookmarking_all"] . '[delimiter]' . $username; }
                break;
            case 'sort':
                if ($h->subPage == 'user') { 
                    $sort = $h->cage->get->testPage('sort');
                    $sort_lang = 'bookmarking_' . str_replace('-', '_', $sort);
                    $h->pageTitle = $h->lang[$sort_lang] . '[delimiter]' . $username;
                }
                break;
            case 'users':
                $h->pageTitle = 'Users';
                $h->pageType = 'users';
                break;
        }

        if ($h->pageType != 'user' && $h->subPage != 'user') { return false; }
        
        // read this user into the global hotaru object for later use on this page
        if ($username) {
            $h->displayUser->set($h, 0, $username);
        } else {
            // when the account page has been submitted (get id in case username has changed)
            $userid = $h->cage->post->testInt('userid');
            if ($userid) { 
                $h->displayUser->set($h, $userid); 
            } else {
                $h->displayUser->set($h, $h->currentUser->id); // default to self 
            }
        }
        //print "<br/><br/>displayUser<br/>";
        //print_r($h->displayUser);
        //print "<br/>*****************<br/>";
        if ($h->displayUser) {
            $h->vars['profile'] = $h->displayUser->getProfileSettingsData($h, 'user_profile');
            $h->vars['settings'] = $h->displayUser->getProfileSettingsData($h, 'user_settings');
        } else {
            $h->pageTitle = $h->lang["main_theme_page_not_found"];
            $h->pageType = '';
        }
        
        /* check for account updates */
        if ($h->pageName == 'account') {            
            $h->vars['checks'] = $h->displayUser->updateAccount($h);
            $h->displayUser->name = $h->vars['checks']['username_check'];
            $h->pageTitle = $h->lang["users_account"] . '[delimiter]' . $h->displayUser->name;
            $h->pageType = 'user';
        }
        
        // TODO deprecate by ver 2.0
        // for all old plugins we still need to set the vars user as well
        $h->vars['user'] = $h->displayUser;
    }
    
    
    /**
     * Match meta tags when browsing results for individual users 
     */
    public function header_meta($h)
    {
        if ($h->pageName == 'profile') {
            if (isset($h->vars['profile']['bio']) && ($h->vars['profile']['bio'] != $h->lang['users_profile_default_bio'])) { 
                echo '<meta name="description" content="' . $h->vars['profile']['bio'] . '" />' . "\n";
            } else {
                echo '<meta name="description" content="' . $h->lang['users_default_meta_description_before'] . $h->displayUser->name . $h->lang['users_default_meta_description_after'] . '" />' . "\n";  // default profile meta description (see language file)
            }
            
            echo '<meta name="keywords" content="' . $h->displayUser->name . $h->lang['users_profile_meta_keywords_more'] . '" />' . "\n";  // default profile meta keywords (see language file)
            
            return true;
        }
        
        
        if ($h->subPage == 'user' && ($h->pageName != 'profile'))
        { 
            $user = $h->cage->get->testUsername('user');
            if ($user) {
                $first_word = $h->pageName;
                if ($first_word == 'sort') { $first_word = $h->cage->get->testPage('sort'); }
                if ($first_word == 'index') { $first_word = $h->lang['users_meta_description_popular']; }
                $first_word = ucfirst(strtolower(make_name($first_word, '-')));
                echo '<meta name="description" content="' . $h->lang['users_meta_description_results_before'] . $first_word . $h->lang['users_meta_description_results_middle'] . $user . $h->lang['users_meta_description_results_after'] . '" />' . "\n";
                echo '<meta name="keywords" content="' . $user . $h->lang['users_profile_meta_keywords_more'] . '" />' . "\n";  // default profile meta keywords (see language file)
                return true;
            }
        }
    }
    
    
    /**
     * Filter posts to this user
     */
    public function bookmarking_functions_preparelist($h)
    {
        $username = $h->cage->get->testUsername('user');
        if ($username) {
            $h->vars['filter']['post_author = %d'] = $h->getUserIdFromName($username);
            unset($h->vars['filter']['post_archived = %s']);
        }
    }
    
    
    /**
     * Replace the default breadcrumbs in specific circumstances
     */
    public function breadcrumbs($h)
    {
        $crumbs = '';
        
        if ($h->displayUser->name) {
            $userlink = "<a href='" . $h->url(array('user'=>$h->displayUser->name)) . "'>";
            $userlink .= $h->displayUser->name . "</a>";
        } else {
        	return false;
        }
        
        // This is for user pages, e.g. account, edit profile, etc:
        switch ($h->pageName)
        {
            case 'profile':
                $crumbs = $userlink . ' / ' . $h->lang["users_profile"];
                //return $crumbs;
                break;
            case 'account':
                $crumbs = $userlink . ' / ' . $h->lang["users_account"];
                //return $crumbs;
                break;
            case 'edit-profile':
                $crumbs = $userlink . ' / ' . $h->lang["users_profile_edit"];
                //return $crumbs;
                break;
            case 'user-settings':
                $crumbs = $userlink . ' / ' . $h->lang["users_settings"];
                //return $crumbs;
                break;
            case 'permissions':
                $crumbs = $userlink . ' / ' . $h->lang["users_permissions"];
                //return $crumbs;
                break;
            case 'user-logins':
                $crumbs = $userlink . ' / ' . $h->lang["users_logins"];
                //return $crumbs;
                break;
        }

        // This is used for filtered story pages, e.g. popular, latest, etc:
        if ($h->subPage == 'user' && $h->pageType == 'list') {
            switch ($h->pageName) {
                case 'index':
                    $title = $h->lang["bookmarking_top"];
                    break;
                case 'latest':
                    $title = $h->lang["bookmarking_latest"];
                    break;
                case 'upcoming':
                    $title = $h->lang["bookmarking_upcoming"];
                    break;
                case 'all':
                    $title = $h->lang["bookmarking_all"];
                    break;
                case 'sort':
                    $sort = $h->cage->get->testPage('sort');
                    $sort_lang = 'bookmarking_' . str_replace('-', '_', $sort);
                    $title = $h->lang[$sort_lang];
                    break;
                default:
                    $title = $h->lang['users_posts'];
                    break;
            }

            $user = $h->cage->get->testUsername('user');
            $crumbs = "<a href='" . $h->url(array('user'=>$user)) . "'>\n";
            $crumbs .= $user . "</a>\n ";
            $crumbs .= " / " . $title;                                    
            
            return $crumbs . $h->rssBreadcrumbsLink('', array('user'=>$user));
        }
        
        // only show if the person has admin access
        if ($h->currentUser->adminAccess) { 
            
            // put a dropdown on the right handside of the breadcrumb nav
            $crumbs .= '<li class="pull-right">' .
                    '<div class="btn-group">' .
                    '<a class="btn btn-xs btn-primary dropdown-toggle" data-toggle="dropdown" href="#">' .
                    'Admin&nbsp;<span class="caret"></span></a>' .
                    '<ul class="dropdown-menu">' .
                    '<!-- dropdown menu links -->';

                        $crumbs .= '<li><a href="' . $h->url(array('page'=>'account', 'user'=>$h->displayUser->name)) . '">' . $h->lang["users_account"] . '</a></li>';
                        $crumbs .= '<li><a href="' . $h->url(array('page'=>'user-logins', 'user'=>$h->displayUser->name)) . '">' . $h->lang("users_logins") . '</a></li>';
                        $crumbs .= '<li><a href="' . $h->url(array('page'=>'user-settings', 'user'=>$h->displayUser->name)) . '">' . $h->lang["users_settings"] . '</a></li>';                        
                        
                        $crumbs .= '<li class="divider"></li>';
                        
                        // show permissions   
                        $href = $h->url(array('page'=>'permissions', 'user'=>$h->displayUser->name));
                        $crumbs .= '<li><a href="' . $href . '">' . $h->lang["users_permissions"] . '</a></li>';
 
                        // show User Manager link only if theplugin is active
                        if ($h->isActive('user_manager')) {
                            $crumbs .= '<li><a href="' . BASEURL . 'admin_index.php?search_value=' . $h->displayUser->name . '&amp;plugin=user_manager&amp;page=plugin_settings&amp;type=search#tab_settings">' . $h->lang['user_man_link'] . '</a></li>';

                            $h->pluginHook('profile_navigation_restricted');                        
                         }        
                         
                $crumbs .= '</ul>' .
                  '</div></li>';
        }

        return $crumbs;
    }
    
    
    /**
     * Display the right page
     */
    public function theme_index_main($h)
    {
        if ($h->pageType != 'user' && $h->pageType != 'users') { return false; }

        if ($h->pageType == 'users') {
                $this->usersBrowse($h);
                return;
        }
        
        //print_r($h->displayUser);
        if (!$h->displayUser->name) { return false; }
        if ($h->userExists(0, $h->displayUser->name) == 'no') { return false; }
        
        // determine permissions
        $admin = false; $own = false; $denied = false;
        if ($h->currentUser->getPermission('can_access_admin') == 'yes') { $admin = true; }
        if ($h->currentUser->id == $h->displayUser->id) { $own = true; }

        $h->template('users_navigation');
        
        switch($h->pageName) {
            case 'profile':
                $h->template('users_profile');
                return true;
                break;
            case 'account':
                if (!$admin && !$own) { $denied = true; break; }
                $h->template('users_account');
                return true;
                break;
            case 'edit-profile':
                if (!$admin && !$own) { $denied = true; break; }
                $h->template('users_edit_profile');
                return true;
                break;
            case 'user-settings':
                if (!$admin && !$own) { $denied = true; break; }
                $h->template('users_settings');
                return true;
                break;
            case 'permissions':
                if (!$admin ) { $denied = true; break; }
                $this->editPermissions($h);
                $h->template('users_permissions');
                return true;
                break;
            case 'user-logins':
                if (!$admin && !$own) {
                    $denied = true;
                    break; 
                }
                if (version_compare($h->version, '1.6.6') > 0) {
                    $h->vars['logins'] = $h->getUserLogins($h->displayUser->id);
                    $h->template('users_logins');
                    return true;
                }
                break;
        }
        
        if ($denied) {
            $h->messages[$h->lang["main_access_denied"]] = 'red';
            $h->showMessages();
        }
    }
    
    
    public function navigation($h)
    {
        $h->template('users_top_navigation');
    }
    
    
    private function usersBrowse($h)
    {
        // gets query and total count for pagination
        $users_query = $h->getUsers(0, 'query');
        $users_count = $h->getUsers(0, 'count');
        
        $limit = 30;
        // pagination 
        $h->vars['pagedResults'] = $h->pagination($users_query, $users_count, $limit, 'users');
        
        $h->template('users_browse');
        
        if ($h->vars['pagedResults']) { echo $h->pageBar($h->vars['pagedResults']); }
    }
    
    
    /**
     * Save profile data (from hook in edit_profile.php)
     */
    public function users_edit_profile_save($h, $vars)
    {
        $username = $vars[0];
        $profile = $vars[1];
        
        // check CSRF key
        if (!$h->csrf()) {
            $h->message = $h->lang['error_csrf'];
            $h->messageType = "red";
            return false;
        }
        
        $h->displayUser->saveProfileSettingsData($h, $profile, 'user_profile', $h->displayUser->id);
        
        /*  Problem! The previous profile data is cached and we don't want to disable caching for profiles, 
            nor do we want to clear the entire db_cache, so instead, we'll delete the cache file that holds
            the previous profile for this user. */
        $sql = "SELECT usermeta_value FROM " . DB_PREFIX . "usermeta WHERE usermeta_userid = %d AND usermeta_key = %s";
        $query = $h->db->prepare($sql, $h->displayUser->id, 'user_profile');
        $cache_file = CACHE . 'db_cache/' . md5($query) . '.php';
        if (file_exists($cache_file)) {
            unlink($cache_file); // delete cache file.
        }
        
        $h->message = $h->lang["users_profile_edit_saved"] . "<br />\n";
        $h->message .= "<a href='" . $h->url(array('user'=>$h->displayUser->name)) . "'>";
        $h->message .= $h->lang["users_profile_edit_view_profile"] . "</a>\n";
        $h->messageType = "green";
    }
    
    
    /**
     * Save settings data (from hook in user_settings.php)
     */
    public function user_settings_save($h, $vars)
    {
        $username = $vars[0];
        $settings = $vars[1];
        
        // check CSRF key
        if (!$h->csrf()) {
            $h->message = $h->lang['error_csrf'];
            $h->messageType = "red";
            return false;
        }
        
        $h->displayUser->saveProfileSettingsData($h, $settings, 'user_settings', $h->displayUser->id);
        
        /*  Problem! The previous settings data is cached and we don't want to disable caching for settings, 
            nor do we want to clear the entire db_cache, so instead, we'll delete the cache file that holds
            the previous settings for this user. */
        $sql = "SELECT usermeta_value FROM " . DB_PREFIX . "usermeta WHERE usermeta_userid = %d AND usermeta_key = %s";
        $query = $h->db->prepare($sql, $h->displayUser->id, 'user_settings');
        $cache_file = CACHE . 'db_cache/' . md5($query) . '.php';
        if (file_exists($cache_file)) {
            unlink($cache_file); // delete cache file.
        }
        
        $h->message = $h->lang["users_settings_saved"] . "<br />\n";
        $h->messageType = "green";
    }
    
    
    /** 
     * Enable admins to edit a user
     */
    public function editPermissions($h)
    {
        // prevent non-admin user viewing permissions of admin user
        if (($h->displayUser->role) == 'admin' && ($h->currentUser->role != 'admin')) {
            $h->messages[$h->lang["users_account_admin_admin"]] = 'red';
            $h->showMessages();
            return true;
        }
        
        $perm_options = $h->getDefaultPermissions('', 'site', true);
        $perms = $h->displayUser->getAllPermissions();
        
        // If the form has been submitted...
        if ($h->cage->post->keyExists('permissions')) {
        
            // check CSRF key
            if (!$h->csrf()) {
                $h->messages[$h->lang['error_csrf']] = 'red';
                return false;
            }
        
           foreach ($perm_options as $key => $options) {
                if ($value = $h->cage->post->testAlnumLines($key)) {
                    $h->displayUser->setPermission($key, $value);
                }
            }

            $h->displayUser->updatePermissions($h);   // physically store changes in the database
            
            // get the newly updated latest permissions:
            $perm_options = $h->getDefaultPermissions('', 'site', true);
            $perms = $h->displayUser->getAllPermissions();
            $h->messages[$h->lang['users_permissions_updated']] = 'green';
        }
        
        $h->vars['perm_options'] = '';
        foreach ($perm_options as $key => $options) {
            $h->vars['perm_options'] .= "<tr><td>" . make_name($key) . ": </td>\n";
            foreach($options as $value) {
                if (isset($perms[$key]) && ($perms[$key] == $value)) { $checked = 'checked'; } else { $checked = ''; } 
                if ($key == 'can_access_admin' && $h->displayUser->role == 'admin') { $disabled = 'disabled'; } else { $disabled = ''; }
                $h->vars['perm_options'] .= "<td><input type='radio' name='" . $key . "' value='" . $value . "' " . $checked . " " . $disabled . "> " . $value . " &nbsp;</td>\n";
            }
            $h->vars['perm_options'] .= "</tr>";
        }
    }
    

    /**
     * Show stats on Admin home page
     */
    public function admin_theme_main_stats($h, $vars)
    {
        if (version_compare($h->version, '1.6.6') > 0) {
            $ui = \Libs\UserInfo::instance();
        } else {
            $ui = new UserInfo();
        }
        $stats = $ui->stats($h); //, 'today');

		//var_dump($stats);
	
		echo "<li>&nbsp;</li>";
		if ($stats) {
		    foreach ($stats as $stat) {
			$users[$stat[0]] = $stat[1];
		    }
		}
	 
		if (isset($vars) && (!empty($vars))) {
			foreach ($vars as $key => $value) {
				$key_lang = 'users_admin_stats_' . $key;
				echo "<li class='title'>" . $h->lang($key_lang) . "</li>";
				foreach ($value as $stat_type) {
					if (isset($value) && !empty($value)) {
	
						switch ($stat_type) {
						    case 'all':
							$user_count = isset($users) ? array_sum($users) : '';						
							break;
						    default:
							if (isset($users[$stat_type])) { $user_count = $users[$stat_type]; } else { $user_count = 0; }
							break;
						}

						if (!defined('SITEURL')) { define('SITEURL', BASEURL); }

						$link = "";
						$dontlink = array('');
						if ($h->isActive('user_manager')) {
							if (!in_array($stat_type, $dontlink)) {
							$link = SITEURL . "admin_index.php?user_filter=$stat_type&plugin=user_manager&page=plugin_settings&type=filter&csrf=" . $h->csrfToken;
							}
						}
						
						$lang_name = 'users_admin_stats_' . $stat_type;
						echo '<li data-bind="text: userCount">';
						if ($link) { echo "<a href='" . $link . "'>"; }
						echo $h->lang($lang_name) . ": " . $user_count;
						if ($link) { echo "</a>"; }
						echo "</li>";
					}
				}
			}
		}
    }
    
    
    /**
     * If a user feed, set it up
     */
    public function post_rss_feed($h)
    {
        $user = $h->cage->get->testUsername('user');
        if (!$user) {
            return false; 
        }
        
        $user_id = $h->getUserIdFromName($user);
        if ($user_id) { 
            $h->vars['postRssFilter']['post_author = %d'] = $user_id;
        }
        $h->vars['postRssFeed']['description'] = $h->lang["post_rss_from_user"] . " " . $user; 
    }
}
