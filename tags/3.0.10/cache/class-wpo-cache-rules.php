<?php

if (!defined('ABSPATH')) die('No direct access allowed');

/**
 * Page caching rules and exceptions
 */

if (!class_exists('WPO_Cache_Config')) require_once('class-wpo-cache-config.php');

require_once dirname(__FILE__) . '/file-based-page-cache-functions.php';

if (!class_exists('WPO_Cache_Rules')) :

class WPO_Cache_Rules {

	/**
	 * Cache config object
	 *
	 * @var mixed
	 */
	public $config;

	/**
	 * Instance of this class
	 *
	 * @var mixed
	 */
	public static $instance;

	public function __construct() {
		$this->config = WPO_Cache_Config::instance()->get();
		$this->setup_hooks();
	}

	/**
	 * Setup hooks/filters
	 */
	public function setup_hooks() {
		add_action('save_post', array($this, 'purge_post_on_update'), 10, 1);
		add_action('wp_trash_post', array($this, 'purge_post_on_update'), 10, 1);
		add_action('comment_post', array($this, 'purge_post_on_comment'), 10, 3);
		add_action('wp_set_comment_status', array($this, 'purge_post_on_comment_status_change'), 10, 1);
		add_action('after_switch_theme', array($this, 'purge_cache'), 10);
	}

	/**
	 * Purge post cache when there is a new approved comment
	 *
	 * @param  int        $comment_id  Comment ID.
	 * @param  int|string $approved    Comment approved status. can be 0, 1 or 'spam'.
	 * @param  array      $commentdata Comment data array.
	 */
	public function purge_post_on_comment($comment_id, $approved, $commentdata) {
		if (1 !== $approved) {
			return;
		}

		if (!empty($this->config['enable_page_caching'])) {
			$post_id = $commentdata['comment_post_ID'];

			WPO_Page_Cache::delete_single_post_cache($post_id);
		}
	}

	/**
	 * Every time a comment's status changes, purge it's parent posts cache
	 *
	 * @param int $comment_id Comment ID.
	 */
	public function purge_post_on_comment_status_change($comment_id) {
		if (!empty($this->config['enable_page_caching'])) {
			$comment = get_comment($comment_id);
			$post_id = $comment->comment_post_ID;

			WPO_Page_Cache::delete_single_post_cache($post_id);
		}
	}

	/**
	 * Automatically purge all file based page cache on post changes
	 * We want the whole cache purged here as different parts
	 * of the site could potentially change on post updates
	 *
	 * @param Integer $post_id - WP post id
	 */
	public function purge_post_on_update($post_id) {
		$post_type = get_post_type($post_id);

		if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || 'revision' === $post_type) {
			return;
		}

		/**
		 * Purge the whole cache if set to true, only the edited post otherwise. Default is false.
		 *
		 * @param boolean $purge_all_cache The default filter value
		 * @param integer $post_id         The saved post ID
		 */
		if (apply_filters('wpo_purge_all_cache_on_update', false, $post_id)) {
			$this->purge_cache();
		} else {
			if (apply_filters('wpo_delete_cached_homepage_on_post_update', true, $post_id)) WPO_Page_Cache::delete_homepage_cache();
			WPO_Page_Cache::delete_single_post_cache($post_id);
		}
	}

	/**
	 * Clears the cache.
	 */
	public function purge_cache() {
		if (!empty($this->config['enable_page_caching'])) {
			wpo_cache_flush();
		}
	}

	/**
	 * Returns an instance of the current class, creates one if it doesn't exist
	 *
	 * @return object
	 */
	public static function instance() {
		if (empty(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

endif;
