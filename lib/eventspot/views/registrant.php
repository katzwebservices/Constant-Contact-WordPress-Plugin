<?php

	if(!empty($v->costs)) {
		if(is_object($v->costs)) {
			$v->costs = array($v->costs);
		}
		$costs = '<ul class="ul-disc">';
		foreach($v->costs as $val) {
			$val->rate = floatval($val->rate);
			$costs .= '<li>';
			$costs .= empty($val->rate) ? __('Free', 'constant-contact-api') : "<strong>{$val->feeType}</strong>: {$val->count} Guest(s) x {$val->rate} = {$val->total}";
			$costs .= '</li>';
		}
		$costs .= '</ul>';
	}

	$customs = array();
	$customInformation = array('customInformation1', 'customInformation2');
	// Vars customInformation1 and customInformation2
	foreach($customInformation as $k) {
		// Contain arrays of CustomField objects
		foreach($v->{$k} as $customfield) {
			$answer = '';
			foreach((array)$customfield->answers as $li) {
				// if it's a date, treat it as such
				if(preg_match('/^20[0-9]{2}\-/', $li)) {
					$li = constant_contact_event_date($li);
				}
				$answer .= '<li>'.$li.'</li>';
			}

			$val = "<ul class='ul-disc'>{$answer}</ul>";

			$customs[$customfield->question] = $val;
		}
	}

	$data = array(
			__('Registration Information', 'constant-contact-api') => array(
			__('Registration Status', 'constant-contact-api') => $v->registrationStatus,
			__('Registration Date', 'constant-contact-api') => constant_contact_event_date($v->registrationDate),
			__('Guest Count', 'constant-contact-api') => get_if_not_empty($v->guestCount,1),
			__('Payment Status', 'constant-contact-api') => $v->paymentStatus,
			__('Order Amount', 'constant-contact-api') => $v->orderAmount,
			__('Currency Type', 'constant-contact-api') =>$v->currencyType,
			__('Payment Type', 'constant-contact-api') => $v->paymentType,
			__('Summary of Costs', 'constant-contact-api') => $costs,
		),
		__('Personal Information', 'constant-contact-api') => array(
		    __('Name', 'constant-contact-api') => $v->title,
		    __('Email', 'constant-contact-api') => get_if_not_empty($v->email,'', "<a href='mailto:{$v->email}'>{$v->email}</a>"),
		    __('Phone', 'constant-contact-api') => $v->personalInformation->phone,
			__('Cell Phone', 'constant-contact-api') => $v->personalInformation->cellPhone,
	        __('Address', 'constant-contact-api') => constant_contact_create_location($v->personalInformation),
		),
		__('Business Information', 'constant-contact-api') => array(
			__('Company', 'constant-contact-api') => $v->businessInformation->company,
			__('Job Title', 'constant-contact-api') => $v->businessInformation->jobTitle,
			__('Department', 'constant-contact-api') => $v->businessInformation->department,
			__('Phone', 'constant-contact-api') => $v->businessInformation->phone,
			__('Fax', 'constant-contact-api') => $v->businessInformation->fax,
			__('Website', 'constant-contact-api') => $v->businessInformation->website,
			__('Blog', 'constant-contact-api') => $v->businessInformation->blog,
			__('Address', 'constant-contact-api') => constant_contact_create_location($v->businessInformation),
		),
		__('Custom Information', 'constant-contact-api') => $customs
	);

		echo constant_contact_generate_table($data);
?>

<p class="submit"><a href="<?php echo remove_query_arg(array('registrant', 'refresh')); ?>" class="button-primary"><?php _e('Return to Event', 'constant-contact-api'); ?></a> <a href="<?php echo add_query_arg('refresh', 'registrant'); ?>" class="button-secondary alignright" title="<?php _e('Registrant data is stored for 1 hour. Refresh data now.', 'constant-contact-api'); ?>"><?php _e('Refresh Registrant', 'constant-contact-api'); ?></a></p>
