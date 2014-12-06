<?php
/**
 * File: /plugins/tos_antispam/tos_antispam_settings.php
 * Purpose: Admin settings for the TOS AntiSpam plugin
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
 
class TosAntispamSettings
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
        
        echo "<h1>" . $h->lang["tos_antispam_settings_header"] . "</h1>\n";
          
        // Get settings from database if they exist...
        $tos_antispam_settings = $h->getSerializedSettings();
        $registration = $tos_antispam_settings['registration'];
        $post_submission = $tos_antispam_settings['post_submission'];
        $first_x_posts = $tos_antispam_settings['first_x_posts'];

        $question = $tos_antispam_settings['question'];
        $use_choices = $tos_antispam_settings['use_choices'];
        $choices = $tos_antispam_settings['choices'];
        $answer = $tos_antispam_settings['answer'];

        $question2 = $tos_antispam_settings['question2'];
        $use_choices2 = $tos_antispam_settings['use_choices2'];
        $choices2 = $tos_antispam_settings['choices2'];
        $answer2 = $tos_antispam_settings['answer2'];
    
        $h->pluginHook('tos_antispam_settings_get_values');
        
        //...otherwise set to blank:
        if (!isset($registration)) { $registration = 'checked'; }
        if (!isset($post_submission)) { $post_submission = ''; }
        if (!isset($first_x_posts)) { $first_x_posts = 1; }
            
        echo "<form role='form' name='tos_antispam_settings_form' action='" . BASEURL . "admin_index.php?page=plugin_settings&amp;plugin=tos_antispam' method='post'>\n";
        
		// REGISTRATION

		echo "<h2>" . $h->lang["tos_antispam_settings_user_registration"] . "</h2>";

        echo "<p><input type='checkbox' name='register' value='tos_antispam_register' " . $registration . " >&nbsp;&nbsp;";
        echo $h->lang["tos_antispam_settings_registration"] . "</p>\n"; 

        echo "<p>" . $h->lang["tos_antispam_settings_question"] . " <input type='text' size='60' name='question' value='" . sanitize($question, 'ents') . "'></p>\n";
        echo "<p><input type='checkbox' name='use_choices' value='multiple_choice' " . $use_choices . " >&nbsp;&nbsp;";
        echo $h->lang["tos_antispam_settings_use_choices"] . "</p>\n"; 
        echo "<p>" . $h->lang["tos_antispam_settings_choices"] . "</p>";
		echo "<p><input type='text' size='80' name='choices' value='" . $this->show_list($choices) . "'></p>\n";
        echo "<p>" . $h->lang["tos_antispam_settings_answer"] . " <input type='text' size='20' name='answer' value='" . sanitize($answer, 'ents') . "'></p>\n";
		echo "<br />";

		// POST SUBMISSION

		echo "<h2>" . $h->lang["tos_antispam_settings_post_submission"] . "</h2>";

        echo "<p><input type='checkbox' name='post_submission' value='tos_antispam_post_submission' " . $post_submission . " >&nbsp;&nbsp;";
        echo $h->lang["tos_antispam_settings_submission"] . "</p>\n"; 

        echo "<br />\n";
        echo "<p><input type='text' size='5' name='first_x_posts' value='" . $first_x_posts . "'>&nbsp;&nbsp;" . $h->lang["tos_antispam_settings_first_x_posts"] . "</p>\n";
        
        echo "<br />\n";
        echo "<p>" . $h->lang["tos_antispam_settings_question"] . " <input type='text' size='60' name='question2' value='" . sanitize($question2, 'ents') . "'></p>\n";
        echo "<p><input type='checkbox' name='use_choices2' value='multiple_choice' " . $use_choices2 . " >&nbsp;&nbsp;";
        echo $h->lang["tos_antispam_settings_use_choices"] . "</p>\n"; 
        echo "<p>" . $h->lang["tos_antispam_settings_choices"] . "</p>";
		echo "<p><input type='text' size='80' name='choices2' value='" . $this->show_list($choices2) . "'></p>\n";
        echo "<p>" . $h->lang["tos_antispam_settings_answer"] . " <input type='text' size='20' name='answer2' value='" . sanitize($answer2, 'ents') . "'></p>\n";
    
        $h->pluginHook('tos_antispam_settings_form');
                
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
            
        // use TOS AntiSpam on registration
        if ($h->cage->post->keyExists('register')) { 
            $registration = "checked";
        } else {
            $registration = "";
        }
        
        // use TOS AntiSpam on post submission
        if ($h->cage->post->keyExists('post_submission')) { 
            $post_submission = "checked";
        } else {
            $post_submission = "";
        }
        
        // first_x_posts
        $first_x_posts = trim($h->cage->post->testInt('first_x_posts'));
        if (!$first_x_posts) {
            $first_x_posts = 1; 
        }

        // Anti-spam question 1
        if ($h->cage->post->keyExists('question')) { 
            $question = sanitize($h->cage->post->getHtmLawed('question'), 'tags', '<b><i><u>');
            $error = 0;
        } else {
            $question = ''; 
            $error = 1;
			$h->messages[$h->lang["tos_antispam_settings_no_question"]] = "red";
        }
        
        // Use Anti-spam choices 1
        if ($h->cage->post->keyExists('use_choices')) { 
            $use_choices = "checked";
        } else {
            $use_choices = "";
        }

        // Anti-spam choices 1
        if ($h->cage->post->keyExists('choices')) { 
            $answer_string = $h->cage->post->sanitizeTags('choices');
            $choices = explode(',', $answer_string);
            shuffle($choices);
            $choices = array_map('trim', $choices);
            foreach ($choices as $choice) {
                $new_choices[make_url_friendly($choice)] = $choice;
            }
            if (isset($new_choices)) { $choices = $new_choices; }
            $error = 0;
        } else {
            $choices = array();
			if ($use_choices2) {
	            $error = 1;
				$h->messages[$h->lang["tos_antispam_settings_no_choices"]] = "red";
			}
        }
        
        // Anti-spam correct answer 1
        $answer = trim($h->cage->post->sanitizeTags('answer'));
        if ($answer && $use_choices && isset($choices[make_url_friendly($answer)])) {
            $error = 0;
		} elseif ($answer && !$use_choices) {
			$error = 0;
        } else {
            $answer = ''; 
            $error = 1;
            $h->messages[$h->lang["tos_antispam_settings_no_answer"]] = "red";
        }
        
        // Anti-spam question 2
        if ($h->cage->post->keyExists('question2')) { 
            $question2 = sanitize($h->cage->post->getHtmLawed('question2'), 'tags', '<b><i><u>');
            $error = 0;
        } else {
            $question2 = ''; 
            $error = 1;
			$h->messages[$h->lang["tos_antispam_settings_no_question2"]] = "red";
        }
        
        // Use Anti-spam choices 2
        if ($h->cage->post->keyExists('use_choices2')) { 
            $use_choices2 = "checked";
        } else {
            $use_choices2 = "";
        }

        // Anti-spam choices 2
        if ($h->cage->post->keyExists('choices2')) { 
            $answer_string = $h->cage->post->sanitizeTags('choices2');
            $choices2 = explode(',', $answer_string);
            shuffle($choices2);
            $choices2 = array_map('trim', $choices2);
			$new_choices = array(); // reset after doing choices 1
            foreach ($choices2 as $choice) {
                $new_choices[make_url_friendly($choice)] = $choice;
            }
            if (isset($new_choices)) { $choices2 = $new_choices; }
            $error = 0;
        } else {
            $choices2 = array();
			if ($use_choices2) {
	            $error = 1;
	            $h->messages[$h->lang["tos_antispam_settings_no_choices2"]] = "red";
			}
        }
        
        // Anti-spam correct answer 2
        $answer2 = trim($h->cage->post->sanitizeTags('answer2'));
        if ($answer2 && $use_choices2 && isset($choices2[make_url_friendly($answer2)])) {
            $error = 0;
		} elseif ($answer2 && !$use_choices2) {
			$error = 0;
        } else {
            $answer2 = ''; 
            $error = 1;
            $h->messages[$h->lang["tos_antispam_settings_no_answer2"]] = "red";
        }
        
        $h->pluginHook('tos_antispam_save_settings');
        
        if ($error == 0) {
            // save settings
            $tos_antispam_settings['registration'] = $registration;
            $tos_antispam_settings['post_submission'] = $post_submission;
            $tos_antispam_settings['first_x_posts'] = $first_x_posts;
            $tos_antispam_settings['question'] = $question;
            $tos_antispam_settings['use_choices'] = $use_choices;
            $tos_antispam_settings['choices'] = $choices;
            $tos_antispam_settings['answer'] = $answer;
            $tos_antispam_settings['question2'] = $question2;
            $tos_antispam_settings['use_choices2'] = $use_choices2;
            $tos_antispam_settings['choices2'] = $choices2;
            $tos_antispam_settings['answer2'] = $answer2;
            $h->updateSetting('tos_antispam_settings', serialize($tos_antispam_settings));
            
            $h->messages[$h->lang["main_settings_saved"]] = "green";
        }
        $h->showMessages();
        
        return true;    
    }
    
    /**
     * HTML for pre-filling the choices
     *
     * @param array $choices
     * @return string $output
     */
    public function show_list($choices = array()) 
    {
        $output = '';
        foreach ($choices as $key => $value) {
            if ($value) {
                $output .= sanitize($value, 'ents') . ", ";
            }
        }
        $output = rstrtrim($output, ", ");
        
        return $output;
    }
}
?>