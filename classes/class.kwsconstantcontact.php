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

	/**
	 * Get access to instance or create one
	 * @return KWSConstantContact
	 */
	function getInstance() {
		if(empty(self::$instance)) {
			self::$instance = new KWSConstantContact;
		}
		return self::$instance;
	}

	/**
	 * Add actions to clear caches when appropriate.
	 *
	 * Clear the lists cache by running `do_action('ctct_flush_lists');`
	 * Clear the contacts cache by running `do_action('ctct_flush_contacts');`
	 * Clear the campaigns cache by running `do_action('ctct_flush_campaigns');`
	 */
	function setHooks() {
		add_action('ctct_flush_lists', array(&$this, '_flushLists'));
		add_action('ctct_flush_contacts', array(&$this, '_flushContacts'));
		add_action('ctct_flush_campaigns', array(&$this, '_flushCampaigns'));
	}

	/**
	 * Clear the lists cache
	 */
	function _flushLists() {
		delete_transient('ctct_all_lists');
	}
	/**
	 * Clear the contacts cache
	 */
	function _flushContacts() {
		delete_transient('ctct_all_contacts');
	}
	/**
	 * Clear the campaigns cache
	 */
	function _flushCampaigns() {
		delete_transient('ctct_all_campaigns');
	}

	private function setUrls() {
		foreach($this->cache_services as $key => $service) {
			$this->{$service}->baseUrl = Config::get('endpoints.base_url') . Config::get('endpoints.'.$key);
		}
	}

	/**
	 * Check whether the plugin is configured with an active account.
	 *
	 * @uses  KWSConstantContact::getContactByEmail()
	 * @param  boolean $force Force re-checking configuration. Otherwise, it will use previous results as stored in the `ctct_configured` option.
	 * @return boolean    `true`: Configured; `false`: Not configured properly.
	 */
	public function isConfigured($force = false) {

		if(!$force) {
			return get_site_option( 'ctct_configured' );
		}

		// Force caching feature to turn off.
		$_GET['cache'] = true;
		try {
			$contacts = $this->getContactByEmail('asdasdasdasd@asdasdasdasdasdasdasdasd.com');
			$this->configured = 1;
			update_site_option( 'ctct_configured', 1 );
		} catch(Exception $e) {
			$this->configured = 0;
			delete_site_option( 'ctct_configured' );
		}
		// Turn caching back on.
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

	/**
	 * Add a contact if it doesn't exist and update it if it does.
	 * @param array|KWSContact $data Array of contact data or an existing KWSContact or Contact object.
	 */
	function addUpdateContact($data) {

		$contact = new KWSContact($data);

		// check to see if a contact with the email addess already exists
		$existingContact = $this->getContactByEmail($contact->get('email'));

		// create a new contact if one does not exist
        if (empty($existingContact)) {
            $action = "Creating Contact";
            try {
	            $returnContact = $this->addContact(CTCT_ACCESS_TOKEN, $contact);
	            $action .= ' Succeeded';
			} catch(Exception $e) {
				$returnContact = false;
				$action .= ' Failed';
        		do_action('ctct_log_message', 'Creating Contact Exception', $e);
			}
        // update the existing contact if address already existed
        } else {

            $action = "Updating Contact";

            try {
            	// Update the contact details
            	$existingContact = $existingContact->update($data);

            	// Push the update to CTCT
            	$returnContact = $this->updateContact(CTCT_ACCESS_TOKEN, $existingContact);
            	$action .= ' Succeeded';
        	} catch(Exception $e) {
        		$returnContact = false;
        		$action .= ' Failed';
        		do_action('ctct_log_message', 'Updating Contact Exception', $e);
        	}
        }

        do_action('ctct_log_message', $action, $returnContact);
        do_action('ctct_debug', $action, $returnContact);

        return $returnContact;
	}

	/**
	 * Get all contacts
	 * @uses KWSConstantContact::getAll()
	 * @return array
	 */
	function getAllContacts() {
		return $this->getAll('Contacts');
	}

	/**
	 * Get all lists
	 * @uses KWSConstantContact::getAll()
	 * @return array
	 */
	function getAllLists() {
		return $this->getAll('Lists');
	}

	/**
	 * Get all contact sends
	 * @uses KWSConstantContact::getAll()
	 * @return array
	 */
	function getAllContactSends($id) {
		return $this->getAll('ContactSends', $id);
	}

	/**
	 * Get all email campaigns
	 * @uses KWSConstantContact::getAll()
	 * @return array
	 */
	function getAllEmailCampaigns($status = NULL) {
		return $this->getAll('EmailCampaigns', $status);
	}

	/**
	 * Function to fetch multi-page results. Works for all component types.
	 * @param  string $type         Component type name
	 * @param  int|string $id_or_status Either the ID of the component or the status filter. Depends on the component. Example: `$id_or_status = 'sent';` for `EmailCampaigns` component. Example: `$id_or_status = 13;` for `ContactSends` component.
	 * @param  string $param        Search filter. Sets the limit for requests.
	 * @param  array  $results      Pass the previous results for recursive calls to the method.
	 * @return array               Results array with `id` as key to each key/value pair.
	 */
	function getAll($type = '', $id_or_status = NULL, $param = NULL, &$results = array()) {

		do_action('ctct_debug', $type);
		add_filter('ctct_cache', function() { return 60 * 60 * 48; });

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
     * @param string $email - contact email address to search for
     * @param string $null - placeholder for PHP 5.4 Strict standards
     * @return KWSContact|boolean If contact, KWSContact object; if none, false
     */
    public function getContactByEmail($email, $null = null ) {
        $contact = $this->contactService->getContacts(CTCT_ACCESS_TOKEN, array('email' => $email));
        if(!empty($contact->results)) {
        	return new KWSContact($contact->results[0]);
        }
        return false;
    }


}