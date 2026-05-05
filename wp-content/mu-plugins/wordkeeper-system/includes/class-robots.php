<?php

namespace WordKeeper\System;

class Robots{

	private $settings;
	private $hash;


	/**
	 * Constructor
	 */
	public function __construct(){
		$settings = Settings::get_instance()->get_settings();

		// Filter the settings down to just bots settings
		$this->settings = array_filter($settings, function($key){
			return (strpos($key, 'bots') === 0);
		}, ARRAY_FILTER_USE_KEY);

		// Sort by key to maintain a consistent serialization check
		ksort($this->settings);

		// Hash the array
		$this->hash = md5(serialize($this->settings));
	}


	/**
	* Disallow my account, cart, checkout, and add to cart links
	*
	* @param    string    $output		The robots.txt content
	* @param    string    $public		Whether the site is considered "public" or not
	*/
	public function render($output, $public){
		// Block all bots for staging/dev sites
		if(strpos(ABSPATH, '/staging_') !== false || strpos(ABSPATH, '/dev_') !== false){
			$output = "User-agent: *\nDisallow: /";
			return $output;
		}

		$add = array();
		$list = array();
		$botmap = array(
			'bots/ahrefs' => array(
				'AhrefsBot',
				'AhrefsSiteAudit',
			),
			'bots/moz' => array(
				'rogerbot',
				'dotbot',
			),
			'bots/semrush' => array(
				'SemrushBot',
				'SemrushBot-SA',
				'SemrushBot-BA',
				'SemrushBot-SI',
				'SemrushBot-SWA',
				'SemrushBot-CT',
				'SemrushBot-BM',
				'SplitSignalBot',
			),
			'bots/screaming-frog' => 'Screaming Frog SEO Spider',
			'bots/gptbot' => 'GPTBot',
			'bots/majestic' => 'MJ12bot',
			'bots/dataforseo' => 'DataForSeoBot',
			'bots/raven' => 'RavenCrawler',
			'bots/yandex' => 'Yandex',
			'bots/baidu' => 'Baiduspider',
			'bots/huawei' => 'PetalBot',
			'bots/seznam' => 'SeznamBot',
			'bots/mailru' => 'Mail.Ru',
			'bots/qwant' => 'Qwantify',
			'bots/sogou' => array(
				'Sogou Spider',
				'Sogou blog',
				'Sogou inst spider',
				'Sogou News Spider',
				'Sogou Orion spider',
				'Sogou spider2',
				'Sogou web spider',
			),
			'bots/coccoc' => 'coccoc',
			'bots/gptbot' => 'GPTBot',
			'bots/google-extended' => 'Google-Extended',
			'bots/google-other' => 'GoogleOther',
			'bots/amazonbot' => 'Amazonbot',
			'bots/anthropic-ai' => 'anthropic-ai',
			'bots/applebot' => 'Applebot',
			'bots/ccbot' => 'CCBot',
			'bots/facebookbot' => 'FacebookBot',
		);

		// Block random URL junk
		if(!empty(get_option('permalink_structure'))){
			$list[] = '/*?p=*';
		}
		$list[] = '/*&p=*';

		// WP search
		$list[] = '/*?s=*';
		$list[] = '/*&s=*';

		// iCal feeds
		$list[] = '/*?ical=1';
		$list[] = '/*&ical=1';

		// The Events Calendar date filter
		if(defined('TRIBE_EVENTS_FILE')){
			$list[] = '/*?tribe-bar-date=*';
			$list[] = '/*&tribe-bar-date=*';
			$list[] = '/*?outlook-ical=1';
			$list[] = '/*&outlook-ical=1';
			$list[] = '/*?eme_ical=1';
			$list[] = '/*&eme_ical=1';
		}

		// My Calendar
		if(defined('MC_DIRECTORY')){
			$list[] = '/*?cid=mc-print-view';
			$list[] = '/*&cid=mc-print-view';
		}

		// Author ID pages
		$list[] = '/?author=*';

		// Misc WP junk feed/comment files/URLs
		$list[] = '/*wp-comments*';
		$list[] = '/*wp-trackback*';
		$list[] = '/*wp-feed*';
		$list[] = '/*replytocom=*';

		// Preview pages
		$list[] = '/*?preview=*';
		$list[] = '/*&preview=*';

		// E-commerce links
		$list[] = '/*add-to-cart=*';
		$list[] = '/*add_to_wishlist=*';
		$list[] = '/*cart/*';
		$list[] = '/*checkout/*';
		$list[] = '/*my-account/*';
		$list[] = '/*myaccount/*';

		// Events Manager AJAX links
		if(file_exists(WP_CONTENT_DIR) . '/plugins/events-manager'){
			$list[] = '/*?ajaxCalendar=1*';
			$list[] = '/*&ajaxCalendar=1*';
		}

		// Block WP Download Manager links from indexing
		if(file_exists(WP_CONTENT_DIR) . '/plugins/download-manager'){
			$list[] = '/*?wpdmdl=*';
			$list[] = '/*&wpdmdl=*';
		}

		// General store URLs
		if(function_exists('wc_get_page_permalink')){
			$checkout = wc_get_page_permalink('checkout');
			$checkout = parse_url($checkout);
			if(isset($checkout['path'])){
				$checkout = rtrim($checkout['path'], '/') . '/*';
				$list[] = $checkout;
			}

			$cart = wc_get_page_permalink('cart');
			$cart = parse_url($cart);
			if(isset($cart['path'])){
				$cart = rtrim($cart['path'], '/') . '/*';
				$list[] = $cart;
			}

			$account = wc_get_page_permalink('myaccount');
			$account = parse_url($account);
			if(isset($account['path'])){
				$account = rtrim($account['path'], '/') . '/*';
				$list[] = $account;
			}
		}

		// Woo filters
		if(class_exists('WooCommerce')){
			$list[] = '/*wc-ajax=add_to_cart';
			$list[] = '/*wc-ajax=remove_from_cart';
			$list[] = '/*orderby=price';
			$list[] = '/*orderby=rating';
			$list[] = '/*orderby=date';
			$list[] = '/*orderby=price-desc';
			$list[] = '/*orderby=popularity';
			$list[] = '/*orderby=title';
			$list[] = '/*orderby=desc';
			$list[] = '/*?filter=';
			$list[] = '/*&filter=';
			$list[] = '/*paged=&count=*';
			$list[] = '/*?count=*';
			$list[] = '/*&count=*';
		}

		// Speed URLs.  Google is creatively dumb when reading JS and needs to be told not to crawl
		// URLs that are never invoked in the first place.
		if(class_exists('\WordKeeper\Speed\Config')){
			$config = \WordKeeper\Speed\Config::get_instance()->get_config();

			// Disable widget cache crawls if widget caching is enabled
			if($config['config']['cache-widgets'] === true){
				$list[] = '/*wp-json/wordkeeper-speed/v1/widget/';
			}
		}

		foreach($list as $item){
			if(strpos($output, ltrim($item, '/')) === false || substr($item, 0, 2) == '//'){
				$add[$item] = true;
			}
		}

		if(count($add) > 0){
			$values = array_keys($add);
			$add = implode("\nDisallow: ", $values);
			$add = "# Stop bots from crawling junk URLs\nUser-agent: *\nCrawl-delay:1\nDisallow: ". $add;
			$output .= "\n" . $add;
		}

		// Allow plugins that have blocked words like "checkout" in the URL
		$output .= "\nAllow: /*/plugins/*";

		// Disallow blocked bots if necessary
		foreach($botmap as $bot => $useragent){
			// Block if bot is flagged as blocked
			if($this->settings[$bot] === true){
				if(is_string($useragent)){
					$output .= "\n\nUser-agent: " . $useragent . "\n";
					$output .= "Disallow: /";
				}
				elseif(is_array($useragent)){
					foreach($useragent as $name){
						$output .= "\n\nUser-agent: " . $name . "\n";
						$output .= "Disallow: /";
					}
				}
			}
		}

		return $output;
	}


