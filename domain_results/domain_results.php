<?php
/**
 * name: Domain Results
 * description: Show a page of results for a given domain
 * version: 0.2
 * folder: domain_results
 * class: DomainResults
 * requires: bookmarking 0.1
 * hooks: theme_index_top, theme_index_main, breadcrumbs, bookmarking_sort_filter, bookmarking_functions_preparelist, show_post_author_date
 * author: Nick Ramsay
 * authorurl: http://hotarucms.org/member.php?1-Nick
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
 * @link      http://hotarucms.com
 */
class DomainResults
{
	private $domain = '';


	/**
	 * Displays "More from site.com" wherever the plugin hook is.
	 */
	public function show_post_author_date($h)
	{
		$get_domain = $this->getDomain($h->post->domain);
		
		if (!$get_domain) { return false; }

                $h->vars['domain_results']['domain'] = $get_domain;
                $h->template('domain_results_link', 'domain_results', false);
                
		//echo $h->lang["domain_results_pre_domain"] . "<a href='" . $h->url(array('domain'=>$get_domain)) . "'>". $h->lang["domain_results_domain"] . $get_domain . "</a>\n";
	}


	/** 
	 * get domain, tld and subdomain from url
	 */ 
	private function getDomain($url)
	{	
                if (preg_match("/([a-z0-9\-]+\.)*[a-z0-9\-]+\.[a-z]+/", parse_url($url, PHP_URL_HOST), $_domain_tld))
                { 
                    return $_domain_tld[0];
                }
		return false;
	}

	/**
	 * Check if "domain" is a key in the url
	 */
	private function isDomainPage($h)
	{
		$this->domain = $h->cage->get->getHtmLawed('domain');
		return ($this->domain) ? true : false;
	}


	/**
	 * Check if we're looking at domain results
	 */
	public function theme_index_top($h)
	{
		if (!$this->isDomainPage($h)) { return false; }

		// deal with the page title:
		$h->pageTitle = $this->domain;
                
		if (!$h->pageName) {
                    $h->pageName = 'popular';
                }
		
                if ($h->pageName == $h->home) {
                    $h->pageTitle .=  '[delimiter]' . SITE_NAME;
                }

		// set other properties
		$h->subPage = 'domain';
		$h->pageType = 'list';
	}

        
        /**
        * Display All Activity page
        */
        public function theme_index_main($h)
        {
//                if ($h->pageName == 'domains') {
//                    $this->domainsList($h);
//                    return true;
//                }
                
                return false;
        }
    
        
        /**
         * 
         */
        private function domainsList($h)
        {
            // gets query and total count for pagination
            $domains_query = $this->getDomains(0, 'query');
            $domains_count = $this->getDomains(0, 'count');

            $limit = 40;
            // pagination 
            $h->vars['pagedResults'] = $h->pagination($domains_query, $domains_count, $limit, 'domain_results');

            $h->template('domain_results_list');

            if ($h->vars['pagedResults']) { echo $h->pageBar($h->vars['pagedResults']); }
        }
    
        
        private function getDomains($h, $limitCount = 0, $type = '', $fromId = 0)
	{
		$limit = (!$limitCount) ? '' : "LIMIT " . $limitCount;
		
                $select = ($type == 'count') ? 'count(post_id)' : 'P.* '; //user_id, U.user_username, U.user_email, U.user_role, U.user_date ';
                
                $sql = "SELECT " . $select . " FROM " . TABLE_POSTS . " AS P ORDER BY P.url ASC " . $limit;
                $query = $sql; // $h->db->prepare($sql);

                if ($type == 'query') { return $query; }
                $result = ($type == 'count') ? $h->db->get_var($query) : $h->db->get_results($query);

		if ($result) { return $result; } else { return false; }
	}
        

	/**
	 * Clean up breadcrumbs
	 */
	public function breadcrumbs($h)
	{ 
		if (!$this->isDomainPage($h)) { return false; }

		return $this->domain;
	}


	/**
	 * Apply domain filtering to sort links
	 */
	public function bookmarking_sort_filter($h)
	{
		if (!$this->isDomainPage($h)) { return false; }

		$h->vars['popular_link'] = $h->url(array('page'=>'popular', 'domain'=>$this->domain));
		$h->vars['upcoming_link'] = $h->url(array('page'=>'upcoming', 'domain'=>$this->domain));
		$h->vars['latest_link'] = $h->url(array('page'=>'latest', 'domain'=>$this->domain));
		$h->vars['all_link'] = $h->url(array('page'=>'all', 'domain'=>$this->domain));

		$h->vars['24_hours_link'] = $h->url(array('sort'=>'top-24-hours', 'domain'=>$this->domain));
		$h->vars['48_hours_link'] = $h->url(array('sort'=>'top-48-hours', 'domain'=>$this->domain));
		$h->vars['7_days_link'] = $h->url(array('sort'=>'top-7-days', 'domain'=>$this->domain));
		$h->vars['30_days_link'] = $h->url(array('sort'=>'top-30-days', 'domain'=>$this->domain));
		$h->vars['365_days_link'] = $h->url(array('sort'=>'top-365-days', 'domain'=>$this->domain));
		$h->vars['all_time_link'] = $h->url(array('sort'=>'top-all-time', 'domain'=>$this->domain));
	}


	/**
	 * Filter posts to this domain
	 */
	public function bookmarking_functions_preparelist($h)
	{
		if (!$this->isDomainPage($h)) { return false; }
	
		$h->vars['filter']['post_domain LIKE %s'] = "%" . urlencode($this->domain);
		unset($h->vars['filter']['post_archived = %s']); // no need to restrict to archived posts
	}
}