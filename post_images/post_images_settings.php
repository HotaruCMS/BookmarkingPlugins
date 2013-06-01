<?php
/**
* Settings page for the Post Images PLugin 
*/
class PostImagesSettings
{
	/**
	* Admin settings for the Post Images plugin
	*/
	public function settings($h)
	{		
		$folder = BASE . '/content/images/post_images/';
		if(is_dir($folder) && is_writable($folder)) {
                    //echo '<span class="green">OK</span> Post_images folder found and writable.'; 
                } else { 
                    echo '<span class="red"> ! </span> Make sure '.$folder.' exists and is writable by hotaru.';                     
                }

		// If the form has been submitted, go and save the data...
		if ($h->cage->post->getAlpha('submitted') == 'true') { 
				$this->saveSettings($h); 
		}
		
		// Get settings from database if they exist...
		$post_images_settings = $h->getSerializedSettings();  
		
		// set choices to blank
		$no = "";
		$url = "";
		$sitethumbshot = "";

		// determine which is selected
		switch($post_images_settings['default']) {
				case 'no':
					$no = "checked";
					break;
				case 'url':
					$url = "checked";
					break;
				default:
					$sitethumbshot = "checked";
		}
		
		$s = '';
		$t ='';
		$m = '';
		// determine which is selected
		switch($post_images_settings['sitethumbshot_size']) {
				case 'M':
					$m = "checked";
					break;
				case 'S':
					$s = "checked";
					break;
				default:
					$t = "checked";
		}
		$show_in_related_posts = '';
		$show_in_related_posts = $post_images_settings['show_in_related_posts'];
		$show_in_posts_widget = '';
		$show_in_posts_widget = $post_images_settings['show_in_posts_widget'];
		$post_images_pullRight = isset($post_images_settings['pullRight']) ? $post_images_settings['pullRight'] : '';	
                
		// start form
		echo "<form name='post_images_settings_form' ";
		echo "action='" . SITEURL . "admin_index.php?page=plugin_settings&amp;plugin=post_images' method='post'>\n";
		// instructions
		echo "<p>" . $h->lang['post_images_settings_default'] . "</p>";
		// input fields
		// w
		echo "<p>" . $h->lang['post_images_settings_w'];
		echo "<br /><input type='text' size=4 name='w' value='" . $post_images_settings['w'] . "' /></p>";
		// h
		echo "<p>" . $h->lang['post_images_settings_h'];
		echo "<br /><input type='text' size=4 name='h' value='" . $post_images_settings['h'] . "' /></p>";
		// quality
		echo "<p>" . $h->lang['post_images_settings_quality'];
		echo "<br /><input type='text' size=4 name='quality' value='" . $post_images_settings['quality'] . "' /></p>";
		// memory
		echo "<p>" . $h->lang['post_images_settings_memory'];
		echo "<br /><input type='text' size=4 name='memory' value='" . $post_images_settings['memory'] . "' /></p>";
		// radio buttons
		echo "<p><label><input type='radio' name='default' value='no' " . $no . " >";
		echo "&nbsp;&nbsp;" . $h->lang["post_images_settings_no"] . "</label></p>\n"; 

		echo "<p><input type='radio' name='default' value='url' " . $url . " >";
		echo "&nbsp;&nbsp;" . $h->lang["post_images_settings_url"] . "</p>\n"; 
		
		echo "<p><input type='radio' name='default' value='sitethumbshot' " . $sitethumbshot . " >";
		echo "&nbsp;&nbsp;" . $h->lang["post_images_settings_sitethumbshot"] . "</p>\n";         
		// input fields
		// default
		echo "<p>" . $h->lang['post_images_settings_default_url'];
		echo "<br /><input type='text' size=30 name='default_url' value='" . $post_images_settings['default_url'] . "' /> <i>required if default is chosen</i></p>";
		// sitethumbshot
		echo "<p>" . $h->lang['post_images_settings_sitethumbshot_key'];
		echo "<br /><input type='text' size=30 name='sitethumbshot_key' value='" . $post_images_settings['sitethumbshot_key'] . "' /> <i>required if sitethumbshot is chosen</i></p>";
		// sitethumbshot
		echo "<p>" . $h->lang['post_images_settings_sitethumbshot_size'];
		echo '<br /><select name="sitethumbshot_size"><option value="T" label="T" '.$t.'></option><option value="S" label="S" '.$s.'></option><option value="M" label="M" '.$m.'></option></select> <i>required if sitethumbshot is chosen</i></p>';
		// show in related posts?
		echo '<h3>' . $h->lang['post_images_settings_related_posts_heading'] . '</h3>';
		
		echo "<p>" . $h->lang['post_images_settings_related_posts'] . "<input type='checkbox' name='show_in_related_posts' value='show_in_related_posts' " . $show_in_related_posts . " ></p>";
		// show in posts widget?
		
		echo "<p>" . $h->lang['post_images_settings_posts_widget'] . "<input type='checkbox' name='show_in_posts_widget' value='show_in_posts_widget' " . $show_in_posts_widget . " ></p>";
                
                echo "<p>" . $h->lang['post_images_settings_pullRight'] . "<input type='checkbox' name='post_images_pullRight' value='post_images_pullRight' " . $post_images_pullRight . " ></p>";
		
                echo "<br />";
		// end form
		echo "<br />";
		echo "<input type='hidden' name='submitted' value='true' />\n";
		echo "<input type='submit' value='" . $h->lang["main_form_save"] . "' />\n";
		echo "<input type='hidden' name='csrf' value='" . $h->csrfToken . "' />\n";
		echo "</form>\n";
	}

