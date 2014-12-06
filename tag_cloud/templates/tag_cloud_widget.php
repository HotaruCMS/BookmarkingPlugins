<?php
/**
 * Tag Cloud - Widget
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


<?php

$cloud = $h->vars['cloud'];
$show_title = $h->vars['show_title'];

if ($show_title) { ?>

    <h4 class='widget_head widget_tag_cloud_title'>
        <a href='<?php echo $h->url(array('page' => 'tag-cloud')); ?>'>
            <?php echo $h->lang("tag_cloud_widget_title"); ?>
        </a>
    </h4>

<?php } ?>
        
    <div class='widget_body widget_tag_cloud'>
        <?php foreach ($cloud as $tag) { ?>
            <a href='<?php echo $h->url(array('tag' => $tag['link_word'])); ?>' class='widget_tag_group<?php echo $tag['class']; ?>'>
                <?php echo $tag["show_word"]; ?>
            </a>
        <?php } ?>
            <a href='<?php echo $h->url(array('page' => 'tag-cloud')); ?>' class='widget_more_tags'>
                    <?php echo $h->lang("tag_cloud_widget_more"); ?>
            </a>
    </div>
