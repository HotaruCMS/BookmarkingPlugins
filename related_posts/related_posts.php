<?php
/**
* name: Related Posts
* description: Show a list of related posts
* version: 1.4
* folder: related_posts
* class: relatedPosts
* hooks: install_plugin, theme_index_top, header_include, submit_settings_get_values, submit_settings_form2, submit_save_settings, submit_step3_pre_buttons, submit_step3_post_buttons, show_post_middle, admin_plugin_settings
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
	
        public function theme_index_top($h)
        {
            $h->vars['related_posts_settings'] = $h->getSerializedSettings('related_posts');        
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
                $h->template('related_posts_form2');
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
                
		if ($count > 5) {
                    $tags = array_slice($tags, 0, 5);
                } // restrict to first 5 tags only
		
                foreach ($tags as $key => $value) {
                    $tags[$key] = trim($value); 
                } // trim whitespace from each tag
                
		$tags = implode(' ', $tags);

		// abort of no tags for this post
		if (!$tags) { 
                    $this->noRelatedPosts($h);
                    return true;                     
                }
                
		// get the results and generate HTML:
		$this->showRelatedPosts($h, $tags, $num_posts);
		
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
		$results = $this->getRelatedPosts($h, $search_terms, $num_posts);
		if (!$results) {
                    // Show "No other posts found with matching tags"
                    return $this->noRelatedPosts($h);
		} 
                
                $h->vars['related_posts_results'] = $results;
                $h->template('related_posts_list');
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
			$h->template('related_posts_none');
		}
		
		return true;
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
                if (!isset($h->vars['select'])) { return false; }
            
		$h->vars['filter']['post_archived != %s'] = 'Y';
		$h->vars['filter']['post_id != %d'] = $h->post->id;
		$h->vars['filter']['post_type = %s'] = 'news';
                if ($h->version > '1.6.6') {
                    $prepared_search = $h->prepareSearchFilter($h, $search_terms);
                } else {
                    if (!$h->isActive('search')) { return false; }
                    require_once(PLUGINS . 'search/search.php');
                    $search = new Search();                                    
                    $prepared_search = $search->prepareSearchFilter($h, $search_terms);
                }
		extract($prepared_search);

		$prepared_filter = $h->db->select($h, array($h->vars['select']), 'posts', $h->vars['filter'], $h->vars['orderby'], $num_posts, false, true);

		$results = $h->db->getData($h, 'posts', $prepared_filter);
		return $results;
	}

}
?>