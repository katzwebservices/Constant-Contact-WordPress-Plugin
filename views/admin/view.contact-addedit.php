<form method="post">
    <table class="widefat clear fixed ctct_table" cellspacing="0">
        <thead>
            <th scope="col" class="column-name" style="width:30%"><?php _e('Field Name', 'ctct'); ?></th>
            <th scope="col" class="column-title"><?php _e('Field Values', 'ctct'); ?></th>
        </thead>
        <tbody>
            <?php

        $alt = ''; $html = '';
        foreach ($Contact as $key => $value) {
            $alt = empty($alt) ? ' class="alt"' : '';

            if(!$Contact->is_editable($key)) { continue; }

            $html .= '<tr'.$alt.'>';
            $html .= sprintf('<th scope="row" valign="top" class="column-name"><label for="%s">%s</label></th>', esc_attr($key), $Contact->getLabel($key));
            $html .= '<td>';


            switch($key) {
                case 'lists':
                    $html .= KWSContactList::outputHTML('all', array('type' => 'checkboxes', 'checked' => $Contact->get('lists', true)));
                    break;
                case 'addresses':
                    $personal = '
                    <label class="wrap">
                    	<input name="addresses[%%id%%][line1]" type="text" class="regular-text" value="'.esc_attr($Contact->get('personal_line1')).'" placeholder="'.esc_attr($Contact->getLabel('line1')).'" title="'.esc_attr($Contact->getLabel('line1')).'" />
                    </label>
                    <label class="wrap">
                    	<input name="addresses[%%id%%][line2]" type="text" class="regular-text" value="'.esc_attr($Contact->get('personal_line2')).'" placeholder="'.esc_attr($Contact->getLabel('line2')).'" title="'.esc_attr($Contact->getLabel('line2')).'" />
                    </label>
                    <label class="wrap">
                    	<input name="addresses[%%id%%][line3]" type="text" class="regular-text" value="'.esc_attr($Contact->get('personal_line3')).'" placeholder="'.esc_attr($Contact->getLabel('line3')).'" title="'.esc_attr($Contact->getLabel('line3')).'" />
                    </label>
                    <input name="addresses[%%id%%][city]" type="text" class="" value="'.$Contact->get('personal_city').'" placeholder="'.esc_attr($Contact->getLabel('city')).'" title="'.esc_attr($Contact->getLabel('city')).'" />
                    <input name="addresses[%%id%%][state_code]" size="2" type="text" class="" value="'.$Contact->get('personal_state_code').'" placeholder="'.esc_attr($Contact->getLabel('state_code')).'" title="'.esc_attr($Contact->getLabel('state_code')).'" />
                    <input name="addresses[%%id%%][postal_code]" size="10" type="text" class="" value="'.$Contact->get('personal_postal_code').'" placeholder="'.esc_attr($Contact->getLabel('postal_code')).'"  title="'.esc_attr($Contact->getLabel('postal_code')).'" size="8" />
                    <input name="addresses[%%id%%][sub_postal_code]" type="text" class="" value="'.$Contact->get('personal_sub_postal_code').'" placeholder="'.esc_attr($Contact->getLabel('sub_postal_code')).'" title="'.esc_attr($Contact->getLabel('sub_postal_code')).'" size="6" /><br />
                    <input name="addresses[%%id%%][address_type]" type="hidden" value="%%name%%" />
                    ';
                    $html .= sprintf('<h3>%s</h3> <div>%s</div>',
                                     __('Personal Address', 'ctct'), str_replace(array('%%name%%', '%%id%%'), array('personal', 0), $personal));
                    $html .= sprintf('<h3>%s</h3> <div>%s</div>',
                                     __('Business Address', 'ctct'), str_replace(array('%%name%%', '%%id%%'), array('business', 1), $personal));
                    break;
                case 'custom_fields':
                	$i = 1;
                	$html .= '<ul class="ctct-checkboxes">';
                	while($i < 16) {
                        $label = sprintf( esc_attr_x( 'Custom Field %d', 'Admin label for custom fields', 'ctct'), $i );
                	    $html .= sprintf('<li><label class="wrap">%1$s <input title="" placeholder="%1$s" type="text" name="CustomField%2$d" class="input regular-text" value="%3$s" /></label></li>', $label, $i, $Contact->get('CustomField'.$i));
                	    $i++;
                	}
                	$html .= '</ul>';
                	break;
                case 'notes':
                    $html .= '<textarea name="notes" type="text" class="all-options" title="'.esc_attr($Contact->getLabel($key)).'" placeholder="'.esc_attr($Contact->getLabel($key)).'">'.$Contact->get('notes').'</textarea>';
                    break;
                default:
                	$html .= '<input id="'.$key.'" type="text" name="'.$key.'" class="regular-text" title="'.esc_attr($Contact->getLabel($key)).'" value="'.$Contact->get($key).'" placeholder="'.esc_attr($Contact->getLabel($key)).'" />';
                    break;
            }

            $html .= '</td>
            </tr>';
        }

        echo $html;
    ?>
        </tbody>
    </table>

    <div class="submit">
        <input type="submit" class="button button-primary button-large" value="<?php _e('Submit', 'consatnt-contact-apo'); ?>" />
    </div>
</form>