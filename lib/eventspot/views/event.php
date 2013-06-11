
<h2 class="fittext"><?php esc_attr_e( $v->name, 'constant-contact-api' ); ?></h2>

<?php
// Only show if the event isn't draft; there's nothing to show if it's draft.
if($v->status !== 'DRAFT') { ?>
<div class="clear component-summary">
<?php
		$cols = array(
		              'registered' => __('Registered', 'constant-contact-api'),
		              'attendedCount' => ($completed ? __('Attended', 'constant-contact-api') : __('Attending', 'constant-contact-api')),
		              'cancelledCount' => __('Cancelled', 'constant-contact-api')
		            );
		$i = 1;
		foreach($cols as $col => $label) {
			$html .= '<dl class="'.$col.' summary-'.$i.'">
			        <dt>'.$label.'</dt>
			        <dd>'.htmlentities($v->{"{$col}"}).'</dd>
			    </dl>';
			$i++;
		}
		echo $html;
	?>
</div>
<?php } ?>

<h3><?php _e('Event Details:', 'constant-contact-api'); ?></h3>
<table class="ctct_table widefat" cellspacing="0">
	<?php
	if(!$v) {
		echo sprintf('<tbody><tr><td><p>%s</p></td></tr></tbody></table><p class="submit"><a href="'.admin_url('admin.php?page=constant-contact-events').'" class="button-primary">%s</a></p></div>', __('Event Not Found', 'constant-contact-api'), __('Return to Events', 'constant-contact-api'));
		return;
	}
	$html = '';

	?>
	<tbody>
		<tr><th scope="row" id="name" class="manage-column column-name" style=""><?php _e('Name', 'constant-contact-api'); ?></th><td><?php echo $v->name; ?></td></tr>
		<tr class="alt"><th scope="row" id="description" class="manage-column column-name" style=""><?php _e('Description', 'constant-contact-api'); ?></th><td><?php echo_if_not_empty($v->description); ?></td></tr>
		<tr><th scope="row" id="title" class="manage-column column-name" style=""><?php _e('Title', 'constant-contact-api'); ?></th><td><?php echo $v->title; ?></td></tr>
		<tr class="alt"><th scope="row" id="created" class="manage-column column-name" style=""><?php _e('Created', 'constant-contact-api'); ?></th><td><?php echo constant_contact_event_date($v->createdDate); ?></td></tr>
		<tr><th scope="row" id="status" class="manage-column column-name" style=""><?php _e('Status', 'constant-contact-api'); ?></th><td><?php echo $v->status; ?></td></tr>
		<tr class="alt"><th scope="row" id="type" class="manage-column column-name" style=""><?php _e('Type', 'constant-contact-api'); ?></th><td><?php echo $v->eventType; ?></td></tr>
		<tr><th scope="row" id="start" class="manage-column column-name" style=""><?php _e('Start', 'constant-contact-api'); ?></th><td><?php echo (!empty($v->startDate) ? constant_contact_event_date($v->startDate) : __('None', 'constant-contact-api')); ?></td></tr>
		<tr><th scope="row" id="end" class="manage-column column-name" style=""><?php _e('End', 'constant-contact-api'); ?></th><td><?php echo (!empty($v->endDate) ? constant_contact_event_date($v->endDate) : __('None', 'constant-contact-api')); ?></td></tr>
		<tr><th scope="row" id="registrationurl" class="manage-column column-name" style=""><?php _e('Registration URL', 'constant-contact-api'); ?></th><td><?php echo_if_not_empty($v->registrationUrl, '', '<a href="'.$v->registrationUrl.'">'.$v->registrationUrl.'</a>'); ?></td></tr>
		<tr class="alt"><th scope="row" id="location" class="manage-column column-name" style=""><?php _e('Location', 'constant-contact-api'); ?></th><td><?php echo constant_contact_create_location($v->eventLocation); ?></td></tr>
	</tbody>
</table>
<p class="submit"><a href="<?php echo remove_query_arg(array('view','refresh')); ?>" class="button-primary"><?php _e('Return to Events', 'constant-contact-api'); ?> <a href="<?php echo add_query_arg('refresh', 'event'); ?>" class="button-secondary alignright" title="<?php _e('Refresh Event data now.', 'constant-contact-api'); ?>"><?php _e('Refresh Event', 'constant-contact-api'); ?></a></a>

<?php

$_registrants = constant_contact_old_api_get_all('Registrants', $this->old_api, $v);
$_cancelled = array();
foreach($_registrants as $k => $reg) {
	if($reg->registrationStatus == 'CANCELLED') {
		$_cancelled[] = $reg;
		unset($_registrants[$k]);
		continue;
	}
}

// If no registrants, let's get outta here.
if(empty($_registrants) && empty($_cancelled)) { return; }

?>
<h2><?php _e('Registrants', 'constant-contact-api'); ?></h2>
<?php

if(!empty($_registrants) && current_user_can('list_users')) {
?>
	<h3 id="registrants"><?php _e('Registered', 'constant-contact-api'); ?></h3>
<?php
	include('registrants.php');
?>
	<p class="submit"><a href="<?php echo add_query_arg('refresh', 'registrants').'#registrants'; ?>" class="button-secondary alignright" title="Event registrants data is stored for 1 hour. Refresh data now.">Refresh Registrants</a></p>
<?php
}

if(!empty($_cancelled) && current_user_can('list_users')) {
	$_registrants = $_cancelled;
?>
	<h3 id="reg_cancelled"><?php _e('Cancelled', 'constant-contact-api'); ?></h3>
<?php
	include('registrants.php');
?>
	<p class="submit"><a href="<?php echo add_query_arg('refresh', 'registrants').'#reg_cancelled'; ?>" class="button-secondary alignright" title="Event registrants data is stored for 1 hour. Refresh data now."><?php _e('Refresh Cancelled', 'constant-contact-api'); ?></a></p>
<?php
} // End empty $_cancelled