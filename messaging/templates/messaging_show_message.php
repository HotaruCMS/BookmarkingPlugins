<?php
/**
 * Messaging show message
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

<div id="messaging_show_message" class="col-md-9">

<?php echo $h->showMessages(); ?>

<?php
if ( $h->vars['message_id'] !== -1 ) {
    $h->setAvatar($h->vars['message_from_id'], 16);    
?>

<div class="animated fadeInRight">
    
            <div class="message-box-header">
                <div class="pull-right tooltip-demo">
                    <a href="<?php echo BASEURL; ?>index.php?page=compose&amp;reply=<?php echo $h->vars['message_id']; ?>" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Reply"><i class="fa fa-reply"></i> <?php echo $h->lang("messaging_reply"); ?></a>
<!--                    <a href="#" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Print email"><i class="fa fa-print"></i> </a>-->
                    <a href="mailbox.html" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Move to trash"><i class="fa fa-trash-o"></i> </a>
                </div>
                <h2>
                    <i class="fa fa-envelope"></i>
                    <a href="<?php echo $h->url(array('page'=>'inbox', 'user'=>$h->displayUser->name)); ?>">Inbox</a> <?php //echo $h->lang["messaging_view_message"]; ?>
                </h2>
                <div class="message-options">
                    <h3>
                        <span class="font-noraml"><?php echo $h->lang['messaging_subject']; ?> </span><?php echo $h->vars['message_subject']; ?>
                    </h3>
                    <h5>
                        <span class="pull-right font-noraml"><?php echo $h->vars['message_date']; ?></span>
                        <span class="font-noraml"><?php echo $h->lang['messaging_from']; ?> </span><a href="<?php echo $h->url(array('user'=>$h->vars['message_from_name'])); ?>"><?php echo $h->vars['message_from_name']; ?></a>
                    </h5>
                </div>
            </div>
            <div class="message-box">
                <div class="message-body">                    
                        <?php echo $h->vars['message_body']; ?>                    
                </div>
                <div class="message-body text-right">
                        <a class="btn btn-sm btn-default" href="<?php echo BASEURL; ?>index.php?page=compose&amp;reply=<?php echo $h->vars['message_id']; ?>"><i class="fa fa-reply"></i> <?php echo $h->lang("messaging_reply"); ?></a>
<!--                        <a class="btn btn-sm btn-default" href="mail_compose.html"><i class="fa fa-arrow-right"></i> <?php echo $h->lang("messaging_forward"); ?></a>
                        <button title="" data-placement="top" data-toggle="tooltip" type="button" data-original-title="Print" class="btn btn-sm btn-default"><i class="fa fa-print"></i> Print</button>-->
                        <button title="" data-placement="top" data-toggle="tooltip" data-original-title="Trash" class="btn btn-sm btn-danger"><i class="fa fa-trash-o"></i> Remove</button>
                </div>

                <div class="clearfix"></div>
                </div>
            </div>
<?php } ?>

</div>