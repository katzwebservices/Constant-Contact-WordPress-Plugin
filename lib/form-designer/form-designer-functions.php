<?php
/**
 * @package CTCT\Form Designer
 */

/**
 * Return the current page being accessed
 * From http://www.webcheatsheet.com/PHP/get_current_page_url.php
 *
 * @return string Page URL
 */
function ctct_current_page_url() {
     $pageURL = 'http';
     if (isset($_SERVER["HTTPS"]) AND ($_SERVER["HTTPS"] == "on")) {$pageURL .= "s";}
     $pageURL .= "://";
     if ($_SERVER["SERVER_PORT"] != "80") {
      $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
     } else {
      $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
     }
     return esc_url(remove_query_arg('success',$pageURL));
}

function constant_contact_signup_form_shortcode($atts, $content=null) {

    return constant_contact_public_signup_form($atts, false);

};

/**
 * HTML Signup form to be used in widget and shortcode
 *
 * Based on original widget code but broken out to be used in shortcode and
 * any other place where non-logged-in users will be signing up.
 *
 * Modify the output by calling `add_filter('constant_contact_form', 'your_function');`
 *
 * @param array|string $passed_args Settings for generating the signup form
 * @param boolean $echo True: Echo the form; False: return form output.
 * @return string Form HTML output
 */
