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
			return __( "Edit Contact", 'constant-contact-api' );
		}
		if ( empty( $value ) && $this->isSingle() || $value == 'single' ) {
			return __( 'Contact', 'constant-contact-api' );
		}
		if ( empty( $value ) && $this->isAdd() || $value == 'add' ) {
			return __( 'Add a Contact', 'constant-contact-api' );
		}

		return __( 'Contacts', 'constant-contact-api' );
	}

	protected function getNavTitle() {
		return __( 'Contacts', 'constant-contact-api' );
	}

	protected function add() {

		$data = esc_attr_recursive( (array) $_POST );

		$Contact = new KWSContact( $data );

		if( $Contact instanceof Exception ) {
			$this->show_exception( $Contact );
		} else {
			include( CTCT_DIR_PATH . 'views/admin/view.contact-addedit.php' );
		}
	}

	protected function processForms() {

		$action = "Getting Contact By Email Address";

		// check if the form was submitted
		if ( isset( $_POST['email_addresses'] ) && ! empty( $_POST['email_addresses'] ) ) {


			try {

				$data = $_POST;

				$returnContact = $this->cc->addUpdateContact( $data );

				if( $returnContact instanceof CtctException ) {
					throw $returnContact;
				}

				// create a new contact if one does not exist
				if ( $returnContact && ! is_wp_error( $returnContact ) ) {

					// Force getAll() to clear
					add_option( 'ctct_flush_contacts', 'flush!', '', 'no' );

					wp_redirect( add_query_arg( array(
						'page' => $this->getKey(),
						'view' => $returnContact->id
					), admin_url( 'admin.php' ) ) );

					// update the existing contact if address already existed
				}

				// catch any exceptions thrown during the process and print the errors to screen
			} catch ( CtctException $ex ) {
				
				$exception = KWSConstantContact::convertException( $ex );

				if( ! is_array( $exception ) ) {
					$exception = array( $exception );
				}
				
				// Could be WP_Error or array of WP_Errors
				foreach ( $exception as $e ) {
					$this->errors[] = $e;
				}
				do_action('ctct_error', $action.' Exception', $ex );
			}
		}
	}

	protected function edit() {

		if ( empty( $this->id ) ) {
			esc_html_e( 'You have not specified a Contact to edit', 'constant-contact-api' );

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
			esc_html_e( 'You have not specified a Contact to view', 'constant-contact-api' );

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

		if( $Contact instanceof Exception ) {
			$this->show_exception( $Contact );
			return;
		} elseif ( is_null( $Contact->id ) ) {
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

			$edit_link = sprintf( '<a href="%s">%s</a>', esc_url( $edit_url ), esc_html__('View their profile', 'constant-contact-api' ) );

			$first_name = $Contact->get( 'first_name' );

			/** translators: %s is a link to "View their profile" */
			$user_details_text = sprintf( esc_html__('%s is a user on this site. %s.', 'constant-contact-api'), $first_name, $edit_link );

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


		$params = kws_get_contacts_view_params();

		$Contacts = $this->cc->getAllContacts( $params );

		if( $Contacts instanceof CtctException ) {
			$this->show_exception( $Contacts );
			return;
		}
		
		kws_print_subsub( 'status', array(
			array( 'val' => '', 'text' => __('Recently Updated', 'constant-contact-api') ),
			array( 'val' => 'ACTIVE', 'text' => __('Active', 'constant-contact-api') ),
			array( 'val' => 'UNCONFIRMED', 'text' => __('Unconfirmed', 'constant-contact-api') ),
			array( 'val' => 'OPTOUT', 'text' => __('Opt-Out', 'constant-contact-api') ),
			array( 'val' => 'REMOVED', 'text' => __('Removed', 'constant-contact-api') ),
		) );

		/** @var Contact[] $Contacts Get them in chronological order */
		$Contacts = array_reverse( $Contacts, true );
		include( CTCT_DIR_PATH . 'views/admin/view.contacts-view.php' );
	}
}

new CTCT_Admin_Contacts;