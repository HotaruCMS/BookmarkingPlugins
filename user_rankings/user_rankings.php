<?php
/**
 * name: User Rankings
 * description: List your site's top users
 * version: 0.2
 * folder: user_rankings
 * class: UserRankings
 * requires: activity 0.7
 * hooks: install_plugin, header_include, admin_sidebar_plugin_settings, admin_plugin_settings, theme_index_top, theme_index_main, profile_navigation, profile_content
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


class UserRankings
{
    /**
     *  Add default settings for Sidebar Comments plugin on installation
     */
    public function install_plugin($h)
    {
        // Default settings
        $user_rankings_settings = $h->getSerializedSettings();
        
        if ($h->isActive('avatar')) {
            if (!isset($user_rankings_settings['show_avatar'])) { $user_rankings_settings['show_avatar'] = "checked"; }
        } else {
            if (!isset($user_rankings_settings['show_avatar'])) { $user_rankings_settings['show_avatar'] = ""; }
        }
	if (!isset($user_rankings_settings['show_admin'])) { $user_rankings_settings['show_admin'] = 'checked'; }
        if (!isset($user_rankings_settings['time_period_days'])) { $user_rankings_settings['time_period_days'] = 30; }
        if (!isset($user_rankings_settings['avatar_size_widget'])) { $user_rankings_settings['avatar_size_widget'] = 16; }
        if (!isset($user_rankings_settings['avatar_size_page'])) { $user_rankings_settings['avatar_size_page'] = 16; }
        if (!isset($user_rankings_settings['show_name'])) { $user_rankings_settings['show_name'] = 'checked'; }
        if (!isset($user_rankings_settings['show_points'])) { $user_rankings_settings['show_points'] = "checked"; }
        if (!isset($user_rankings_settings['widget_number'])) { $user_rankings_settings['widget_number'] = 10; }
        if (!isset($user_rankings_settings['page_number'])) { $user_rankings_settings['page_number'] = 20; }
        if (!isset($user_rankings_settings['points_post'])) { $user_rankings_settings['points_post'] = 100; }
        if (!isset($user_rankings_settings['points_comment'])) { $user_rankings_settings['points_comment'] = 50; }
        if (!isset($user_rankings_settings['points_vote'])) { $user_rankings_settings['points_vote'] = 20; }
        if (!isset($user_rankings_settings['cache_duration'])) { $user_rankings_settings['cache_duration'] = 240; } // 12 hours
	if (!isset($user_rankings_settings['truncate_username'])) { $user_rankings_settings['truncate_username'] = 14; }
        
        $h->updateSetting('user_rankings_settings', serialize($user_rankings_settings));
        
        // widget
        $h->addWidget('user_rankings', 'user_rankings', '');  // plugin name, function name, optional arguments
    }
    
    
    /**
     *  Set up the User Rankings page
     */
    public function theme_index_top($h)      
    {   
        $h->vars['user_ranking_settings'] = $h->getSerializedSettings('user_rankings');
        
        if ($h->pageName != 'user_rankings' && $h->pageName != 'ranking') { return false; }
        
        switch ($h->pageName)
        {
            case 'user-rankings':
                $h->pageTitle = $h->lang["user_rankings_title"];
                $h->pageType = 'rankings';
                break;
            case 'ranking':
                $h->pageTitle = $h->lang["user_rankings_title"] . "[delimiter]" . $h->currentUser->name;
                $h->pageType = 'user';  // this setting hides the posts filter bar
                $h->subPage = 'user'; 
                break;
        }                
    }


    /**
     *  Display the User Rankings page
     */
    public function theme_index_main($h)
    {
        if ($h->pageName != 'user_rankings' && $h->pageName != 'ranking') { return false; }

        switch ($h->pageName)
        {
            case 'user_rankings':
                $h->displayTemplate('user_rankings_page');
                return true;
                break;
            case 'ranking':
                $h->displayTemplate('user_rankings_single');
                return true;
                break;
        }
                
        return true;
    }
    
    /**
     * Profile navigation link
     */
    public function profile_navigation($h)
    {
        if (isset($h->vars['theme_settings']['userProfile_tabs']) && $h->vars['theme_settings']['userProfile_tabs']) {
            echo "<li><a href='#ranking' data-toggle='tab'>" . $h->lang('user_rankings_profile_tab_title') . "</a></li>\n";
        } else {
            echo "<li><a href='" . $h->url(array('page'=>'ranking', 'user'=>$h->displayUser->name)) . "' >" . $h->lang('user_rankings_profile_tab_title') . "</a></li>\n";         
        } 
    }
    
    /**
     * 
     * @param type $h
     * @return boolean
     */
    public function profile_content($h)
    {       
            echo '<div class="tab-pane" id="ranking">';            
            
                $h->displayTemplate('user_rankings_single');                         
            
            echo "</div>";            

            return true;
    }
    
    
    /**
     * Display the latest user rankings in a widget block
     */
    public function widget_user_rankings($h)
    {
	$ur_settings = $h->getSerializedSettings('user_rankings');
        if (!$ur_settings) { return false; }

        // build link that will link the widget title to user_rankings page...
        $anchor_title = sanitize($h->lang["user_rankings_title_anchor_title"], 'ents');
        $title = "<a href='" . $h->url(array('page'=>'user_rankings')) . "' title='" . $anchor_title . "'>";
        $title .= $h->lang['user_rankings_widget_title'] . "</a>";

        $output = "<h2 class='widget_head user_rankings_widget_title'>\n";
        $link = BASEURL;
        $output .= $title;
        $output .= $h->lang['user_rankings_widget_subtitle'];
	$output = str_replace('30', $ur_settings['time_period_days'], $output);
        $output .= "</h2>\n"; 
            
        $output .= "<ul class='widget_body user_rankings_widget_items'>\n";
        
        $output .= $this->displayUserRankings($h, true); // 'widget' = true
        $output .= "</ul>\n\n";
        
        // Display the whole thing:
        if (isset($output) && $output != '') { echo $output; }
    }
    
    
    public function displaySingleUserRanking($h, $userId = 0)
    {
        $ur_settings = $h->vars['user_ranking_settings'];
        if (!$ur_settings) { return false; }
        
        $result = $this->singleUserRanking($h, $userId, 90);
        
        if (!$result) { return false; }	
	
        $output = '';
        if ($result) {
            foreach ($result as $activity)
            {
                $points = 0;
                switch (strtolower($activity->useract_key)) {
                    case 'post':
                        $points = $activity->points * $ur_settings['points_post'];
                        break;
                    case 'vote':
                        $points = $activity->points * $ur_settings['points_vote'];
                        break;
                    case 'comment':
                        $points = $activity->points * $ur_settings['points_comment'];
                        break;
                    default:
                        break;
                }            
                $output .= "<li>" . ucfirst($activity->useract_key) . ' : ' . number_format($points, 0) . "</li>";
            }
        }
        
        return $output;
    }
    
    
    /**
     * Get user rankings <li> items
     *
     * @param array $users
     * @param bool $widget
     * return string $output
     */
    public function displayUserRankings($h, $widget = false)
    {
        // get settings from the database
        $ur_settings = $h->getSerializedSettings('user_rankings');
        if (!$ur_settings) { return false; }
        
        if ($widget) { 
            $limit = $ur_settings["widget_number"];
            $css = 'widget';
        } else { 
            $limit = $ur_settings["page_number"];
            $css = 'page';
        }
        
        $need_cache = false;
        $label = 'user_rankings_' . $css;
        
        // check for a cached version and use it if no recent update:
        $output = $h->cacheHTML($ur_settings['cache_duration'], '', $label);
        if ($output) {
            return $output;
        } else {
            $need_cache = true;
        }

        // get all users with activity in the last X days, ordered by points
        $result = $this->generateUserRankings($h, $limit);
        if (!$result) { return false; }	
	
        $output = '';
        foreach ($result as $users => $data)
        {
           
            $output .= "<li class='user_rankings_" . $css . "_item user_rankings_clearfix'>\n";
            
            if ($ur_settings['show_avatar'] && $h->isActive('avatar')) {
                $size = 'avatar_size_' . $css;
                $h->setAvatar($data->useract_userid, $ur_settings[$size]);
                $output .= "<div class='user_rankings_" . $css . "_avatar'>\n";
                $output .= $h->linkAvatar();
                $output .= "</div> \n";
            }

	    $show_username = substr($data->user_username, 0, $ur_settings['truncate_username']);
            
            if ($ur_settings['show_name']) {
                $output .= "<a class='user_rankings_" . $css . "_name' href='" . $h->url(array('user' => $data->user_username)) . "'>" . $show_username . "</a> \n";
            }
            
            $h->vars['user_rankings_output'] = "";
            $h->pluginHook('user_rankings_item', '', array('points' => $data->points, 'css' => $css));
            $output .= $h->vars['user_rankings_output'];
            
            $output .= "<div class='user_rankings_" . $css . "_points'>" . $data->points . "</div>\n";
            $output .= "</li>\n\n";

        }
        
        if ($need_cache) {
            $h->cacheHTML($ur_settings['cache_duration'], $output, $label); // make or rewrite the cache file
        }
        
        return $output;
    }
    
    
    /**
     *  Generate User Rankings
     */
    public function generateUserRankings($h, $limit = 10)
    {
        // get settings from the database
        $ur_settings = $h->getSerializedSettings('user_rankings');
        if (!$ur_settings) { return false; }

	$show_admin = $ur_settings['show_admin'] ? "" : " AND u.user_role <> 'admin' ";
        $time_ago = "- " . $ur_settings['time_period_days'] . " Days";
        $time_ago = date('YmdHis', strtotime($time_ago));

	$sql = "SELECT a.useract_userid, u.user_username, SUM( CASE" .
		" WHEN (a.useract_key = 'post') THEN " . $ur_settings['points_post'] .
		" WHEN (a.useract_key = 'vote') THEN " . $ur_settings['points_vote'] .
		" WHEN (a.useract_key = 'comment') THEN " . $ur_settings['points_comment'] .
		" END) AS points" .
		" FROM " . TABLE_USERACTIVITY ." a RIGHT JOIN " . TABLE_USERS . " u ON a.useract_userid = u.user_id ".
		" WHERE a.useract_archived = %s AND a.useract_status = %s AND a.useract_date > %s" . $show_admin .
		" GROUP BY a.useract_userid" .
		" ORDER BY points DESC LIMIT ". $limit;

        $query = $h->db->prepare($sql, 'N', 'show', $time_ago);		
        $results = $h->db->get_results($query);

	return $results;
    }
    
    public function singleUserRanking($h, $userId = 0, $time_ago = '100000')
    {
        // Allow archived posts here since we may want to get total score from beginning
        
        $sql = "SELECT useract_key, count(useract_id) as points FROM " . TABLE_USERACTIVITY . 
                " WHERE useract_userid = %d AND useract_status = %s AND useract_date > %s" .
                " GROUP BY useract_key";
        
        $time_ago = "- " . $time_ago . " Days";
        $time_ago = date('YmdHis', strtotime($time_ago));
        
        $query = $h->db->prepare($sql, $userId, 'show', $time_ago);	
        $results = $h->db->get_results($query);

	return $results;
    }
}