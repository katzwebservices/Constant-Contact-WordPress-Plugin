<?php

use Ctct\ConstantContact;
use Ctct\Components\Contacts\Contact;
use Ctct\Components\Contacts\Address;
use Ctct\Components\Contacts\CustomField;
use Ctct\Components\Contacts\Note;
use Ctct\Components\Contacts\ContactList;
use Ctct\Components\Contacts\EmailAddress;
use Ctct\Exceptions\CtctException;

class KWSContact extends Contact {

    /**
     * The items of the Contact object that aren't writeable.
     *
     * @todo  remove Notes when avaiable again.
     * @var array
     */
    private static $read_only = array('created_date', 'status', 'modified_date', 'id', 'confirmed', 'source', 'source_details', 'opt_out_date', 'opt_in_date', 'confirm_status', 'notes');

	function __construct($Contact = '') {

		if(is_array($Contact)) {
			$Contact = $this->prepare($Contact, true);
		}

        if(is_wp_error($Contact)) {
            return $Contact;
        }

		if(!empty($Contact) && (is_array($Contact) || $Contact instanceof Contact)) {
			foreach($Contact as $k => &$v) {
                $this->{$k} = self::prepareValue($v);
			}
		}
	}

    public static function getReadOnly() {
        return self::$read_only;
    }

    private static function prepareValue($value) {
        if(empty($value) || is_numeric($value)) {
            return $value;
        } else if(is_string($value)) {
            $value = stripslashes($value);
        } else {
            if(!empty($value)) {

            foreach($value as $k => $v) {
                if(is_array($value)) {
                    $value[$k] = self::prepareValue($v);
                } else {
                    $value->{$k} = self::prepareValue($v);
                }
            }}
        }
        return $value;
    }

	/**
     * Factory method to create a Contact object from an array
     * @param array $props - Associative array of initial properties to set
     * @return Contact
     */
    public static function create(array $props)
    {
        $contact = new KWSContact($props);

        return $contact;
    }

    public function update($new_contact_array) {

    	$existing_contact = wp_clone($this);

    	$new_contact = new KWSContact($new_contact_array, true);

        foreach(self::$read_only as $key) {
            unset($new_contact->{$key});
        }

    	foreach($new_contact as $k => $v) {
    		$existing_contact->{$k} = $v;
    	}

    	return $existing_contact;
    }

    private function prepareAddress(array $address) {
    	return wp_parse_args($address, array('line1' => '', 'line2' => '', 'line3' => '', 'city' => '', 'address_type' => '', 'state_code' => '', 'state_name' => '', 'country_code' => '', 'country_name' => '', 'postal_code' => '', 'sub_postal_code' => ''));
    }

    /**
     * Split a full name into pieces. Used for comment form submissions.
     * @param  array $contact_array Array of contact data
     * @return array                Array with a better name structure
     */
    private function prepareName($contact_array, $key = 'name') {
        if(!function_exists('ctct_parse_name')) {
            include_once(CTCT_DIR_PATH.'lib/nameparse.php');
        }

        if(!isset($contact_array[$key])) { return $contact_array; }

        $name = ctct_parse_name($contact_array[$key]);

        if(isset($name['first']) && empty($contact_array['first_name'])) {
            $contact_array['first_name'] = $name['first'];
        }

        if(isset($name['middle']) && empty($contact_array['middle_name'])) { $contact_array['middle_name'] = $name['middle']; }

        if(isset($name['last']) && empty($contact_array['last_name'])) { $contact_array['last_name'] = $name['last']; }

        return $contact_array;
    }