function constant_contact_public_signup_form( $passed_args, $echo = true) {

    do_action('ctct_debug', 'constant_contact_public_signup_form', $passed_args, @$_POST);

    $output = $error_output = $success = $haserror = $hiddenlistoutput = '';
    $default_args = array(
        'before' => null,
        'after' => null,
        'formid' => 0,
        'redirect_url' => false,
        'lists' => array(),
        'title' => '',
        'exclude_lists' => array(),
        'description' => '',
        'show_list_selection' => false,
        'list_selection_title' => __('Add me to these lists:', 'ctct'),
        'list_selection_format' => NULL,
        'list_format' => NULL, // Used by form
        'widget' => false, // is this request coming from the widget?
    );

    $settings = shortcode_atts( $default_args, $passed_args );

    /**
     * This unique id will be used to differentiate from other forms on the same page.
     * It will also be used to store cached forms.
     *
     * Only get the first 10 characters, since that's all we really need.
     * @var string
     */
    $unique_id = substr( sha1(maybe_serialize($settings)), 0, 10 );

    $form = wp_get_cc_form($settings['formid']);

    // Merge using the form settings
    $settings = shortcode_atts( $settings, $form );

    // Override one more time using the passed args as the final
    $settings = shortcode_atts( $settings, $passed_args );

    // BACKWARD COMPATIBILITY
    $settings['list_selection_format'] = empty( $settings['list_selection_format'] ) ? $settings['list_format'] : $settings['list_selection_format'];

    extract($settings, EXTR_SKIP);

    // The form does not exist.
    if(!$form) {

    	do_action('ctct_log', sprintf('Form #%s does not exist. Called on %s', $formid, add_query_arg(array())));

    	if(current_user_can('manage_options')) {
    		return '<!-- Constant Contact API Error: Form #'.$formid.' does not exist. -->';
    	}

    	return false;
    }

    // If other lists aren't passed to the function,
    // use the default lists defined in the form designer.
    if(empty($lists)) { $lists = $form['lists']; }

    $selected = $lists;
    if($widget) {
        $lists = isset( $form['lists'] ) ? $form['lists'] : null;
        $show_list_selection = ( !empty( $form['formfields'] ) && is_array( $form['formfields'] ) ) ? in_array('lists', $form['formfields']) : null;
        $list_selection_format = @$form['list_format'];
        $selected = isset($form['checked_by_default']) ? $form['checked_by_default'] : false;
    }

    /**
     * Make it possible to call using shortcode comma separated values. eg: lists=1,2,3
     */
    if(is_string($lists)) { $lists = explode(',', $lists); }

    // The form is retrieved from constant_contact_retrieve_form()
    // and then the variables are replaced further down the function.
    if($formid !== '' && function_exists('constant_contact_retrieve_form')) {
        $force = (isset($_REQUEST['cache']) || (isset($_REQUEST['uniqueformid']) && $_REQUEST['uniqueformid'] === $unique_id)) ? true : false;
        $form = constant_contact_retrieve_form($formid, $force, $unique_id, $lists);
    } elseif(!function_exists('constant_contact_retrieve_form') && current_user_can('manage_options')) {
        echo '<!-- Constant Contact API Error: `constant_contact_retrieve_form` function does not exist. -->';
    }

    // If the form returns an error, we want to get out of here!
    if(empty($form) || is_wp_error($form)) {
        if(is_wp_error($form)) {
            do_action('ctct_debug', 'Form is empty or WP_Error', $form);
        }
        return false;
    }

    // Modify lists with this filter
    $lists = apply_filters('constant_contact_form_designer_lists', apply_filters('constant_contact_form_designer_lists_'.$formid, $lists));

    /**
     * Display errors or Success message if the form was submitted.
     */

    $ProcessForm = CTCT_Process_Form::getInstance();

    $errors = $ProcessForm->getErrors();
    $success = '';

    /**
     * Success message: If no errors AND signup was successful show the success message
     */
    if( !empty( $errors ) ) {
        $haserror = ' has_errors';

        $error_output = '';

        do_action('ctct_debug', 'Handling errors in constant_contact_public_signup_form', $errors);

        // Set up error display
        $error_output .= '<div id="constant-contact-signup-errors" class="error">';
        $error_output .= '<ul>';
        foreach ($errors as $error ) {
            $label =
            $error_output .= '<li><label for="'.$error->get_error_code().'">'.$error->get_error_message().'</label></li>';
        }
        $error_output .= '</ul>';
        $error_output .= '</div>';

        // Filter output so text can be modified by plugins/themes
        $error_output = apply_filters('constant_contact_form_errors', $error_output);

    } elseif( is_a( $ProcessForm->getResults(), 'Ctct\Components\Contacts\Contact') ) {

        $success = '<p class="success cc_success">';
        $success .= esc_html__('Success, you have been subscribed.', 'constant-contact-api');
        $success .= '</p>';

        $success = apply_filters('constant_contact_form_success', $success);
    }

    $form = str_replace('<!-- %%SUCCESS%% -->', $success, $form);
    $form = str_replace('<!-- %%ERRORS%% -->', $error_output, $form);
    $form = str_replace('<!-- %%HASERROR%% -->', $haserror, $form);

    // Generate the current page url, removing the success _GET query arg if it exists
    $current_page_url = remove_query_arg('success', ctct_current_page_url());
    $form = str_replace('<!-- %%ACTION%% -->', $current_page_url, $form);

    if( strpos( $form , '%%LISTSELECTION%%' ) > 0 ) {

        $listsOutput = '';

        // If lists are submitted, use those.
        // Otherwise, consider all/no lists selected based on `$selected` setting.
        $selected = !empty($_POST['lists']) ? (array)$_POST['lists'] : (bool)$selected;

        // Remove the cache for this whole joint
        $listsOutput = KWSContactList::outputHTML($lists, array(
            'fill' => true,
            'id_attr' => $unique_id.'-%%id%%',
            'showhidden' => false,
            'checked' => $selected,
            'type' => $list_selection_format ? $list_selection_format : 'hidden',
        ));

        // If you're showing list selection, show the label and wrap it in a container.
        if( $list_selection_format !== 'hidden' ) {
            $listsOutput = '<div class="cc_newsletter input-text-wrap">
                '.$listsOutput.'
            </div>';
        }

        $form = str_replace('<!-- %%LISTSELECTION%% -->', $listsOutput, $form);

    }

    /**
     * Finish form output including a hidden field for referrer and submit button
     */
    $hiddenoutput = '
        <div>
            <input type="hidden" id="cc_redirect_url" name="cc_redirect_url" value="'. urlencode( $redirect_url ) .'" />
            <input type="hidden" id="cc_referral_url" name="cc_referral_url" value="'. urlencode( $current_page_url ) .'" />
                <input type="hidden" name="uniqueformid" value="'.$unique_id.'" />
                <input type="hidden" name="ccformid" value="'.$formid.'" />
        </div>';
    $form = str_replace('<!-- %%HIDDEN%% -->', $hiddenoutput, $form);

    // All remaining tags should be removed.
    $form = preg_replace('/\%\%(.*?)\%\%/ism', '', $form);

    $output = apply_filters('constant_contact_form', apply_filters( 'constant_contact_form_'.$formid, $form));

    do_action('ctct_debug', 'form output', $output);

    /**
     * Echo the output if $settings['echo'] is true
     */
    if ($echo) { echo $output; }

    /**
     * And always return the $output
     */
    return $output;
}


