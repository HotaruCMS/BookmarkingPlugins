<?php
/**
 * Template for Search
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
    <form class="navbar-search pull-right" accept-charset="UTF-8" name='search_form' id='search_form' action='<?php echo BASEURL; ?>index.php?page=search' method='get'>     
        <input name="search" id="search_input" type="text" class="search-query span2" placeholder="<?php echo $h->lang('search_text'); ?>">
        <input name="utf8" type="hidden" value="✓">
        <input type="hidden" id="dosearch" />
    </form>
   
