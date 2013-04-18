<?php
require_once(PLUGINS . 'autoreader/helper/form.helper.php' );
require_once(PLUGINS . 'autoreader/helper/tag.helper.php');
require_once(PLUGINS . 'autoreader/libs/tools.class.php');  // comment out when using cache_libs hook

class AutoReaderFuncs
{
	public function getSettings($h)
	{
		$h->vars['autoreader_settings'] = $h->getSerializedSettings('autoreader');

		// Table names init
		$this->db = array(
			'campaign'            =>  DB_PREFIX .  'autoreader_campaign',
			'campaign_category'   =>  DB_PREFIX .  'autoreader_campaign_category',
			'campaign_feed'       =>  DB_PREFIX .  'autoreader_campaign_feed',
			'campaign_word'       =>  DB_PREFIX .  'autoreader_campaign_word',
			'campaign_post'       =>  DB_PREFIX .  'autoreader_campaign_post',
			'log'                 =>  DB_PREFIX .  'autoreader_log'
		);

		$this->cachepath = PLUGINS . 'autoreader/' . $h->vars['autoreader_settings']['wpo_cachepath'];

		# Cron command / url
		$this->cron_url = BASEURL . 'admin_index.php?page=plugin_settings&plugin=autoreader&template=cron&code=' .   $h->vars['autoreader_settings']['wpo_croncode'];
		$this->cron_command = '*/20 * * * * '. AutoReaderFuncs::getCommand() . ' ' . $this->cron_url;
	}

	/*
	* Retrieves campaigns from database
	*/
	public function getCampaigns($h, $args = '')
	{
		extract(WPOTools::getQueryArgs($args, array('fields' => '*',
																	'search' => '',
																	'orderby' => 'created_on',
																	'ordertype' => 'DESC',
																	'where' => '',
																	'unparsed' => false,
																	'limit' => null)));

		if(! empty($search))
		$where .= " AND title LIKE '%{$search}%' ";

	//  	if($unparsed)
	//  	  $where .= " AND active = 1 AND (frequency + UNIX_TIMESTAMP(lastactive)) < ". (current_time('timestamp', true) - get_option('gmt_offset') * 3600) . " ";

		$sql = "SELECT $fields FROM " . DB_PREFIX . "autoreader_campaign WHERE 1 = 1 $where "
				. "ORDER BY $orderby $ordertype $limit";

		return $h->db->get_results($sql);
	}

	/*
	* Retrieves feeds for a certain campaign
	*
	* @param   integer   $id     Campaign id
	*/
	public function getCampaignFeeds($h,$id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "autoreader_campaign_feed WHERE campaign_id = %d";
		return $h->db->get_results($h->db->prepare($sql, $id));
	}

	/*
	* Retrieves all posts for a certain campaign
	*
	* @param   integer   $id     Campaign id
	*/
	public function getCampaignPosts($h, $id)
	{
		$sql = "SELECT post_id FROM " . DB_PREFIX . "autoreader_campaign_post WHERE campaign_id = %d";
		return $h->db->get_results($h->db->prepare($sql, $id));
	}

	/*
	* Adds a feed by url and campaign id
	*
	*/
	public function addCampaignFeed($h, $id, $feed)
	{

		$simplepie = $this->fetchFeed($feed, true);
		$url = $h->db->escape($simplepie->subscribe_url());

		// If it already exists, ignore it
		if(! $h->db->get_var("SELECT id FROM " . DB_PREFIX . "autoreader_campaign_feed WHERE campaign_id = $id AND url = '$url' "))
		{
			$h->db->query(WPOTools::insertQuery(DB_PREFIX . 'autoreader_campaign_feed',
			array('url' => $url,
					'title' => $h->db->escape($simplepie->get_title()),
					'description' => $h->db->escape($simplepie->get_description()),
					'logo' =>$h->db->escape($simplepie->get_image_url()),
					'campaign_id' => $id)
			));

			return  $h->db->insert_id;
		}
		return false;
	}

	/*
	* List campaigns section
	*/
	public function adminList($h)
	{
		if(isset($_REQUEST['q']))
		{
			$q = $_REQUEST['q'];
			$campaigns = $this->getCampaigns('search=' . $q);
		}
		else
		{
			$campaigns = $this->getCampaigns('orderby=CREATED_ON');
		}
	}

	/*
	* Add campaign section
	*/
	public function adminAdd($h)
	{
		$data = $this->campaign_structure;
		$data_add = $h->cage->post->testAlnumLines('campaign_add');

		if(isset($data_add))
		{
			if($this->errno)
				$data = $this->campaign_data;
			else
				$addedid = $this->adminProcessAdd();
		}

		$author_usernames = $this->getBlogUsernames();
		$campaign_add = true;
		// include(WPOTPL . 'edit.php');
	}


	/*
	* Edit campaign section
	*/
	public function adminEdit($h)
	{
		$id = $h->cage->get->testInt('id');
		if(!$id) die("Can't be called directly");

		$data = $this->getCampaignData($h, $id);

		// $author_usernames = $this->getBlogUsernames();
		$campaign_edit = true;

		return $data;
	}

	public function adminEditCategories($h, $data, $parent = 0, $level = 0, $categories = 0)
	{
		if ( !$categories )
			$args = array("orderby"=>"category_order", "order"=>"ASC");
		$categories = $h->getCategories($args);

		if ( $categories )
		{
			require_once(LIBS . 'Category.php');
			$catObj = new Category();
			$depth = 1;

			echo "<ul class='categories_widget'>\n";
			echo '<li class="required pad0">';
			echo radiobutton_tag('campaign_categories[]', 1, 'All', 'id=category_1');
			echo "&nbsp;" . label_for('category_1' , 'All') .  "</li>\n";

			foreach ($categories as $cat)
			{
				$cat_level = 1;    // top level category.
				if ($cat->category_safe_name != "all")
				{
					echo '<li class="required pad'.$depth.'">';
					if ($cat->category_parent > 1)
					{
						$depth = $catObj->getCatLevel($h, $cat->category_id, $cat_level, $categories);
						for($i=1; $i<$depth; $i++)
						{
							echo "--- ";
						}
					}
					$category = stripslashes(html_entity_decode(urldecode($cat->category_name), ENT_QUOTES,'UTF-8'));
					echo radiobutton_tag('campaign_categories[]', $cat->category_id, in_array($cat->category_id, $data['categories']), 'id=category_' .$cat->category_id);
					echo "&nbsp;" . label_for('category_' .  $cat->category_id, $category) .  "</li>\n";
				}
			}
			echo "</ul>\n";
		}
		else
		{
			return false;
		}
	}

