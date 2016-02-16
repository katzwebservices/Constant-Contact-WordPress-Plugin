<div class="clear component-summary">
    <dl class="summary-1">
        <dt><?php echo esc_html( $Campaign->getLabel('status') ); ?></dt>
        <dd><?php echo esc_html( $Campaign->get('status', true) ); ?></dd>
    </dl>
    <dl class="summary-2">
        <dt><?php echo esc_html( $Campaign->getLabel('created_date') ); ?></dt>
        <dd><?php echo esc_html( kws_format_date($Campaign->get('created_date')) ); ?></dd>
    </dl>
</div>


<div class="clear component-summary"><?php
    echo kws_generate_tracking_summary_report( $Campaign->get('tracking_summary') );
?></div>

<table class="wp-list-table widefat fixed striped ctct_table" cellspacing="0">
    <thead>
        <tr>
            <th scope="col" class="column-name"><?php esc_html_e('Name', 'constant-contact-api'); ?></th>
            <th scope="col" class="column-title"><?php esc_html_e( 'Data', 'constant-contact-api'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php
    $alt = ''; $html = '';

    /**
     * @var KWSCampaign $Campaign
     * @var string $key
     * @var $value
     */
    foreach ($Campaign as $key => $value) {

        if( is_null( $value ) ) { continue; }

        $alt = empty($alt) ? ' class="alt"' : '';

        $html .= '<tr'.$alt.'>';

        if(is_array($value)) {
            switch($key) {
                case 'sent_to_contact_lists':
                case 'lists':
                    $html .= sprintf('<th scope="row" valign="top" class="column-name">%s</th>
                        <td>%s</td>', esc_html( $Campaign->getLabel($key) ), KWSContactList::outputHTML($value, array('type' => 'ul')));
                    break;
                case 'click_through_details':
                    $clickThroughOutput = '';
                    if(!empty($value)) {
                        $clickThroughOutput = '<ul class="ul-disc">';
                        foreach((array)$value as $click) {
                            $clickThroughOutput .= '<li>';
                            $clickThroughOutput .= '<a class="block" href="'.esc_js( $click->url ).'">'.esc_html( $click->url ).'</a>';
                            $clickThroughOutput .= '<strong>'.sprintf('%d %s', $click->click_count, _n(__('Click', 'constant-contact-api'), __('Clicks', 'constant-contact-api'), $click->click_count)).'</strong>';
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

            // Make sure we're dealing with text
            if ( ! is_string( $Campaign->get( $key ) ) ) {
                continue;
            }

            $html .= sprintf( '<th scope="row" valign="top" class="column-name">%s</th>
                <td>%s</td>', esc_html( $Campaign->getLabel( $key ) ), make_clickable( esc_html( $Campaign->get( $key, true ) ) ) );
        }
        $html .= '
        </tr>';
    }

    echo $html;
    ?>
    </tbody>
</table>