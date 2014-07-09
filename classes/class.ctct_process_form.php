<?php

/**
 * Handle all plugin form submissions.
 *
 * @todo  Support `cc_redirect_url` setting for widgets
 */
class CTCT_Process_Form {

	private $errors = array();

	var $results;

	private $id;

	static private $instance;

	private $is_processed = false;

	function __construct() {

		add_action('plugins_loaded', array(&$this, 'process'));

		self::$instance = &$this;
	}

	/**
	 * Get an instance of the object
	 * @return CTCT_Process_Form
	 */
	static function &getInstance() {

		if( empty( self::$instance ) ) {
			self::$instance = new CTCT_Process_Form;
		}

		return self::$instance;
	}

	public function is_processed() {
		return $this->is_processed;
	}

	/**
	 * Process the form if there is a $_POST['uniqueformid'] set
	 *
	 * 1. Validates the data
	 * 2. Creates a KWSContact contact object
	 * 3. Validate the email based on settings
	 * 4. If valid, add/update
	 *
	 * @uses KWSConstantContact::addUpdateContact() To add/update contact
	 * @todo Return contact on success.
	 * @return WP_Error|KWSContact Returns a WP error if error, otherwise a contact object.
	 */
	function process() {

		// Check that the form was submitted and we have an email value, otherwise return false
		if(!isset($_POST['uniqueformid'])) { return false; }

		$this->id = esc_attr($_POST['uniqueformid']);

		// Validate the POST data
		$data = $this->sanitizePost();

		// Create the contact
		$KWSContact = new KWSContact($data);

		// Check If Email Is Real
		$this->validateEmail($KWSContact);

		$this->is_processed = true;

		// If validation failed, stop processing
		if( !empty( $this->errors ) ) {
			return;
		}

		// Otherwise, let's Add/Update
		$this->results = KWSConstantContact::getInstance()->addUpdateContact($KWSContact);
	}

	/**
	 * Add key/value pair to results output
	 * @param string $key   The key of the pair
	 * @param mixed $value The value to set
	 */
	private function setResults($key = '', $value) {
		$this->results[$this->id][$key] = $value;
	}

	/**
	 * Get results for a form based on the form key
	 * @see  constant_contact_public_signup_form() for how the key is generated (sha1 of form settings array)
	 * @param  string $key The key of the form (its unique id)
	 * @return array|false      If a form with unique ID `$key` has been processed, return the form results array. Otherwise, return false.
	 */
	public function getResults($key = '') {

		if(empty($this->id)) { return NULL; }

		return $this->results;
	}

	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Sanitize the post data array before working with it
	 * @return array Sanitized $_POST array
	 */
	private function sanitizePost() {

		unset($_POST['fields']['lists']);

		foreach($_POST['fields'] as $key => $value) {
			if(isset($value['value'])) {
				$output[$key] = esc_attr($value['value']);
			} else {
				$output[$key] = esc_attr($value);
			}
		}

		// Make sure lists are IDs
		if(!empty($_POST['lists'])) {
			foreach($_POST['lists'] as $list) {
				if(!is_numeric($list)) { continue; }
				// Constant Contact requires list IDs to be strings of numbers...
				$output['lists'][] = (string)intval($list);
			}
		}

		unset($output['constant-contact-signup-submit']);

		return $output;
	}