	/*
	* Resets a campaign (sets post count to 0, forgets last parsed post)
	*/
	public function adminReset($h)
	{
		$id = $h->cage->post->testInt('id');
		if(!$id) die("Can't be called directly");

		// Reset count and lasactive
		$h->db->query(WPOTools::updateQuery(DB_PREFIX . 'autoreader_campaign', array(
			'count' => 0,
			'lastactive' => 0
		), "id = $id"));

		// Reset feeds hashes, count, and lasactive
		foreach($this->getCampaignFeeds($h, $id) as $feed)
		{
			$h->db->query(WPOTools::updateQuery(DB_PREFIX . 'autoreader_campaign_feed', array(
			'count' => 0,
			'lastactive' => 0,
			'hash' => ''
			), "id = {$feed->id}"));
		}

		//no need to delete cron jobs here

		$arr = array('id'=> $id);
		return json_encode(array('id'=>$id));

	}

	/*
	* Deletes a campaign
	*/
	public function adminDelete($h)
	{

		$id = $h->cage->post->testInt('id');
		if(!$id) die("Can't be called directly");

		$h->db->query("DELETE FROM " . DB_PREFIX . "autoreader_campaign WHERE id = $id");
		$h->db->query("DELETE FROM " . DB_PREFIX . "autoreader_campaign_feed WHERE campaign_id = $id");
		$h->db->query("DELETE FROM " . DB_PREFIX . "autoreader_campaign_word WHERE campaign_id = $id");
		$h->db->query("DELETE FROM " . DB_PREFIX . "autoreader_campaign_category WHERE campaign_id = $id");

		//delete any cron jobs for this campaign
		$timestamp = time();
		$hook = "autoreader_runcron";
		$args = array('id'=> $id);
		$cron_data = array('hook'=>$hook, 'args'=>$args);
		$h->pluginHook('cron_delete_job', 'cron', $cron_data);

		$arr = array('id'=> $id);
		return json_encode($arr);

	}

	/*
	* Options section
	*/
	public function adminOptions($h)
	{

		if(isset($_REQUEST['update']))
		{
			update_option('wpo_unixcron',     isset($_REQUEST['option_unixcron']));
			update_option('wpo_log',          isset($_REQUEST['option_logging']));
			update_option('wpo_log_stdout',   isset($_REQUEST['option_logging_stdout']));
			update_option('wpo_cacheimages',  isset($_REQUEST['option_caching']));
			update_option('wpo_cachepath',    rtrim($_REQUEST['option_cachepath'], '/'));

			$updated = 1;
		}

		if(!is_writable($this->cachepath))
			$not_writable = true;

		include(WPOTPL . 'options.php');
	}


	/*
	* Called by cron.php to update the site
	*/
	public function runCron($h, $log = true)
	{
		$this->getSettings($h);
		$code= $h->cage->get->testAlnumLines('code');
		if ($code == $h->vars['autoreader_settings']['wpo_croncode'])
		{
			$this->log($h, 'Running cron job');
			$this->processAll($h);
		}
	}

	/**
	* Finds a suitable command to run cron
	*
	* @return string command
	**/
	public function getCommand()
	{
		$commands = array(
			@WPOTools::getBinaryPath('curl'),
			@WPOTools::getBinaryPath('wget'),
			@WPOTools::getBinaryPath('lynx', ' -dump'),
			@WPOTools::getBinaryPath('ftp')
		);

		return WPOTools::pick($commands[0], $commands[1], $commands[2], $commands[3], '<em>{wget or similar command here}</em>');
	}

	/**
	* Determines what the title has to link to
	*
	* @return string new text
	**/
	public function filterPermalink($h, $url)
	{
		// if from admin panel
		if($this->admin)
			return $url;

		if(get_the_ID())
		{
			$campaignid = (int) get_post_meta(get_the_ID(), 'wpo_campaignid', true);

			if($campaignid)
			{
				$campaign = $this->getCampaignById($campaignid);
				if($campaign->linktosource)
				return get_post_meta(get_the_ID(), 'wpo_sourcepermalink', true);
			}
			return $url;
		}
	}


	/**
	* Processes all campaigns
	*
	*/
	public function processAll($h)
	{
		@set_time_limit(0);

		$campaigns = $this->getCampaigns($h, 'unparsed=1');

		foreach($campaigns as $campaign)
		{
			$this->processCampaign($h, $campaign);
		}
	}

	/**
	* Processes a campaign
	*
	* @param   object    $campaign   Campaign database object
	* @return  integer   Number of processed items
	*/
	public function processCampaign($h, $campaign)
	{
		@set_time_limit(0);
		ob_implicit_flush();

		// Get campaign
		$campaign = is_numeric($campaign) ? $this->getCampaignById($h,$campaign) : $campaign;

		if ($campaign)
		{
			// Log
			$this->log($h, 'Processing campaign ' . $campaign->title . ' (ID: ' . $campaign->id . ')');

			// Get feeds
			$count = 0;
			$feeds = $this->getCampaignFeeds($h, $campaign->id);

			if ($feeds)
			{
				foreach($feeds as $feed)
				$count += $this->processFeed($h, $campaign, $feed);
			}

			$h->db->query(WPOTools::updateQuery(DB_PREFIX . 'autoreader_campaign', array(
				'count' => $campaign->count + $count,
				'lastactive' => current_time('mysql', true)
			), "id = {$campaign->id}"));

			return $count;
		}
	}

