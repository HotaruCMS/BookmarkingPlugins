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

?>
 
<h4 class='widget_head comments_widget_title'>
    <a href='<?php echo $h->url(array('page'=>'comments')); ?>' title='<?php echo $h->comments_widget_anchor_title; ?>'>
        <?php echo $h->lang('comments_widget_title'); ?>
    </a>
    <a href='<?php echo $h->url(array('page'=>'rss_comments')); ?>' title='<?php echo $h->comments_widget_anchor_title; ?>'>
        <i class="fa fa-rss"></i>
    </a>
</h4>
                
<ul class='widget_body comments_widget_items'>
    <?php foreach ($h->vars['comments'] as $comment) { ?>
    
    <li class='comments_widget_item'>
        <?php
        if($h->isActive('avatar') && $h->vars['comments_widget_settings']['avatar']) {
                $h->setAvatar($comment->comment_user_id, $h->vars['comments_widget_settings']['avatar_size'], 'g', $comment->user_username, $comment->user_email );  ?>
                
                <div class='comments_widget_avatar'>
                    <?php echo $h->linkAvatar(); ?>
                </div>
        <?php } ?>
            
        <?php
        if ($h->vars['comments_widget_settings']['author']) { ?>
            <a class='comments_widget_author' href='<?php echo $h->url(array('user' => $comment->username)); ?>'>
               <?php echo $comment->username; ?>
            </a>
        <?php } ?>
        
        <div class='comments_widget_content'>
           <a href='<?php echo $comment->comment_link; ?>' title='<?php echo $comment->comment_tooltip; ?>'>
               <?php echo $comment->item_content; ?>
           </a>
        </div>
    </li>   
    <?php } ?>
</ul>
