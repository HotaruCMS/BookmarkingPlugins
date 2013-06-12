<?php
/**
 * Gravatar Settings
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

class GravatarSettings
{
     /**
     * Admin settings for the Submit plugin
     */
    public function settings($h)
    {
        // If the form has been submitted, go and save the data...
        if ($h->cage->post->getAlpha('submitted') == 'true') { 
            $this->saveSettings($h); 
        }    
               
        $h->showMessage(); // Saved / Error message
        
        // Get settings from database if they exist...
        $gravatar_settings = $h->getSerializedSettings();
        
        $default_avatar = $gravatar_settings['default_avatar'];
        
        //...otherwise set to blank: 
        if (!$default_avatar) { $default_avatar = ''; }   

		echo "<p>" . $h->lang["gravatar_settings_default_avatar"] . "</p>";
        
        echo "<form name='gravatar_settings_form' action='" . BASEURL . "admin_index.php?page=plugin_settings&amp;plugin=gravatar' method='post'>\n";

		$custom_exists = false;
		echo "<p>";
		
		$gravatars = array('mm', 'identicon', 'monsterid', 'wavatar', 'retro', 'blank');
		foreach ($gravatars as $gravatar) 
		{
			$style = ($gravatar === $default_avatar) ? " style='border: 2px solid #ff0000'" : "";
			if (($gravatar === 'blank') && ($gravatar !== $default_avatar)) { $style = " style='border: 1px solid #e6e6e6'"; }
			echo "<img src='http://www.gravatar.com/avatar/00000000000000000000000000000000?d=" . $gravatar . "&f=y' title='" . $gravatar . "' " . $style . " /> &nbsp; ";
		}
		
		if (file_exists(THEMES . THEME . "images/default_80.png"))
		{
			$style =  ($default_avatar === 'custom') ? " style='border: 2px solid #ff0000'" : "";
			echo "<img src='" . SITEURL . "content/themes/" . THEME . "images/default_80.png' title='custom' " . $style . " />";
			$custom_exists = true;
		}
		echo "</p><br />";

		echo "<p><select name='default_avatar'>";
		
		$avatars = array('mm', 'identicon', 'monsterid', 'wavatar', 'retro', 'blank', 'custom');
		foreach ($avatars as $avatar) {
		    echo "<option ";
		    if ($avatar === $default_avatar) { echo "selected='yes' "; }
		    echo "value='" . $avatar . "'>" . $avatar . "</option>";
		}
		
		echo "</select> ";
		echo "</p>";
		
		if (!$custom_exists) { echo "<p>" . $h->lang["gravatar_settings_custom_note"] . "</p><br />\n"; }

        echo "<input type='hidden' name='submitted' value='true' />\n";
        echo "<input type='submit' class='btn btn-primary' value='" . $h->lang["main_form_save"] . "' />\n";
        echo "<input type='hidden' name='csrf' value='" . $h->csrfToken . "' />\n";
        echo "</form>\n";
    }
    
    
    /**
     * Save Gravatar Settings
     */
    public function saveSettings($h) 
    {
        // Get current settings 
        $gravatar_settings = $h->getSerializedSettings();
        
		// default page
		if ($h->cage->post->keyExists('default_avatar')) {
            $default_avatar = $h->cage->post->testAlpha('default_avatar');
        } else {
            $default_avatar = 'identicon';
        }

        $gravatar_settings['default_avatar'] = $default_avatar;  
    
        $h->updateSetting('gravatar_settings', serialize($gravatar_settings));
        
        $h->message = $h->lang["main_settings_saved"];
        $h->messageType = "green";
        
        return true;    
    }
    
}
?>
