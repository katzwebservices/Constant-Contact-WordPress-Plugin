<?php

class KWS_V1API extends ConstantContact {

	public static $registrant_cache_age;
	public static $event_cache_age;

	public function __construct($authType, $apiKey, $username, $param){
		parent::__construct($authType, $apiKey, $username, $param);

		self::$event_cache_age = apply_filters('constant_contact_event_cache_age', apply_filters('constant_contact_cache_age', 60 * 60 * 6));
		self::$registrant_cache_age = apply_filters('constant_contact_registrant_cache_age', apply_filters('constant_contact_cache_age', 60 * 60 * 24 * 7));
	}

	function refreshCache($type = '') {
		return constant_contact_refresh_cache($type);
	}

	/**
	 * Get all details for an event
	 * @param  Event Event - Event object to get details for
	 * @return Event
	 */
	public function getEventDetails(Event $Event){

	    $EventsCollection = new EventsCollection($this->CTCTRequest);
	    $key = constant_contact_cache_key('Events', $Event);
	    $details = get_transient($key);
	    if( empty( $details ) || $this->refreshCache('events')) {
	    	$details = $EventsCollection->getEventDetails($this->CTCTRequest->baseUri.$Event->link);
	    	set_transient( $key, $details, self::$event_cache_age);
	    }

	    return $details;
	}

	/**
	 * Get detailed information on a Registrant
	 * @param Registrant $Registrant - Registrant Object
	 * @return Registrant
	 */
	public function getRegistrantDetails(Registrant $Registrant){
	    $EventsCollection = new EventsCollection($this->CTCTRequest);

	    $key = constant_contact_cache_key('Registrant', $Registrant);
	    $details = get_transient( $key );
		if(!$details || $this->refreshCache('registrant')) {
			$details = $EventsCollection->getRegistrantDetails($this->CTCTRequest->baseUri.$Registrant->link);
			set_transient( $key, $details, self::$registrant_cache_age);
		}

	    return $details;
	}

}