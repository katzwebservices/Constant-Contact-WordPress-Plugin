<h2 class="fittext"><?php echo $Campaign->get('name'); ?></h2>
<div class="clear component-summary">
    <dl class="summary-1" style="width:20%; max-width:275px;">
        <dt><?php echo esc_html( $Campaign->getLabel('status') ); ?></dt>
        <dd><?php echo esc_html( $Campaign->get('status', true) ); ?></dd>
    </dl>
    <dl class="summary-2" style="width:40%; max-width:475px;">
        <dt><?php echo esc_html( $Campaign->getLabel('created_date') ); ?></dt>
        <dd><?php echo esc_html( kws_format_date($Campaign->get('created_date')) ); ?></dd>
    </dl>
</div>
<table class="widefat fixed ctct_table" cellspacing="0">
    <thead>
        <th scope="col" class="column-name"><?php esc_html_e('Name', 'ctct'); ?></th>
        <th scope="col" class="column-title"><?php esc_html_e( 'Data', 'ctct'); ?></th>
    </thead>
    <tbody>
        <?php
    $alt = ''; $html = '';
    foreach ($Campaign as $key => $value) {

        if(empty($value)) { continue; }

        $alt = empty($alt) ? ' class="alt"' : '';

        $html .= '<tr'.$alt.'>';

        if(is_array($value)) {
            switch($key) {
                case 'sent_to_contact_lists':
                case 'lists':
                    $html .= sprintf('<th scope="row" valign="top" class="column-name">%s</th>
                        <td>%s</td>', esc_html( $Campaign->getLabel($key) ), KWSContactList::outputHTML($Campaign->get($key), array('type' => 'ul')));
                    break;
                case 'click_through_details':
                    $clickThroughOutput = '';
                    if(!empty($value)) {
                        $clickThroughOutput = '<ul class="ul-disc">';
                        foreach((array)$value as $click) {
                            $clickThroughOutput .= '<li>';
                            $clickThroughOutput .= '<a class="block" href="'.$click->url.'">'.$click->url.'</a>';
                            $clickThroughOutput .= '<strong>'.sprintf('%d %s', $click->click_count, _n(__('Click', 'ctct'), __('Clicks', 'ctct'), $click->click_count)).'</strong>';
                            $clickThroughOutput .= '</li>';
                        }

                        $clickThroughOutput .= '</ul>';
                    }

                    $html .= sprintf('<th scope="row" valign="top" class="column-name">%s</th>
                        <td>%s</td>', esc_html( $Campaign->getLabel($key) ), $clickThroughOutput);

                    break;
                default:
                    $html .= sprintf('<th scope="row" valign="top" class="column-name">%s</th>
                        <td>%s</td>', esc_html( $Campaign->getLabel($key) ), esc_html( print_r( $Campaign->get($key), true) ) );
                break;
            }
        } else {
            if(!is_string($Campaign->get($key))) { continue; }
            $html .= sprintf('<th scope="row" valign="top" class="column-name">%s</th>
                <td>%s</td>', esc_html( $Campaign->getLabel($key) ), esc_html( $Campaign->get($key, true) ));
        }
        $html .= '
        </tr>';
    }

    echo $html;
?>
</tbody>
</table>