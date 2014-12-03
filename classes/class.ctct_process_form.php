<?php

/**
 * Handle all plugin form submissions.
 *
 * @todo  Support `cc_redirect_url` setting for widgets
 */
class CTCT_Process_Form {

	private $errors = array();

	private $data = array();

	private $results = false;

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

	public function id() {
		return $this->id;
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

		$this->id = esc_attr( $_POST['uniqueformid'] );

		// Validate the POST data
		$this->data = $this->sanitizePost();

		// Create the contact
		$KWSContact = new KWSContact( $this->data );

		$this->checkRequired();

		$this->validatePhone( $KWSContact );

		// Check If Email Is Real
		$this->validateEmail($KWSContact);

		$this->is_processed = true;

		// If validation failed, stop processing
		if( !empty( $this->errors ) ) {
			return;
		}

		// Otherwise, let's Add/Update
		$result = KWSConstantContact::getInstance()->addUpdateContact( $KWSContact );

		if( is_wp_error( $result ) ) {

			$this->errors[] = $result;

			$this->results = false;

		} else {

			$this->results = $result;

		}
	}

	/**
	 * Check to make sure required fields are not empty
	 * @return void
	 */
	function checkRequired() {

		foreach ( $_POST['fields'] as $key => $field ) {

			if( !empty( $field['req'] ) && (!isset( $field['value'] ) || $field['value'] === '') ) {
				$this->errors[] = new WP_Error('empty_field', sprintf( _x('The %s field is required.', 'Failed user form submission error', 'ctct'), esc_html( $field['label'] ) ), $key );
			}
		}

	}

