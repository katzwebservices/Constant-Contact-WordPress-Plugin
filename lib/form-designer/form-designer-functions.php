<?php
/**
 * @package CTCT\Form Designer
 */

class CTCT_Form_Designer_Helper {

    /**
     * Return the current page being accessed
     *
     * @return string Page URL
     */
    static function current_page_url() {

         return esc_url( remove_query_arg( 'success', add_query_arg( array() ) ) );

    }

    static function get_form_object( $form ) {
        return self::get_form( $form, 'object');
    }

    static function get_form( $form, $type = 'array', $forms = array()) {
        $form = intval($form);
        if ($form != 0 && ! $form )
            return false;

        if(empty($forms)) { $forms = self::get_forms(); }

        if ( !isset($forms[$form]) ) {

            // If the form isn't in the all forms array as a key,
            // make sure it's not missing the correct key assignment
            // by checking it against the cc-form-id
            foreach($forms as $key => $f) {
                if((isset($f['cc-form-id']) && intval($f['cc-form-id']) === $form) || (isset($f['form']) && intval($f['form']) === $form)) {
                    self::get_form($key, $type, $forms);
                }
            }

            return false;
        } else {
            if(strtolower($type) != 'array') {
                return (object)$forms[$form];
            } else {
                return (array)$forms[$form];
            }
        }
    }

    static function create_form() {
        return self::update_form_object( -1, $_REQUEST );
    }

    static function delete_form( $menu ) {
        $forms = self::get_forms();
        if(isset($forms[$menu]) && !empty($forms[$menu])) {
            unset($forms[$menu]);
            return self::set_forms($forms);
        }
        return false;
    }

    /**
     * Get a base int so that when deleting forms, there's never overwriting.
     * @param  array $forms If setting for the first time, we'll need the $forms array to get the highest number.
     * @return integer Form ID that won't conflict with other forms.
     */
    static function get_form_increment($forms = array()) {

        $previous = get_option('cc_form_increment');

        if(!empty($forms) && is_array($forms) && empty($previous)) {
            $previous = 0;
            foreach($forms as $form) {
                if($form['form'] > $previous) { $previous = $form['form']; }
            }
        }

        $increment = floatval($previous) + 1;

        update_option('cc_form_increment', $increment);

        return $increment++;
    }

    static function generate_form_from_request($r, $forms) {
        if(!is_array($r)) { return false; }

        // We don't want to save this extranneous stuff into the DB
        unset($r['_wp_http_referer'], $r['action'], $r['save_form'], $r['page'], $r['form-style'], $r['closedpostboxesnonce'], $r['meta-box-order-nonce'], $r['update-cc-form-nonce']);

        $r = esc_attr_recursive($r);

        if(!isset($r['cc-form-id']) || $r['cc-form-id']  === '' || $r['cc-form-id'] == -1 || $r['cc-form-id'] == '-1') {
            $r['cc-form-id'] = self::get_form_increment($forms);
        }

        $using_default_form_name = empty( $r['form-name'] ) || ( $r['form-name'] === apply_filters('constant_contact_default_form_name', __('Enter form name here', 'ctct')) );

        if( $using_default_form_name ) {
            $r['form-name'] = sprintf( esc_attr_x( 'Form #%d', 'Default form name when none is provided.', 'ctct') , $r['cc-form-id'] );
        }

        return $r;
    }

    /**
     * Saves the form - Updates if exists; Creates if not exists
     *
     * @uses self::generate_form_from_request()
     * @param  integer $form_id The ID of the form to be saved. `-1` if not yet exists.
     * @param  array   $data    The form data as an array
     * @return WP_Error|integer     If form update failed, `WP_Error`; otherwise, the saved/updated form ID
     */
    static function update_form_object( $form_id = -1, $data = array()) {
        $form_id = floatval($form_id);

        // Get existing forms
        $forms = self::get_forms();

        // Whittle down submitted form into just the fields we want to save
        $form = self::generate_form_from_request($data, $forms);

        // form doesn't already exist, so create a new form
        if ($form_id == -1 || $form_id == '-1' || $form_id === '' || !isset($form['cc-form-id']) || $form['cc-form-id']  === '') {

            // Get the new form id, set in self::generate_form_from_request()
            $form_id = get_option('cc_form_increment');

        } elseif(isset($forms[$form_id])) {

            // Hook into the form saving process if you want
            $form = apply_filters("wp_update_cc_form_$form_id", $form );

        } else {

            do_action('ctct_log', $data, 'error');

            return new WP_Error('update_form_object_failed', __('The form both does not exist and does exist. Can not process!', 'ctct'));
        }

        // Add the form to the forms array
        $forms[$form_id] = $form;

        // That cached version's gotta go.
        delete_transient("cc_form_$form_id");

        // Update forms array to DB
        self::set_forms($forms);

        // Return the new form id
        return floatval($form['cc-form-id']);

    }


