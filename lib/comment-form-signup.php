<?php

if (!class_exists( 'CTCT_Comment_Form_Signup' )) {
class CTCT_Comment_Form_Signup {

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
	 * @global array $tgm_mc_options Array of plugin options
	 * @global array $tgm_mc_comment_data Array of submitted comment data
	 */
	public function comment_post($posted_data) {
		global $current_user;

		$data = $_POST;

		if(is_user_logged_in()) {
			$data['name'] = empty($data['name']) ? $current_user->data->display_name : $data['name'];
			$data['email'] = $current_user->data->user_email;
		}

		$data['email'] = rand(0,10000000).$data['email'];
		$data['lists'] = self::get_lists();

		if (isset($data['ctct-subscribe']) && $data['ctct-subscribe'] === 'subscribe') {
			$returnContact = WP_CTCT::getInstance()->cc->addUpdateContact($data);
			do_action('ctct_log', $returnContact);
		}

		return $posted_data;
	}

	private function get_lists() {
		return (array)CTCT_Settings::get('comment_form_lists');
	}

	/**
	 * Outputs the checkbox area below the comment form that allows
	 * commenters to subscribe to the email list.
	 *
	 * @since 1.0.0
	 *
	 * @global array $tgm_mc_options Array of plugin options
	 * @return null Return early if in the admin or the email list hasn't been set
	 */
	public function comment_form() {

		/** Don't do anything if we are in the admin */
		if ( is_admin() )
			return;

		/** Don't do anything unless the user has already logged in and selected a list */
/*		if ( empty( $tgm_mc_options['current_list_id'] ) )
			return;
*/
		$clear = CTCT_Settings::get('comment_form_clear') ? 'style="clear: both;"' : '';
		$checked_status = ( ! empty( $_COOKIE['tgm_mc_checkbox_' . COOKIEHASH] ) && 'checked' == $_COOKIE['tgm_mc_checkbox_' . COOKIEHASH] ) ? true : false;
		$checked = $checked_status ? 'checked="checked"' : '';
		$status = ''; //$this->get_viewer_status();

		if ( 'admin' == $status ) {
			echo '<p class="ctct-subscribe" ' . $clear . '>' . CTCT_Settings::get('comment_form_admin_text') . '</p>';
		}
		elseif ( 'subscribed' == $status ) {
			echo '<p class="ctct-subscribe" ' . $clear . '>' . CTCT_Settings::get('comment_form_subscribed_text') . '</p>';
		}
		elseif ( 'pending' == $status ) {
			echo '<p class="ctct-subscribe" ' . $clear . '>' . CTCT_Settings::get('comment_form_pending_text') . '</p>';
		}
		else {
			echo '<p class="ctct-subscribe" ' . $clear . '>';

				echo sprintf('<label for="ctct-comment-subscribe"><input type="checkbox" name="ctct-subscribe" id="ctct-comment-subscribe" value="subscribe" style="width: auto;" %s /> %s</label>', $checked, CTCT_Settings::get('comment_form_check_text'));
			echo '</p>';
		}

	}

}}

$CTCT_Comment_Form_Signup = new CTCT_Comment_Form_Signup;