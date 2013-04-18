<?php
/**
* name: RSS Autoreader
* description: Enables reading of RSS feeds and populating database
* version: 1.0.1
* folder: autoreader
* class: Autoreader
* type: autoreader
* hooks: install_plugin, admin_header_include, admin_plugin_settings, admin_sidebar_plugin_settings,  autoreader_runcron, admin_theme_index_top, libs_cache, admin_topright
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
* @author    shibuya246 <blog@shibuya246.com>
* @copyright Copyright (c) 2009, Hotaru CMS
* @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
* @link      http://www.hotarucms.org/
*
* Ported from original WP Plugin WP-O-Matic
* Description: Enables administrators to create posts automatically from RSS/Atom feeds.
* Author: Guillermo Rauch
* Plugin URI: http://devthought.com/wp-o-matic-the-wordpress-rss-agreggator/
* Version: 1.0RC4-6
*
* Code modification for hotaru by shibuya246
*/

//require_once(PLUGINS . 'autoreader/autoreader_settings.php');
require_once(PLUGINS . 'autoreader/libs/autoreader_functions.php');    // comment out when using cache libs hook

class Autoreader
{
	var $version = '1.0';
	var $newsetup = true;  // set to true only if this version requires db changes from last version
	var $wpo_help = false;

	/**
	*
	* @param <type> $h
	*/
	public function install_plugin($h)
	{                
		// Default settings
	$autoreader_settings = $h->getSerializedSettings();

	$settings = array( 'wpo_log' => true,
			'wpo_log_stdout' => false,
			'wpo_unixcron' => false,
			'wpo_cacheimages' => 0,
			'wpo_croncode' => substr(md5(time()), 0, 8),
			'wpo_cachepath' => 'cache',
			'wpo_help' => false,
			'wpo_premium' => false
		);

	foreach ($settings as $setting => $value) {
		if (!isset($autoreader_settings[$setting])) { $autoreader_settings[$setting] = $value; }
	}

	$h->updateSetting('autoreader_settings', serialize($autoreader_settings));

		$this->activate($h);
	}


	public function admin_theme_index_top($h) {        
			if ($h->cage->get->testAlnumLines('plugin')  == 'autoreader') {
		if ($h->cage->get->keyExists('template')) {

		//require_once(PLUGINS . 'autoreader/libs/autoreader_functions.php');    // comment out when using cache_libs hook

		$template = $h->cage->get->testAlnumLines('template');
		$h->vars['autoreader']['wpo_help'] = $this->wpo_help;		
		$h->vars['autoreader_settings'] = $this->getOptionSettings($h);

		switch ($template) {
		case 'autoreader_add':
			$this->autoreader_add($h);
			break;
		case 'autoreader_options':
			$this->autoreader_options($h);
			break;
		case 'autoreader_list':
			$this->autoreader_list($h);
			break;
		case 'cron':
			$autoreaderFuncs = new AutoReaderFuncs();
			$autoreaderFuncs->runCron($h, $h->vars['autoreader_settings']['wpo_log']);
			exit; // this is an exit since running a cron job internally, no web display
		default:

		}
		$h->displayTemplate($template);
		exit;
		}
	}
	}
     
