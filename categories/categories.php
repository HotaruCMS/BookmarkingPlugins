<?php
/**
 * name: Categories
 * description: Enables categories for posts
 * version: 2.2
 * folder: categories
 * class: Categories
 * type: categories
 * hooks: theme_index_top, install_plugin, header_include, pagehandling_getpagename, bookmarking_functions_preparelist, show_post_author_date, categories_post_show, header_end, breadcrumbs, header_meta, post_rss_feed, admin_plugin_settings, admin_sidebar_plugin_settings
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

class Categories
{
    /*
    * Setup the default settings
    * */
    public function install_plugin($h)
    {
           // Get plugin settings if they exist
           $categories_settings = $h->getSerializedSettings();

           if (!isset($categories_settings['categories_nav_style'])) { $categories_settings['categories_nav_style'] = 'style1'; }
           if (!isset($categories_settings['categories_nav_show'])) { $categories_settings['categories_nav_show'] = 'checked'; }

           // Update plugin settings
           $h->updateSetting('categories_settings', serialize($categories_settings));
    }
        
    /**
     * Determine if we are filtering to a category
     * Categories might be numeric, e.g. category=3 or safe names, e.g. category=news_and_business
     * We also test for urls like domain.com/News/ where "News" is a category 
     */
    public function theme_index_top($h)
    {
        // if there's a "category" key in the url...
        
        if ($h->cage->get->keyExists('category'))
        { 
            //print 'category: ' .$h->cage->get->keyExists('category');
            $category = $h->cage->get->noTags('category');
            
            if (is_numeric($category)) {
                $catInfo = $h->getCatFullData($category);
            } else {               
                $catInfo = $h->getCatFullData(0, $category);
            }
            
            if ($catInfo) {
                $h->vars['category_id'] = isset($catInfo->category_id) ? $catInfo->category_id : '';
                $h->vars['category_name'] = isset($catInfo->category_name) ? $catInfo->category_name : '';
                $h->vars['category_safe_name'] = isset($catInfo->category_safe_name) ? $catInfo->category_safe_name : '';
                $h->vars['category_parent'] = isset($catInfo->category_parent) ? $catInfo->category_parent : '';
                $h->vars['category_desc'] = isset($catInfo->category_desc) ? $catInfo->category_desc : '';
                $h->vars['category_keywords'] = isset($catInfo->category_keywords) ? $catInfo->category_keywords : '';
            } else {
                return false;
            }
            
            $h->pageTitle = isset($h->vars['category_name']) ? $h->vars['category_name'] : null;
            if (!$h->pageName) { $h->pageName = 'popular'; }
            if ($h->pageName == $h->home) { $h->pageTitle .=  '[delimiter]' . SITE_NAME; }
            $h->subPage = 'category';
            $h->pageType = 'list';
        }
        elseif (!$h->pageType)  // only do this if we don't know the pageType yet... 
        {
            if ($h->pageName == 'all') { return false; } // when sorting to "all", we don't want to filter to the "all" category!

            /*  if $h->pageName is set, then there must be an odd number of query vars where
                the first one is the page name. Let's see if it's a category safe name... */
            $catInfo = $h->getCatFullData(0, $h->pageName);
            
            if ($catInfo) {
                $h->vars['category_id'] = isset($catInfo->category_id) ? $catInfo->category_id : '';
                $h->vars['category_name'] = isset($catInfo->category_name) ? $catInfo->category_name : ''; 
                $h->vars['category_desc'] = isset($catInfo->category_desc) ? $catInfo->category_desc : '';
                $h->vars['category_keywords'] = isset($catInfo->category_keywords) ? $catInfo->category_keywords : '';     
                $h->vars['category_safe_name'] = $h->pageName;
                $h->pageTitle = $h->vars['category_name'];
                $h->subPage = 'category';  // overwrite the current pageName which is the category name
                $h->pageType = 'list';
            }
        }
    }
    
    
    /**
     * Include CSS and JavaScript files for this plugin
     */
    public function header_include($h)
    {
        $categories_settings = $h->getSerializedSettings();
        $h->vars['categories_settings_nav_style'] = isset($categories_settings['categories_nav_style']) ? $categories_settings['categories_nav_style'] : 'style1';
        $h->vars['categories_settings_nav_show'] = isset($categories_settings['categories_nav_show']) ? $categories_settings['categories_nav_show'] : 'checked';
        
        if ($h->vars['categories_settings_nav_style'] == 'style1') { 
            $h->includeJs('categories', 'suckerfish');            
        }
        
        $h->includeCss();      // include a files that match the name of the plugin folder:
    }
    
    
    
    /**
     * Checks if url query string is /category_name/post_name/
     *
     * @return bool
     *
     * Only used for friendly urls. This is necessary because if a url 
     * is /people/top-10-longest-beards/ there's no actual mention of "category" there!
     */
    public function pagehandling_getpagename($h)
    {
        // Can't get keys from the url with Inspekt, so must get the whole query string instead.
        $query_string = $h->cage->server->sanitizeTags('QUERY_STRING');

        // no query string? exit...
        if (!$query_string) { return false; }
        
        // we actually only need the first pair, so won't bother looping.
        $query_string = preg_replace('/&amp;/', '&', $query_string);
        $pairs = explode('&', $query_string); 
        
        // no pairs or equal sign? exit...
        if (!$pairs[0] || !strpos($pairs[0], '=')) { return false; }
        
        list($key, $value) = explode('=', $pairs[0]);
        
        // no key or no value? exit...
        if (!$key || !$value) { return false; }

        $exists = $h->getCatId(urlencode($key));
        
        // no category? exit...
        if (!$exists) { return false; }
        
        // Now we know that $key is a category so $value must be the post name. Go get the post_id...
        $h->post->id = $h->post->isPostUrl($h, $value);
        
        // no post? exit...
        if (!$h->post->id) { return false; }
        
        $h->post->readPost($h, $h->post->id);
        $h->pageName = $h->post->url; // slug for page title
        $h->pageTitle = $h->post->title;
        $h->pageType = 'post';
        return true;
    }
    
    
    /**
     * Also changes meta when browsing a category page
     * Since we have already loaded data in theme_index_top we can use vars here
     */
    public function header_meta($h)
    {    
        if ($h->subPage == 'category') { 
            if (isset($h->vars['category_desc'])) {
                echo '<meta name="description" content="' . urldecode($h->vars['category_desc']) . '" />' . "\n";
            } else {
                echo '<meta name="description" content="' . $h->lang('header_meta_description') . '" />' . "\n";  // default meta tags
            }
            
            if (isset($h->vars['category_keywords'])) {
                echo '<meta name="keywords" content="' . urldecode($h->vars['category_keywords']) . '" />' . "\n";
            } else {
                echo '<meta name="description" content="' . $h->lang('header_meta_keywords') . '" />' . "\n";  // default meta tags
            }

            return true;
        }
    }
    
    
    /**
     * Read category settings
     */
    public function post_read_post_1()
    {
        //categories
        $h->post->vars['useCategories'] = $this->getSetting('submit_categories') == 'checked' && $this->isActive() ? true : false; 
    }

     /* ******************************************************************** 
     * ********************************************************************* 
     * ******************* FUNCTIONS FOR SHOWING POSTS ********************* 
     * *********************************************************************
     * ****************************************************************** */
    
    /**
     * Gets a category from the url and sets the filter for get_posts
     *
     * @return bool
     */
    public function bookmarking_functions_preparelist($h)
    {
        if ($h->subPage == 'category') {
            // When a user clicks a parent category, we need to show posts from all child categories, too.
            // This only works for one level of sub-categories.
            $filter_string = '(post_category = %d';
            $values = array($h->vars['category_id']);
            $parent = $h->getCatParent($h->vars['category_id']);
            
            if ($parent == 1) {
                $children = $h->getCatChildren($h->vars['category_id']);
                if ($children) {
                    foreach ($children as $child_id) {
                        $filter_string .= ' || post_category = %d';
                        array_push($values, $child_id->category_id); 
                    }
                }
            }
            
            $filter_string .= ')';
            $h->vars['filter'][$filter_string] = $values; 
            $h->vars['filter']['post_archived = %s'] = 'N'; // don't include archived posts
        }
    }
    
    
    /**
     * Shows categories before post title in breadcrumbs
     */
    public function breadcrumbs($h)
    { 
        $crumbs = '';
                
        if ($h->subPage == 'category' && isset($h->vars['category_parent'])) {
            // the pageType is "list"
            $parent_id = $h->vars['category_parent'];
            
            if ($parent_id > 1) {
                $parent_name = $h->getCatName($parent_id);
                $parent_name = stripslashes(htmlentities($parent_name, ENT_QUOTES, 'UTF-8'));
                $crumbs .= "<a href='" . $h->url(array('category'=>$parent_id)) . "'>";
                $crumbs .= $parent_name . "</a> / \n";
            }
    
            $crumbs .= "<a href='" . $h->url(array('category'=>$h->vars['category_id'])) . "'>\n";
            $crumbs .= $h->vars['category_name'] . "</a>\n ";
            $crumbs .= $h->rssBreadcrumbsLink('', array('category'=>$h->vars['category_id']));
        } elseif ($h->pageType == 'post') {
            // the pageName is the post slug (post_url)
            $parent_id = isset($h->vars['category_parent']) ? $h->vars['category_parent'] : $h->getCatParent($h->post->category);
            
            if ($parent_id > 1 && $h->post->category) {
                $parent_name = $h->getCatName($parent_id);
                $parent_name = stripslashes(htmlentities($parent_name, ENT_QUOTES, 'UTF-8'));
                $crumbs .= "<a href='" . $h->url(array('category'=>$parent_id)) . "'>";
                $crumbs .= $parent_name . "</a> &raquo; \n";
            }
    
            if ($h->post->category) {
                $crumbs .= "<a href='" . $h->url(array('category'=>$h->post->category)) . "'>\n";
                $crumbs .= $h->getCatName($h->post->category) . "</a> &raquo; \n";
            }
            $origTitle = $h->post->title;
            $shortTitle = $h->lang('bookmarking_breadcrumb_this_post');
            $crumbs .= "<a href='" . $h->url(array('page'=>$h->post->id)) . "' title='" . $origTitle . "' >" . $shortTitle . "</a>\n";
        }
        
        if ($crumbs) { return $crumbs; } else { return false; }
    }
    
    
    /**
     * Shows category in each post if selected separately
     */
    public function categories_post_show($h)
    {
        if ($h->post->category && $h->post->category != 1) {
            $this->set_category_into_post($h);
            // We are adding this multiple times so we need to use the false flag
            $h->template('category_post_show', 'categories', false);
        }   
    }
    
    
    /**
     * Shows category in each post as part of show_post_author_date hook
     */
    public function show_post_author_date($h)
    { 
        if ($h->post->category && $h->post->category != 1) {
            $this->set_category_into_post($h);
            // We are adding this multiple times so we need to use the false flag
            $h->template('category_post_author_date', 'categories', false);
        }        
    }
    
    /**
     * Shows category in each post as part of show_post_author_date hook
     */
    private function set_category_into_post($h)
    { 
        if ($h->post->category != 1) { 

            // Since old versions dont have joins on their queries they cant pull in categoryname in the readPost
            // We make it here instead
            if ($h->version <= '1.6.6') {
                $cat_name = $h->getCatName($h->post->category);
                $cat_name = htmlentities($cat_name, ENT_QUOTES,'UTF-8');
                $h->post->categoryName = $cat_name;
            }
            
            return true;
        }   
        
        return false;
    }


     /* ******************************************************************** 
     * ********************************************************************* 
     * ************************* EXTRA FUNCTIONS *************************** 
     * *********************************************************************
     * ****************************************************************** */


    /**
     * Category Bar - categories nav bar
     *
     */
    public function header_end($h)
    {
        if ($h->vars['categories_settings_nav_show'] == 'checked') {
            
            if (!isset($h->categoriesDisplay)) {
            
                $categories = $h->getCatFullData();

                // set the initial level Id as 1 for the top - as long as that never changes to be ALL
                // TODO
                // newly installed category plugin should always have cat1 = all but could it get changed for some reason?
                // should we look up 'all' and return its id to be safe?
                $topLevelId = 1;
                $parentCats = array();

                // if there are no categories set up yet (watch for the default all category in db as well)
                if (!$categories || count($categories) == 1) { echo '<br/>'; return false; }

                // loop through the results and populate an array with the current top cats
                foreach ($categories as $category) {
                        if (strtolower($category->category_id) != 1) {
                            $parentCats['p_' . $category->category_parent][] = $category;                                    
                        }
                }

                // TODO
                // If we are caching the db query, then why not also cache off this foreach loop result and save the processing power ?        
                $h->categoriesDisplay = $this->loopCats($h, $parentCats, $topLevelId, '');
            }
            
            // only required for older themes
            $h->vars['output'] = $this->loopCats($h, $parentCats, $topLevelId, '');
            
            if ($h->vars['categories_settings_nav_style'] == 'style2') {
                $h->template('category_bar_2');
            } else {
                $h->template('category_bar');
            }
        } else {
                echo '<br/>';
        }
    }
    
    function loopCats($h, $parentCats, $topLevelId, $output = '')
    {
        if (!$parentCats) {
            return $output;
        }
        
        $thisLevel =  $parentCats['p_' . $topLevelId];

        if (!$thisLevel) {
            return false;
        }
        
        // loop through based on the top level and populate menus below it                        
        foreach ($thisLevel as $category) {

            if (isset($parentCats['p_' . $category->category_id])) $children = count($parentCats['p_' . $category->category_id]); else $children = 0;
            
            // echo li with this function
            $output .= $this->categoryLink($h, $category, '', $children); 

            // call function to loop back on this with $parentCats['p_' . $category->category_id]
            if ($children > 0) {
                if ($h->vars['categories_settings_nav_style'] == 'style2') $output .= "<ul class='children dropdown-menu'>"; else $output .= "<ul class='children'>";
                $output .= $this->loopCats($h, $parentCats, $category->category_id);
                $output .= "</ul>";
            }
            $output .= "</li>";
        }
        return $output;
    }
    
    function loopCatsMega($h, $parentCats, $topLevelId, $output = '') {

        if (!$parentCats) {
            return $output;
        }
        
        $thisLevel =  $parentCats['p_' . $topLevelId];

        if (!$thisLevel) {
            return false;
        }
        
        // loop through based on the top level and populate menus below it                        
        foreach ($thisLevel as $category) {

            if (isset($parentCats['p_' . $category->category_id])) $children = count($parentCats['p_' . $category->category_id]); else $children = 0;
            
            // echo li with this function
            $output .= '<li class="col-sm-3"> <ul> <li class="dropdown-header">';
            $output .= $category->category_name . '</li>';                                   

            if ($children) {
                // only go 1 deep for this menu
                foreach ($parentCats['p_' . $category->category_id] as $child) {
                    $output .= '<li><a href="#">';
                    $output .= $child->category_name;
                    $output .= "</a></li>";
                }
            }
            $output .= "</ul></li>";
            
        }
        return $output;
    }
  


    /** 
     * HTML link for each category 
     * 
     * @param array $category  
     * @param string $output  
     * @return string $output 
     */ 
    public function categoryLink($h, $category, $output, $children = 0) 
    { 
        if (FRIENDLY_URLS == "true") {  
            $link = $category->category_safe_name;  
        } else { 
            $link = $category->category_id; 
        }
        
        $active = '';

	// give active status to highest parent tab 
        if (isset($h->vars['category_id'])) {
            // is this already a parent catgeory? Make the tab active:
            if (($h->vars['category_id'] == $category->category_id) && ($category->category_parent == 1)) {
                $active = " class='active_cat active'";
            } elseif (isset($h->vars['category_parent']) &&($h->vars['category_parent'] == $category->category_id)) {
                // is this a child category? If so, make the parent tab active:
                $active = " class='active_cat active'";
            }
        }
        
        $category_name = stripslashes(urldecode($category->category_name));
        $category_name = htmlentities($category_name, ENT_QUOTES,'UTF-8');
        
        if ($children && $h->vars['categories_settings_nav_style'] == 'style2') {            
            //$output .= '<li class="divider-vertical"></li>';
            $output .= '<li class="dropdown">';
            $output .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown">' . $category_name . ' <b class="caret"></b></a>'; 
        } else {                            
            $output .= '<li' . $active . '><a href="' . $h->url(array('category'=>$link)) .'">' . $category_name . "</a>\n";
        }
        
        return $output; 
    } 


    /**
     * If a category feed, set it up
     */
    public function post_rss_feed($h)
    {
        $category = $h->cage->get->noTags('category');
        
        if (!$category) { return false; }
        
        if (FRIENDLY_URLS == "true") { $cat_id = $h->getCatId($category); }
        if (FRIENDLY_URLS == "false") { $cat_id = $category; }
        
        if (!$cat_id) { return false; }

        // When a user clicks a parent category, we need to show posts from all child categories, too.
        // This only works for one level of sub-categories.

        $filter_string = '(post_category = %d';
        $values = array($cat_id);
        $parent = $h->getCatParent($cat_id);
        
        if ($parent == 1) {
            $children = $h->getCatChildren($cat_id);
            if ($children) {
                foreach ($children as $child_id) {
                    $filter_string .= ' || post_category = %d';
                    array_push($values, $child_id->category_id); 
                }
            }
        }
        
        $filter_string .= ')';
        $h->vars['postRssFilter'][$filter_string] = $values; 

        $category = str_replace('_', ' ', stripslashes(html_entity_decode($cat_id, ENT_QUOTES,'UTF-8'))); 
        $h->vars['postRssFeed']['description'] = $h->lang("post_rss_in_category") . " " . $h->getCatName($cat_id); 
    }
}
