	<p style="color: #777;font-family: Georgia, 'Times New Roman', 'Bitstream Charter', Times, serif;font-size: 13px;font-style: italic;padding: 0;margin: 15px 0 0 0;"><?php echo $title; ?></p>
	<table class="widefat fixed ctct_table" cellspacing="0" style="border:0px;">
			<thead>
				<tr>
					<td style="text-align:left; padding:8px 0!important; font-weight:bold;" id="title" class="manage-column column-name" style="">Event Name</td>
					<td style="text-align:center; padding:8px 0!important; font-weight:bold;" id="registered" class="manage-column column-name" style=""># Registered</td>
					<td style="text-align:center; padding:8px 0!important; font-weight:bold;" id="cancelled" class="manage-column column-name" style=""># Cancelled</td>
					<td style="text-align:left; padding:8px 0!important; font-weight:bold;" id="details" class="manage-column column-name" style="">Last Registrant</td>
				</tr>
			</thead>
			<tbody>
			<?php
			if(empty($events)) {?>
				<tr><td colspan="6">
				<h3>No events found&hellip;</h3>
				</td></tr></table>
			<?php
				return;
			}
			foreach($events as $id => $v) {
				$v = $this->old_api->getEventDetails($v); // The cancelled registrants count won't work otherwise...
			?>
			<tr class='author-self status-inherit' valign="top">
				<td class="column-title" style="padding:8px 0;">
					<a href="<?php echo add_query_arg('view', constant_contact_get_id_from_object($v), remove_query_arg('refresh', admin_url('admin.php?page=constant-contact-events'))); ?>" style="display:inline;white-space: nowrap; width: 100%; overflow: hidden; text-overflow: ellipsis; font-weight:bold;" title="<?php echo esc_html($v->name).' - '.esc_html($v->title); ?>"><?php echo esc_html($v->name); ?></a>
				</td>
				<td class="column-date" style="padding:8px 0; text-align:center;">
					<a href="<?php echo add_query_arg('view', constant_contact_get_id_from_object($v), remove_query_arg('refresh', admin_url('admin.php?page=constant-contact-events#registrants'))); ?>" style="display:block; width:100%; line-height:1.4;"><?php echo_if_not_empty((int)$v->registered,0); ?></a>
				</td>
				<td class="column-date" style="padding:8px 0; text-align:center;">
					<a href="<?php echo add_query_arg('view', constant_contact_get_id_from_object($v), remove_query_arg('refresh', admin_url('admin.php?page=constant-contact-events#cancelled'))); ?>" style="display:block; width:100%; line-height:1.4;"><?php echo_if_not_empty((int)$v->cancelledCount,0); ?></a>
				</td>
				<td class="column-date" style="padding:8px 0; text-align:left;">
					<?php echo $this->latest_registrant($v); ?>
				</td>
			</tr>
<?php } ?>
		</table>