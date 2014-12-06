<?php
/**
 * File: /plugins/related_posts/related_posts_settings.php
 * Purpose: Admin settings for the related posts plugin
 *
 * author: Shibuya246
 * authorurl: http://hotarucms.org/members/shibuya246
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

class relatedPostsSettings
{
    /**
     * Related Posts Settings Page
     */
    public function settings($h) {

	 // If the form has been submitted, go and save the data...
        if ($h->cage->post->getAlpha('submitted') == 'true') {
            $this->saveSettings($h);
        }
              
	// Get settings from database if they exist...
        $related_posts_settings = $h->getSerializedSettings();

	$settings = array( 'submit_related_posts_submit' => '10',
			'submit_related_posts_post' => '5',
	    );

	foreach ($settings as $setting => $value) {
	    $$setting = $related_posts_settings[$setting];
	    if (!$related_posts_settings[$setting]) { $$setting = $value; }
	}

	echo "<form clas='form-horizontal' role='form' name='settings_form' action='" . BASEURL . "admin_index.php?page=plugin_settings&plugin=related_posts' method='post'>";

	// setting1
        echo '<div class="form-group">';
        echo '<label for="related_posts_setting_posts_submit" class="col-sm-2 control-label">' . $h->lang('related_posts_setting_posts_submit') . '</label>';
        echo '<div class="col-sm-10">';
        echo '<input type="text" class="form-control" name="related_posts_setting_posts_submit" placeholder="Submit Posts" value="' . $submit_related_posts_submit . '">';
        echo '<span class="help-text">' . $h->lang("submit_settings_related_posts_submit") .'</span>';
        echo '</div>';
        echo '</div>';        

        // setting2
        echo '<div class="form-group">';
        echo '<label for="related_posts_setting_posts_post" class="col-sm-2 control-label">' . $h->lang('related_posts_setting_posts_post') . '</label>';
        echo '<div class="col-sm-10">';
        echo '<input type="text" class="form-control" name="related_posts_setting_posts_post" id="inputRelatedPostsSettingPostsPost" placeholder="Number of Posts" value="' . $submit_related_posts_post . '">';
        echo '<span class="help-text">' . $h->lang("submit_settings_related_posts_post") .'</span>';
        echo '</div>';
        echo '</div>';
        
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
        if ($h->cage->post->keyExists('related_posts_setting_posts_submit')) {
            if ($h->cage->post->getHtmLawed('related_posts_setting_posts_submit')) {
                $related_posts_setting_posts_submit = $h->cage->post->getHtmLawed('related_posts_setting_posts_submit');
            } else {
                $related_posts_setting_posts_submit = ''; $error = 1;
            }
        } else {
            $related_posts_setting_posts_submit = ''; $error = 1;
        }

        // related_posts_setting_posts_post
        if ($h->cage->post->keyExists('related_posts_setting_posts_post')) {
            if ($h->cage->post->getHtmLawed('related_posts_setting_posts_post')) {
                $related_posts_setting_posts_post = $h->cage->post->getHtmLawed('related_posts_setting_posts_post');
            } else {
                $related_posts_setting_posts_post = ''; $error = 1;
            }
        } else {
            $related_posts_setting_posts_post = ''; $error = 1;
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
            // these settings should really have been serialized to save space but no problems
            // install has them as 2 separate settings in db 

            $h->updateSetting('submit_related_posts_submit', $related_posts_setting_posts_submit);
            $h->updateSetting('submit_related_posts_post', $related_posts_setting_posts_post);

            $h->message = $h->lang["main_settings_saved"];
            $h->messageType = "green";
            $h->showMessage();

            return true;
        }
    }

}
?>