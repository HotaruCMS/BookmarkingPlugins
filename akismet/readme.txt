Akismet Plugin for Hotaru CMS
---------------------------------
Created by: shibuya246

Description
-----------
This plugin accesses the Akismet.com blacklist (http://akismet.com/) to keep spam users out of your site. It does the following:

1. Checks for a new user's username, email address and IP address on the blacklist.
2. Puts users into moderation if they are found on the blacklist. 
3. Includes a note in the User Manager plugin stating whether the user's name, email or IP address was flagged.
4. Provides as option to add any killspammed or deleted users to the blacklist so that they won't be able to register on other Hotaru CMS sites.

Instructions
------------
1. Upload the "akismet" folder to your plugins folder. Install it from Plugin Management in Admin.
2. Go to Admin -> Plugin Settings -> Stop Spam and enter your API key, which you can get here: 
http://akismet.com/signup

Notes
-----
1. Spammers can only be added to the Akismet.com database when killspamming or deleting them from the User Manager plugin, not from their Account page.

Changelog
---------
v.0.1 2013/04/23 - shibuya246 - Released first version
