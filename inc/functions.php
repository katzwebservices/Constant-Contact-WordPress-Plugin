<?php

/**
 * Check whether the oAuth access token is set
 * @return boolean
 */
function is_ctct_configured(&$api = false) {
	if($api) {
		return $api->isConfigured();
	}
	if(!defined('CTCT_ACCESS_TOKEN')) { return false; }

	$token = CTCT_ACCESS_TOKEN;

	return !empty($token);
}

if(!function_exists('r')) {
	function r($data='', $die = false, $title = false) {
		if($title) {
			echo '<h3>'.$title.'</h3>';
		}
		echo '<pre>'.print_r($data, true).'</pre>';
		if($die) { die(); }
	}
}


function kws_ob_include($path, &$CTCT = NULL) {

	ob_start();
	include($path);
	$content = ob_get_clean();

	return $content;
}

function constant_contact_tip($tip, $echo = true) {
	$tip = '<span class="ctct_tip ctct_help" title="'.esc_attr( $tip ).'">?</span>';
	if($echo) { echo $tip; } return $tip;
}


function kws_format_date($date, $include_time = false) {
	$date = date_i18n(get_option('date_format'), strtotime($date), true);
	return $include_time ? $date = date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($date), true) : $date;
}

function kws_has_avatar( $email ) {
	$request = wp_remote_get( 'http://www.gravatar.com/avatar/' . md5($email) . '?d=404', array('limit-response-size' => 1));
	return $request['response']['code'] !== 404;
}

function kws_current_class($key, $val = '', $echo = false) {
	if((isset($_GET[$key]) && $_GET[$key] === $val) || (empty($val) && empty($_GET[$key]))) {
		$class = ' class="current filter-'.sanitize_title( $val ).'"';
		if($echo) { echo $class;} else { return $class; }
	} else {
		$class = ' class="filter-'.sanitize_title( $val ).'"';
	}
}

/**
 * array_diff with sorting built in
 *
 * @param  array $a array 1 to compare
 * @param  [type] $b array 2 to compare
 * @return array    array of differences in array
 */
function kws_array_diff(array $a, array $b) {
    $map = $out = array();

    sort($a);
    sort($b);

    $ad1 = array_diff($a, $b);
    $ad2 = array_diff($b, $a);

    return array_unique(array_merge($ad1, $ad2));
}

/**
 * Echo the submenu links for browsing admin page filters
 *
 * To generate a menu with "active" and "inactive" contact status, you could use:
 *
 * `
 * $array = array(
 * array('val' => 'active', 'text' => 'Active Contacts'),
 * array('val' => 'inactive', 'text' => 'Inactive Contacts')
 * );
 * kws_print_subsub('status', $array);
 *
 * @param  string $key   The key for the filtering, or "Filter by" parameter. Example: `status`
 * @param  array $array Associative array of items with `val` and `text` parameters. `val` is the URL param, and `text` is the link text.
 * @return [type]        [description]
 */
function kws_print_subsub($key, $array) {

	$output = '<ul class="subsubsub">';
	$i = 1;

	foreach($array as $item) {

		$link = add_query_arg(array($key => (empty($item['val']) ? NULL : $item['val'])));

		$link = remove_query_arg( 'paged', $link );

		$output .= '<li><a '.kws_current_class($key, $item['val']).' href="'.$link.'">'. esc_attr($item['text']).'</a>';
		if(sizeof($array) !== $i) { $output .= ' |'; }
		$output .= '</li>';
		$i++;
	}

	$output .= '</ul>';

	echo $output;
}

/**
 * Recursively remove empty items from an array
 * @param  mixed $haystack
 * @return array
 * @link http://stackoverflow.com/a/7696597
 */
function kws_array_remove_empty($haystack) {

	$haystack = (array)$haystack;

    foreach ($haystack as $key => $value) {
        if (is_array($value)) {
            $haystack[$key] = kws_array_remove_empty($haystack[$key]);
        }

        if (empty($haystack[$key])) {
            unset($haystack[$key]);
        }
    }

    return $haystack;
}

function kws_remove_matching_from_contact_array($old, $new, $first_layer_key = 'custom_fields', $second_layer_key = '', $match = 'equals') {

	foreach($old->{$first_layer_key} as $key => $old_first_layer) {
		foreach($new->{$first_layer_key} as $k => $new_first_layer) {
			if(
			   ($match === 'equals' && ($old_first_layer->{$second_layer_key} === $new_first_layer->{$second_layer_key})) ||
			   ($match === 'is' && $old_first_layer == $new_first_layer)
			) {
				unset($new->{$first_layer_key}[$key]);
			}
		}
	}

	return $new;
}

function kws_compare_contact_objects($old, $new) {
	unset($new->id, $new->status, $new->source, $new->source_details);

	foreach($new as $k => $v) {

		// We'll deal with arrays below.
		if(is_array($v) || is_object($v)) { continue; }

		if(maybe_serialize( $v ) === maybe_serialize( $old->{$k} ) || $v === NULL || ($v !== 0 && empty($v) && empty($old->{$k}))) {
			unset($new->{$k});
		 	unset($old->{$k});
		}
	}

	$new = kws_remove_matching_from_contact_array($old, $new, 'email_addresses', 'email_address');

	$new = kws_remove_matching_from_contact_array($old, $new, 'lists', 'id');

	$new = kws_remove_matching_from_contact_array($old, $new, 'notes', 'note');

	$new = kws_remove_matching_from_contact_array($old, $new, 'custom_fields', '', 'is');

	foreach($new->addresses as $key => $new_address) {
		unset($new_address->id);
		foreach($old->addresses as $old_address) {
			unset($old_address->id);
			if($old_address == $new_address) {
				unset($new->addresses[$key]);
			}

		}
	}

	$new = kws_array_remove_empty($new);

	return $new;
}

if(!function_exists('get_if_not_empty')) {
function get_if_not_empty($check = null, $empty = '', $echo = false) {
    if(!isset($check) || (empty($check) && $check !== 0)) { return $empty; }
    if(!$echo) { return $check; }
    return $echo;
}
}

if(!function_exists('echo_if_not_empty')) {
function echo_if_not_empty($check = null, $empty = '', $echo = false) {
    echo get_if_not_empty($check, $empty, $echo);
}
}

if(!function_exists('esc_attr_recursive')) {
function esc_attr_recursive($array) {
	if(is_array($array)) {
		foreach($array as $key => $item) {
			$array[$key] = esc_attr_recursive($item);
		}
	} else {
		$array = htmlspecialchars($array);
	}
	return $array;
}
}
