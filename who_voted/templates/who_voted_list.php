<?php
/**
 * Template for Plugin WhoVoted - show list
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

$who_voted_settings = $h->vars['who_voted_settings'];
$limit = $who_voted_settings['who_voted_num'];
$avatars = $who_voted_settings['who_voted_avatars'];
$avatar_size = $who_voted_settings['who_voted_avatar_size'];
$avatar_shape = isset($who_voted_settings['who_voted_avatar_shape']) ? $who_voted_settings['who_voted_avatar_shape'] : '';
$names = $who_voted_settings['who_voted_names'];
$show_title = $who_voted_settings['who_voted_widget_title'];
        
$results = $h->vars['who_voted_results'];
?>

<div id='who_voted'>
    <?php 
        if ($show_title) { ?>
            <h3 id='who_voted_title'><?php echo $h->lang['who_voted']; ?></h3>
        <?php } ?>
        
    <div id='who_voted_content'>
        <?php
            foreach ($results as $item) {
                $h->setAvatar($item->user_id, $avatar_size, 'g', 'img-' . $avatar_shape);
                if ($avatars) {
                    echo $h->linkAvatar(); 
                }
                if ($names) { ?>
                    <a href='<?php $h->url(array('user' => $item->user_username)); ?>'>
                       <?php echo $item->user_username; ?>
                    </a>
            <?php }
            } ?>
    </div>
  </div>
