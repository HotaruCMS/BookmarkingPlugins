Gravatar Plugin for Hotaru CMS
--------------------------------
Created by: Nick Ramsay

Description
-----------
Enable user avatars with Gravatar, a "global" avatar which has become especially popular since Wordpress adopted it for their avatars. (http://en.gravatar.com/) 

Instructions
------------
1. Upload the "gravatar" folder to your plugins folder. 
2. Install it from Plugin Management in Admin. 
3. Choose the default avatar in Plugin Settings / Gravatar.

Changing the default Gravatar
-----------------------------
If a user doesn't have a Gravatar account that matches their email address, then Gravatar will send back the default avatar you use choose in Plugin Settings. If you want to provide a customized avatar, create an 80px .png image called "default_80.png" and put it in your theme's images folder. Then select "custom" from the choice of default avatars in Plugin Settings / Gravatar. Note that it's a little bit faster to use Gravatar's own default images than your own.

NOTE: For users of previous versions of this plugin, please note that the collection of random avatars from v.1.0 is no longer used. In fact, v.1.1 doesn't even have an images folder. You can delete the /plugins/gravatar/images folder from your site if you wish.

Changelog
---------
v.1.1 2013/06/03 - Nick - Uses Gravatar's random default avatars
v.1.0 2010/07/06 - Nick - Added option to randomize the default avatar
v.0.9 2010/04/03 - Nick - Removed requirement to have the Users plugin enabled
v.0.8 2010/02/10 - Nick - Added ability to test if a user has a Gravatar
v.0.7 2009/12/26 - Nick - Updates for compatibility with Hotaru 1.0
v.0.6 2009/10/31 - Nick - Changes to make it easier for other plugins to use Gravatar
v.0.5 2009/10/26 - Nick - Added a "rating" setting (edit in "install_plugin" function)
v.0.4 2009/10/06 - Nick - Updates for compatibility with Hotaru 0.7
v.0.3 2009/10/01 - Nick - Updates for compatibility with Hotaru 0.6
v.0.2 2009/08/28 - Nick - Updates for compatibility with Hotaru 0.5
v.0.1 2009/08/19 - Nick - Released first version