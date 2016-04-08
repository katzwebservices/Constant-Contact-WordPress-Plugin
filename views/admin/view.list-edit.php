
<h3><?php echo 'List: ';?> <a href="<?php echo esc_url( add_query_arg(array('edit' => $List->id), remove_query_arg('view')) ); ?>" class="button edit-new-h2" title="edit">edit</a></h3>
<table class="widefat fixed ctct_table" cellspacing="0">
    <thead>
        <th scope="col" class="column-name"><?php _e('Name', 'constant-contact-api'); ?></th>
        <th scope="col" class="column-title"><?php _e('Data', 'constant-contact-api'); ?></th>
    </thead>
    <tbody>
        <tr>

    </tbody>
</table>