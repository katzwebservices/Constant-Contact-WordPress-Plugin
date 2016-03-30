<?php
/**
 * @package CTCT
 * @version 3.0
 */

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

/**
 * Convert a CTCT Tracking Summary object in to the sheets we know and love.
 *
 * @since 3.2
 *
 * @param \Ctct\Components\Tracking\TrackingSummary $summary
 *
 * @return string HTML output <dl>
 */
function kws_generate_tracking_summary_report( $summary ) {

	$output = '';

	$i = 1;

	// Create summary "Sheets"
	foreach( $summary as $k => $v ) {

		// Spam Count may be null
		if( is_null( $v ) ) { continue; }

		$output .= '
			<dl class="'.$k.' summary-'.$i.'">
                <dt>'.esc_html( ucwords(str_replace('_', ' ', $k)) ).'</dt>
                <dd>'.esc_html( $v ).'</dd>
            </dl>';
		$i++;
	}

	return $output;
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

		$link = remove_query_arg( array( 'paged', 'refresh', 'modified_since' ), $link );

		$title = isset( $item['title'] ) ? ' title="'.esc_attr( $item['title'] ).'"' : '';

		$output .= '<li><a '.kws_current_class($key, $item['val']).' href="'.esc_url( $link ).'" '.$title.'>'. esc_attr($item['text']).'</a>';
		if(sizeof($array) !== $i) { $output .= ' |'; }
		$output .= '</li>';
		$i++;
	}

	$output .= '</ul>';

	echo $output;
}
/**
 * Get status and modified_since parameters for the current Contacts view
 *
 * @since 4.0
 *
 * @return array Array with `modified_since` and `status` params, if set
 */
function kws_get_contacts_view_params() {
	$params = array();

	if( isset( $_GET['status'] ) ) {
		$params['status'] = esc_attr( $_GET['status'] );
	}

	$since = ! empty( $_GET['modified_since'] ) ? esc_attr( $_GET['modified_since'] ) : ( isset( $_GET['view'] ) ? false : '-1 month' );

	if( $since && $since = strtotime( $since ) ) {
		$params['modified_since'] = date( 'c', $since );
	}

	return $params;
}

function kws_print_modified_since_filter( $label = '', $default = '-1 month' ){
	$params = kws_get_contacts_view_params();
	$modified_since = !empty( $_GET['modified_since'] ) ? esc_attr( urldecode( $_GET['modified_since'] ) ) : ( empty( $params ) ? '' : $default );
	?>
<form action="<?php echo admin_url('admin.php'); ?>" method="get">
	<input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>" />
	<label for="ctct_modified_since" class="screen-reader-text"><?php echo esc_html( $label ); ?></label>
	<select name="modified_since" id="ctct_modified_since">
		<option value="" <?php selected( empty( $modified_since ) ); ?>><?php esc_html_e('Date modified&hellip;', 'constant-contact-api' ); ?></option>
		<option value="-1 day" <?php selected( '-1 day', $modified_since ); ?>><?php esc_html_e('In the last day', 'constant-contact-api' ); ?></option>
		<option value="-1 week"<?php selected( '-1 week', $modified_since, true ); ?>><?php esc_html_e('In the last week', 'constant-contact-api' ); ?></option>
		<option value="-1 month"<?php selected( '-1 month', $modified_since, true ); ?>><?php esc_html_e('In the last month', 'constant-contact-api' ); ?></option>
		<option value="-3 months"<?php selected( '-3 months', $modified_since, true ); ?>><?php esc_html_e('In the last 3 months', 'constant-contact-api' ); ?></option>
		<option value="-1 year"<?php selected( '-1 year', $modified_since, true ); ?>><?php esc_html_e('In the last year', 'constant-contact-api' ); ?></option>
	</select>
	<?php if( isset( $_GET['status'] ) ) { ?><input type="hidden" name="status" value="<?php echo esc_attr( $_GET['status'] ); ?>" /><?php } ?>
	<input type="submit" class="button button-secondary button-small" value="Filter">
</form>
<?php
}

/**
 * @param \Ctct\Components\EventSpot\Address|\Ctct\Components\Contacts\Address $address
 * @param string $location
 *
 * @return mixed|string|void
 */
function constant_contact_create_location( $address = array(), $location = '', $wpautop = false ) {

	if( '' !== $location ) {
		$address->location = $location;
	}

	$address_template = '{{location}}
{{line1}}
{{line2}}
{{line3}}
{{city}}, {{state_code}} {{postal_code}}
{{country}}';
	
	$address_string = $address_template;

	foreach( $address as $key => $value ) {
		$address_string = str_replace( '{{' . $key . '}}', $value, $address_string );
	}

	$address_string = normalize_whitespace( $address_string );

	if( $wpautop ) {
		$address_string = wpautop( $address_string );
	}

	return apply_filters( 'constant_contact_create_location', $address_string );
}

