<?php
/**
 * File: /plugins/follow/follow_settings.php
 * Purpose: Admin settings for the Follow plugin
 *
 * PHP version 5
 *
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
 * @author    shibuya246
 * @copyright Copyright (c) 2010, shibuya246
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 *
 * @link      http://www.hotarucms.org/
 */

class FollowSettings
{
    /**
     * Follow Settings Page
     */
    public function settings($h) {

	echo "<h1>" . $h->lang["follow_settings_header"] . "</h1>";

	/*
	echo "<div class='floatright' style='width:160px;'>";
	Follow::admin_topright($h);
	echo "</div>";
	echo '<br class="clearfix"/>';
	*/

	 // If the form has been submitted, go and save the data...
        if ($h->cage->post->getAlpha('submitted') == 'true') {
            $this->saveSettings($h);
        }

        // Get settings from database if they exist...
        $follow_settings = $h->getSerializedSettings();

	$settings = array( 'follow_show_time_date' => 'checked',
			'follow_show_extra_fields' => ''
	    );

	foreach ($settings as $setting => $value) {
	    $setting = $follow_settings[$setting];
	    if (!isset($follow_settings[$setting])) { $setting = $value; }
	}

	echo "<form name='follow_settings_form' action='" . BASEURL . "admin_index.php?page=plugin_settings&plugin=follow' method='post'>";

	// setting1
        echo "<p><input type='checkbox' name='follow_show_time_date' value='follow_show_time_date' " . $follow_settings['follow_show_time_date'] . " >  " . $h->lang["follow_settings_show_time_date"] . "</p>";

        // setting2
        echo "<p><input type='checkbox' name='follow_show_extra_fields' value='follow_show_extra_fields' " . $follow_settings['follow_show_extra_fields'] . " >  " . $h->lang["follow_settings_show_extra_fields"] . "</p>";

        $h->pluginHook('follow_settings_form');

        echo "<br /><br />";
        echo "<input type='hidden' name='submitted' value='true' />";
        echo "<input type='submit' value='" . $h->lang["main_form_save"] . "' />";
        echo "<input type='hidden' name='csrf' value='" . $h->csrfToken . "' />";
        echo "</form>";

    }


    /**
     * Save admin settings
     *
     * @return true
     */
    public function saveSettings($h)
    {
        $error = 0;

        // show_time_date
        if ($h->cage->post->keyExists('follow_show_time_date')) {
            $follow_show_time_date = 'checked';
        } else {
            $follow_show_time_date = 'false';
        }

        // show_extra_fields
        if ($h->cage->post->keyExists('follow_show_extra_fields')) {
            $follow_show_extra_fields = 'checked';
        } else {
            $follow_show_extra_fields = 'false';
        }
                

        if ($error == 1)
        {
            $h->message = $h->lang["main_settings_not_saved"];
            $h->messageType = "red";
            $h->showMessage();

            return false;
        }
        else
        {
            $follow_settings['follow_show_time_date'] = $follow_show_time_date;
            $follow_settings['follow_show_extra_fields'] = $follow_show_extra_fields;

            $h->updateSetting('follow_settings', serialize($follow_settings));

            $h->message = $h->lang["main_settings_saved"];
            $h->messageType = "green";
            $h->showMessage();

            return true;
        }
    }
    
    /**
     *
     * @param <type> $h
     */
    function admin_plugin_support($h)
    {		
            echo "<a href='http://www.pledgie.com/campaigns/10714'><img alt='Click here to lend your support to: Follow Plugin and make a donation at www.pledgie.com !' src='http://www.pledgie.com/campaigns/10714.png?skin_name=chrome' border='0' /></a>";
            echo "<br/><br/><small>If you appreciate the work of this plugin, help support its continued development by clicking here for a donation.</small>";
    }
   

}
?>
