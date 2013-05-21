<?php

use Ctct\ConstantContact;
use Ctct\Util\Config;
use Ctct\Services\ListService;
class KWSConstantContact extends ConstantContact {

	var $configured = false;
	static $instance;

	private $cache_services = array('contacts' => 'contactService', 'campaigns' => 'emailMarketingService', 'activities' => 'activityService', 'lists' => 'listService');

	public function __construct($apiKey = null) {

		if(empty($apiKey)) { $apiKey = CTCT_APIKEY; }

		parent::__construct($apiKey);

		$Client = new KWSRestClient();

		$this->setRestClient($Client);
		$this->setUrls();
		$this->setHooks();

		self::$instance = $this;
	}

	function getInstance() {
		if(empty(self::$instance)) {
			self::$instance = new KWSConstantContact;
		}
		return self::$instance;
	}

	function setHooks() {
		add_action('ctct_flush_lists', array(&$this, '_flushLists'));
		add_action('ctct_flush_contacts', array(&$this, '_flushContacts'));
		add_action('ctct_flush_campaigns', array(&$this, '_flushCampaigns'));
	}

	function _flushLists() {
		delete_site_transient('ctct_all_lists');
	}
	function _flushContacts() {
		delete_site_transient('ctct_all_contacts');
	}
	function _flushCampaigns() {
		delete_site_transient('ctct_all_campaigns');
	}

	private function setUrls() {
		foreach($this->cache_services as $key => $service) {
			$this->{$service}->baseUrl = Config::get('endpoints.base_url') . Config::get('endpoints.'.$key);
		}
	}

	public function isConfigured($force = false) {

		if(!$force) {
			return get_site_option( 'ctct_configured' );
		}

		$_GET['cache'] = true;
		try {
			$contacts = $this->getContactByEmail('asdasdasdasd@asdasdasdasdasdasdasdasd.com');
			$this->configured = 1;
			update_site_option( 'ctct_configured', 1 );
		} catch(Exception $e) {
			$this->configured = 0;
			delete_site_option( 'ctct_configured' );
		}
		unset($_GET['cache']);

		return $this->configured;
	}

	/**
	 * Allow subclasses to set the REST Client used by the services
	 * @param RestClientInterface $restClient
	 * @see BaseService::setRestClient()
	 */
	function setRestClient($restClient) {
	    $this->contactService->setRestClient($restClient);
	    $this->emailMarketingService->setRestClient($restClient);
	    $this->activityService->setRestClient($restClient);
	    $this->campaignTrackingService->setRestClient($restClient);
	    $this->contactTrackingService->setRestClient($restClient);
	    $this->campaignScheduleService->setRestClient($restClient);
	    $this->listService->setRestClient($restClient);
	    $this->accountService->setRestClient($restClient);
	}

	function addUpdateContact($data) {

		$contact = new KWSContact($data);

		// check to see if a contact with the email addess already exists in the account
		$response = $this->getContactByEmail($contact->get('email'));

		// create a new contact if one does not exist
        if (empty($response->results)) {
            $action = "Creating Contact";
            try {
	            $returnContact = $this->addContact(CTCT_ACCESS_TOKEN, $contact);
			} catch(Exception $e) {
				r($e, "Creating Contact Failed");
				$returnContact = false;
			}
        // update the existing contact if address already existed
        } else {
            $action = "Updating Contact";
            r($response);
            $contact = $response;
            r($contact);
            try {
            	$contact = $contact->update($data);
            	$returnContact = $this->updateContact(CTCT_ACCESS_TOKEN, $contact);
        	} catch(Exception $e) {
        		r($e, true, 'Updating Contact Failed');
        		$returnContact = false;
        	}
        }
        r($returnContact, true, $action);
        return $returnContact;
	}

	function getAllContacts() {
		return $this->getAll('Contacts');
	}

	function getAllLists() {
		return $this->getAll('Lists');
	}

	function getAllContactSends($id) {
		return $this->getAll('ContactSends', $id);
	}

	function getAllEmailCampaigns($status = NULL) {

		return $this->getAll('EmailCampaigns', $status);

	}

	function getAll($type = '', $id_or_status = NULL, $param = NULL, &$results = array()) {

		do_action('ctct_debug', $type);
		add_filter('ctct_cache', function() { return 60 * 60 * 24; });

		if($type === 'EmailCampaigns'){
			if(!is_null($param)) { $id_or_status = null; }
			$fetch = $this->{"get{$type}"}(CTCT_ACCESS_TOKEN, $id_or_status, $param);
		} else if(!is_null($id_or_status)) {
			if(is_null($param)) { $param = '?limit=500'; }
			$fetch = $this->{"get{$type}"}(CTCT_ACCESS_TOKEN, $id_or_status, $param);
		} else {
			if(is_null($param)) { $param = '?limit=500'; }
			$fetch = $this->{"get{$type}"}(CTCT_ACCESS_TOKEN, $param);
		}

		// If this returns the results directly, not as an object.
		if(is_array($fetch)) { return $fetch; }

		foreach($fetch->results as $r) { $results[$r->id] = $r;  }

		if(!empty($fetch->next) && $fetch->next != $param) {
    		$this->getAll($type, $id_or_status, $fetch->next, $results);
    	}

    	return $results;
    }

    /**
     * Get contacts with a specified email eaddress
     * @param string $accessToken - Constant Contact OAuth2 access token
     * @param string $email - contact email address to search for
     * @return array
     */
    public function getContactByEmail($email)
    {
        $contact = $this->contactService->getContacts(CTCT_ACCESS_TOKEN, array('email' => $email));
        if(!empty($contact->results)) {
        	return new KWSContact($contact->results[0]);
        }
        return false;
    }


}