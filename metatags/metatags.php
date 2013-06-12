<?php
/**
 * name: Metatags
 * description: Insert/edit metatags for top page
 * version: 0.1
 * folder: metatags
 * class: Metatags
 * type: metatags
 * hooks: install_plugin, admin_sidebar_plugin_settings, admin_plugin_settings, header_meta
 * author: shibuya246
 * authorurl: http://hotarucms.org/member.php?shibuya246
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
 * @author    shibuya246 <admin@hotarucms.org>
 * @copyright Copyright (c) 2009 - 2013, Hotaru CMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      http://www.hotarucms.org/
 */

class Metatags
{    
    /**
     * Default settings on install
     */
    public function install_plugin($h)
    {
        // Default settings 
        if (!$h->getSetting('metatags_description')) { $h->updateSetting('metatags_description', ''); }
        if (!$h->getSetting('metatags_keywords')) { $h->updateSetting('metatags_keywords', ''); }
    }
    
    
    /**
     * Also changes meta when browsing a category page
     */
    public function header_meta($h)
    {    
        // TODO 
        // Make proper check list for which pages should get meta
        if (!$h->subPage == 'category')
        { 
            $metatags_description = $h->getSetting('metatags_description');
            $metatags_keywords = $h->getSetting('metatags_keywords');
            
            if (isset($metatags_description)) {
                echo '<meta name="description" content="' . urldecode($metatags_description) . '" />' . "\n";
            } else {
                echo '<meta name="description" content="' . $h->lang['header_meta_description'] . '" />' . "\n";  // default meta tags
            }
            
            if (isset($metatags_keywords)) {
                echo '<meta name="keywords" content="' . urldecode($metatags_keywords) . '" />' . "\n";
            } else {
                echo '<meta name="keywords" content="' . $h->lang['header_meta_keywords'] . '" />' . "\n";  // default meta tags
            }

            return true;
        }
    }
   

}

?>