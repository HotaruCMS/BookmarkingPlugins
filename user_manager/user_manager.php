<?php
/**
 * name: User Manager
 * description: Manage users.
 * version: 1.5
 * folder: user_manager
 * class: UserManager
 * type: Admin
 * requires: users 1.1, user_signin 0.5
 * hooks: hotaru_header, install_plugin, admin_header_include, admin_plugin_settings, admin_sidebar_plugin_settings, post_manager_user_name, comment_manager_user_name, submit_edit_end, admin_sidebar_users
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
 * @author    Nick Ramsay <admin@hotarucms.org>
 * @copyright Copyright (c) 2009, Hotaru CMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      http://www.hotarucms.org/
 */
 
class UserManager
{
    // See user_manager_settings.php for main functions

    /**
     * Insert icons next to user name
     *
     * @return bool
     */
    public function comment_manager_user_name($h) {
        // deprecated 
    }


    /**
     * Add link to user at bottom of Submit Edit Post
     */
    public function submit_edit_end($h, $vars)
    {
        // need admin access permissions:
        if ($h->currentUser->getPermission('can_access_admin') != 'yes') { return false; }
        
        $username = $h->getUserNameFromId($vars['userid']);
        
        echo "<p class='user_man_find_user'><a href='" . BASEURL . "admin_index.php?search_value=" . $username . "&plugin=user_manager&page=plugin_settings&type=search'>" . $h->lang['user_man_find_user'] . "</a></p>";
    }
    
    /**
     * Add link to admin sidebar
     */
    public function admin_sidebar_users($h)
    {
        $links = array(
            'Add User' => array('admin_index.php?plugin=user_manager&page=plugin_settings&subpage=add_user'),
            'List Users' => array('admin_index.php?page=plugin_settings&plugin=user_manager')
        );
        return $links;
    }
}

?>