    /**
     * Clean and parse an array of contact details to be in CTCT format
     *
     * The method filters out any data not allowed by Constant Contact, so you don't
     * need to worry about having just the right details.
     *
     * It converts `email`, `email_address` and `user_email` into the
     * proper `email_addresses` array so you don't have to.
     *
     * @param  array   $contact_array Contact information.
     * @param  boolean $add           [description]
     * @return [type]                 [description]
     */
	private function prepare(array $contact_array, $add = false) {

        $defaults = array(
            'id' => NULL,
            'status' => NULL,
            'name' => NULL,
            'first_name' => NULL,
            'middle_name' => NULL,
            'last_name' => NULL,
            'source' => NULL,
            'email_addresses' => array(),
                'email' => NULL,
            	'email_address' => NULL,
                'user_email' => NULL,
            'prefix_name' => NULL,
            'job_title' => NULL,
            'addresses' => array(),
                'address' => NULL,
                'address_line1' => NULL,
                'address_line2' => NULL,
                'address_line3' => NULL,
                'address_city' => NULL,
                'address_state_code' => NULL,
                'address_state_name' => NULL,
                'address_country_code' => NULL,
                'address_postal_code' => NULL,
                'address_sub_postal_code' => NULL,
            'notes' => array(),
            'company_name' => NULL,
            'home_phone' => NULL,
            'work_phone' => NULL,
            'cell_phone' => NULL,
            'fax' => NULL,
            'custom_fields' => array(),
            'lists' => array(),
            'source_details' => CTCT_APIKEY,
        );

        $Contact = wp_parse_args( $contact_array, $defaults );

       # r($contact_array);
       # r($Contact);

        foreach($Contact as $k => $v) {

            /** Only allow permitted data in the Contact array */
            if(!array_key_exists($k, $defaults)) {

                // @todo: Add logging notice.
                unset($Contact[$k]);
            }

            switch($k) {
                case 'user_email':
                    if(!empty($v)) {
                        $Contact['email'] = $Contact['user_email'];
                    }
                    unset($Contact['user_email']);
                    break;
                case 'name':
                    $Contact = $this->prepareName($Contact);
                    unset($Contact['name']);
                    break;
                case 'CustomField1':
                case 'CustomField2':
                case 'CustomField3':
                case 'CustomField4':
                case 'CustomField5':
                case 'CustomField6':
                case 'CustomField7':
                case 'CustomField8':
                case 'CustomField9':
                case 'CustomField10':
                case 'CustomField11':
                case 'CustomField12':
                case 'CustomField13':
                case 'CustomField14':
                case 'CustomField15':
                    $Contact['custom_fields'][] = array('name' => $k, 'value' => $v);
                    unset($Contact[$k]);
                    break;
                case 'address_line1':
                case 'address_line2':
                case 'address_line3':
                case 'address_city':
                case 'address_state_code':
                case 'address_state_name':
                case 'address_country_code':
                case 'address_postal_code':
                case 'address_sub_postal_code':
                    $Contact['address'][str_replace('address_', '', $k)] = $v;
                    unset($Contact[$k]);
                    break;
            }
        }

        if(!empty($Contact['email_address']) && empty($Contact['email_addresses'])) {
        	$Contact['email_addresses'] = array($Contact['email_address']);
        }
        unset($Contact['email_address']);

        if(!empty($Contact['email']) && empty($Contact['email_addresses'])) {
            $Contact['email_addresses'] = array($Contact['email']);
        }
        unset($Contact['email']);

        $Contact['email_addresses'] = (array)$Contact['email_addresses'];
        if(empty($Contact['email_addresses'])) {
            return new WP_Error('no_email_address', 'There was no email defined.', $Contact);
        }

        foreach($Contact['email_addresses'] as &$email_address) {

        	$email_address = EmailAddress::create( array('email_address' => $email_address) );

        }

        if(!empty($Contact['address']) && empty($Contact['addresses'])) {
        	$Contact['address'] = $this->prepareAddress($Contact['address']);
        	$Contact['addresses'] = array($Contact['address']);
        }
        unset($Contact['address']);


        if(!empty($Contact['addresses'])) {
            $Contact['addresses'] = (array)$Contact['addresses'];
            foreach($Contact['addresses'] as $key => &$address) {

                $check_address = $address;
                foreach($check_address as $k => $v) {
                    if(empty($v)) {
                        unset($check_address[$k]);
                    }
                }

                if(empty($check_address)) {
                    unset($address, $Contact['addresses'][$key]);
                    continue;
                }

                $address['address_type'] = strtoupper($address['address_type']);

            	if(!in_array($address['address_type'], array('PERSONAL', 'BUSINESS', 'UNKNOWN'))) {
            		$address['address_type'] = 'PERSONAL';
            	}

                $address = Address::create($this->prepareAddress((array)$address));
            }

            if(empty($Contact['addresses'])) {
                unset($Contact['addresses']);
            }
    	}

        if(isset($Contact['notes'])) {

            unset($Contact['notes']);

            /*// CTCT got rid of updating notes for now.
            $Contact['notes'] = array( $Contact['notes'] );
            foreach( $Contact['notes'] as &$note) {
                $note = is_array($note) ? $note : array('note' => $note);
                $note = Note::create($note);
            }*/

        }

        if(isset($Contact['custom_fields'])) {
            $Contact['custom_fields'] = (array)$Contact['custom_fields'];
            foreach ($Contact['custom_fields'] as &$custom_field) {
                $custom_field = CustomField::create($custom_field);
            }
        }

        if(isset($Contact['lists'])) {
            $Contact['lists'] = (array)$Contact['lists'];
            foreach ($Contact['lists'] as &$contact_list) {
            	if(is_numeric( $contact_list )) {
            		$contact_list = array('id' => (string)$contact_list);
            	}
                $contact_list = ContactList::create($contact_list);
            }
        }

        if($add || !in_array($Contact['status'], array('ACTIVE', 'UNCONFIRMED', 'OPTOUT', 'REMOVED', 'NON_SUBSCRIBER', 'VISITOR'))) {
        	unset($Contact['status']);
        }

        if($add) {
        	unset($Contact['id'], $Contact['confirmed']);
        }

        return $Contact;
	}

