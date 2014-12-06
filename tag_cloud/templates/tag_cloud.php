<?php
/**
 * Tag Cloud
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
    <?php echo $h->showMessages(); ?>

    <div class="tag_cloud">
    <?php
        foreach ($h->vars['tagCloud'] as $tag) {
          echo "<a href='" . $h->url(array('tag' => $tag['link_word'])) . "' ";
          echo "class='tag_group" . $tag['class'] . "'>" . $tag["show_word"] . "</a>\n";
        }
    ?>
    </div>
