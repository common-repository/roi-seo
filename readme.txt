=== ROI_SEO ===
Contributors: roiseo
Donate link: http://www.redcross.ca/donate
Tags: 
Requires at least: 3.0
Tested up to: 3.0
Stable tag: 0.3

THIS PLUGIN IS NO LONGER SUPPORTED


== Description ==

!! **THIS PLUGIN IS NO LONG SUPPORTED.** PLEASE USE [YOAST SEO PLUGIN](https://wordpress.org/plugins/wordpress-seo/) INSTEAD.

== Frequently Asked Questions ==


== Screenshots ==

== Changelog ==

= 0.3 (Dec 10, 2014) =
* minor update

= 0.2.3 (May 4, 2012) =
* fixed: support for custom post type formatting
* fixed: fix bug when multi-activing plugin default settings weren't entered
* fixed: support for if `$wp_query->query_vars['author_name']` isn't set on on authors page

= 0.2.2 (Feb 20, 2012) =
* fixed: support for is_author() pages
* fixed: issue where if option page_for_posts was HOME, the most recent post's SEO info was used (instead of HOME)

= 0.2.1 (Feb 1, 2012) =
* fixed: bug where wp_title outputs twice when using 'automatic'

= 0.2 (Jan 13, 2012) =
* fixed: bug where 'tags' where using archive format instead of tag format
* **NEW!:** option to automatically add tags (using Wordpress's `wp_title()` and `wp_head()` hooks) instead of manually editing `header.php`
	* select "auto" or "manual" HTML output via *Settings > ROI Search Engine Optimization > Options*
	* updated instructions & FAQ to support both methods of output
	
= 0.1.8 (Jan 3, 2012) =
* fixed: issues with default keywords being the description in coma's instead of the keywords for the home page

= 0.1.7 (Dec 27, 2011) =
* changes: to readme.txt (FAQ)
* fixed: hide metabox values when is_home && page_on_front == $post->post_type == page

= 0.1.6 (Dec 9, 2011) =
* fixed: using jQuery() instead of $() - more compatible/friendly with other plugins

= 0.1.5 (Dec 8, 2011) =
* fixed: folder name change causing broken URL's to plugin resources

= 0.1.2 (Nov 25, 2011) =
* fixed: admin: only printing jquery scripts on pages where plugin appears,  

= 0.1.1 (Nov 25, 2011) =
* change: made "Plugin by.." credit optional in settings page; default off

= 0.1 (Nov 24, 2011) =
* Initial Release

= 0.0 (Nov 18, 2011) =
* Started Development


== Upgrade Notice ==

= 0.2 (Jan 13, 2012) =
* If had < 2.0 installed and want to use the "Automatic" feature, you have to un-comment any header tags that were replaced with `roi_seo()` and remove the `roi_seo()`. The `roi_seo()` method still works and is still reccomended.

= 0.1 (Nov 24, 2011) =
* ROI_SEO replaces jquery in admin_enqueue_scripts w/ http://code.jquery.com/jquery-latest.js