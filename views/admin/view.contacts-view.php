<p class="submit"><a href="<?php echo esc_url( add_query_arg('refresh', 'contacts') ); ?>" class="button-secondary alignright"><?php esc_html_e('Refresh Contacts', 'ctct'); ?></a></p>

<table class="widefat fixed users ctct_table" cellspacing="0">
    <thead>
        <tr>
            <th scope="col" id="email" class="manage-column column-name" style=""><?php _e('Email Address', 'ctct'); ?></th>
            <th scope="col" id="name" class="manage-column column-name" style=""><?php _e('Name', 'ctct'); ?></th>
            <th scope="col" id="status" class="manage-column column-name" style=""><?php _e('Status', 'ctct'); ?></th>
            <th scope="col" id="id" class="manage-column column-name" style=""><?php _e('View or Edit', 'ctct'); ?></th>
        </tr>
    </thead>
    <tbody>

<?php

foreach ($Contacts as $Contact ) {
        $Contact = new KWSContact($Contact);
        $Admin_Contacts = new CTCT_Admin_Contacts;
        $alt = empty( $alt ) ? 'class="alt"' : '';
    ?>
        <tr <?php echo $alt; ?>>
            <td class="email column-email">
                <a href="<?php
                    echo esc_url( add_query_arg(array(
                        'page' => $Admin_Contacts->getKey(),
                        'view' => $Contact->id
                    ), admin_url('admin.php')) );

                ?>" title="<?php _e('View Contact', 'ctct'); ?>"><?php echo $Contact->get('email_address');?></a>
            </td>
            <td class="column-name">
                <?php echo $Contact->get('name'); ?>
            </td>
            <td class="column-status">
                <?php
                    echo $Contact->get('status');
                ?>
            </td>
            <td class="column-edit">
            	<div class="button-group">
	                <a href="<?php
	                    echo esc_url( add_query_arg(array('page' => $Admin_Contacts->getKey(), 'view' => $Contact->id), admin_url('admin.php')) );
	                ?>" class="button view-new-h2" title="view"><?php _e('view', 'ctct'); ?></a>
	                <a href="<?php
	                    echo esc_url( add_query_arg(array('page' => $Admin_Contacts->getKey(), 'edit' => $Contact->id), admin_url('admin.php')) );
	                ?>" class="button edit-new-h2" title="edit"><?php _e('edit', 'ctct'); ?></a>
	            </div>
            </td>
        </tr>
    <?php
}
?>
    <tr class="show-if-empty">
        <td colspan="4"><h3><?php _e('There are no contacts with this status.', 'ctct');?></h3></td>
    </tr>
    </tbody>
</table>