function wp_get_cc_form_object( $form ) {
	return wp_get_cc_form( $form, 'object');
}

function wp_get_cc_form( $form, $type = 'array', $forms = array()) {
	$form = intval($form);
	if ($form != 0 && ! $form )
		return false;

	if(empty($forms)) {	$forms = wp_get_cc_forms(); }

	if ( !isset($forms[$form]) ) {

		// If the form isn't in the all forms array as a key,
		// make sure it's not missing the correct key assignment
		// by checking it against the cc-form-id
		foreach($forms as $key => $f) {
			if((isset($f['cc-form-id']) && intval($f['cc-form-id']) === $form) || (isset($f['form']) && intval($f['form']) === $form)) {
				wp_get_cc_form($key, $type, $forms);
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

function wp_create_cc_form() {
	return wp_update_cc_form_object( -1, $_REQUEST );
}

function wp_delete_cc_form( $menu ) {
	$forms = wp_get_cc_forms();
	if(isset($forms[$menu]) && !empty($forms[$menu])) {
		unset($forms[$menu]);
		return wp_set_cc_forms($forms);
	}
	return false;
}

/**
 * Get a base int so that when deleting forms, there's never overwriting.
 * @param  array $forms If setting for the first time, we'll need the $forms array to get the highest number.
 * @return integer Form ID that won't conflict with other forms.
 */
function cc_get_form_increment($forms = array()) {

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

function cc_generate_form_from_request($r, $forms) {
	if(!is_array($r)) { return false; }

	// We don't want to save this extranneous stuff into the DB
	unset($r['_wp_http_referer'], $r['action'], $r['save_form'], $r['page'], $r['form-style'], $r['closedpostboxesnonce'], $r['meta-box-order-nonce'], $r['update-cc-form-nonce']);

	$r = esc_attr_recursive($r);

	if(!isset($r['cc-form-id']) || $r['cc-form-id']  === '' || $r['cc-form-id'] == -1 || $r['cc-form-id'] == '-1') {
		$r['cc-form-id'] = cc_get_form_increment($forms);
	}
	if($r['form-name'] == apply_filters('constant_contact_default_form_name', __('Enter form name here', 'constant-contact-api'))) { $r['form-name'] = 'Form #'.$r['cc-form-id']; }

	return $r;
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
	$form_id = floatval($form_id);

	// Get existing forms
	$forms = wp_get_cc_forms();

	// Whittle down submitted form into just the fields we want to save
	$form = cc_generate_form_from_request($data, $forms);

	// form doesn't already exist, so create a new form
	if ($form_id == -1 || $form_id == '-1' || $form_id === '' || !isset($form['cc-form-id']) || $form['cc-form-id']  === '') {

		// Get the new form id, set in cc_generate_form_from_request
		$form_id = get_option('cc_form_increment');

		// Add the form to the forms array
		$forms[$form_id] = $form;
	} elseif(isset($forms[$form_id])) {
		// Hook into the form saving process if you want
		$form = apply_filters("wp_update_cc_form_$form_id", $form );

		$forms[$form_id] = $form;
	} else {
        do_action('ctct_log', $data, 'error');
		return new WP_Error('wp_update_cc_form_object_failed', __('The form both does not exist and does exist. Can not process!','constant-contact-api'));
	}

	// That cached version's gotta go.
	delete_transient("cc_form_$form_id");

	// Update forms array to DB
	wp_set_cc_forms($forms);

	// Return the new form id
	return floatval($form['cc-form-id']);

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
	// Hook into the data saved in the form
	$forms = apply_filters("wp_set_cc_forms", $forms );

	return update_option('cc_form_design', $forms);
}


/**
 * Get an array of the form designer forms
 * @return array Forms
 */
function wp_get_cc_forms() {

	$cc_forms = get_option('cc_form_design');

	if(!$cc_forms) { $cc_forms = array(); }

	// Generate truncated menu names
	$previous_names = array();

	foreach( (array) $cc_forms as $key => $_cc_form ) {
		$name = !empty($_cc_form['form-name']) ? $_cc_form['form-name'] : sprintf(__('Form #%s', 'constant-contact-api'), $key);

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


function ctct_check_default($form, $name, $id, $value) {
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

global $formfield_num;
$formfield_num = 0;

function make_formfield_list_items($array, $checkedArray, $name) {
	$out = '';
	foreach($array as $a) {
		$out .= make_formfield_list_item($a[0], $a[1], !empty($checkedArray) ? in_array($a[0], $checkedArray) : $a[2], $name);
	}
	return $out;
}

function make_formfield_list_item($id, $title, $checked = false, $name = 'formfields') {
	if($checked) { $checked = ' checked="checked"';}
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

function make_formfield($_form_object = array(), $class, $id, $value, $checked, $default = '', $type="text", $labeldefault = '') {
	global $formfield_num;

	$out = $position = $emailWidth = $hide = '';

	$name = 'f';
	$class = trim($class .' ui-state-default menu-item ui-state-default formfield');
	if((isset($_form_object['f'][$formfield_num]) && isset($_form_object['f'][$formfield_num]['n']))) {
		$checked = 'checked="checked"';
	} else {
		$hide = ' style="display:none;"';
	}

	$defaultAlign = 'Align';
	$defaultSize = 'Input Size';
	$hideinputs = false;
    $hidevalue = false;
	if($type == 'text' || $type=='t') {
		$t = 't';
		$labelLabel = __('Label text', 'constant-contact-api');
		$defaultLabel = __('Input placeholder text', 'constant-contact-api');
		$inputValue = ctct_check_default($_form_object, $name, $id, $value);
		//$value = 'Form Text';
	} elseif($type=='button' || $type=='submit' || $type=='b' || $type=='s') {
		$t = 'b';
		$labelLabel = __('Button label', 'constant-contact-api');
		$inputValue = '';
		$defaultLabel = __('Button text', 'constant-contact-api');
	} elseif($type=='textarea' || $type=='ta') {
		$t = 'ta';
		$hideinputs = true;
		$labelLabel = 'Headline';
		$default = $labeldefault;
		$inputValue = ctct_check_default($_form_object,$name, $id, $value);
		$defaultLabel = __('The Form Text content will be placed where this item is. Edit the Form Text above &uarr;', 'constant-contact-api');
	} elseif($type == 'lists') {
        $t = 'lists';
        $hidevalue = true;
        $labelLabel = __('Subscribe Message', 'consatnt-contact-api');
        $default = $labeldefault;
        $inputValue = ctct_check_default($_form_object,$name, $id, $value);
        $defaultLabel = __('The lists will be placed where this item is.', 'constant-contact-api');
    }
	$defaultRequired = 'Required';

	$position = (isset($_form_object['f'][$formfield_num]['pos']) && !empty($_form_object['f'][$formfield_num]['pos'])) ? $_form_object['f'][$formfield_num]['pos'] : '';
	$size = (isset($_form_object['f'][$formfield_num]['size']) && !empty($_form_object['f'][$formfield_num]['size'])) ? $_form_object['f'][$formfield_num]['size'] : '';
	$required = (isset($_form_object['f'][$formfield_num]['required']) && !empty($_form_object['f'][$formfield_num]['required'])) ? ' checked="checked"' : '';
	$bold = (isset($_form_object['f'][$formfield_num]['bold']) && !empty($_form_object['f'][$formfield_num]['bold'])) ? ' checked="checked"' : '';
	$italic = (isset($_form_object['f'][$formfield_num]['italic']) && !empty($_form_object['f'][$formfield_num]['italic'])) ? ' checked="checked"' : '';

	if(isset($_form_object['f'][$formfield_num]['val'])) {
		$default = html_entity_decode( stripslashes($_form_object['f'][$formfield_num]['val']) );
	}


	if(isset($_form_object['f'][$formfield_num]['label'])) {
		$inputValue = html_entity_decode( stripslashes($_form_object['f'][$formfield_num]['label']) );
	} elseif(isset($labeldefault) && !empty($labeldefault)) {
		$inputValue = $labeldefault;
	}

	$name = $name.'['.$formfield_num.']';
	$formfield_num++;
	$out .= '
		<li class="'.$class.'"'.$hide.'>
			<dl class="menu-item-bar">
				<dt class="menu-item-handle">
					<span class="item-title">'.$value.'</span>
					<span class="item-controls">
						<span class="item-type"></span>
						<input type="checkbox" name="'.$name.'[n]" id="'.$id.'" value="'.$name.'" '.$checked.' class="checkbox hide-if-js" rel="'.$type.'" />
						<a class="item-edit" id="edit-'.$id.'" title="Edit '.$name.'" href="#">Edit Menu Item</a>
					</span>
				</dt>
			</dl>
			<div class="menu-item-settings"><div class="wrap">
				<input type="hidden" name="'.$name.'[id]" value="'.$id.'" />
				<input type="hidden" name="'.$name.'[t]" value="'.$t.'" />
				<input type="hidden" name="'.$name.'[pos]" id="'.$id.'_pos" value="'.$position.'" class="position" />';
	if(!$hideinputs) {
        $out .= "\n".'<p><label for="'.$id.'_label" class="labelValue howto"><span class="description">'.$labelLabel.'</span><input name="'.$name.'[label]" type="text" id="'.$id.'_label" value="'.$inputValue.'" class="labelValue widefat"  /></label></p>
						<p class="labelStyle defaultSkin wp_themeSkin">
							<label for="'.$id.'_bold" class="labelStyle mce_bold">
								<a class="mceIcon" title="'.esc_attr__('Make label bold', 'constant-contact-api').'"><input type="checkbox" name="'.$name.'[bold]" id="'.$id.'_bold" value="bold"'.$bold.' /> Bold</a>
							</label>
							<label for="'.$id.'_italic"'.$italic.' class="labelStyle mce_italic">
								<a class="mceIcon" title="'.esc_attr__('Make label italic', 'constant-contact-api').'"><input type="checkbox" name="'.$name.'[italic]" id="'.$id.'_italic" value="italic" /> Italic</a>
							</label>';
			if($id == 'email_address' || $t == 'b') {
				$out .= '<input type="hidden" name="'.$name.'[required]" id="'.$id.'_required" value="required" />';
			} else {
                if(!$hidevalue) {
				$out .= '<label for="'.$id.'_required" class="labelStyle howto"><span>'.$defaultRequired.'</span>&nbsp;<input type="checkbox" name="'.$name.'[required]" id="'.$id.'_required" value="required"'.$required.' class="labelRequired"  /></label>';
                }
			}
		$out .= '</p>';
        if(!$hidevalue) {
		  $out .= "\n".'<div class="clear"><label for="'.$id.'_default" class="labelDefault howto"><span class="description">'.$defaultLabel.'</span><input type="text" name="'.$name.'[val]" id="'.$id.'_default" value="'.$default.'" class="labelDefault widefat"  /></label></div>';
        }
	} else {
		$out .= "\n".'<div><span class="description">'.$defaultLabel.'</span></div>';
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
function ctct_input_value($form, $name, $default) {
	echo ctct_check_default($form, $name, '', $default);
}


/**
 * Print value="" and/or selected="selected" for `<select>`s on the form generator admin screen
 * @param  array $form    Form settings array
 * @param  string $name    Key of the field
 * @param string $value Value of the `<select>` `<option>`
 */
function ctct_check_select($form, $name, $value, $default = false) {
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
function ctct_check_radio($form, $name, $value, $default = false) {
	echo " value='$value'";
	$check = (isset($form[$name]) && $form[$name] !== '' && $form[$name] === $value);
	if($check || (empty($form) && $default)) {
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
function ctct_check_checkbox($form, $name = '', $value = '', $default = false) {
	ctct_check_radio($form, $name, $value, $default);
}

function ctct_get_check_field($form, $name, $value, $echo = ' selected="selected"', $default = false, $type = 'select') {
	if(is_array($value)) {
		foreach($value as $val) {
			if(ctct_get_check_field($name, $val, $echo)) { exit; } // If one is true, stop processing
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
function ctct_check_field($form, $name, $value, $echo = ' selected="selected"', $default = false, $type = 'select') {
	echo ctct_get_check_field($form, $name, $value, $echo, $default, $type);
}

