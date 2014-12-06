<?php
/**
 * Activity functions
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

class ActivityFunctions
{
    /**
    * Check if the post this action applies to can be shown
    *
    * @param array $activity 
    * return string which is the title
    */
    public function postSafe($h, $item = array())
    {
        // Post used in Hotaru's url function
        if ($item->useract_key == 'post') {
            $postid = $item->useract_value;
        } elseif  ($item->useract_key2 == 'post') {
            $postid = $item->useract_value2;
        }
        
        if (isset($postid) && $postid) {        
            $sql = "SELECT post_title, post_url, post_status FROM " . TABLE_POSTS . " WHERE post_id = %d";
            $post = $h->db->get_row($h->db->prepare($sql, $postid));

            // return status
            if (isset($post))
            {                
                if ($post->post_status == 'buried' || $post->post_status == 'pending') { 
                        return false;
                } else {
                        return array($post->post_title, urldecode($post->post_url));
                }
            }
        } elseif  ($item->useract_key2 == 'comment') {
            $commentId = $item->useract_value2;
            $sql = "SELECT C.comment_content, C.comment_post_id, P.* FROM " . TABLE_COMMENTS . " AS C LEFT OUTER JOIN " . TABLE_POSTS . " AS P ON C.comment_post_id = P.post_id WHERE C.comment_id = %d";
            $comment = $h->db->get_row($h->db->prepare($sql, $commentId));

            if (isset($comment))
            {
                $comment_title = urlencode(strip_tags(urldecode($comment->comment_content)));
                $post_url = $comment->post_url . '#c' . $commentId;
                //comment_post_id
                return array($comment_title, $post_url);
            }           
        }
        
        
    }
    
    
    /**
     * Get activity items
     *
     * @param array $activity 
     * @param array $activity_settings
     * return string $output
     */
    public function getActivityItems($h, $activity = array())
    {
        $output = '';
        
        // Get settings from database if they exist... (should be in cache by now)
        $activity_settings = $h->getSerializedSettings('activity');
        
        foreach ($activity as $item)
        {
            // Post used in Hotaru's url function
            if ($item->useract_key == 'post') {
                    $post_id = $item->useract_value;
            } elseif  ($item->useract_key2 == 'post') {
                    $post_id = $item->useract_value2;
            }
            
            $result = $this->postSafe($h, $item);
            if (is_array($result)) {
                $title = $result[0];
                $url = $result[1];
            }
            if (!isset($title) || !$title) { continue; } // skip if postis buried or pending, postSafe returns title if safe            
            $item->title = $title;
            $item->url = $url;        
            
            // Hide activity if its post has been buried or set to pending:
            if ($h->post->status == 'pending' || $h->post->status == 'buried') { continue; }
                       
            $userid = $item->useract_userid;
            $username = isset($item->user_username) ? $item->user_username : '';
            
            //$h->post->vars['catSafeName'] =  $h->getCatSafeName($h->post->category);
            $post_title = stripslashes(html_entity_decode(urldecode($title), ENT_QUOTES,'UTF-8'));
            //$title_link = $h->url(array('page'=>$post_id));
            if (FRIENDLY_URLS == "true") {
                $title_link = SITEURL . $item->url;
            } else {
                $title_link = SITEURL . 'index.php?page=' . $post_id;
            }
            
            // OUTPUT ITEM
            $output .= "<li class='activity_widget_item'>\n";
            
            if($h->isActive('avatar') && $activity_settings['widget_avatar']) {
                $h->setAvatar($userid, $activity_settings['widget_avatar_size']);
                $output .= "<div class='activity_widget_avatar'>\n";
                $output .= $h->linkAvatar();
                $output .= "</div> \n";
            }

            if ($activity_settings['widget_user']) {
                if (!$userid) { 
                        $output .= $h->lang('activity_anonymous');
                } else {
                    $output .= "<a class='activity_widget_user' href='" . $h->url(array('user' => $username)) . "'>" . $username . "</a> \n";
                }
            }
            
            $output .= "<div class='activity_widget_content'>\n";
            
            $result = $this->activitySwitch($h, $item);
            
            $output .= $result['output'] . "&quot;<a href='" . $title_link . $result['cid'] . "' >" . $post_title . "</a>&quot; \n";
            
            if ($activity_settings['time']) {                 
                $output .= "<small>";
                $output .= time_difference(unixtimestamp($item->useract_date), $h->lang) . " " . $h->lang("activity_post_ago");  
                $output .="</small>";
                //$output .= "<small>[" . date('g:ia, M jS', strtotime($item->useract_date)) . "]</small>";
            }
            
            $output .= "</div>\n";
            $output .= "</li>\n\n";
        }
        
        return $output;
    }
    
    
    /**
    * Get activity content (Profile and Activity Pages only)
    *
    * @param array $activity 
    * return string $output
    */
    public function activityContent($h, $item = array())
    {
           if (!$item) { return false; }

           $output = '';

           // Post used in Hotaru's url function
           if ($item->useract_key === 'post') {
                   $post_id = $item->useract_value;
           } elseif  ($item->useract_key2 === 'post') {
                   $post_id = $item->useract_value2;
           }
           
           $result = $this->postSafe($h, $item);
            if (is_array($result)) {
                $title = $result[0];
                $url = $result[1];
            }

           // Comment
           if ($item->useract_key == 'comment') {
               $comment = $h->getComment($item->useract_value);                    
               $comment_title = isset($comment->comment_content) ? stripslashes(html_entity_decode(urldecode($comment->comment_content), ENT_QUOTES,'UTF-8')) : '';
               $comment_title = truncate($comment_title, 80, true);
           }

           // content
           $post_title = isset($title) ? stripslashes(html_entity_decode(urldecode($title), ENT_QUOTES,'UTF-8')) : '';
           // not using $h->url as it loads post and category from db which takes time
           if (FRIENDLY_URLS == "true") {
               $title_link = isset($url) ? SITEURL . $url : '';
           } else {
               $title_link = SITEURL . 'index.php?page=' . $post_id;
           }

           $result = $this->activitySwitch($h, $item);

           if ($item->useract_key == 'comment') {
               $output = $result['output'] . "<a href='" . $title_link . $result['cid'] . "' data-toggle='tooltip' title='" . $comment_title . "' data-original-title='" . $comment_title . "' >" . $post_title . "</a>\n";
           } else {
               $output = $result['output'] . "<a href='" . $title_link . $result['cid'] . "' >" . $post_title . "</a>\n";
           }

           return $output;
    }
    
   
   /**
    * Determine the language for the action
    *
    * @param array $item
    * @return string $output
    */
    public function activitySwitch($h, $item = NULL)
    {
           if (!$item) { return false; }

           $cid = ''; // comment id string
           $output = '';

           switch ($item->useract_key) {
                   case 'comment':
                           $output = $h->lang("activity_commented") . " ";
                           $cid = "#c" . $item->useract_value; // comment id to be put on the end of the url
                           break;
                   case 'post':
                           if ($h->post->type) {
                               $post_lang = "activity_submitted_" . $h->post->type; // e.g. news, blog, etc.
                               $output = $h->lang($post_lang) . " ";
                           } else {
                               $output = $h->lang("activity_submitted_news") . " ";
                           }
                           break;
                   case 'vote':
                           switch ($item->useract_value) {
                                   case 'up':
                                           $output = $h->lang("activity_voted_up") . " ";
                                           break;
                                   case 'down':
                                           $output = $h->lang("activity_voted_down") . " ";
                                           break;
                                   case 'flag':
                                           $output = $h->lang("activity_voted_flagged") . " ";
                                           break;
                                   default:
                                           break;
                           }
						   if ($item->useract_key2 == 'comment') { $output = $output . $item->useract_key2 . ' '; }
                           break;
                   default:
                           // for plugins to add language of alternative "useract_key"s
                           $h->vars['activity_output'] = '';
                           $h->pluginHook('activity_output', '', array('key'=>$item->useract_key));
                           $output = $h->vars['activity_output'];
                           break;
           }

           return array('output'=>$output, 'cid'=>$cid);
    }
   
}

?>
