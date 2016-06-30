<?php
/**
 * @package CTCT
 * @version 3.0
 */

/**
 * Handle all plugin form submissions.
 *
 */
class CTCT_Process_Form {

	private $errors = array();

	private $data = array();

	private $results = false;

	private $id;

	static private $instance;

	private $is_processed = false;

	function __construct() {
		$this->add_hooks();
	}

	private function add_hooks() {
		add_action( 'template_redirect', array( &$this, 'process' ) );
	}

	/**
	 * Get an instance of the object
	 *
	 * @return CTCT_Process_Form
	 */
	static function &getInstance() {

		if ( empty( self::$instance ) ) {
			self::$instance = new self;
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
	 * Return errors array
	 *
	 * @since 3.2.0
	 * @return array
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Process the form for backend and frontend
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

		$this->id = NULL;

		// Check that the form was submitted
		if ( is_admin() ) {

			// Validate nonce from the Profile page
			$valid_nonce = wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $_POST['user_id'] );

			$this->id = ( ! empty( $_POST['user_id'] ) && $valid_nonce ) ? $_POST['user_id'] : false;

		} else {
			$this->id = isset( $_POST['uniqueformid'] ) ? esc_attr( $_POST['uniqueformid'] ) : false;
		}

		if ( empty( $this->id ) ) {
			return false;
		}

		// Validate the POST data
		$this->data = $this->sanitizePost();

		// Create the contact
		$KWSContact = new KWSContact( $this->data );

		if ( is_wp_error( $KWSContact ) ) {
			$this->errors[] = $KWSContact;

			return;
		}

		$this->checkRequired();

		// Check If Email Is Real
		$this->validateEmail( $KWSContact );

		$this->is_processed = true;

		// If validation failed, stop processing
		if ( ! empty( $this->errors ) ) {
			return;
		}

		// Otherwise, let's Add/Update
		$result = KWSConstantContact::getInstance()->addUpdateContact( $KWSContact );

		if ( is_wp_error( $result ) ) {

			$this->errors[] = $result;

			$this->results = false;

		} else {

			$this->results = $result;

		}

		if ( empty( $this->results ) ) {
			return;
		}

		$this->maybe_redirect();


	}

	/**
	 * If the request included a Redirect URL, parse, sanitize, and process the redirection
	 *
	 * @since  3.1.6
	 * @return void
	 */
	function maybe_redirect() {

		if ( ! empty( $_POST['cc_redirect_url'] ) ) {

			$safe_redirect = false;

			$requested_url = urldecode( $_POST['cc_redirect_url'] );

			$parsed = parse_url( $requested_url );


			/**
			 * This is a local URL, has a path but not a domain or http://
			 *
			 * We use wp_safe_redirect() because it's definitely local.
			 */
			if ( ! empty( $parsed['path'] ) && empty( $parsed['host'] ) && empty( $parsed['scheme'] ) ) {

				// Generate the URL based on the path
				$redirect_url = site_url( $parsed['path'] );

				if ( ! empty( $parsed['query'] ) ) {
					$redirect_url .= '?' . $parsed['query'];
				}

				$safe_redirect = true;
			} /**
			 * If there's a query, it might include some url-encoded `'"`s that will get stripped if just sanitized.
			 * Instead, let's parse out the query, sanitize the URL, then add back in the query.
			 *
			 * Otherwise, this: http://example.com/?custom-redirect=123&example=\/&'"
			 * Would be stripped to this: http://example.com/?custom-redirect=123&example=\/&
			 *
			 * Note the missing ' and "
			 */
			elseif ( ! empty( $parsed['query'] ) && ! empty( $parsed['scheme'] ) && ! empty( $parsed['host'] ) ) {

				$path = isset( $parsed['path'] ) ? $parsed['path'] : '';

				$temp_url = $parsed['scheme'] . '://' . $parsed['host'] . $path;

				$temp_url = esc_url_raw( $temp_url );

				$redirect_url = $temp_url . '?' . $parsed['query'];

			} else {

				$redirect_url = wp_sanitize_redirect( $requested_url );

			}

			/**
			 * Set whether to use wp_safe_redirect() for a request. If local URL, defaults to yes. If not, defaults to no.
			 *
			 * @var boolean
			 */
			$safe_redirect = apply_filters( 'constant_contact_force_use_safe_redirect', $safe_redirect, $this );

			do_action( 'ctct_activity', 'Redirecting User after processing', $redirect_url );

			if ( $safe_redirect ) {

				wp_safe_redirect( $redirect_url );

			} else {

				wp_redirect( $redirect_url );

			}

			exit();
		}

	}

