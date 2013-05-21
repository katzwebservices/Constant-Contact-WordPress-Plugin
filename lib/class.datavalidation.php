<?php

/**
* @todo Add an is_email filter for global activation
 */
class DataValidation {

	var $host = 'dvapi.com';
	var $post_endpoint = 'http://dvapi.com/email/validate';
	var $get_endpoint = 'http://dvapi.com';
	var $apikey;
	var $message;

	function __construct($apikey, $email = false) {
		$this->apikey = $apikey;

		if($email) {
			return $this->validate($email);
		}
	}

	function validate($email) {

		$location = $this->post($email);

		// The post failed.
		if(!$location) { return false; }

		$response = $this->get($location);
		$response = @json_decode($response);

		if(empty($response)) { return false; }

		$this->message = $this->getStatusMessage($response->code);

		return $this->getStatusIntent($response->code);
	}

	function getRequestArgs($body) {
		$args = array(
		    'headers' => array(
		        'Host' => $this->host,
				'apikey' => $this->apikey,
				'Content-Type' => 'application/json',
				'Content-Length' => strlen($body),
			),
			'timeout' => 5,
			'redirection' => 0,
			'httpversion' => '1.1',
		);
		if(!empty($body)) {
			$args['body'] = $body;
		}
		return $args;
	}

	function get($location) {
		$url = $this->get_endpoint.$location.'.json';
		$request = wp_remote_get($url, $this->getRequestArgs(''));
		return wp_remote_retrieve_body($request);
	}

	function post($email) {
		$body = json_encode(array(
				'settings' => array(),
				#'apikey' => $this->apikey,
				'emails' => array(
			    	array('email' => $email)
			    )
			));


		$request = wp_remote_post($this->post_endpoint, $this->getRequestArgs($body));

		$headers = wp_remote_retrieve_headers($request);

		return isset($headers['location']) ? $headers['location'] : false;
	}

	function getStatusIntent($status_code) {
		switch($status_code) {
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
			case 6:
				return false;
				break;
			case 7:
			case 9:
			case 10:
				return null;
				break;

			case 8:
				return true;
				break;
		}
	}
	/**
	 * Get the message associated with the status code
	 *
	 * Modify the output using `datavalidation_status_message` filter
	 *
	 * @filter datavalidation_status_message
	 * @param  integer $status_code Status code returned from DataValidation.com
	 * @return string              Status message
	 */
	function getStatusMessage($status_code) {

		switch($status_code) {
			case 1: $message = __('Invalid email address', 'datavalidation'); break;
			case 2:	$message = __('Invalid email address host name', 'datavalidation'); break;
			case 3:	$message = __('Invalid email server for this domain', 'datavalidation'); break;
			case 4:	$message = __('Invalid email address local part', 'datavalidation'); break;
			case 5:	$message = __('Email address length exceeded', 'datavalidation'); break;
			case 6:	$message = __('Email address not found', 'datavalidation'); break;
			case 7:	$message = __('Email address could not be verified', 'datavalidation'); break;
			case 8:	$message = __('Email address found', 'datavalidation'); break;
			case 9:	$message = __('Domain accepts all recipients &ndash; Accepts All', 'datavalidation'); break;
			case 10: $message = __('Domain has an email server but there is no connection to it &ndash; Ambiguous', 'datavalidation'); break;
		}

		return apply_filters('datavalidation_status_message', $message, $status_code);
	}

}