    /**
     * Update forms setting
     *
     * Applies `ctct_set_cc_forms` filter to the forms array first
     *
     * @filter set_forms
     * @param  [type] $forms [description]
     * @return [type]        [description]
     */
    static function set_forms($forms) {

        // Hook into the data saved in the form
        $forms = apply_filters("ctct_set_cc_forms", $forms );

        return update_option('cc_form_design', $forms);
    }


    /**
     * Get an array of the form designer forms
     * @return array Forms
     */
    static function get_forms() {

        $cc_forms = get_option('cc_form_design');

        if(!$cc_forms) { $cc_forms = array(); }

        // Generate truncated menu names
        $previous_names = array();

        foreach( (array) $cc_forms as $key => $_cc_form ) {
            $name = !empty($_cc_form['form-name']) ? $_cc_form['form-name'] : sprintf(__('Form #%s', 'ctct'), $key);

            $_cc_form['truncated_name'] = trim( wp_html_excerpt( $name, 30 ) );
            if ( isset($_cc_form['form-name']) && $_cc_form['truncated_name'] != $name)
                $_cc_form['truncated_name'] .= '&hellip;';

            if(!in_array(sanitize_user( $name ), $previous_names)) {
                $previous_names[] = sanitize_user( $name );
            } else {
                $namekey = sanitize_user( $name );
                $previous_names[$namekey] = isset($previous_names[$namekey]) ? ($previous_names[$namekey] + 1) : 1;
                $_cc_form['truncated_name'] .= ' ('.$previous_names[$namekey].')';
            }

            $cc_forms[$key]['truncated_name'] = $_cc_form['truncated_name'];
        }

        return $cc_forms;
    }


    static function check_default($form, $name, $id, $value) {
        $inputValue = '';
        if(isset($value)) {
        $inputValue = $value;
        }
        if(isset($form[$name]) && is_array($form[$name])) {
            $inputValue = isset($form[$name][$id]) ? $form[$name][$id] : $value;
        } elseif(isset($form[$name]) && !is_array($form[$name])){
            $inputValue = isset($form[$name]) ? $form[$name] : $value;
        } else {
            $inputValue = isset($form[$id]) ? $form[$id] : $value;
        }
        return html_entity_decode(stripslashes($inputValue));
    }

    static function make_formfield_list_items($array, $checkedArray, $name) {
        $out = '';
        foreach($array as $a) {
            $out .= self::make_formfield_list_item($a[0], $a[1], !empty($checkedArray) ? in_array($a[0], $checkedArray) : $a[2], $name);
        }
        return $out;
    }

    static function make_formfield_list_item($id, $title, $checked = false, $name = 'formfields') {

        if($checked) {
            $checked = ' checked="checked"';
        }

        $style = '';
        if($id == 'email_address') {
            $checked = ' checked="checked" disabled="disabled"';
        }

        if($id == 'lists') {
            $style = ' style="display:none;"';
        }

        return '<li'.$style.'>
            <label class="menu-item-title"><input type="checkbox" class="menu-item-checkbox" name="'.$name.'['.$id.']" value="'.$id.'"'.$checked.' /> '.$title.'</label>
        </li>';
    }

