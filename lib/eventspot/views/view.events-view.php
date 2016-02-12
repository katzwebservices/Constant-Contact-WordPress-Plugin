<div class="tablenav top">
	<div class="alignright"><a href="<?php echo esc_url( add_query_arg('refresh', 'events') ); ?>" class="button-secondary alignright" title="<?php echo sprintf( esc_attr__('Event registrants data is stored for %s hours. Refresh data now.', 'ctct'), round(apply_filters('constant_contact_cache_age', 60 * 60 * 6 ) / 3600)); ?>"><?php esc_html_e('Refresh Events', 'ctct'); ?></a></div>
</div>
<table class="wp-list-table widefat striped ctct_table">
	<thead>
		<tr>
			<th scope="col" id="name" class="manage-column column-primary"><?php _e('Event Name', 'ctct'); ?></th>
			<th scope="col" id="title" class="manage-column column-title"><?php _e('Title', 'ctct'); ?></th>
			<th scope="col" id="eventid" class="manage-column"><?php _e('Shortcode', 'ctct'); ?> <span class="ctct_help cc_tip" title="<?php _e('Use the ID inside the [eventspot] shortcode to display a single event in your post or page content; for example: [eventspot id=\'abc1244\' /]', 'ctct'); ?>">?</span></th>
			<?php if(!isset($_GET['status']) || $_GET['status'] == 'all') {?>
			<th scope="col" id="status" class="manage-column"><?php _e('Status', 'ctct'); ?></th>
			<?php } ?>
			<th scope="col" id="start" class="manage-column"><?php _e('Start', 'ctct'); ?></th>
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
				<td class="manage-column column-primary">
					<strong><a class="row-title"
					           href="<?php echo add_query_arg( 'view', $v->id, remove_query_arg( array( 'refresh', 'registrant' ) ) ); ?>"
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
					<label><span class="screen-reader-text"><?php esc_html_e('Event embed shortcode', 'ctct'); ?></span><input type="text" class="widefat code" readonly="readonly"
					       value="<?php esc_attr_e( sprintf( '[eventspot id="%s" /]', $v->id ), 'ctct' ); ?>"/></label>
				</td>
				<?php if ( ! isset( $_GET['status'] ) || $_GET['status'] == 'all' ) { ?>
					<td class="column-role" data-colname="<?php esc_attr_e( 'Status', 'ctct' ); ?>"><?php
						echo ucwords( strtolower( esc_html( $v->status ) ) );
						?></td>
				<?php } ?>
				<td class="manage-column"
				    data-colname="<?php esc_attr_e( 'Start', 'ctct' ); ?>"><?php
					echo( isset( $v->start_date ) ? constant_contact_event_date( $v->start_date ) : esc_html__( 'None', 'ctct' ) );
					?></td>
				<td class="manage-column"
				    data-colname="<?php esc_attr_e( '# Registered', 'ctct' ); ?>"><?php
					echo_if_not_empty( esc_html( $v->total_registered_count ), 0 );
					?></td>
			</tr>
			<?php
			flush();
		}
	}
	?>
	</tbody>
</table>