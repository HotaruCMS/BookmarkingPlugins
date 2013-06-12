<?php
/**
 * name: Gravatar
 * description: Enables Gravatar avatars for users
 * version: 1.1
 * folder: gravatar
 * class: Gravatar
 * type: avatar
 * hooks: install_plugin, avatar_set_avatar, avatar_get_avatar, avatar_show_avatar, avatar_test_avatar, admin_plugin_settings
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
 *
 *
 * USAGE: This class hooks into the Avatar class, so is used like this:
 * 
 * $avatar = new Avatar($h, $user_id, $size, $rating);
 * $avatar->getAvatar($h); // returns the avatar for custom display... OR...
 * $avatar->linkAvatar($h); // displays the avatar linked to the user's profile
 * $avatar->wrapAvatar($h); // displays the avatar linked and wrapped in a div (css class: avatar_wrapper)
 *
 * Shortcuts:
 * $h->setAvatar($user_id, $size, $rating);
 * $h->getAvatar(); $h->linkAvatar(); $h->wrapAvatar();
 */

class Gravatar
{
	/* 
		$default can be set as follows on the Gravatar Settings page:
	
		custom: your own default_80.png image in your theme images folder (if not present, default_80.png in /gravatar/images is used).
		mm: (mystery-man) a simple, cartoon-style silhouetted outline of a person (does not vary by email hash)
		identicon: a geometric pattern based on an email hash
		monsterid: a generated 'monster' with different colors, faces, etc
		wavatar: generated faces with differing features and backgrounds
		retro: awesome generated, 8-bit arcade-style pixelated faces
		blank: a transparent PNG image
	*/
	
	private $default = 'identicon'; 


	/**
	 * Install Gravatar settings if they don't already exist
	 */
	public function install_plugin($h)
	{
		// Default settings 
		$gravatar_settings = $h->getSerializedSettings();
		if (!isset($gravatar_settings['default_avatar'])) { $gravatar_settings['default_avatar'] = "identicon"; }
		$h->updateSetting('gravatar_settings', serialize($gravatar_settings));
	}
	
	
    /**
     * Set global $h vars for this avatar
     *
     * @param $vars array of size, user_id and user_email
     */
    public function avatar_set_avatar($h, $vars)
    {
        $h->vars['avatar_size'] = $vars['size'];
        $h->vars['avatar_rating'] = $vars['rating'];
        $h->vars['avatar_user_id'] = $vars['user_id'];
        $h->vars['avatar_user_name'] = $vars['user_name'];
        $h->vars['avatar_user_email'] = $vars['user_email'];
        $h->vars['avatar_img_class'] = $vars['img_class'];
    }
    
    
    /**
     * test for existence and if there, return the avatar with no surrounding HTML div
     *
     * @param string $email - email of avatar user
     * @param int $size - size (1 ~ 512 pixels)
     * @param string $rating - g, pg, r or x
     * @param bool - test for existence?
     *
     * @return return the avatar
     */
    public function avatar_test_avatar($h)
    {
        $grav_url = $this->buildGravatarUrl($h->vars['avatar_user_email'], $h->vars['avatar_size'], $h->vars['avatar_rating'], TRUE);

        $headers = @get_headers($grav_url);
        if (preg_match("|200|", $headers[0])) {
            return $this->buildGravatarImage($grav_url, $h->vars['avatar_size']);
        }
    }
    
    
    /**
     * return the avatar with no surrounding HTML div
     *
     * @return return the avatar
     */
    public function avatar_get_avatar($h)
    {
    	// get default from settings
		$h->vars['gravatar_settings'] = $h->getSerializedSettings('gravatar');
		$this->default = $h->vars['gravatar_settings']['default_avatar'];
        	
        $grav_url = $this->buildGravatarUrl($h, $h->vars['avatar_user_email'], $h->vars['avatar_size'], $h->vars['avatar_rating']);
        $img_url = $this->buildGravatarImage($grav_url, $h->vars['avatar_size'], $h->vars['avatar_img_class']);
        return $img_url;
    }
    
    
    /**
     * Build Gravatar image
     *
     * @param string $email - email of avatar user
     * @param int $size - size (1 ~ 512 pixels)
     * @param string $rating - g, pg, r or x
     * @param string $d the Gravatar "default" parameter - 404 just tests for existence
     * @return string - html for image
     */
    public function buildGravatarUrl($h, $email = '', $size = 32, $rating = 'g', $test = FALSE)
    {
    	/*	3 cases:
    	
    		1. If testing for existence, send Gravatar d=404
    		2. If using a custom avatar, send Gravatar its location
    		3. Otherwise, use the default Gravatar image specified in plugin settings for Gravatar
    	*/
    	
    	if ($test)
    	{
    		$d = '404';
    	}
        elseif ($this->default === 'custom')
        {         	
        	// Look in the theme's images folder for a custom default avatar before using the one in the Gravatar images folder
        	if (file_exists(THEMES . THEME . "images/default_80.png"))
        	{
                $default_image = BASEURL . "content/themes/"  . THEME . "images/default_80.png";
                $d = urlencode($default_image); // Gravatar will redirect back and use this custom avatar
            } 
            
            // otherwise use the Gravatar "Mystery Man" default avatar
            else 
            { 
                $d = 'mm';
            }            
        }
        else
        {
        	$d = $this->default;
        }
        
        // build the gravatar url
        $grav_url = "http://www.gravatar.com/avatar/".md5( strtolower($email) ).
            "?d=". $d.
            "&amp;size=" . $size . 
            "&amp;r=" . $rating;
        
        return $grav_url;
    }
    
    
    /**
     * Build Gravatar image
     *
     * @param string $email - email of avatar user
     * @param int $size - size (1 ~ 512 pixels)
     * @param string $rating - g, pg, r or x
     * @return string - html for image
     */
    public function buildGravatarImage($grav_url = '', $size = 32, $class = '')
    {
        if (!$grav_url) { return false; }
        
        $resized = "style='height: " . $size . "px; width: " . $size . "px'";
                
        if ($class) $class = ' ' . $class;
        $img_url = "<img class='avatar" . $class . "' width='" . $size . "' height='" . $size . "' src='" . $grav_url . "' " . $resized  ." alt='' />";
        return $img_url;
    }
}

?>