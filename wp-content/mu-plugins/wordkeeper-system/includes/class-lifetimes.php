<?php

namespace WordKeeper\System;

class Lifetimes{

	private $settings;


	/**
	 * Constructor
	 */
	public function __construct(){
		// Register a global PHP callback to remove caching headers if the final status code of the request isn't cacheable
		// Not ideal since you can only register one header callback, but it's not a common function and this is the perfect
		// and intended use case for that function.  So it will work for our purposes
		header_register_callback(function(){
			$cache = array(
				200,
				301,
				302,
				404
			);

			// If the response code signifies an uncacheable request, remove caching headers
			$code = (int) http_response_code();
			if(!in_array($code, $cache)){
				header_remove('X-Accel-Expires');
			}
		});

		$settings = Settings::get_instance()->get_settings();

		// Filter the settings down to just cache settings
		$this->settings = array_filter($settings, function($key){
			return (strpos($key, 'cache') === 0);
		}, ARRAY_FILTER_USE_KEY);

		// Sort by key to maintain a consistent serialization check
		ksort($this->settings);
	}


	/**
	 * Send relevant cache control headers
	 *
	 * @return void
	 */
	public function cache(){
		global $post;

		$headers = headers_list();
		$redirect = false;
		$feed = false;

		// Don't change cache times for requests that already have a cache time set
		foreach($headers as $header){
			// Already set an expires header, exit
			if(strpos($header, 'X-Accel-Expires:') === 0){
				return;
			}

			// Current URL is a redirect
			if(strpos($header, 'Location:') === 0){
				$redirect = true;
			}

			// Current URL is a feed page
			if(strpos($header, 'application/rss+xml') !== false && strpos($header, 'Content-Type') !== false){
				$feed = true;
			}
		}

		// Only cache redirects for 15 minutes
		if($redirect){
			header('X-Accel-Expires: 900');
			return;
		}

		// Cache post/comment/RSS feeds for 1 hour
		if($feed){
			header('X-Accel-Expires: 3600');
			return;
		}

		// Shorten cache lifetime for Event Manager AJAX links
		if(isset($_GET['ajaxCalendar']) && $_GET['ajaxCalendar'] == 1){
			header('X-Accel-Expires: 900');
			return;
		}

		// Only cache pagespeed=off links for 30 minutes
		if(isset($_GET['pagespeed']) && strtolower($_GET['pagespeed']) == 'off'){
			header('X-Accel-Expires: 1800');
			return;
		}

		// Don't cache WP Download Manager download links
		if(isset($_GET['wpdmdl'])){
			header('X-Accel-Expires: 0');
			return;
		}

		$parts = parse_url($_SERVER['REQUEST_URI']);
		$path = (isset($parts['path'])) ? rtrim($parts['path'], '/') : '/';
		$disable = array();
		$cachetime = 10800;
		$custom = false;

		// If we are dealing with a non-REST URL with a query string, cache for 3 hours
		if(strpos($path, '/wp-json') === false && !empty($parts['query'])){
			$cachetime = 10800;
		}
		// If we are dealing with an AMP page, only cache for 3 hours
		elseif(preg_match('#/(?:amp|web-stories)/?$#i', $path)){
			$cachetime = 10800;
		}
		// Cache single posts for varying lengths by age of post
		elseif(is_singular()){
			$allowed = array(
				10800,
				43200,
				86400,
				2592000
			);

			// Set a cache lifetime override if one is defined
			switch($post->post_type){
				case 'post':
				case 'page':
					if(in_array('cache/' . $post->post_type, array_keys($this->settings))){
						// Set cache time to allowed override value
						if($this->settings['cache/' . $post->post_type] !== 'default'){
							$override = (int) $this->settings['cache/' . $post->post_type];
							if(in_array($override, $allowed)){
								$cachetime = $override;
								$custom = true;
							}
						}
					}
					break;
				default:
					break;
			}

			// If no cache lifetime override is defined, use the default sliding scale
			if(!$custom){
				$modified = get_the_modified_date('U', $post);
				$delta = strtotime('now') - $modified;

				// Less than a month
				if($delta < 2592000){
					$cachetime = 86400;
				}
				// 1-6 months
				elseif($delta < 15768000 && $delta > 2592000){
					$cachetime = 2592000;
				}
				// 6-12 months
				elseif($delta < 31536000 && $delta > 15768000){
					$cachetime = 15768000;
				}
				else{
					$cachetime = 31536000;
				}
			}
		}
		// Cache search page for 3 hours
		elseif(is_search()){
			// 3 hours
			$cachetime = 10800;
		}
		// Cache archive pages for 1 day
		elseif(is_archive()){
			// 24 hours
			$cachetime = 86400;
		}

		// Prevent caching of customized wp-login.php pages
		switch(trim($_SERVER['SCRIPT_NAME'], '/')){
			case 'wp-login.php':
				$cachetime = 0;
				break;
			default:
				break;
		}

		// Add Woo URLs to cache disable array
		if(function_exists('wc_get_page_permalink')){
			$checkout = wc_get_page_permalink('checkout');
			$checkout = parse_url($checkout);
			if(isset($checkout['path'])){
				$checkout = rtrim($checkout['path'], '/');
				$disable[] = $checkout;
			}

			$cart = wc_get_page_permalink('cart');
			$cart = parse_url($cart);
			if(isset($cart['path'])){
				$cart = rtrim($cart['path'], '/');
				$disable[] = $cart;
			}

			$account = wc_get_page_permalink('myaccount');
			$account = parse_url($account);
			if(isset($account['path'])){
				$account = rtrim($account['path'], '/');
				$disable[] = $account;
			}
		}

		// For The Events Calendar, only cache the main events page for 6 hours
		if(strpos($_SERVER['REQUEST_URI'], '/events/') !== false && class_exists('Tribe__Events__Main')){
			$cachetime = 21600;
		}

		// Reduce cache time for URLs with query strings (sanitized) to 30 minutes
		if(!empty($_SERVER['QUERY_STRING'])){
			$args = get_sanitized_args();
			if(!empty($args)){
				// Cache swoof searches for longer
				if(!empty($args['swoof'])){
					$cachetime = 10800;
				}
				// Otherwise treat as a general query string URL
				else{
					$cachetime = 1800;
				}
			}
		}

		// Disable cache for any path in the disable array
		if(!empty($disable)){
			$check = str_replace($disable, '', $path);
			if($path != $check){
				$cachetime = 0;
			}
		}

		$cachetime = apply_filters('wordkeeper/system/cachetime', $cachetime);

		header('X-Accel-Expires: ' . $cachetime);
	}

