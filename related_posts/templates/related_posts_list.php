<?php
/**
 * Template for Related Posts - show posts
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

$results = $h->vars['related_posts_results'];
?>

<div class='related_posts_container'>
    <h3 id='related_posts_title'><?php echo $h->lang['related_posts']; ?></h3>

    <ul class='related_posts'>
        <?php 
            foreach ($results as $item) {
                $h->readPost(0, $item); // needed for the url function
                ?>
        
        <li class='related_posts_item'>
            <div class='related_posts_item_block'>
                <?php
			$indent = '';
                    if ($h->isActive('vote')) {
                        $indent = "related_posts_indent";    
                        if (!isset($item->post_votes_up)) { $item->post_votes_up = '&nbsp;'; }
                            ?>
                
			<span class='related_posts_vote vote_color_<?php echo $item->post_status; ?>'>
                                <?php echo $item->post_votes_up; ?>
			</span>
				
                <?php } ?>
                
                <?php 
                    if($h->isActive('post_images')) {
                        $h->vars['post_images_settings'] = $h->getSerializedSettings('post_images');
                        if($h->vars['post_images_settings']['show_in_related_posts'] == 'checked') {
                            if(isset($h->post->vars['img']) && strlen($h->post->vars['img']) > 0 ) {
                                if (substr($h->post->vars['img'],0,32) != 'http://images.sitethumbshot.com/') {	
                                    // We have an image for this post and it is not a site thumbnail so use the image and prepend path to folder
                                    echo "<span class=\"related_posts_image\"><a href=\"" . $h->url(array('page'=>$item->post_id)) . "\"> <img src=\"" . BASEURL . "content/images/post_images/" . $h->post->vars['img'] . "\" alt=\"" . stripslashes(urldecode($item->post_title)) . "\" /></a></span>";
                                } else {
                                    // Image is a 3rd party site thumbnail, print it out as it is
                                    echo "<span class=\"related_posts_image\"><img src=\"" . $h->post->vars['img'] . "\" /></span>";
				}
                            }
                        }
                    }
                ?>
                
                
		<span class="related_posts_link <?php echo $indent; ?>">
                    <a href='<?php echo $h->url(array('page'=>$item->post_id)); ?>' title='<?php echo $h->lang['related_links_new_tab']; ?>'>
                        <?php echo stripslashes(urldecode($item->post_title)); ?>
                    </a>
                </span>
            </div>
        </li>
    <?php } ?>
    </ul>
</div>
                
<div class="clear" ></div>

		
