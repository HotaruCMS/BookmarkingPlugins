<?php
/**
* name: Post Images
* description: Add images to your posts
* version: 1.5
* folder: post_images
* class: PostImages
* type: post_images
* hooks: install_plugin, admin_sidebar_plugin_settings, admin_plugin_settings, submit_2_fields, header_include_raw, post_read_post, submit_functions_process_submitted, post_add_post, post_update_post, pre_show_post, header_include, theme_index_top, footer, show_post_pre_title
* author: Matthis de Wit
* authorurl: http://fourtydegrees.nl/ties
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


class PostImages
{
	/**
	* Install Post Images
	*/
	public function install_plugin($h)
	{
		$exists = $h->db->column_exists('posts', 'post_img');
		if (!$exists) {
				$h->db->query("ALTER TABLE " . TABLE_POSTS . " ADD post_img TEXT NOT NULL DEFAULT '' AFTER post_comments");
		}
		$post_images_settings = $h->getSerializedSettings();

		if (!isset($post_images_settings['w'])) { $post_images_settings['w'] = 75; }
		if (!isset($post_images_settings['h'])) { $post_images_settings['h'] = 75; }
		if (!isset($post_images_settings['quality'])) { $post_images_settings['quality'] = 90; }
		if (!isset($post_images_settings['memory'])) { $post_images_settings['memory'] = '16M'; }
		if (!isset($post_images_settings['default'])) { $post_images_settings['default'] = 'no'; }
		if (!isset($post_images_settings['default_url'])) { $post_images_settings['default_url'] = SITEURL . 'content/images/post_images/default.jpg'; }
		if (!isset($post_images_settings['wepsnapr_key'])) { $post_images_settings['sitethumbshot_key'] =''; }
		if (!isset($post_images_settings['wepsnapr_size'])) { $post_images_settings['sitethumbshot_size'] = 'T'; }
		if (!isset($post_images_settings['show_in_related_posts'])) { $post_images_settings['show_in_related_posts'] = 'unchecked'; }
		if (!isset($post_images_settings['show_in_posts_widget'])) { $post_images_settings['show_in_posts_widget'] = 'unchecked'; }
		if (!isset($post_images_settings['post_images_pullRight'])) { $post_images_settings['post_images_pullRight'] = 'unchecked'; }		
                
                $h->updateSetting('post_images_settings', serialize($post_images_settings));

		$folder = BASE . '/content/images/post_images/';
		if(!is_dir($folder)){
			if(mkdir($folder,0777,true)) $h->messages['Image folder created'] = 'green';
			else $h->messages['There is no folder, and I could not create one. Please create one manually.'] = 'red';
		}
		else if(!is_writable($folder)){
			if(chmod("/somedir/somefile", 777)) $h->messages["Image folder found and made writable"] = "green";
			else $h->messages["Image folder permissions could not be set, please manually make it writable."] = 'red';
		}
		else {
			//
		}		
	}
	/**
	* Include CSS and JavaScript files for this plugin
	*/
	public function header_include($h)
	{
		// include a files that match the name of the plugin folder:
                $h->includeCss('post_images');                                        
	}  
        
        
        public function footer($h)
        {
                if (($h->pageName != 'submit2') && ($h->pageName != 'edit_post')) { return false; }
                $h->displayTemplate('image_script');
                echo "<script type='text/javascript' src='".SITEURL."content/plugins/post_images/javascript/jquery.Jcrop.min.js'></script>";
        }
        
        
	/**
	* Read post media if post_id exists.
	*/
	public function post_read_post($h){
		if (!isset($h->post->vars['post_row']->post_img)) { return false; }
		$h->post->vars['img'] = $h->post->vars['post_row']->post_img;
	}
	/**
	* Add a media field to submit form 2 and edit post page
	*/
	public function submit_2_fields($h)
	{
		if (!isset($h->post->vars['img'])) { 
				if (isset($h->vars['submitted_data']['submit_img'])) { 
					$h->post->vars['img'] = urldecode($h->vars['submitted_data']['submit_img']);
				} else {
				switch ($h->vars['post_images_settings']['default']){
					case 'sitethumbshot' :
						$h->post->vars['img'] = 'http://images.sitethumbshot.com/?size='.$h->vars['post_images_settings']['sitethumbshot_size'].'&key='.$h->vars['post_images_settings']['sitethumbshot_key'].'&url='.$h->vars['submitted_data']['submit_orig_url'];
						break;
					case 'url' :
						$h->post->vars['img'] = $h->vars['post_images_settings']['default_url'];
						break;
					default :
						$h->post->vars['img'] = '';
				}
				}
			if (isset($h->vars['submitted_data']['submit_img_coords'])) { 
					$h->post->vars['img_coords'] = $h->vars['submitted_data']['submit_img_coords'];
				} else {
					$h->post->vars['img_coords'] = '';
				}
		}
		$h->displayTemplate('form_field');
	}
	/**
	* Include jQuery for hiding and showing email options in plugin settings
	*/
	public function header_include_raw($h)
	{
		if (($h->pageName != 'submit2') && ($h->pageName != 'edit_post')) { return false; }		
		echo "<link rel='stylesheet' href='".SITEURL."content/plugins/post_images/css/jquery.Jcrop.css' type='text/css' />";
		$h->displayTemplate('image_script');
	}                        
        
	/**
	* Check and update post_submit in Submit step 2 and Post Edit pages
	*/
	public function submit_functions_process_submitted($h)
	{
		if (($h->pageName != 'submit2') && ($h->pageName != 'edit_post')) { return false; }
		$h->vars['post_images_settings'] = $h->getSerializedSettings();
		if ($h->cage->post->keyExists('post_img')) {
				$h->post->vars['img'] = $h->cage->post->getHtmLawed('post_img');
		} else {
				switch ($h->vars['post_images_settings']['default']){
					case 'sitethumbshot' :
						$h->post->vars['img'] = 'http://images.sitethumbshot.com/?size='.$h->vars['post_images_settings']['sitethumbshot_size'].'&key='.$h->vars['post_images_settings']['sitethumbshot_key'].'&url='.$h->vars['submitted_data']['submit_orig_url'];
						break;
					case 'url' :
						$h->post->vars['img'] = $h->vars['post_images_settings']['default_url'];
						break;
					default :
						$h->post->vars['img'] = '';
					}
		}
		if ($h->cage->post->keyExists('post_img_coords')) {
				$h->post->vars['img_coords'] = $h->cage->post->getHtmLawed('post_img_coords');
		} else {
				$h->post->vars['img_coords'] =  ''; // default
		}

		//$h->vars['submitted_data']['submit_img'] = $h->post->vars['img'];
		//$h->vars['submitted_data']['submit_img_coords'] = $h->post->vars['img_coords'];
		$h->vars['submitted_data']['submit_img'] = urlencode($this->cropImage($h));
		$h->vars['submitted_data']['submit_img_coords'] = '';
	}


	/**
	* Add media in the posts table
	*/
	public function post_add_post($h)
	{
		if (!isset($h->vars['submitted_data']['submit_img'])) { return false; }

		$h->post->vars['img'] = $h->vars['submitted_data']['submit_img'];
		
		$sql = "UPDATE " . TABLE_POSTS . " SET post_img = %s WHERE post_id = %d";
		$h->db->query($h->db->prepare($sql, urlencode($h->post->vars['img']), $h->post->vars['last_insert_id']));
	}


	/**
	* Update media in the posts table
	*/
	public function post_update_post($h)
	{
		if (!isset($h->vars['submitted_data']['submit_img'])) { return false; }

		$h->post->vars['img'] = $h->vars['submitted_data']['submit_img'];
		
		$sql = "UPDATE " . TABLE_POSTS . " SET post_img = %s WHERE post_id = %d";
		$h->db->query($h->db->prepare($sql, urlencode($h->post->vars['img']), $h->post->id));
	}


	/**
	* Add to list view
	*/
	public function pre_show_post($h){
		//$h->displayTemplate('display_image','',false);
	}

        /**
	* Add to list view
	*/
	public function show_post_pre_title($h){
		$h->displayTemplate('display_image','',false);
	}
        

	/**
	* Crop function
	*/
	private function cropImage($h){
		//$h->vars['post_images_settings'] = $h->getSerializedSettings();
		$src = $h->post->vars['img'];
                
                if (!$src) return false;
                
		if(strstr($src,'http://images.sitethumbshot.com/'))
                        return $src;
		else if(strstr($src,'http://') === false && file_exists(BASE.'content/images/post_images/'.$src))
                        $src = BASE.'content/images/post_images/'.$src;
                
		$src = html_entity_decode($src, ENT_QUOTES,'UTF-8');
		$src = str_replace(" ","%20",$src);
		$get_info = getimagesize($src);
		if(!$get_info) return '';
		$file_mime = $get_info['mime'];
		ini_set('memory_limit',$h->vars['post_images_settings']['memory']);
		if($h->vars['post_images_settings']['default'] == 'sitethumbshot'){
			if($h->vars['post_images_settings']['sitethumbshot_size'] == 'T'){
				$h->vars['post_images_settings']['h'] = 70;
				$h->vars['post_images_settings']['w'] = 90;
			}
			if($h->vars['post_images_settings']['sitethumbshot_size'] == 'S'){
				$h->vars['post_images_settings']['h'] = 90;
				$h->vars['post_images_settings']['w'] = 120;
			}
			if($h->vars['post_images_settings']['sitethumbshot_size'] == 'M'){
				$h->vars['post_images_settings']['h'] = 150;
				$h->vars['post_images_settings']['w'] = 200;
			}
		}
		$targ_w = $h->vars['post_images_settings']['w'];
		$targ_h = $h->vars['post_images_settings']['h'];
		$jpeg_quality = $h->vars['post_images_settings']['quality'];
		
		$image_name = preg_replace('/\W+/','_',$h->vars['submitted_data']['submit_title']) . '_' . str_replace('.','_',microtime(true)) . '.jpg';
		$target = 'content/images/post_images/' . urlencode($image_name);
		
		// read in the passed image
		if(substr($file_mime,-3)=="gif" || substr($file_mime,-3)=="GIF"){
			$img_r = imagecreatefromgif("$src"); // Attempt to open
		}
		else if(substr($file_mime,-3)=="png" || substr($file_mime,-3)=="PNG"){
			$img_r = imagecreatefrompng ("$src"); // Attempt to open
		}
		else {
			$img_r = imagecreatefromjpeg ("$src"); // Attempt to open
		}

//		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );
		
		$srcW = imagesx($img_r);
		$srcH = imagesy($img_r);
		$coords = array();
		$coordsArray = explode(' , ',$h->post->vars['img_coords']);
//		print_r($coordsArray);
		$destX = (int)$coordsArray[0]/(int)$coordsArray[4]*$srcW;
		$destY = (int)$coordsArray[1]/(int)$coordsArray[5]*$srcH;
		$destW = (int)$coordsArray[2]/(int)$coordsArray[4]*$srcW;
		$destH = (int)$coordsArray[3]/(int)$coordsArray[5]*$srcH;
		
		imagecopyresampled($dst_r,$img_r,0,0,$destX,$destY,$targ_w,$targ_h,$destW,$destH);
		imagejpeg($dst_r, BASE.$target , $jpeg_quality);
		if(strstr($src, SITEURL . 'content/images/post_images/') && $src != $h->vars['post_images_settings']['default_url']){
			unlink(str_replace(SITEURL, BASE, $src));
		}
		return $image_name;
	}
	
	public function theme_index_top($h){
		// get the admin settings for this plugin
                $h->vars['post_images_settings'] = $h->getSerializedSettings();
                
		if($h->vars['post_images_settings']['default'] == 'sitethumbshot'){
			if($h->vars['post_images_settings']['sitethumbshot_size'] == 'T'){
				$h->vars['post_images_settings']['h'] = 70;
				$h->vars['post_images_settings']['w'] = 90;
			}
			if($h->vars['post_images_settings']['sitethumbshot_size'] == 'S'){
				$h->vars['post_images_settings']['h'] = 90;
				$h->vars['post_images_settings']['w'] = 120;
			}
			if($h->vars['post_images_settings']['sitethumbshot_size'] == 'M'){
				$h->vars['post_images_settings']['h'] = 150;
				$h->vars['post_images_settings']['w'] = 200;
			}
		}
		if($h->cage->post->getAlpha('type') == 'postImages' && $h->cage->post->testUri('url')){
			$html =  file_get_contents($h->cage->post->getHtmLawed('url'));
			$parseUrl = parse_url(trim($h->cage->post->getHtmLawed('url')));
			$hostname = trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));
			$html = str_replace('src="/','src="'.$parseUrl['scheme'].'://'.$hostname.'/',$html);
			$html = str_replace("src='/","src='".$parseUrl['scheme']."://".$hostname.'/',$html);
			preg_match_all('/( src=["\']{1}(?!http)\w{1})/',$html,$matches, PREG_OFFSET_CAPTURE);
			echo '<pre>';
			$matches = array_unique($matches);
			print_r($matches);
			$rel_dir = (substr($parseUrl['path'],-1) == '/' || strlen($parseUrl['path']) == 0 ? $parseUrl['path'] : dirname($parseUrl['path']).'/');
			$rel_path = $parseUrl['scheme']."://".$hostname.$rel_dir;
			$rel_div_l = strlen($rel_path);
			$i = 0;
			foreach($matches[0] as $match){
				echo $rel_div_l.' '.var_dump($match[1]);
				echo '-'.$match[1]+6+$i*$rel_div_l."\n";
				$html = substr($html,0,$match[1]+6+$i*$rel_div_l) . $rel_path . substr($html,$match[1]+6+$i*$rel_div_l);
				$i++;
			}
			echo '</pre>';
			echo $html;
			die();
		}
	}
}
?>