	/**
	* Processes a feed
	*
	* @param   $campaign   object    Campaign database object
	* @param   $feed       object    Feed database object
	* @return  The number of items added to database
	*/
	public function processFeed($h, &$campaign, &$feed)
	{

		@set_time_limit(0);

		// Log
		$this->log($h, 'Processing feed ' . $feed->title . ' (ID: ' . $feed->id . ')');

		// Access the feed
		$simplepie = $this->fetchFeed($feed->url, false, $campaign->max);

		// Get posts (last is first)
		$items = array();
		$count = 0;

		foreach($simplepie->get_items() as $item)
		{
			if($feed->hash == $this->getItemHash($item))
			{
				if($count == 0) $this->log($h, 'No new posts');
				break;
			}

			if($this->isDuplicate($h, $campaign, $feed, $item))
			{
				$this->log($h, 'Filtering duplicate post');
				break;
			}

			$count++;
			array_unshift($items, $item);

			if($count == $campaign->max)
			{
				$this->log($h, 'Campaign fetch limit reached at ' . $campaign->max);
				break;
			}
		}

		// Processes post stack
		foreach($items as $item)
		{
			$this->processItem($h, $campaign, $feed, $item);
			$lasthash = $this->getItemHash($item);
		}

		// If we have added items, let's update the hash
		if($count)
		{
			$h->db->query(WPOTools::updateQuery(DB_PREFIX . 'autoreader_campaign_feed', array(
			'count' => $count,
			'lastactive' => time(),//current_time('mysql', true),
			'hash' => $lasthash
			), "id = {$feed->id}"));

			$this->log($h, $count . ' posts added' );
		}

		return $count;
	}


	/**
	* Processes an item
	*
	* @param   $item       object    SimplePie_Item object
	*/
	public function getItemHash($item)
	{
		return sha1($item->get_title() . $item->get_permalink());
	}


	/**
	* Processes an item
	*
	* @param   $campaign   object    Campaign database object
	* @param   $feed       object    Feed database object
	* @param   $item       object    SimplePie_Item object
	*/
	public function processItem($h, &$campaign, &$feed, &$item)
	{
		$this->log($h, 'Processing item');

		// Item content
		$content = $this->parseItemContent($h, $campaign, $feed, $item);

		// Item date
		/* if($campaign->feeddate && ($item->get_date('U') > (current_time('timestamp', 1) - $campaign->frequency) && $item->get_date('U') < current_time('timestamp', 1)))
			$date = $item->get_date('U');
		else
			$date = null;*/

		if($campaign->feeddate)
			$date = $item->get_date('U');
		else
			$date = null;

		// Categories
		$categories = $this->getCampaignData($h, $campaign->id, 'categories');

		// Meta
		$permalink=$item->get_permalink();
		$root=$_SERVER['HTTP_HOST'];

		// $posturl=file_get_contents("/original_url.php?blog=$permalink");
		// $posturl = $this->get_sourceurl($permalink);
		$posturl =  $permalink;

		$meta = array(
			'wpo_campaignid' => $campaign->id,
			'wpo_feedid' => $feed->id,
			'wpo_sourcepermalink' =>$posturl,
	//	  'wpo_website' => $wpo_website
		);

		//tags
		$post_tags=$item->get_categories();

		$tag_list="";
		if($post_tags)
		{
			foreach($post_tags as $post_tag)
			$tag_list.=$post_tag->term.",";
				$tag_list = trim($tag_list,',');
		}

		// Create post
		$postid = $this->insertPost($h, $item->get_title(), $content, $date, $categories, $tag_list, $campaign->posttype, $campaign->authorid, $campaign->allowpings, $campaign->comment_status, $campaign->trunc, $meta);


		/*
		// If pingback/trackbacks
		if($campaign->dopingbacks)
		{
			$this->log('Processing item pingbacks');

			require_once(ABSPATH . WPINC . '/comment.php');
			pingback($content, $postid);
		}
	*/

		// Save post to log database
		$h->db->query(WPOTools::insertQuery(DB_PREFIX . 'autoreader_campaign_post', array(
			'campaign_id' => $campaign->id,
			'feed_id' => $feed->id,
			'post_id' => $postid,
			'hash' => $this->getItemHash($item)
		)));
	}



	/**
	* Processes an item
	*
	* @param   $campaign   object    Campaign database object
	* @param   $feed       object    Feed database object
	* @param   $item       object    SimplePie_Item object
	*/
	public function isDuplicate($h, &$campaign, &$feed, &$item)
	{
		$row = $h->urlExists($item->get_permalink());
		//print "------" .  $item->get_permalink() . " ******          ";
		//print_r($row);
		//$hash = $this->getItemHash($item);
		//$row = $h->db->get_row("SELECT * FROM " . DB_PREFIX . "autoreader_campaign_post "
		//                      . "WHERE campaign_id = {$campaign->id} AND feed_id = {$feed->id} AND hash = '$hash' ");
		return !! $row;
	}

	/**
	* Submit a post to db
	*
	*
	* @param   string    $title            Post title
	* @param   string    $content          Post content
	* @param   integer   $timestamp        Post timestamp
	* @param   array     $category         Array of categories
	* @param   string    $status           'draft', 'published' or 'private'
	* @param   integer   $authorid         ID of author.
	* @param   boolean   $allowpings       Allow pings
	* @param   boolean   $comment_status   'open', 'closed', 'registered_only'
	* @param   array     $meta             Meta key / values
	* @return  integer   Created post id
	*/
	public function insertPost($h, $title, $content, $timestamp = null, $category = null, $tags= null, $status = 'pending', $authorid = null, $allowpings = true, $comment_status = 'open', $truncate_length = 200, $meta = array())
	{
		$date = $timestamp;
		//$date = ($timestamp) ? gmdate('Y-m-d H:i:s', $timestamp + (get_option('gmt_offset') * 3600)) : null;

		$h->post = new Post();

		// Force unique urls for posts with duplicate titles:
		$i = 1;
		$occurrence = "";
		while ($h->titleExists($title . $occurrence))
		{
			$i++;
			$occurrence = " " . $i;
		}
		
		// parse title for problem characters etc.
		$title = $this->makeTitleFriendly($title);
		
		$h->post->url = make_url_friendly($title . $occurrence);
		$h->post->title = $title;
		$h->post->content = truncate($content, $truncate_length);
		$h->post->date = $date;
		$h->post->type = 'news';
		$h->post->category =  $category[0];
		$h->post->tags = $tags;
		$h->post->origUrl = $meta["wpo_sourcepermalink"];
		$h->post->domain = get_domain($meta["wpo_sourcepermalink"]);
		$h->post->author = $authorid;
		$h->post->status = $status;

		if (!$h->urlExists($h->post->origUrl))
		{
			$h->addPost();
		}

		/* $postid = wp_insert_post(array(
			'post_title' 	            => $title,
			'post_content'  	        => $content,
			'post_content_filtered'  	=> $content,
			'post_category'           => $category,
			'post_status' 	          => $status,
			'post_author'             => $authorid,
			'post_date'               => $date,
			'comment_status'          => $comment_status,
			'ping_status'             => $allowpings
		));

			foreach($meta as $key => $value)
				$this->insertPostMeta($postid, $key, $value);
		*/

		$postid = $h->post->vars['last_insert_id'];
		return $postid;
	}
	
