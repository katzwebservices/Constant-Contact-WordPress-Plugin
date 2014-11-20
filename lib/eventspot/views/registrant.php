<?php

	if(!empty($v->costs)) {
		if(is_object($v->costs)) {
			$v->costs = array($v->costs);
		}
		$costs = '<ul class="ul-disc">';
		foreach($v->costs as $val) {
			$val->rate = floatval($val->rate);
			$costs .= '<li>';
			$costs .= empty($val->rate) ? __('Free', 'ctct') : "<strong>{$val->feeType}</strong>: {$val->count} Guest(s) x {$val->rate} = {$val->total}";
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
			__('Registration Information', 'ctct') => array(
			__('Registration Status', 'ctct') => $v->registrationStatus,
			__('Registration Date', 'ctct') => constant_contact_event_date($v->registrationDate),
			__('Guest Count', 'ctct') => get_if_not_empty($v->guestCount,1),
			__('Payment Status', 'ctct') => $v->paymentStatus,
			__('Order Amount', 'ctct') => $v->orderAmount,
			__('Currency Type', 'ctct') =>$v->currencyType,
			__('Payment Type', 'ctct') => $v->paymentType,
			__('Summary of Costs', 'ctct') => $costs,
		),
		__('Personal Information', 'ctct') => array(
		    __('Name', 'ctct') => $v->title,
		    __('Email', 'ctct') => get_if_not_empty($v->email,'', "<a href='mailto:{$v->email}'>{$v->email}</a>"),
		    __('Phone', 'ctct') => $v->personalInformation->phone,
			__('Cell Phone', 'ctct') => $v->personalInformation->cellPhone,
	        __('Address', 'ctct') => constant_contact_create_location($v->personalInformation),
		),
		__('Business Information', 'ctct') => array(
			__('Company', 'ctct') => $v->businessInformation->company,
			__('Job Title', 'ctct') => $v->businessInformation->jobTitle,
			__('Department', 'ctct') => $v->businessInformation->department,
			__('Phone', 'ctct') => $v->businessInformation->phone,
			__('Fax', 'ctct') => $v->businessInformation->fax,
			__('Website', 'ctct') => $v->businessInformation->website,
			__('Blog', 'ctct') => $v->businessInformation->blog,
			__('Address', 'ctct') => constant_contact_create_location($v->businessInformation),
		),
		__('Custom Information', 'ctct') => $customs
	);

		echo constant_contact_generate_table($data);
?>

<p class="submit"><a href="<?php echo remove_query_arg(array('registrant', 'refresh')); ?>" class="button-primary"><?php _e('Return to Event', 'ctct'); ?></a> <a href="<?php echo add_query_arg('refresh', 'registrant'); ?>" class="button-secondary alignright" title="<?php _e('Registrant data is stored for 1 hour. Refresh data now.', 'ctct'); ?>"><?php _e('Refresh Registrant', 'ctct'); ?></a></p>
