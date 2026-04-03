<?php

namespace WordKeeper\System;

// Common core functions with special handling
// Including them either speeds them up by allowing special OpCode instructions
// Or reduces moderate overhead associated with fallback from the active namespace
// without having to use FQFN's for every reference
use add_action;
use apply_filters;
use array_merge;
use array_push;
use array_unique;
use class_exists;
use clean_post_cache;
use count;
use current_user_can;
use explode;
use filter_var;
use func_get_arg;
use func_num_args;
use get_comment;
use get_object_taxonomies;
use get_option;
use get_permalink;
use get_post;
use get_term_link;
use get_the_terms;
use http_build_query;
use in_array;
use is_admin;
use is_array;
use is_user_logged_in;
use is_wp_error;
use json_encode;
use rtrim;
use str_replace;
use strpos;
use trim;
use wc_get_page_permalink;
use wp_doing_cron;
use wp_is_post_revision;

/**
 * The WordKeeper System Purge class
 */
class Purge {

	/**
	 * List of GET URLs to purge
	 */
 	protected static $gets = array();

	/**
	 * List of POST URLs to purge
	 */
	protected static $posts = array();

	/**
	 * Array of post objects added to the purger during the page load lifecycle
	 *
	 * @var array
	 */
	protected static $objects = array();

	/**
	 * Queud list of post IDs to purge immediately before purge submission
	 *
	 * @var array
	 */
	protected static $queued = array();

	/**
	 * Type of purge
	 */
	protected static $type = '';

	/**
	 * Storage for trimmed home URL
	 *
	 * @var string
	 */
	protected static $home = '';


	/**
	 * Initialize the class and set its properties
	 */
	public function __construct(){

	}


	/**
	 * Execute the specified purge + type (if applicable)
	 */
	public function purge(){
		// Purge or exit based on purge type/params
		switch(self::$type){
			case 'all':
				Purge::purge_all();
				break;
			case 'url':
				if(empty(self::$gets) && empty(self::$posts) && empty(self::$queued)){
					return;
				}
				Purge::purge_by_url();
				break;
			default:
				return;
				break;
		}
	}


