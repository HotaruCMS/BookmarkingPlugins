<?php
/**
 * name: Tags
 * description: Show tags, filter tags and RSS for tags
 * version: 2.0
 * folder: tags
 * class: Tags
 * type: tags
 * hooks: theme_index_top, header_include, header_include_raw, header_meta, show_post_extra_fields, bookmarking_functions_preparelist, breadcrumbs, post_rss_feed, admin_plugin_settings
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

class Tags
{
    /**
     * Determine if we are filtering to tags
     */
    public function theme_index_top($h)
    {
        $h->vars['tags_settings'] = $h->getSerializedSettings('tags');
        
        // commit list of exclude tags to memory for later in page when we loop
        $h->vars['exclude_tags'] = array();
        if ($h->vars['tags_settings']['tags_setting_exclude_active'] && $h->vars['tags_settings']['tags_setting_exclude_words'])  {
            $exclude_tags = explode(',', $h->vars['tags_settings']['tags_setting_exclude_words']);	    
            array_walk($exclude_tags, array($this,'trim_value'));
            $h->vars['exclude_tags'] = $exclude_tags;
        } 
                
        if ($h->cage->get->keyExists('tag')) { 
            $h->pageTitle = stripslashes(make_name($h->cage->get->noTags('tag')));
            if (!$h->pageName) { $h->pageName = 'popular'; }
            if ($h->pageName == $h->home) { $h->pageTitle .=  '[delimiter]' . SITE_NAME; }
            $h->subPage = 'tags';
            $h->pageType = 'list';
            $h->vars['tag'] = $h->cage->get->noTags('tag');
        } 
    }
    
    
    /**
     * Match meta tag to a post's keywords (description is done in the Submit plugin)
     * Also changes meta when browsing a tag page
     */
    public function header_meta($h)
    {    
        if ($h->pageType == 'post')
        {
            echo '<meta name="keywords" content="' . stripslashes($h->post->tags) . '" />' . "\n";
            return true;
        } 
        elseif ($h->subPage == 'tags')
        { 
            $tag = stripslashes($h->vars['tag']); 
            
            if ($tag) {
                echo '<meta name="description" content="' . $h->lang['tags_meta_description_before'] . $tag . $h->lang['tags_meta_description_after'] . '" />' . "\n";
                echo '<meta name="keywords" content="' . $tag . $h->lang['tags_meta_keywords_more'] . '" />' . "\n";
                return true;
            }
        }
    }
    
    
    /**
     * JavaScript for dropdown tags list
     */
    public function header_include_raw($h)
    {    
//        echo "<script type='text/javascript'>\n";
//        echo "$(document).ready(function(){\n";
//            echo "$('.tags_link').click(function () {\n";
//            echo "var target = $(this).parents('div').nextAll('div.show_post_extras').children('div.show_tags');\n";
//            echo "target.fadeToggle();\n";
//            echo "return false;\n";
//            echo "});\n";
//        echo "});\n";
//        echo "</script>\n";
    }

    
    /**
     * Gets a tag from the url and sets the filter for get_posts
     */
    public function bookmarking_functions_preparelist($h)
    {
        if ($h->subPage == 'tags') 
        {
            $tag = stripslashes($h->vars['tag']); 
            
            if ($tag) {
                $h->vars['filter']['post_tags LIKE %s'] = '%' . urlencode($tag) . '%'; 
                $h->vars['filter']['post_archived = %s'] = 'N'; // don't include archived posts
            }
            return true;    
        }
        
        return false;    
    }
    
    
    /**
     * Add RSS link to breadcrumbs
     */
    public function breadcrumbs($h)
    {
        if ($h->subPage != 'tags') { return false; }
        
        $crumbs = "<a href='" . $h->url(array('tag'=>$h->vars['tag'])) . "'>\n";
        $crumb_title = stripslashes(make_name($h->cage->get->noTags('tag')));
        $crumbs .= $crumb_title . "</a>\n ";
        
        return $crumbs . $h->rssBreadcrumbsLink('', array('tag'=>$h->vars['tag']));
    }
    
    
    /**
     * Shows tags in each post
     */
//    public function show_post_extras($h)
//    { 
//        if ($h->post->tags)
//        { 
//            echo "";
//        }
//    }
    
    
     /**
     * List of tags
     */
    public function show_post_extra_fields($h, $vars = array())
    {
        if (!$h->post->tags) { return false; }

        $tags = explode(',', $h->post->tags);
	    
        $statusTags = array();
        if ($h->vars['exclude_tags']) {
            foreach ($tags as $tag) {
                if (in_array($tag, $h->vars['exclude_tags'])) {
                    $statusTags[$tag] = 'exclude';
                }
            }
            //$tags = array_diff( $tags, $h->vars['exclude_tags'] );
        }                
	
        // check the params
        if (isset($vars[0]) && isset($vars[1]) && ($vars[0] == "tags") && ($vars[1] == "raw")) {
            $raw = true;
        } else {
            $raw = false;
        }
        
        // put vars in memory for template
        $h->vars['tags']['raw'] = $raw;
        $h->vars['tags']['tags'] = $tags;
        $h->vars['statusTags'] = $statusTags;
                
        $h->template('tags', 'tags', false);

    }


    
    //required for above array_walk method
    public function trim_value(&$value)
    { 
	$value = trim($value);
    }


    
    
    /**
     * If a tag feed, set it up
     */
    public function post_rss_feed($h)
    {
        $tag = $h->cage->get->noTags('tag');
        if (!$tag) { return false; }
        
        $h->vars['postRssFilter']['post_tags LIKE %s'] = '%' . urlencode(stripslashes($tag)) . '%'; 
        $tag = str_replace('_', ' ', stripslashes(html_entity_decode($tag, ENT_QUOTES,'UTF-8'))); 
        $h->vars['postRssFeed']['description'] = $h->lang["post_rss_tagged"] . " " . $tag;
    }
}
?>