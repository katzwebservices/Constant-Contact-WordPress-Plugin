<?php

use Ctct\Exceptions\CTCTException;
use Ctct\Util\RestClientInterface;
use Ctct\Util\RestClient;
use Ctct\Util\Config;

/**
 * Wrapper for curl HTTP request
 *
 * @package     Util
 * @author         Constant Contact
 */
class KWSRestClient implements RestClientInterface
{
	static private $debug = false;
	static private $cachekey;
	static private $flushkey;

	/**
	 * Make an Http GET request
	 * @param $url - request url
	 * @param array $headers - array of all http headers to send
	 * @return array - array of the response body, http info, and error (if one exists)
	 */
	public function get($url, array $headers)
	{
		return self::httpRequest($url, "GET", $headers);
	}

	/**
	 * Make an Http POST request
	 * @param $url - request url
	 * @param array $headers - array of all http headers to send
	 * @param $data - data to send with request
	 * @return array - array of the response body, http info, and error (if one exists)
	 * @todo Figure out why it doesn't work for post but it does for everything else!
	 */
	public function post($url, array $headers = array(), $data = null)
	{
		#return RestClient::post($url, $headers, $data);
		return self::httpRequest($url, "POST", $headers, $data);
	}

	/**
	 * Make an Http PUT request
	 * @param $url - request url
	 * @param array $headers - array of all http headers to send
	 * @param $data - data to send with request
	 * @return array - array of the response body, http info, and error (if one exists)
	 */
	public function put($url, array $headers = array(), $data = null)
	{
		return self::httpRequest($url, "PUT", $headers, $data);
	}

	/**
	 * Make an Http DELETE request
	 * @param $url - request url
	 * @param array $headers - array of all http headers to send
	 * @param $data - data to send with request
	 * @return array - array of the response body, http info, and error (if one exists)
	 */
	public function delete($url, array $headers = array())
	{
		return self::httpRequest($url, "DELETE", $headers);
	}

	/**
	 * Make an Http request
	 * @param $url - request url
	 * @param array $headers - array of all http headers to send
	 * @param $data - data to send with the request
	 * @throws CTCTException - if any errors are contained in the returned payload
	 * @return CurlResponse
	 */
	private static function httpRequest($url, $method, array $headers = array(), $data = null)
	{

		self::$debug = current_user_can('manage_options') && (isset($_GET['debug']) && $_GET['debug'] === 'requests');

		// Make it WP format.
		$headers[] = "User-Agent: Constant Contact WordPress Plugin v".CTCT_VERSION;
		$headers = implode("\n", $headers);

		$args = array(
			'headers' => $headers,
			'method' => $method,
			'body' => $data,
			'timeout' => 50,
			'redirection' => (strtoupper($method) === 'POST' ? 0 : 10),
			'httpversion' => '1.1',
			'ssl_verify' => 0,
			'cache' => self::getCache($url, $data),
			'cache_key' => apply_filters('ctct_cachekey', self::$cachekey),
			'flush_key' => apply_filters('ctct_flushkey', self::$flushkey),
		);


		$response = wp_remote_request( $url, $args );

		// check if any errors were returned
		$body = json_decode($response['body'], true);
		if (isset($body[0]) && array_key_exists('error_key', $body[0])) {
			$ex = new CtctException($response['body']);
			$ex->setCurlInfo($response['response']);
			$ex->setErrors($body);

			do_action('ctct_log_message', 'httpRequest Error', $ex);
			do_action('ctct_debug', 'httpRequest Error', $ex);

			$errors = $ex->getErrors();

			preg_match('/^#\/(.*?):(.+)/ism', $errors[0]['error_message'], $matches);
			if(!empty($matches)) {
				$error_field = $matches[1];
				$error_message = $matches[2];
			} else {
				$error_field = NULL;
				$error_message = $errors[0]['error_message'];
			}
			$Error = new WP_Error($errors[0]['error_key'], $error_message, array('field' => $error_field, 'response' => $response, 'request' => $args, 'request_url' => $url));

			throw $ex;
		}

		$responseClass = new stdClass();
		$responseClass->body = $response['body'];

		return $responseClass;
	}

	static private function getCache($url, $data) {

		if(defined('DOING_AJAX')) { return 0; }

		return apply_filters('ctct_cache', apply_filters('constant_contact_cache_age', 60 * 60 * 6, $url, $data), $url, $data);
	}
}