	/**
	 * Send relevant cache control headers
	 *
	 * @return void
	 */
	public function rest($result, $server, $request){
		$headers = headers_list();

		// Don't change cache times for requests that already have a cache time set
		foreach($headers as $header){
			if(strpos($header, 'X-Accel-Expires:') === 0){
				return $result;
			}
		}

		$path = parse_url($_SERVER['REQUEST_URI']);
		$path = (isset($path['path'])) ? rtrim($path['path'], '/') : '/';
		$disable = array();
		$cachetime = 10800;

		$cachetime = apply_filters('wordkeeper/system/cachetime', $cachetime);

		header('X-Accel-Expires: ' . $cachetime);

		return $result;
	}

	/**
	 * Don't cache responses for failed REST API auth requests
	 *
	 * @return void
	 */
	public function rest_auth($auth){
		if(is_wp_error($auth)){
			header('X-Accel-Expires: 0');
		}

		return $auth;
	}

	/**
	 * Filter any WP issued redirects to limit cache lifetimes if the redirect is invalid or would create a redirect loop
	 *
	 * @param string $location
	 * @param int $status
	 * @return void
	 */
	public function redirects($location, $status){
		// If headers were already sent, exit early
		// That means something in the site tried to redirect after it's too late to do a redirect
		if(headers_sent()){
			return $location;
		}

		// Only manage cache control for GET/HEAD requests
		$method = (in_array($_SERVER['REQUEST_METHOD'], array('GET', 'HEAD'))) ? true : false;

		// Maintain the current scheme
		$base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';

		// Maintain the current host but filter out junk chars at the end of the domain that still route to the host
		$base .= preg_replace('#[^a-zA-Z]*$#' , '', $_SERVER['HTTP_HOST']);

		// If the redirect location starts with /, validate w/ scheme and host to prevent false negatives from validation
		$filter = (strpos($location, '/') === 0) ? filter_var($base . $location, FILTER_VALIDATE_URL) : filter_var($location, FILTER_VALIDATE_URL);

		// Set the active URL
		$current = $base . $_SERVER['REQUEST_URI'];

		// Must have a valid redirect location and valid method to manage redirection
		if($filter !== false && $method != false){
			// Remove any pre-existing cache header
			header_remove('X-Accel-Expires');

			// For exact redirects to self, don't cache and cancel the redirect
			if($location == $current){
				header('X-Accel-Expires: 0');
				return false;
			}
			// If the redirect location is path-only and redirects to the active URL, don't cache and cancel
			elseif($_SERVER['REQUEST_URI'] == $location){
				header('X-Accel-Expires: 0');
				return false;
			}
			// For redirects that only change case, don't cache, but allow
			elseif(strtolower($location) == strtolower($current)){
				header('X-Accel-Expires: 0');
				return $location;
			}
			// For redirects that only change between space chars, don't cache, but allow
			elseif(preg_replace('#(?:%20|\+)#', '', $location) == preg_replace('#(?:%20|\+)#', '', $current)){
				header('X-Accel-Expires: 0');
				return $location;
			}
			// For more complex redirects, parse the request
			else{
				$sourceparts = parse_url($current);
				$redirectparts = parse_url($location);

				// Don't cache redirects when the source URL is the home page
				if(!empty($sourceparts) && !empty($sourceparts['path']) && $sourceparts['path'] == '/'){
					header('X-Accel-Expires: 0');
				}

				// Parse redirect URL
				if(!empty($redirectparts) && !empty($sourceparts)){
					// If the redirect URL is invalid, don't cache the redirect
					if((empty($redirectparts['host']) || empty($redirectparts['scheme'])) && strpos($location, '/') !== 0){
						header('X-Accel-Expires: 0');
					}
					// If there isn't a path to the source or redirect, don't cache it
					elseif(empty($redirectparts['path']) || empty($sourceparts['path'])){
						header('X-Accel-Expires: 0');
					}
					// If the path is the same for source/dest and source query is just tracking vars, don't cache
					elseif(!empty($sourceparts['query']) && $sourceparts['path'] == $redirectparts['path']){
						// Get the list of query string args minus known tracking vars
						$args = get_sanitized_args();

						// If the only query string vars in the URL were tracking vars, don't cache the redirect
						if(count($args) == 0){
							header('X-Accel-Expires: 0');
						}
					}
					// If the path is the same for source/dest and dest query is just tracking vars, don't cache
					elseif(!empty($redirectparts['query']) && $sourceparts['path'] == $redirectparts['path']){
						// Get the list of query string args minus known tracking vars
						parse_str(html_entity_decode($redirectparts['query']), $args);
						$args = get_sanitized_args($args);

						// If the only query string vars in the URL were tracking vars, don't cache the redirect
						if(count($args) == 0){
							header('X-Accel-Expires: 0');
						}
					}
				}
				// If redirect URL can't be parsed, don't cache it
				else{
					header('X-Accel-Expires: 0');
				}
			}
		}
		else{
			header('X-Accel-Expires: 0');
		}

		return $location;
	}

	/**
	 * Set no cache headers for login page
	 *
	 * @return void
	 */
	public function login(){
		$headers = headers_list();

		// Remove any pre-existing cache headers
		foreach($headers as $header){
			// Already set an expires header, remove it
			if(strpos($header, 'X-Accel-Expires:') === 0){
				header_remove('X-Accel-Expires');
			}
		}

		header('X-Accel-Expires: 0');
	}

	/**
	* Wakeup magic method.
	*/
	public function __wakeup(){
		throw new \LogicException(__CLASS__ . ' should not be unserialized');
	}
}