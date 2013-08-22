<?php

function constant_contact_refresh_cache($type) {
	return (isset($_GET['refresh']) && strtolower($_GET['refresh']) === strtolower($type));
}

function constant_contact_cache_key($string, $passed = null) {
	// Generate an unique caching key for this request
	$key = $string;
	if(!empty($passed) && isset($passed->link)) {
		$key = $string.$passed->link;
	}
	$key = 'cc_'.sha1($key);
	return $key;
}

function constant_contact_old_api_get_all($type = 'Events', &$api, $passed = null, &$all = array(), $page = null) {

	$key = constant_contact_cache_key('all_'.$type, $passed);

	if(!constant_contact_refresh_cache($type) && $cached = get_transient($key)) {
		return $cached;
	}

	if(!empty($passed)) {
		$items = $api->{"get{$type}"}($passed, $page);
	} else {
		$items = $api->{"get{$type}"}($page);
	}

	// If no results
	if(empty($items[strtolower($type)])) { return $all; }

	// Otherwise, add items
	foreach($items[strtolower($type)] as $item) {
		$allkey = isset($item->startDate) ? strtotime($item->startDate) : null;
		$all[$allkey] = $item;
	}

	// Sort by event date
	krsort($all);

	if(!empty($items['nextLink'])) {
		constant_contact_old_api_get_all($type, $api, $passed, $all, $items['nextLink']);
	}

	set_transient($key, $all, apply_filters('constant_contact_cache_age', 60 * 60 * 6));

	return $all;
}

function constant_contact_get_timezone($value='') {

	$timezone = null;

	if (date_default_timezone_get()) { $timezone = date_default_timezone_get(); }

	if (ini_get('date.timezone')) { $timezone = ini_get('date.timezone'); }

	return $timezone;
}

function constant_contact_event_date($value = null) {

	// We get the current server timezone
	$timezone = constant_contact_get_timezone();

	// We set the timezone to the blog timezone
	date_default_timezone_set(get_option('timezone_string'));

	// We convert the date to "Date at Time"
	$string = sprintf(__('%1$s at %2$s','constant-contact-api'), date_i18n(get_option('date_format'), strtotime($value), false), date_i18n(get_option('time_format'), strtotime($value), false));

	// We restore the timezone to what it was
	date_default_timezone_set($timezone);

    return $string;
}

function constant_contact_get_id_from_object($object) {
	return preg_replace('/(?:.+\/)(.+)/ism', '$1', $object->id);
}

function constant_contact_create_location($v = array()) {
    if(empty($v)) { return ''; }
    foreach($v as $key=> $l)  { $v->{$key} = (string)get_if_not_empty($l, ''); }
    extract((array)$v);
    $location = @get_if_not_empty($location,'', "{$location}<br />");
    $locationForMap = @get_if_not_empty($locationForMap,'', "<br />{$locationForMap}");
    $address1 = @get_if_not_empty($addr1, '', $addr1.'<br />');
    $address2 = @get_if_not_empty($addr2, '', $addr2.'<br />');
    $address3 = @get_if_not_empty($addr3, '', $addr3.'<br />');
    $city = @get_if_not_empty($city,'', "{$city}, ");
    $state = @get_if_not_empty($state,'', "{$state} ");
    $postalCode = @get_if_not_empty($postalCode,'', "{$postalCode} ");
    $province = @get_if_not_empty($province,'', ", {$province} ");
    $country = @get_if_not_empty($country,'', "<br />{$country}");
    return apply_filters('constant_contact_create_location', rtrim(trim("{$location}{$address1}{$address2}{$address3}{$city}{$state}{$postalCode}{$province}{$country}")));
}

function constant_contact_generate_table(array $data) {

	foreach($data as $k => $val) {
		$rows = constant_contact_generate_rows($val);

		if(empty($rows)) { continue; }
	?>
		<h2><?php echo $k; ?></h2>
		<table class="widefat form-table" id="<?php echo sanitize_title($k); ?>" cellspacing="0">
			<tbody>
			<?php echo $rows; ?>
			</tbody>
		</table>
	<?php
	}
}

function constant_contact_generate_rows(array $data) {
	$reg = '';
	foreach($data as $key => $val) {
		if(!is_string($val)) { continue; }
		$key = preg_replace('/(?!^)[[:upper:]]/',' \0',$key);
		$reg .= '<tr><th scope="row" id="'.sanitize_title($key).'" class="manage-column column-name" style=""><span>'.$key.'</span></th><td>'.get_if_not_empty($val, '<span class="description">(Empty)</span>').'</td></tr>';
	}
	return $reg;
}