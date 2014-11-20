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

    protected function getTitle($value = '') {
        if(empty($value) && $this->isEdit() || $value == 'edit')
            return __("Edit Contact", 'ctct');
        if(empty($value) && $this->isSingle() || $value == 'single')
            return __('Contact', 'ctct');
        if(empty($value) && $this->isAdd() || $value == 'add')
            return __('Add a Contact', 'ctct');

        return __('Contacts', 'ctct');
    }

    protected function getNavTitle() {
        return __('Contacts', 'ctct');
    }

	protected function add() {

        $Contact = new KWSContact();

        include(CTCT_DIR_PATH.'views/admin/view.contact-addedit.php');
	}

    protected function processForms() {

        // check if the form was submitted
        if (isset($_POST['email_addresses']) && !empty($_POST['email_addresses'])) {

            $action = "Getting Contact By Email Address";

            try {

                $data = $_POST;

                $returnContact = $this->cc->addUpdateContact( $data );

                // create a new contact if one does not exist
                if ($returnContact) {

                    wp_redirect(add_query_arg(array(
                        'page' => $this->getKey(),
                        'view' => $returnContact->id
                    ), admin_url('admin.php')));

                // update the existing contact if address already existed
                } else {

                }

            // catch any exceptions thrown during the process and print the errors to screen
            } catch (CtctException $ex) {
                r($ex, true, $action.' Exception');
                $this->errors = $ex;
            }
        }
    }

    protected function edit() {

        if( empty( $this->id ) ) {
            esc_html_e('You have not specified a Contact to edit', 'ctct');
            return;
        }

        $Contact = $this->cc->getContact(CTCT_ACCESS_TOKEN, $this->id );

        // The fetching of the contact failed.
        if( is_null( $Contact->id ) ) {
            return;
        }

        $Contact = new KWSContact($Contact);

        include(CTCT_DIR_PATH.'views/admin/view.contact-addedit.php');
    }

    protected function single() {

        $id = $this->id;

        if( empty( $id ) ) {
            esc_html_e('You have not specified a Contact to view', 'ctct');
            return;
        }

        if($refresh = get_option( 'ctct_refresh_contact_'.$id)) {
            delete_option( 'ctct_refresh_contact_'.$id);
            add_filter('ctct_cache', '__return_false');
        }

        $Contact = $this->cc->getContact(CTCT_ACCESS_TOKEN, $id);

        // The fetching of the contact failed.
        if( is_null( $Contact->id ) ) {
            return;
        }

        $Contact = new KWSContact($Contact);
        $summary = $this->cc->getContactSummaryReport(CTCT_ACCESS_TOKEN, $Contact->get('id'));
        include(CTCT_DIR_PATH.'views/admin/view.contact-view.php');
    }

    protected function view() {

        add_filter('ctct_cachekey', function() {
            return isset($_GET['status']) ? false : 'ctct_all_contacts';
        });

        $Contacts = $this->cc->getAllContacts();

        kws_print_subsub('status', array(
            array('val' => '', 'text' => 'All'),
            array('val' => 'ACTIVE', 'text' => 'Active'),
            array('val' => 'UNCONFIRMED', 'text' => 'Unconfirmed'),
            array('val' => 'OPTOUT', 'text' => 'Opt-Out'),
            array('val' => 'REMOVED', 'text' => 'Removed'),
            array('val' => 'NON_SUBSCRIBER', 'text' => 'Non-Subscriber'),
        ));

    	if(empty($Contacts)) {
    		esc_html_e( 'Your account has no contacts.', 'ctct');
    	} else {
    		include(CTCT_DIR_PATH.'views/admin/view.contacts-view.php');
    	}
    }
}

new CTCT_Admin_Contacts;