
<h3><?php echo 'List: ';?> <a href="<?php echo esc_url( add_query_arg(array('edit' => $List->id), remove_query_arg('view')) ); ?>" class="button edit-new-h2" title="edit">edit</a></h3>
<table class="widefat fixed ctct_table" cellspacing="0">
    <thead>
        <th scope="col" class="column-name"><?php _e('Name', 'ctct'); ?></th>
        <th scope="col" class="column-title"><?php _e('Data', 'ctct'); ?></th>
    </thead>
    <tbody>
        <tr>

    </tbody>
</table>