    /**
     * Add a note Contact object
     * @param Note $note - note to add to the contact
     */
    public function addNote($note) {

    	if(is_string($note)) {
    		$note = array('note' => $note);
    	}

    	if (! $note instanceof EmailAddress) {
            $note = Note::create($note);
        }

        $this->notes[0] = $note;
    }

	function getLabel($key) {

		switch($key) {
			case 'id':
				return 'ID';
				break;
			case 'email_addresses':
				return 'Email Address';
				break;
            case 'line1': return 'Address'; break;
            case 'line2': return 'Address Line 2'; break;
            case 'line3': return 'Address Line 3'; break;
		}

		$key = ucwords(preg_replace('/\_/ism', ' ', $key));
	    $key = preg_replace('/Addr([0-9])/', __('Address $1', 'ctct'), $key);
	    $key = preg_replace('/Field([0-9])/', __('Field $1', 'ctct'), $key);

		return $key;
	}

    function is_editable($key, $check_status = true) {
        return !in_array($key, $this::$read_only) && (!$check_status || ($check_status && $this->status !== 'NON_SUBSCRIBER'));
    }

    function set($key, $value) {

        if(!$this->is_editable(strtolower($key), false)) { return false; }

        switch(strtolower($key)) {
            case 'email_addresses':
                $this->email_addresses[0]->email_address = $value;
                break;
            case 'notes':
                // Ctct got rid of notes for now.
                if(isset($this->notes[0]) && is_object($this->notes[0])) {
                    $this->notes[0]->note = $value;
                } else {
                    $this->addNote($value);
                }
                break;

            case (preg_match('/^personal_/ism', $key) ? true : false):
                $key = strtolower(str_ireplace('personal_', '', $key));
                $this->addresses[0]->{$key} = $value;
                break;
            case (preg_match('/^business_/ism', $key) ? true : false):
                $key = strtolower(str_ireplace('business_', '', $key));
                $this->addresses[1]->{$key} = $value;
                break;
            case (preg_match('/^customfield([0-9]+)/ism', $key, $matches) ? true : false):
                // First, check whether it already exists.
                foreach((array)$this->custom_fields as $customfield) {
                    if(strtolower($customfield->name) === strtolower($key)) {
                        $customfield->value = $value;
                        return true;
                    }
                }

                $this->custom_fields = (array)$this->custom_fields;

                // Otherwise, create it.
                $this->custom_fields[] = CustomField::create(array(
                    'name' => 'CustomField'.$matches[1],
                    'value' => $value
                ));
                break;
            default:
                $this->{$key} = $value;
                break;
        }
        return true;
    }
	function get($key, $format = NULL) {

        if(!$this->is_editable($key)) { $format = NULL; }

        switch(strtolower($key)) {
			case 'status':
            	return $format ? ucfirst(strtolower($this->{$key})) : $this->{$key};
				break;
			case 'opt_in_date':
			case 'opt_out_date':
            case 'created_date':
            case 'modified_date':

				if(empty($this->{$key})) { return false; }

                $date = date_i18n( get_option('date_format'), strtotime($this->{$key}), true);

                // If boolean is passed instead of null, show the raw time.
				return !empty( $format ) || is_null( $format ) ? $date : $this->{$key};
				break;
			case 'email_addresses':
			case 'email_address':
            case 'email':
				return $this->getEmail();
				break;
			case 'full_name':
				return $format ? '<span data-name="prefix_name" class="editable" data-id="'.$this->get('id').'">'.$this->get('prefix_name') .'</span> <span data-name="first_name" class="editable" data-id="'.$this->get('id').'">'.$this->get('first_name') .'</span> <span data-name="middle_name" class="editable" data-id="middle_name">'. $this->get('middle_name') .'</span> <span data-name="last_name" class="editable" data-id="'.$this->get('id').'">'.$this->get('last_name').'</span>' : $this->get('first_name') .' ' . $this->get('middle_name') .' '.$this->get('last_name');
				break;
			case 'name':
                    return $format ? '<span data-name="first_name" class="editable" data-id="'.$this->get('id').'">'.$this->get('first_name') .'</span> <span data-name="last_name" class="editable" data-id="'.$this->get('id').'">'.$this->get('last_name').'</span>' : $this->get('first_name') .' ' .$this->get('last_name');
				break;
            case 'notes':
                    if(empty($this->{$key})) { return ''; }
                    $notes = is_array($this->{$key}) ? $this->{$key}[0]->note : $this->{$key};
                    return $format ? '<span data-name="notes" class="editable" data-id="'.$this->get('id').'">'.$notes.'</span>' : $notes;
                break;
            case 'address':
            case 'personal_address':
            case 'home_address':
                return $this->getAddress('personal', $format);
                break;
            case 'business_address':
                return $this->getAddress('business', $format);
                break;

            /**
             * If an item has a parent, the parent is added as a prefix using `{parent}_` syntax.
             *
             * The following cases match personal addresses and business addresses in the
             * format of `personal_line1`, `business_city`, etc.
             *
             */
            case (preg_match('/^personal_/ism', $key) ? true : false):
                $key = strtolower(str_ireplace('personal_', '', $key));
                return isset($this->addresses[0]) ? $this->addresses[0]->{$key} : '';
                break;
            case (preg_match('/^business_/ism', $key) ? true : false):
                $key = strtolower(str_ireplace('business_', '', $key));
                return isset($this->addresses[1]) ? $this->addresses[1]->{$key} : '';
                break;
            case (preg_match('/^customfield/ism', $key) ? true : false):
                foreach((array)$this->custom_fields as $customfield) {
                    if(strtolower($customfield->name) === strtolower($key)) {
                        return $customfield->value;
                    }
                }
                break;
            case 'lists':
            	$lists = array();
                if($format) {
                    foreach((array)$this->lists as $list) {
                        $lists[] = $list->id;
                    }
                } else {
                    $lists = $this->lists;
                }
                return $lists;
                break;
			default:
				if(preg_match('/date/', $key) && $format) {
					return date('jS F Y \- H:i', (int)$cc->convert_timestamp($v));
				}

				if(isset($this->{$key})) {
                    $this->{$key} = is_string($this->{$key}) ? stripslashes($this->{$key}) : $this->{$key};
					return $this->{$key};
				} else {
					return '';
				}
				break;
		}
	}

