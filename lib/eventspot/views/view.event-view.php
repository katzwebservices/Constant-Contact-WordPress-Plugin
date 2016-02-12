<?php
/**
 * @global CTCT_EventSpot $this
 * @global \Ctct\Components\EventSpot\EventSpot $Event
 */
$shortcode = sprintf( '[eventspot id="%s" /]', $Event->id );
?>

<h2 class="ctct-page-name alignleft"><?php echo esc_html( $Event->name ); ?></h2>

<div class="ctct-embed-shortcode">
	<label>
		<span class="howto"><?php esc_html_e('Embed the event in a post or page using this shortcode:', 'ctct'); ?></span>
		<input type="text" size="<?php echo strlen( $shortcode ); ?>" class="select-text code" readonly="readonly" value="<?php echo esc_attr( $shortcode ); ?>"/>
	</label>
</div>


<?php
if( $Event && in_array( $Event->status, array( 'ACTIVE', 'DRAFT' ) ) && ! empty( $Event->start_date ) ) {
	echo '<div class="clear"><h3>';
	$diff = human_time_diff( strtotime( $Event->start_date ) );
	printf( esc_html__('Time until event starts: %s', 'ctct' ), $diff );
	echo '</h3></div>';
}
?>
<table class="wp-list-table widefat form-table striped ctct_table" cellspacing="0">
	<?php
	if(!$Event) {
		echo sprintf('<tbody><tr><td><p>%s</p></td></tr></tbody></table><p class="submit"><a href="'.admin_url('admin.php?page=constant-contact-events').'" class="button-primary">%s</a></p></div>', __('Event Not Found', 'ctct'), __('Return to Events', 'ctct'));
		return;
	}
	$html = '';
	?>
	<tbody>
		<tr><th scope="row" id="name" class="manage-column column-name"><?php _e('Name', 'ctct'); ?></th><td><?php echo $Event->name; ?></td></tr>
		<tr class="alt"><th scope="row" id="description" class="manage-column column-name"><?php _e('Description', 'ctct'); ?></th><td><?php echo wpautop( $Event->description ); ?></td></tr>
		<tr><th scope="row" id="title" class="manage-column column-name"><?php _e('Title', 'ctct'); ?></th><td><?php echo $Event->title; ?></td></tr>
		<tr class="alt"><th scope="row" id="created" class="manage-column column-name"><?php _e('Created', 'ctct'); ?></th><td><?php echo constant_contact_event_date($Event->created_date); ?></td></tr>
		<tr><th scope="row" id="status" class="manage-column column-name"><?php _e('Status', 'ctct'); ?></th><td><?php echo $Event->status; ?></td></tr>
		<tr class="alt"><th scope="row" id="type" class="manage-column column-name"><?php _e('Type', 'ctct'); ?></th><td><?php echo $Event->type; ?></td></tr>
		<tr><th scope="row" id="start" class="manage-column column-name"><?php _e('Start', 'ctct'); ?></th><td><?php echo (!empty($Event->start_date) ? constant_contact_event_date($Event->start_date) : __('None', 'ctct')); ?></td></tr>
		<tr><th scope="row" id="end" class="manage-column column-name"><?php _e('End', 'ctct'); ?></th><td><?php echo (!empty($Event->end_date) ? constant_contact_event_date($Event->end_date) : __('None', 'ctct')); ?></td></tr>
		<?php if( !empty( $Event->registration_url ) ) { ?>
		<tr><th scope="row" id="registrationurl" class="manage-column column-name"><?php _e('Registration URL', 'ctct'); ?></th><td><?php echo_if_not_empty($Event->registration_url, '', '<a href="' . esc_url( $Event->registration_url ) . '">' . $Event->registration_url . '</a>'); ?></td></tr>
		<?php } ?>
		<tr class="alt"><th scope="row" id="location" class="manage-column column-name"><?php _e('Twitter Hashtag', 'ctct'); ?></th><td><?php printf( '<a href="https://twitter.com/hashtag/%s">%s</a>', str_replace( array( '#', '%23' ), '', $Event->twitter_hash_tag ), $Event->twitter_hash_tag ); ?></td></tr>
		<tr class="alt"><th scope="row" id="location" class="manage-column column-name"><?php _e('Location', 'ctct'); ?></th><td><?php  echo constant_contact_create_location( $Event->address, $Event->location ); ?></td></tr>
	</tbody>
</table>
<p class="submit"><a href="<?php echo remove_query_arg(array('view','refresh')); ?>" class="button-primary"><?php _e('Return to Events', 'ctct'); ?> <a href="<?php echo add_query_arg('refresh', 'event'); ?>" class="button-secondary alignright" title="<?php _e('Refresh Event data now.', 'ctct'); ?>"><?php _e('Refresh Event', 'ctct'); ?></a></a>

<?php

// Use $_registrants instead of total_registrant_count because that expires after event is done
$_registrants = $this->cc->getAll( 'EventRegistrants', array('id' => $Event->id ) );

$_cancelled = array();
$total_guests = 0;
/** @var \Ctct\Components\EventSpot\Registrant\Registrant $reg */
foreach($_registrants as $k => $reg) {
	$total_guests += $reg->guest_count;

	if($reg->registration_status == 'CANCELLED') {
		$_cancelled[] = $reg;
		unset($_registrants[$k]);
		continue;
	}
}

$registrant_text = sprintf( _n( __('%s Registrant', 'ctct'), __('%s Registrants', 'ctct'), sizeof( $_registrants ) ), number_format_i18n( sizeof( $_registrants ) ) );
$total_guests_text = sprintf( _n( __('%s Guest', 'ctct'), __('%s Guests', 'ctct'), $total_guests ), number_format_i18n( $total_guests ) );
$total_attending_text = sprintf( __('%s Attending', 'ctct'), ( sizeof( $_registrants ) + $total_guests ) );
$registrant_header = esc_html( sprintf( _x('%s: %s, %s', '# attending: registrants registered, guests registered', 'ctct' ), $total_attending_text, $registrant_text, $total_guests_text ) );
$registrant_header = empty( $_registrants ) ? esc_html__('No Registrations (so far!)', 'ctct') : $registrant_header;
?>
<h2 class="ctct-page-name"><?php echo $registrant_header; ?></h2>
<?php

if(!empty($_registrants) && current_user_can('list_users')) {
?>
	<h3 id="registrants"><?php _e('Registered', 'ctct'); ?></h3>
<?php
	include( 'partials/event-registrants.php' );
?>
<?php
}
?>
<p class="submit"><a href="<?php echo add_query_arg('refresh', 'eventregistrants').'#registrants'; ?>" class="button-secondary alignright" title="Event registrants data is stored for 1 hour. Refresh data now."><?php esc_html_e('Refresh Registrants', 'ctct'); ?></a></p>
<?php
if(!empty($_cancelled) && current_user_can('list_users')) {
	$_registrants = $_cancelled;
?>
	<h3 id="reg_cancelled"><?php _e('Cancelled', 'ctct'); ?></h3>
<?php
	include( 'partials/event-registrants.php' );
?>
	<p class="submit"><a href="<?php echo add_query_arg('refresh', 'eventregistrants').'#reg_cancelled'; ?>" class="button-secondary alignright" title="Event registrants data is stored for 1 hour. Refresh data now."><?php esc_html_e('Refresh Cancelled', 'ctct'); ?></a></p>
<?php
} // End empty $_cancelled