<?php
use Ctct\ConstantContact;
use Ctct\Components\Contacts\Contact;
use Ctct\Components\Contacts\Address;
use Ctct\Components\Contacts\CustomField;
use Ctct\Components\Contacts\Note;
use Ctct\Components\Contacts\ContactList;
use Ctct\Components\Contacts\EmailAddress;
use Ctct\Exceptions\CtctException;


class CTCT_Admin_Lists extends CTCT_Admin_Page {

    var $errors;
    var $id;
    var $can_add = false;
    var $can_edit = false;
    var $component = 'ContactList';

    protected function getKey() {
        return "constant-contact-lists";
    }

    protected function getNavTitle() {
        return __('Lists', 'ctct');
    }

    protected function getTitle($type = '') {
        if($this->isEdit()) { return "Edit Lists"; }
        if($this->isSingle() || $type === 'single') { return "List #".intval(@$_GET['view']); }
        return __('Lists', 'ctct');
    }

	protected function add() {

	}

    protected function processForms() {
    }

    protected function edit() {

        $id = intval(@$_GET['edit']);

        if(!isset($id) || empty($id)) {
            esc_html_e('You have not specified a List to edit', 'ctct');
            return;
        }

        $List = $this->cc->getList(CTCT_ACCESS_TOKEN, $id);

        include(CTCT_DIR_PATH.'views/admin/view.list-edit.php');
    }

    /**
     * Show all the contacts for a single list
     * @return [type] [description]
     */
    protected function single() {

        $id = intval(@$_GET['view']);

        if(!isset($id) || empty($id)) {
            esc_html_e('You have not specified a List to view.', 'ctct');
            return;
        }

        // We define the transient key that is used so we can force-flush it
        add_filter('ctct_cachekey', function() { return 'ctct_contacts_from_list_'.intval(@$_GET['view']); });

        $Contacts = $this->cc->getAll('ContactsFromList', $id, 50 );

        include(CTCT_DIR_PATH.'views/admin/view.contacts-view.php');

    }

    protected function view() {

        // We define the transient key that is used so we can force-flush it
        add_filter('ctct_cachekey', function() { return 'ctct_all_lists'; });

        $Lists = $this->cc->getAllLists();

    	if(empty($Lists)) {
    		esc_html_e( 'Your account has no lists.', 'ctct');
    	} else {

            include(CTCT_DIR_PATH.'views/admin/view.lists-view.php');

    	}
    }
}

new CTCT_Admin_Lists;