	/**
	*	makeTitleFriendly
	*	formats the post title properly in utf-8
	*	@param	string	$title	Post title
	*	@return	string	$title
	*/
	private function makeTitleFriendly($title)
	{
		// parse title for problem entities
		$title = htmlentities($title, ENT_QUOTES, "UTF-8");		
		$problem_chars = array(
										'&#039;' => '%26amp%3B%238217%3B',
										'&#039;' => '&amp;#8217;',
										'&amp;' => '%26amp%3Bamp%3B',
										'...' => '&#8230;',
										'&amp;' => '&amp;amp;',
									 );
									 
		foreach($problem_chars as $true_char => $crap_char)
		{
			$title = str_replace($crap_char, $true_char, $title);
		}
		return $title;
	}

	/**
	* insertPostMeta
	*
	*
	*/
	public function insertPostMeta($h, $postid, $key, $value)
	{
		$result = $h->db->query( "INSERT INTO $h->db->postmeta (post_id,meta_key,meta_value ) "
							. " VALUES ('$postid','$key','$value') ");

		return $h->db->insert_id;
	}


	/**
	* Checks submitted campaign edit form for errors
	*
	*
	* @return array  errors
	*/
	public function adminCampaignRequest($h)
	{
		$data_active = $h->cage->post->testAlnumLines('campaign_active');
		$data_template = $h->cage->post->testAlnumLines('campaign_templatechk');
		$data_cacheimages = $h->cage->post->keyExists('campaign_cacheimages');
		$data_feeddate = $h->cage->post->keyExists('campaign_feeddate');
		$data_allowpings = $h->cage->post->keyExists('campaign_allowpings');
		$data_dopingbacks = $h->cage->post->keyExists('campaign_dopingbacks');
		$data_linktosource =  $h->cage->post->testInt('campaign_linktosource');

		# Main data
		$this->campaign_data = array('main' => array(), 'rewrites' => array(),
					'categories' => array(), 'feeds' => array());
		$this->campaign_data['main'] = array(
			'title'         => $h->cage->post->getHtmLawed('campaign_title'),
			'active'        => $h->cage->post->keyExists('campaign_active'),
			'slug'          => $h->cage->post->testAlnumLines('campaign_slug'),
			'template'      => ($h->cage->post->keyExists('campaign_template'))
										? $data = $h->cage->post->testAlnumLines('campaign_template') : null,
			'frequency'     =>  $h->cage->post->testInt('campaign_frequency_d') * 86400
									+ $h->cage->post->testInt('campaign_frequency_h') //* 3600
									+ $h->cage->post->testInt('campaign_frequency_m') * 60,
			'cacheimages'   => $h->cage->post->keyExists( 'campaign_cacheimages'),
			'feeddate'      => $h->cage->post->keyExists( 'campaign_feeddate'),
			'posttype'      => $h->cage->post->testAlpha('campaign_posttype'),
			'author'        => $h->cage->post->testUsername('campaign_author'),
			'comment_status'=> $h->cage->post->testAlpha('campaign_commentstatus'),
			'allowpings'    => $h->cage->post->keyExists('campaign_allowpings'),
			'dopingbacks'   => $h->cage->post->keyExists('campaign_dopingbacks'),
			'max'           => $h->cage->post->testInt('campaign_max'),
		'trunc'           => $h->cage->post->testInt('campaign_trunc'),
			'linktosource'  => $h->cage->post->keyExists('campaign_linktosource')
		);

		// New feeds
		$results=($h->cage->post->getRaw('campaign_feed/new'));

		foreach( $results as $i => $feed)
		{
			$feed = trim($feed);
			if(!empty($feed))
			{
				if(!isset($this->campaign_data['feeds']['new']))
				$this->campaign_data['feeds']['new'] = array();

				$this->campaign_data['feeds']['new'][$i] = $feed;
			}
		}

		// Existing feeds to delete
		if($h->cage->post->keyExists('/campaign_feed/delete'))
		{
			$this->campaign_data['feeds']['delete'] = array();
			foreach($h->cage->post->getRaw('/campaign_feed/delete') as $feedid => $yes)
			$this->campaign_data['feeds']['delete'][] = intval($feedid);
		}

		// Existing feeds.
		if($h->cage->post->keyExists('campaign_edit'))
		{
			$this->campaign_data['feeds']['edit'] = array();
			foreach($this->getCampaignFeeds($h,intval($h->cage->post->testInt('campaign_edit'))) as $feed)
			$this->campaign_data['feeds']['edit'][$feed->id] = $feed->url;
		}

		// Categories
		if($h->cage->post->keyExists('campaign_categories') )
		{
			foreach($h->cage->post->keyExists('campaign_categories') as $key => $value)
			{
				$this->campaign_data['categories'][] = $value;
			}
		}

		# New categories
		if($h->cage->post->keyExists('campaign_newcat') )
		{
			foreach($h->cage->post->keyExists('campaign_newcat') as $k => $on)
			{
				$catname = $on;
				if(!empty($catname))
				{
					if(!isset($this->campaign_data['categories']['new']))
						$this->campaign_data['categories']['new'] = array();

					$this->campaign_data['categories']['new'][] = $catname;
				}
			}
		}

		// Rewrites
		if($h->cage->post->keyExists('campaign_word_origin') )
		{
			foreach($h->cage->post->keyExists('campaign_word_origin') as $id => $origin_data)
			{
				$rewrite = isset($data['campaign_word_option_rewrite'])
							&& isset($data['campaign_word_option_rewrite'][$id]);
				$relink = isset($data['campaign_word_option_relink'])
							&& isset($data['campaign_word_option_relink'][$id]);

				if($rewrite || $relink)
				{
					$rewrite_data = trim($data['campaign_word_rewrite'][$id]);
					$relink_data = trim($data['campaign_word_relink'][$id]);

					// Relink data field can't be empty
					if(($relink && !empty($relink_data)) || !$relink)
					{
						$regex = isset($data['campaign_word_option_regex'])
								&& isset($data['campaign_word_option_regex'][$id]);

						$data = array();
						$data['origin'] = array('search' => $origin_data, 'regex' => $regex);

						if($rewrite)
						$data['rewrite'] = $rewrite_data;

						if($relink)
						$data['relink'] = $relink_data;

						$this->campaign_data['rewrites'][] = $data;
					}
				}
			}
		}

		$errors = array('errors'=>0, 'basic' => array(), 'feeds' => array(), 'categories' => array(),
							'rewrite' => array(), 'options' => array());
		$this->errno = 0;

		# Main
		if(empty($this->campaign_data['main']['title']))
		{
			$errors['basic'][] = 'You have to enter a campaign title';
			$this->errno++;
		}

		# Feeds
		$feedscount = 0;

		if(isset($this->campaign_data['feeds']['new'])) $feedscount += count($this->campaign_data['feeds']['new']);
		if(isset($this->campaign_data['feeds']['edit'])) $feedscount += count($this->campaign_data['feeds']['edit']);
		if(isset($this->campaign_data['feeds']['delete'])) $feedscount -= count($this->campaign_data['feeds']['delete']);

		if(!$feedscount)
		{
			$errors['feeds'][] ='You have to enter at least one feed';
			$this->errno++;
		}
		else
		{
			if(isset($this->campaign_data['feeds']['new']))
			{
				foreach($this->campaign_data['feeds']['new'] as $feed)
				{
					$simplepie = $this->fetchFeed($feed, true);
					if($simplepie->error())
					{
						$errors['feeds'][] = 'Feed <strong>' . $feed . '</strong> could not be parsed (SimplePie said: ' . $simplepie->error() . ')';
						$this->errno++;
					}
				}
			}
		}

		# Categories
		if(! sizeof($this->campaign_data['categories']))
		{
			$errors['categories'][] ='Select at least one category';
			$this->errno++;
		}

		# Rewrite
		if(sizeof($this->campaign_data['rewrites']))
		{
			foreach($this->campaign_data['rewrites'] as $rewrite)
			{
			if($rewrite['origin']['regex'])
			{
				if(false === @preg_match($rewrite['origin']['search'], ''))
				{
					$errors['rewrites'][] = 'There\'s an error with the supplied RegEx expression';
					$this->errno++;
				}
			}
			}
		}

		# Options
		//Allow blank username as code will allocate currentuser when writing to sql db
		//if(! $h->getUserIdFromName($this->campaign_data['main']['author']))
		//{
		//  $errors['options'][] = 'Author username not found';
		//  $this->errno++;
		//}

		if(! $this->campaign_data['main']['frequency'])
		{
			$errors['options'][] ='Selected frequency is not valid';
			$this->errno++;
		}

		if(! ($this->campaign_data['main']['max'] === 0 || $this->campaign_data['main']['max'] > 0))
		{
			$errors['options'][] ='Max items should be a valid number (greater than zero)';
			$this->errno++;
		}

		if(! ($this->campaign_data['main']['trunc'] === 0 || $this->campaign_data['main']['trunc'] > 0))
		{
			$errors['options'][] ='Max truncated value should be a valid number (greater than zero)';
			$this->errno++;
		}

		//print "cacheimages: " . $this->campaign_data['main']['cacheimages'];
		if($this->campaign_data['main']['cacheimages'] && !is_writable( $this->cachepath))
		{
			$errors['options'][] = 'Cache path must be present and writable before enabling image caching.';
			$this->errno++;
		}

		$errors['errors'] = $this->errno;
		$this->errors = $errors;

		return json_encode($errors);

		//exit;
	}

