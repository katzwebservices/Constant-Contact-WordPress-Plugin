	<h4><?php echo esc_html( $title ); ?></h4>
	<table class="wp-list-table widefat striped ctct_table stuffbox">
		<thead>
			<tr>
				<th class="manage-column column-primary"><?php esc_html_e('Event Name', 'constant-contact-api'); ?></th>
				<th class="manage-column column-tags"><?php esc_html_e('# Registered', 'constant-contact-api' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			if(empty($events)) {?>
				<tr><td colspan="6">
				<h3><?php esc_html_e('No events found.', 'constant-contact-api'); ?></h3>
				</td></tr></table>
			<?php
				return;
			}

			foreach($events as $id => $v) {
			?>
			<tr>
				<th scope="row" class="manage-column">
					<a href="<?php echo add_query_arg('view', $v->id, remove_query_arg('refresh', admin_url('admin.php?page=constant-contact-events'))); ?>" style="display:inline;white-space: nowrap; width: 100%; overflow: hidden; text-overflow: ellipsis; font-weight:bold;" title="<?php echo esc_html( $v->name ).' - '.esc_html($v->title); ?>"><?php echo esc_html($v->name); ?></a>
				</th>
				<td class="manage-column">
					<a href="<?php echo add_query_arg('view', $v->id, remove_query_arg('refresh', admin_url('admin.php?page=constant-contact-events#registrants'))); ?>" style="display:block; width:100%; line-height:1.4;"><?php echo intval( $v->total_registered_count ); ?></a>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>