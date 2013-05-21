<?php
Exceptional::$controller = 'view.campaigns-view.php';
Exceptional::$action = 'view campaigns';
?>
<table class="widefat ctct_table" cellspacing="0">
    <thead>
        <tr>
            <th scope="col" id="date" class="manage-column column-name" style=""><?php _e('Name', 'constant-contact-api'); ?></th>
            <th scope="col" id="contact-count" class="manage-column column-name" style=""><?php _e('Modified Date', 'constant-contact-api'); ?></th>
            <th scope="col" id="view" class="manage-column column-name" style=""><?php _e('View or Edit', 'constant-contact-api'); ?></th>
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
                <td class="column-name">
                    <?php echo htmlentities($result->name);?>
                </td>
                <td class="column-name">
                    <?php
                        echo kws_format_date($result->modified_date);
                    ?>
                </td>
                <td class="column-name">
		            <div class="button-group">
		                <a href="<?php
		                    echo add_query_arg(array('view' => $result->id), remove_query_arg('add'));
		                ?>" class="button view-new-h2" title="view">View</a>
		                <a href="<?php
		                    echo add_query_arg(array('edit' => $result->id), remove_query_arg('add'));
		                ?>" class="button view-new-h2" title="edit">Edit</a>
		            </div>
                </td>
            </tr>
        <?php
    }
}
?>
    </tbody>
</table>