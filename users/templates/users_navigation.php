<?php
/**
 * User Profile
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
 
$username = $h->vars['user']->name;
$userId = $h->vars['user']->id;
$imageFolder = BASE . '/content/images/user/' . $userId . '/';

// make folder if does not exist
if(!is_dir($imageFolder)) mkdir($imageFolder,0777,true);

// check if we have profile pix for user. If not use default
$imageId = rand(1, 2);

if (file_exists($imageFolder . 'filename.jpg'))
    $fileUrl = BASEURL . 'content/images/user/' . $userId . '';
else 
    $fileUrl = BASEURL . 'content/images/user/default/profile-pix' . $imageId . '.jpg';
?> 

<div class="">
    <div id="userProfilePixBox" class="span8" style="position:relative;">
	
	<div id="" style="position:absolute;">
	    <img title="<?php echo $username; ?>"  src="<?php echo $fileUrl;?>" alt="userPix">	    
	</div>
	
	
	<div id="profileAvatarOverlay" style="position:absolute;">
	    <?php
                if ($h->isActive('avatar')) {
                       echo "<div id='profile_avatar'>";
                       $h->setAvatar($h->vars['user']->id, 140, 'g', 'img-polaroid');
                       echo $h->linkAvatar();
                       echo "</div>";
               }
    ?>
        </div>	
	    	
    </div>
    
     <div class="span3">
	<div class="mainBox">
	    <div class="profileBox">
		<h3><?php echo $username; ?></h3>
                <div class="followersLine">
                    <div class="followers"><?php echo $h->postsApproved($h->vars['user']->id); ?> posts</div>
                    
                </div>
            </div>
           
	    
	</div>
    </div>
    
    <div class="clear">&nbsp;</div>
</div>
<br/>

<div class="profile_navigation2 tabbable tabs-below">
 
    <ul class="nav nav-tabs">        
        <li class="active"><a href='<?php echo $h->url(array('user'=>$username)); ?>' data-toggle='tab'><?php echo $h->lang["users_profile"]; ?></a></li>
        <?php $h->pluginHook('profile_navigation'); ?>
    
    
    <?php // show account and profile links to owner or admin access users: 
        if (($h->currentUser->name == $username)) { ?>

            <li><a href='<?php echo $h->url(array('page'=>'account', 'user'=>$username)); ?>'><?php echo $h->lang["users_account"]; ?></a></li>
            <li><a href='<?php echo $h->url(array('page'=>'edit-profile', 'user'=>$username)); ?>'><?php echo $h->lang["users_profile_edit"]; ?></a></li>
            <li><a href='<?php echo $h->url(array('page'=>'user-settings', 'user'=>$username)); ?>'><?php echo $h->lang["users_settings"]; ?></a></li>

    <?php } ?>
    
    </ul> 
    
    
    <div class="tab-content">
        <div class="tab-pane active" id="profile">
            Profile
        </div>
        
        <div class="form tab-pane" id="settings">
            settings
        </div>
    </div>
    
</div>