    static function make_formfield($_form_object = array(), $class, $id, $value, $checked, $default = '', $type="text", $labeldefault = '') {
        global $formfield_num;

        if( !isset( $formfield_num) ) {
            $formfield_num = 0;
        }

        $field = isset( $_form_object['f'][ $formfield_num ] ) ? $_form_object['f'][ $formfield_num ] : array();

        $out = $hide = '';

        $name = 'f';
        $class = trim($class .' ui-state-default menu-item ui-state-default formfield');
        if( isset($field['n']) ) {
            $checked = 'checked="checked"';
        } else {
            $hide = ' style="display:none;"';
        }

        $hideinputs = false;
        $hide_value_input = false;

        switch ( $type ) {
            case 'text':
            case 't':
                $input_type = 't';
                $field_label = __('Label text', 'ctct');
                $field_desc = __('Input placeholder text', 'ctct');
                $inputValue = self::check_default($_form_object, $name, $id, $value);
                break;

            case 'button':
            case 'submit':
            case 'b':
            case 's':
                $input_type = 'b';
                $field_label = __('Button label', 'ctct');
                $inputValue = '';
                $field_desc = __('Button text', 'ctct');
                break;

            case 'textarea':
            case 'ta':
                $input_type = 'ta';
                $hideinputs = true;
                $field_label = __('Headline', 'ctct');
                $default = $labeldefault;
                $inputValue = self::check_default($_form_object,$name, $id, $value);

                $field_desc  = '';
                $field_desc .= '<h3>'.esc_html__('This is a Placeholder', 'ctct').'</h3>';
                $field_desc .= '<p>'.esc_html__('This item will be replaced by the Custom Text entered above &uarr;', 'ctct').'</p>';
                break;

            case 'lists':
                $input_type = 'lists';
                $hide_value_input = true;
                $field_label = __('Subscribe Message', 'ctct');
                $default = $labeldefault;
                $inputValue = self::check_default($_form_object,$name, $id, $value);
                $field_desc = __('The lists will be placed where this item is.', 'ctct');
                break;
        }

        // If the field position is set, use it. Otherwise, use the natural order of the fields
        $position = (!empty($field['pos'])) ? $field['pos'] : $formfield_num;

        $size = (!empty($field['size'])) ? $field['size'] : '';
        $required = (!empty($field['required'])) ? ' checked="checked"' : '';
        $bold = (!empty($field['bold'])) ? ' checked="checked"' : '';
        $italic = (!empty($field['italic'])) ? ' checked="checked"' : '';

        if(isset($field['val'])) {
            $default = html_entity_decode( stripslashes($field['val']) );
        }


        if(isset($field['label'])) {
            $inputValue = html_entity_decode( stripslashes($field['label']) );
        } elseif(isset($labeldefault) && !empty($labeldefault)) {
            $inputValue = $labeldefault;
        }

        $name = $name.'['.$formfield_num.']';
        $formfield_num++;
        $out .= '
            <li class="'.$class.'"'.$hide.'>
                <dl class="menu-item-bar">
                    <dt class="menu-item-handle">
                        <span class="item-title">'.$value.' <i class="dashicons dashicons-sort" title="'.esc_attr__('Drag and drop to re-order fields.', 'ctct').'"></i></span>
                        <span class="item-controls">
                            <span class="item-type"></span>
                            <input type="checkbox" name="'.$name.'[n]" id="'.$id.'" value="'.$name.'" '.$checked.' class="checkbox hide-if-js" rel="'.$type.'" />
                            <a class="item-edit" id="edit-'.$id.'" title="Edit '.$name.'" href="#">Edit Menu Item</a>
                        </span>
                    </dt>
                </dl>
                <div class="menu-item-settings"><div class="wrap">
                    <input type="hidden" name="'.$name.'[id]" value="'.$id.'" />
                    <input type="hidden" name="'.$name.'[t]" value="'.$input_type.'" />
                    <input type="hidden" name="'.$name.'[pos]" id="'.$id.'_pos" value="'.$position.'" class="position" />';
        // If not a text area
        if( $input_type !== 'ta') {
            $out .= "\n".'<p><label for="'.$id.'_label" class="labelValue howto"><span class="description">'.$field_label.'</span><input name="'.$name.'[label]" type="text" id="'.$id.'_label" value="'.$inputValue.'" class="labelValue widefat"  /></label></p>
                            <div class="labelStyle">
                                <label for="'.$id.'_bold" class="labelStyle mce_bold">
                                    <span class="dashicons dashicons-editor-bold" title="'.esc_attr__('Make label bold', 'ctct').'"></span><input type="checkbox" name="'.$name.'[bold]" id="'.$id.'_bold" value="bold"'.$bold.' />
                                </label>
                                <label for="'.$id.'_italic"'.$italic.' class="labelStyle mce_italic">
                                    <span class="dashicons dashicons-editor-italic" title="'.esc_attr__('Make label italic', 'ctct').'"></span>
                                    <input type="checkbox" name="'.$name.'[italic]" id="'.$id.'_italic" value="italic" />
                                </label>';
                if($id == 'email_address' || $input_type == 'b') {
                    $out .= '<input type="hidden" name="'.$name.'[required]" id="'.$id.'_required" value="required" />';
                } else {
                    if(!$hide_value_input) {
                    $out .= '<label for="'.$id.'_required" class="labelStyle checkbox"><span>'.__('Required', 'ctct').'</span>&nbsp;<input type="checkbox" name="'.$name.'[required]" id="'.$id.'_required" value="required"'.$required.' class="labelRequired"  /></label>';
                    }
                }
            $out .= '<div class="clear"></div></div>';
            if(!$hide_value_input) {
              $out .= "\n".'<div class="clear"><label for="'.$id.'_default" class="labelDefault howto"><span class="description">'.$field_desc.'</span><input type="text" name="'.$name.'[val]" id="'.$id.'_default" value="'.$default.'" class="labelDefault widefat"  /></label></div>';
            }
        } else {
            $out .= "\n".'<div><span class="description">'.$field_desc.'</span></div>';
        }
        $out .='<div class="clear"></div></div>
                </div>
                <ul class="menu-item-transport"></ul>
            </li>';
        return $out;
    }