	/**
	 * Check to make sure required fields are not empty
	 *
	 * @return void
	 */
	function checkRequired() {

		if ( ! isset( $_POST['cc-fields'] ) || ! is_array( $_POST['cc-fields'] ) ) {
			return;
		}

		foreach ( $_POST['cc-fields'] as $key => $field ) {

			if ( ! empty( $field['req'] ) && ( ! isset( $field['value'] ) || $field['value'] === '' ) ) {
				$this->errors[] = new WP_Error( 'empty_field', sprintf( _x( 'The %s field is required.', 'Failed user form submission error', 'constant-contact-api' ), esc_html( $field['label'] ) ), $key );
			}
		}

	}

	/**
	 * Add key/value pair to results output
	 *
	 * @param string $key The key of the pair
	 * @param mixed $value The value to set
	 */
	private function setResults( $key = '', $value ) {
		$this->results[ $this->id ][ $key ] = $value;
	}

	/**
	 * Get results for a form based on the form key
	 *
	 * @see  constant_contact_public_signup_form() for how the key is generated (sha1 of form settings array)
	 *
	 * @param  string $key The key of the form (its unique id)
	 *
	 * @return array|false      If a form with unique ID `$key` has been processed, return the form results array. Otherwise, return false.
	 */
	public function getResults( $key = '' ) {

		if ( empty( $this->id ) ) {
			return NULL;
		}

		return $this->results;
	}

	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Sanitize the post data array before working with it
	 *
	 * @return array Sanitized $_POST array
	 */
	private function sanitizePost() {

		$post = isset( $_POST ) ? $_POST : array();

		if ( is_admin() ) {

			$post['cc-fields'] = array(
				'email_address' => isset( $post['email'] ) ? $post['email'] : NULL,
				'first_name'    => isset( $post['first_name'] ) ? $post['first_name'] : NULL,
				'last_name'     => isset( $post['last_name'] ) ? $post['last_name'] : NULL,
			);

			unset( $post['email'] );
		}

		unset( $post['cc-fields']['lists'] );

		$output = array();

		foreach ( $post['cc-fields'] as $key => $value ) {
			if ( isset( $value['value'] ) ) {
				$output[ $key ] = esc_attr( $value['value'] );
			} elseif ( is_string( $value ) ) {
				$output[ $key ] = esc_attr( $value );
			}
		}

		// Make sure lists are IDs
		if ( ! empty( $post['cc-lists'] ) ) {
			foreach ( $post['cc-lists'] as $list ) {
				if ( ! is_numeric( $list ) ) {
					continue;
				}
				// Constant Contact requires list IDs to be strings of numbers...
				$output['lists'][] = (string) intval( $list );
			}
		}

		unset( $output['constant-contact-signup-submit'] );

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
	 *
	 * @return boolean If valid, return `true`, otherwise return `false` and set $this->errors with WP_Error
	 */
	function validateEmail( KWSContact &$Contact ) {

		$email = $Contact->get( 'email' );

		$email = trim( $email );

		// 1: Check if it's an email at all
		if ( empty( $email ) ) {

			do_action( 'ctct_activity', 'Empty email address', $email );

			$this->errors[] = new WP_Error( 'empty_email', __( 'Please enter your email address.', 'constant-contact-api' ), 'email_address' );

			return false;
		}

		if ( ! is_email( $email ) ) {

			do_action( 'ctct_activity', 'Invalid email address', $email );

			$this->errors[] = new WP_Error( 'not_email', __( 'Invalid email address.', 'constant-contact-api' ), 'email_address' );

			return false;
		}

		$methods = (array) CTCT_Settings::get( 'spam_methods' );

		// 2: Akismet validation
		if ( in_array( 'akismet', $methods ) ) {
			$akismetCheck = $this->akismetCheck( $Contact );
			if ( is_wp_error( $akismetCheck ) ) {
				$this->errors[] = $akismetCheck;

				return false;
			}
		}

		// 3: WangGuard validation
		if ( in_array( 'wangguard', $methods ) && function_exists( 'wangguard_verify_email' ) && wangguard_server_connectivity_ok() ) {
			global $wangguard_api_host;

			if ( $api_key = get_site_option( 'wangguard_api_key' ) ) {

				ob_start();

				// If WangGuard isn't set up yet, set'er up!
				if ( empty( $wangguard_api_host ) ) {
					wangguard_init();
				}

				$return = wangguard_verify_email( $email, wangguard_getRemoteIP(), wangguard_getRemoteProxyIP() );

				// Errors
				ob_get_clean();

				if ( $return == 'checked' || $return == 'not-checked' ) {
					do_action( 'ctct_activity', 'WangGuard validation passed.', $email, $return );
				} elseif ( 'error:100' === $return ) {
					do_action( 'ctct_activity', 'WangGuard is not configured.', $email );
				} else {
					do_action( 'ctct_activity', 'Wangguard email validation failed.', $email, $return );
					$this->errors[] = new WP_Error( 'wangguard', __( 'Email validation failed.', 'constant-contact-api' ), $email, $return );

					return false;
				}
			} else {
				do_action( 'ctct_activity', 'WangGuard is not configured.', $email );
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
		if ( apply_filters( 'disable_constant_contact_akismet', false ) ) {
			return true;
		}

		// Akismet not activated
		if ( ! class_exists( 'Akismet' ) ) {
			do_action( 'ctct_activity', 'The Akismet class does not exist. Please upgrade to Version 3+ of Akismet.' );

			return true;
		}

		$key = Akismet::get_api_key();

		if ( empty( $key ) ) {

			do_action( 'ctct_activity', 'Empty Akismet API key. Not processing.' );

			return true;
		}

		/** Disable nonce verification */
		add_filter( 'akismet_comment_nonce', function () {
			return 'inactive';
		} );

		ob_start();

		$comment_data = array(
			'user_ID'              => get_current_user_id(),
			'comment_post_ID'      => isset( $_POST['cc_referral_post_id'] ) ? intval( $_POST['cc_referral_post_id'] ) : NULL,
			'comment_author'       => $Contact->get( 'name' ),
			'comment_author_email' => $Contact->get( 'email' ),
			'is_test'              => apply_filters( 'constant_contact_akismet_is_test', false )
		);

		$check = Akismet::auto_check_comment( $comment_data );

		ob_clean();

		if ( empty( $check ) || empty( $check['akismet_result'] ) ) {

			do_action( 'ctct_activity', 'There was an issue with Akismet: the response was invalid.', $check );

		} else {
			switch ( $check['akismet_result'] ) {
				case 'true':
					do_action( 'ctct_activity', __( 'Akismet caught this entry as spam', 'constant-contact-api' ), $check );

					return new WP_Error( 'akismet', __( 'Your entry was flagged as spam.', 'constant-contact-api' ), $check );
					break;
				case 'false':
					do_action( 'ctct_activity', __( 'Akismet cleared this comment', 'constant-contact-api' ), $check );
					break;
				default:
					do_action( 'ctct_error', sprintf( __( 'Akismet was unable to check this entry (response: %s), will automatically retry again later.', 'constant-contact-api' ), substr( $check['akismet_result'], 0, 50 ) ), $check );
			}
		}

		return true;
	}
}

CTCT_Process_Form::getInstance();