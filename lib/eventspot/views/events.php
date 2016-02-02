
<table class="wp-list-table widefat striped ctct_table" cellspacing="0">
	<thead>
		<tr>
			<th scope="col" id="name" class="manage-column column-primary column-title"><?php _e('Name', 'ctct'); ?></th>
			<th scope="col" id="title" class="manage-column"><?php _e('Title', 'ctct'); ?></th>
			<th scope="col" id="eventid" class="manage-column"><?php _e('Shortcode', 'ctct'); ?> <span class="ctct_help cc_tip" title="<?php _e('Use the ID inside the [eventspot] shortcode to display a single event in your post or page content; for example: [eventspot id=\'abc1244\' /]', 'ctct'); ?>">?</span></th>
			<?php if(!isset($_GET['status']) || $_GET['status'] == 'all') {?>
			<th scope="col" id="status" class="manage-column"><?php _e('Status', 'ctct'); ?></th>
			<?php } ?>
			<th scope="col" id="start" class="manage-column column-date"><?php _e('Start', 'ctct'); ?></th>
			<th scope="col" id="registered" class="manage-column"><?php _e('# Registered', 'ctct'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	if(empty($events)) { ?>
		<tr>
			<td colspan="6">
				<h3><?php esc_html_e('No events found.', 'ctct'); ?></h3>
			</td>
		</tr>
	<?php
	} else {

		$alt = 'alt';
		$i   = 0;
		foreach ( $events as $id => $v ) {
			$i ++;
			if ( $alt == 'alt' ) {
				$alt = '';
			} else {
				$alt = 'alt';
			}
			?>
			<tr id="event-<?php echo $i; ?>" valign="middle" class="<?php echo $alt; ?>"
			    data-colname="<?php esc_attr_e( 'Name', 'ctct' ); ?>">
				<td class="manage-column column-title column-primary">
					<strong><a class="row-title"
					           href="<?php echo add_query_arg( 'view', constant_contact_get_id_from_object( $v ), remove_query_arg( 'refresh' ) ); ?>"
					           title="<?php echo esc_html( $v->name ) . ' - ' . esc_html( $v->title ); ?>"><?php echo esc_html( $v->name ); ?></a></strong>
					<button type="button" class="toggle-row"><span
							class="screen-reader-text"><?php esc_html_e( 'Show more details', 'ctct' ); ?></span>
					</button>
				</td>
				<td class="manage-column" data-colname="<?php esc_attr_e( 'Title', 'ctct' ); ?>">
					<?php echo esc_html( $v->title ); ?>
				</td>
				<td class="manage-column column-shortcode"
				    data-colname="<?php esc_attr_e( 'Shortcode', 'ctct' ); ?>">
					<input type="text" class="widefat code" readonly="readonly"
					       value="<?php esc_attr_e( sprintf( '[eventspot id="%s" /]', constant_contact_get_id_from_object( $v ) ), 'ctct' ); ?>"/>
				</td>
				<?php if ( ! isset( $_GET['status'] ) || $_GET['status'] == 'all' ) { ?>
					<td class="column-role" data-colname="<?php esc_attr_e( 'Status', 'ctct' ); ?>"><?php
						echo ucwords( strtolower( esc_html( $v->status ) ) );
						?></td>
				<?php } ?>
				<td class="manage-column"
				    data-colname="<?php esc_attr_e( 'Start', 'ctct' ); ?>"><?php
					echo( isset( $v->startDate ) ? constant_contact_event_date( $v->startDate ) : esc_html__( 'None', 'ctct' ) );
					?></td>
				<td class="manage-column"
				    data-colname="<?php esc_attr_e( '# Registered', 'ctct' ); ?>"><?php
					echo_if_not_empty( esc_html( $v->registered ), 0 );
					?></td>
			</tr>
			<?php
			flush();
		}
	}
	?>
	</tbody>
</table>