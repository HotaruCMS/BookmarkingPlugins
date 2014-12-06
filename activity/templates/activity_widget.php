<?php
/**
 * Template for Activity plugin: activity_widget - for user activity
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
$activity = $h->vars['activity'];

// build link that will link the widget title to all activity...        
$anchor_title = htmlentities($h->lang("activity_title_anchor_title"), ENT_QUOTES, 'UTF-8');
$title = "<a href='" . $h->url(array('page'=>'activity')) . "' title='" . $anchor_title . "'>" .$h->lang('activity_title') . "</a>";
        
if (isset($activity) && !empty($activity)) { ?>
            
    <h4 class='widget_head activity_widget_title'>
        <?php echo $title; ?><a href="<?php echo $h->url(array('page'=>'rss_activity')); ?>" title="<?php echo $anchor_title; ?>">
        <img src="<?php echo BASEURL; ?>content/themes/<?php echo THEME; ?>images/rss_16.png" width="16" height="16" alt="RSS" />\n</a>
    </h4>

    <ul class='widget_body activity_widget_items'>            
        <?php echo $this->getWidgetActivityItems($h, $activity, false); ?>
    </ul>
            
<?php }
