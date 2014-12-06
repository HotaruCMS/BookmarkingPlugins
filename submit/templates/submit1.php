<?php
/**
 * Template for Submit: Submit Step 1
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
 
$submitted_url = urldecode($h->vars['submitted_data']['submit_orig_url']);
?>
<div id="submit_1">

    <?php echo $h->showMessage(); ?>           

    <h3><?php echo $h->lang["submit_instructions_1"]; ?></h3>
    
    <form role='form' class='' name='submit_1' action='<?php echo BASEURL; ?>index.php?page=submit1' method='post'>
        
        <?php if ($h->currentUser->getPermission('can_post_without_link') == 'yes') { ?>
        <div class="row">
            <div class="col-md-12">
                <button id="submit_button_2" type='submit' class='pull-right submit btn btn-warning' value='checked' name='no_link'>
                    <?php echo $h->lang['submit_post_without_link']; ?>
                </button>
            </div>
        </div>
        <?php } ?>
        
        <div class="form-group">
            <label for='submitUrl' ><?php echo $h->lang["submit_url"]; ?></label>
            <div class="input-group">
                <input id='submit_orig_url' class='form-control' type='text' name='submit_orig_url' value='<?php echo $submitted_url; ?>' placeholder="http://" />      
                <span class="input-group-btn">
                    <button id="submit_button_1" type='submit' class='submit btn btn-primary' name='submit'>
                        <?php echo $h->lang["main_form_next"]; ?>&nbsp;&nbsp;<i class='fa fa-arrow-right'></i> 
                    </button>
                </span>
            </div>
        </div> 
        
        
        
        <input type='hidden' name='submit1' value='true' />
        <input type='hidden' name='page' value='<?php echo $h->pageName; ?>' />
        <input type='hidden' name='csrf' value='<?php echo $h->csrfToken; ?>' />
        
    </form>

</div>
