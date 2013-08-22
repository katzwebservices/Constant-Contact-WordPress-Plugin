<?php
/*
Plugin Name: Cache Requests
Plugin URI: http://wordpress.org/extend/plugins/cache-http/
Description: Easily cache requests made using the WordPress HTTP API
Version: 1.0.1
Author: Katz Web Services, Inc.
Author URI: http://www.katzwebservices.com
*/

class Cache_WP_HTTP {

	private $debug = false;

	function __construct() {

		$this->debug = current_user_can('manage_options') && isset($_REQUEST['debug']) && $_REQUEST['debug'] === 'requests';

		add_filter('pre_http_request', array(&$this, 'pre_http_request'), 10, 3);

		add_filter('http_response', array(&$this, 'http_response'), 10, 3);
	}


	function pre_http_request($content, $r, $url) {

		$key = $this->getKey($url, $r);

		$this->r(sprintf('request transient key: %s<br />request url: %s,<br />request args: %s', $key, $url, print_r($r, true)), false, 'Request Details');

		// If caching isn't set, return.
		if(!$this->getCacheTime($r)) {
			$this->r('Not cached because the `cache` parameter is not set or cacheTime() method says no cache');
			return false;
		}

		$response = maybe_unserialize( get_transient( $key ));

		$this->flushCache($url, $r);

		if(
			strtoupper($r['method'] !== 'GET') || // Only cache `$_GET`s
		   	(current_user_can('manage_options') && isset($_REQUEST['cache'])) || // Define `cache` for fresh data
			!$response || is_wp_error($response) || // If error, don't cache
			($response && $response['response']['code'] !== 200) // If error, don't cache
		) {
			if(strtoupper($r['method'] !== 'GET')) {
				// If something's been PUT or POSTed to the same endpoint, let's reset the cache for that.
				$this->r('not cached due to method not GET');
			} elseif((current_user_can('manage_options') && isset($_REQUEST['cache']))) {
				$this->r('not cached due to overrides');
			} elseif(!$response || is_wp_error($response)) {
				$this->r('not cached due to no response (or error response)');
				$this->r($response, false, '$response:');
			} else {
				$this->r(sprintf('not cached due to response code being %s', $response['response']['code']));
			}

			return false;
		}

		if($this->debug) {
			$this->r($response, false, 'Response (Cached)');
		}

		return $response;
	}

	/**
	 * Filter the response from WP_HTTP class
	 * @param  array $response Response array
	 * @param  array $args     Response args
	 * @param  string $url      URL of the request
	 * @return array           Modified response
	 */
	function http_response($response, $args, $url ) {

		$time = $this->getCacheTime($args);
		if(!is_null($time)) {
			$key = $this->getKey($url, $args);
			$success = set_transient( $key, $response, $time );
			$this->r(sprintf('cache time: %s<br/>cache key: %s<br />cache success: %s', $time, $key, (bool)$success));
		}

		return $response;
	}

	/**
	 * Check the parameters to find if the cache duration has been set.
	 *
	 * Use `cache_wp_http_default_time` to set the default cache time. Default: 24 hours.
	 *
	 * @filter cache_wp_http_default_time
	 * @param  array $args Request settings
	 * @return boolean|float       False: no cache; Boolean: # of seconds to be cached.
	 */
	function getCacheTime($args) {

		if(!isset($args['cache']) || is_null($args['cache'])) { return NULL; }

		$cacheTime = $args['cache'];

		// Default cachetime
		if($cacheTime === true || $cacheTime * 1 === 1) {
			return apply_filters('cache_wp_http_default_time', 60 * 60 * 24);
		} else {
			return is_numeric($cacheTime) ? $cacheTime : false;
		}
	}

	/**
	 * Generate a unique key for the cache
	 *
	 * Uses the OAuth token; that way if another account is authorized, the data's no longer saved.
	 *
	 */
	function getKey($url, $args) {

		$cacheargs = array();
		$cache_prefix = (!empty($args['cache_prefix']) && is_string($args['cache_prefix']) && strlen($args['cache_prefix']) < 6) ? $args['cache_prefix'] : 'cache_';

		if(!empty($args['cache_key']) && is_string($args['cache_key']) && strlen($args['cache_key']) < 45) {
			return $args['cache_key'];
		}

		$cacheargs['body'] = $args['body'];
		$cacheargs['method'] = $args['method'];

		$key = $cache_prefix.sha1($url.maybe_serialize( $cacheargs ));

		return $key;
	}

	function flushCache($url, $r = array()) {

		if(isset($r['flush_key'])) {
			$this->r(sprintf('Flush URL: %s<br />Deleting transietnt with key %s', $url, $r['flush_key']), false, 'flushCache');
			delete_transient($r['flush_key']);
		}

#		$key = $this->getKey($url,  array('body' => NULL, 'method' => 'GET'));
#		$this->r(sprintf('Flush URL: %s<br />Deleting transietnt with key %s', $url, $key), false, 'flushCache');
#		delete_transient($key);

	}

	function r($data='', $die = false, $title = false) {
		if(!$this->debug) { return false; }
		if($title) {
			echo '<h3>'.$title.'</h3>';
		}
		echo '<pre>'.print_r($data, true).'</pre>';
		if($die) { die(); }
	}
}

$Cache_WP_HTTP = new Cache_WP_HTTP();