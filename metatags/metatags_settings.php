<?php
/**
 * File: /plugins/metatags/metatags_settings.php
 * Purpose: Admin settings for the Metatags plugin
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
 
class MetatagsSettings
{
     /**
     * Admin settings for metatags
     */
    public function settings($h)
    {
        // If the form has been submitted, go and save the data...
        if ($h->cage->post->getAlpha('submitted') == 'true') { 
            $this->saveSettings($h); 
        }
          
        // Get settings from database if they exist...
        $metatags_description = $h->getSetting('metatags_description');
        $metatags_keywords = $h->getSetting('metatags_keywords');
    
        $h->pluginHook('metatags_settings_get_values');
        
        //...otherwise set to blank:
        if (!$metatags_description) { $metatags_description = ''; } 
        if (!$metatags_keywords) { $metatags_keywords = ''; }
              
        echo "<form name='metatags_settings_form' action='" . BASEURL . "admin_index.php?page=plugin_settings&amp;plugin=metatags' method='post'>\n";
        
        echo "<p>" . $h->lang["metatags_settings_instructions"] . "</p><br />";
            
        echo "<p>" . $h->lang["metatags_settings_description"] . " <input type='text' size=60 name='metatags_description' value='" . $metatags_description . "'></p>\n";    
        echo "<p>" . $h->lang["metatags_settings_keywords"] . " <input type='text' size=60 name='metatags_keywords' value='" . $metatags_keywords . "'></p>\n";    
        
        $h->pluginHook('metatags_settings_form');
                
        echo "<br /><br />\n";    
        echo "<input type='hidden' name='submitted' value='true' />\n";
        echo "<input type='submit' value='" . $h->lang["main_form_save"] . "' />\n";
        echo "<input type='hidden' name='csrf' value='" . $h->csrfToken . "' />\n";
        echo "</form>\n";
    }
    
    
     /**
     * Save admin settings for metatags
     *
     * @return true
     */
    public function saveSettings($h)
    {
        $error = 0;
        
        // test the description input
        if ($h->cage->post->keyExists('metatags_description')) { 
            $metatags_description = $h->cage->post->getHtmLawed('metatags_description');
        } else {
            $metatags_description = '';            
        }
        
    
        // test the keywords input
        if ($h->cage->post->keyExists('metatags_keywords')) { 
            $metatags_keywords = $h->cage->post->getHtmLawed('metatags_keywords');
        } else {
            $metatags_keywords = ''; 
        }
        
        
        $h->pluginHook('metatags_save_settings');
        
        if ($error == 0) {
            // save settings
            $h->updateSetting('metatags_description', $metatags_description);
            $h->updateSetting('metatags_keywords', $metatags_keywords);
            
            $h->message = $h->lang["main_settings_saved"];
            $h->messageType = "green alert-success";
        }
        $h->showMessage();
        
        return true;    
    }
    
}
?>