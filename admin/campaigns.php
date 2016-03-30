<?php
/** @define "CTCT_DIR_PATH" "../" */

use Ctct\ConstantContact;
use Ctct\Components\Contacts\Contact;
use Ctct\Components\Contacts\Address;
use Ctct\Components\Contacts\CustomField;
use Ctct\Components\Contacts\Note;
use Ctct\Components\Contacts\ContactList;
use Ctct\Components\Contacts\EmailAddress;
use Ctct\Exceptions\CtctException;


class CTCT_Admin_Campaigns extends CTCT_Admin_Page {

	var $errors;
	var $id;
	var $can_edit = false;
	var $can_add = false;

	protected function getKey() {
		return "constant-contact-campaigns";
	}

	protected function getNavTitle() {
		return $this->getTitle( 'views' );
	}

	protected function getTitle( $type = '' ) {

		$title = __( 'Campaigns', 'constant-contact-api' );

		if ( empty( $type ) && $this->isEdit() || $type == 'edit' ) {
			$title = __('Edit Campaign', 'constant-contact-api');
		} elseif ( ( $this->isSingle() && empty( $type ) ) || $type === 'single' ) {

			$id = intval( $_GET['view'] );
			$emailCampaign = $this->cc->getEmailCampaign( CTCT_ACCESS_TOKEN, $id );

			if( is_object( $emailCampaign ) && ! empty( $emailCampaign->name ) ) {
				/** translators: %s is the campaign name, %d is the list ID */
				$title = sprintf( __( 'Campaign: "%s"', 'constant-contact-api' ), esc_html( $emailCampaign->name ) );
			} else {
				/** translators: %d is the campaign ID */
				$title = sprintf( __( 'Campaign #%s', 'constant-contact-api' ), $id );
			}
		}

		return $title;
	}

	/**
	 * @todo Implement adding campaigns. Needs better CTCT support.
	 */
	protected function add() {

	}

	protected function processForms() {
	}

	protected function edit() {

		$id = intval( @$_GET['edit'] );

		if ( ! isset( $id ) || empty( $id ) ) {
			esc_html_e( 'You have not specified a Campaign to edit', 'constant-contact-api' );

			return;
		}

		$CC_Campaign = $this->cc->getEmailCampaign( CTCT_ACCESS_TOKEN, $id );

		$Campaign = new KWSCampaign( $CC_Campaign );

		if ( $Campaign->status === 'DRAFT' ) {
			foreach (
				array(
					'last_run_date',
					'next_run_date',
					'tracking_summary',
					'sent_to_contact_lists',
					'click_through_details'
				) as $key
			) {
				unset( $Campaign->{$key} );
			}
		}

		include( CTCT_DIR_PATH . 'views/admin/view.campaign-edit.php' );
	}

	protected function single() {

		$id = isset( $_GET['view'] ) ? intval( $_GET['view'] ) : 0;

		if ( ! isset( $id ) || empty( $id ) ) {
			esc_html_e( 'You have not specified a Campaign to view', 'constant-contact-api' );

			return;
		}

		$CC_Campaign = $this->cc->getEmailCampaign( CTCT_ACCESS_TOKEN, $id );

		if( $CC_Campaign instanceof Exception ) {
			$this->show_exception( $CC_Campaign );
		} else {
			$Campaign = new KWSCampaign( $CC_Campaign );

			include( CTCT_DIR_PATH . 'views/admin/view.campaign-view.php' );
		}
	}

	protected function view() {

		$status = isset( $_GET['status'] ) ? $_GET['status'] : NULL;

		$Campaigns = $this->cc->getAllEmailCampaigns( $status );

		if( $Campaigns instanceof CtctException ) {
			$this->show_exception( $Campaigns );
			return;
		}

		kws_print_subsub( 'status', array(
			array( 'val' => '', 'text' => 'All' ),
			array( 'val' => 'RUNNING', 'text' => 'Running' ),
			array( 'val' => 'DRAFT', 'text' => 'Draft' ),
			array( 'val' => 'SCHEDULED', 'text' => 'Scheduled' ),
			array( 'val' => 'SENT', 'text' => 'Sent' ),
		) );

		include( CTCT_DIR_PATH . 'views/admin/view.campaigns-view.php' );
	}
}

new CTCT_Admin_Campaigns;
