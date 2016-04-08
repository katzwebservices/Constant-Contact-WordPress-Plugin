<?php
/**
 * @uses CTCT_Admin_Lists::single
 * @global Ctct\Components\Contacts\Contact[] $Contacts
 */
?>
<div class="tablenav top">
    <div class="alignleft actions">
        <?php kws_print_modified_since_filter( __('Date Modified', 'constant-contact-api') ); ?>
    </div>
    <div class="alignright">
        <a href="<?php echo esc_url( add_query_arg('refresh', 'contacts') ); ?>" class="button button-secondary alignright button-small"><?php esc_html_e('Refresh Contacts', 'constant-contact-api'); ?></a>
    </div>
</div>

<table class="wp-list-table widefat fixed striped ctct_table" cellspacing="0">
    <thead>
        <tr>
            <th scope="col" id="email" class="manage-column column-name column-primary" style=""><?php esc_html_e('Email Address', 'constant-contact-api'); ?></th>
            <th scope="col" id="name" class="manage-column column-name" style=""><?php esc_html_e('Name', 'constant-contact-api'); ?></th>
            <th scope="col" id="name" class="manage-column column-name" style=""><?php esc_html_e('Date Added', 'constant-contact-api'); ?></th>
            <th scope="col" id="status" class="manage-column column-name" style=""><?php esc_html_e('Status', 'constant-contact-api'); ?></th>
            <th scope="col" id="id" class="manage-column column-name" style=""><?php esc_html_e('View or Edit', 'constant-contact-api'); ?></th>
        </tr>
    </thead>
    <tbody>

<?php

    if( empty( $Contacts ) ) {
        printf( '<tr><td colspan="5"><div class="no-results"><h3>%s</h3></div></td></tr>', esc_html__( 'No contacts match this status.', 'constant-contact-api' ) );
    }

/**
 * @var Ctct\Components\Contacts\Contact[] $Contacts
 */
foreach ( $Contacts as $Contact ) {
#    var_dump( $Contact );

        /** @var KWSContact $Contact */
        $Contact = new KWSContact($Contact);
        $Admin_Contacts = new CTCT_Admin_Contacts;
        $alt = empty( $alt ) ? 'class="alt"' : '';
    ?>
        <tr <?php echo $alt; ?>>
            <td class="manage-column column-title column-primary">
                <a href="<?php
                    echo esc_url( add_query_arg(array(
                        'page' => $Admin_Contacts->getKey(),
                        'view' => $Contact->id
                    ), admin_url('admin.php')) );

                ?>" title="<?php _e('View Contact', 'constant-contact-api'); ?>"><?php echo $Contact->get('email_address');?></a>

                <button type="button" class="toggle-row"><span class="screen-reader-text"><?php esc_html_e('Show more details', 'constant-contact-api'); ?></span></button>
            </td>
            <td class="manage-column column-name" data-colname="<?php esc_attr_e( 'Name', 'constant-contact-api' ); ?>">
                <?php echo $Contact->get('name'); ?>
            </td>
            <td class="manage-column column-name" data-colname="<?php esc_attr_e( 'Date Added', 'constant-contact-api' ); ?>">
                <?php echo $Contact->get('created_date', true ); ?>
            </td>
            <td class="manage-column column-name" data-colname="<?php esc_attr_e( 'Status', 'constant-contact-api' ); ?>">
                <?php
                    echo $Contact->get('status');
                ?>
            </td>
            <td class="manage-column column-edit" data-colname="<?php esc_attr_e( 'Actions', 'constant-contact-api' ); ?>">
            	<div class="button-group">
	                <a href="<?php
	                    echo esc_url( add_query_arg(array('page' => $Admin_Contacts->getKey(), 'view' => $Contact->id), admin_url('admin.php')) );
	                ?>" class="button view-new-h2" title="view"><?php _e('view', 'constant-contact-api'); ?></a>
	                <a href="<?php
	                    echo esc_url( add_query_arg(array('page' => $Admin_Contacts->getKey(), 'edit' => $Contact->id), admin_url('admin.php')) );
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