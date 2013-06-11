<?php
Exceptional::$controller = 'view.campaigns-view.php';
Exceptional::$action = 'view campaigns';
?>
<table class="widefat ctct_table" cellspacing="0">
    <thead>
        <tr>
            <th scope="col" id="date" class="manage-column column-name" style=""><?php _e('Name', 'constant-contact-api'); ?></th>
            <th scope="col" id="contact-count" class="manage-column column-name" style=""><?php _e('Modified Date', 'constant-contact-api'); ?></th>
            <th scope="col" id="view" class="manage-column column-name" style=""><?php _e('View Campaign', 'constant-contact-api'); ?></th>
        </tr>
    </thead>
    <tbody>
<?php

if(empty($Campaigns)) {
    ?>
    <tr><td colspan="4"><?php _e('No Results', 'constant-contact-api'); ?></td></tr>
    <?php
} else {
    foreach ($Campaigns as $result ) {
        ?>
            <tr>
                <td class="column-name post-title wrap"><strong><a href="<?php echo add_query_arg(array('view' => $result->id), remove_query_arg('add')); ?>"><?php echo htmlentities($result->name); ?></a></strong></td>
                <td class="column-name wrap"><?php echo kws_format_date($result->modified_date); ?></td>
                <td class="column-name wrap">
		            <div class="button-group">
		            	<a href="<?php echo add_query_arg(array('view' => $result->id), remove_query_arg('add')); ?>" class="button view-new-h2" title="<?php _e('View Campaign', 'constant-contact-api'); ?>"><?php _e('View', 'constant-contact-api'); ?> </a>
		            </div>
                </td>
            </tr>
        <?php
    }
}
?>
    </tbody>
</table>