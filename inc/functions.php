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
                <dt>'.esc_html( ctct_get_label_from_field_id( $k ) ).'</dt>
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

	$since = ! empty( $_GET['modified_since'] ) ? esc_attr( $_GET['modified_since'] ) : ( ( isset( $_GET['view'] ) || isset( $_GET['status'] ) ) ? false : '-1 month' );

	if( $since && $since = strtotime( $since ) ) {
		$params['modified_since'] = date( 'c', $since );
	}

	return $params;
}

/**
 * Display a "Modified Since" dropdown for views where you want to filter by modified date
 * @since 4.0
 * @param string $label
 * @param string $default
 */
function kws_print_modified_since_filter( $label = '', $default = '-1 month' ){
	$params = kws_get_contacts_view_params();
	$modified_since = isset( $_GET['modified_since'] ) ? esc_attr( urldecode( $_GET['modified_since'] ) ) : ( empty( $params['modified_since'] ) ? '' : $default );
	?>
<form action="<?php echo admin_url('admin.php'); ?>" method="get">
	<label for="ctct_modified_since" class="screen-reader-text"><?php echo esc_html( $label ); ?></label>
	<select name="modified_since" id="ctct_modified_since">
		<option value="" <?php selected( empty( $modified_since ) ); ?>><?php esc_html_e('Date modified&hellip;', 'constant-contact-api' ); ?></option>
		<option value="-1 day" <?php selected( '-1 day', $modified_since ); ?>><?php esc_html_e('In the last day', 'constant-contact-api' ); ?></option>
		<option value="-1 week"<?php selected( '-1 week', $modified_since, true ); ?>><?php esc_html_e('In the last week', 'constant-contact-api' ); ?></option>
		<option value="-1 month"<?php selected( '-1 month', $modified_since, true ); ?>><?php esc_html_e('In the last month', 'constant-contact-api' ); ?></option>
		<option value="-3 months"<?php selected( '-3 months', $modified_since, true ); ?>><?php esc_html_e('In the last 3 months', 'constant-contact-api' ); ?></option>
		<option value="-1 year"<?php selected( '-1 year', $modified_since, true ); ?>><?php esc_html_e('In the last year', 'constant-contact-api' ); ?></option>
	</select>
	<input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>" />
	<?php if( isset( $_GET['view'] ) ) { ?><input type="hidden" name="view" value="<?php echo esc_attr( $_GET['view'] ); ?>" /><?php } ?>
	<?php if( isset( $_GET['status'] ) ) { ?><input type="hidden" name="status" value="<?php echo esc_attr( $_GET['status'] ); ?>" /><?php } ?>
	<input type="submit" class="button button-secondary button-small" value="<?php esc_attr_e('Filter', 'constant-contact-api'); ?>">
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
 * Convert field ids to upper-cased words
 *
 * email_address => Email Address
 * home_phone => Home Phone
 *
 * @since 4.0
 *
 * @param $field_id
 *
 * @return string
 */
function ctct_get_label_from_field_id( $field_id ) {

	switch( $field_id ) {
		case 'id':
			return __('ID', 'constant-contact-api');
			break;
		case 'email_addresses':
			return __('Email Address', 'constant-contact-api');
			break;
		case 'addresses':
		case 'line1':
			return __('Address', 'constant-contact-api');
			break;
		case 'line2':
			return __('Address Line 2', 'constant-contact-api');
			break;
		case 'line3':
			return __('Address Line 3', 'constant-contact-api');
			break;
	}

	$label = $field_id;
	$label = ucwords( implode(' ', explode( '_', $label ) ) );
	$label = preg_replace('/Addr([0-9])/', __('Address $1', 'constant-contact-api'), $label);
	$label = preg_replace('/Field([0-9])/', __('Field $1', 'constant-contact-api'), $label);

	return $label;
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
	?>
	<table class="ctct_table wp-list-table widefat <?php echo $class; ?>">
		<tbody>
		<?php
		foreach ( $Component as $key => $value ) {
			if( is_null( $value ) || in_array( $key, array( 'id' ) ) ) {
				continue;
			}
			$label = ctct_get_label_from_field_id( $key );
			if( ! $recursive ) {
				$label = '<h4>'.$label.'</h4>';
			} else {
				$label = '<strong>'.$label.'</strong>';
			}
			echo '<tr class="ctct-component-key-'.sanitize_title( $key ).'">';
			echo '<th scope="row" style="vertical-align: top"><span>'.$label.'</span></th>';
			echo '<td>';
			if( is_a( $value, '\Ctct\Components\Component' ) ) {
				ctct_display_component_item( $value, $recursive + 1 );
			} elseif( is_array( $value ) ) {
				foreach ( $value as $item ) {
					if( is_null( $item ) ) {
						continue;
					}
					if( is_a( $item, '\Ctct\Components\Component' ) ) {
						ctct_display_component_item( $item, $recursive + 1 );
					} else {
						echo '<p>'.$item.'</p>';
					}
				}
			} elseif( in_array( $key, array( 'date', 'registration_date' ) ) ) {
				echo date_i18n( sprintf( '%s \a\t %s', get_option('date_format'), get_option('time_format') ), strtotime( $value ), false );
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
}

/**
 * Process displaying different components in different ways!
 *
 * @since 4.0
 * @param $Component
 * @param $recursive
 * @return void
 */
function ctct_display_component_item( $Component, $recursive ) {

	$class = get_class( $Component );

	switch ( $class ) {
		case '\Ctct\Components\EventSpot\Address':
		case '\Ctct\Components\Contacts\Address':
			echo constant_contact_create_location( $Component );
			break;
		case 'Ctct\Components\EventSpot\PaymentSummary':
			ctct_display_payment_summary( $Component );
			break;
		case 'Ctct\Components\EventSpot\Registrant\RegistrantSection':
			ctct_display_component_section( $Component );
			break;
		case 'Ctct\Components\EventSpot\Guest':

			$guest_sections = sizeof( $Component->guest_section ) === 1 ? array( $Component->guest_section ) : $Component->guest_section;

			/** @var Ctct\Components\EventSpot\Guest $Component */
			foreach ( $guest_sections as $section ) {
				ctct_display_component_section( $section );
			}
			break;
		case '\Ctct\Components\Component':
		case 'Ctct\Components\EventSpot\Registrant\RegistrantFee':
		default:
			ctct_generate_component_table( $Component, $recursive + 1 );
			break;
	}
}

/**
 * @param Ctct\Components\EventSpot\PaymentSummary $Component
 */
function ctct_display_payment_summary( $Component ) {

	if( empty( $Component->order ) || 'NA' === $Component->payment_status ) {
		esc_html_e( 'No Payment Details', 'constant-contact-api' );
		return;
	}

	$output = '<dl class="ctct-component-section">
		<dt>' . esc_html__( 'Order ID', 'constant-contact-api' ) . '</dt><dd>' . esc_html( $Component->order->order_id ) . '</dd>
		<dt>' . esc_html__( 'Order Date', 'constant-contact-api' ) . '</dt><dd>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $Component->order->order_date ) ) ) . '</dd>
		<dt>' . esc_html__( 'Payment Status', 'constant-contact-api' ) . '</dt><dd>' . esc_html( ucwords( strtolower( $Component->payment_status ) ) ) . '</dd>
		<dt>' . esc_html__( 'Payment Type', 'constant-contact-api' ) . '</dt><dd>' . esc_html( ucwords( strtolower( $Component->payment_type ) ) ) . '</dd>
	</dl>';

	$event_amount_string = esc_html__( 'Event Cost', 'constant-contact-api' );
	$payment_details_string = esc_html__( 'Payment Details', 'constant-contact-api' );
	$fees_string            = esc_html__( 'Fees', 'constant-contact-api' );
	$fee_string            = esc_html__( 'Fee Name', 'constant-contact-api' );
	$name_string            = esc_html__( 'Registrant', 'constant-contact-api' );
	$quantity_string        = esc_html__( 'Quantity', 'constant-contact-api' );
	$amount_string          = esc_html__( 'Amount', 'constant-contact-api' );

	$subtotal = 0;
	$fees_output = "<table class='ctct_table widefat'><thead><tr><th>{$fee_string}</th><th>{$name_string}</th><th>{$quantity_string}</th><th>{$amount_string}</th></tr></thead><tbody>";
	foreach ( $Component->order->fees as $fee ) {
		$subtotal = $subtotal + $fee->amount;
		$fees_output .= "<tr><th scope='row'>{$fee->type}</th><td>{$fee->name}</td><td>{$fee->quantity}</td><td>{$fee->amount}</td></tr>";
	}
	$fees_output .= '</tbody></table>';

	$event_cost = number_format_i18n( ( $Component->order->total - $subtotal ), 2 );
	$event_row = ( $event_cost < 0 ) ? '' : "<tr><th colspan='2'><strong>{$event_amount_string}</strong></th><td colspan='2' style='text-align: right'>{$event_cost}</td></tr>";
	$subtotal   = number_format_i18n( $subtotal, 2 );

	$output .= <<<EOD
	<h3>{$payment_details_string}</h3>
	<table class='ctct_table widefat'>
		<thead>
		</thead>
		<tbody>
			{$event_row}
			<tr><th><strong>{$fees_string}</strong></th><td colspan='3' style='text-align: right'>{$subtotal}</td></tr>
			<tr><td colspan='4'>$fees_output</td></tr>
		</tbody>
EOD;

	$output .= "
		<tfoot>";

	if ( isset( $Component->promo_code ) ) {
		$promocode_info         = $Component->promo_code->promo_code_info;
		$total_discount         = number_format_i18n( $Component->promo_code->total_discount, 2 );
		$discount_amount        = ( 'PERCENT' === $promocode_info->discount_type ) ? $promocode_info->discount_percent . '%' : $promocode_info->discount_amount;
		$discount_amount_string = sprintf( __( '%%%d off', 'constant-contact-api' ), $discount_amount );
		$discount_string        = __( 'Discounts:', 'constant-contact-api' );
		$output .= "<tr><th scope='row'><strong>{$discount_string}</strong></th><td><code>{$promocode_info->code_name}</code> ({$discount_amount_string})</td><td colspan='2' style='text-align: right'>{$total_discount}</td></tr>";
	}

	$total_string = esc_html__( 'Total:', 'constant-contact-api' );
	$total        = number_format_i18n( $Component->order->total, 2 );

	$output .= "
			<tr><th><strong>{$total_string}</strong></th><td colspan='3' style='text-align: right'>{$Component->order->currency_type} {$total}</td></tr>
		</tfoot>
	</table>";

	echo $output;
}

