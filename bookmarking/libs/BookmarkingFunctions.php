<?php
/**
 * Bookmarking functions
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

class BookmarkingFunctions
{
    /**
     * Access modifier to set protected properties
     */
    public function __set($var, $val)
    {
        $this->$var = $val;
    }
    
    
    /**
     * Access modifier to get protected properties
     * The & is necessary (http://bugs.php.net/bug.php?id=39449)
     */
    public function &__get($var)
    {
        return $this->$var;
    }
    
    
    /**
     * Prepare filter and breadcrumbs for social bookmarking pages
     * Two main types: one for list pages and the other for 
     * miscelleneous stuff like Sidebar Posts
     *
     * @param string $type e.g. latest, upcoming, top-24-hours
     * @param string $return - 'posts', 'count' or 'query'
     * @return array
     */
    public function prepareList($h, $type = '', $return = 'posts')
    {
        if (!isset($h->vars['filter'])) { $h->vars['filter'] = array(); }
        
        if ($type) {            
            // For the posts widget or other non-pages... 
            $h->vars['filter'] = array(); // flush filter
            $this->prepareListFilters($h, $type);            
        } else {
            // for pages, i.e. lists of stories with pagination
            switch ($h->pageName) {
                case 'popular':
                    $this->prepareListFilters($h, 'top');
                    break;
                case 'latest':
                case 'new':
                    $this->prepareListFilters($h, 'new');
                    break;
                case 'upcoming':
                    $this->prepareListFilters($h, 'upcoming');
                    break;
                case 'sort':
                    $sort = $h->cage->get->testPage('sort');
                    $this->prepareListFilters($h, $sort);
                    break;
                default:
                    $this->prepareListFilters($h, 'all');
                }

            $h->pluginHook('bookmarking_functions_preparelist', '', array('return' => $return));
        }
        
        // select
        if (!isset($h->vars['select'])) {
//            if ($type) {
//                // for widgets or other non-pages
//                $h->vars['joinPostVotes'] = false;
//                $h->vars['select'] = 'P.*, U.user_username, U.user_email ';  
//            } else {
//                $h->vars['joinPostVotes'] = true;;
//                $h->vars['select'] = 'P.*, U.user_username, U.user_email, PV.vote_rating '; 
//            }
            
            // this is instead of the alternative join above. Found it to be faster without join
            $h->vars['select'] = 'P.*, U.user_username, U.user_email '; 
        }
        
        // ordrby
        if (!isset($h->vars['orderby'])) {
            $h->vars['orderby'] = 'P.post_date DESC'; 
        }
        
        $limit = 0; 
        $all = true;
        
        // $type is used in sidebar posts, etc so we need to specify a limit, e.g. 10.
        if ($type) { 
            $limit = $h->vars['limit'] ? $h->vars['limit'] : $limit = 0;
            $all = false;
        }

        // if we want to count the totals, we need to replace the select clause with COUNT, but some queries that use MATCH and relevance are a bit complicated, 
        // so we'll let those plugins (e.g. search) add COUNT to their queries themselves and skip them here (which we can do by checking if select is an array).

        if ($return == 'count' && (!is_array($h->vars['select']))) { $h->vars['select'] = "count(post_id) AS number"; }
        if ($return == 'query') { $all = true; }    // this removes the "LIMIT" parameter so we can add it later when paginating.
        
        if ($all == true) { $limit = ''; } elseif ($limit == 0) { $limit = "20"; }
        
        // default to posts of type "news" if not otherwise set
        if (!isset($h->vars['filter']['post_type = %s'])) { $h->vars['filter']['post_type = %s'] = $h->vars['bookmarking_settings']['default_type']; }

        // get the prepared SQL query
        $prepare_array = $h->db->select($h, array($h->vars['select']), 'posts', $h->vars['filter'],
                $h->vars['orderby'], $limit, false, true, $return);
        
        // count
        if($return == 'count') {
            unset($h->vars['select']);  // so it doesn't get used again unintentionally
            $count_array = $h->db->getData($h, 'posts', $prepare_array);
            if ($count_array)  {
                //$h->messages[$h->lang['bookmarking_number_of_posts'] . ' : ' . number_format($count_array[0]->number,0)] = 'alert-info';
                return $count_array[0]->number;
            }
            return 0;
        }
            
        // query
        if ($return == 'query') { 
            if (isset($prepare_array[1])) {
                return $h->db->prepare($prepare_array);
            }
            return $prepare_array[0];
        }
        
        // posts OR post count depending on the query
        return $h->db->getData($h, 'posts', $prepare_array);
    }
    
    
    /**
     * Prepare list filters
     *
     * @param string $type e.g. latest, upcoming, top-24-hours
     */
    public function prepareListFilters($h, $type = '')
    {
        $start = date('YmdHis', time_block());
        
        switch ($type) {
            case 'new':
                // Filters page to "new" stories only
                $h->vars['filter']['post_archived = %s'] = 'N'; 
                $h->vars['filter']['post_status = %s'] = 'new';
                $h->vars['orderby'] = "post_date DESC";
                break;
            
            case 'upcoming':
                // Filters page to "new" stories by most votes, but only stories from the last X days!
                $vote_settings = unserialize($h->getSetting('vote_settings', 'vote')); 
                $upcoming_duration = "-" . $vote_settings['upcoming_duration'] . " days"; // default: -5 days
                $h->vars['filter']['post_archived = %s'] = 'N'; 
                $h->vars['filter']['post_status = %s'] = 'new'; 
                $end = date('YmdHis', strtotime($upcoming_duration)); // should be negative
                $h->vars['filter']['(post_date >= %s AND post_date <= %s)'] = array($end, $start); 
                $h->vars['orderby'] = "post_votes_up DESC, post_date DESC";
                break;
                
            case 'top-24-hours':
                 // Filters page to "top" stories from the last 24 hours only
                $h->vars['filter']['post_status = %s'] = 'top';
                $end = date('YmdHis', strtotime("-1 day"));
                $h->vars['filter']['(post_date >= %s AND post_date <= %s)'] = array($end, $start); 
                $h->vars['orderby'] = "post_votes_up DESC, post_date DESC";
                break;
            
            case 'top-48-hours':
                // Filters page to "top" stories from the last 48 hours only
                $h->vars['filter']['post_status = %s'] = 'top'; 
                $end = date('YmdHis', strtotime("-2 days"));
                $h->vars['filter']['(post_date >= %s AND post_date <= %s)'] = array($end, $start); 
                $h->vars['orderby'] = "post_votes_up DESC, post_date DESC";
                break;
            
            case 'top-7-days':
                // Filters page to "top" stories from the last 7 days only
                $h->vars['filter']['post_status = %s'] = 'top'; 
                $end = date('YmdHis', strtotime("-7 days"));
                $h->vars['filter']['(post_date >= %s AND post_date <= %s)'] = array($end, $start); 
                $h->vars['orderby'] = "post_votes_up DESC, post_date DESC";
                break;
            
            case 'top-30-days':
                // Filters page to "top" stories from the last 30 days only
                $h->vars['filter']['post_status = %s'] = 'top'; 
                $end = date('YmdHis', strtotime("-30 days"));
                $h->vars['filter']['(post_date >= %s AND post_date <= %s)'] = array($end, $start); 
                $h->vars['orderby'] = "post_votes_up DESC, post_date DESC";
                break;
            
            case 'top-365-days':
                 // Filters page to "top" stories from the last 365 days only
                $h->vars['filter']['post_status = %s'] = 'top'; 
                $end = date('YmdHis', strtotime("-365 days"));
                $h->vars['filter']['(post_date >= %s AND post_date <= %s)'] = array($end, $start); 
                $h->vars['orderby'] = "post_votes_up DESC, post_date DESC";
                break;
            
            case 'top-all-time':
                // Filters page to "top" stories in order of votes
                $h->vars['filter']['post_status = %s'] = 'top'; 
                $h->vars['orderby'] = "post_votes_up DESC, post_date DESC";
                break;
            
            case 'top':
                // Assume 'top' page and filter to 'top' stories.
                $h->vars['filter']['post_archived = %s'] = 'N'; 
                $h->vars['filter']['post_status = %s'] = 'top';
                $h->vars['orderby'] = "post_date DESC";
                break;

            default:
                // Filters page to "all" stories
                $h->vars['filter']['post_archived = %s'] = 'N'; 
                $h->vars['filter']['(post_status = %s OR post_status = %s)'] = array('top', 'new');
                $h->vars['orderby'] = "post_date DESC";
                break;
        }             
    }
    
    
    public function getVotedPostsByThisUser($h)    //$post_query, $post_count, $posts_per_page
    {
            if (!$h->isActive('vote')) { return false; }
            
            if (!$h->postList) { return false; }
            
            // get a list of the post_ids
            $inArrayString = "(" . implode(',', $h->postList) . ")";
            //print_r($inArrayString);
            
            if ($h->currentUser->loggedIn) {
                $userId = $h->currentUser->id;
                
                $sql = "SELECT vote_post_id FROM " . TABLE_POSTVOTES . " WHERE vote_post_id IN " . $inArrayString . " AND vote_user_id = %d AND vote_rating != %d";
                $currentUserVotedPosts = $h->db->get_results($h->db->prepare($sql, $userId, -999), ARRAY_N);   
            } else {
                $sql = "SELECT vote_post_id FROM " . TABLE_POSTVOTES . " WHERE vote_post_id IN " . $inArrayString . " AND vote_user_ip = %s AND vote_user_id = %d AND vote_rating != %d";
                $currentUserVotedPosts = $h->db->get_results($h->db->prepare($sql, $h->cage->server->testIp('REMOTE_ADDR'), 0, -999), ARRAY_N); 
            }
            
            $votedArray = array();
            if ($currentUserVotedPosts) {
                foreach ($currentUserVotedPosts as $key => $val) {                                     
                    if (isset($val)) {
                        //print $val[0];
                        $votedArray[$val[0]] = 1;
                    }
                }
            }
            
            return $votedArray;
    }
    
    
    public function makePostList($h, $list)
    {
            if ($list) {
                // reset the postList as we are about to create a new one
                $h->postList = array();
                foreach ($list as $item)  // or use array_map
                {
                    $h->postList[] = $item->post_id;
                }
            }
    }
}
