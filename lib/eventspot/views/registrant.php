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
					$li = date('jS F Y \- H:i', strtotime($li));
				}
				$answer .= '<li>'.$li.'</li>';
			}

			$val = "<ul class='ul-disc'>{$answer}</ul>";

			$customs[$customfield->question] = $val;
		}
	}

	$data = array(
		'Registration Information' => array(
			'Registration Status'=> $v->registrationStatus,
			'Registration Date' => date('jS F Y \- H:i', strtotime($v->registrationDate)),
			'Guest Count'=> get_if_not_empty($v->guestCount,1),
			'Payment Status'=> $v->paymentStatus,
			'Order Amount'=> $v->orderAmount,
			'Currency Type'=>$v->currencyType,
			'Payment Type' => $v->paymentType,
			'Summary of Costs' => $costs,
		),
		'Personal Information' => array(
		    'Name' => $v->title,
		    'Email' => get_if_not_empty($v->email,'', "<a href='mailto:{$v->email}'>{$v->email}</a>"),
		    'Phone' => $v->personalInformation->phone,
			'Cell Phone' => $v->personalInformation->cellPhone,
	        'Address' => constant_contact_create_location($v->personalInformation),
		),
		'Business Information' => array(
			'Company' => $v->businessInformation->company,
			'Job Title' => $v->businessInformation->jobTitle,
			'Department' => $v->businessInformation->department,
			'Phone' => $v->businessInformation->phone,
			'Fax' => $v->businessInformation->fax,
			'Website' => $v->businessInformation->website,
			'Blog' => $v->businessInformation->blog,
			'Address' => constant_contact_create_location($v->businessInformation),
		),
		'Custom Information' => $customs
	);

		echo constant_contact_generate_table($data);
?>

<p class="submit"><a href="<?php echo remove_query_arg(array('registrant', 'refresh')); ?>" class="button-primary"><?php _e('Return to Event', 'constant-contact-api'); ?></a> <a href="<?php echo add_query_arg('refresh', 'registrant'); ?>" class="button-secondary alignright" title="Registrant data is stored for 1 hour. Refresh data now."><?php _e('Refresh Registrant', 'constant-contact-api'); ?></a></p>
