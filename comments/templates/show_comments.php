<?php
/**
 * Show Comments on an individual post
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

$display = ($h->comment->votes_down >= $h->vars['comment_hide']) ? 'display: none;' : ''; // comments are shown unless they have X negative votes

// color label for status type
switch ($h->comment->status) {
    case 'approved':
        $label = 'label-blue'; 
        break;            
    case 'pending':
        $label = 'label-gray'; 
        break;
    case 'declined':
        $label = "label-red";
        break;
    default:
        $label = 'label-gray';
        break;
}

?>
    <a id="c<?php echo $h->comment->id; ?>"></a>

    <?php if ($h->comment->avatarSize < 16) {$comment_header_size=16;} else { $comment_header_size= $h->comment->avatarSize; } ?>
    <div class="comment" style="margin-left: <?php echo $h->comment->depth * 2.0; ?>em;">
    
                <?php   // Show avatars if enabled (requires an avatars plugin)
                        if ($h->comment->avatars == 'checked') {
                            if($h->isActive('avatar')) {
                                $h->setAvatar($h->comment->author, $h->comment->avatarSize, 'g', 'img-circle');
                                echo $h->wrapAvatar();
                            }
                        }
                ?>
        <div class="comment_body">
            <div class="comment_content">
                <div class="comment_name">
                    <?php
                    $username = $h->comment->authorname;
                    echo $h->lang['comments_written_by'] . " ";
                    echo "<a href='" . $h->url(array('user' => $username)) . "'>" . $username . "</a>";
                    ?>
                </div>

                <div class="comment_date">
                    <?php 
                    if ($h->comment->status != 'approved') {
                        echo '<span class="label ' . $label . '">' . strtoupper($h->comment->status) . '</span>&nbsp;'; 
                    }
                    echo '<i class="fa fa-clock-o"></i>&nbsp;';
                    echo time_difference(unixtimestamp($h->comment->date), $h->lang) . " ";
                    //echo time_ago($h->comment->date); 
                    ?>
                </div>

                <div class="comment_text" style="<?php echo $display; ?>">
                    <?php
                        $result = $h->pluginHook('show_comments_content');
                        if (!isset($result) || !is_array($result)) {
                            echo nl2br($h->comment->content);
                        }
                    ?>
                </div>
                
            </div>
            
            <div class="comment_footer">
            
                <?php   // Show votes if enabled (requires a comment voting plugin)
                        if ($h->comment->voting == 'checked') {
                            $h->pluginHook('show_comments_votes'); 
                        }
                ?>

                <div class="comment_reply_wrapper">

                    <?php   // REPLY LINK - (if logged in) AND (can comment) AND (form is turned on)...
                        if ($h->currentUser->loggedIn
                            && ($h->currentUser->getPermission('can_comment') != 'no')
                            && ($h->comment->thisForm == 'open')) { ?>

                        <?php if ($h->comment->depth < $h->comment->levels-1) { // No nesting after X levels (minus 1 because nestings tarts at 0) ?>
                            <a href='#' class='comment_reply_link' onclick="reply_comment(
                                '<?php echo BASEURL; ?>',
                                '<?php echo $h->comment->id; ?>',
                                '<?php echo $h->lang['comments_form_submit']; ?>');
                                return false;" ><?php echo $h->lang['comments_reply_link']; ?></a>
                        <?php } ?>
                    <?php } ?>
                </div>
                <div class="comment_edit_link">
                    <div class="comment_controls">
                        <?php
                        if ($display) { echo "<a href='#' class='comment_show_hide'>" . $h->lang['comments_show_hide'] . "</a>"; }
                        ?>
                    </div>
                    
                    <?php   // EDIT LINK - (if comment form is open AND ((comment owner AND permission to edit own comments) OR (permission to edit ALL comments))...
                    if ($h->comment->thisForm == 'open') {
                        if (($h->currentUser->id == $h->comment->author && ($h->currentUser->getPermission('can_edit_comments') == 'own'))
                            || ($h->currentUser->getPermission('can_edit_comments') == 'yes')) { ?>
                            <a href='#' class='comment_edit_link' onclick="edit_comment(
                                '<?php echo BASEURL; ?>',
                                '<?php echo $h->comment->id; ?>',
                                '<?php echo urlencode($h->comment->content); ?>',
                                '<?php echo $h->lang['comments_form_edit']; ?>');
                                return false;" ><?php echo $h->lang['comments_edit_link']; ?></a>
                    <?php } ?>
                <?php } ?>
                </div>
                
                
            </div>
        </div>

        
            
    </div>
    
    
    
