<?php
/**
 * File: /plugins/stop_spam/stop_spam_settings.php
 * Purpose: Admin settings for the Stop Spam plugin
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
 
class StopSpamSettings
{
     /**
     * Admin settings for stop_spam
     */
    public function settings($h)
    {
        // If the form has been submitted, go and save the data...
        if ($h->cage->post->getAlpha('submitted') == 'true') { 
            $this->saveSettings($h); 
        }  

        if ($h->cage->post->getAlpha('tested') == 'true') { 
            $this->testSpam($h); 
        } 
          
        // Get settings from database if they exist...
        $stop_spam_key = $h->getSetting('stop_spam_key');
        $stop_spam_type = $h->getSetting('stop_spam_type');
    
        $h->pluginHook('stop_spam_settings_get_values');
        
        //...otherwise set to blank:
        if (!$stop_spam_key) { $stop_spam_key = ''; } 
        if (!$stop_spam_type) { $stop_spam_type = 'go_pending'; }
        
        // determine which radio button is checked
        if ($stop_spam_type == 'go_pending') { 
            $go_pending = 'checked'; 
            $block_reg = ''; 
        } else {
            $go_pending = ''; 
            $block_reg = 'checked'; 
        }
        
        $h->showMessages();
            
        echo "<form name='stop_spam_settings_form' action='" . BASEURL . "admin_index.php?page=plugin_settings&amp;plugin=stop_spam' method='post'>\n";
        
        echo "<p>" . $h->lang["stop_spam_settings_instructions"] . "</p><br />";
            
        echo "<p>" . $h->lang["stop_spam_settings_key"] . " <input type='text' size=30 name='stop_spam_key' value='" . $stop_spam_key . "'></p>\n";    
        
        echo "<p><input type='radio' name='ss_type' value='go_pending' " . $go_pending . "> " . $h->lang["stop_spam_settings_go_pending"] . "</p>\n";    
        echo "<p><input type='radio' name='ss_type' value='block_reg' " . $block_reg . "> " . $h->lang["stop_spam_settings_block_reg"] . "</p>\n";    
    
        $h->pluginHook('stop_spam_settings_form');
                
        echo "<br /><br />\n";    
        echo "<input type='hidden' name='submitted' value='true' />\n";
        echo "<input type='submit' class='btn btn-primary' value='" . $h->lang["main_form_save"] . "' />\n";
        echo "<input type='hidden' name='csrf' value='" . $h->csrfToken . "' />\n";
        echo "</form>\n";
        
        
        echo "<h3>" . $h->lang["stop_spam_test_title"] . "</h3>";        
        echo "<p>" . $h->lang["stop_spam_test_instructions"] . "</p><br />";
        
        echo "<form name='stop_spam_test_form' action='" . BASEURL . "admin_index.php?page=plugin_settings&amp;plugin=stop_spam' method='post'>\n";
                           
        //echo '<div class="input-prepend"><span class="add-on">@</span><input class="span8" id="stop_spam_test_username" name="stop_spam_test_username" type="text" placeholder="Username"></div>';
        //echo '<br/>';
        echo '<div class="input-prepend"><span class="add-on"><i class="icon-envelope"></i></span><input class="span8" id="stop_spam_test_email" name="stop_spam_test_email" type="email" placeholder="Email"></div>';
         echo '<br/>';
        echo '<div class="input-prepend"><span class="add-on">::</span><input class="span8" id="stop_spam_test_ip" name="stop_spam_test_ip" type="text" placeholder="IP Address"></div>';
                
        echo "<br />\n";    
        echo "<input type='hidden' name='tested' value='true' />\n";
        echo "<input type='submit' class='btn' value='Test' />\n";
        echo "<input type='hidden' name='csrf' value='" . $h->csrfToken . "' />\n";
        echo "</form>\n";
    }
    
    
    /**
     * Test Spam
     * 
     */
    public function testSpam($h)
    {
        $username = $h->cage->post->testAlnumLines('stop_spam_test_username');
        $email = $h->cage->post->testEmail('stop_spam_test_email');
        $ip = $h->cage->post->testIp('stop_spam_test_ip');
       
        if (!$username && !$email && !$ip) { $h->messages[$h->lang('stop_spam_no_test_data')] = 'red'; return false; }

        // Include our StopSpam class:
        require_once(PLUGINS . 'stop_spam/libs/StopSpam.php');
        $spam = new StopSpamFunctions();
        
        $json = $spam->checkSpammers($username, $email, $ip);
        $flags = $spam->flagSpam($h, $json);
        
        $msg = implode(', ', $flags);
            
        if ($flags) {
            $h->messages[$h->lang('stop_spam_test_result') . ' : ' . $msg] = 'alert-danger';
        } else {
            $h->messages[$h->lang('stop_spam_test_negative')] = 'alert-info';
        }
       
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
        if ($h->cage->post->keyExists('stop_spam_key')) { 
            $stop_spam_key = $h->cage->post->testAlnumLines('stop_spam_key');
            $error = 0;
        } else {
            $stop_spam_key = ''; 
            $error = 1;
            $h->message = $h->lang["stop_spam_settings_no_key"];
            $h->messageType = "red";
        }
        
    
        // stop forum spam type
        if ($h->cage->post->keyExists('ss_type')) { 
            $stop_spam_type = $h->cage->post->testAlnumLines('ss_type');
        } else {
            $stop_spam_type = ''; 
        }
        
        
        $h->pluginHook('stop_spam_save_settings');
        
        if ($error == 0) {
            // save settings
            $h->updateSetting('stop_spam_key', $stop_spam_key);
            $h->updateSetting('stop_spam_type', $stop_spam_type);
            
            $h->message = $h->lang["main_settings_saved"];
            $h->messageType = "green";
        }
        $h->showMessage();
        
        return true;    
    }
    
}
?>