<?php
/**
 * Bookmarking Flags
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

//TODO 
// this should only be once on the page and then moved to the right place when div is clicked for it
// the postid would get passed in based on the click from js
// move this all to bookmarking or a plugin of its own

// note $id gets replaced by js values later
$id = $h->post->id ? $h->post->id : '#';

?>

<li class='alert_choices' style='display:none;'>
    <ul>
        <li><a class='btn btn-xs btn-primary' rel='nofollow' href='<?php echo $h->url(array('page'=>$id, 'alert'=>3)); ?>'><i class="fa fa-flag"></i>&nbsp;<?php echo $h->lang["bookmarking_alert_reason_3"]; ?></a></li>
        <li><a class='btn btn-xs btn-primary' rel='nofollow' href='<?php echo $h->url(array('page'=>$id, 'alert'=>4)); ?>'><i class="fa fa-flag"></i>&nbsp;<?php echo $h->lang["bookmarking_alert_reason_4"]; ?></a></li>
        <li><a class='btn btn-xs btn-primary' rel='nofollow' href='<?php echo $h->url(array('page'=>$id, 'alert'=>5)); ?>'><i class="fa fa-flag"></i>&nbsp;<?php echo $h->lang["bookmarking_alert_reason_5"]; ?></a></li>
        <li><a class='btn btn-xs btn-default' rel='nofollow' href='<?php echo $h->url(array('page'=>$id, 'alert'=>6)); ?>'><i class="fa fa-flag"></i>&nbsp;<?php echo $h->lang["bookmarking_alert_reason_6"]; ?></a></li>
        <li><a class='btn btn-xs btn-warning' rel='nofollow' href='<?php echo $h->url(array('page'=>$id, 'alert'=>2)); ?>'><i class="fa fa-flag"></i>&nbsp;<?php echo $h->lang["bookmarking_alert_reason_2"]; ?></a></li>
        <li><a class='btn btn-xs btn-danger' rel='nofollow' href='<?php echo $h->url(array('page'=>$id, 'alert'=>1)); ?>'><i class="fa fa-flag"></i>&nbsp;<?php echo $h->lang["bookmarking_alert_reason_1"]; ?></a></li>
    
    </ul>
</li>