	/**
	 * Purges cache by provided URL list
	 *
	 * @return void
	 */
	public static function purge_by_url(){
		// Late purge queued post IDs to ensure that they have fully saved with all relevant metadata changes before purging
		if(!empty(self::$queued)){
			foreach(self::$queued as $post_id){
				self::purge_post($post_id,  true);
			}
		}

		// Normalize the posts array to a single post if there's only one post object in the array
		// Helps with backwards compatibility
		$objects = (count(self::$objects) == 1) ? self::$objects[0] : self::$objects;

		// Filter pages and pass the full list of purged posts in case extra filtration is needed on pages passed to purger
		$gets = apply_filters('wordkeeper/system/purge', self::$gets, $objects);

		// Determine scheme and be sure to include alternate schemes in the purge request
		$scheme = (strpos(self::$home, 'https://') !== false) ? 'https://' : 'http://';
		$switchscheme = (strpos(self::$home, 'https://') !== false) ? 'http://' : 'https://';

		// Make sure that versions of the page with http and https or ending with/without a slash are in the array
		foreach($gets as $get){
			// Add the alternate scheme version to the pages array
			$gets[] = str_replace($scheme, $switchscheme, $get);

			// Switch terminating slash as long as the URL doesn't have a query string or file extension
			if(strpos($get, '?') === false && preg_match('#\.[0-9a-zA-Z]+$#', $get) == 0){
				// Add alternating version of page (slash/no-slash) to purge array if it doesn't exist
				$last = substr($get, -1);
				$switchslash = ($last == '/') ? rtrim($get, '/') : $get . '/';
				$switchboth = ($last == '/') ? rtrim(str_replace($scheme, $switchscheme, $get), '/') : str_replace($scheme, $switchscheme, $get) . '/';

				$gets[] = $switchslash;
				$gets[] = $switchboth;
			}
		}

		// Remove any duplicate URLs from the purge list
		$gets = array_values(array_unique($gets));

		// Make sure that versions of the page with http and https are in the array
		$posts = array_keys(self::$posts);
		foreach(self::$posts as $post){
			$addpost = str_replace($scheme, $switchscheme, $post['url']);
			if(!isset(self::$posts[$addpost])){
				self::$posts[$addpost] = array(
					'url' => $addpost,
					'body' => $post['body']
				);
			}
		}

		// Remove legacy page keys from the array (if present from old hooks)
		if(is_array($gets)){
			if(isset($gets['pages']) && is_array($gets['pages'])){
				$partial = $gets;
				unset($partial['pages']);
				$gets = array_unique(array_merge($partial, $gets['pages']));
				unset($partial);
			}
		}

		// Was the last purge less than 4 seconds ago?
		// If so, queue purge for next purge event
		$lastpurge = get_transient('wordkeeper/purge/last');
		if(!empty($lastpurge)){
			if((strtotime('now') - ((int) $lastpurge)) <= 3){
				// Are there existing queued purges?
				// Merge them if so
				$nextpurge = get_transient('wordkeeper/purge/next');
				if(is_array($nextpurge)){
					$nextpurge = array_merge($gets, $nextpurge);
					$nextpurge = array_values(array_unique($nextpurge));
				}
				// Otherwise this purge is the full set of purges
				else{
					$nextpurge = $gets;
				}

				// Set the next purge to include this purge's URLs and exit
				set_transient('wordkeeper/purge/next', $nextpurge, 86400);
				return true;
			}
		}

		// Get any URLs that weren't successfully purged in
		// a previous purge due to rate limiting
		$addpurges = get_transient('wordkeeper/purge/next');
		if(!empty($addpurges)){
			// Only support up to 1000 past page purges to ensure no "abuse"
			if(count($gets) < 1000){
				$gets = array_values(array_unique(array_merge($gets, $addpurges)));
				delete_transient('wordkeeper/purge/next');
			}
		}

		// Skip purge requests if there's nothing to purge
		if(empty($gets) && empty($posts)){
			return;
		}

		// JSON encode page list
		$gets = json_encode($gets);
		$posts = json_encode(self::$posts);

		// Parse and pass user
		$user = explode('/', trim(__DIR__, '/'));
		$user = $user[1];

		//  Submit the list of pages to the purger
		$response = Utilities::http_post(
			'http://purge/?cache=url&timestamp=' . strtotime('now'),
			http_build_query(
				array(
					'user'  => $user,
					'cache' => 'url',
					'auth'  => (file_exists(dirname(\ABSPATH) . '/auth')) ? file_get_contents(dirname(\ABSPATH) . '/auth') : '',
					'gets' => $gets,
					'posts' => $posts,
					'path' => \ABSPATH,
				)
			)
		);

		// If the purge is rate limited, save the page list for later
		if(!empty($response) && $response['status_code'] == 429){
			$gets = json_decode($gets);
			set_transient('wordkeeper/purge/next', $gets, 86400);
		}

		// If the purge was successful, flag the last purge time
		$success = (!empty($response) && $response['response'] == 'OK') ? true : false;
		if($success){
			set_transient('wordkeeper/purge/last', strtotime('now'));
		}

		// Return whether purge succeeded
		return $success;
	}


	/**
	 * Purge all caches
	 *
	 * @return void
	 */
	public static function purge_all(){
		$user = explode('/', trim(__DIR__, '/'));
		$user = $user[1];

		$response = Utilities::http_post(
			'http://purge/?cache=all',
			http_build_query(
				array(
					'user'  => $user,
					'cache' => 'all',
					'auth'  => (file_exists(dirname(\ABSPATH) . '/auth')) ? file_get_contents(dirname(\ABSPATH) . '/auth') : '',
					'path' => \ABSPATH,
				)
			)
		);

		return $response;
	}


	/**
	 * Purge all caches
	 * @return void
	 */
	public static function purge_cache(){
		self::$type = 'all';
	}