	/**
		*
		* @param <type> $h
		*/
	public function autoreader_add($h)
	{
	$id = 0;
	$action = $h->cage->post->testAlnumLines('action');
	$action_get = $h->cage->get->testAlnumLines('action');
	if (!$action) { $action = $action_get; }
	
		$autoreaderFuncs = new AutoReaderFuncs();
		switch ($action) {
		case "edit":
			$autoreaderFuncs = new AutoReaderFuncs();
			$data = $autoreaderFuncs->adminEdit($h);
			echo '<h2>Editing Campaign #' . $data['main']['id'] . ", " .  $data['main']['title'] . '</h2>';
			$h->vars['autoreader']['data'] = $data;		    		    
			break;
		case "save" :
			$errors = $autoreaderFuncs->adminCampaignRequest($h);
			print $errors;
			$arr_errors = json_decode($errors);
			if ($arr_errors->errors == 0) {
			if ( $h->cage->post->keyExists('campaign_edit') ) {
				$cid = $h->cage->post->getInt('campaign_edit');
				$autoreaderFuncs->adminProcessEdit($h,$cid);
			}
			else {
				$autoreaderFuncs->adminProcessAdd($h);
			}
			}
			exit;  // this is an ajax return call, so we don't want any html echoing to the screen
		case "tools" :
			$cid = $h->cage->post->getInt('id');
			$result = $autoreaderFuncs->adminProcessTools($h);
			print $result;
			exit; // this is an ajax return call, so we don't want any html echoing to the screen
		case "test_feed" :
			$data = $h->cage->post->testUri('url');
			$autoreaderFuncs->adminTestfeed($data);
			exit;  // this is an ajax return call, so we don't want any html echoing to the screen
		default :
			echo '<h2>Add New Campaign</h2>';
			$h->vars['autoreader']['data'] = array('main' => array(), 'rewrites' => array(),
				'categories' => array(), 'feeds' => array());		   
		}
	}

	/**
		*
		* @param <type> $h 
		*/
	public function autoreader_options($h)
	{
	$action = $h->cage->post->testAlnumLines('action');

	switch ($action) {
		case "save":
		echo json_encode($h->vars['autoreader_settings']);
		exit;
		case "flush":
		$hook = "autoreader_runcron";
		$cron_data = array('hook'=>$hook);
		$h->pluginHook('cron_flush_hook', 'cron', $cron_data);
		exit;
		default :        

	}
	}

	/**
	*
	* @param <type> $h 
	*/
	public function autoreader_list($h)
	{	
	$action = $h->cage->post->testAlnumLines('action');

	$autoreaderFuncs = new AutoReaderFuncs();

	switch ($action) {
		case "fetch":
		$fetched= $autoreaderFuncs->adminForcefetch($h);
		$array = array('fetched'=> $fetched);
		echo json_encode($array);
		exit;
		case "delete":
		$result = $autoreaderFuncs->adminDelete($h);
		echo $result;
		exit;
		case "reset":
		$result = $autoreaderFuncs->adminReset($h);
		echo $result;
		exit;
	default :

	}
	}

	/**
	* Called when autoreader plugin is first activated
	*
	* @param <type> $h
	* @param <type> $force_install
	*/
	public function activate($h, $force_install = false)
	{

	AutoReaderFuncs::getSettings($h);

	// only re-install if there is new version or plugin has been uninstalled
	if($force_install || ! $h->getPluginVersion() || $h->getPluginVersion() != $this->version)   
	{        
		# autoreader_campaign
		$exists = $h->db->table_exists(str_replace(DB_PREFIX, "", $this->db['campaign']));
		if (!$exists) {
				$h->db->query ( "CREATE TABLE " . $this->db['campaign'] . " (
										id int(11) unsigned NOT NULL auto_increment,
										title varchar(255) NOT NULL default '',
										active tinyint(1) default '1',
										slug varchar(250) default '',
										template MEDIUMTEXT default '',
										frequency int(5) default '180',
										feeddate tinyint(1) default '0',
										cacheimages tinyint(1) default '1',
										posttype enum('new','pending','top') NOT NULL default 'pending',
										authorid int(11) default NULL,
										comment_status enum('open','closed','registered_only') NOT NULL default 'open',
										allowpings tinyint(1) default '1',
										dopingbacks tinyint(1) default '1',
										max smallint(3) default '10',
				trunc smallint(5) default '200',
										linktosource tinyint(1) default '0',
										count int(11) default '0',
										lastactive datetime NOT NULL default '0000-00-00 00:00:00',
										created_on datetime NOT NULL default '0000-00-00 00:00:00',
										PRIMARY KEY (id)
									) ENGINE=" . DB_ENGINE . " DEFAULT CHARSET=" . DB_CHARSET . " COLLATE=" . DB_COLLATE . " COMMENT='autoreader campaign'; "
				);
		}
	else {
		//update for 0.1 versions
		$sql = "ALTER TABLE " . $this->db['campaign'] . " MODIFY COLUMN posttype enum('new','pending','top') NOT NULL default 'pending'";
		$h->db->query($h->db->prepare($sql));

		// update for 0.2 versions
		$exists = $h->db->column_exists('autoreader_campaign', 'trunc');
		if (!$exists) {
		$sql = "ALTER TABLE " . $this->db['campaign'] . " ADD trunc smallint(5) NOT NULL DEFAULT '200' AFTER max";
		$h->db->query($h->db->prepare($sql));
		}
	}

	# autoreader_campaign_category
		$exists = $h->db->table_exists( str_replace(DB_PREFIX, "", $this->db['campaign_category']));
		if (!$exists) {
				$h->db->query ( "CREATE TABLE " . $this->db['campaign_category'] . " (
							id int(11) unsigned NOT NULL auto_increment,
							category_id int(11) NOT NULL,
							campaign_id int(11) NOT NULL,
							PRIMARY KEY  (id)
						) ENGINE=" . DB_ENGINE . " DEFAULT CHARSET=" . DB_CHARSET . " COLLATE=" . DB_COLLATE . " COMMENT='autoreader campaign category'; "
					);
		}

