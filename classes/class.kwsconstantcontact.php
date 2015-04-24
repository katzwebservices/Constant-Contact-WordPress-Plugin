<?php

use Ctct\ConstantContact;
use Ctct\Util\Config;
use Ctct\Components\Contacts\Contact;
use Ctct\Services\ListService;

final class KWSConstantContact extends ConstantContact {

	var $configured = false;

	static $instance;

	private $cache_services = array(
		'contacts' => 'contactService',
		'campaigns' => 'emailMarketingService',
		'activities' => 'activityService',
		'lists' => 'listService'
	);

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'edd' ), '1.6' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'edd' ), '1.6' );
	}

	public function __construct($apiKey = null) {

		if(empty($apiKey)) { $apiKey = CTCT_APIKEY; }

		parent::__construct($apiKey);

		// Use our own REST client.
		$this->setRestClient( new KWSRestClient() );
		$this->setUrls();
		$this->setHooks();

		self::$instance = &$this;
	}

	/**
	 * Get access to instance or create one
	 * @return KWSConstantContact
	 */
	static function getInstance() {

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
			do_action('ctct_debug', 'isConfigured: getContactByEmail succeeded. Adding configured option.' );
			update_site_option( 'ctct_configured', 1 );
		} catch(Exception $e) {
			$this->configured = 0;
			do_action('ctct_debug', 'isConfigured: getContactByEmail failed. Deleting configured option.' );
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
	 * @return  boolean|KWSContact Returns false if failed, otherwise returns a contact object.
	 */
	function addUpdateContact($data) {

		$contact = new KWSContact($data);

		// check to see if a contact with the email addess already exists
		$existingContact = $this->getContactByEmail($contact->get('email'));

		// create a new contact if one does not exist
        if (empty($existingContact)) {
            $action = "Creating Contact";
            try {
	            $returnContact = $this->addContact(CTCT_ACCESS_TOKEN, $contact, true);

	            if( is_wp_error( $returnContact ) ) {
		            $action .= ' Failed';
		            /** @var WP_Error $returnContact */
		            do_action('ctct_error', 'Creating Contact Exception', $returnContact->get_error_message() );
	            } else {
		            $action .= ' Succeeded';
	            }

			} catch(Exception $e) {
				$returnContact = false;
				$action .= ' Failed';
        		do_action('ctct_error', 'Creating Contact Exception', $e->getMessage() );
			}
        // update the existing contact if address already existed
        } else {

            $action = "Updating Contact";

            try {

            	if( $existingContact->get('status') === 'OPTOUT' ) {

            		$action .= ' Failed';
            		do_action('ctct_error', 'The contact has opted out; cannot add or update.', $existingContact );
            		$returnContact = new WP_Error('optout', __('You have opted out of our newsletters and cannot re-subscribe.') );

            	} else {

					// Update the contact details
					$modifiedContact = $existingContact->update($data);

					$returnContact = $this->updateContact(CTCT_ACCESS_TOKEN, $modifiedContact);
					$action .= ' Succeeded';

					unset( $modifiedContact );
            	}

        	} catch(Exception $e) {
        		$returnContact = false;
        		$action .= ' Failed';
        		do_action('ctct_error', 'Updating Contact Exception', $e);
        	}
        }

        do_action('ctct_activity', $action, $returnContact );

        return $returnContact;
	}

	/**
	 * Add a new contact to an account
	 *
	 * Clone of the addContact, but it unsets the readOnly items.
	 *
	 * @param string $accessToken - Valid access token
	 * @param Contact $contact - Contact to add
	 * @param boolean $actionByVisitor - is the action being taken by the visitor
	 * @return Contact
	 */
	public function addContact($accessToken, Contact $contact, $actionByVisitor = false)
	{
	    $params = array();
	    if ($actionByVisitor == true) {
	        $params['action_by'] = "ACTION_BY_VISITOR";
	    }

	    // If you have set an `id` or `status`, it
	    // makes everything screwy.
	    foreach(KWSContact::getReadOnly() as $key) {
	    	unset($contact->{$key});
	    }

		if( empty( $contact->lists ) ) {
			return new WP_Error('nolists', __('A contact cannot be added without lists.') );
		}

		return $this->contactService->addContact($accessToken, $contact, $params);
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
		$status = empty($status) ? array() : array('status' => $status);
		return $this->getAll('EmailCampaigns', NULL, $status);
	}

	/**
	 * Get all email campaigns
	 * @uses KWSConstantContact::getAll()
	 * @return array
	 */
	function getAllEvents($status = NULL) {
		$status = empty($status) ? array() : array('status' => $status);
		return $this->getAll('EmailCampaigns', NULL, $status);
	}


	/**
	 * Function to fetch multi-page results. Works for all component types.
	 * @param  string $type         Component type name
	 * @param  int|string $id_or_status Either the ID of the component or the status filter. Depends on the component. Example: `$id_or_status = 'sent';` for `EmailCampaigns` component. Example: `$id_or_status = 13;` for `ContactSends` component.
	 * @param  string $param        Search filter. Sets the limit for requests.
	 * @param  array  $results      Pass the previous results for recursive calls to the method.
	 * @return array               Results array with `id` as key to each key/value pair.
	 */
	function getAll($type = '', $id_or_status = NULL, $param = array(), &$results = array()) {

		add_filter('ctct_cache', function() { return 60 * 60 * 48; });

		switch($type) {
			case "Lists":
				// Lists doesn't support limit param
				$fetch = $this->{"get{$type}"}(CTCT_ACCESS_TOKEN, (array)$param);
				break;
			case "EmailCampaigns":
			default:

				// email campaigns have special 50 limit; others have 500
				$max_limit = ($type === 'EmailCampaigns') ? 50 : 500;


				if( is_null( $param ) ) {
					$param = $max_limit;
				}


				if( !is_null( $id_or_status ) ) {

					$fetch = $this->{"get{$type}"}(CTCT_ACCESS_TOKEN, $id_or_status, $param);

				} else {

					if( empty( $param ) ) {
						$param = array( 'limit' => $max_limit );
					}

					$fetch = $this->{"get{$type}"}(CTCT_ACCESS_TOKEN, $param);
				}
		}

		// If this returns the results directly, not as an object.
		if(is_array($fetch)) { return $fetch; }

		foreach($fetch->results as $r) { $results[$r->id] = $r;  }

		// If there is a next link set and the next link
		// is not the current page (no next link, actually),
		// recursively call the function.
		if(!empty($fetch->next) && $fetch->next != @$param['next']) {
    		$this->getAll($type, $id_or_status, array('next' => $fetch->next), $results);
    	}

    	return $results;
	}

	/**
	 * We override the default so we can pass arrays as well as strings.
	 * @param string $param
	 * @return array
	 */
	private function determineParam( $param ) {

		if( is_array( $param ) ) {
			// If it's an array, we need to convert it to a string because of
			$param = '?'.http_build_query( $param, '', '&' );
		}

		return parent::determineParam( $param);
	}

    /**
     * Get contacts with a specified email eaddress
     * @param string $email - contact email address to search for
     * @param string $null - placeholder for PHP 5.4 Strict standards to be compatible with the overridden getContactByEmail method
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