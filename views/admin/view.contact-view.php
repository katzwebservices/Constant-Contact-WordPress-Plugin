<h2 class="fittext">
<?php
    echo kws_has_avatar($Contact->get('email_address')) ? '<span style="float:left; margin-right:10px;">'.get_avatar($Contact->get('email_address'), 50, '404', $Contact->get('name')).'</span>' : '';
?>
    <?php echo $Contact->get('full_name'); ?>
</h2>

<div class="clear component-summary">
<?php
$i = 1;

// Create summary "Sheets"
foreach($summary as $k => $v) {

    // Spam Count may be null
    if( is_null( $v ) ) { continue; }

    echo '<dl class="'.$k.' summary-'.$i.'">
            <dt>'.ucwords(str_replace('_', ' ', $k)).'</dt>
            <dd>'.$v.'</dd>
        </dl>';
    $i++;
}
?>
</div>

<table class="widefat clear fixed ctct_table" cellspacing="0">
    <thead>
        <th scope="col" class="column-name"><?php _e('Name', 'ctct'); ?></th>
        <th scope="col" class="column-title"><?php _e('Data', 'ctct'); ?></th>
    </thead>
    <tbody>
        <?php

    $alt = ''; $html = '';
    foreach ($Contact as $key => $value) {
        $alt = empty($alt) ? ' class="alt"' : '';

        #if(!$Contact->is_editable($key)) { continue; }

        $html .= '<tr'.$alt.'>';
        $html .= sprintf('<th scope="row" valign="top" class="column-name">%s</th>', $Contact->getLabel($key));
        $html .= '<td>';

        if(is_array($value)) {
            switch($key) {
                case 'lists':

                    $html .= KWSContactList::outputHTML('all', array(
                        'type' => 'checkboxes',
                        'checked' => $Contact->get('lists', true)
                    ));

                    break;
                case 'addresses':
                    if($personal = $Contact->get('personal_address', true)) {
                        $html .= sprintf('<h3>%s</h3> <div>%s</div>', __('Personal Address', 'ctct'), $personal);
                    }

                    if($business = $Contact->get('business_address', true)) {
                        $html .= sprintf('<h3>%s</h3> <div>%s</div>', __('Business Address', 'ctct'), $business);
                    }
                    break;
                case 'email_addresses':
                    $html .= sprintf('<span class="editable" data-name="'.$key.'">%s</span>', $Contact->get('email_address'));
                    break;
                case 'notes':
                    // Constant Contact got rid of Notes for now.
                    $html .= $Contact->is_editable($key) ? sprintf('<span class="editable" data-name="'.$key.'">%s</span>', $Contact->get('notes')) : $Contact->get('notes');
                    break;
                case 'custom_fields':
                    $i = 1;
                    $html .= '<ul class="ctct-checkboxes">';
                    while($i < 16) {
                        $html .= sprintf('<li>Custom Field %1$s <span class="editable" data-name="CustomField%d">%s</span></li>', $i, $Contact->get('CustomField'.$i));
                        $i++;
                    }
                    $html .= '</ul>';
                    break;
                default:
                    $html .= $Contact->get($key, true);
                    break;
            }
        } else {
            $html .= sprintf('<span%s>%s</span></td>', ($Contact->is_editable($key) ? ' class="editable" data-name="'.$key.'"' : ' class="not-editable"'), $Contact->get($key));
        }

        $html .= '</td>
        </tr>';
    }

    echo $html;
?>
</tbody>
</table>