/**
 * @param \Ctct\Components\EventSpot\Address|\Ctct\Components\Contacts\Address $address
 * @param string $title
 */
function constant_contact_get_map_url_from_address( $address ) {

	$address_array = array(
		$address->line1,
		$address->line2,
		$address->line3,
		$address->city,
		$address->state,
		$address->country
	);

	$address_string = implode( ', ', array_filter( $address_array ) );

	$address_qs = urlencode( trim( rtrim( $address_string, ',' ) ) );

	return sprintf( 'https://maps.google.com/maps?q=%s', $address_qs );
}

/**
 * Generate a HTML table for a component
 *
 * @param \Ctct\Components\Component $Component
 * @return string Table output of component variables and values
 */
function ctct_generate_component_table( $Component, $recursive = 0 ) {

	$class = ' ctct-component-level-'.$recursive;
	$component_class_name = str_replace('\\', '-', strtolower( get_class( $Component ) ) );
	$class .= ' '.sanitize_title( $component_class_name );

	switch( $recursive ) {
		case 0:
			$class .= ' striped';
			break;
		case 1:
			$class .= '';
			break;
		default:
			$class .= ' ctct-light-border striped';
	}

	ob_start();
	?>
	<table class="ctct_table wp-list-table widefat <?php echo $class; ?>">
		<tbody>
		<?php
		foreach ( $Component as $key => $value ) {
			if( is_null( $value ) || in_array( $key, array( 'id' ) ) ) {
				continue;
			}

			$label = ucwords( implode(' ', explode( '_', $key ) ) );

			if( ! $recursive ) {
				$label = '<h4>'.$label.'</h4>';
			} else {
				$label = '<strong>'.$label.'</strong>';
			}

			echo '<tr class="ctct-component-key-'.sanitize_title( $key ).'">';
			echo '<th scope="row" style="vertical-align: top"><span>'.$label.'</span></th>';
			echo '<td>';
			if( is_a( $value, '\Ctct\Components\Component' ) ) {
				echo ctct_generate_component_table( $value, $recursive + 1 );
			} elseif( is_array( $value ) ) {
				foreach ( $value as $item ) {
					if( is_null( $item ) ) {
						continue;
					}
					if( is_a( $item, '\Ctct\Components\EventSpot\Address' ) || is_a( $item, '\Ctct\Components\Contacts\Address' ) ) {
						echo constant_contact_create_location( $item );
					} if( is_a( $item, '\Ctct\Components\Component' ) ) {
						echo ctct_generate_component_table( $item, $recursive + 1 );
					} else {
						echo '<p>'.$item.'</p>';
					}
				}
			} else {
				echo $value;
			}
			echo '</td>';
			echo '</tr>';
		}
		?>
		</tbody>
	</table>
	<?php

	return ob_get_contents();
}

/**
 * Should the request be refreshed?
 *
 * If DOING_AJAX or $_GET['refresh'] === strtolower( $type ), returns true
 *
 * @param string $type Type name; ie Contacts
 *
 * @return bool
 */
function constant_contact_refresh_cache($type) {
	return ( ( defined('DOING_AJAX') && DOING_AJAX ) || (isset($_GET['refresh']) && strtolower($_GET['refresh']) === strtolower($type) ) );
}

function kws_paginate_results( \Ctct\Components\ResultSet $resultSet, $limit = 50 ) {

	$output = '';

	if( ! empty( $resultSet->next ) ) {
		$next_results = sprintf( esc_html__('Next %d Results', 'constant-contact-api'), $limit );
		$output = sprintf( '<ul class="page-numbers"><li class="page-number"><a href="%s">%s</a></li><li class="page-number view-all"><a href="%s">%s</a></li></ul>',
			add_query_arg( array( 'next' => $resultSet->next ), remove_query_arg( array( 'status', 'limit' ) ) ),
			$next_results,
			add_query_arg( array( 'all' => 1 ), remove_query_arg( array( 'next', 'limit' ) ) ),
			esc_html__('View All Results', 'constant-contact-api')
		);
	}

	return $output;
}

/**
 * Print an array of notices
 * @param WP_Error[]|array $notices
 * @param string $class
 * @param bool $echo
 *
 * @return string
 */
function kws_print_notices( $notices = array(), $class = 'updated', $echo = true ) {

	$output = '<div class="' . esc_attr( $class ) . '">';

	foreach ( (array)$this->notices as $key => $notice ) {

		if( is_wp_error( $notice ) ) {

			$output .= '<h3>'.esc_html( sprintf( __('Error: %s', 'constant-contact-api'), $notice->get_error_code() ) ).'</h3>';

			$output .= wpautop( esc_html( $notice->get_error_message() ) );

		} else {
			$output .= wpautop( esc_html( $notice ) );
		}

	}
	$output .= '</div>';

	if( $echo ) {
		echo $output;
	}

	return $output;
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
