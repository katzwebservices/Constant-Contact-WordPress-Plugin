<table class="widefat ctct_table fixed" cellspacing="0">
	<thead>
		<tr>
			<th scope="col" class="id manage-column column-name"><?php _e('List ID', 'ctct'); ?></th>
			<th scope="col" class="name manage-column column-name"><?php _e('Name', 'ctct'); ?></th>
			<th scope="col" class="count manage-column column-name"><?php _e('Contact Count', 'ctct'); ?></th>
			<th scope="col" class="view manage-column column-name"><?php _e('View List Contacts', 'ctct'); ?></th>
		</tr>
	</thead>
	<tbody>

<?php

foreach ( (array)$Lists as $List ) {
		$List = new KWSContactList($List);
		$alt = empty( $alt ) ? 'class="alt"' : '';
	?>
		<tr <?php echo $alt; ?>>
			<td class="column-id">
				<?php echo $List->get('id'); ?>
			</td>
			<td class="column-name">
				<?php echo $List->get('name', true);?>
			</td>
			<td class="column-name">
				<?php echo $List->get('contact_count');?>
			</td>
			<td class="column-name">
				<a href="<?php
					echo add_query_arg(array('view' => $List->id), remove_query_arg('add'));
				?>" class="button view-new-h2" title="view"><?php _e('View Contacts', 'ctct'); ?></a>
			</td>
		</tr>
	<?php
}

?>
	</tbody>
</table>