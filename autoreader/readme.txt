RSS Autoreader Plugin for Hotaru CMS
--------------------------------
Created by: shibuya246 (http://shibuya246.com)

Description
-----------
Automatic reading of rss feeds to populate post table for submissions to hotaru

Instructions
------------
1. Upload the "autoreader" folder to your plugins folder.
2. Install it from Plugin Management in Admin. 
3. Add a campaign and insert details of feed, category, author, schedule for auto reading etc
4. Check in Cron plugin to see that job has been saved
5. You can also manually fetch campaign feeds by clicking the link on "List Campaign" page that says "Fetch"

Notes
-------
This plugin currently will not work in any version of IE

Changelog
---------
v.1.0.1 2011/04/21 - mabujo - another fix for ampersand bug and slight makeTitleFriendly method improvement.
v.1.0 2011/04/20 - mabujo - added new makeTitleFriendly method to contain all the title utf-8 encoding fixes.
Fixed problem with ampersand/&/&amp; character, and a fix for a latin extended set character found in certain yahoo feeds.
v.0.9 2011/04/19 - mabujo - Revert Nick's 1.5 dependency changes, fixes title encoding problems 
(+ apostrophes cutting off the field when editing e.t.c.), fixes not being able to assign posts to certain users,
 fixes where db prefix was ignored in adminUpdateCampaignPosts. 
v.0.8 2010/12/20 - Nick - Removed dependency on core category functions
v.0.7 2010/08/20 - Nick - Fix for unnecessary url assignment
v.0.6 2010/08/14 - Nick - Creates unique urls for posts with duplicate titles
v.0.5 2010/06/02 - Nick - Removed new truncate function as it created more problems than it solved.
v.0.4 2010/06/02 - shibuya246 - Added new truncate function
v 0.3 2010/05/14 - shibuya246 - Reorganized code to use libs folder, Added truncation setting and 'All' category for posts
v.0.2 2010/04/05 - shibuya246 - Allow "top" posts to be selected, db table modify
v.0.1 2010/03/22 - shibuya246 - Released first version
