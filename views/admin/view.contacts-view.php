<?php
/**
 * @uses CTCT_Admin_Lists::single
 * @global Ctct\Components\Contacts\Contact[] $Contacts
 */
?>
<div class="tablenav top">
    <div class="alignleft actions">
        <?php kws_print_modified_since_filter( __('Date Modified', 'ctct') ); ?>
    </div>
    <div class="alignright">
        <a href="<?php echo esc_url( add_query_arg('refresh', 'contacts') ); ?>" class="button button-secondary alignright button-small"><?php esc_html_e('Refresh Contacts', 'ctct'); ?></a>
    </div>
</div>

<table class="wp-list-table widefat fixed striped ctct_table" cellspacing="0">
    <thead>
        <tr>
            <th scope="col" id="email" class="manage-column column-name column-primary" style=""><?php esc_html_e('Email Address', 'ctct'); ?></th>
            <th scope="col" id="name" class="manage-column column-name" style=""><?php esc_html_e('Name', 'ctct'); ?></th>
            <th scope="col" id="name" class="manage-column column-name" style=""><?php esc_html_e('Date Added', 'ctct'); ?></th>
            <th scope="col" id="status" class="manage-column column-name" style=""><?php esc_html_e('Status', 'ctct'); ?></th>
            <th scope="col" id="id" class="manage-column column-name" style=""><?php esc_html_e('View or Edit', 'ctct'); ?></th>
        </tr>
    </thead>
    <tbody>

<?php

    if( empty( $Contacts ) ) {
        printf( '<tr><td colspan="5"><div class="no-results"><h3>%s</h3></div></td></tr>', esc_html__( 'No contacts match this status.', 'ctct' ) );
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

                ?>" title="<?php _e('View Contact', 'ctct'); ?>"><?php echo $Contact->get('email_address');?></a>

                <button type="button" class="toggle-row"><span class="screen-reader-text"><?php esc_html_e('Show more details', 'ctct'); ?></span></button>
            </td>
            <td class="manage-column column-name" data-colname="<?php esc_attr_e( 'Name', 'ctct' ); ?>">
                <?php echo $Contact->get('name'); ?>
            </td>
            <td class="manage-column column-name" data-colname="<?php esc_attr_e( 'Date Added', 'ctct' ); ?>">
                <?php echo $Contact->get('created_date', true ); ?>
            </td>
            <td class="manage-column column-name" data-colname="<?php esc_attr_e( 'Status', 'ctct' ); ?>">
                <?php
                    echo $Contact->get('status');
                ?>
            </td>
            <td class="manage-column column-edit" data-colname="<?php esc_attr_e( 'Actions', 'ctct' ); ?>">
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