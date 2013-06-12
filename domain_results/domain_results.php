<?php
/**
 * name: Domain Results
 * description: Show a page of results for a given domain
 * version: 0.1
 * folder: domain_results
 * class: DomainResults
 * requires: bookmarking 0.1
 * hooks: theme_index_top, breadcrumbs, bookmarking_sort_filter, bookmarking_functions_preparelist, show_post_author_date
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
		
		if (!$get_domain) { return FALSE; }

		echo $h->lang["domain_results_pre_domain"] . "<a href='" . $h->url(array('domain'=>$get_domain)) . "'>". $h->lang["domain_results_domain"] . $get_domain . "</a>\n";
	}


	/** 
	 * http://stackoverflow.com/questions/399250/going-where-php-parse-url-doesnt-parsing-only-the-domain
	 */ 
	private function getDomain($url)
	{
		$pieces = parse_url($url);
		$domain = isset($pieces['host']) ? $pieces['host'] : '';
		
		if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs))
		{
			return $regs['domain'];
		}

		return false;
	}

	/**
	 * Check if "domain" is a key in the url
	 */
	private function isDomainPage($h)
	{
		$this->domain = $h->cage->get->getHtmLawed('domain');
		return ($this->domain) ? TRUE : FALSE;
	}


	/**
	 * Check if we're looking at domain results
	 */
	public function theme_index_top($h)
	{
		if (!$this->isDomainPage($h)) { return FALSE; }

		// deal with the page title:
		$h->pageTitle = $this->domain;
		if (!$h->pageName) { $h->pageName = 'popular'; }
		if ($h->pageName == $h->home) { $h->pageTitle .=  '[delimiter]' . SITE_NAME; }

		// set other properties
		$h->subPage = 'domain';
		$h->pageType = 'list';
	}


	/**
	 * Clean up breadcrumbs
	 */
	public function breadcrumbs($h)
	{ 
		if (!$this->isDomainPage($h)) { return FALSE; }

		return $this->domain;
	}


	/**
	 * Apply domain filtering to sort links
	 */
	public function bookmarking_sort_filter($h)
	{
		if (!$this->isDomainPage($h)) { return FALSE; }

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
		if (!$this->isDomainPage($h)) { return FALSE; }
	
		$h->vars['filter']['post_domain LIKE %s'] = "%" . urlencode($this->domain);
		unset($h->vars['filter']['post_archived = %s']); // no need to restrict to archived posts
	}
}