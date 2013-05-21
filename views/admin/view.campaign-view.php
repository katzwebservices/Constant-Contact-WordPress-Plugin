<h2 class="fittext"><?php echo $Campaign->get('name'); ?></h2>
<div class="clear component-summary">
    <dl class="summary-1" style="width:20%; max-width:275px;">
        <dt><?php echo $Campaign->getLabel('status'); ?></dt>
        <dd><?php echo $Campaign->get('status', true); ?></dd>
    </dl>
    <dl class="summary-2" style="width:40%; max-width:475px;">
        <dt><?php echo $Campaign->getLabel('created_date'); ?></dt>
        <dd><?php echo kws_format_date($Campaign->get('created_date')); ?></dd>
    </dl>
</div>
<table class="widefat fixed ctct_table" cellspacing="0">
    <thead>
        <th scope="col" class="column-name"><?php _e('Name', 'constant-contact-api'); ?></th>
        <th scope="col" class="column-title"><?php _e('Data', 'constant-contact-api'); ?></th>
    </thead>
    <tbody>
        <?php

    $alt = ''; $html = '';
    foreach ($Campaign as $key => $value) {
        $alt = empty($alt) ? ' class="alt"' : '';

        if(empty($value)) { continue; }

        $html .= '<tr'.$alt.'>';

        if(is_array($value)) {
            switch($key) {
                case 'sent_to_contact_lists':
                case 'lists':
                    $html .= sprintf('<th scope="row" valign="top" class="column-name">%s</th>
                        <td>%s</td>', $Campaign->getLabel($key), KWSContactList::outputHTML($Campaign->get($key)));
                    break;
                default:
                    $html .= sprintf('<th scope="row" valign="top" class="column-name">%s</th>
                        <td>%s</td>', $Campaign->getLabel($key), print_r($Campaign->get($key), true));
                break;
            }
        } else {
            if(!is_string($Campaign->get($key))) { continue; }
            $html .= sprintf('<th scope="row" valign="top" class="column-name">%s</th>
                <td>%s</td>', $Campaign->getLabel($key), esc_html( $Campaign->get($key, true) ));
        }
        $html .= '
        </tr>';
    }

    echo $html;
?>
</tbody>
</table>