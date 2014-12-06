<?php
/**
 * name: Tag Cloud
 * description: Tag cloud page and widget
 * version: 0.4
 * folder: tag_cloud
 * class: TagCloud
 * requires: widgets 0.6
 * hooks: install_plugin, theme_index_top, header_include, theme_index_main, admin_plugin_settings, admin_sidebar_plugin_settings
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

class TagCloud
{
     /**
     * ********************************************************************* 
     * ********************* FUNCTIONS FOR POST CLASS ********************** 
     * *********************************************************************
     * ****************************************************************** */
     
    /**
     * Add a post_tags field to posts table if it doesn't alredy exist
     */
    public function install_plugin($h)
    {
        $tag_cloud_settings = $h->getSerializedSettings();
        
        if (!isset($tag_cloud_settings['tags_num_tags_page'])) { $tag_cloud_settings['tags_num_tags_page'] = 100; }
        if (!isset($tag_cloud_settings['tags_num_tags_widget'])) { $tag_cloud_settings['tags_num_tags_widget'] = 25; }
        if (!isset($tag_cloud_settings['tags_widget_title'])) { $tag_cloud_settings['tags_widget_title'] = 'checked'; }
        
        $h->updateSetting('tag_cloud_settings', serialize($tag_cloud_settings));

        // widget
        $h->addWidget('tag_cloud', 'tag_cloud', '');  // plugin name, function name, optional arguments
    }
    
    
    /**
     * Add additional member variables when the $post object is read in the Submit plugin.
     */
    public function theme_index_top($h)
    {
        // Get page title:
        switch ($h->pageName)
        {
            case 'tag-cloud':
                $h->pageTitle = $h->lang["tag_cloud"];
                $h->pageType = 'tags';
                break;
        }
    }
    
    
    /**
     * Display the tag cloud page
     *
     * @return bool
     */
    public function theme_index_main($h)
    {
        if ($h->pageName == 'tag-cloud') 
        {
            // get the number of tags to show:
            if ($h->isTest) { timer_start('cats'); }
            $tag_cloud_settings = $h->getSerializedSettings();
            if ($h->isTest) { print timer_stop(7, 'cats'); }
            $tag_count = $tag_cloud_settings['tags_num_tags_page'];
            // 0.0000069 - sep 24, 2014
             
            // build the tag cloud:
            $h->vars['tagCloud'] = $this->buildTagCloud($h, $tag_count);
            
            // display the tag cloud:
            $h->template('tag_cloud');
            return true;
        } 
        
        return false;
    }
            
            
    /**
     * Widget Tag Cloud
     */
    public function widget_tag_cloud($h)
    {
        $tag_cloud_settings = $h->getSerializedSettings('tag_cloud');
        $tag_count = $tag_cloud_settings['tags_num_tags_widget'];
        $show_title = $tag_cloud_settings['tags_widget_title'];
        
        // build the tag cloud:
        $cloud = $this->buildTagCloud($h, $tag_count);
        if (!$cloud) { return false; }
        
        $h->vars['show_title'] = $show_title;
        $h->vars['cloud'] = $cloud;
        
        $h->template('tag_cloud_widget', 'tag_cloud');
    }
    
    
    /**
     * Build Tag Cloud
     *
     * @param int $count number of tags to show
     * @return array
     */
    public function buildTagCloud($h, $count)
    { 	
        // TODO call a Hotaru lib function rather db direct
        
	$sql ="SELECT tags_word, COUNT(tags_word) AS CNT FROM " . TABLE_TAGS . ", " . TABLE_POSTS;
        $sql .= " WHERE tags_archived = %s AND (tags_post_id = post_id) AND";
        $sql .= " (post_status = %s || post_status = %s)";
	$sql .= " GROUP BY tags_word ORDER BY CNT DESC LIMIT " . $count;

        $query = $h->db->prepare($sql, 'N', 'new', 'top');
        $h->smartCache('on', 'tags', 60, $query); // start using cache
        $tags = $h->db->get_results($query);
        $h->smartCache('off'); // stop using cache

	//var_dump($tags);
        if (!$tags) { return false; }
        
        // Put the tags in an array:
        $popular_tags = array();
        if ($tags) {
            foreach ($tags as $tag) {		
                array_push($popular_tags, $tag->tags_word);
            }
        }

	//var_dump($popular_tags);

        // Divide into 10 groups and assign a class number (0 ~ 9) to each group:
        $grouped_tags = array_chunk($popular_tags, ($count/10), TRUE);
        foreach ($grouped_tags as $groupid => $group) {
            foreach ($group as $rank => $tag) {
                $tag = trim(urldecode($tag));
                $classed_tags[$rank]['link_word'] = $tag;
                $classed_tags[$rank]['show_word'] = $tag = stripslashes(str_replace('_', ' ', $tag));
                $classed_tags[$rank]['class'] = $groupid;
            }
        }

	//var_dump($grouped_tags);

        // Shuffle the order of the classed tags:
        shuffle($classed_tags);
        return $classed_tags;
    }

}
?>