	/**
	* Creates a campaign, and runs processEdit. If processEdit fails, campaign is removed
	*
	* @return campaign id if created successfully, errors if not
	*/
	public function adminProcessAdd($h)
	{
		// Insert a campaign with dumb data
		$h->db->query(WPOTools::insertQuery(DB_PREFIX . 'autoreader_campaign', array('lastactive' => 0, 'count' => 0)));
		$cid = $h->db->insert_id;

		// Process the edit
		$this->campaign_data['main']['lastactive'] = 0;
		$this->adminProcessEdit($h,$cid);
		return $cid;
	}

	/**
	* Cleans everything for the given id, then redoes everything
	*
	* @param integer $id           The id to edit
	*/
	public function adminProcessEdit($h,$id)
	{
		// If we need to execute a tool action we stop here
		//if($this->adminProcessTools($h)) return;

		// Delete all to recreate
		$h->db->query("DELETE FROM " . DB_PREFIX . "autoreader_campaign_word WHERE campaign_id = $id");
		$h->db->query("DELETE FROM " . DB_PREFIX . "autoreader_campaign_category WHERE campaign_id = $id");

		// Process categories
		# New
		if(isset($this->campaign_data['categories']['new']))
		{
			foreach($this->campaign_data['categories']['new'] as $category)
			$this->campaign_data['categories'][] = wp_insert_category(array('cat_name' => $category));

			unset($this->campaign_data['categories']['new']);

		// print "new campaign";
		}

		# All
		foreach($this->campaign_data['categories'] as $category)
		{
			// Insert
			$h->db->query(WPOTools::insertQuery(DB_PREFIX . 'autoreader_campaign_category',
			array('category_id' => $category,
					'campaign_id' => $id)
			));
		}

		// Process feeds
		# New
		if(isset($this->campaign_data['feeds']['new']))
		{
			foreach($this->campaign_data['feeds']['new'] as $feed)
			$this->addCampaignFeed($h, $id, $feed);
		}

		# Delete
		if(isset($this->campaign_data['feeds']['delete']))
		{
			foreach($this->campaign_data['feeds']['delete'] as $feed)
			$h->db->query("DELETE FROM " . DB_PREFIX . "autoreader_campaign_feed WHERE id = $feed ");
		}

		// Process words
		foreach($this->campaign_data['rewrites'] as $rewrite)
		{
			$h->db->query(WPOTools::insertQuery(DB_PREFIX . 'autoreader_campaign_word',
			array('word' => $rewrite['origin']['search'],
					'regex' => $rewrite['origin']['regex'],
					'rewrite' => isset($rewrite['rewrite']),
					'rewrite_to' => isset($rewrite['rewrite']) ? $rewrite['rewrite'] : '',
					'relink' => isset($rewrite['relink']) ? $rewrite['relink'] : null,
					'campaign_id' => $id)
			));
		}

		// Main
		$main = $this->campaign_data['main'];

		//Test author name
		$authorname =  $this->campaign_data['main']['author'];
		$userid = $h->getUserIdFromName($authorname);
		if ($userid)
		{
			$main['authorid'] =$userid;  } else { $main['authorid'] = $h->currentUser->id;
		}
		unset($main['author']);

		// Query
		$query = WPOTools::updateQuery(DB_PREFIX . 'autoreader_campaign', $main, 'id = ' . intval($id));
		$h->db->query($query);

		if ($this->campaign_data['main']['active'])
		{
			//get data ready to pass to cron plugin
			$timestamp = time();
			switch ($this->campaign_data['main']['frequency'])
			{
				case 43200 : $recurrence = "twicedaily"; break;
				case 86400 : $recurrence = "daily"; break;
				case 302400 : $recurrence = "weekly"; break;
				default : $recurrence = "hourly"; break;
			}
			$hook = "autoreader_runcron";
			$args = array('id'=> $id);
			$cron_data = array('timestamp'=>$timestamp, 'recurrence'=>$recurrence, 'hook'=>$hook, 'args'=>$args);
			$h->pluginHook('cron_update_job', 'cron', $cron_data);
		}
		else
		{
			$hook = "autoreader_runcron";
			$args = array('id'=> $id);
			$cron_data = array('hook'=>$hook, 'args'=>$args);
			$h->pluginHook('cron_delete_job', 'cron', $cron_data);
		}

		// campaign_frequency_d
		// campaign_frequency_h
		// campaign_frequency_m
	}

