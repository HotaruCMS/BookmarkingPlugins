<?php
/**
 * Follow - Followers
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
 * @author    shibuya246 <admin@hotarucms.org>
 * @copyright Copyright (c) 2009 - 2013, Hotaru CMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      http://www.hotarucms.org/
 */

$follow_type = $h->vars['follow_type'];
$follow_settings = $h->vars['follow_settings'];
?>

<div id="list_followers" class="col-md-9">

<h4><?php echo $follow_type . " : " . $h->vars['follow_count'] .""; ?></h4>

<table class="table table-bordered follow_list">
    <tr class="info follow_list_headers">
        <td><?php echo $follow_type; ?></td>
        <td><?php echo $h->lang['follow_list_activity']; ?></td>
        <td>&nbsp;</td>
    </tr>
    
    <?php if (isset($h->vars['follow_list']->items)) { ?>

        <?php foreach ($h->vars['follow_list']->items as $user) { ?>
            <tr id="follow_user_<?php echo $user->user_id; ?>" class="follow_row">
            
                
                <td class="follow_user">		    
			 <?php if($h->isActive('avatar')) {
			     $h->setAvatar($user->user_id, 32);
			     echo $h->wrapAvatar();
			 } ?>
<!--		    <a href="<?php //echo $h->url(array('user'=>$user->user_username)); ?>">
			<?php //echo $user->user_username; ?>
		    </a>-->
		</td>
                                
                <td class="follow_activity">
                    <?php 
                    $action = $h->pluginHook('follow_activity', '', array($user->user_id)); 
                    
                    if (!$action) { echo "No activity yet."; } else {
                        include_once (PLUGINS . 'activity/libs/ActivityFunctions.php');
                        $activityFuncs = new ActivityFunctions();
                        echo $activityFuncs->activityContent($h, $action['Activity_follow_activity']);
                        if ($follow_settings['follow_show_time_date']) {
                            echo "<br /><small>[" . date('g:ia, M jS', strtotime($action['Activity_follow_activity']->useract_date)) . "]</small>";
                        }
                    }
                    ?>
                </td>                               

		<?php
		if ($user->user_id != $h->currentUser->id) {		    
		    $type = $h->isFollowing($user->user_id) == 0 ? 'Follow' : 'Unfollow';
                    $btnType  = $h->isFollowing($user->user_id) == 0 ? 'btn-primary' : 'btn-danger';

		    echo '<td class="follow_update"><center>';
			if ($h->currentUser->loggedIn) {
			    echo '<input type="button" class="btn btn-sm ' . $btnType . ' follow_button" name="'. $type. '_' . $user->user_id .'" id="' . $type . '_' . $user->user_id .'" value="' . $type .'">';
			}
		    echo '</center></td>';
		 }
		 else {
		     echo '<td class="follow_update"><center>You</center></td>';
		 }
		 ?>
            </tr>
        <?php } ?>
    
    <?php } else { ?>
        <tr><td colspan='4'><center><?php echo $h->lang['follow_no_followers']; ?></center></td></tr>
    <?php } ?>
    
</table>

    <?php echo $h->pageBar($h->vars['follow_list']); ?>
    
   
</div>


 <script type='text/javascript'>
    jQuery('document').ready(function($) {

    $(".follow_button").click(function(){

	    var button = $(this);
	    var array = $(this).attr('id').split('_');
            var user_id = array[array.length-1];
	    var type = array[array.length-2].toLowerCase();
            var formdata = 'action=' + type + '&user_id=' + user_id;
            var sendurl = BASEURL +"content/plugins/follow/templates/follow_update.php";

	    $.ajax(
		{
		type: 'post',
		url: sendurl,
		data: formdata,
		error: 	function(XMLHttpRequest, textStatus, errorThrown) {
				$(this).attr('value', 'error');
		},
		success: function(data, textStatus) { // success means it returned some form of json code to us. may be code with custom error msg
			if (data.error) {
			    $('#error_message').html(data.error);
			}
			else
			{			    
			    $(button).attr('value', data.result)
			    $(button).attr('id', data.result + '_' +user_id);
			    $(button).attr('name', data.result + '_' + user_id);
                            if (data.result === 'Follow') {
                                $(button).removeClass('btn-danger').addClass('btn-success');
                            } else if (data.result === 'Unfollow') {
                                $(button).removeClass('btn-success').addClass('btn-danger');
                            } 
			}
		},
		dataType: "json"
	    });
	 });
    });
 

 </script>