	/**
	 * Purge entire page cache on theme changes
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function purge_theme(){
		self::$type = 'all';
	}


	/**
	 * Purge a post when it is changed in key ways (published, updated, etc)
	 *
	 * @access public
	 * @param mixed $post_id
	 * @return void
	 */
	public static function purge_post($post_id, $force = false){
		// Exit early if we're already purging all caches
		if(self::$type == 'all'){
			return;
		}

		static $uniques = array();

		// Only purge for valid posts that haven't already been added to the purge list
		if($force || (is_numeric($post_id) && !in_array($post_id, $uniques))){

			// Fetch the post object for further filtering
			$post = get_post($post_id);

			// If the post comes back empty, exit now
			if(empty($post)){
				return;
			}

			if($force || (is_admin() && is_user_logged_in() && current_user_can('publish_posts') && current_user_can('edit_posts')) || wp_doing_cron()){

				// Don't purge for autosaves
				if(defined('DOING_AUTOSAVE') && \DOING_AUTOSAVE){
					return;
				}

				// Don't purge for shop orders
				if($post->post_type == 'shop_order'){
					return;
				}

				// If it's an ACF field group, purge all instead
				if($post->post_type == 'acf-field-group'){
					self::$type = 'all';
					return;
				}

				// Don't purge for revisions
				if($post->post_status == 'revision'){
					return;
				}

				// Don't purge for auto-drafts
				if($post->post_status == 'auto-draft'){
					return;
				}

				// Only purge drafts if forced
				if($post->post_status == 'draft' && !$force){
					return;
				}

				// Only purge drafts if forced
				if($post->post_status == 'pending' && !$force){
					return;
				}

				// Only purge private posts if the forced
				if($post->post_status == 'private' && !$force){
					return;
				}

				// Detect Divi dynamic CSS mode and purge all if it's enabled
				if(function_exists('et_is_builder_plugin_active')){
					if(et_is_builder_plugin_active()){
						$options = get_option('et_pb_builder_options', array());
						$dynamic_css = isset($options['performance_main_dynamic_css']) ? $options['performance_main_dynamic_css'] : 'on';
					}
					else{
						$dynamic_css = et_get_option('divi_dynamic_css', 'on');
					}

					// If dyanmic CSS isn't off, clear the entire site cache
					// Divi is weird about false values, though.  It could be set to false as eithier a boolean or a string
					if($dynamic_css !== false && $dynamic_css !== 'false'){
						self::$type = 'all';
						return;
					}
				}

				// Set the purge type to URL at this point
				// It's not previously set to all and we have
				// filtered for exceptions by now
				self::$type = 'url';

				// Get home page URL, current scheme, and the alternate scheme for each URL
				if(empty(self::$home)){
					self::$home = rtrim(get_option('home'), '/');
				}

				// Process purges for AJAX URLs that are cacheable POSTs
				if($post->post_type == 'product' || $post->post_type == 'product_variation'){
					// Add default gallery links to purge, include as post instead of get and include product_id
					self::$gets[] = self::$home . '/?wc-ajax=get_default_gallery';
					self::$posts[self::$home . '/?wc-ajax=get_default_gallery'] = array(
						'url' => self::$home . '/?wc-ajax=get_default_gallery',
						'body' => 'product_id=' . $post->ID
					);

					// Add variation gallery links to purge, include as post instead of get and include product_id
					self::$gets[] = self::$home . '/?wc-ajax=get_variation_gallery';
					self::$posts[self::$home . '/?wc-ajax=get_variation_gallery'] = array(
						'url' => self::$home . '/?wc-ajax=get_variation_gallery',
						'body' => 'product_id=' . $post->ID
					);
				}

				// Add basic/global URLs to purge array if we haven't already done that
				if(count(self::$gets) == 0){
					self::$gets[] = self::$home;
					self::$gets[] = self::$home . '/';
					self::$gets[] = self::$home . '/feed';
					self::$gets[] = self::$home . '/feed/';
					self::$gets[] = self::$home . '/feed/rss2';
					self::$gets[] = self::$home . '/feed/rss2/';
					self::$gets[] = self::$home . '/feed/rss';
					self::$gets[] = self::$home . '/feed/rss/';
					self::$gets[] = self::$home . '/feed/rdf';
					self::$gets[] = self::$home . '/feed/rdf/';
					self::$gets[] = self::$home . '/feed/atom';
					self::$gets[] = self::$home . '/feed/atom/';
					self::$gets[] = self::$home . '/robots.txt';
					self::$gets[] = self::$home . '/wp-json/wp/v2/posts';
					self::$gets[] = self::$home . '/wp-json/wp/v2/posts/';
					self::$gets[] = self::$home . '/sitemap.xml';
					self::$gets[] = self::$home . '/sitemap_index.xml';
				}

				// If its a variation, then we need to make sure its parent AND its own URL is added to the purge list
				if(class_exists('WooCommerce') && $post->post_type == 'product_variation'){
					$shop_page_url = wc_get_page_permalink('shop');
					self::$gets[] = $shop_page_url;

					$parent = wc_get_product($post->post_parent);
					$parent_url = $parent->get_permalink();
					self::$gets[] = $parent_url;

					$post_id = $post->post_parent;
					$post = get_post($post->post_parent);
				}

				$attribute_list = array();
				// If post is a product, add the main shop URL to the purge list
				if(class_exists('WooCommerce') && $post->post_type == 'product'){
					$shop_page_url = wc_get_page_permalink('shop');
					self::$gets[] = $shop_page_url;

					$product = wc_get_product($post->ID);

					if($product && $product->get_type() === 'variable'){
						$variations = $product->get_children();

						// Base URL for the product
						$base_url = get_permalink($post->ID);

						foreach($variations as $variation_id){
							$variation_object = wc_get_product($variation_id);
							if(!empty($variation_object)){
								$attributes = $variation_object->get_variation_attributes();

								$query_params = [];
								foreach($attributes as $attribute_key => $attribute_value){
									$attribute_list[] = str_replace('attribute_', '', $attribute_key);
									if(!$attribute_value){continue;}
									$query_params[$attribute_key] = urlencode($attribute_value);
								}

								// Append query parameters to the base URL
								$variation_url = add_query_arg($query_params, $base_url);
								self::$gets[] = $variation_url;
							}
						}
					}
				}

				// Add the post's permalink to the purge array
				$url = get_permalink($post_id);
				if(!filter_var($url, FILTER_VALIDATE_URL) === false){
					self::$gets[] = $url;

					// Add the query string version of the post URL to the purge array if it's not already there
					if(strpos($url, 'p=') === false){
						self::$gets[] = self::$home . '/?p=' . $post->ID;
					}
				}

				// Add post specifics
				self::$gets[] = self::$home . '/wp-json/wp/v2/posts/' . $post->ID;
				self::$gets[] = self::$home . '/wp-json/wp/v2/posts/' . $post->ID . '/';

				// Fetch a list of taxonomy related URLs tied to the post
				$taxonomies = get_object_taxonomies($post);
				$taxonomies_to_skip = array('product_type', 'product_visibility');
				$categories = array();
				foreach($taxonomies as $taxonomy){
					if(in_array($taxonomy, $taxonomies_to_skip)){ continue; }
					$terms = get_the_terms($post_id, $taxonomy);

					if(!is_wp_error($terms)){
						if(is_array($terms)){
							foreach($terms as $term){
								if($post->post_type == 'product' && in_array($term->taxonomy, $attribute_list)){ continue; }

								$url = get_term_link($term, $taxonomy);

								if(!is_wp_error($url)){
									// If it is a regular category, support the route without the category in the URL as well
									if($taxonomy == 'category'){
										$categories[] = str_replace('/category/', '/', $url);
									}

									// Add the term URL to the purge list
									$categories[] = $url;
								}
							}
						}
					}
				}

				// Add the taxonomy URLs to the page purger
				foreach($categories as $category){
					if(!filter_var($category, FILTER_VALIDATE_URL) === false){
						self::$gets[] = $category;
						self::$gets[] = rtrim($category, '/') . '/feed';
						self::$gets[] = rtrim($category, '/') . '/feed/';
						self::$gets[] = rtrim($category, '/') . '/page/2';
						self::$gets[] = rtrim($category, '/') . '/page/2/';
					}
				}

				// Add the relevant post archive links
				$archive = rtrim(get_post_type_archive_link($post->post_type), '/');
				if(!empty($archive)){
					self::$gets[] = $archive;
					self::$gets[] = $archive . '/';
					self::$gets[] = $archive . '/feed';
					self::$gets[] = $archive . '/feed/';
					self::$gets[] = $archive . '/page/2';
					self::$gets[] = $archive . '/page/2/';
				}
			}

			// Clean the associated WP post cache
			clean_post_cache($post_id);

			// Add post object to posts array for filtration later
			if(!empty($post)){
				self::$objects[] = $post;
			}

			// Update the uniques array so that we don't re-purge the same post
			$uniques[] = $post_id;
		}
	}