	/**
	* Processes edit campaign tools actions
	*
	*
	*/
	public function adminProcessTools($h)
	{
		$id = $h->cage->post->testInt('id');
		$count = 0;

		if($h->cage->post->testAlnumLines('tool') == 'tool_removeall')
		{
			$posts = $this->getCampaignPosts($h,$id);

			if ($posts)
			{
				foreach($posts as $post)
				{
					$count++;
					$h->post->id = $post->post_id;
					$h->deletePost();
				}
			}

			// Delete log
			$h->db->query("DELETE FROM " . DB_PREFIX . "autoreader_campaign_post WHERE campaign_id = {$id} ");

			// Update feed and campaign posts count
			$h->db->query(WPOTools::updateQuery(DB_PREFIX . 'autoreader_campaign', array('count' => 0), "id = {$id}"));
			$h->db->query(WPOTools::updateQuery(DB_PREFIX . 'autoreader_campaign_feed', array('hash' => 0, 'count' => 0), "campaign_id = {$id}"));

			$this->tool_success = 'All posts removed';
			$result = array('result' => $count . ' posts were succesfully deleted.');
			return json_encode($result);
		}

		if($h->cage->post->testAlnumLines('tool') == 'tool_changetype')
		{
			$this->adminUpdateCampaignPosts($h, $id, array(
			'post_status' => $h->cage->post->testAlpha('campaign_tool_changetype')
			));

			$result = array('result' => 'Posts were changed.');
			return json_encode($result);
		}

		//Test author name
		//    $authorname =  $this->campaign_data['main']['author'];
		//    $userid = $h->getUserIdFromName($authorname);
		//    if ($userid) { $main['authorid'] =$userid;  } else { $main['authorid'] = $h->currentUser->id;  }
		//    unset($main['author']);

		if($h->cage->post->testAlnumLines('tool') == 'tool_changeauthor')
		{
			$authorname = $h->cage->post->testAlnumLines('campaign_tool_changeauthor');
			$userid = $h->getUserIdFromName($authorname);
			if ($userid)
			{
				$main['authorid'] =$userid;
			}
			else
			{
				$main['authorid'] = $h->currentUser->id;
			}

			if($userid)
			{
				$this->adminUpdateCampaignPosts($h, $id, array('post_author' => $userid));
				$result = array('result' => 'Posts were changed.');
			}
			else
			{
				$result = array("error" => "That username could not be found.");
			}
			return json_encode($result);
		}

		$error = array("error" => "There was an error. No tools could be run");
		return json_encode($error);
	}

	public function adminUpdateCampaignPosts($h,$id, $properties)
	{
		$posts = $this->getCampaignPosts($h, $id);

		foreach($posts as $post)
			$h->db->query(WPOTools::updateQuery(DB_PREFIX . 'posts', $properties, "post_id = $post->post_id"));
	}

