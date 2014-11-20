<?php
/**
 * Handle registration hooks
 *
 */

class CTCT_Registration {

	var $method;
	var $format;

	/**
	 * Set up hooks, filters, and whether to process registration at all
	 */
	function __construct() {

		$this->method = CTCT_Settings::get('register_page_method');
		$this->format = CTCT_Settings::get('list_selection_format');

		// Disable registration
		if($this->method === 'none') { return; }

		// register user registration action
		add_action('register_post', array(&$this, 'process_submission'), 10, 3);
		add_filter('wpmu_signup_user_notification', array(&$this, 'process_submission_multisite')); // For multisite

		// register show user register form action
		add_action('signup_extra_fields', array(&$this, 'form') ); // For multisite
		add_action('register_form', array(&$this, 'form') );
	}

	/**
	 * The multisite registration process for logged-in users seems to lack a 'register_post'-like solution. This
	 * attempts to mimic it by processing on 'wpmu_signup_user_notification', which only is called on successful registration.
	 *
	 * @global  $pagenow
	 * @param array $user
	 * @return bool
	 */
	function process_submission_multisite($user = array()) {
		global $pagenow;

		if($pagenow == 'wp-signup.php' && isset($_POST['user_email'])) {
			$errors = new WP_Error();
			$this->process_submission(false, $_POST['user_email'], $errors);
			return true;
		}
		return false;
	}

	/**
	 * Hook into 'register_post' action to manage subscription of new users during user registration
	 *
	 * @uses KWSConstantContact::addUpdateContact()
	 * @global  $cc
	 * @param string $login The login name of the user
	 * @param string $email The email address of the user
	 * @param WP_Error $errors any errors going on thrown by WordPress
	 * @return <type>
	 */
	function process_submission($login = '', $email = '',$errors = false) {
		$KWSLog = new KWSLog;

		do_action('ctct_log', 'Starting to process registration for '.$login);

		// Don't register users if there are errors thrown by WordPress
		if(!empty($errors->errors)) {
			return;
		}

		$has_subscribed = false;
		$post = $_POST;

		if($this->method == 'checkbox' && !empty($post['ctct-subscribe'])) {
			// subscribe or update the user to the lists admin have selected
			$has_subscribed = true;
			$post['lists'] = CTCT_Settings::get('registration_checkbox_lists');
		} else {
			if(!empty($post['lists']) && is_array($post['lists'])) {
				// subscribe or update the user to the lists they have selected
				$has_subscribed = true;
			}
		}

		if($has_subscribed) {
			do_action('ctct_log', sprintf('Processing Registration for %s', $email));
			$returnContact = WP_CTCT::getInstance()->cc->addUpdateContact(apply_filters('cc_register_post_data', $post, $login, $email, $errors));
		}

	}

	/**
	 * Hook into 'register_form' action to show our subscription form to users while they are registering themselves
	 *
	 * @return <type>
	 */
	function form() {

		$reg = '';

		$regform = '
		<style>
			.ctct-register { margin-bottom:16px }
			.ctct-register p { margin:8px 0 }
		</style>';

		// Margin bottom is to match .login form .input bottom margin.
		// Sorry for the hack.
		$regform .= '<div class="ctct-register">';

		$title = CTCT_Settings::get('signup_title');

		switch( $this->method ) {
			case 'checkbox':
				$reg = '<p class="ctct-subscribe">';

					$reg .= sprintf('<label for="ctct-registration-subscribe"><input type="checkbox" name="ctct-subscribe" id="ctct-registration-subscribe" value="subscribe" style="width: auto;" %s /> %s</label>', checked(CTCT_Settings::get('default_opt_in'), true, false), $title);
				$reg .= '</p>';
				$title = false;
				break;
			default:

				$title = empty($title) ? '' : '<label>'.$title.'</label>';

				$include = CTCT_Settings::get('registration_checkbox_lists');

				$checked = isset($_POST['lists']) ? $_POST['lists'] : (CTCT_Settings::get('default_opt_in') ? true : false);

				$reg .= KWSContactList::outputHTML( $include, array(
					'type' => $this->method,
					'checked' => $checked,
					'blank' => CTCT_Settings::get('default_select_option_text')
				));

			break;
		}

		// Prepare the description from the settings screen
		$signup_description =  trim(rtrim(CTCT_Settings::get('signup_description')));

		if(!empty($signup_description)) {
			$signup_description = wpautop($signup_description);
			$signup_description = "<div class='description'>$signup_description</div>";
		}

		$regform .= (CTCT_Settings::get('signup_description_position') === 'before') ? $title . $signup_description . $reg : $title . $reg . $signup_description;

		$regform .= '</div>';

		$regform = apply_filters('constant_contact_register_form', $regform);

		echo $regform;

	}

}

$CTCT_Registration = new CTCT_Registration;