	/**
	 * Purge post associated with the comment tied to the post
	 *
	 * @access public
	 * @param mixed $comment_id
	 * @return void
	 */
	public static function purge_comment($comment_id, $approved = null){
		// Exit early if we're already purging all caches
		if(self::$type == 'all'){
			return;
		}

		// Only process if the ID is a valid ID
		if(is_numeric($comment_id)){
			// If the comment is approved and the user that approved the comment is allowed to edit posts, purge the post
			if($approved == 1 || ((is_admin() && is_user_logged_in() && current_user_can('publish_posts') && current_user_can('edit_posts')) || wp_doing_cron())){
				$comment = get_comment($comment_id);
				$post_id = $comment->comment_post_ID;
				self::purge_post($post_id, true);
			}
		}
	}


	/**
	 * Purge post associated with the comment tied to the post on comment status transition
	 *
	 * @access public
	 * @param string $new_status
	 * @param string $old_status
	 * @param object $comment
	 * @return void
	 */
	public static function purge_comment_transition($new_status, $old_status, $comment){
		// Exit early if we're already purging all caches
		if(self::$type == 'all'){
			return;
		}

		if((is_admin() && is_user_logged_in() && current_user_can('publish_posts') && current_user_can('edit_posts')) || wp_doing_cron()){
			$post_id = $comment->comment_post_ID;

			// Only further process if we have a valid ID
			if(is_numeric($post_id)){
				self::purge_post($post_id, true);
			}
		}
	}