		# autoreader_campaign_feed
		$exists = $h->db->table_exists(str_replace(DB_PREFIX, "", $this->db['campaign_feed']));
		if (!$exists) {
				$h->db->query ( "CREATE TABLE " . $this->db['campaign_feed'] . " (
										id int(11) unsigned NOT NULL auto_increment,
											campaign_id int(11) NOT NULL default '0',
											url varchar(255) NOT NULL default '',
											type varchar(255) NOT NULL default '',
											title varchar(255) NOT NULL default '',
											description varchar(255) NOT NULL default '',
											logo varchar(255) default '',
											count int(11) default '0',
											hash varchar(255) default '',
											lastactive datetime NOT NULL default '0000-00-00 00:00:00',
											PRIMARY KEY  (id)
									) ENGINE=" . DB_ENGINE . " DEFAULT CHARSET=" . DB_CHARSET . " COLLATE=" . DB_COLLATE . " COMMENT='autoreader campaign feed'; "
				);
		}

		# autoreader_campaign_post
		$exists = $h->db->table_exists(str_replace(DB_PREFIX, "", $this->db['campaign_post']));
		if (!$exists) {
				$h->db->query ( "CREATE TABLE " . $this->db['campaign_post'] . " (
									id int(11) unsigned NOT NULL auto_increment,
										campaign_id int(11) NOT NULL,
										feed_id int(11) NOT NULL,
										post_id int(11) NOT NULL,
										hash varchar(255) default '',
										PRIMARY KEY  (id)
								) ENGINE=" . DB_ENGINE . " DEFAULT CHARSET=" . DB_CHARSET . " COLLATE=" . DB_COLLATE . " COMMENT='autoreader campaign post'; "
					);
		}

			# autoreader_campaign_word
			$exists = $h->db->table_exists(str_replace(DB_PREFIX, "", $this->db['campaign_word']));
			if (!$exists) {
				$h->db->query ( "CREATE TABLE " . $this->db['campaign_word'] . " (
										id int(11) unsigned NOT NULL auto_increment,
											campaign_id int(11) NOT NULL,
											word varchar(255) NOT NULL default '',
											regex tinyint(1) default '0',
											rewrite tinyint(1) default '1',
											rewrite_to varchar(255) default '',
											relink varchar(255) default '',
											PRIMARY KEY  (id)
										) ENGINE=" . DB_ENGINE . " DEFAULT CHARSET=" . DB_CHARSET . " COLLATE=" . DB_COLLATE . " COMMENT='autoreader campaign word'; "
					);
			}

			# autoreader_log
			$exists = $h->db->table_exists(str_replace(DB_PREFIX, "", $this->db['log']));
			if (!$exists) {
				$h->db->query ( "CREATE TABLE " . $this->db['log'] . " (
										id int(11) unsigned NOT NULL auto_increment,
											message mediumtext NOT NULL default '',
											created_on datetime NOT NULL default '0000-00-00 00:00:00',
											PRIMARY KEY  (id)
										) ENGINE=" . DB_ENGINE . " DEFAULT CHARSET=" . DB_CHARSET . " COLLATE=" . DB_COLLATE . " COMMENT='autoreader log'; "
					);
			}


				# Options
				WPOTools::addMissingOptions(array(
				'wpo_log'          => array(1, 'Log WP-o-Matic actions'),
				'wpo_log_stdout'   => array(0, 'Output logs to browser while a campaign is being processed'),
				'wpo_unixcron'     => array(WPOTools::isUnix(), 'Use unix-style cron'),
				'wpo_croncode'     => array(substr(md5(time()), 0, 8), 'Cron job password.'),
				'wpo_cacheimages'  => array(0, 'Cache all images. Overrides campaign options'),
				'wpo_cachepath'    => array('cache', 'Cache path relative to wpomatic directory')
				));

			$this->installed = true;
		}
	}


	/**
	* Checks that autoreader tables exist
	*
	* @param <type> $h
	* @return <type>
	*/
	public function tablesExist($h)
	{
		foreach($this->db as $table)
		{
			if(!  $h->db->query("SELECT * FROM {$table}"))
				return false;
		}
		return true;
	}

	/**
	* Get Option Settings, update if required
	*
	*
	*/
	public function getOptionSettings($h, $options = null)
	{
		$autoreader_settings = $h->getSerializedSettings('autoreader');

		if ($h->cage->post->testAlpha('action') == "save" ) {
				$autoreader_settings['wpo_log'] = $h->cage->post->keyExists('option_logging');
				$autoreader_settings['wpo_log_stdout'] =  $h->cage->post->keyExists('option_log_stdout');
				$autoreader_settings['wpo_unixcron'] =  $h->cage->post->keyExists('option_unixcron');
				$autoreader_settings['wpo_croncode'] =  $h->cage->post->keyExists('option_croncode');
				$autoreader_settings['wpo_cacheimages'] = $h->cage->post->keyExists('option_caching');
				$autoreader_settings['wpo_cachepath'] = $h->cage->post->testPage('option_cachepath');

				$h->updateSetting('autoreader_settings', serialize($autoreader_settings),'autoreader');
				$array = array('saved' => 'true');
		}
		else {
				$array = $autoreader_settings;
		}

		return $array;
	}

	/**
	* hook for cron jobs
	*
	* @param <type> $h
	* @param <type> $args
	*/
	public function autoreader_runcron($h, $args) {
	$autoreader_settings = $h->getSerializedSettings('autoreader');
	$h->vars['autoreader_settings'] = $autoreader_settings;
	$cid = $args['id'];
	$autoreaderfuncs = new AutoReaderFuncs();
			$autoreaderfuncs->getSettings($h);
	$this->forcefetched = $autoreaderfuncs->processCampaign($h,$cid);
}

	/**
	* adds hook to use _autoloader
	*
	* @param <type> $h
	* @return <type>
	*/
	public function libs_cache($h) {
	//return "autoreader/libs";
	}

	/**
	*
	* @param <type> $h
	*/
	function admin_topright($h)
	{
	$campaign_id = 11021;

	if ($h->cage->get->testAlpha('plugin') == $h->plugin->folder) {
		echo "<a href='http://www.pledgie.com/campaigns/$campaign_id'><img alt='Click here to lend your support to: Follow Plugin and make a donation at www.pledgie.com !' src='http://www.pledgie.com/campaigns/$campaign_id.png?skin_name=chrome' border='0' /></a>";
		echo "<br/><br/><small>If you appreciate the work of this plugin, help support its continued development by clicking here for a donation.</small>";
	}
	}

}
?>