<h2 class="ctct-page-name">
<?php
    echo kws_has_avatar($Contact->get('email_address')) ? '<span style="float:left; margin-right:10px;">'.get_avatar($Contact->get('email_address'), 50, '404', $Contact->get('name')).'</span>' : '';
?>
    <?php echo $Contact->get('full_name'); ?>
</h2>
<?php

    /**
     * @global string|null $user_details If user exists in system, HTML link to their site profile in WordPress. Otherwise, `NULL`
     * @see CTCT_Admin_Contacts::generate_user_details
     * @since 3.2
     */
    echo $user_details;

?>

<div class="clear component-summary">
<?php
    /**
     * @global string $summary_report HTML of opens/bounces/clicks/etc.
     * @see CTCT_Admin_Contacts::generate_summary_report
     * @since 3.2
     */
    echo $summary_report;

?>
</div>

<table class="wp-list-table widefat fixed striped ctct_table" cellspacing="0">
    <thead>
        <th scope="col" class="manage-column column-title"><?php _e('Name', 'constant-contact-api'); ?></th>
        <th scope="col" class="manage-column column-title"><?php _e('Data', 'constant-contact-api'); ?></th>
    </thead>
    <tbody>
        <?php

    $alt = ''; $html = '';
    foreach ($Contact as $key => $value) {
        $alt = empty($alt) ? ' class="alt"' : '';

        #if(!$Contact->is_editable($key)) { continue; }

        $html .= '<tr'.$alt.'>';
        $html .= sprintf('<th scope="row" valign="top" class="manage-column column-title">%s</th>', $Contact->getLabel($key));
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
                        $html .= sprintf('<h3>%s</h3> <div>%s</div>', __('Personal Address', 'constant-contact-api'), $personal);
                    }

                    if($business = $Contact->get('business_address', true)) {
                        $html .= sprintf('<h3>%s</h3> <div>%s</div>', __('Business Address', 'constant-contact-api'), $business);
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
        } else if( is_bool( $value ) ) {
            $html .= $value ? esc_html__('Yes', 'constant-contact-api') : esc_html__('No', 'constant-contact-api');
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