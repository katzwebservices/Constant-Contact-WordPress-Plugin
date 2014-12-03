<?php

/**
* @todo Add an is_email filter for global activation
 */
class DataValidation {

	var $host = 'https://api.datavalidation.com';
	var $post_endpoint = 'http://dvapi.com/email/validate';
	var $get_endpoint = 'https://api.datavalidation.com/1.0/rt/%s/';
	var $apikey;
	var $last_response;

	private $message = '';

	function __construct($apikey, $email = false) {
		$this->apikey = $apikey;

		if($email) {
			return $this->validate($email);
		}
	}

	function validate($email) {

		$response = $this->get( $email );

		if( empty( $response ) || ( !is_array( $response) && !is_string( $response ) ) ) {
			return new WP_Error('datavalidation', 'Empty response from DataValidation' );
		}

		$response = json_decode($response);

		// Invalid JSON response
		if( empty( $response ) ) {
			return new WP_Error('datavalidation', 'Invalid response from DataValidation', $response );
		}

		$this->last_response = $response;

		$this->message = $this->getStatusMessageFromCode($response->code);

		return $this->getStatusIntent( $response );
	}

	public function getStatusMessage() {
		return $this->message;
	}

	function getRequestArgs( $body ) {
		$args = array(
		    'headers' => array(
		        'Host' => $this->host,
				'Authorization' => 'bearer ' . $this->apikey,
				'Accept' => '*/*',
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

	function get( $email ) {

		$url = sprintf( $this->get_endpoint, $email );

		$request = wp_remote_get($url, $this->getRequestArgs(''));

		if( empty( $request ) ) {
			return false;
		}

		if( is_wp_error( $request ) ) {
			return $request;
		}

		return wp_remote_retrieve_body($request);
	}

	function getStatusIntent( $response ) {

		switch( $response->grade ) {
			case 'F':
				$return = false;
				break;

			case 'D':
				$return = null;
				break;

			default:
				$return = true;
				break;
		}

		return apply_filters( 'datavalidation_status_intent', $return, $response );
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
	function getStatusMessageFromCode($status_code) {

		$message = '';

		switch($status_code) {
			case 1: $message = __('Invalid email address', 'ctct'); break;
			case 2:	$message = __('Invalid email address host name', 'ctct'); break;
			case 3:	$message = __('Invalid email server for this domain', 'ctct'); break;
			case 4:	$message = __('Invalid email address local part', 'ctct'); break;
			case 5:	$message = __('Email address length exceeded', 'ctct'); break;
			case 6:	$message = __('Email address not found', 'ctct'); break;
			case 7:	$message = __('Email address could not be verified', 'ctct'); break;
			case 8:	$message = __('Email address found', 'ctct'); break;
			case 9:	$message = __('Domain accepts all recipients &ndash; Accepts All', 'ctct'); break;
			case 10: $message = __('Domain has an email server but there is no connection to it &ndash; Ambiguous', 'ctct'); break;
		}

		return apply_filters('datavalidation_status_message', $message, $status_code);
	}

}