<table class="widefat ctct_table" cellspacing="0">
	<thead>
		<tr>
			<th scope="col" class="manage-column column-name wrap"><?php _e('Name', 'ctct'); ?></th>
			<th scope="col" class="manage-column column-name wrap"><?php _e('Email', 'ctct'); ?></th>
			<th scope="col" class="manage-column column-name wrap"><?php _e('Registration Date', 'ctct'); ?></th>
			<th scope="col" class="manage-column column-name wrap"><?php _e('Guest Count', 'ctct'); ?></th>
			<th scope="col" class="manage-column column-name wrap"><?php _e('Payment Status', 'ctct'); ?></th>
			<th scope="col" class="manage-column column-name wrap"><?php _e('Details', 'ctct'); ?></th>
	</thead>
	<tbody>
		<?php
		$alt = '';
		foreach($_registrants as $reg) {
			if($alt == 'alt') { $alt = '';} else { $alt = 'alt'; }
		?>
		<tr <?php echo $alt; ?>>
			<td><?php echo $reg->title; ?></td>
			<td><?php echo_if_not_empty($reg->email,'', "<a href='mailto:{$reg->email}'>{$reg->email}</a>"); ?></td>
			<td><?php echo_if_not_empty($reg->registrationDate, __('None', 'ctct'), constant_contact_event_date($reg->registrationDate)); ?></td>
			<td><?php echo_if_not_empty($reg->guestCount,1); ?></td>
			<td><?php echo $reg->paymentStatus; ?></td>
			<td><a href="<?php echo add_query_arg('registrant', constant_contact_get_id_from_object($reg), remove_query_arg('refresh')); ?>">View Details</a></td>
		</tr>
		<?php } ?>
	</tbody>
</table>