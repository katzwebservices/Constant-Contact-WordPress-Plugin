<?php
use Ctct\Exceptions\CtctException;
use Ctct\Components\EmailMarketing\Campaign;
use Ctct\Components\Component;
use Ctct\Util\Config;
use Ctct\Components\EmailMarketing\MessageFooter;
use Ctct\Components\Tracking\TrackingSummary;
use Ctct\Components\EmailMarketing\ClickThroughDetails;
use Ctct\Components\Contacts\ContactList;
use Ctct\Exceptions\IllegalArgumentException;

class KWSCampaign extends Campaign {

	private static $read_only = array('created_date', 'last_run_date', 'next_run_date', 'last_sent_date', 'permalink_url', 'tracking_summary', 'click_through_details', 'status', 'modified_date', 'id', 'confirmed', 'source', 'source_details', 'opt_out_date', 'opt_in_date', 'confirm_status', 'sent_to_contact_lists');

	function __construct($Campaign = '') {

		if(is_array($Campaign)) {
			$Campaign = $this->prepare($Campaign, true);
		}

		if(!empty($Campaign) && (is_array($Campaign) || $Campaign instanceof Campaign)) {
			foreach($Campaign as $k => &$v) {
				$this->{$k} = $v;
			}
		}
	}

	/**
     * Factory method to create a Contact object from an array
     * @param array $props - Associative array of initial properties to set
     * @return Contact
     */
    public static function create(array $props)
    {
    	$Campaign = new KWSCampaign($props);

        return $Campaign;
    }

    public function update(array $new_contact_array) {

    	$existing_contact = wp_clone($this);

    	$new_contact = new KWSCampaign($new_contact_array, true);

    	unset($new_contact->id, $new_contact->status, $new_contact->source, $new_contact->source_details);

    	foreach($new_contact as $k => $v) {
    		$existing_contact->{$k} = $v;
    	}

    	return $existing_contact;
    }

    private function prepareAddress(array $address) {
    	return wp_parse_args($address, array('line1' => '', 'line2' => '', 'line3' => '', 'city' => '', 'address_type' => 'PERSONAL', 'state_code' => '', 'country_code' => '', 'postal_code' => '', 'sub_postal_code' => ''));
    }

    /**
     *
     * @link http://dotcms.constantcontact.com/docs/email-campaigns/email-campaigns-collection.html?method=POST
     * @param  [type] $message_footer_array [description]
     * @return [type]                       [description]
     */
    private function prepareMessageFooter($message_footer_array) {
    	$defaults = array("city" => '', "state" => '', "country" => '', "organization_name" => '', "address_line_1" => '', "address_line_2" => '', "address_line_3" => '', "international_state" => '', "postal_code" => '', "include_forward_email" => false, "forward_email_link_text" => '', "include_subscribe_link" => true, "subscribe_link_text" => '');
		$message_footer = wp_parse_args( $message_footer_array, $defaults );
		$message_footer['country'] = strtoupper($message_footer['country']);
		return $message_footer;
    }

	private function prepare(array $campaign_array, $add = false) {

		$defaults = array(
			'id' => NULL,
			'name' => NULL,
			'subject' => NULL,
			'from_name' => NULL,
			'from_email' => NULL,
			'reply_to_email' => NULL,
			'template_type' => NULL,
			'created_date' => NULL,
			'modified_date' => NULL,
			'last_run_date' => NULL,
			'next_run_date' => NULL,
			'status' => NULL,
			'is_permission_reminder_enabled' => NULL,
			'permission_reminder_text' => NULL,
			'is_view_as_webpage_enabled' => NULL,
			'view_as_web_page_text' => NULL,
			'view_as_web_page_link_text' => NULL,
			'greeting_salutations' => NULL,
			'greeting_name' => NULL,
			'greeting_string' => NULL,
			'message_footer' => NULL,
			'tracking_summary' => NULL,
			'email_content' => NULL,
			'email_content_format' => NULL,
			'style_sheet' => NULL,
			'text_content' => NULL,
			'sent_to_contact_lists' => array(),
	    	'click_through_details' => array(),
		);

        $Campaign = wp_parse_args( $campaign_array, $defaults );

        if (array_key_exists("message_footer", $Campaign)) {
            $Campaign['message_footer'] = MessageFooter::create($this->prepareMessageFooter($Campaign['message_footer']));
        }

        $Campaign['greeting_name'] = strtoupper($Campaign['greeting_name']);
        if(!in_array($Campaign['greeting_name'], array('FIRST_NAME', 'LAST_NAME', 'FIRST_AND_LAST_NAME', 'NONE'))) {
        	$Campaign['greeting_name'] = 'NONE';
        }

		return $Campaign;
	}

	function getLabel($key) {

		switch($key) {
			case 'id':
				return 'ID';
				break;
			case 'email_addresses':
				return 'Email Address';
				break;
		}

		$key = ucwords(preg_replace('/\_/ism', ' ', $key));
	    $key = preg_replace('/Addr([0-9])/', __('Address $1', 'ctct'), $key);
	    $key = preg_replace('/Field([0-9])/', __('Field $1', 'ctct'), $key);

		return $key;
	}

	function get($key, $format = false) {
		switch($key) {
			case 'created_date':
			case 'next_run_date':
			case 'modified_date':
			case 'last_run_date':
				$date = date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($this->{$key}), true);

				return $format ? $date : $this->{$key};
				break;
			case 'status':
				return $format ? ucfirst(strtolower($this->{$key})) : $this->{$key};
				break;
			default:
				if(isset($this->{$key})) {
					return $this->{$key};
				} else {
					return '';
				}
				break;
		}
	}

	function editable($key) {
	    return !in_array($key, $this::$read_only);
	}

	function set($key, $value) {
	    if(!$this->editable($key)) {
	    	throw new CtctException('Cannot set value for key '.$key.'; this field is not editable.');
	    }
	    switch($key) {
	        case 'created_date':
	        case 'status':
	        case 'modified_date':
	        case 'id':
	        case 'confirmed':
	        case 'source':
	        case 'source_details':
	        case 'opt_out_date':
	        case 'opt_in_date':
	        case 'confirm_status':
	            return false;
	            break;
	        case 'email_addresses':
	            $this->email_addresses[0]->email_address = $value;
	            break;
	        default:
	            $this->{$key} = $value;
	            break;
	    }
	    return true;
	}
}