	function getAddress($type = 'personal', $format = false) {
        $output = '';
		foreach($this->addresses as $Address) {
			if(strtoupper($type) !== $Address->address_type) { continue; }

			$output = $format ? '<span data-parent="'.$Address->address_type.'" data-name="line1" class="editable">'.$Address->line1.'</span>' : $Address->line1;

			if(!empty($Address->line2) || $format) {
                $output .= $format ? '<br /><span data-parent="'.$Address->address_type.'" data-name="line2" class="editable">'.$Address->line2.'</span>' : '<br />'.$Address->line2;
            }
			if(!empty($Address->line3) || $format) {
                $output .= $format ? '<br /><span data-parent="'.$Address->address_type.'" data-name="line3" class="editable">'.$Address->line3.'</span>' : '<br />'.$Address->line3;
            }
			if(!empty($Address->city) || $format) {
				$output .= $format ? '<br /><span data-parent="'.$Address->address_type.'" data-name="city" class="editable">'.$Address->city.'</span>' : '<br />'.$Address->city;
				if(!empty($Address->state_code)) { $output .= ','; }
			}
			if(!empty($Address->state_code) || $format) {
                $output .= $format ? ' <span data-parent="'.$Address->address_type.'" data-name="state_code" class="editable">'.$Address->state_code.'</span>' : ' '.$Address->state_code;
            }
			if(!empty($Address->postal_code) || $format) {
                $output .= $format ? ' <span data-parent="'.$Address->address_type.'" data-name="postal_code" class="editable">'.$Address->postal_code.'</span>' : ' '.$Address->postal_code;
				if(!empty($Address->sub_postal_code)) {
					$output .= $format ? '-<span data-parent="'.$Address->address_type.'" data-name="sub_postal_code" title="Sub-Postal Code" class="editable">'.$Address->sub_postal_code.'</span>' : '-'.$Address->sub_postal_code;
				}
			}
			if(!empty($Address->country_code) || $format) {
                $output .= $format ? '<br /><span data-parent="'.$Address->address_type.'" data-name="country_code" class="editable">'.strtoupper($Address->country_code).'</span>' : '<br />'.strtoupper($Address->country_code);
            }
		}
		return $output;
	}

	private function getEmail() {
		return ( !empty( $this->email_addresses ) && !empty($this->email_addresses[0]) && !empty($this->email_addresses[0]->email_address) ) ? $this->email_addresses[0]->email_address : false;
	}
}