	/**
	 * Purge post and associated terms
	 *
	 * @access public
	 * @param mixed $term_id
	 * @return void
	 */
	public static function purge_term($term_id, $taxonomy){
		// Exit early if we're already purging all caches
		if(self::$type == 'all'){
			return;
		}

		// Verify that the term ID is in a valid format
		if(is_numeric($term_id)){
			$args = array(
				'post_type' => 'post',
				'tax_query' => array(
					array(
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $term_id,
					),
				),
			);

			$query = new \WP_Query($args);

			foreach($query->posts as $post){
				self::purge_post($post->ID);
			}
		}
	}


	/**
	 * Purge a specific URL
	 *
	 * @param string $url
	 * @return void
	 */
	public static function purge_single($url){
		// Exit early if we're already purging all caches
		if(self::$type == 'all'){
			return;
		}

		// Set the purge type to URL specific
		self::$type = 'url';

		// If the URL validates as a URL, add it to the pages array
		if(filter_var($url, FILTER_VALIDATE_URL)){
			self::$gets[] = $url;

			// If the current URL is a post, get the post ID and run it through the normal purge post process
			if(is_singular()){
				$id = url_to_postid($url);
				if(is_numeric($id) && $id != 0){
					self::purge_post($id);
				}
			}
		}
	}


	/**
	 * Queue post IDs for late stage purge to ensure that the post has fully updated with all changes before purging
	 *
	 * @param int $post_id
	 * @return void
	 */
	public static function queue_purge_post($post_id){
		// Exit early if we're already purging all caches
		if(self::$type == 'all'){
			return;
		}

		if(!in_array($post_id, self::$queued)){
			// Look for global element post types that should trigger a full cache purge instead
			$post = get_post($post_id);
			switch($post->post_type){
				case 'elementor_library':
				case 'elementor_snippet':
				case 'et_footer_layout':
				case 'et_header_layout':
				case 'et_pb_layout':
				case 'et_template':
				case 'et_theme_builder':
				case 'fl-theme-layout':
				case 'acf-field-group':
					if($post->post_status === 'publish'){
						self::$type = 'all';
						return;
					}
					break;
				case 'shop_order':
				case 'revision':
				case 'auto-draft':
					return;
					break;
				default:
					break;
			}
			self::$type = 'url';
			self::$queued[] = $post_id;
		}
	}


