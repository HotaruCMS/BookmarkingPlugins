<?php
/**
 * Template for Submit: Submit Step 2
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
$h->pluginHook('submit_2_assign');

?>
<div id="submit_2">

    <?php $h->showMessages(); ?>
    
    <?php echo $h->lang["submit_instructions_2"]; ?>

    <form role='form' name='submit_2' id='submit_2_form' action='<?php echo BASEURL; ?>index.php?page=submit2' method='post'>

    <?php if (!$h->vars['submit_editorial']) { // only show if posting a link ?>
        <div class="form-group">
            <label for='submitUrl' ><?php echo $h->lang["submit_url"]; ?>&nbsp; </label>
            <?php echo truncate($h->vars['submit_orig_url'], 60); ?>
        </div>
    <?php } ?>

    <div class="form-group">
        <label for='submitTitle' ><?php echo $h->lang["submit_title"]; ?>&nbsp; </label>
        <input class='form-control' type='text' id='post_title' name='post_title' value='<?php echo $h->vars['submit_title']; ?>'>
    </div>
 
    <?php if ($h->vars['submit_use_content']) { ?>
    <div class="form-group">
        <label for='submitContent' ><?php echo $h->lang["submit_content"]; ?>&nbsp; </label>
        <div class="message-text" >
            <textarea id="post_content" name="post_content" class="message_body"><?php echo $h->vars['submit_content']; ?></textarea>
        </div>  
        <div class="help-block text-right">
            <small><?php echo $h->lang['submit_allowable_tags']; ?>
            <?php echo $h->vars['submit_allowable_tags']; ?></small>
        </div>
    </div>
    <?php } ?>
    
    
    <?php if ($h->vars['submit_use_categories']) { ?>
    <div class="form-group">
        <label for='submitUrl' ><?php echo $h->lang["submit_category"]; ?>&nbsp; </label>
        <select name='post_category' class='form-control'>
            <?php echo $h->vars['submit_category_picker']; ?>
        </select>
    </div>
    <?php } ?>
    
    <?php if ($h->vars['submit_use_tags']) { ?>
    	<div class="form-group">
        	<label for='submitUrl' ><?php echo $h->lang["submit_tags"]; ?>&nbsp; </label>
            <input class='form-control' type='text' id='post_tags' name='post_tags' value='<?php echo $h->vars['submit_tags']; ?>'>&nbsp; 
            <small><?php echo $h->lang['submit_tags_comma_separated']; ?></small>
        </div>
    <?php } ?>
    
    <?php $h->pluginHook('submit_2_fields'); ?>
            
    <input type='hidden' name='submit_orig_url' value='<?php echo $h->vars['submit_orig_url']; ?>' />
    <input type='hidden' name='submit_post_id' value='<?php echo $h->vars['submit_post_id']; ?>' />
    <input type='hidden' name='submit2' value='true' />
    <input type='hidden' name='submit_key' value='<?php echo $h->vars['submit_key']; ?>' />
    <input type='hidden' name='csrf' value='<?php echo $h->csrfToken; ?>' />
    
    <input class='btn btn-primary pull-right' type='submit' onclick="javascript:safeExit=true;" class='submit' name='submit' value='<?php echo $h->lang['main_form_next']; ?>' />    
    
    </form>
    <?php $h->pluginHook('image_upload'); ?>

</div>