	/**
	 * Save bot changes
	 *
	 * @param array $data
	 * @return void
	 */
	public function save($data){
		$changes = array_filter($data, function($key){
			return str_starts_with($key, 'bots');
		}, ARRAY_FILTER_USE_KEY);

		// Initialize saved data
		$save = $this->settings;

		// Process changes
		foreach($changes as $key => $value){
			$save[$key] = $value;
		}

		// Sort by key to maintain consistent serialization check
		ksort($save);

		// Hash the array
		$hash = md5(serialize($save));

		// If there have been changes, purge the robots.txt page cache and check for a text file to update
		if($hash != $this->hash){
			$this->purge();
			$this->save_file();
		}
	}


	/**
	 * Purge the robots.txt page cache
	 *
	 * @return void
	 */
	private function purge(){
		// Purge robots.txt cache
		$home = get_option('home');
		$response = Purge::purge_single(rtrim($home, '/') . '/robots.txt');
		$response = Purge::purge_single(rtrim($home, '/') . '/robots.txt?robots=1');
	}


	/**
	 * Save changes to robots.txt static file
	 *
	 * @return void
	 */
	private function save_file(){
		$path = ABSPATH . 'robots.txt';
		if(file_exists($path)){
			$front = new Front();

			$existing = file_get_contents($path);
			$output = $this->render('', null);
			$prefix = '# Start Robots Customizations';
			$suffix = '# End Robots Customizations';

			$begin = strpos($existing, $prefix);

			if($begin !== false){
				$existing_array = explode(PHP_EOL, $existing);
				$content_size = 0;

				$begin_index = -1;
				$end_index = -1;

				foreach($existing_array as $index => $line){
					if($begin_index == -1 && strpos($line, $prefix) !== false){
						$begin_index = $index;
					}

					if(strpos($line, $suffix) !== false){
						$end_index = $index;
					}

					$content_size++;
				}

				if($begin_index != -1 && $end_index != -1){
					$begin_slice = array_slice($existing_array, 0, $begin_index + 1);
					$begin_part = implode(PHP_EOL, $begin_slice);

					$end_slice = array_slice($existing_array, $end_index, $content_size);
					$end_part = implode(PHP_EOL, $end_slice);

					$full_output = $begin_part . $output . PHP_EOL . $end_part;
				}
			}
			else{
				$output = PHP_EOL . $prefix . PHP_EOL . $output . PHP_EOL . $suffix . PHP_EOL;
				$full_output = $existing . $output;
			}

			file_put_contents($path, $full_output);
		}
	}

	/**
	* Wakeup magic method.
	*/
	public function __wakeup(){
		throw new \LogicException(__CLASS__ . ' should not be unserialized');
	}
}