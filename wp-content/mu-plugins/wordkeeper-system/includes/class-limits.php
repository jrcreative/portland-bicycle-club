<?php

namespace WordKeeper\System;

class Limits{
	protected static $instance;
	private array $plans = [
		'basic' => [
			'cron' => 60,
			'ecomm' => false,
			'images' => 'daily',
			'installs' => 1,
			'memory' => 256,
			'multisite' => false,
			'objectcache' => false,
			'opcache' => 'file',
			'pageviews' => 30000,
			'storage' => 1,
			'webp' => false,
			'workermode' => 'ondemand',
			'workers' => 1,
		],
		'plus' => [
			'cron' => 60,
			'ecomm' => true,
			'images' => 'daily',
			'installs' => 3,
			'memory' => 256,
			'multisite' => false,
			'objectcache' => false,
			'opcache' => 'file',
			'pageviews' => 50000,
			'storage' => 5,
			'webp' => false,
			'workermode' => 'ondemand',
			'workers' => 1,
		],
		'pro' => [
			'cron' => 60,
			'ecomm' => true,
			'images' => 'daily',
			'installs' => 1,
			'memory' => 256,
			'multisite' => false,
			'objectcache' => false,
			'opcache' => 'file',
			'pageviews' => 75000,
			'storage' => 8,
			'webp' => false,
			'workermode' => 'ondemand',
			'workers' => 2,
		],
		'premier' => [
			'cron' => 60,
			'ecomm' => false,
			'images' => 'daily',
			'installs' => 5,
			'memory' => 256,
			'multisite' => true,
			'objectcache' => false,
			'opcache' => 'file',
			'pageviews' => 100000,
			'storage' => 1,
			'webp' => false,
			'workermode' => 'ondemand',
			'workers' => 2,
		],
		'shared' => [
			'cron' => 60,
			'ecomm' => false,
			'images' => 'daily',
			'installs' => 5,
			'memory' => 256,
			'multisite' => true,
			'objectcache' => false,
			'opcache' => 'file',
			'pageviews' => 100000,
			'storage' => 1,
			'webp' => false,
			'workermode' => 'ondemand',
			'workers' => 2,
		],
		't1' => [
			'cron' => 60,
			'ecomm' => true,
			'images' => 'ondemand',
			'installs' => 25,
			'memory' => 256,
			'multisite' => true,
			'objectcache' => false,
			'opcache' => 'memory',
			'pageviews' => 1000000,
			'storage' => 40,
			'webp' => true,
			'workermode' => 'ondemand',
			'workers' => 2,
		],
		't2' => [
			'cron' => 15,
			'ecomm' => true,
			'images' => 'ondemand',
			'installs' => 50,
			'memory' => 512,
			'multisite' => true,
			'objectcache' => true,
			'opcache' => 'memory',
			'pageviews' => 2500000,
			'storage' => 70,
			'webp' => true,
			'workermode' => 'dynamic',
			'workers' => 4,
		],
		't3' => [
			'cron' => 15,
			'ecomm' => true,
			'images' => 'ondemand',
			'installs' => 100,
			'memory' => 512,
			'multisite' => true,
			'objectcache' => true,
			'opcache' => 'memory',
			'pageviews' => 5000000,
			'storage' => 150,
			'webp' => true,
			'workermode' => 'dynamic',
			'workers' => 6,
		],
		't4' => [
			'cron' => 15,
			'ecomm' => true,
			'images' => 'ondemand',
			'installs' => 200,
			'memory' => 512,
			'multisite' => true,
			'objectcache' => true,
			'opcache' => 'memory',
			'pageviews' => 10000000,
			'storage' => 300,
			'webp' => true,
			'workermode' => 'dynamic',
			'workers' => 8,
		],
		't5' => [
			'cron' => 15,
			'ecomm' => true,
			'images' => 'ondemand',
			'installs' => 200,
			'memory' => 512,
			'multisite' => true,
			'objectcache' => true,
			'opcache' => 'memory',
			'pageviews' => 10000000,
			'storage' => 300,
			'webp' => true,
			'workermode' => 'dynamic',
			'workers' => 8,
		],
	];

	private array $limits;

	public function __construct(){
		$limit = '';
		if(file_exists(dirname(ABSPATH) . '/tier')){
			$limit = trim(file_get_contents(dirname(ABSPATH) . '/tier'));
		}

		// If we know the limits for the active plan, load them
		if(!empty($limit)){
			$this->limits = $this->plans[$limit];
		}
		// Otherwise load the Basic plan limits
		else{
			$this->limits = [
				'cron' => 60,
				'ecomm' => false,
				'images' => 'daily',
				'installs' => 1,
				'memory' => 256,
				'multisite' => false,
				'objectcache' => false,
				'opcache' => 'file',
				'pageviews' => 30000,
				'storage' => 1,
				'webp' => false,
				'workermode' => 'ondemand',
				'workers' => 1,
			];
		}
	}

	/**
	 * Either returns an existing single class instance or creates a new and returns it.
	 *
	 * @access static
	 * @param void
	 * @return object
	 */
	public static function get_instance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}

	// Get limits
	public function get(): array {
		return $this->limits;
	}

	/**
	* Wakeup magic method.
	*/
	public function __wakeup(){
		throw new \LogicException(__CLASS__ . ' should not be unserialized');
	}
}
