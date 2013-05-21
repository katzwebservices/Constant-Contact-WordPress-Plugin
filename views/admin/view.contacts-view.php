<table class="wp-list-table widefat fixed users ctct_table" cellspacing="0">
    <thead>
        <tr>
            <th scope="col" id="email" class="manage-column column-name" style=""><?php _e('Email Address', 'constant-contact-api'); ?></th>
            <th scope="col" id="name" class="manage-column column-name" style=""><?php _e('Name', 'constant-contact-api'); ?></th>
            <th scope="col" id="status" class="manage-column column-name" style=""><?php _e('Status', 'constant-contact-api'); ?></th>
            <th scope="col" id="id" class="manage-column column-name" style=""><?php _e('View or Edit', 'constant-contact-api'); ?></th>
        </tr>
    </thead>
    <tbody>

<?php

foreach ($Contacts as $Contact ) {
        $Contact = new KWSContact($Contact);
    ?>
        <tr>
            <td class="email column-email">
                <a href="<?php
                    echo add_query_arg(array('page' => CTCT_Admin_Contacts::getKey(), 'view' => $Contact->id), admin_url('admin.php'));
                ?>" title="<?php _e('View Contact', 'constant-contact-api'); ?>"><?php echo $Contact->get('email_address');?></a>
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
	                    echo add_query_arg(array('page' => CTCT_Admin_Contacts::getKey(), 'view' => $Contact->id), admin_url('admin.php'));
	                ?>" class="button view-new-h2" title="view"><?php _e('view', 'constant-contact-api'); ?></a>
	                <a href="<?php
	                    echo add_query_arg(array('page' => CTCT_Admin_Contacts::getKey(), 'edit' => $Contact->id), admin_url('admin.php'));
	                ?>" class="button edit-new-h2" title="edit"><?php _e('edit', 'constant-contact-api'); ?></a>
	            </div>
            </td>
        </tr>
    <?php
}
?>
    <tr class="show-if-empty">
        <td colspan="4"><h3><?php _e('There are no contacts with this status.', 'constant-contact-api');?></h3></td>
    </tr>
    </tbody>
</table>