
<table class="ctct_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th scope="col" id="name" class="manage-column column-title" style=""><?php _e('Name', 'ctct'); ?></th>
			<th scope="col" id="title" class="manage-column column-title" style=""><?php _e('Title', 'ctct'); ?></th>
			<th scope="col" id="eventid" class="manage-column column-id" style=""><?php _e('Shortcode', 'ctct'); ?> <span class="ctct_help cc_tip" title="<?php _e('Use the ID inside the [eventspot] shortcode to display a single event in your post or page content; for example: [eventspot id=\'abc1244\' /]', 'ctct'); ?>">?</span></th>
			<?php if(!isset($_GET['status']) || $_GET['status'] == 'all') {?>
			<th scope="col" id="status" class="manage-column column-date" style=""><?php _e('Status', 'ctct'); ?></th>
			<?php } ?>
			<th scope="col" id="start" class="manage-column column-author" style=""><?php _e('Start', 'ctct'); ?></th>
			<th scope="col" id="registered" class="manage-column column-date" style=""><?php _e('# Registered', 'ctct'); ?></th>
			<!-- <th scope="col" id="cancelled" class="manage-column column-date" style=""># Cancelled</th> -->
		</tr>
	</thead>
	<tbody>
	<?php
	if(empty($events)) { ?>
		<tr><td colspan="6">
		<h3><?php _e('No events found.', 'ctct'); ?></h3>
		</td></tr></table>
	<?php
		return;
	}

	$alt = 'alt'; $i = 0;
	foreach($events as $id => $v) {
		$i++;
		if($alt == 'alt') { $alt = '';} else { $alt = 'alt'; }
		?>
		<tr id="event-<?php echo $i; ?>" valign="middle"  class="<?php echo $alt; ?>">
			<td class="column-name post-title wrap">
				<strong><a class="row-title" href="<?php echo add_query_arg('view', constant_contact_get_id_from_object($v), remove_query_arg('refresh')); ?>" title="<?php echo esc_html($v->name).' - '.esc_html($v->title); ?>"><?php echo esc_html($v->name); ?></a></strong>
			</td>
			<td class="column-title post-title wrap">
				<?php echo $v->title; ?>
			</td>
			<td class="column-title column-shortcode wrap">
				<input type="text" class="widefat" readonly="readonly" value="<?php esc_attr_e(sprintf('[eventspot id="%s" /]', constant_contact_get_id_from_object($v)), 'ctct'); ?>" />
			</td>
	<?php if(!isset($_GET['status']) || $_GET['status'] == 'all') {?>
			<td class="column-role wrap">
				<?php echo ucwords(strtolower(esc_html($v->status))); ?>
			</td>
	<?php } ?>
			<td class="column-role column-startdate wrap">
				<?php echo (isset($v->startDate) ? constant_contact_event_date($v->startDate) : __('None', 'ctct')); ?>
			</td>
			<td class="column-id column-count wrap">
				<?php echo_if_not_empty($v->registered,0); ?>
			</td>
		</tr>
		<?php
		flush();
	}
	?></tbody>
</table>