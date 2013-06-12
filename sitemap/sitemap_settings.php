<?php
/**
 *  Sitemap Settings
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
 * @author    Justin Tiearney <admin@obzerver.com>
 * @copyright Copyright (c) 2009 - 2013, Hotaru CMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      http://obzerver.com
 */
class SitemapSettings extends Sitemap
{

	public function settings($h)
	{
		if ($h->cage->post->getAlpha('submitted') == 'true') { 
			$this->saveSettings($h);
			$h->message = $h->lang["sitemap_settings_saved"];
			$h->messageType = "green alert-success";
			$h->showMessage();
		}else if($h->cage->post->getAlpha('generate') == 'true') {
			$this->createSitemap($h);
			$h->message = $h->lang["sitemap_generated"];
			$h->messageType = "green alert-success";
			$h->showMessage();
		}else if($h->cage->post->getAlpha('newpass') == 'true') {
			$this->newPassword($h);
			$h->message = $h->lang["sitemap_password_generated"];
			$h->messageType = "green alert-success";
			$h->showMessage();
		}else if ($h->cage->post->getAlpha('ping') == 'true') {
			$this->pingSites($h);						
			$h->showMessages();
		}

		//Get settings from database
		$sitemap_settings = $h->getSerializedSettings();

                // TODO
                // Call these as functions in hotaru not raw SQL
                
		//Retrieve the links and last update time from the database
		$sql = "SELECT COUNT(post_id) FROM ". TABLE_POSTS;
		$maps = $h->db->get_var($sql);

		//Retrieve categories from the database
		$sql = "SELECT COUNT(category_name) FROM ". TABLE_CATEGORIES;
		$maps_cat = $h->db->get_var($sql);

		//Retrieve tags from the database
		$sql = "SELECT COUNT(tags_word) FROM ". TABLE_TAGS;
		$maps_tag = $h->db->get_var($sql);
		
                // show header
                echo "<h1>" . $h->lang["sitemap_settings_header"] . "</h1>\n";

		echo "<h3>" . $h->lang['sitemap_configure_sitemap'] . "</h3>\n";
		
		echo '<form name="input" action="'. BASEURL . 'admin_index.php?page=plugin_settings&amp;plugin=sitemap" method="post">';
		print $sitemap_settings['sitemap_compress'];
		echo $h->lang['sitemap_compress'].'<input type="checkbox" name ="sitemap_compress" value="sitemap_compress" ' . $sitemap_settings['sitemap_compress'] . '> <br />';
		echo $h->lang['sitemap_frequency'].'<select name ="sitemap_frequency">
			<option selected="yes">'.$sitemap_settings['sitemap_frequency'].'</option>
				<option>hourly</option><option>daily</option><option>weekly</option>
					<option>monthly</option><option>yearly</option></select> <br />';
		
		// Fetch priorities
		$priorities = $this->getPriorities($h);
		
		// Loop and build select/option
		foreach ( $priorities as $priority) {
			echo $h->lang[$priority].'<select name ="' . $priority . '">';
			$selected = '';
			for ( $i=10,$min=0; $i>$min; $i-- ) {
				$iteration = number_format($i/10, 1);
				$selected = '';
				
				// Check if current iteration = our setting
				if ( floatval($sitemap_settings[$priority]) === floatval($iteration) ) {
					$selected = ' selected="yes"';
				}
				echo '<option'.$selected.'>'.$iteration.'</option>';
			}
			echo '</select> <br />';
		}
		echo "<br />";
		echo '<input type="checkbox" name ="sitemap_include_posts" value="sitemap_include_posts" '.$sitemap_settings['sitemap_include_posts'].'>&nbsp;'.$h->lang['sitemap_include_posts'].' (' . $maps . ')<br />';
		echo '<input type="checkbox" name ="sitemap_include_cats" value="sitemap_include_cats" '.$sitemap_settings['sitemap_include_cats'].'>&nbsp;'.$h->lang['sitemap_include_cats'].' (' . $maps_cat . ')<br />';
		echo '<input type="checkbox" name ="sitemap_include_tags" value="sitemap_include_tags" '.$sitemap_settings['sitemap_include_tags'].'>&nbsp;'.$h->lang['sitemap_include_tags'].' (' . $maps_tag . ')<br />';
		echo "<br />";

		echo '<input type="checkbox" name ="sitemap_cron" value="sitemap_cron" '.$sitemap_settings['sitemap_use_cron'].'>&nbsp;'.$h->lang['sitemap_use_cron'].' <br />';
		echo '<input type="checkbox" name ="sitemap_ping_google" value="sitemap_ping_goolge" '.$sitemap_settings['sitemap_ping_google'].'>&nbsp;'.$h->lang['sitemap_ping_google'].' <br />';
		echo '<input type="checkbox" name ="sitemap_ping_bing" value="sitemap_ping_bing" '.$sitemap_settings['sitemap_ping_bing'].'>&nbsp;'.$h->lang['sitemap_ping_bing'].' <br />';
		echo "<br />";
		
		echo '<input type="hidden" name="submitted" value="true">';
		echo '<input type="hidden" name="generate" value="false">';
		echo '<input type="hidden" name="newpass" value="false">';
		echo '<input type="submit" value="' . $h->lang['sitemap_form_save_settings'] . '" />';
		echo '<input type="hidden" name="csrf" value="' . $h->csrfToken . '" />';
		echo '</form>';
		
		echo "<br />";

		//PING SITEMAP
		echo "<h3>" . $h->lang['sitemap_ping'] . "</h3>\n";

		//Display the last time you ran the sitemap ping tool
		echo $h->lang['sitemap_last_pinged'].' '.$sitemap_settings['sitemap_last_pinged'].'<br />';

		//Allow the user to run the sitemap creation tool
		echo '<form name="input" action="'. BASEURL . 'admin_index.php?page=plugin_settings&amp;plugin=sitemap" method="post">';
		echo '<input type="hidden" name="ping" value="true">';
		echo '<input type="submit" value="' . $h->lang['sitemap_form_ping'] . '" />';
		echo '<input type="hidden" name="csrf" value="' . $h->csrfToken . '" />';
		echo '</form>';

		echo '<br />';

		//GENERATE SITEMAP
		echo "<h3>" . $h->lang['sitemap_generate_sitemap'] . "</h3>\n";
		
		//Print where to find the sitemap
		if(strcmp($sitemap_settings['sitemap_compress'],'checked') == 0) { $sitemap_file = 'sitemap.gz'; } else { $sitemap_file = 'sitemap.xml'; }
		echo $h->lang['sitemap_location'].' <a href="' . $sitemap_settings['sitemap_location'] . $sitemap_file . '" target="_blank">'. $sitemap_settings['sitemap_location'] . $sitemap_file . "</a>";
		echo '<br />';
		
		//Display the last time you ran the sitemap creation tool
		echo $h->lang['sitemap_last_run'].' '.$sitemap_settings['sitemap_last_run'].'<br />';
		
		//Allow the user to run the sitemap creation tool
		echo '<form name="input" action="'. BASEURL . 'admin_index.php?page=plugin_settings&amp;plugin=sitemap" method="post">';
		echo '<input type="hidden" name="submitted" value="false">';
		echo '<input type="hidden" name="generate" value="true">';
		echo '<input type="hidden" name="newpass" value="false">';
		echo '<input type="submit" value="' . $h->lang['sitemap_form_new_sitemap'] . '" />';
		echo '<input type="hidden" name="csrf" value="' . $h->csrfToken . '" />';
		echo '</form>';
		
		echo "<br />";
		
		echo "<h3>" . $h->lang['sitemap_manual_sitemap'] . "</h3>\n";
		echo $h->lang['sitemap_manual_sitemap_note'] . "<p />";

		echo "cron job: 0 0 * * * wget -O - -q -t 1 &quot;".BASEURL."index.php?page=sitemap&passkey=".$sitemap_settings['sitemap_password'].'&quot;<br />';
		//
		echo '<form name="input" action="'. BASEURL . 'admin_index.php?page=plugin_settings&amp;plugin=sitemap" method="post">';
		echo '<input type="hidden" name="submitted" value="false">';
		echo '<input type="hidden" name="generate" value="false">';
		echo '<input type="hidden" name="newpass" value="true">';
		echo '<input type="submit" value="' . $h->lang['sitemap_form_new_password'] . '">';
		echo '<input type="hidden" name="csrf" value="' . $h->csrfToken . '" />';
		echo '</form>';

		echo $h->lang['sitemap_manual_instructions'];
	}
	
