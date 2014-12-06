<?php
/**
 * Template for Categories (Menu Bar)
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

// check whether we have the fluid setting. If not make false
$fluid = isset($h->vars['theme_settings']['fullWidth']) && $h->vars['theme_settings']['fullWidth'] == 'checked'  ? '-fluid' : '';

?>

<div class="container-fluid">
            <div id="category-row"  class="row">

<div id="header_end" class="container<?php echo $fluid; ?>">
                        <!-- CATEGORIES, ETC -->
                        
                
<div id="category_bar">
	<ul>
		<?php $h->pluginHook('category_bar_start'); ?>
		<?php echo $h->categoriesDisplay; ?>
		<?php $h->pluginHook('category_bar_end'); ?>
	</ul>
</div>

<div class="clear"></div>


</div></div></div>