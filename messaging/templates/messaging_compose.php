<?php
/**
 * Messaging compose new message
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

<?php
    if ($h->vars['message_reply']) { 
        $h->vars['message_subject'] = $h->lang["messaging_re"] . $h->vars['message_subject'];
    }
?>

<div id="messaging_compose" class="col-md-9">

<?php echo $h->showMessages(); ?>

<div class="message-box-header">
    <div class="pull-right">
<!--        <a href="mailbox.html" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Move to draft folder"><i class="fa fa-pencil"></i> Draft</a>-->
        <a href="<?php echo BASEURL; ?>index.php?page=inbox" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Discard email"><i class="fa fa-trash-o"></i></a>
    </div>
    <h2>
        <i class="fa fa-pencil-square-o"></i>
        <?php echo $h->lang["messaging_compose"]; ?>
    </h2>
    <?php if ($h->vars['message_reply']) { ?>
        <?php echo $h->lang['messaging_in_reply_to']; ?>
        <a href="<?php echo BASEURL; ?>index.php?page=show_message&amp;id=<?php echo $h->vars['message_id']; ?>" target="_blank">
            <?php echo $h->vars['message_subject']; ?>
        </a>
    <?php } ?>
</div>
    
<form class="form-horizontal" role="form" name="compose_message" action="<?php echo BASEURL; ?>index.php?page=compose&amp;action=send" method="post">

    <div class="message-box">
        <div class="message-body">
            <div class="form-group"><label class="col-sm-2 control-label"><?php echo $h->lang['messaging_to']; ?></label>

                <div class="col-sm-10">
                    <input type="text" class="form-control" id="message_to" name="message_to" placeholder="<?php echo $h->lang['messaging_username']; ?>" value="<?php echo $h->vars['message_to']; ?>">
                </div>
            </div>
            <div class="form-group"><label class="col-sm-2 control-label"><?php echo $h->lang['messaging_subject']; ?></label>

                <div class="col-sm-10"><input type="text" class="form-control" id="message_subject" name="message_subject" value="<?php echo $h->vars['message_subject']; ?>"></div>
            </div>
    </div>
        
    <div class="message-text" >
        <textarea id="message_body" name="message_body" class="message_body"><?php echo $h->vars['message_body']; ?></textarea>
    </div>

    <div class="message-body text-right tooltip-demo">
        <button type="submit"  class="btn btn-sm btn-primary" data-toggle="tooltip" data-placement="top" title="Send"><i class="fa fa-reply"></i> <?php echo $h->lang['messaging_send']; ?></button>
        <a href="mailbox.html" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Discard email"><i class="fa fa-times"></i> Discard</a>
<!--            <a href="mailbox.html" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Move to draft folder"><i class="fa fa-pencil"></i> Draft</a>-->
    </div>
        
</div>
    <input type='hidden' name='csrf' value='<?php echo $h->csrfToken; ?>' />
    

</form>

</div>

