<?php
/**
 * Template for Domain Results plugin: domain_results_list
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

?>
<div id='activity'>
    <ul class='activity_items' id='activity_items_list'>
  
<?php
if ($h->vars['pagedResults']->items) { 
    foreach ($h->vars['pagedResults']->items as $user) {
        //print_r($user);
        
        $userId = $user->user_id;
        $username = isset($user->user_username) ? $user->user_username : '';

        switch ($user->user_role) {
            case 'admin':
                $label = 'label-blue'; 
                break;            
            case 'moderator':
                $label = 'label-green'; 
                break;
            case 'comment':
                $label = "label-orange";
                break;
            default:
                $label = 'label-gray';
                break;
        }
        ?>

        <li class="info-box gray" id="user_li_<?php echo $userId; ?>">
            <div class="avatarBox">

                <?php if($h->isActive('avatar')) { ?>
                            <div class='avatar_small'>
                                <?php 
                                    $h->setAvatar($userId, 32, 'g', 'img-circle', $user->user_email, $username); 
                                    echo $h->linkAvatar(); 
                                ?>
                            </div>
                        <?php } ?>

            </div>
            <div class="info">
              <span class="name">
                  <span class="label <?php echo $label; ?>"><?php echo strtoupper($user->user_role); ?></span> 
                  <strong class="indent">
                      <?php if ($userId == 0) { echo $h->lang('activity_anonymous'); } else {
                            echo "<a class='activity_user' href='" . $h->url(array('user' => $username)) . "'>" . $username . "</a>";
                        }?>
                  </strong> <?php //echo $act->activityContent($h, $action); ?>

              </span>
              <span class="time pull-right"><i class="icon-time"></i> <?php echo time_ago($user->user_date); ?></span>
            </div>
          </li>          

    <?php }
}

?>
    </ul>
</div>