	/*
	 * Used to save our plugin settings.
	 * */
	public function saveSettings($h)
	{
		//Get settings from database
		$sitemap_settings = $h->getSerializedSettings();
                
		//Change the compression of the sitemap
		$sitemap_settings['sitemap_compress'] = $h->cage->post->keyExists('sitemap_compress') ? 'checked' : '';

		//Change the frequency of page updates
		if($h->cage->post->keyExists('sitemap_frequency'))
		{
			$sitemap_settings['sitemap_frequency'] = $h->cage->post->getAlpha('sitemap_frequency');
		}
		
		//Use the Cron plugin
		if($h->cage->post->keyExists('sitemap_cron'))
		{
			$sitemap_settings['sitemap_use_cron'] = 'checked';
			
			// set up cron job for sitemap generation:
			$hook = "sitemap_runcron";
			$timestamp = time();
			$recurrence = "daily"; 
			$cron_data = array('timestamp'=>$timestamp, 'recurrence'=>$recurrence, 'hook'=>$hook);
			$h->pluginHook('cron_update_job', 'cron', $cron_data); 
		}
		else
		{
			$sitemap_settings['sitemap_use_cron'] = '';
			
			// delete any existingcron job for sitemaps
			$hook = "sitemap_runcron";
			$cron_data = array('hook'=>$hook);
			$h->pluginHook('cron_delete_job', 'cron', $cron_data);
		}

		// Ping Google, Bing
		$sitemap_settings['sitemap_ping_google'] = $h->cage->post->keyExists('sitemap_ping_google') ? 'checked' : '';
		$sitemap_settings['sitemap_ping_bing'] = $h->cage->post->keyExists('sitemap_ping_bing') ? 'checked' : '';

		// Posts,Cats,Tags
		$sitemap_settings['sitemap_include_posts'] = $h->cage->post->keyExists('sitemap_include_posts') ? 'checked' : '';
		$sitemap_settings['sitemap_include_cats'] = $h->cage->post->keyExists('sitemap_include_cats') ? 'checked' : '';
		$sitemap_settings['sitemap_include_tags'] = $h->cage->post->keyExists('sitemap_include_tags') ? 'checked' : '';
		
		// Get priorities
		$priorities = $this->getPriorities($h);
		
		// Loop, check if exists in post, save
		foreach ( $priorities as $priority ) {
			if( $h->cage->post->keyExists($priority) && $h->cage->post->testFloat($priority) )
			{
				$sitemap_settings[$priority] = $h->cage->post->getRaw($priority);
			}
		}
		
		$h->updateSetting('sitemap_settings', serialize($sitemap_settings));
	}
	
	public function newPassword($h)
	{
		//Get settings from database
		$sitemap_settings = $h->getSerializedSettings();		
		$sitemap_settings['sitemap_password'] = md5(rand());
		$h->updateSetting('sitemap_settings', serialize($sitemap_settings));
	}
	
	private function getPriorities($h)
	{
		return array( 'sitemap_priority_baseurl', 'sitemap_priority_categories', 'sitemap_priority_posts');
	}
}
?>
