<?php
/**
 * File: /plugins/categories/categories_settings.php
 * Purpose: Admin settings for the categories plugin
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

class CategoriesSettings
{
     /**
     * Admin settings for Categories
     */
    public function settings($h)
    {
        // If the form has been submitted, go and save the data...
        if ($h->cage->post->getAlpha('submitted') == 'true') {
            $this->saveSettings($h);
        }
              
	// Get settings from database if they exist...
        $categories_settings = $h->getSerializedSettings();
       
	echo '<form name="categories_settings_form" action="'. BASEURL . 'admin_index.php?page=plugin_settings&amp;plugin=categories" method="post">';
		
        echo '<input type="checkbox" name ="categories_nav_show" value="categories_nav_show" '.$categories_settings['categories_nav_show'].'>&nbsp;'.$h->lang('categories_settings_nav_show'). '<br />';
		
       	echo $h->lang['categories_setting_nav_style'].'&nbsp;<select name ="categories_nav_style">
			<option selected="yes">'.$categories_settings['categories_nav_style'].'</option>
                        <option>style1</option>
                        <option>style2</option></select> <br />';
		
	
        echo "<br /><br />";
        echo "<input type='hidden' name='submitted' value='true' />";
        echo "<input type='submit' value='" . $h->lang("main_form_save") . "' />";
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

        //Change the nav style setting
        if($h->cage->post->keyExists('categories_nav_style'))
        {
                $categories_settings['categories_nav_style'] = $h->cage->post->getAlnum('categories_nav_style');
        }      

        $categories_settings['categories_nav_show'] = $h->cage->post->keyExists('categories_nav_show') ? 'checked' : '';

        if ($error == 1)
        {
            $h->message = $h->lang["main_settings_not_saved"];
            $h->messageType = "red";
            $h->showMessage();

            return false;
        }
        else
        {                     
            $h->updateSetting('categories_settings', serialize($categories_settings));

            $h->message = $h->lang["main_settings_saved"];
            $h->messageType = "green";
            $h->showMessage();

            return true;
        }
    }

}
?>