<?php

if (!defined('ABSPATH')) die('No direct access allowed');

/**
 * File based page cache drop in
 */
require_once(dirname(__FILE__) . '/file-based-page-cache-functions.php');

if (!defined('WPO_CACHE_DIR')) define('WPO_CACHE_DIR', untrailingslashit(WP_CONTENT_DIR) . '/wpo-cache');

/**
 * Load extensions.
 */
wpo_cache_load_extensions();

$no_cache_because = array();

// Don't cache robots.txt or htacesss or sitemap. Remember to properly escape any output to prevent injection.
if (strpos($_SERVER['REQUEST_URI'], 'robots.txt') !== false || strpos($_SERVER['REQUEST_URI'], '.htaccess') !== false || strpos($_SERVER['REQUEST_URI'], 'sitemap.xml') !== false) {
	$no_cache_because[] = 'The file path is unsuitable for caching ('.$_SERVER['REQUEST_URI'].')';
}

// Don't cache non-GET requests.
if (!isset($_SERVER['REQUEST_METHOD']) || 'GET' !== $_SERVER['REQUEST_METHOD']) {
	$no_cache_because[] = 'The request method was not GET ('.$_SERVER['REQUEST_METHOD'].')';
}

$file_extension = $_SERVER['REQUEST_URI'];
$file_extension = preg_replace('#^(.*?)\?.*$#', '$1', $file_extension);
$file_extension = trim(preg_replace('#^.*\.(.*)$#', '$1', $file_extension));

// Don't cache disallowed extensions. Prevents wp-cron.php, xmlrpc.php, etc.
if (!preg_match('#index\.php$#i', $_SERVER['REQUEST_URI']) && in_array($file_extension, array( 'php', 'xml', 'xsl' ))) {
	$no_cache_because[] = 'The request extension is not suitable for caching';
}

// Don't cache if logged in.
if (!empty($_COOKIE)) {
	$wp_cookies = array('wordpressuser_', 'wordpresspass_', 'wordpress_sec_', 'wordpress_logged_in_');

	if (empty($GLOBALS['wpo_cache_config']['enable_user_caching']) || false == $GLOBALS['wpo_cache_config']['enable_user_caching']) {
		foreach ($_COOKIE as $key => $value) {
			foreach ($wp_cookies as $cookie) {
				if (false !== strpos($key, $cookie)) {
					$no_cache_because[] = 'WordPress login cookies were detected';
					break(2);
				}
			}
		}
	}

	if (!empty($_COOKIE['wpo_commented_posts'])) {
		foreach ($_COOKIE['wpo_commented_posts'] as $path) {
			if (rtrim($path, '/') === rtrim($_SERVER['REQUEST_URI'], '/')) {
				$no_cache_because[] = 'The user has commented on a post (comment cookie set)';
				break;
			}
		}
	}

	// get cookie exceptions from options.
	$cache_exception_cookies = !empty($GLOBALS['wpo_cache_config']['cache_exception_cookies']) ? $GLOBALS['wpo_cache_config']['cache_exception_cookies'] : array();
	// filter cookie exceptions.
	$cache_exception_cookies = apply_filters('wpo_cache_exception_cookies', $cache_exception_cookies);

	// check if any cookie exists from exception list.
	if (!empty($cache_exception_cookies)) {
		foreach ($_COOKIE as $key => $value) {
			foreach ($cache_exception_cookies as $cookie) {
				if ('' != trim($cookie) && false !== strpos($key, $cookie)) {
					$no_cache_because[] = 'An excepted cookie was set ('.$key.')';
					break 2;
				}
			}
		}
	}
}

// check in not disabled current user agent
if (!empty($_SERVER['HTTP_USER_AGENT']) && false === wpo_is_accepted_user_agent($_SERVER['HTTP_USER_AGENT'])) {
	$no_cache_because[] = "In the settings, caching is disabled for matches for this request's user agent";
}

// Deal with optional cache exceptions.
if (wpo_url_in_exceptions(wpo_current_url())) {
	$no_cache_because[] = 'In the settings, caching is disabled for matches for the current URL';
}

if (!empty($_GET)) {
	// get variables used for building filename.
	$get_variable_names = wpo_cache_query_variables();

	$get_variables = wpo_cache_maybe_ignore_query_variables(array_keys($_GET));

	// if GET variables include one or more undefined variable names then we don't cache.
	$diff = array_diff($get_variables, $get_variable_names);
	if (!empty($diff)) {
		$no_cache_because[] = "In the settings, caching is disabled for matches for one of the current request's GET parameters";
	}
}

if (!empty($no_cache_because)) {
	// Only output if the user has turned on debugging output
	if (defined('WP_DEBUG') && WP_DEBUG) {
		wpo_cache_add_footer_output("\n<!-- WP Optimize page cache - https://getwpo.com - page not served from cache because: ".implode(', ', array_filter($no_cache_because, 'htmlspecialchars'))." -->\n");
	}
	return;
}

wpo_serve_cache();

ob_start('wpo_cache');
