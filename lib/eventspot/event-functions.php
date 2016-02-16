<?php

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
	$string = sprintf(__('%1$s at %2$s', 'constant-contact-api'), date_i18n(get_option('date_format'), strtotime($value), false), date_i18n(get_option('time_format'), strtotime($value), false));

	// We restore the timezone to what it was
	date_default_timezone_set($timezone);

    return $string;
}

function constant_contact_get_id_from_object($object) {
	return preg_replace('/(?:.+\/)(.+)/ism', '$1', $object->id);
}


/**
 * @param \Ctct\Components\EventSpot\Registrant\Registrant $registrant
 * @return string Name
 */
function constant_contact_registrant_name( $registrant ) {
	return trim( sprintf( '%s %s', $registrant->first_name, $registrant->last_name ) );
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