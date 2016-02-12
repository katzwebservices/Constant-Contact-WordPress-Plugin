<?php
use Ctct\ConstantContact;
use Ctct\Components\Contacts\Contact;
use Ctct\Components\Contacts\Address;
use Ctct\Components\Contacts\CustomField;
use Ctct\Components\Contacts\Note;
use Ctct\Components\Contacts\ContactList;
use Ctct\Components\Contacts\EmailAddress;
use Ctct\Exceptions\CtctException;


class CTCT_Admin_Contacts extends CTCT_Admin_Page {

	var $errors;
	var $id;
	var $can_edit = true;
	var $can_add = true;
	var $component = 'Contact';

	protected function getKey() {
		return "constant-contact-contacts";
	}

	/**
	 * @param string $value
	 *
	 * @return mixed
	 */
	protected function getTitle( $value = '' ) {
		if ( empty( $value ) && $this->isEdit() || $value == 'edit' ) {
			return __( "Edit Contact", 'ctct' );
		}
		if ( empty( $value ) && $this->isSingle() || $value == 'single' ) {
			return __( 'Contact', 'ctct' );
		}
		if ( empty( $value ) && $this->isAdd() || $value == 'add' ) {
			return __( 'Add a Contact', 'ctct' );
		}

		return __( 'Contacts', 'ctct' );
	}

	protected function getNavTitle() {
		return __( 'Contacts', 'ctct' );
	}

	protected function add() {

		$data = esc_attr_recursive( (array) $_POST );

		$Contact = new KWSContact( $data );

		include( CTCT_DIR_PATH . 'views/admin/view.contact-addedit.php' );
	}

	protected function processForms() {

		// check if the form was submitted
		if ( isset( $_POST['email_addresses'] ) && ! empty( $_POST['email_addresses'] ) ) {

			$action = "Getting Contact By Email Address";

			try {

				$data = $_POST;

				$returnContact = $this->cc->addUpdateContact( $data );

				// create a new contact if one does not exist
				if ( $returnContact && ! is_wp_error( $returnContact ) ) {

					// Force getAll() to clear
					add_option( 'ctct_flush_contacts', 'flush!', '', 'no' );

					wp_redirect( add_query_arg( array(
						'page' => $this->getKey(),
						'view' => $returnContact->id
					), admin_url( 'admin.php' ) ) );

					// update the existing contact if address already existed
				} else {
					$this->errors[] = $returnContact;
				}

				// catch any exceptions thrown during the process and print the errors to screen
			} catch ( CtctException $ex ) {
				r( $ex, true, $action . ' Exception' );
				$this->errors = $ex;
			}
		}
	}

	protected function edit() {

		if ( empty( $this->id ) ) {
			esc_html_e( 'You have not specified a Contact to edit', 'ctct' );

			return;
		}

		$Contact = $this->cc->getContact( CTCT_ACCESS_TOKEN, $this->id );

		// The fetching of the contact failed.
		if ( is_null( $Contact->id ) ) {
			return;
		}

		$Contact = new KWSContact( $Contact );

		/** @define "CTCT_DIR_PATH" "../" */
		include( CTCT_DIR_PATH . 'views/admin/view.contact-addedit.php' );
	}

	protected function single() {

		$id = $this->id;

		if ( empty( $id ) ) {
			esc_html_e( 'You have not specified a Contact to view', 'ctct' );

			return;
		}

		/**
		 * When the contact is updated using AJAX, we add an option to enforce a refresh
		 * @see KWSAJAX::processAjax
		 */
		if ( $refresh = get_option( 'ctct_refresh_contact_' . $id ) ) {
			delete_option( 'ctct_refresh_contact_' . $id );
			add_filter( 'ctct_cache', '__return_false' );
		}

		$Contact = $this->cc->getContact( CTCT_ACCESS_TOKEN, $id );

		// The fetching of the contact failed.
		if ( is_null( $Contact->id ) ) {
			return;
		}

		$Contact = new KWSContact( $Contact );

		$summary_report = $this->generate_summary_report( $Contact );
		$user_details = $this->generate_user_details( $Contact );

		include( CTCT_DIR_PATH . 'views/admin/view.contact-view.php' );
	}

	/**
	 * @param KWSContact $Contact
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	private function generate_user_details( $Contact ) {

		$output = null;

		$email_address = $Contact->get('email_address');

		if( $user = get_user_by('email', $email_address ) ) {

			$edit_url = add_query_arg( 'wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), get_edit_user_link( $user->ID ) );

			$edit_link = sprintf( '<a href="%s">%s</a>', esc_url( $edit_url ), esc_html__('View their profile', 'ctct' ) );

			$first_name = $Contact->get( 'first_name' );

			/** translators: %s is a link to "View their profile" */
			$user_details_text = sprintf( esc_html__('%s is a user on this site. %s.', 'ctct'), $first_name, $edit_link );

			$output = '<div class="user-details"><h3>' . $user_details_text . '</h3></div>';
		}

		return $output;
	}

	/**
	 * @param KWSContact $Contact
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	private function generate_summary_report( $Contact ) {

		/**
		 * @var Ctct\Components\Tracking\TrackingSummary
		 */
		$summary = $this->cc->contactTrackingService->getSummary( CTCT_ACCESS_TOKEN, $Contact->get( 'id' ) );

		return kws_generate_tracking_summary_report( $summary );
	}

	protected function view() {


		kws_print_subsub( 'status', array(
			array( 'val' => '', 'text' => __('Recently Updated', 'ctct') ),
			array( 'val' => 'ACTIVE', 'text' => __('Active', 'ctct') ),
			array( 'val' => 'UNCONFIRMED', 'text' => __('Unconfirmed', 'ctct') ),
			array( 'val' => 'OPTOUT', 'text' => __('Opt-Out', 'ctct') ),
			array( 'val' => 'REMOVED', 'text' => __('Removed', 'ctct') ),
		) );


		$params = array();
		$since = false;
		if( isset( $_GET['status'] ) ) {
			$params['status'] = esc_attr( $_GET['status'] );
		} else {
			$since = '-1 month';
		}
		$since = isset( $_GET['modified_since'] ) ? esc_attr( $_GET['modified_since'] ) : $since;
		if( $since = strtotime( $since ) ) {
			$params['modified_since'] = date( 'c', $since );
		}

		$Contacts = $this->cc->getAllContacts( $params );

		/** @var Contact[] $Contacts Get them in chronological order */
		$Contacts = array_reverse( $Contacts, true );
		include( CTCT_DIR_PATH . 'views/admin/view.contacts-view.php' );
	}
}

new CTCT_Admin_Contacts;