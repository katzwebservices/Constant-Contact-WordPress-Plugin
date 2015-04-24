<table class="widefat ctct_table" cellspacing="0">
    <thead>
        <tr>
            <th scope="col" id="date" class="manage-column column-name" style=""><?php esc_html_e('Name', 'ctct'); ?></th>
            <th scope="col" id="contact-count" class="manage-column column-name" style=""><?php esc_html_e('Modified Date', 'ctct'); ?></th>
            <th scope="col" id="view" class="manage-column column-name" style=""><?php esc_html_e('View Campaign', 'ctct'); ?></th>
        </tr>
    </thead>
    <tbody>
<?php

if(empty($Campaigns)) {
    ?>
    <tr><td colspan="4"><h3><?php esc_html_e('No results.', 'ctct'); ?></h3></td></tr>
    <?php
} else {
    foreach ($Campaigns as $result ) {
        $alt = empty( $alt ) ? 'class="alt"' : '';
        ?>
            <tr <?php echo $alt; ?>>
                <td class="column-name post-title wrap"><strong><a href="<?php echo esc_url( add_query_arg(array('view' => $result->id), remove_query_arg('add'))); ?>"><?php echo esc_html($result->name); ?></a></strong></td>
                <td class="column-name wrap"><?php echo kws_format_date($result->modified_date); ?></td>
                <td class="column-name wrap">
		            <div class="button-group">
                        <a href="https://ui.constantcontact.com/rnavmap/evaluate.rnav/?activepage=ecampaign.view&amp;pageName=ecampaign.view&amp;agent.uid=<?php echo esc_attr( $result->id ); ?>&amp;action=edit" class="button button-secondary" target="_blank" title="<?php printf(esc_html__('View "%s" on ConstantContact.com', 'ctct'), $result->name ); ?>"><?php esc_html_e( 'View on ConstantContact.com', 'ctct'); ?></a>
		            </div>
                </td>
            </tr>
        <?php
    }
}
?>
    </tbody>
</table>