	/**
	 * Validate email based on user settings.
	 *
	 * First, verify email using `is_email()` WordPress function (required)
	 *
	 * Then, process email validation based on settings.
	 *
	 * @param  KWSContact $Contact Contact object
	 * @return WP_Error|boolean 	If valid, return `true`, otherwise return a WP_Error object.
	 */
	function validateEmail(KWSContact $Contact) {

		if(!class_exists('DataValidation')) {
			include_once(CTCT_DIR_PATH.'lib/class.datavalidation.php');
		}

		if(!class_exists('SMTP_validateEmail')) {
			include_once(CTCT_DIR_PATH.'lib/mail/smtp_validateEmail.class.php');
		}

		$email = $Contact->get('email');

		$is_valid = array();

		// 1: Check if it's an email at all
		if(empty($email)) {
			do_action('ctct_debug', 'Empty email address', $email);
			$this->errors[] = new WP_Error('empty_email', __('Please enter your email address.', 'constant-contact-api'));
			return;
		} else if(!is_email($email)) {
			do_action('ctct_debug', 'Invalid email address', $email);
			$this->errors[] = new WP_Error('not_email', __('Invalid email address.', 'constant-contact-api'));
			return;
		}

		$methods = (array)CTCT_Settings::get('spam_methods');

		// 2: Akismet validation
		if(in_array('akismet', $methods)) {
			$akismetCheck = $this->akismetCheck($Contact);
			if(is_wp_error($akismetCheck)) {
				$this->errors[] = $akismetCheck;
				return;
			}
		}

		// 3: WangGuard validation
		if(in_array('wangguard', $methods) && function_exists('wangguard_verify_email') && wangguard_server_connectivity_ok()) {
			global $wangguard_api_host;

			// If WangGuard isn't set up yet, set'er up!
			if(empty($wangguard_api_host)) { wangguard_init(); }

			$return = wangguard_verify_email($email , wangguard_getRemoteIP() ,  wangguard_getRemoteProxyIP());

			if($return == 'checked' || $return == 'not-checked') {
				do_action('ctct_debug', 'DataValidation validation passed.', $email, $return);
			} else {
				$this->errors[] = new WP_Error('wangguard', 'Email validation failed.', $email, $return);
				return;
			}
		}

		// 4: DataValidation.com validation
		if(in_array('datavalidation', $methods) && class_exists('DataValidation')) {
			$Validate = new DataValidation(CTCT_Settings::get('datavalidation_api_key'));
			$validation = $Validate->validate($email);

			if($validation === false) {
				do_action('ctct_debug', 'DataValidation validation failed.', $email, $Validate);
				$message = isset($Validate->message) ? $Validate->message : __('Not a valid email.', 'constant-contact-api');
				$this->errors[] = new WP_Error('datavalidation', $message, $email, $Validate);
				return;
			} elseif($validation === null) {
				do_action('ctct_debug', 'DataValidation validation inconclusive.', $email, $Validate);
			} elseif($validation === true) {
				do_action('ctct_debug', 'DataValidation validation passed.', $email, $Validate);
			}
		}

		// 5: SMTP validation
		if(in_array('smtp', $methods) && class_exists('SMTP_validateEmail')) {

			try {

				$SMTP_Validator = new SMTP_validateEmail();

				// Timeout after 5 seconds
				$SMTP_Validator->max_conn_time = 1;
				$SMTP_Validator->max_read_time = 1;
				$SMTP_Validator->debug = 1;

				$results = $SMTP_Validator->validate( array($email), get_option( 'admin_email' ));

				if($results[$email]) {
					do_action('ctct_debug', 'SMTP validation passed.', $email, $results);
				} else {
					do_action('ctct_debug', 'SMTP validation failed.', $email, $results);
					$this->errors[] = new WP_Error('smtp', 'Email validation failed.', $email, $results);
					return;
				}

			} catch( Exception $e ) {

				do_action('ctct_error', 'SMTP validation broke.', $e );

				return;
			}

		}

		return true;
	}

	/**
	 * Verify the $_POST using Akismet
	 *
	 * @filter akismet_comment_nonce
	 * @link http://akismet.com/development/api/#comment-check
	 * @uses akismet_init()
	 * @uses akismet_auto_check_comment()
	 *
	 */
	function akismetCheck() {
		global $akismet_api_host, $akismet_api_port;

		if(!function_exists('akismet_http_post') || apply_filters('disable_constant_contact_akismet', false)) {
			return true;
		}

		/** If Akismet hasn't been initialized, initialize it. */
		if(empty($akismet_api_port)) { akismet_init(); }

		/** Disable nonce verification */
		add_filter('akismet_comment_nonce', function() { return 'inactive'; });

		ob_start();
	    $check = akismet_auto_check_comment(array());
	    ob_clean();

	    if(empty($check) || empty($check['akismet_result'])) {
	    	do_action('ctct_debug', 'There was an issue with Akismet: the response was invalid.', $check);
	    } else {
		    switch($check['akismet_result']) {
		    	case 'true':
		    		do_action('ctct_activity', __('Akismet caught this entry as spam'), $check);
		    		return new WP_Error('akismet', __('Your entry was flagged as spam.'), $check);
		    		break;
		    	case 'false':
		    		do_action('ctct_debug', __('Akismet cleared this comment'), $check);
		    		break;
		    	default:
		    		do_action('ctct_error', sprintf(__('Akismet was unable to check this entry (response: %s), will automatically retry again later.'), substr($check['akismet_result'], 0, 50)), $check);
		    }
		}

	    return true;
	}
}

new CTCT_Process_Form;