User Signin Plugin for Hotaru CMS
--------------------------------
Created by: Nick Ramsay

Description
-----------
Provides user login and registration. Includes anti-spam features such as ReCaptcha and email validation.

Instructions
------------
1. Upload the "user_signin" folder to your plugins folder. 
2. Install it from Plugin Management in Admin.
3. Edit settings in Admin -> User Signin

Changelog
---------
v.1.1 2014/10/13 - shibuya246 - added user navigation template for customising (particularly for older themes not using bootstrap)
v 1.0 2014/10/05 - shibuya246 - Updated hook for spam filter
v 0.9 2014/10/01 - shibuya246 - Restyled form
v.0.8 2013/05/03 - shibuya246 - Plugin hook updated
v.0.7 2010/08/06 - Nick - Minor fix for &amp; in urls when redirecting from login to submit step 2
v.0.6 2010/04/27 - Nick - Fix for unnecessary ReCaptcha-related errors on the settings page 
v.0.5 2010/04/01 - Nick - Built-in reCaptcha removed in favor of separate reCaptcha plugin (user_signin_register template updated)
v.0.4 2010/03/20 - Nick - Fix for emails when using SMTP email authentication
v.0.3 2010/02/26 - Nick - New plugin hook in the registration form; mail sent through Hotaru's email function
v.0.2 2010/02/23 - Nick - Throws out killspammed, banned or suspended users when checking the cookie
v.0.1 2009/12/27 - Nick - Released first version
