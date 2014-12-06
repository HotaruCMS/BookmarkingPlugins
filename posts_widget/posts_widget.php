<?php
/**
 * name: Posts Widget
 * description: Adds links in widgets to the latest posts and top stories on the site.
 * version: 1.9.2
 * folder: posts_widget
 * class: PostsWidget
 * requires: widgets 0.6, bookmarking 0.1
 * hooks: install_plugin, admin_sidebar_plugin_settings, admin_plugin_settings, hotaru_header, header_include, footer
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
 
class PostsWidget
{
    /**
     *  Add default settings for Posts Widget plugin on installation
     */
    public function install_plugin($h)
    {
        // Plugin settings
        $pw_settings = $h->getSerializedSettings();
        if (!isset($pw_settings['items'])) { $pw_settings['items'] = 10; }
        if (!isset($pw_settings['length'])) { $pw_settings['length'] = 0; }

		// for adding or removing from the Widgets page:
		$widgets['posts_widget_top'] = 'checked';
		$widgets['posts_widget_latest'] = 'checked';
		$widgets['posts_widget_upcoming'] = 'checked';
		$widgets['posts_widget_day'] = 'checked';
		$widgets['posts_widget_week'] = 'checked';
		$widgets['posts_widget_month'] = 'checked';
		$widgets['posts_widget_year'] = 'checked';
		$widgets['posts_widget_all-time'] = 'checked';

        if (!isset($pw_settings['widgets'])) { $pw_settings['widgets'] = $widgets; }
        
        $h->updateSetting('posts_widget_settings', serialize($pw_settings));

        // Widgets:
        // plugin name, function name, optional arguments
        $h->addWidget('posts_widget', 'posts_widget_top', 'top');
        $h->addWidget('posts_widget', 'posts_widget_latest', 'new');
        $h->addWidget('posts_widget', 'posts_widget_upcoming', 'upcoming');
        $h->addWidget('posts_widget', 'posts_widget_day', 'top-24-hours');
        $h->addWidget('posts_widget', 'posts_widget_week', 'top-7-days');
        $h->addWidget('posts_widget', 'posts_widget_month', 'top-30-days');
        $h->addWidget('posts_widget', 'posts_widget_year', 'top-365-days');
        $h->addWidget('posts_widget', 'posts_widget_all-time', 'top-all-time');
    }
    
    
    /**
     * Display the top or latest posts in the sidebar
     *
     * @param $type either 'top' or 'new', matching the post_status in the db.
     */
    public function widget_posts_widget($h, $type = 'top')
    {
        $this->postsWidgetDefault($h, $type);
        
        // test for ajax loading
        // js in footer hook functin below
        if (1==0) {            
            $output = "<div id='widget_posts_latest'></div>";
            echo $output;
        }
    }
    
    
    /**
     * Display the default sidebar box
     *
     * @param $type either 'top' or 'new', matching the post_status in the db.
     */
    public function postsWidgetDefault($h, $type)
    {
        $posts = $this->getPostsWidget($h, $type, false);
        $h->vars['posts_widget_title'] = $this->getWidgetTitle($h, $type);
        
        if (isset($posts) && !empty($posts)) {
                        
            switch ($type) {
                case 'top':
                    $link = $h->url(array('page'=>'popular'));
                    break;
                case 'new':
                    $link = $h->url(array('page'=>'latest'));
                    break;
                case 'upcoming':
                    $link = $h->url(array('page'=>'upcoming'));
                    break;
                case 'top-24-hours':
                    $link = $h->url(array('sort'=>'top-24-hours'));
                    break;
                case 'top-7-days':
                    $link = $h->url(array('sort'=>'top-7-days'));
                    break;
                case 'top-30-days':
                    $link = $h->url(array('sort'=>'top-30-days'));
                    break;
                case 'top-365-days':
                    $link = $h->url(array('sort'=>'top-365-days'));
                    break;
                case 'top-all-time':
                    $link = $h->url(array('sort'=>'top-all-time'));
                    break;
                default:
                    $link = BASEURL;
            }
            
            $h->vars['posts_widget_link'] = $link;
            $this->getPostsWidgetItems($h, $posts, $type);
        }
        
    }

    
    /**
     * Get sidebar title
     *
     * @param $type either 'top' or 'new', matching the post_status in the db.
     * return array $posts
     */
    public function getWidgetTitle($h, $type)
    {
        // FILTER TO NEW POSTS OR TOP POSTS?
        if ($type == 'new' && $h->pageName != 'latest') { 
            $title = $h->lang['posts_widget_latest_posts'];
        } elseif ($type == 'top' && $h->pageName != 'popular') {
            $title = $h->lang['posts_widget_top_posts'];
        } elseif ($type == 'upcoming' && $h->pageName != 'upcoming') {
            $title = $h->lang['posts_widget_upcoming_posts'];
        } else {
            switch($type) {
                case 'top-24-hours':
                    $title = $h->lang['posts_widget_top_24_hours'];
                    break;
                case 'top-7-days':
                    $title = $h->lang['posts_widget_top_7_days'];
                    break;
                case 'top-30-days':
                    $title = $h->lang['posts_widget_top_30_days'];
                    break;
                case 'top-365-days':
                    $title = $h->lang['posts_widget_top_365_days'];
                    break;
                case 'top-all-time':
                    $title = $h->lang['posts_widget_top_all_time'];
                    break;
                default:
                    $title = "No title?";
            }
        }
        return $title;
    }
    

    /**
     * Get widget posts
     *
     * @param $type either 'top' or 'new', matching the post_status in the db.
     * return array $posts
     */
    public function getPostsWidget($h, $type, $custom = true, $limit = 0)
    {
        if (!$limit) { 
            $pw_settings = $h->getSerializedSettings('posts_widget', 'posts_widget_settings');
            $limit = (isset($pw_settings['items'])) ? $pw_settings['items'] : 10; 
        }
 
        $h->vars['limit'] = $limit;
        $posts = '';
        
        // include bookmarking_functions class:
        require_once(PLUGINS . 'bookmarking/libs/BookmarkingFunctions.php');
        $funcs = new BookmarkingFunctions();
        
        if (!$custom) {
            // Show latest on front page, top stories on latest page, or both otherwise
            if ($type == 'new' && $h->pageName != 'latest') {  
                $posts = $funcs->prepareList($h, 'new');
            } elseif ($type == 'top' && $h->pageName != 'popular') {
                $posts = $funcs->prepareList($h, 'top');
            } elseif ($type == 'upcoming' && $h->pageName != 'upcoming') {
                $posts = $funcs->prepareList($h, 'upcoming');
            }
        } else {
            // Return posts regardless of what page we're viewing
            if ($type == 'new') { 
                $posts = $funcs->prepareList($h, 'new');    // get latest stories
            } elseif ($type == 'top') {
                $posts = $funcs->prepareList($h, 'top');    // get top stories
            } elseif ($type == 'upcoming') {
                $posts = $funcs->prepareList($h, 'upcoming');    // get upcoming stories
            }
        }
        
        if ($type == 'all') {
            $posts = $funcs->prepareList($h, 'all');    // get all stories
        } elseif ($type == 'top-24-hours') {
            $posts = $funcs->prepareList($h, 'top-24-hours');    // get top stories from last 24 hours
        } elseif ($type == 'top-48-hours') {
            $posts = $funcs->prepareList($h, 'top-48-hours');    // get top stories from last 48 hours
        } elseif ($type == 'top-7-days') {
            $posts = $funcs->prepareList($h, 'top-7-days');    // get top stories from last 7 days
        } elseif ($type == 'top-30-days') {
            $posts = $funcs->prepareList($h, 'top-30-days');    // get top stories from last 30 days
        } elseif ($type == 'top-365-days') {
            $posts = $funcs->prepareList($h, 'top-365-days');    // get top stories from last 365 days
        } elseif ($type == 'top-all-time') {
            $posts = $funcs->prepareList($h, 'top-all-time');    // get top stories from all time
        }

        if ($posts) { return $posts; } else { return false; }
    }
    
    
    /**
     * Get post widget items
     *
     * @param array $posts 
     * return string $ouput
     */
    public function getPostsWidgetItems($h, $posts = array(), $type = 'new')
    {
        if (!$posts) { return false; }
        
//        $need_cache = false;
//        
//        // check for a cached version and use it if no recent update:
//        $output = $h->smartCache('html', 'posts', 10, '', $type);
//        if ($output) {
//            return $output;
//        } else {
//            $need_cache = true;
//        }

        // get max post title length
        $pw_settings = $h->getSerializedSettings('posts_widget', 'posts_widget_settings');
        $length = (isset($pw_settings['length'])) ? $pw_settings['length'] : 0; 
        
        $post_images_settings = $h->getSerializedSettings("post_images");
        $h->vars['posts_widgets_showImages'] = ($h->isActive('post_images') && $post_images_settings['show_in_posts_widget'] == 'checked') ? true : false;
          
        // determine if we should show vote counts before titles...
        $vote_settings = $h->getSerializedSettings('vote', 'vote_settings');
        $h->vars['widget_votes'] = $vote_settings['posts_widget'];
        
        $h->vars['posts'] = array();
                
        foreach ($posts as $item) {
            
            $h->post->url = $item->post_url; // used in Hotaru's url function
            $h->post->category = $item->post_category; // used in Hotaru's url function
            
            $item->post_title = stripslashes(html_entity_decode(urldecode($item->post_title), ENT_QUOTES,'UTF-8'));
            
            if ($length) {
                $item->post_title = truncate($item->post_title, $length);
            }
            
            // Display images from post images plugin 
            $item->imageType = 'none';
            if ($h->vars['posts_widgets_showImages'] && isset($item->post_img) && strlen($item->post_img) > 0 ) {                    
                $item->imageType = substr($item->post_img,0,32) != 'http://images.sitethumbshot.com/' ? 'thumb' : 'dummy';
            }
            
            // make sure also works for older versions of Hotaru
            if ($h->version < 1.7) {
                $item->urlLink = $h->url(array('page'=>$item->post_id));
            } else {
                $item->urlLink = $h->url(array('postUrl'=>$item->post_url, 'postId' => $item->post_id));
            }
            
            $h->vars['posts'][] = $item;
        }
                
        $h->vars['posts_type'] = $type;
        $h->template('posts_widget_list', 'posts_widget', false);
        
//      if ($need_cache) {
//            $h->smartCache('html', 'posts', 10, $output, $type); // make or rewrite the cache file
//      }
        
        return true;
    }
    
    public function footer($h) {
        // test for ajax loading
        if (1==0) {
            ?>
            <script type='text/javascript'>
                jQuery(window).load(function() {        

                var sendurl = SITEURL + "index.php?page=api";
                var formdata = 'method=hotaru.posts.getLatest&format=json';

                $.ajax(
                    {
                    type: 'post',
                            url: sendurl,
                            cache: false,
                            data: formdata,
                            beforeSend: function () {
                                            //$('#adminNews').html('<img src="' + SITEURL + "content/admin_themes/" + ADMIN_THEME + 'images/ajax-loader.gif' + '"/>&nbsp;Loading latest news.<br/>');
                                    },
                            error: 	function(XMLHttpRequest, textStatus, errorThrown) {
                                            $('#widget_posts_latest').html('ERROR');                                    
                            },
                            success: function(data) { // success means it returned some form of json code to us. may be code with custom error msg                                                                               
                                            $('#widget_posts_latest').html(data).fadeIn("fast");
                                            //$('#hotaruImg').fadeOut("slow");

                            },
                            dataType: "html"
                    });
                });
            </script>

            <?php
        }
    }
}
?>