<?php
/**
 * Template for Tags: Tags
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

$raw = $h->vars['tags']['raw'];
$tags = $h->vars['tags']['tags'];
$statusTags = $h->vars['statusTags'];
$displayButtons = $h->vars['tags_settings']['tags_setting_display_buttons'];

foreach ($tags as $tag) {
    $urlLink = $h->url(array('tag' => str_replace(' ', '_', urlencode(trim($tag)))));
    $status = isset($statusTags[$tag]) &&  $statusTags[$tag] == 'exclude' ? "btn-danger" : "btn-primary";
    
    $btnClass = ($displayButtons) ? "btn btn-xs " . $status : "";
    
    if ($statusTags != "exclude" || $h->isAdmin) {
        echo "<li><a class='" . $btnClass . "' href='" . $urlLink . "'>#" . trim($tag) . "</a></li>";
    }
}