	function validatePhone( KWSContact &$Contact ) {

		/**
		 * Whether to validate phone numbers at all.
		 * @var boolean
		 */
		$validate = apply_filters( 'constant_contact_validate_phone_number', true );

		if( !$validate ) {
			return;
		}

		if(!class_exists('libphonenumber\PhoneNumberUtil')) {
			include_once(CTCT_DIR_PATH.'vendor/giggsey/libphonenumber-for-php/src/libphonenumber/PhoneNumberUtil.php');
		}


		// en_US becomes "en", "US"
		$locale_pieces = explode( '_', get_locale() );

		// If only one piece, use that. If two, use the second piece.
		$locale = isset( $locale_pieces[ 1 ] ) ? $locale_pieces[ 1 ] : $locale_pieces[ 0 ];

		$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

		// Check the different number types
		$phone_numbers = array(
			'home_phone',
			'work_phone',
			'cell_phone',
			'fax',
		);

		/**
		 * Modify the field IDs to check when validating phone numbers
		 * @var array
		 */
		$phone_numbers = apply_filters( 'constant_contact_validate_phone_number_fields', $phone_numbers );

		foreach ( (array)$phone_numbers as $key ) {

			// Get the number
			$phone = $Contact->get( $key );

			if( empty( $phone ) ) { continue; }

			try {

				$number_proto = $phoneUtil->parse( $phone, $locale );

				/**
				 * Do you want to check for a number being valid (more strict), or just possible (less strict)
				 * @var string 'possible' or 'valid'
				 */
				$valid_or_possible = apply_filters('constant_contact_phone_number_validation', 'possible' );

				switch ( $valid_or_possible ) {

					case 'valid':
						$phoneUtil->isValidNumber( $number_proto );
						break;

					case 'possible':
					default:
						$phoneUtil->isPossibleNumber( $number_proto );
						break;
				}

			} catch (\libphonenumber\NumberParseException $e) {

				do_action('ctct_activity', 'Invalid phone number', $e->getMessage() );

			    $this->errors[] = new WP_Error('invalid_phone_number', __('Please enter a valid phone number.', 'ctct'), $key );

			}

		}

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

		$post = isset( $_POST ) ? $_POST : array();

		unset($post['fields']['lists']);

		foreach($post['fields'] as $key => $value) {
			if(isset($value['value'])) {
				$output[$key] = esc_attr($value['value']);
			} else {
				$output[$key] = esc_attr($value);
			}
		}

		// Make sure lists are IDs
		if(!empty($post['lists'])) {
			foreach($post['lists'] as $list) {
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
	function validateEmail(KWSContact &$Contact) {

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
			do_action('ctct_activity', 'Empty email address', $email );
			$this->errors[] = new WP_Error('empty_email', __('Please enter your email address.', 'ctct'), 'email_address');
			return;
		} else if(!is_email($email)) {
			do_action('ctct_activity', 'Invalid email address', $email);
			$this->errors[] = new WP_Error('not_email', __('Invalid email address.', 'ctct'), 'email_address');
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
				do_action('ctct_activity', 'WangGuard validation passed.', $email, $return);
			} else {
				$this->errors[] = new WP_Error('wangguard', __('Email validation failed.', 'ctct'), $email, $return);
				return;
			}
		}

		// 4: DataValidation.com validation
		if(in_array('datavalidation', $methods) && class_exists('DataValidation')) {

			$Validate = new DataValidation(CTCT_Settings::get('datavalidation_api_key'));

			$validation = $Validate->validate($email);

			$process_inconclusive = apply_filters( 'ctct_process_inconclusive_emails', true );

			if( is_wp_error( $validation ) ) {

				do_action('ctct_activity', 'DataValidation.com error', 'The email was not processed because of the error: '.$validation->get_error_message() );

				return;

			} elseif( $validation === false || ($validation === null && !$process_inconclusive) ) {

				do_action('ctct_activity', 'DataValidation validation failed.', $email, $Validate);

				$message = isset($Validate->message) ? $Validate->message : __('Not a valid email.', 'ctct');
				$this->errors[] = new WP_Error('datavalidation', $message, $email, $Validate);
				return;

			} if($validation === null) {
				do_action('ctct_activity', 'DataValidation validation inconclusive.', $email, $Validate);
			} elseif($validation === true) {
				do_action('ctct_activity', 'DataValidation validation passed.', $email, $Validate);
			}
		}

		// 5: SMTP validation
		if(in_array('smtp', $methods) && class_exists('SMTP_validateEmail')) {

			try {

				$SMTP_Validator = new SMTP_validateEmail();

				// Timeout after 1 second
				$SMTP_Validator->max_conn_time = 1;
				$SMTP_Validator->max_read_time = 1;
				$SMTP_Validator->debug = 0;

				// Prevent PHP notices about timeouts
				ob_start();
				$results = $SMTP_Validator->validate( array($email), get_option( 'admin_email' ));
				ob_clean();

				if( isset( $results[ $email ] ) ) {

					// True = passed
					if( $results[ $email ] ) {

						do_action('ctct_activity', 'SMTP validation passed.', $email, $results);

						return true;

					} else {

						do_action('ctct_activity', 'SMTP validation failed.', $email, $results);

						$this->errors[] = new WP_Error('smtp', __('Email validation failed.', 'ctct'), $email, $results);

						return false;
					}


				} else {

					do_action('ctct_activity', 'SMTP validation did not work', 'Returned empty results. Maybe it timed out?' );

					return true;
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
	function akismetCheck( $Contact ) {
		global $akismet_api_host, $akismet_api_port;


		// Disable using a filter
		if( apply_filters('disable_constant_contact_akismet', false) ) {
			return true;
		}

		// Akismet not activated
		if( !class_exists( 'Akismet' ) ) {
			do_action('ctct_activity', 'The Akismet class does not exist. Please upgrade to Version 3+ of Akismet.' );
			return true;
		}

		$key = Akismet::get_api_key();

		if( empty( $key ) ) {

			do_action('ctct_activity', 'Empty Akismet API key. Not processing.' );

			return true;
		}

		/** Disable nonce verification */
		add_filter('akismet_comment_nonce', function() { return 'inactive'; });

		ob_start();

		$comment_data = array(
	    	'user_ID' => get_current_user_id(),
	    	'comment_post_ID' => isset( $_POST['cc_referral_post_id'] ) ? intval( $_POST['cc_referral_post_id'] ) : NULL,
	    	'comment_author' => $Contact->get('name'),
	    	'comment_author_email' => $Contact->get('email'),
	    	'is_test' => apply_filters( 'constant_contact_akismet_is_test', false )
	    );

	    $check = Akismet::auto_check_comment( $comment_data );

	    ob_clean();

	    if( empty( $check ) || empty( $check['akismet_result'] ) ) {

	    	do_action('ctct_activity', 'There was an issue with Akismet: the response was invalid.', $check);

	    } else {
		    switch($check['akismet_result']) {
		    	case 'true':
		    		do_action('ctct_activity', __('Akismet caught this entry as spam'), $check);
		    		return new WP_Error('akismet', __('Your entry was flagged as spam.'), $check);
		    		break;
		    	case 'false':
		    		do_action('ctct_activity', __('Akismet cleared this comment'), $check);
		    		break;
		    	default:
		    		do_action('ctct_error', sprintf(__('Akismet was unable to check this entry (response: %s), will automatically retry again later.'), substr($check['akismet_result'], 0, 50)), $check);
		    }
		}

	    return true;
	}
}

new CTCT_Process_Form;