	/**
	* Save Post Images settings
	*/
	public function saveSettings($h)
	{
		// Get settings from database if they exist...
		$post_images_settings = $h->getSerializedSettings();  
		$post_images_settings['w'] = $h->cage->post->getInt('w');
		$post_images_settings['h'] = $h->cage->post->getInt('h');
		$post_images_settings['quality'] = $h->cage->post->getInt('quality');
		$post_images_settings['memory'] = $h->cage->post->getAlnum('memory');
		$post_images_settings['default'] = $h->cage->post->getAlpha('default');
		$post_images_settings['default_url'] = $h->cage->post->getHtmLawed('default_url');
		$post_images_settings['sitethumbshot_key'] = $h->cage->post->getHtmLawed('sitethumbshot_key');
		$post_images_settings['sitethumbshot_size'] = $h->cage->post->getAlpha('sitethumbshot_size');
		if ($h->cage->post->keyExists('show_in_related_posts')) {
			$post_images_settings['show_in_related_posts'] = 'checked';
			}
		else {
			$post_images_settings['show_in_related_posts'] = 'unchecked';
			}
		if ($h->cage->post->keyExists('show_in_posts_widget')) {
			$post_images_settings['show_in_posts_widget'] = 'checked';
			}
		else {
			$post_images_settings['show_in_posts_widget'] = 'unchecked';
			}
               if ($h->cage->post->keyExists('post_images_pullRight')) {
			$post_images_settings['pullRight'] = 'checked';
			}
		else {
			$post_images_settings['pullRight'] = 'unchecked';
			}         
		
		// if bitly is chosen but either of the login or api key fields are empty, set error, don't save
		if ($post_images_settings['default'] == 'sitethumbshot' &&
				(strlen($post_images_settings['sitethumbshot_key']) == 0 || !$post_images_settings['sitethumbshot_size']))
		{
				// error message
				$h->message = $h->lang["post_images_settings_error"];
				$h->messageType = "red";
		} 
		else 
		{
				// update settings and set message
				$h->updateSetting('post_images_settings', serialize($post_images_settings));
				$h->message = $h->lang["main_settings_saved"];
				$h->messageType = "green";
		}
		
		// show message
		$h->showMessage();
		
		return true;
	}
}
?>