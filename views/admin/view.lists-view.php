<table class="wp-list-table widefat fixed striped ctct_table" cellspacing="0">
	<thead>
		<tr>
			<th scope="col" class="name column-primary manage-column column-name"><?php _e('Name', 'ctct'); ?></th>
			<th scope="col" class="id manage-column column-name"><?php _e('List ID', 'ctct'); ?></th>
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
			<td class="column-name column-primary" data-colname="<?php esc_attr_e( 'Name', 'ctct' ); ?>">
				<?php echo $List->get('name', true);?>
				<button type="button" class="toggle-row"><span class="screen-reader-text"><?php esc_html_e('Show more details', 'ctct'); ?></span></button>
			</td>
			<td class="column-id" data-colname="<?php esc_attr_e( 'List ID', 'ctct' ); ?>">
				<?php echo esc_html( $List->get('id') ); ?>
			</td>
			<td class="column-name" data-colname="<?php esc_attr_e( 'Contact Count', 'ctct' ); ?>">
				<?php echo esc_html( $List->get('contact_count') );?>
			</td>
			<td class="column-name">
				<a href="<?php
					echo esc_url( add_query_arg(array('view' => $List->id), remove_query_arg('add')) );
				?>" class="button view-new-h2" title="view"><?php esc_html_e('View Contacts', 'ctct'); ?></a>
			</td>
		</tr>
	<?php
}

?>
	</tbody>
</table>