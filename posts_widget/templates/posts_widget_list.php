<?php
/**
 * Comment Widget - show list of comments
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

$posts = $h->vars['posts'];
$type = $h->vars['posts_type'];
$title = isset($h->vars['posts_widget_title']) ? $h->vars['posts_widget_title'] : '';
$link = isset($h->vars['posts_widget_link']) ? $h->vars['posts_widget_link'] : '';
$votes = $h->vars['widget_votes'];
$showImages = $h->vars['posts_widgets_showImages'];

?>
<!-- TITLE -->
<h4 class='widget_head posts_widget_title'>
    <a href='<?php echo $link; ?>' title='<?php echo $h->lang("posts_widget_title_anchor_title"); ?>'>
        <?php echo $title; ?>
    </a>

    <?php if ($type == 'top' || $type == 'new' || $type == 'upcoming') { ?>
        <a href='<?php echo $h->url(array('page'=>'rss', 'status'=>$type)); ?>' title='<?php echo $h->lang("posts_widget_icon_anchor_title"); ?>'>
            <i class="fa fa-rss"></i>
        </a>
    <?php } ?>
</h4> 
            
<!-- LIST -->
<ul class='widget_body posts_widget_items'>
    <?php foreach ($posts as $post) { 
            $h->post->url = $post->post_url; // used in Hotaru's url function
            $h->post->category = $post->post_category; // used in Hotaru's url function    
    ?>
    <li class='posts_widget_item'>
    <?php if ($votes == 'checked') { ?>
        
            <div class='posts_widget_vote vote_color_<?php echo $post->post_status; ?>'>
            <?php echo $post->post_votes_up; ?>
            </div>
        
    <?php } ?>        
        
    <?php if ($votes == 'checked') { ?>  
        <div class='posts_widget_link posts_widget_indent'>
    <?php } else { ?>
        <div class='posts_widget_link'>
    <?php } ?>

        <?php if ($showImages) { ?>
            <?php if ($post->imageType == 'thumb') { ?>
                <div class="posts_widget_image">                    
                    <a href='<?php echo $h->url(array('page'=>$post->post_id)); ?>'>
                        <img src='<?php echo BASEURL . "content/images/post_images/" . $post->post_img; ?>' alt='<?php echo $post->post_title; ?>' />
                    </a>
                </div>
            <?php } elseif($post->imageType == 'dummy') { ?>
                <div class="posts_widget_image">
                    <img src='<?php echo $post->post_img; ?>' />
                </div>
            <?php } ?>
        <?php } ?>
                    
            <a href='<?php echo $h->url(array('page'=>$post->post_id)); ?>' title='<?php echo urldecode($post->post_domain); ?>'>
                <?php echo $post->post_title; ?>
            </a>
        </div>
    </li>
    <?php } ?>
</ul>
