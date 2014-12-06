<?php
/**
 *  Bookmarking Settings
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

class BookmarkingSettings
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
        $bookmarking_settings = $h->getSerializedSettings();
        
        $posts_per_page = $bookmarking_settings['posts_per_page'];
        $rss_redirect = $bookmarking_settings['rss_redirect'];
	$default_type = $bookmarking_settings['default_type'];
	$default_page = $bookmarking_settings['default_page'];
        $archive = $bookmarking_settings['archive'];
        $sort_bar_dropdown = $bookmarking_settings['sort_bar_dropdown'];
    
        $use_alerts = $bookmarking_settings['use_alerts'];
        $alerts_to_bury = $bookmarking_settings['alerts_to_bury'];
        $physical_delete = $bookmarking_settings['physical_delete'];
        
        $h->pluginHook('bookmarking_settings_get_values');
        
        //...otherwise set to these defaults:
        if (!$posts_per_page) { $posts_per_page = 10; }
        if (!$rss_redirect) { $rss_redirect = ''; }           
	if (!$default_type) { $default_type = 'news'; }
        if (!$archive) { $archive = 'no_archive'; }
        if (!$sort_bar_dropdown) { $sort_bar_dropdown = ''; }        
        if (!isset($use_alerts)) { $use_alerts = 'checked'; }
        if (!$alerts_to_bury) { $alerts_to_bury = 5; }
        if (!$physical_delete) { $physical_delete = ''; }
        
        echo "<form role='form' name='bookmarking_settings_form' action='" . BASEURL . "admin_index.php?page=plugin_settings&amp;plugin=bookmarking' method='post'>\n";

        // posts per page
        echo "<p><input type='text' size=5 name='posts_per_page' value='" . $posts_per_page . "' /> ";
        echo $h->lang("bookmarking_settings_posts_per_page") . "</p>\n";

	$sql = "SELECT DISTINCT post_type FROM " . TABLE_POSTS . " ORDER BY post_type ASC";
	$types = $h->db->get_results($h->db->prepare($sql));	
	echo "<p><select name ='default_type'>";
	if ($types)
	{
		foreach ($types as $type) {
		    echo "<option ";
		    if ($type->post_type == $default_type) { echo "selected='yes' "; }
		    echo "value='" . $type->post_type . "'>" . $type->post_type . "</option>";
		}
	}
	else 
	{
	    echo "<option selected='yes' value='" . $default_type . "'>" . $default_type . "</option>";
	}
	echo "</select> " . $h->lang("bookmarking_settings_default_type") . "</p>";

	echo "<p><select name ='default_page'>";
	$pages = array('popular', 'upcoming', 'latest', 'all');
	foreach ($pages as $page) {
	    echo "<option ";
	    if ($page == $default_page) { echo "selected='yes' "; }
	    echo "value='" . $page . "'>" . $page . "</option>";
	}
	echo "</select> " . $h->lang("bookmarking_settings_default_page") . "</p>";

        // rss redirecting?
        echo "<p><input type='checkbox' name='rss_redirect' value='rss_redirect' " . $rss_redirect . " >&nbsp;&nbsp;" . $h->lang("bookmarking_settings_rss_redirect") . "<br />\n"; 
      
        // sort_bar_dropdown?
        echo "<p><input type='checkbox' name='sort_bar_dropdown' value='sort_bar_dropdown' " . $sort_bar_dropdown . " >&nbsp;&nbsp;" . $h->lang("bookmarking_settings_sort_bar_dropdown") . "<br />\n"; 
      
        echo "<p><input type='checkbox' name='bookmarking_use_alerts' value='bookmarking_use_alerts' " . $use_alerts . " > " . $h->lang("bookmarking_settings_use_alerts") . "</p>\n";
        echo "<p>" . $h->lang("bookmarking_settings_alerts_to_bury") . " <input type='text' size=5 name='bookmarking_alerts_to_bury' value='" . $alerts_to_bury . "' /> <small> (Default: 5)</small></p>\n";        
        echo "<p><input type='checkbox' id='bookmarking_physical_delete' name='bookmarking_physical_delete' " . $physical_delete . " /> " . $h->lang("bookmarking_settings_physical_delete") . "</p>";        
        
        $h->pluginHook('bookmarking_settings_form');
    
        echo "<br />\n";

        echo "<input type='hidden' name='submitted' value='true' />\n";
        echo "<button class='btn btn-primary' type='submit'>" . $h->lang("main_form_save") . "</button>\n";
        echo "<input type='hidden' name='csrf' value='" . $h->csrfToken . "' />\n";
        echo "</form>\n";
    }
    
    
    /**
     * Save Submit Settings
     */
    public function saveSettings($h) 
    {
        // Get current settings 
        $bookmarking_settings = $h->getSerializedSettings();
        
        // Posts per page
        $posts_per_page = $h->cage->post->testInt('posts_per_page'); 
        if (!$posts_per_page) { 
            $posts_per_page = $bookmarking_settings['posts_per_page']; 
        }        
    
        // RSS Redirecting
        if ($h->cage->post->keyExists('rss_redirect')) { 
            $rss_redirect = 'checked'; 
        } else { 
            $rss_redirect = ''; 
        }        
        
        // sort_bar_dropdown
        if ($h->cage->post->keyExists('sort_bar_dropdown')) { 
            $sort_bar_dropdown = 'checked'; 
        } else { 
            $sort_bar_dropdown = ''; 
        }  
	
	// default type
	if ($h->cage->post->testAlnumLines('default_type')) {
            $default_type = $h->cage->post->testAlnumLines('default_type');
        } else { 
            $default_type = 'news'; 
        }

	// default page
	if ($h->cage->post->testAlnumLines('default_page')) {
            $default_page = $h->cage->post->testAlnumLines('default_page');
        } else {
            $default_page = 'popular';
        }
        
        // Check the status of our checkbox for physical delete
        if ($h->cage->post->keyExists('bookmarking_physical_delete')) { 
            $physical_delete = 'checked'; 
        } else { 
            $physical_delete = ''; 
        }
        
        // Use alerts
        if ($h->cage->post->keyExists('bookmarking_use_alerts')) { 
            $use_alerts = 'checked'; 
        } else { 
            $use_alerts = ''; 
        }
        
        
        // Check alerts_to_bury
        if ($h->cage->post->keyExists('bookmarking_alerts_to_bury')) { 
            $alerts_to_bury = $h->cage->post->testInt('bookmarking_alerts_to_bury'); 
            if ($alerts_to_bury < 1) {
                $h->messages[$h->lang("bookmarking_settings_alerts_to_bury_invalid")] = "red";
                $error = 1;
                $alerts_to_bury = $bookmarking_settings['alerts_to_bury'];
            }
        } else { 
            $h->messages[$h->lang("bookmarking_settings_alerts_to_bury_invalid")] = "red";
            $error = 1;
            $alerts_to_bury = $bookmarking_settings['alerts_to_bury'];
        }

        $h->pluginHook('bookmarking_save_settings');
        
        $bookmarking_settings['posts_per_page'] = $posts_per_page;
        $bookmarking_settings['rss_redirect'] = $rss_redirect;       
	$bookmarking_settings['default_type'] = $default_type;
	$bookmarking_settings['default_page'] = $default_page;
        $bookmarking_settings['sort_bar_dropdown'] = $sort_bar_dropdown;  
    
        $bookmarking_settings['use_alerts'] = $use_alerts;
        $bookmarking_settings['alerts_to_bury'] = $alerts_to_bury;
        $bookmarking_settings['physical_delete'] = $physical_delete;
        
        $h->updateSetting('bookmarking_settings', serialize($bookmarking_settings));
        
        $h->message = $h->lang("main_settings_saved");
        $h->messageType = "green";
        
        return true;    
    }    
}
?>