	/**
	* Parses an item content
	*
	* @param   $campaign       object    Campaign database object
	* @param   $feed           object    Feed database object
	* @param   $item           object    SimplePie_Item object
	*/
	public function parseItemContent($h, &$campaign, &$feed, &$item)
	{
		$content = $item->get_content();

		// Caching
		//    if ($h->vars['autoreader']['settings']['wpo_cacheimages'] || $campaign->cacheimages)   // set override here for all campaigns  get_option('wpo_cacheimages')
		//    {
		//      $images = WPOTools::parseImages($content);
		//      $urls = $images[2];
		//
		//      if(sizeof($urls))
		//      {
		//        $this->log($h, 'Caching images');
		//
		//        foreach($urls as $url)
		//        {
		//          $newurl = $this->cacheRemoteImage($h, $url);
		//          if($newurl)
		//            $content = str_replace($url, $newurl, $content);
		//        }
		//      }
		//    }

		// cut images here
		preg_replace("/<img[^>]+\>/i", "", $content);

		// Template parse
		$vars = array(
			'{content}',
			'{title}',
			'{permalink}',
			'{feedurl}',
			'{feedtitle}',
			'{feedlogo}',
			'{campaigntitle}',
			'{campaignid}',
			'{campaignslug}'
		);

		$replace = array(
			$content,
			$item->get_title(),
			$item->get_link(),
			$feed->url,
			$feed->title,
			$feed->logo,
			$campaign->title,
			$campaign->id,
			$campaign->slug
		);

		$content = str_ireplace($vars, $replace, ($campaign->template) ? $campaign->template : '{content}');

		// Rewrite
		$rewrites = $this->getCampaignData($h, $campaign->id, 'rewrites');
		foreach($rewrites as $rewrite)
		{
			$origin = $rewrite['origin']['search'];

			if(isset($rewrite['rewrite']))
			{
			$reword = isset($rewrite['relink'])
							? '<a href="'. $rewrite['relink'] .'">' . $rewrite['rewrite'] . '</a>'
							: $rewrite['rewrite'];

			if($rewrite['origin']['regex'])
			{
				$content = preg_replace($origin, $reword, $content);
			} else
				$content = str_ireplace($origin, $reword, $content);
			} else if(isset($rewrite['relink']))
			$content = str_ireplace($origin, '<a href="'. $rewrite['relink'] .'">' . $origin . '</a>', $content);
		}

		return $content;
	}

	/**
	* Cache remote image
	*
	* @return string New url
	*/
	public function cacheRemoteImage($h, $url)
	{
		$contents = @file_get_contents($url);

		$url=explode("?", $url );
		$url=$url[0];
		$filename = substr(md5(time()), 0, 5) . '_' . basename($url);

		$cachepath = $this->cachepath;

		if(is_writable($cachepath) && $contents)
		{
			file_put_contents($cachepath . '/' . $filename, $contents);
			return $this->pluginpath . '/' . $h->vars['autoreader_settings']['wpo_cachepath'] . '/' . $filename;
		}
		return false;
	}

	/**
	* Parses a feed with SimplePie
	*
	* @param   boolean     $stupidly_fast    Set fast mode. Best for checks
	* @param   integer     $max              Limit of items to fetch
	* @return  SimplePie_Item    Feed object
	**/
	public function fetchFeed($url, $stupidly_fast = false, $max = 0)
	{
		# SimplePie
		if(! class_exists('SimplePie'))
			require_once( LIBS . 'extensions/SimplePie/simplepie.inc' );

		$feed = new SimplePie();
		$feed->enable_order_by_date(false); // thanks Julian Popov
		$feed->set_feed_url($url);
		$feed->set_item_limit($max);
		$feed->set_stupidly_fast($stupidly_fast);
		$feed->enable_cache(false);
		$feed->init();
		$feed->handle_content_type();

		return $feed;
	}

	/**
	* Returns all blog usernames (in form [user_login => display_name (user_login)] )
	*
	* @return array $usernames
	**/
	public function getBlogUsernames()
	{
		$return = array();
		$users = get_users_of_blog();

		foreach($users as $user)
		{
			if($user->display_name == $user->user_login)
			$return[$user->user_login] = "{$user->display_name}";
			else
			$return[$user->user_login] = "{$user->display_name} ({$user->user_login})";
		}

		return $return;
	}


	/**
	* Returns all data for a campaign
	*
	*
	*/
	public function getCampaignData($h, $id, $section = null)
	{
		$campaign = (array) $this->getCampaignById($h, $id);
		if($campaign)
		{
			$campaign_data = $campaign_structure = array('main' => array(), 'rewrites' => array(),
												'categories' => array(), 'feeds' => array());

			// Main
			if(!$section || $section == 'main')
			{
				$campaign_data['main'] = array_merge($campaign_data['main'], $campaign);
				$userdata = $h->getUserNameFromId($campaign_data['main']['authorid']);
				$campaign_data['main']['author'] = $userdata;
			}

			// Categories
			if(!$section || $section == 'categories')
			{
				$categories = $h->db->get_results("SELECT * FROM " . DB_PREFIX . "autoreader_campaign_category WHERE campaign_id = $id");
				if ($categories)
				{
					foreach($categories as $category)
					$campaign_data['categories'][] = $category->category_id;
				}
			}

			// Feeds
			if(!$section || $section == 'feeds')
			{
				$campaign_data['feeds']['edit'] = array();

				$feeds = $this->getCampaignFeeds($h, $id);
				if ($feeds)
				{
					foreach($feeds as $feed)
						$campaign_data['feeds']['edit'][$feed->id] = $feed->url;
				}
			}

			// Rewrites
			if(!$section || $section == 'rewrites')
			{
				$rewrites = $h->db->get_results("SELECT * FROM " . DB_PREFIX . "autoreader_campaign_word WHERE campaign_id = $id");
				if ($rewrites)
				{
					foreach($rewrites as $rewrite)
					{
						$word = array('origin' => array('search' => $rewrite->word, 'regex' => $rewrite->regex), 'rewrite' => $rewrite->rewrite_to, 'relink' => $rewrite->relink);

						if(! $rewrite->rewrite) unset($word['rewrite']);
						if(empty($rewrite->relink)) unset($word['relink']);

						$campaign_data['rewrites'][] = $word;
					}
				}
			}

			if($section)
			return $campaign_data[$section];

			return $campaign_data;
		}

		return false;
	}

