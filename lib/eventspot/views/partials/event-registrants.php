<?php
/**
 * @global \Ctct\Components\EventSpot\Registrant\Registrant[] $_registrants
 */
?>
<table class="wp-list-table widefat striped ctct_table">
	<thead>
		<tr>
			<th scope="col" class="manage-column column-name wrap column-primary"><?php esc_html_e('Name', 'constant-contact-api'); ?></th>
			<th scope="col" class="manage-column column-name wrap"><?php esc_html_e('Email', 'constant-contact-api'); ?></th>
			<th scope="col" class="manage-column column-name wrap"><?php esc_html_e('Registration Date', 'constant-contact-api'); ?></th>
			<th scope="col" class="manage-column column-name wrap"><?php esc_html_e('Guest Count', 'constant-contact-api'); ?></th>
			<th scope="col" class="manage-column column-name wrap"><?php esc_html_e('Payment Status', 'constant-contact-api'); ?></th>
			<th scope="col" class="manage-column column-name wrap"><?php esc_html_e('Details', 'constant-contact-api'); ?></th>
	</thead>
	<tbody>
		<?php
		$alt = '';

		foreach($_registrants as $reg) {

			if($alt == 'alt') { $alt = '';} else { $alt = 'alt'; }
		?>
		<tr <?php echo $alt; ?>>
			<td class="manage-column column-name column-primary"><?php echo constant_contact_registrant_name( $reg ); ?><button type="button" class="toggle-row"><span
						class="screen-reader-text"><?php esc_html_e( 'Show more details', 'constant-contact-api' ); ?></span>
				</button></td>
			<td class="manage-column" data-colname="<?php esc_html_e('Email', 'constant-contact-api' ); ?>"><?php echo_if_not_empty($reg->email,'', "<a href='mailto:{$reg->email}'>{$reg->email}</a>"); ?></td>
			<td class="manage-column" data-colname="<?php esc_html_e('Date', 'constant-contact-api' ); ?>"><?php echo_if_not_empty($reg->registration_date, __('None', 'constant-contact-api'), constant_contact_event_date($reg->registration_date)); ?></td>
			<td class="manage-column" data-colname="<?php esc_html_e('Guests', 'constant-contact-api' ); ?>"><?php echo_if_not_empty($reg->guest_count,1); ?></td>
			<td class="manage-column" data-colname="<?php esc_html_e('Payment', 'constant-contact-api' ); ?>"><?php if( isset( $reg->payment_status ) ) { echo_if_not_empty($reg->payment_status, esc_html__('N/A', 'constant-contact-api'), esc_html( $reg->payment_status ) ); } ?></td>
			<td class="manage-column" data-colname="<?php esc_html_e('Details', 'constant-contact-api' ); ?>"><a href="<?php echo add_query_arg('registrant', constant_contact_get_id_from_object($reg), remove_query_arg('refresh')); ?>"><?php esc_html_e('View Details', 'constant-contact-api'); ?></a></td>
		</tr>
		<?php } ?>
	</tbody>
</table>