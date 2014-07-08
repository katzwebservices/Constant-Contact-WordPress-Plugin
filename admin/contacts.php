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
            return __("Edit Contact", 'constant-contact-api');
        if(empty($value) && $this->isSingle() || $value == 'single')
            return __('Contact', 'constant-contact-api');
        if(empty($value) && $this->isAdd() || $value == 'add')
            return __('Add a Contact', 'constant-contact-api');

        return 'Contacts';
    }

    protected function getNavTitle() {
        return 'Contacts';
    }

	protected function add() {

        $Contact = new KWSContact();

        include(CTCT_DIR_PATH.'views/admin/view.contact-addedit.php');
	}

    protected function processForms() {

        // check if the form was submitted
        if (isset($_POST['email_addresses']) && !empty($_POST['email_addresses'])) {
            $email = esc_attr($_REQUEST['email_addresses']);

            /////RANDASDSADSADASD
            $email = rand(0,10000).$email;

            $action = "Getting Contact By Email Address";
            try {

                $data = $_POST;
                #$data = new KWSContact($data);

               # $data['email_addresses'] = $email;

               /* // Placeholder until we get $_POST
                $data = array(
                    'first_name' => 'Example',
                    'last_name' => 'Jones',
                    'job_title' => 'President',
                    'email_addresses' => array(rand(0, 0200000).$email),
                    // 'address' => array(
                    //             'line1' => '584 Elm Street',
                    //             'city' => 'Cortez',
                    //             'address_type' => 'personal',
                    //             'country_code' => 'us',
                    //         ),
                    'addresses' => array(
                            array(
                                'line1' => '14870 Road 29',
                                'address_type' => 'personal',
                                'country_code' => 'us',
                            ),
                            array(
                                'line1' => '216 A',
                                'line2' => 'W. Montezuma Ave.',
                                'city' => 'Cortez',
                                'postal_code' => '81321',
                                'address_type' => 'business',
                                'country_code' => 'us',
                            ),
                    ),
                    'custom_fields' => array(
                         array(
                         'name' => 'CustomField1',
                         'value' => 'custom value now doesnt match'
                        ),
                        array(
                          'name' => 'CustomField2',
                          'value' => 'Does not match'
                         )
                    ),
                    'notes' => array(
                        array(
                         'note' => 'Note 1'
                        )
                    ),
                    'lists' => array('3', '27', '34')
                );*/

                $returnContact = $this->cc->addUpdateContact($data);

                // create a new contact if one does not exist
                if ($returnContact) {

                    wp_redirect(add_query_arg(array('page' => $this->getKey(), 'view' => $returnContact->id), admin_url('admin.php')));

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

        /**
         * @todo Specify no contact ID error
         */
        if( empty( $this->id ) ) {
            echo 'no contact is specified.';
            return;
        }

        $Contact = $this->cc->getContact(CTCT_ACCESS_TOKEN, $this->id );

        $Contact = new KWSContact($Contact);

        include(CTCT_DIR_PATH.'views/admin/view.contact-addedit.php');
    }

    protected function single() {

        $id = $this->id;

        /**
         * @todo Specify no contact ID error
         */
        if( empty( $id ) ) {
            echo 'no contact is specified.';
            return;
        }

        if($refresh = get_option( 'ctct_refresh_contact_'.$id)) {
            delete_option( 'ctct_refresh_contact_'.$id);
            add_filter('ctct_cache', '__return_false');
        }

        $CC_Contact = $this->cc->getContact(CTCT_ACCESS_TOKEN, $id);
        $Contact = new KWSContact($CC_Contact);
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
    		echo __( 'Your account has no contacts.', 'constant-contact-api' );
    	} else {
    		include(CTCT_DIR_PATH.'views/admin/view.contacts-view.php');
    	}
    }
}

new CTCT_Admin_Contacts;