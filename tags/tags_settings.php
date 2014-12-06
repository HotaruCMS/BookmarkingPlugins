<?php
/**
 * File: /plugins/tags/tags_settings.php
 * Purpose: Admin settings for the tags plugin
 *
 * author: Nick Ramsay
 * authorurl: http://hotarucms.org/member.php?1-Nick
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

class TagsSettings
{
    /**
     * Tags Settings Page
     */
    public function settings($h) {

	// If the form has been submitted, go and save the data...
        if ($h->cage->post->getAlpha('submitted') == 'true') {
            $this->saveSettings($h);
        }
              
	// Get settings from database if they exist...
        $tags_settings = $h->getSerializedSettings();

	$settings = array( 'tags_setting_exclude_active' => '',
			'tags_setting_exclude_words' => '',
                        'tags_setting_display_buttons' => '',
	    );

	foreach ($settings as $setting => $value) {
	    $$setting = $tags_settings[$setting];
	    if (!$tags_settings[$setting]) { $$setting = $value; }
	}

	echo "<form class='form-horizontal' role='form' name='tags_settings_form' action='" . BASEURL . "admin_index.php?page=plugin_settings&plugin=tags' method='post'>";

	// setting1
        echo '<div class="form-group">';
            echo '<label for="tags_setting_exclude_active" class="col-sm-2 control-label">' . $h->lang('tags_setting_exclude_active') . '</label>';
            echo '<div class="col-sm-10">';
                echo '<div class="checkbox">';
                    echo '<label>';
                        echo '<input type="checkbox" name="tags_setting_exclude_active" value="tags_setting_exclude_active" ' . $tags_setting_exclude_active . '>';
                    echo '</label>';
                echo '</div>';
            echo '</div>';
        echo '</div>';
        
        // setting2
        echo '<div class="form-group">';
            echo '<label for="tags_setting_exclude_words" class="col-sm-2 control-label">' . $h->lang('tags_setting_exclude_words') . '</label>';
            echo '<div class="col-sm-10">';
                echo '<textarea class="form-control" name="tags_setting_exclude_words" rows="8">' . $tags_setting_exclude_words . '</textarea>';
            echo '</div>';
        echo '</div>';        

        // setting3
        echo '<div class="form-group">';
            echo '<label for="tags_setting_display_buttons" class="col-sm-2 control-label">' . $h->lang('tags_setting_display_buttons') . '</label>';
            echo '<div class="col-sm-10">';
                echo '<div class="checkbox">';
                    echo '<label>';
                        echo '<input type="checkbox" name="tags_setting_display_buttons" value="tags_setting_display_buttons" ' . $tags_setting_display_buttons . '>';
                    echo '</label>';
                echo '</div>';
            echo '</div>';
        echo '</div>';
        
        echo '<br/>';
        
        // submit button
        echo '<div class="form-group">';
            echo '<div class="col-sm-offset-2 col-sm-10">';
                echo "<input type='hidden' name='submitted' value='true' />";
                echo "<button type='submit' class='btn btn-primary'>" . $h->lang["main_form_save"] . "</buton>" ;
                echo "<input type='hidden' name='csrf' value='" . $h->csrfToken . "' />";
            echo '</div>';
        echo '</div>';
        
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

        // show setting1?
        if ($h->cage->post->keyExists('tags_setting_exclude_active')) {
            $tags_setting_exclude_active = 'checked';
        } else {
            $tags_setting_exclude_active = '';
        }

        // tags_setting_exclude_words
        if ($h->cage->post->keyExists('tags_setting_exclude_words')) {
            if ($h->cage->post->getHtmLawed('tags_setting_exclude_words')) {
                $tags_setting_exclude_words = $h->cage->post->getHtmLawed('tags_setting_exclude_words');
            } else {
                $tags_setting_exclude_words = ''; $error = 1;
            }
        } else {
            $tags_setting_exclude_words = ''; $error = 1;
        }

        // show setting3
        if ($h->cage->post->keyExists('tags_setting_display_buttons')) {
            $tags_setting_display_buttons = 'checked';
        } else {
            $tags_setting_display_buttons = '';
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
            $tags_settings['tags_setting_exclude_active'] = $tags_setting_exclude_active;
            $tags_settings['tags_setting_exclude_words'] = $tags_setting_exclude_words;
            $tags_settings['tags_setting_display_buttons'] = $tags_setting_display_buttons; 
            
            $h->updateSetting('tags_settings', serialize($tags_settings));
            
            $h->message = $h->lang["main_settings_saved"];
            $h->messageType = "green";
            $h->showMessage();

            return true;
        }
    }

}
?>