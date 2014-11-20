<?php
use \KWSContactList;
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
    	return $this->getTitle('views');
    }
    protected function getTitle($value = '') {
        if(empty($value) && $this->isEdit() || $value == 'edit')
            return "Edit Campaign";
        if(empty($value) && $this->isSingle() || $value == 'single')
            return sprintf('Campaign #%s', $_GET['view']);

        return 'Campaigns';
    }

    /**
     * @todo Implement adding campaigns. Needs better CTCT support.
     */
	protected function add() {}

    protected function processForms() {}

    protected function edit() {

        $id = intval(@$_GET['edit']);

        if(!isset($id) || empty($id)) {
            esc_html_e('You have not specified a Campaign to edit', 'ctct');
            return;
        }

        $CC_Campaign = $this->cc->getEmailCampaign(CTCT_ACCESS_TOKEN, $id);

        $Campaign = new KWSCampaign($CC_Campaign);

        if($Campaign->status === 'DRAFT') {
        	foreach(array('last_run_date', 'next_run_date', 'tracking_summary', 'sent_to_contact_lists', 'click_through_details') as $key) {
        		unset($Campaign->{$key});
        	}
        }

        include(CTCT_DIR_PATH.'views/admin/view.campaign-edit.php');
    }

    protected function single() {

        $id = intval(@$_GET['view']);

        if(!isset($id) || empty($id)) {
            esc_html_e('You have not specified a Campaign to view', 'ctct');
            return;
        }

        $CC_Campaign = $this->cc->getEmailCampaign(CTCT_ACCESS_TOKEN, $id);
        $Campaign = new KWSCampaign($CC_Campaign);

        include(CTCT_DIR_PATH.'views/admin/view.campaign-view.php');

    }

    protected function view() {

    	$status = isset($_GET['status']) ? $_GET['status'] : null;

        add_filter('ctct_cachekey', function() {
            return isset($_GET['status']) ? false : 'ctct_all_campaigns';
        });

    	$Campaigns = $this->cc->getAllEmailCampaigns($status);

        kws_print_subsub('status', array(
            array('val' => '', 'text' => 'All'),
            array('val' => 'DRAFT', 'text' => 'Draft'),
            array('val' => 'RUNNING', 'text' => 'Running'),
            array('val' => 'SCHEDULED', 'text' => 'Scheduled'),
            array('val' => 'SENT', 'text' => 'Sent'),
        ));

        include(CTCT_DIR_PATH.'views/admin/view.campaigns-view.php');
    }
}

new CTCT_Admin_Campaigns;