    /**
     * Print the form setting value (value="") for form generator admin screen
     * @param  array $form    Form settings array
     * @param  string $name    Key of the field
     * @param  string $default Default value for the field
     */
    static function input_value($form, $name, $default) {
        echo self::check_default($form, $name, '', $default);
    }


    /**
     * Print value="" and/or selected="selected" for `<select>`s on the form generator admin screen
     * @param  array $form    Form settings array
     * @param  string $name    Key of the field
     * @param string $value Value of the `<select>` `<option>`
     */
    static function check_select($form, $name, $value, $default = false) {
        echo " value='$value'";
        $check = (isset($form[$name]) && $form[$name] == $value);
        if($check || !(isset($form[$name]) ) && $default) {
            echo ' selected="selected"';
        }
    }

    /**
     * Print value="" and/or selected="selected" for `<input type="radio">`s on the form generator admin screen
     * @param  array $form    Form settings array
     * @param  string $name    Key of the field
     * @param string $value Value of the radio
     * @param boolean $default Is this radio selected by default
     */
    static function check_radio($form, $name, $value, $default = false) {
        echo " value='$value'";
        $check = (isset($form[$name]) && $form[$name] !== '' && $form[$name] === $value);
        if($check || ( (empty($form) || !isset($form[$name]) ) && $default)) {
            echo ' checked="checked"';
        }
    }

    /**
     * Print value="" and/or selected="selected" for `<input type="checkbox">`s on the form generator admin screen. Alias of `ctct_check_radio()`
     * @param  array $form    Form settings array
     * @param  string $name    Key of the field
     * @param string $value Value of the checkbox
     * @param boolean $default Is this checkbox selected by default
     * @see  ctct_check_radio()
     */
    static function check_checkbox($form, $name = '', $value = '', $default = false) {
        self::check_radio($form, $name, $value, $default);
    }

    static function get_check_field($form, $name, $value, $echo = ' selected="selected"', $default = false, $type = 'select') {
        if(is_array($value)) {
            foreach($value as $val) {
                if(self::get_check_field($name, $val, $echo)) { exit; } // If one is true, stop processing
            }
        }
        if(isset($form[$name]) && !empty($form[$name]) && (
                $type != 'select' ||
                ($type == 'select' && $form[$name] == $value || strtolower($form[$name]) == strtolower($value))
            )
        ) {
            return stripslashes(html_entity_decode($echo));
        } elseif($default) {
            return $echo;
        }
        return false;
    }

    static function check_field($form, $name, $value, $echo = ' selected="selected"', $default = false, $type = 'select') {
        echo self::get_check_field($form, $name, $value, $echo, $default, $type);
    }



}

/**
 * Return the current page being accessed
 * From http://www.webcheatsheet.com/PHP/get_current_page_url.php
 *
 * @return string Page URL
 */
function ctct_current_page_url() {
     return CTCT_Form_Designer_Helper::current_page_url();
}


function wp_get_cc_form_object( $form ) {
	return CTCT_Form_Designer_Helper::get_form( $form, 'object' );
}

function wp_get_cc_form( $form, $type = 'array', $forms = array()) {
    return CTCT_Form_Designer_Helper::get_form( $form, $type, $forms );
}

function wp_create_cc_form() {
	return CTCT_Form_Designer_Helper::create_form();
}

function wp_delete_cc_form( $menu ) {
    return CTCT_Form_Designer_Helper::delete_form( $menu );
}