	/**
	* Retrieves logs from database
	*
	*
	*/
	public function getLogs($h, $args = '')
	{
		extract(WPOTools::getQueryArgs($args, array('orderby' => 'created_on',
																	'ordertype' => 'DESC',
																	'limit' => null,
																	'page' => null,
																	'perpage' => null)));
		if(!is_null($page))
		{
			if($page == 0) $page = 1;
			$page--;

			$start = $page * $perpage;
			$end = $start + $perpage;
			$limit = "LIMIT {$start}, {$end}";
		}
		$sql = "SELECT * FROM " . DB_PREFIX . "autoreader_log ORDER BY $orderby $ordertype $limit";

		return $h->db->get_results($h->db->prepare($sql));
	}

	/**
	* Retrieves a campaign by its id
	*
	*/
	public function getCampaignById($h, $id)
	{
		$id = intval($id);
		return $h->db->get_row("SELECT * FROM " . DB_PREFIX . "autoreader_campaign WHERE id = $id");
	}

	/**
	* Retrieves a feed by its id
	*
	*/
	public function getFeedById($h, $id)
	{
		$id = intval($id);
		return $h->db->get_row("SELECT * FROM " . DB_PREFIX . "autoreader_campaign_feed WHERE id = $id");
	}


	/**
	* Returns how many seconds left till reprocessing
	*
	* @return seconds
	**/
	public function getCampaignRemaining(&$campaign, $gmt = 0)
	{
		return mysql2date('U', $campaign->lastactive) + $campaign->frequency - time() + (GMT_OFFSET * 3600);
	}


	/**
	* Tests a feed
	*/
	public function adminTestfeed($url)
	{

		//if(!isset($_REQUEST['url'])) return false;

		// $url = $_REQUEST['url'];
		$feed = $this->fetchFeed($url, true);
		$works = ! $feed->error(); // if no error returned

		if($works):
			$json_array = array('result'=>'ok', 'url'=>$feed->feed_url );
		else:
			$json_array = array('result'=>'fail', 'error'=>$works );
		endif ;

		echo json_encode($json_array);

	}


	/**
	* Forcefully processes a campaign
	*/
	public function adminForcefetch($h)
	{
		$cid = $h->cage->post->testInt('id');
		$this->forcefetched = $this->processCampaign($h,$cid);
		return $this->forcefetched;
	}


	/*
	* Saves a log message to database
	*
	* @param string  $message  Message to save
	*/
	public function log($h, $message)
	{
		$autoreader_settings = $h->getSerializedSettings('autoreader');

		if ($autoreader_settings['wpo_log_stdout'])
				echo $message;

		if ($autoreader_settings['wpo_log'])
		{
			$message = $h->db->escape($message);
			$time = gmdate('Y-m-d H:i:s');// time(); // current_time('mysql', true);
			$h->db->query("INSERT INTO " . DB_PREFIX . "autoreader_log (message, created_on) VALUES ('{$message}', '{$time}') ");
		}
	}

	/*
	*
	* @param string $text String to truncate.
	* @param integer $length Length of returned string, including ellipsis.
	* @param string $ending Ending to be appended to the trimmed string.
	* @param boolean $exact If false, $text will not be cut mid-word
	* @param boolean $considerHtml If true, HTML tags would be handled correctly
	* @return string Trimmed string.
	* http://dodona.wordpress.com/2009/04/05/how-do-i-truncate-an-html-string-without-breaking-the-html-code/
	*/
	function truncate($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true)
	{
		if ($considerHtml)
		{
			// if the plain text is shorter than the maximum length, return the whole text
			if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length)
			{
				return $text;
			}
			// splits all html-tags to scanable lines
			preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
			$total_length = strlen($ending);
			$open_tags = array();
			$truncate = '';
			foreach ($lines as $line_matchings)
			{
				// if there is any html-tag in this line, handle it and add it (uncounted) to the output
				if (!empty($line_matchings[1]))
				{
					// if it's an "empty element" with or without xhtml-conform closing slash
					if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1]))
					{
					// do nothing
					// if tag is a closing tag
					}
					else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings))
					{
						// delete tag from $open_tags list
						$pos = array_search($tag_matchings[1], $open_tags);
						if ($pos !== false) 
						{
							unset($open_tags[$pos]);
						}
					// if tag is an opening tag
					} 
					else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) 
					{
						// add tag to the beginning of $open_tags list
						array_unshift($open_tags, strtolower($tag_matchings[1]));
					}
					// add html-tag to $truncate'd text
					$truncate .= $line_matchings[1];
				}
				// calculate the length of the plain text part of the line; handle entities as one character
				$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length+$content_length> $length) 
				{
					// the number of characters which are left
					$left = $length - $total_length;
					$entities_length = 0;
					// search for html entities
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) 
					{
						// calculate the real length of all entities in the legal range
						foreach ($entities[0] as $entity) 
						{
							if ($entity[1]+1-$entities_length <= $left) 
							{
								$left--;
								$entities_length += strlen($entity[0]);
							} 
							else 
							{
								// no more characters left
								break;
							}
						}
					}
					$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
					// maximum lenght is reached, so get off the loop
					break;
				} 
				else 
				{
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}
				// if the maximum length is reached, get off the loop
				if($total_length>= $length) 
				{
					break;
				}
			}
		}
		else 
		{
			if (strlen($text) <= $length) 
			{
				return $text;
			} 
			else 
			{
				$truncate = substr($text, 0, $length - strlen($ending));
			}
		}
		// if the words shouldn't be cut in the middle...
		if (!$exact) 
		{
			// ...search the last occurance of a space...
			$spacepos = strrpos($truncate, ' ');
			if (isset($spacepos)) 
			{
				// ...and cut the text in this position
				$truncate = substr($truncate, 0, $spacepos);
			}
		}
		// add the defined ending to the text
		$truncate .= $ending;
		if($considerHtml) 
		{
			// close all unclosed html-tags
			foreach ($open_tags as $tag) 
			{
				$truncate .= '</' . $tag . '>';
			}
		}
		return $truncate;
	}

}
?>