	/**
	 * Purge operations for bulk actions like batch deletions, etc
	 *
	 * @access public
	 * @return void
	 */
	public static function handle_bulk_operations(){
		// Exit early if we shouldn't even attempt to purge
		if(!isset($_REQUEST['action']) && !isset($_REQUEST['trashed'])){
			return;
		}
		elseif(isset($_REQUEST['action']) && !in_array($_REQUEST['action'], array('approve', 'unapprove', 'trash', 'untrash', 'delete', 'edit'))){
			return;
		}

		// Exit early if we're already purging all caches
		if(self::$type == 'all'){
			return;
		}

		global $pagenow;

		$uniques = array();

		// Need to process both bulk comments and page updates separately
		if($pagenow == 'edit-comments.php' && isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('approve', 'unapprove', 'trash', 'untrash', 'delete'))){
			$comments = $_REQUEST['delete_comments'];
			if($comments && is_array($comments)){
				foreach($comments as $index => $commentID){
					$comment = get_comment($commentID);
					$post_id = $comment->comment_post_ID;

					if(!in_array($post_id, $uniques)){
						array_push($uniques, $post_id);
					}
				}
			}
		}

		// Add support for misc bulk actions
		elseif($pagenow == 'edit.php'){
			if(isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('edit', 'trash', 'untrash', 'delete'))){
				if(isset($_REQUEST['post']) && is_array($_REQUEST['post']) && count($_REQUEST['post']) > 0){
					foreach($_REQUEST['post'] as $post_id){
						if(!in_array($post_id, $uniques)){
							array_push($uniques, $post_id);
						}
					}
				}
				elseif(isset($_REQUEST['post']) && is_numeric($_REQUEST['post'])){
					if(!in_array($_REQUEST['post'], $uniques)){
						array_push($uniques, $_REQUEST['post']);
					}
				}
			}
		}

		// Add support for trash action in post page
		elseif($pagenow == 'post.php' && isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('trash'))){
			if(isset($_REQUEST['post']) && is_array($_REQUEST['post']) && count($_REQUEST['post']) > 0){
				foreach($_REQUEST['post'] as $post_id){
					if(!in_array($post_id, $uniques)){
						array_push($uniques, $post_id);
					}
				}
			}
			elseif(isset($_REQUEST['post']) && is_numeric($_REQUEST['post'])){
				if(!in_array($_REQUEST['post'], $uniques)){
					array_push($uniques, $_REQUEST['post']);
				}
			}
		}

		// If there are any posts to purge, pass them to the purger
		if(!empty($uniques)){
			foreach($uniques as $post_id){
				self::purge_post($post_id);
			}
		}
	}


	/**
	 * Route term events to term purger
	 *
	 * @access public
	 * @return void
	 */
	public static function handle_term_change(){
		// Exit early if we're already purging all caches
		if(self::$type == 'all'){
			return;
		}

		if((is_admin() && is_user_logged_in() && current_user_can('publish_posts') && current_user_can('edit_posts')) || wp_doing_cron()){
			$args_count = func_num_args();
			$args = func_get_args();

			// Verify that the term ID is in a valid format
			if(is_numeric($args[0])){
				if($args_count == 2){
					// Triggered by edit_terms
					self::purge_term($args[0], $args[1]);
				}
				elseif($args_count == 5){
					// Triggered by delete_term
					self::purge_term($args[0], $args[2]);
				}
			}
		}
	}


	/**
	 * Purge on publish
	 *
	 * @param  mixed $new_status
	 * @param  mixed $old_status
	 * @param  mixed $post
	 * @return void
	 */
	public static function handle_status_transition($new_status, $old_status, $post){
		// Exit early if we're already purging all caches
		if(self::$type == 'all'){
			return;
		}

		// If the status changes to either publish or unpublish the post, purge
		if(
			($old_status != 'publish' && $new_status == 'publish') ||
			($old_status == 'publish' && $new_status != 'publish')
		){
			self::purge_post($post->ID, true);
		}
	}


	/**
	 * Purge caches on product stock changes
	 *
	 * Purge all caches whenever the stock levels change after an order is placed.
	 *
	 * @access public
	 * @param mixed $order
	 * @return void
	 */
	public static function handle_stock_update($post_id){
		// Exit early if we're already purging all caches
		if(self::$type == 'all'){
			return;
		}

		self::purge_post($post_id, true);
	}

	/**
	* Wakeup magic method.
	*/
	public function __wakeup(){
		throw new \LogicException(__CLASS__ . ' should not be unserialized');
	}
}
