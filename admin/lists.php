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
        return 'Lists';
    }

    protected function getTitle($type = '') {
        if($this->isEdit()) { return "Edit Lists"; }
        if($this->isSingle() || $type === 'single') { return "List #".intval(@$_GET['view']); }
        return 'Lists';
    }

	protected function add() {

	}

    protected function processForms() {
    }

    protected function edit() {

        $id = intval(@$_GET['edit']);

        /**
         * @todo Specify no contact ID error
         */
        if(!isset($id) || empty($id)) {
            echo 'no contact is specified.';
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

        /**
         * @todo Specify no contact ID error
         */
        if(!isset($id) || empty($id)) {
            echo 'no list is specified.';
            return;
        }

        // We define the transient key that is used so we can force-flush it
        add_filter('ctct_cachekey', function() { return 'ctct_contacts_from_list_'.intval(@$_GET['view']); });

        $Contacts = $this->cc->getAll('ContactsFromList', $id, 50);

        include(CTCT_DIR_PATH.'views/admin/view.contacts-view.php');

    }

    protected function view() {

        // We define the transient key that is used so we can force-flush it
        add_filter('ctct_cachekey', function() { return 'ctct_all_lists'; });

        $Lists = $this->cc->getAllLists();

    	if(empty($Lists)) {
    		echo 'Your account has no lists.';
    	} else {

            include(CTCT_DIR_PATH.'views/admin/view.lists-view.php');

    	}
    }
}

$CTCT_Admin_Lists = new CTCT_Admin_Lists();