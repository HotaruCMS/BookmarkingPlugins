<?php
/**
* name: Related Posts
* description: Show a list of related posts
* version: 1.2
* folder: related_posts
* class: relatedPosts
* requires: search 0.8
* hooks: install_plugin, header_include, submit_settings_get_values, submit_settings_form2, submit_save_settings, submit_step3_pre_buttons, submit_step3_post_buttons, show_post_middle
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

class relatedPosts
{

	/**
	* Default settings on install
	*/
	public function install_plugin($h)
	{
		// Default settings
		if (!$h->getSetting('submit_related_posts_submit')) { $h->updateSetting('submit_related_posts_submit', 10); }
		if (!$h->getSetting('submit_related_posts_post')) { $h->updateSetting('submit_related_posts_post', 5); }
	}
	
	
	/**
	* Gets current settings from the database
	*/
	public function submit_settings_get_values($h)
	{
		// Get settings from database if they exist... should return 'checked'
		$h->vars['related_posts_submit'] = $h->getSetting('submit_related_posts_submit');
		$h->vars['related_posts_post'] = $h->getSetting('submit_related_posts_post');
		
		// doesn't exist - use default:
		if (!isset($h->vars['related_posts_submit'])) {
				$h->vars['related_posts_submit'] = 10;
		}
		// doesn't exist - use default:
		if (!isset($h->vars['related_posts_post'])) {
				$h->vars['related_posts_post'] = 5;
		}
	
	}
	
	
	/**
	* Add related posts field to the submit settings form
	*/
	public function submit_settings_form2($h)
	{
		echo "<br /><input type='text' size=5 name='related_posts_submit' value='" . $h->vars['related_posts_submit'] . "' /> ";
		echo $h->lang["submit_settings_related_posts_submit"] . "<br />\n";
		echo "<br /><input type='text' size=5 name='related_posts_post' value='" . $h->vars['related_posts_post'] . "' /> ";
		echo $h->lang["submit_settings_related_posts_post"] . "<br />\n";
	}
	
	
	/**
	* Save related posts settings.
	*/
	public function submit_save_settings($h)
	{
		// Related posts on submit page
		if ($h->cage->post->keyExists('related_posts_submit')) { 
				if (is_numeric($h->cage->post->testInt('related_posts_submit'))) {
					$h->vars['related_posts_submit'] = $h->cage->post->testInt('related_posts_submit'); 
				}
		} 
		
		// Related posts on post page
		if ($h->cage->post->keyExists('related_posts_post')) { 
				if (is_numeric($h->cage->post->testInt('related_posts_post'))) {
					$h->vars['related_posts_post'] = $h->cage->post->testInt('related_posts_post'); 
				}
		} 
	
		// if empty or not numeric, the existing value will be saved
				
		$h->updateSetting('submit_related_posts_submit', $h->vars['related_posts_submit']);
		$h->updateSetting('submit_related_posts_post', $h->vars['related_posts_post']);
	
	}
	
	
	/**
	* Show message to check related posts
	*/
	public function submit_step3_pre_buttons($h)
	{
		//echo $h->lang["related_posts_instruct"];
	}
	
	
	/**
	* Show message to check related posts
	*/
	public function submit_step3_post_buttons($h)
	{
		echo "<div class='related_instruct'>";	
		echo $h->lang["related_posts_instruct"];
		echo "</div>";			
		// Get settings from database if they exist... should return 'checked'
		$num_posts = $h->getSetting('submit_related_posts_submit');
		$this->prepareSearchTerms($h, $num_posts);
	}
	
	
	/**
	* Show related posts on a post page
	*/
	public function show_post_middle($h)
	{ 
		if ($h->isPage('submit3')) { return false; }
		
		// Get settings from database if they exist... should return 'checked'
		$num_posts = $h->getSetting('submit_related_posts_post');
		$this->prepareSearchTerms($h, $num_posts);
	}
	
	
	/**
	* prepare search terms
	*
	* NOTE: I originally wanted to include the title and category in 
	* the search terms, but found that using ONLY tags is best because 
	* too many words dilute the target topic and anything with less than 
	* 4 characters returns latest first instead of relevance first Using 
	* the title increases the chance of 3 character words. Nick.
	*/
	public function prepareSearchTerms($h, $num_posts = 10)
	{
		/* when we start reading other posts, we'll lose this original one
			which we need later to show comments and whatnot. */
		$original_id = $h->post->id;
		
		$tags = explode(',', $h->post->tags);
		$count = count($tags);
		if ($count > 5) { $tags = array_slice($tags, 0, 5); } // restrict to first 5 tags only
		foreach ($tags as $key => $value) { $tags[$key] = trim($value); } // trim whitespace from each tag
		$tags = implode(' ', $tags);

		// abort of no tags for this post
		if (!$tags) { echo $this->noRelatedPosts($h); return true; }
		
		$search_terms = $tags;
		
		$need_cache = false;
		
		// check for a cached version and use it if no recent update:
		$output = $h->smartCache('html', 'tags', 60, '', 'related_posts_' . $original_id);
		if ($output) {
				echo $output; // cached HTML
				return true;
		} else {
				$need_cache = true;
		}
		
		// get the results and generate HTML:
		$output = $this->showRelatedPosts($h, $search_terms, $num_posts);
		
		// write them to the cache
		if ($need_cache) {
				$h->smartCache('html', 'tags', 60, $output, 'related_posts_' . $original_id); // make or rewrite the cache file
		}
		
		echo $output;
		
		$h->readPost($original_id); // fill the object with the original post details.
	}
	
	
	/**
	* Show related posts
	*
	* @param int $num_posts - max number of posts to show
	*
	*/
	public function showRelatedPosts($h, $search_terms = '', $num_posts = 10)
	{
		$output = '';
		
		$results = $this->getRelatedPosts($h, $search_terms, $num_posts);
		if (!$results) {
				// Show "No other posts found with matching tags"
				return $this->noRelatedPosts($h);
		} 

		$output = "<div class='related_posts_container'><h3 id='related_posts_title'>" . $h->lang['related_posts'] . "</h3>";

		$output .= "<ul class='related_posts'>\n";
		foreach ($results as $item) {
				$h->readPost(0, $item); // needed for the url function
				$output .= "<li class='related_posts_item'>\n<div class='related_posts_item_block'>";
			$indent = '';
			if ($h->isActive('vote')) {
					if (!isset($item->post_votes_up)) { $item->post_votes_up = '&nbsp;'; }
					$output .= "<span class='related_posts_vote vote_color_" . $item->post_status . "'>";
					$output .= $item->post_votes_up;
					$output .= "</span>\n";
				$indent = "related_posts_indent";
			}
			if($h->isActive('post_images'))
			{
				$h->vars['post_images_settings'] = $h->getSerializedSettings("post_images");
				if($h->vars['post_images_settings']['show_in_related_posts'] == 'checked') 
				{
					if(isset($h->post->vars['img']) && strlen($h->post->vars['img']) > 0 )
						{
							if (substr($h->post->vars['img'],0,32) != 'http://images.sitethumbshot.com/')
								{	
									// We have an image for this post and it is not a site thumbnail so use the image and prepend path to folder
									$output .= "<span class=\"related_posts_image\"><a href=\"" . $h->url(array('page'=>$item->post_id)) . "\"> <img src=\"" . BASEURL . "content/images/post_images/" . $h->post->vars['img'] . "\" alt=\"" . stripslashes(urldecode($item->post_title)) . "\" /></a></span>";
								}
									else 
								{
									// Image is a 3rd party site thumbnail, print it out as it is
									$output .= "<span class=\"related_posts_image\"><img src=\"" . $h->post->vars['img'] . "\" /></span>";
								}
							}
				}
			}
				$output .= "<span class=\"related_posts_link " . $indent . "\">";
				$output .= "<a href='" . $h->url(array('page'=>$item->post_id)) . "' ";
				$output .= "title='" . $h->lang['related_links_new_tab'] . "'>\n";
				$output .= stripslashes(urldecode($item->post_title)); 
				$output .= "</a>";
				$output .= "</span></div>";
				$output .= "</li>\n";
		}
		$output .= "</ul>\n";
		$output .= "</div>\n";		
		$output .= "<div class=\"clear\" ></div>";

		return $output;
	}
    
	/**
	* Message when no related posts found, or no tags present on submit step 3
	*
	* @param string $output
	* return string $output
	*/
	public function noRelatedPosts($h, $output = '')
	{
		if ($h->isPage('submit3')) { 
				$output .= "<div id='related_posts_none'>\n";
				$output .= $h->lang['related_links_no_results'];
				$output .= "</div>\n";
		}
		
		return $output;
	}
	
	/**
	* Get related results from the database
	*
	* @param string $search_terms - space separated string of words
	* @param int $num_posts - the max number of posts to return
	* return array|false
	*/
	public function getRelatedPosts($h, $search_terms = '', $num_posts = 10)
	{
		if (!$h->isActive('search')) { return false; }
		
		require_once(PLUGINS . 'search/search.php');
		$search = new Search();
		$h->vars['filter']['post_archived != %s'] = 'Y';
		$h->vars['filter']['post_id != %d'] = $h->post->id;
		$h->vars['filter']['post_type = %s'] = 'news';
		$prepared_search = $search->prepareSearchFilter($h, $search_terms);
		extract($prepared_search);

		$prepared_filter = $h->db->select($h, array($h->vars['select']), 'posts', $h->vars['filter'], $h->vars['orderby'], $num_posts,false, true);

		$results = $h->db->getData($h, 'posts', $prepared_filter);
		return $results;
	}

}
?>