/**
 * Get a base int so that when deleting forms, there's never overwriting.
 * @param  array $forms If setting for the first time, we'll need the $forms array to get the highest number.
 * @return integer Form ID that won't conflict with other forms.
 */
function cc_get_form_increment($forms = array()) {

    return CTCT_Form_Designer_Helper::get_form_increment( $forms );

}

function cc_generate_form_from_request($r, $forms) {

    return CTCT_Form_Designer_Helper::generate_form_from_request( $r, $forms );

}

/**
 * Saves the form - Updates if exists; Creates if not exists
 *
 * @uses cc_generate_form_from_request()
 * @param  integer $form_id The ID of the form to be saved. `-1` if not yet exists.
 * @param  array   $data    The form data as an array
 * @return WP_Error|integer     If form update failed, `WP_Error`; otherwise, the saved/updated form ID
 */
function wp_update_cc_form_object( $form_id = -1, $data = array()) {

    return CTCT_Form_Designer_Helper::update_form_object( $form_id, $data );

}

/**
 * Update forms setting
 *
 * Applies `wp_set_cc_forms` filter to the forms array first
 *
 * @filter wp_set_cc_forms
 * @param  [type] $forms [description]
 * @return [type]        [description]
 */
function wp_set_cc_forms($forms) {
	return CTCT_Form_Designer_Helper::set_forms( $form_id, $data );
}


/**
 * Get an array of the form designer forms
 * @return array Forms
 */
function wp_get_cc_forms() {

	return CTCT_Form_Designer_Helper::get_forms();
}


function ctct_check_default($form, $name, $id, $value) {

    return CTCT_Form_Designer_Helper::check_default($form, $name, $id, $value);
}

function ctct_make_formfield_list_items($array, $checkedArray, $name) {
	return CTCT_Form_Designer_Helper::make_formfield_list_items($array, $checkedArray, $name);
}

function ctct_make_formfield_list_item($id, $title, $checked = false, $name = 'formfields') {
	return CTCT_Form_Designer_Helper::make_formfield_list_item($id, $title, $checked, $name );
}

function ctct_make_formfield($_form_object = array(), $class, $id, $value, $checked, $default = '', $type="text", $labeldefault = '') {

    return CTCT_Form_Designer_Helper::make_formfield($_form_object, $class, $id, $value, $checked, $default, $type, $labeldefault);

}

/**
 * Print the form setting value (value="") for form generator admin screen
 * @param  array $form    Form settings array
 * @param  string $name    Key of the field
 * @param  string $default Default value for the field
 */
function ctct_input_value($form, $name, $default) {
	CTCT_Form_Designer_Helper::input_value($form, $name, $default);
}


/**
 * Print value="" and/or selected="selected" for `<select>`s on the form generator admin screen
 * @param  array $form    Form settings array
 * @param  string $name    Key of the field
 * @param string $value Value of the `<select>` `<option>`
 */
function ctct_check_select($form, $name, $value, $default = false) {
	CTCT_Form_Designer_Helper::check_select($form, $name, $value, $default);
}

/**
 * Print value="" and/or selected="selected" for `<input type="radio">`s on the form generator admin screen
 * @param  array $form    Form settings array
 * @param  string $name    Key of the field
 * @param string $value Value of the radio
 * @param boolean $default Is this radio selected by default
 */
function ctct_check_radio($form, $name, $value, $default = false) {
	CTCT_Form_Designer_Helper::check_radio( $form, $name, $value, $default );
}

/**
 * Print value="" and/or selected="selected" for `<input type="checkbox">`s on the form generator admin screen. Alias of `ctct_check_radio()`
 * @param  array $form    Form settings array
 * @param  string $name    Key of the field
 * @param string $value Value of the checkbox
 * @param boolean $default Is this checkbox selected by default
 * @see  ctct_check_radio()
 */
function ctct_check_checkbox($form, $name = '', $value = '', $default = false) {
	CTCT_Form_Designer_Helper::check_radio( $form, $name, $value, $default );
}

function ctct_get_check_field($form, $name, $value, $echo = ' selected="selected"', $default = false, $type = 'select') {

    return CTCT_Form_Designer_Helper::get_check_field($form, $name, $value, $echo, $default, $type);

}
function ctct_check_field($form, $name, $value, $echo = ' selected="selected"', $default = false, $type = 'select') {
	echo CTCT_Form_Designer_Helper::get_check_field($form, $name, $value, $echo, $default, $type);
}

