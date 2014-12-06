<?php
/**
 * Users Register
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
 
if ($h->cage->post->getAlpha('users_type') == 'register') {
    $username_check = $h->cage->post->testUsername('username');
    $password_check = "";
    $password2_check = "";
    $email_check = $h->cage->post->testEmail('email');    
} else {
    $username_check = "";
    $password_check = "";
    $password2_check = "";
    $email_check = "";
}
?>
    <h2><?php echo $h->lang["user_signin_register"]; ?></h2>
    
    <?php echo $h->showMessages(); ?>
    
    <?php $h->pluginHook('user_signin_register_pre_register_form'); ?>
        
    <div class='well col-md-10 col-md-offset-1 user_login_reg'>  
    
        <?php //echo $h->lang["user_signin_register_instructions"]; ?>
        
        <form role="form" name='register_form' action='<?php echo $h->url(array('page'=>'register')); ?>' method='post'>    
            
            <div class="form-group">
                <label for="registerusername"><?php echo $h->lang["user_signin_register_username"]; ?></label>
                <input type='text' class="form-control" name='username' value='<?php echo $username_check; ?>' />
                <div class="help_link"><?php echo $h->lang["user_signin_register_username_error_short"]; ?></div>
            </div>
            
            <div class="form-group">
                <label for="registeremail"><?php echo $h->lang["user_signin_register_email"]; ?></label>
                <input type='text' class="form-control" name='email' value='<?php echo $email_check; ?>' />
            </div>
            
            <div class="form-group">
                <label for="registerpwd"><?php echo $h->lang["user_signin_register_password"]; ?></label>
                <input type='password' class="form-control" name='password' value='<?php echo $password_check; ?>' />
                <div class="help_link"><?php echo $h->lang["user_signin_register_password_error_short"]; ?></div>
            </div>
        
            <div class="form-group">
                <label for="registerpwd2"><?php echo $h->lang["user_signin_register_password_verify"]; ?></label>
                <input type='password' class="form-control" name='password2' value='<?php echo $password2_check; ?>' />
            </div>
        
            <div class="form-group">
                <?php $h->pluginHook('user_signin_register_register_form'); ?>
            </div>
        
            <?php if ($h->vars['useRecaptcha']) { ?>
                    <div class="form-group">
                        <?php $h->pluginHook('show_recaptcha'); ?>
                    </div>
            <?php  } ?>
        
            <input type='hidden' name='users_type' value='register' />
            <input type='hidden' name='page' value='register'>
            <input type='hidden' name='csrf' value='<?php echo $h->csrfToken; ?>' />
            <button type='submit' class='btn btn-primary'><?php echo $h->lang['user_signin_register_form_submit']; ?></button>            

        </form>
    </div>
