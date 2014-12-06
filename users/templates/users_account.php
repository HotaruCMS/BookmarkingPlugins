<?php
/**
 * Users Update Login, Email and Password
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

/* check for account updates */
//    $h->vars['checks'] = $h->vars['user']->updateAccount($h);
//    $h->vars['user']->name = $h->vars['checks']['username_check'];           
 
// ****************** was in users page at theme_index_top

extract($h->vars['checks']); // extracts $username_check, etc.
$username = $username_check; // used for user_tabs template
if ($username_check == 'deleted') { $h->showMessage(); return true; } // shows "User deleted" notice

?>
<div id="users_account" class="col-md-9">

    <h4><?php echo $h->lang("users_account"); ?></h4>
    
    <?php echo $h->showMessages(); ?>

    <form class="form-horizontal users_form" role="form" name='update_form' action='<?php echo BASEURL; ?>index.php?page=account' method='post'>
        <div class="form-group">
          <label for="inputUsername" class="col-sm-2 control-label"><?php echo $h->lang["users_account_username"]; ?></label>
          <div class="col-sm-9">
            <input disabled type="text" class="form-control" id="inputEmail3" placeholder="Username" name='username' value='<?php echo $username; ?>'>
          </div>
        </div>
        <div class="form-group">
          <label for="inputEmail" class="col-sm-2 control-label"><?php echo $h->lang["users_account_email"]; ?></label>
          <div class="col-sm-9">
            <input type="email" class="form-control" id="inputPassword3" placeholder="Email" name='email' value='<?php echo $email_check; ?>'>
          </div>
        </div>
        
        <?php 
        // show role picker to anyone who can access admin, but not to yourself!
        if (($h->currentUser->getPermission('can_access_admin') == 'yes') 
        && ($h->currentUser->id != $userid_check)) { 
    ?>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="inputAccountRole"><?php echo $h->lang["users_account_role"]; ?></label>
        <div class="col-sm-9">
            <select name='user_role' class="form-control">
                <option value='<?php echo $role_check; ?>'><?php echo $role_check; ?></option>
                <?php 
                    $roles = $h->getUniqueRoles(); 
                    if ($roles) {
                        foreach ($roles as $role) {
                            if ($role != $role_check) {
                                echo "<option value='" . $role . "'>" . $role . "</option>\n";
                            }
                        }
                    }
                ?>
            </select>
            <span class="help-block">
                <?php echo $h->lang["users_account_role_note"]; ?>
            </span>
        </div>
            
        </div>
    <?php } else { // your own role as a hidden field:?>
        <input type='hidden' name='user_role' value='<?php echo $role_check; ?>' />
    <?php } ?>
  
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn btn-warning"><?php echo $h->lang['users_account_update']; ?></button>
          </div>
        </div>

        <input type='hidden' name='userid' value='<?php echo $userid_check; ?>' />
        <input type='hidden' name='page' value='account' />
        <input type='hidden' name='update_type' value='update_general' />
        <input type='hidden' name='csrf' value='<?php echo $h->csrfToken; ?>' />
    </form>
    
    <!-- end account form -->
    
    <!-- start password form -->
    
    <?php $h->pluginHook('users_account_pre_password'); ?>
    
    <?php if ($h->vars['user']->id == $h->currentUser->id)
        
    { // must be looking at own account to show password change form: ?>
    
        <hr/>
        
        <?php $h->pluginHook('users_account_pre_password_user_only'); ?>

        <h4><?php echo $h->lang["users_account_password_instruct"]; ?></h4>
        
<?php echo $password_check_old; ?>
        <form class="form-horizontal users_form" role="form" action='<?php echo BASEURL; ?>index.php' method='post'>
            <div class="form-group">
              <label for="inputPassword1" class="col-sm-2 control-label"><?php echo $h->lang("users_account_form_old_password"); ?></label>
              <div class="col-sm-9">
                <input type="password" class="form-control" id="inputPassword1" name='password_old' placeholder="<?php echo $h->lang("users_account_old_password"); ?>">
              </div>
            </div>
            <div class="form-group">
              <label for="inputPassword2" class="col-sm-2 control-label"><?php echo $h->lang("users_account_form_new_password"); ?></label>
              <div class="col-sm-9">
                <input type="password" class="form-control" id="inputPassword2" name='password_new' placeholder="<?php echo $h->lang("users_account_new_password"); ?>">
                <span class="help-block"><?php echo $h->lang["users_account_password_requirements"]; ?></span>
              </div>
            </div>
            <div class="form-group">
              <label for="inputPassword3" class="col-sm-2 control-label"><?php echo $h->lang("users_account_form_new_password_verify"); ?></label>
              <div class="col-sm-9">
                <input type="password" class="form-control" id="inputPassword3" name='password_new2' placeholder="<?php echo $h->lang("users_account_new_password_verify"); ?>">
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-warning"><?php echo $h->lang['users_account_update']; ?></button>
              </div>
            </div>
            <input type='hidden' name='userid' value='<?php echo $userid_check; ?>' />
            <input type='hidden' name='page' value='account' />
            <input type='hidden' name='update_type' value='update_password' />
            <input type='hidden' name='csrf' value='<?php echo $h->csrfToken; ?>' />
        </form>
        
    <?php } ?>
</div>