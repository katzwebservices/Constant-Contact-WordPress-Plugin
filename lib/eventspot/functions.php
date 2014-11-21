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

function constant_contact_old_api_get_all($type = 'Events', &$api, $passed = null, &$return = array(), $page = null) {

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
	if(empty($items[strtolower($type)])) {
		return $return;
	}

	// Otherwise, add items using the startdate time as the key for sorting below
	foreach($items[strtolower($type)] as $item) {
		$allkey = isset($item->startDate) ? strtotime($item->startDate) : null;
		$return[$allkey] = $item;
	}

	// Sort by event date
	krsort($return);

	if(!empty($items['nextLink'])) {
		constant_contact_old_api_get_all($type, $api, $passed, $return, $items['nextLink']);
	}

	set_transient($key, $return, apply_filters('constant_contact_cache_age', HOUR_IN_SECONDS * 6 ) );

	return $return;
}

function constant_contact_get_timezone($value='') {

	$timezone = null;

	if (date_default_timezone_get()) { $timezone = date_default_timezone_get(); }

	if (ini_get('date.timezone')) { $timezone = ini_get('date.timezone'); }

	return $timezone;
}

/**
 * Returns the timezone string for a site, even if it's set to a UTC offset
 *
 * Adapted from http://www.php.net/manual/en/function.timezone-name-from-abbr.php#89155
 *
 * @link  http://www.skyverge.com/blog/down-the-rabbit-hole-wordpress-and-timezones/
 * @return string valid PHP timezone string
 */
function constant_contact_get_timezone_string() {

    // if site timezone string exists, return it
    if ( $timezone = get_option( 'timezone_string' ) )
        return $timezone;

    // get UTC offset, if it isn't set then return UTC
    if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 0 ) ) )
        return 'UTC';

    // adjust UTC offset from hours to seconds
    $utc_offset *= 3600;

    // attempt to guess the timezone string from the UTC offset
    $timezone = timezone_name_from_abbr( '', $utc_offset );

    // last try, guess timezone string manually
    if ( false === $timezone ) {

        $is_dst = date( 'I' );

        foreach ( timezone_abbreviations_list() as $abbr ) {
            foreach ( $abbr as $city ) {
                if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset )
                    return $city['timezone_id'];
            }
        }
    }

    // fallback to UTC
    return 'UTC';
}

function constant_contact_event_date($value = null) {

	// We get the current server timezone
	$timezone = constant_contact_get_timezone();

	$tz_string = constant_contact_get_timezone_string();

	// We set the timezone to the blog timezone
	date_default_timezone_set( $tz_string );

	// We convert the date to "Date at Time"
	$string = sprintf(__('%1$s at %2$s', 'ctct'), date_i18n(get_option('date_format'), strtotime($value), false), date_i18n(get_option('time_format'), strtotime($value), false));

	// We restore the timezone to what it was
	date_default_timezone_set($timezone);

    return $string;
}

function constant_contact_get_id_from_object($object) {
	return preg_replace('/(?:.+\/)(.+)/ism', '$1', $object->id);
}

function constant_contact_create_location( $v ) {

    if(empty($v)) { return ''; }

    foreach($v as $key=> $l)  {
    	$v->{$key} = (string)get_if_not_empty($l, '');
    }

    $location_array = (array)$v;

    $possible_keys = array(
    	'location' => '',
    	'addr1' => '',
    	'addr2' => '',
    	'addr3' => '',
    	'city' => '',
    	'state' => '',
    	'province' => '',
    	'country' => '',
    	'postalCode' => '',
    	'locationForMap' => '',
    );

    $location_array = wp_parse_args( $location_array, $possible_keys );

    $location = get_if_not_empty($location_array['location'],'', "{$location_array['location']}<br />");
	$locationForMap = get_if_not_empty($location_array['locationForMap'],'', "<br />{$location_array['locationForMap']}");
    $address1 = get_if_not_empty($location_array['addr1'], '', $location_array['addr1'].'<br />');
    $address2 = get_if_not_empty($location_array['addr2'], '', $location_array['addr2'].'<br />');
    $address3 = get_if_not_empty($location_array['addr3'], '', $location_array['addr3'].'<br />');
    $city = get_if_not_empty($location_array['city'],'', "{$location_array['city']}, ");
    $state = get_if_not_empty($location_array['state'],'', "{$location_array['state']} ");
    $postalCode = get_if_not_empty($location_array['postalCode'],'', "{$location_array['postalCode']} ");
    $province = get_if_not_empty($location_array['province'],'', ", {$location_array['province']} ");
    $country = get_if_not_empty($location_array['country'],'', "<br />{$location_array['country']}");

    return apply_filters('constant_contact_create_location', rtrim(trim("{$location}{$address1}{$address2}{$address3}{$city}{$state}{$postalCode}{$province}{$country}")));
}

function constant_contact_generate_table(array $data) {

	foreach($data as $k => $val) {
		$rows = constant_contact_generate_rows($val);

		if(empty($rows)) { continue; }
	?>
		<h2><?php echo $k; ?></h2>
		<table class="widefat form-table ctct_table" id="<?php echo sanitize_title($k); ?>" cellspacing="0">
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
		$alt = empty( $alt ) ? 'class="alt"' : '';
		$key = preg_replace('/(?!^)[[:upper:]]/',' \0',$key);
		$reg .= '<tr '.$alt.'><th scope="row" id="'.sanitize_title($key).'" class="manage-column column-name" style=""><span>'.$key.'</span></th><td>'.get_if_not_empty($val, '<span class="description">(Empty)</span>').'</td></tr>';
	}
	return $reg;
}