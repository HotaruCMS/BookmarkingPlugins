<?php
/**
 * All comments
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
    
<div id='activity'>
    <ul class='activity_items'>
        <?php 
        //print_r($h->comment);
            $user_id = $h->comment->author;
            $username = $h->comment->authorname;
        ?>

        <li class="info-box gray">
        <div class="avatarBox">

            <?php if($h->comment->avatars == 'checked' && $h->isActive('avatar')) { ?>
                        <div class='avatar_small'>
                            <?php $h->setAvatar($h->comment->author, 32, 'g', 'img-circle'); echo $h->linkAvatar(); ?>
                        </div>
                    <?php } ?>

        </div>
        <div class="info ">
            <div class="name row">
                <div class="col-sm-10">
                    <strong class=""><?php echo ucfirst($username); ?></strong> <?php echo nl2br($h->comment->content); ?>                        
                    <div>in <a href="<?php echo $h->getPostUrlForCurrentComment(); ?>">
                          <?php echo $h->comment->postTitle; ?>
                          </a>
                      </div>
                </div>
                <div class="col-sm-2">
                    <div class='pull-right'>
                    <div class="time"><i class="icon-time"></i> <?php echo time_ago($h->comment->date); ?></div>
                        <div>
                             <?php   // Show votes if enabled (requires a comment voting plugin)
                                    if ($h->comment->voting == 'checked') {
                                        $h->pluginHook('show_comments_votes'); 
                                    }
                            ?>
                        </div>
                    </div>
                </div>

              </div>
        </div>
      </li>

    </ul>
</div>
     
 