/**
 * Display "Section" details about a Registrant or a Guest
 * @since 4.0
 * @param Ctct\Components\EventSpot\Registrant\RegistrantSection|Ctct\Components\EventSpot\GuestSection $section
 * @return void
 */
function ctct_display_component_section( $section ) {

	echo '<h3>' . esc_html( $section->label ) . '</h3>';

	$output = '<dl class="ctct-component-section">';

	foreach ( $section->fields as $field ) {
		$value = is_null( $field->values ) ? $field->value : implode( ', ', $field->values );
		$label = $field->label;

		$output .= '<dt>' . esc_html( $label ) . '</dt><dd>' . esc_html( $value ) . '</dd>';
	}

	$output .= '</dl>';

	echo $output;
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
 *
 * @since 4.0.3 Added $title parameter
 *
 * @param WP_Error[]|array $notices
 * @param string $class
 * @param bool $echo
 * @param string $title If passed, used as the title of the error. Otherwise, taken from the error itself.
 *
 * @return string
 */
function kws_print_notices( $notices = array(), $class = 'updated', $echo = true, $title = '' ) {

	$output = '<div class="' . esc_attr( $class ) . '">';

	foreach ( (array)$notices as $key => $notice ) {

		if( is_wp_error( $notice ) ) {

			if( empty( $title ) ) {
				$title = sprintf( __('Error: %s', 'constant-contact-api'), $notice->get_error_code() );
			} else {
				$title = sprintf( $title, $notice->get_error_code() );
			}

			$output .= '<h3>'.esc_html( $title ).'</h3>';

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
