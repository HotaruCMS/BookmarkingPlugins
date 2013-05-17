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
 * @author    Nick Ramsay <admin@hotarucms.org>
 * @copyright Copyright (c) 2009, Hotaru CMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      http://www.hotarucms.org/
 */

?>
<div class="navbar">
    <div class="navbar-inner">
<!--<nav role="navigation" class="clearfix">-->
  <ul class="nav">
      <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="/category/all">All Files</a>
        
          <ul class="dropdown-menu">
            <li><a href="/page/top_sellers">Popular Files</a></li>
            <li><a href="/author/top_authors">Top Authors</a></li>
            <li><a href="/page/top_new_sellers">Top New Authors</a></li>
          </ul>          
        
      </li>
      
       
        <li>
          <a href="/category">More</a>
          <div class="dropdown">
            <ul>
                <li><a href="/category/blogging">Blogging</a></li>
                <li><a href="/category/forums">Forums</a></li>
                <li><a href="/browse/attributes/compatible_with/facebook">Facebook Templates</a></li>
            </ul>
          </div>
        </li>
    <li class="search-container">
      <form accept-charset="UTF-8" action="/search" id="search" method="get"><div style="margin:0;padding:0;display:inline"><input name="utf8" type="hidden" value="✓"></div>
  <input autocomplete="off" id="term" name="term" placeholder="Start Searching …" type="text" value="">
  <button type="submit" class="image-button search no-margin">Search</button>
</form>
    </li>

    <?php $h->pluginHook('category_bar_start'); ?>
    <?php echo $h->vars['output']; ?>
    <?php $h->pluginHook('category_bar_end'); ?>
    
  </ul>
<!--</nav>-->
        
        </div></div>

<nav role="navigation" class="clearfix">

	<ul>
		<?php $h->pluginHook('category_bar_start'); ?>
		<?php echo $h->vars['output']; ?>
		<?php $h->pluginHook('category_bar_end'); ?>
	</ul>

</nav>    

<div class="clear"></div>