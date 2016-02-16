<?php
/**
 * @package CTCT
 * @version 3.0
 */

use Ctct\ConstantContact;
use Ctct\Util\Config;
use Ctct\Components\Contacts\Contact;
use GuzzleHttp\Exception\ClientException;
use Ctct\Components\EmailMarketing\Campaign;
use Ctct\Components\EventSpot\EventSpot;
use Ctct\Services\EventSpotService;
use Ctct\Components\ResultSet;
use Ctct\Exceptions\IllegalArgumentException;
use Ctct\Exceptions\CtctException;

final class KWSConstantContact extends ConstantContact {

	var $configured = false;

	static $instance;

	/**
	 * Handles interaction with Library management
	 * @var EventSpotService
	 */
	public $eventService;

	public function __construct($apiKey = null) {

		if(empty($apiKey)) { $apiKey = CTCT_APIKEY; }

		$this->eventService = new EventSpotService($apiKey);

		parent::__construct($apiKey);

	}

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
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'edd', 'constant-contact-api' ), '1.6' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'edd', 'constant-contact-api' ), '1.6' );
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

		return $this->configured;
	}

	function getContacts( $accessToken, Array $params = array() ) {
		return $this->contactService->getContacts( $accessToken, $params );
	}

	function getContact( $accessToken, $contactId ) {
		return $this->contactService->getContact( $accessToken, $contactId );
	}

	/**
	 * Update contact details for a specific contact
	 * @param string $accessToken - Constant Contact OAuth2 access token
	 * @param Contact $contact - Contact to be updated
	 * @param array $params - associative array of query parameters and values to append to the request.
	 *      Allowed parameters include:
	 *      action_by - Whether the contact is taking the action, or the account owner. Must be one of
	 *                  ACTION_BY_OWNER or ACTION_BY_VISITOR
	 * @return Contact
	 * @throws CtctException
	 */
	public function updateContact( $accessToken, Contact $contact, Array $params = array() ) {
		return $this->contactService->updateContact( $accessToken, $contact );
	}

	/**
	 * Get lists within an account
	 * @param $accessToken - Constant Contact OAuth2 access token
	 * @param array $params - associative array of query parameters and values to append to the request.
	 *      Allowed parameters include:
	 *      modified_since - ISO-8601 formatted timestamp.
	 * @return \Ctct\Components\Contacts\ContactList[]
	 * @throws CtctException
	 */
	public function getLists( $accessToken, Array $params = array() ) {
		return $this->listService->getLists( $accessToken, $params );
	}

	/**
	 * Get an individual contact list
	 * @param $accessToken - Constant Contact OAuth2 access token
	 * @param $listId - list id
	 * @return \Ctct\Components\Contacts\ContactList
	 * @throws CtctException
	 */
	public function getList( $accessToken, $listId ) {
		return $this->listService->getList( $accessToken, $listId );
	}

	/**
	 * Update a Contact List
	 * @param string $accessToken - Constant Contact OAuth2 access token
	 * @param \Ctct\Components\Contacts\ContactList $list - ContactList to be updated
	 * @return \Ctct\Components\Contacts\ContactList
	 * @throws CtctException
	 */
	public function updateList( $accessToken, $list ) {
		return $this->listService->updateList( $accessToken, $list );
	}

	/**
	 * Get a set of campaigns
	 * @param string $accessToken - Constant Contact OAuth2 access token
	 * @param array $params - associative array of query parameters and values to append to the request.
	 *      Allowed parameters include:
	 *      limit - Specifies the number of results displayed per page of output, from 1 - 500, default = 50.
	 *      modified_since - ISO-8601 formatted timestamp.
	 *      next - the next link returned from a previous paginated call. May only be used by itself.
	 * @return ResultSet
	 * @throws CtctException
	 */
	public function getEmailCampaigns( $accessToken, Array $params = array() ) {
		return $this->emailMarketingService->getCampaigns( $accessToken, $params );
	}

	/**
	 * Get campaign details for a specific campaign
	 * @param string $accessToken - Constant Contact OAuth2 access token
	 * @param int $campaignId - Valid campaign id
	 * @return Campaign
	 * @throws CtctException
	 */
	public function getEmailCampaign( $accessToken, $campaignId ) {
		return $this->emailMarketingService->getCampaign( $accessToken, $campaignId );
	}

	/**
	 * @param string $accessToken
	 * @param array $params
	 *
	 * @return ResultSet
	 * @throws CtctException
	 */
	public function getEvents( $accessToken, Array $params = array() ) {
		return $this->eventService->getEvents( $accessToken, $params );
	}

	public function getEvent( $accessToken, $eventId ) {
		return $this->eventService->getEvent( $accessToken, $eventId );
	}

	public function updateEvent( $accessToken, EventSpot $event ) {
		return $this->eventService->updateEvent( $accessToken, $event );
	}

	public function getEventRegistrant( $accessToken, $eventId, $registrantId ) {
		return $this->eventService->getRegistrant( $accessToken, $eventId, $registrantId );
	}

	public function getEventRegistrants( $accessToken, $eventId, Array $params = array() ) {
		$id = $eventId;
		if( is_array( $eventId ) ) {
			$id = $eventId['id'];
			$params = $eventId;
		}

		unset( $params['id'] );

		return $this->eventService->getRegistrants( $accessToken, $id, $params );
	}

	/**
	 * Add a contact if it doesn't exist and update it if it does.
	 * @param array|KWSContact $data Array of contact data or an existing KWSContact or Contact object.
	 * @return  boolean|KWSContact Returns false if failed, otherwise returns a contact object.
	 */
	public function addUpdateContact($data) {

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
            		$returnContact = new WP_Error('optout', __('You have opted out of our newsletters and cannot re-subscribe.', 'constant-contact-api') );

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
			return new WP_Error('nolists', __('A contact cannot be added without lists.', 'constant-contact-api') );
		}

		return $this->contactService->addContact($accessToken, $contact, $params);
	}

	/**
	 * Get all contacts
	 * @uses KWSConstantContact::getAll()
	 * @return array
	 */
	function getAllContacts( $params = null ) {
		return $this->getAll('Contacts', $params );
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
	function getAllEmailCampaigns( $status = NULL ) {
		$status = is_null( $status ) ? array() : array( 'status' => esc_attr( $status ) );
		$campaigns = $this->getAll('EmailCampaigns', $status);
		return $campaigns;
	}

	/**
	 * Get all email campaigns
	 * @uses KWSConstantContact::getAll()
	 * @return array
	 */
	function getAllEventRegistrants($id = NULL) {
		$params = empty($id) ? array() : array('id' => $id);
		return $this->getAll('EventRegistrants', $params );
	}


	/**
	 * Function to fetch multi-page results. Works for all component types.
	 *
	 * @param  string $type          Component type name
	 * @param  array $passed_params Search filter. Sets the limit for requests.
	 * @param  array  $results       Pass the previous results for recursive calls to the method.
	 *
	 * @return array               Results array with `id` as key to each key/value pair.
	 */
	function getAll($type = '', $passed_params = array(), &$results = array()) {

		$params = $passed_params;

		/** @var boolean $returns_array Does the object type return an array of results or a ResultSet? */
		$returns_array = false;

		if( empty( $params['next'] ) ) {

			switch ( $type ) {
				// Lists doesn't support limit param
				case "Lists":
					$max_limit = false;
					$returns_array = true;
					break;
				// email campaigns have special 50 limit; others have 500
				case "EmailCampaigns":
					$max_limit = 50;
					break;
				default:
					$max_limit = 500;
			}

			/**
			 * Only set the limit if next isn't defined
			 */
			if ( $max_limit ) {
				$params['limit'] = $max_limit;
			}
		} else {
			// If next is defined, get rid of all other parameters
			$params = array( 'next' => $params['next'] );

			if( isset( $passed_params['id'] )  ) {
				$params['id'] = $passed_params['id'];
			}
		}

		$cache = true;
		$type_lower = strtolower( $type );
		$cache_key = substr( sprintf( 'ctct_%s_%s', $type_lower, sha1( implode( '', $params ) ) ), 0, 44 );
		$cache_time = apply_filters('constant_contact_cache_age', 60 * 60 * 6, NULL, $params );

		if( constant_contact_refresh_cache( $type ) ) {
			$cache = false;
		}

		$cache = apply_filters('ctct_cache', $cache );

		$fetch = false;
		if( ! $cache ) {
			delete_transient( $cache_key );
		} elseif( $cache && $cache_time ) {
			$fetch = get_transient( $cache_key );
		}

		if( !$fetch ) {

			ob_start();
			$fetch = $this->{"get{$type}"}( CTCT_ACCESS_TOKEN, $params );
			$errors = ob_get_clean();

			echo $errors;

			if( $cache_time ) {
				set_transient( $cache_key, $fetch, $cache_time );
			}
		}

		if( $returns_array ) {

			$results = $fetch;

		} else {

			// Append the result to the existing results array
			foreach ( $fetch->results as $r ) {
				if( is_null( $r ) ) { continue; } // Something went wrong creating the object
				$results[ $r->id ] = $r;
			}

			// If there is a next link set and the next link is not the current page (no next link, actually),
			// recursively call the function.
			if ( ! empty( $fetch->next ) && ( empty( $params['next'] ) || $fetch->next !== $params['next'] ) ) {
				$params['next'] = $fetch->next;

				$this->getAll( $type, $params, $results );
			}
		}

		return $results;
	}

	/**
	 * We override the default so we can pass arrays as well as strings.
	 * @see Ctct\ConstantContact::determineParam
	 * @param string $param
	 * @return array
	 */
	private function determineParam( $param ) {

		if( is_array( $param ) ) {
			// If it's an array, we need to convert it to a string because of
			$param = '?'.http_build_query( $param, '', '&' );
		}

		/**
		 * Below is just an alias of parent
		 * @see Ctct\ConstantContact::determineParam
		 */
		$params = array();
		if (substr($param, 0, 1) === '?') {
			$param = substr($param, 1);
			parse_str($param, $params);
		} else {
			$params['limit'] = $param;
		}

		return $params;
	}

    /**
     * Get contacts with a specified email eaddress
     * @param string $email - contact email address to search for
     * @param string $null - placeholder for PHP 5.4 Strict standards to be compatible with the overridden getContactByEmail method
     * @return KWSContact|false If contact, KWSContact object; if none, false
     */
    public function getContactByEmail($email, $null = null ) {
		$contact = $this->contactService->getContacts(CTCT_ACCESS_TOKEN, array('email' => $email));

        if(!empty($contact->results)) {
        	return new KWSContact($contact->results[0]);
        }
        return false;
    }

	/**
	 * Get all contacts from an individual list
	 * @param string $accessToken - Constant Contact OAuth2 access token
	 * @param string $listId - {@link ContactList} id to retrieve contacts for
	 * @param array $params - associative array of query parameters and values to append to the request.
	 *      Allowed parameters include:
	 *      limit - Specifies the number of results displayed per page of output, from 1 - 500, default = 50.
	 *      modified_since - ISO-8601 formatted timestamp.
	 *      next - the next link returned from a previous paginated call. May only be used by itself.
	 *      email - full email address string to restrict results by
	 *      status - a contact status to filter results by. Must be one of ACTIVE, OPTOUT, REMOVED, UNCONFIRMED.
	 * @return ResultSet
	 * @throws CtctException
	 */
	public function getContactsFromList($accessToken, $list, $param = null)
	{

		/**
		 * When using getAll, the `id` is passed in the second argument along with the `next` param
		 * @see getAll()
		 */
		if( is_null( $param ) && is_array( $list ) && isset( $list['id'] ) ) {
			$listId = $list['id'];
			unset( $list['id'] );
			$param = $list;
		} else{
			$listId = $this->getArgumentId($list, 'ContactList');
		}

		$param = $this->determineParam($param);
		return $this->contactService->getContactsFromList($accessToken, $listId, $param);
	}

	/**
	 * Get the id of object, or attempt to convert the argument to an int
	 * @param mixed $item - object or a numeric value
	 * @param string $className - class name to test the given object against
	 * @throws Ctct\Exceptions\IllegalArgumentException - if the item is not an instance of the class name given, or cannot be
	 * converted to a numeric value
	 * @return int
	 */
	private function getArgumentId($item, $className)
	{
		$id = null;
		if (is_numeric($item)) {
			$id = $item;
		} elseif (join('', array_slice(explode('\\', get_class($item)), -1)) == $className) {
			$id = $item->id;
		} else {
			throw new IllegalArgumentException(sprintf(Config::get('errors.id_or_object'), $className));
		}

		return $id;
	}
}