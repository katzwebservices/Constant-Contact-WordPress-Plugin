<?php

if (!class_exists( 'CTCT_Comment_Form_Signup' )) {

/**
 * Add a checkbox to the comment form to sign up users
 */
class CTCT_Comment_Form_Signup {

	/**
	 * Add the actions to add the checkbox
	 */
	function __construct() {
		if(CTCT_Settings::get('comment_form_signup')) {
			add_action( 'comment_form', array( &$this, 'comment_form' ) );
			add_action( 'preprocess_comment', array( &$this, 'comment_post' ) );
		}
	}

	/**
	 * Sends the email and (optionally) first name of the commenter to the
	 * current MailChimp list.
	 *
	 * @since 1.0.0
	 *
	 * @global WP_User $current_user WP User object
	 */
	public function comment_post($posted_data) {
		global $current_user;

		$data = array_map('esc_attr', $_POST);

		if(is_user_logged_in()) {
			$data['name'] = empty($data['name']) ? $current_user->data->display_name : $data['name'];
			$data['email'] = $current_user->data->user_email;
		}

		$data['email'] = rand(0,10000000).$data['email'];
		$data['lists'] = self::get_lists();

		// Is the checkbox set? If so, add/update user
		if (isset($data['ctct-subscribe']) && $data['ctct-subscribe'] === 'subscribe') {
			$returnContact = WP_CTCT::getInstance()->cc->addUpdateContact($data);
		}

		return $posted_data;
	}

	/**
	 * Get the setting for what lists the contact
	 * should be added to when they check the comment box
	 * @return array CTCT array of list IDs
	 */
	private function get_lists() {
		return (array)CTCT_Settings::get('comment_form_lists');
	}

	/**
	 * Outputs the checkbox area below the comment form that allows
	 * commenters to subscribe to the email list.
	 *
	 * @since 1.0.0
	 *
	 * @todo  Add checks as to whether someone's already subscribed. The logic's there, just not the check.
	 * @return null Return early if in the admin or the email list hasn't been set
	 */
	public function comment_form() {

		/** Don't do anything if we are in the admin */
		if ( is_admin() )
			return;
		$checked = $status = '';
		$clear = CTCT_Settings::get('comment_form_clear') ? 'style="clear: both;"' : '';

		if ( current_user_can( 'administrator' ) && !isset($_GET['debug_comment_form'])) {
			$output = '<p class="ctct-subscribe" ' . $clear . '><label>' . CTCT_Settings::get('comment_form_admin_text') . '</label></p>';
		}
		elseif ( 'subscribed' == $status ) {
			$output = '<p class="ctct-subscribe" ' . $clear . '>' . CTCT_Settings::get('comment_form_subscribed_text') . '</p>';
		}
		elseif ( 'pending' == $status ) {
			$output = '<p class="ctct-subscribe" ' . $clear . '>' . CTCT_Settings::get('comment_form_pending_text') . '</p>';
		}
		else {
			$output = '<p class="ctct-subscribe" ' . $clear . '>';

				$output .= sprintf('<label for="ctct-comment-subscribe"><input type="checkbox" name="ctct-subscribe" id="ctct-comment-subscribe" value="subscribe" style="width: auto;" %s /> %s</label>', $checked, CTCT_Settings::get('comment_form_check_text'));
			$output .= '</p>';
		}

		echo apply_filters( 'ctct_comment_form_checkbox_output', $output );

	}

}}

$CTCT_Comment_Form_Signup = new CTCT_Comment_Form_Signup;