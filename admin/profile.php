<?php

class CTCT_Admin_User_Profile extends CTCT_Admin_Page {

	var $subscribe_method;

	function add_menu() {
		return;
	}

	function add() {
	}

	function edit() {
	}

	function view() {
	}

	function single() {
	}

	function processForms() {
	}

	function addActions() {
		// Get our "User Subscription Method" option value.
		$this->subscribe_method = CTCT_Settings::get( 'profile_page_form' );

		// If it is disabled exit this function
		if ( $this->subscribe_method ) {

			// register user update action
			add_action( 'profile_update', array( &$this, 'update' ) );

		}

		// register show user update form action
		add_action( 'show_user_profile', array( &$this, 'display' ), 1 );
		add_action( 'edit_user_profile', array( &$this, 'display' ), 1 );
	}

	/**
	 * Hook into profile_update action to update our user subscription info if necessary
	 *
	 * @param <type> $user
	 *
	 * @return <type>
	 */
	function update( $user ) {

		CTCT_Process_Form::getInstance()->process();

		$this->errors = CTCT_Process_Form::getInstance()->get_errors();
	}

	/**
	 * Hook into show_user_profile action to display our user subscription settings if necessary
	 *
	 * @global  $cc
	 *
	 * @param <type> $user
	 *
	 * @return <type>
	 */
	function display( $user ) {

		$Contact = new KWSContact( $this->cc->getContactByEmail( $user->data->user_email ) );

		if ( $Contact && current_user_can( 'edit_users' ) && ! isset( $_GET['debug-user-display'] ) ) {
			echo sprintf( __( '
				<p><img src="%s" width="225" height="33" alt="Constant Contact" class="block" /><a href="%s">Admin-Only: Edit this User\'s Details</a> %s</p>
			', 'constant-contact-api' ),
				plugins_url( 'images/admin/logo-horizontal.png', CTCT_FILE ),
				admin_url( 'admin.php?page=constant-contact-contacts&amp;edit=' . $Contact->id ),
				constant_contact_tip( __( 'Users will not see this link or the Constant Contact logo.', 'constant-contact-api' ), false )
			);

		}

		if ( ! $this->subscribe_method ) {
			return;
		}

		$register_page_method = CTCT_Settings::get( 'profile_page_form' );

		// Prepare the description from the settings screen
		$signup_description = CTCT_Settings::get( 'signup_description' );
		if ( $signup_description )    :
			$signup_description = wpautop( $signup_description );
			$signup_description = "<div class='description'>$signup_description</div>";
		endif;

		?>
		<h3><?php echo CTCT_Settings::get( 'signup_title' ); ?></h3>
		<?php echo $signup_description; ?>

		<p><?php

			$lists = (array) $Contact->get( 'lists', true );
			echo KWSContactList::outputHTML( 'all', array(
				'checked' => $lists,
			) );
			?></p>
		<br/>
	<?php
	}
}

new CTCT_Admin_User_Profile;