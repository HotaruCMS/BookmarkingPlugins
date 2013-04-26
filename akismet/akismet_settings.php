<?php
/**
 * File: /plugins/akismet/akismet_settings.php
 * Purpose: Admin settings for the Akismet plugin
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
 * @copyright Copyright (c) 2009, Hotaru CMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      http://www.hotarucms.org/
 */
 
class AkismetSettings
{
     /**
     * Admin settings for akismet
     */
    public function settings($h)
    {
        // If the form has been submitted, go and save the data...
        if ($h->cage->post->getAlpha('submitted') == 'true') { 
            $this->saveSettings($h); 
        }
        
        echo "<h1>" . $h->lang["akismet_settings_header"] . "</h1>\n";
          
        // Get settings from database if they exist...
        $akismet_key = $h->getSetting('akismet_key');
        $akismet_type = $h->getSetting('akismet_type');
    
        $h->pluginHook('akismet_settings_get_values');
        
        //...otherwise set to blank:
        if (!$akismet_key) { $akismet_key = ''; } 
        if (!$akismet_type) { $akismet_type = 'go_pending'; }
        
        // determine which radio button is checked
        if ($akismet_type == 'go_pending') { 
            $go_pending = 'checked'; 
            $block_reg = ''; 
        } else {
            $go_pending = ''; 
            $block_reg = 'checked'; 
        }
            
        echo "<form name='akismet_settings_form' action='" . BASEURL . "admin_index.php?page=plugin_settings&amp;plugin=akismet' method='post'>\n";
        
        echo "<p>" . $h->lang["akismet_settings_instructions"] . "</p><br />";
            
        echo "<p>" . $h->lang["akismet_settings_key"] . " <input type='text' size=30 name='akismet_key' value='" . $akismet_key . "'></p>\n";    
        
        echo "<p><input type='radio' name='ss_type' value='go_pending' " . $go_pending . "> " . $h->lang["akismet_settings_go_pending"] . "</p>\n";    
        echo "<p><input type='radio' name='ss_type' value='block_reg' " . $block_reg . "> " . $h->lang["akismet_settings_block_reg"] . "</p>\n";    
    
        $h->pluginHook('akismet_settings_form');
                
        echo "<br /><br />\n";    
        echo "<input type='hidden' name='submitted' value='true' />\n";
        echo "<input type='submit' value='" . $h->lang["main_form_save"] . "' />\n";
        echo "<input type='hidden' name='csrf' value='" . $h->csrfToken . "' />\n";
        echo "</form>\n";
    }
    
    
     /**
     * Save admin settings for akismet
     *
     * @return true
     */
    public function saveSettings($h)
    {
        $error = 0;
        
        // stop forum spam key
        if ($h->cage->post->keyExists('akismet_key')) { 
            $akismet_key = $h->cage->post->testAlnumLines('akismet_key');
            $error = 0;
        } else {
            $akismet_key = ''; 
            $error = 1;
            $h->message = $h->lang["akismet_settings_no_key"];
            $h->messageType = "red";
        }
        
    
        // stop forum spam type
        if ($h->cage->post->keyExists('ss_type')) { 
            $akismet_type = $h->cage->post->testAlnumLines('ss_type');
        } else {
            $akismet_type = ''; 
        }
        
        
        $h->pluginHook('akismet_save_settings');
        
        if ($error == 0) {
            // save settings
            $h->updateSetting('akismet_key', $akismet_key);
            $h->updateSetting('akismet_type', $akismet_type);
            
            $h->message = $h->lang["main_settings_saved"];
            $h->messageType = "green";
        }
        $h->showMessage();
        
        return true;    
    }
    
}
?>