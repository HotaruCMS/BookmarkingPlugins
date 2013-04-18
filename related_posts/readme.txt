Related Posts Plugin for Hotaru CMS
-----------------------------------
Created by: Nick Ramsay

Description
-----------
Display related posts on post pages and the final step of post submission.

Instructions
------------
1. Upload the "related_posts" folder to your plugins folder. 
2. Install it from Plugin Management in Admin. 
3. Edit the maximum number of posts to show in the Submit plugin's settings page.

Changelog
---------
v.1.2 2010/12/12 - mabujo - Make image clickable, add default size to css, add alt tag, fix error in appending indent class.
v.1.1 2010/12/11 - mabujo - Allow use of post images in related posts results.
v.1.0 2010/12/10 - mabujo - Reinstated noRelatedPosts function as its removal causes a fatal error.
v.0.9 2010/12/08 - Nick - Removed dependency on $h->db->select method.
v.0.8 2010/11/27 - Nick - Minor update to correct indent if Vote plugin not active
v.0.7 2010/08/20 - Nick - Restricts search to first 5 tags only
v.0.6 2010/07/22 - Nick - Excludes journal posts
v.0.5 2010/05/27 - Nick - Updated for compatibility with the Bookmarking plugin
v.0.4 2010/02/22 - Nick - Caches the HTML results until the tags table is updated
v.0.3 2010/02/20 - Nick - Fix for SQL warning when no tags present
v.0.2 2009/12/31 - Nick - Compatibility with Hotaru 1.0
v.0.1 2009/11/23 - Nick - Released first version
