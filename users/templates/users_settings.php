<?php
/**
 * Users Settings
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

// get updated fields. 
if ($h->cage->post->getAlpha('updated_settings') == 'true') {
    
    // plugin hook
    $h->pluginHook('user_settings_pre_save'); 
        
    // this hook does the actual saving. It can only be used by the Users plugin
    $h->pluginHook('user_settings_save', 'users', array($h->vars['user']->name, $h->vars['settings'])); 
} 

// set radio buttons plugin hook
$h->pluginHook('user_settings_fill_form'); 

?>
<div id="users_settings" class="col-md-9">

    <h4><?php echo $h->lang["users_settings"]; ?></h4>
    
    <?php echo $h->showMessage(); ?>

    <form role='form' class='form-horizontal' name='user_settings_form' action='<?php echo $h->url(array('page'=>'user-settings', 'user'=>$h->vars['user']->name)); ?>' method='post'>    
    
        <?php $h->pluginHook('user_settings_extra_settings'); ?>
        
        <button class='btn btn-primary' type='submit' class='submit'><?php echo $h->lang['users_settings_update']; ?></button>
        <input type='hidden' name='updated_settings' value='true' />
        <input type='hidden' name='csrf' value='<?php echo $h->csrfToken; ?>' />
    </form>
</div>