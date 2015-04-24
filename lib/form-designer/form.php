<?php
/**
 * @package CTCT\Form Designer
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


class CTCT_Form_Designer_Output {

	private $debug = false;
	private $valid_request = false;
	private $data = array();
	private $settings = array();
	private $form;
	private $request;
	static private $instance = NULL;

	function __construct( $form = array() ) {

		if( !empty( $form ) ) {
			$this->handle_manual( $form );
		}

		add_action( 'wp_ajax_ctct_form_designer', array(&$this, 'handle_ajax') );
		add_action( 'wp_ajax_nopriv_ctct_form_designer', array(&$this, 'handle_ajax') );

	}

	/**
	 * Get an instance of the object
	 * @return CTCT_Process_Form
	 */
	static function &getInstance() {

		if( empty( self::$instance ) ) {
			self::$instance = new CTCT_Form_Designer_Output;
		}

		return self::$instance;
	}

	function handle_manual( $data ) {

		$this->process_request( $data );
	}

	function handle_ajax( ) {

		$this->process_request();

		if( empty( $this->form ) ) {
			return;
		}

		$this->json();
	}

	function process_request( $data = array() ) {

		if( empty( $data ) ) {

			$data = $this->debug ? $_REQUEST : $_POST;

		}

		$data = stripslashes_deep( $data );

		if( !empty( $data['data'] ) ) {

			$data = $data['data'];

			$data = json_decode( $data, true );
		}

		$valid = true;

		// Make sure required fields are set
		if( empty( $data['verify'] ) || empty( $data['rand'] ) || empty( $data['cc-form-id'] ) || empty( $data['date'] ) ) {
			$valid = false;
		}

		// Some very basic verification. Not secure, but better than nothing.
		else if( $data['verify'] !== ( $data['rand'] . $data['cc-form-id'] . $data['date'] ) ) {
			$valid = false;
		}

		$this->output_type = ( !empty( $data['output'] ) && $data['output'] === 'html' ) ? 'html' : 'json';

		// @TODO - VALIDATE REQUEST USING NONCE
		$this->valid_request = true; ///// $valid;

		$form = array();

		if( isset( $data['form'] ) && is_array( $data['form'] ) ) {
			foreach ( $data['form'] as $key => $value) {

				// Convert `f[1][name]` input names to array
				if( preg_match('/(.*?)\[([0-9]+)\]\[(.*?)\]/ism', $value['name'], $matches ) ) {

					if( !isset( $form[ $matches[ 1 ] ][ $matches[ 2 ] ] ) ) {
						$form[ $matches[ 1 ] ][ $matches[ 2 ] ] = array();
					}

					$form[ $matches[ 1 ] ][ $matches[ 2 ] ][ $matches[ 3 ] ] = $value['value'];

				} else {

					$form[ $value['name'] ] = $value['value'];

				}
			}
		} else {
			$form = $data;
		}

		if( isset($form['f']) && is_array($form['f'])) {

			foreach( $form['f'] as $key => $field ) {

				if(!isset($field['pos']) || !isset($field['id'])) {

					$this->ctctlog('pos not set for '.$key);

					unset($form['f'][ $key ]);
				}

			}

		}

		$this->form = $form;

		unset( $data['form'], $form );

		$this->request = $data;

	}

	static function get_field_id( $passed_form_id = NULL, $field_name ) {
		return "cc_{$passed_form_id}_{$field_name}";
	}

	/**
	 * Get the class string for the field.
	 * @param  array  $field Field passed to render_field()
	 * @return string        HTML string
	 */
	function get_field_class( $field = array() ) {

		$class = '';

		if( !empty( $field['bold'] ) ) {
			$class .= ' kws_bold';
		}

		if( !empty( $field['italic'] ) ) {
			$class .= ' kws_italic';
		}

		return $class;
	}

	function render_field( $passed_field ) {

		$return = $requiredFields = $val = $size = '';

		$field = $passed_field;

		if( empty( $field['id'] ) ) {
			return '';
		}

		$field = stripslashes_deep( $field );

		$unique_form_id = isset( $this->request['uniqueformid'] ) ? $this->request['uniqueformid'] : '';

		// Unique ID for labels, etc.
		$field_id = self::get_field_id( $unique_form_id, $field['id'] );

		do_action('ctct_debug', 'render_field', $field);

		$val = isset( $field['val'] ) ? $field['val'] : '';
		$placeholder = '';
		if( !empty( $this->form['cc_request'] ) && isset($this->form['cc_request']['fields'][$field['id']]['value']) && $this->is_current_form() ) {
			$val = esc_html( $this->form['cc_request']['fields'][$field['id']]['value'] );
		} else if( !empty( $field['val'] ) ) {
			$placeholder = " placeholder='".esc_attr( $field['val'] )."'";
		}

		$label = empty( $field['label'] ) ? '' : esc_html( $field['label'] );

		// If this is the submit button, we add list selection
		$return = "<div class='cc_{$field['id']} kws_input_container gfield'>";

		$class = $this->get_field_class( $field );


		$required = $asterisk = '';

		// The field is required
		if( !empty( $field['required'] ) ) {
			$required = " required";
			$reqlabel = isset( $this->data['text']['reqlabel'] ) ? htmlentities($this->data['text']['reqlabel']) : __('The %s field is required', 'ctct');
			$asterisk = !empty( $this->form['reqast'] ) ? '<span class="req gfield_required" title="'.esc_attr( sprintf($reqlabel, $label) ).'">*</span>' : '';
		}

		// Field name attribute
		$name_attribute = "fields[{$field['id']}]";

		switch ( $field['t'] ) {

			// It's the HTML "Form Text" field
			case 'ta':

				// We allow HTML in the textarea; un-escape the $val
				$val = html_entity_decode( $val );

				// Wrap text in <p>
				$val = wpautop( $val );

				// Strip non-standard tags, for a little more security
				$return .= strip_tags( $val, '<b><strong><em><i><span><u><ul><li><ol><div><attr><cite><a><style><blockquote><q><p><form><br><meta><option><textarea><input><select><pre><code><s><del><small><table><tbody><tr><th><td><tfoot><thead><u><dl><dd><dt><col><colgroup><fieldset><address><button><aside><article><legend><label><source><kbd><tbody><hr><noscript><link><h1><h2><h3><h4><h5><h6><img>');

				break;

			// It's a button (submit)
			case 'b':
			case 's':
				if(!empty($label)) {
					$return .= "\n<label for='{$field_id}' class='$class'>$label</label>\n";
				} else {
					$return .= "\n<label for='{$field_id}' class='$class'>\n";
				}

				$return .= "\n<input type='submit' value='$val' class='{$field['t']} button' id='{$field_id}' name='constant-contact-signup-submit' >\n<div class='kws_clear'></div>";

				if(empty($label)) { $return .= "\n</label>"; }
				break;

			// Lists selection
			case 'lists':
				if(!empty($label)) {
					$return .= "\n<label class='$class'>$label</label>\n";
				}
				$return .= '<!-- %%LISTSELECTION%% -->';
				break;

			// It's a text field
			default:
				if(!empty($label)) { $return .= "<label for='{$field_id}' class='$class gfield_label'>\n$label{$asterisk}</label>"; }

				if( !empty( $placeholder) ) {
					$val = '';
				}

				$return .= "<input type='text' value='$val' $placeholder name='".$name_attribute."[value]' class='{$field['t']} $class{$required}' id='{$field_id}' />\n";

				if( !empty( $required ) ) {
					$requiredFields = "\n".'<input type="hidden" name="'.$name_attribute.'[req]" value="1" />';
				}

				break;
		}

		if( !empty($label) ) {
			$return .= "\n".'<input type="hidden" name="'.$name_attribute.'[label]" value="'.htmlentities($label).'" />';
		}

		$return .= $requiredFields;
		$return .= "\n</div>";

		return $return;

	}

	function html() {

		// Some very basic verification. Not secure, but better than nothing.
		if( !$this->valid_request ) {

			$this->ctctlog('The form was requested without authentication');

			$output = '<!-- Constant Contact: The form was requested without verification. -->';

			return $output;
		}


		if( !isset($this->form['form']) && !isset( $this->form['cc-form-id'] ) ) {

			$this->ctctlog('form does not exist');

			$output = '<!-- Constant Contact: The form you requested does not exist -->';

		} else {

			$output = !empty( $this->form['toggledesign'] ) || !isset( $this->form['toggledesign'] ) ? $this->processStyle().$this->processForm() : $this->processForm();

		}

		return $output;
	}

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
	static function signup_form( $passed_args, $echo = true) {

		do_action('ctct_debug', 'constant_contact_public_signup_form', $passed_args, @$_POST);

	    $output = $error_output = $success = $process_class = $hiddenlistoutput = '';
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

	    $form = CTCT_Form_Designer_Helper::get_form($settings['formid']);

	    // Merge using the form settings
	    $settings = shortcode_atts( $settings, $form );

	    // Override one more time using the passed args as the final
	    $settings = shortcode_atts( $settings, $passed_args );

	    // BACKWARD COMPATIBILITY
	    $settings['list_selection_format'] = empty( $settings['list_selection_format'] ) ? $settings['list_format'] : $settings['list_selection_format'];

	    extract($settings, EXTR_SKIP);

	    // The form does not exist.
	    if(!$form) {

	        do_action('ctct_log', sprintf('Form #%s does not exist. Called on %s', $formid, esc_url(add_query_arg(array()))));

	        if(current_user_can('manage_options')) {
	            return '<!-- Constant Contact API Error: Form #'.$formid.' does not exist. -->';
	        }

	        return false;
	    }

	    // If other lists aren't passed to the function,
	    // use the default lists defined in the form designer.
	    if( empty($lists) && !empty( $form['lists'] ) ) { $lists = $form['lists']; }

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
	        $process_class = ' has_errors';

	        $error_output = '';

	        do_action('ctct_debug', 'Handling errors in constant_contact_public_signup_form', $errors);

	        // Set up error display
	        $error_output .= '<div id="constant-contact-signup-errors" class="error">';
	        $error_output .= '<ul>';
	        foreach ($errors as $error ) {

	        	$error_data = $error->get_error_data();
	        	$error_label_for = '';

	        	// We only want simple error data that we can output here. Akismet triggers an array instead of a string.
	        	if( is_string( $error_data ) ) {

		            /**
		             * The input ID is stored in the WP_Error error data.
		             * @see CTCT_Process_Form::checkRequired()
		             */
		            $error_field_id = CTCT_Form_Designer_Output::get_field_id( $ProcessForm->id(), $error_data );

		            $error_label_for = ' for="'.esc_attr( $error_field_id ).'"';

		        }

	            $error_output .= '<li><label'.$error_label_for.'>'.$error->get_error_message().'</label></li>';
	        }
	        $error_output .= '</ul>';
	        $error_output .= '</div>';

	        // Filter output so text can be modified by plugins/themes
	        $error_output = apply_filters('constant_contact_form_errors', $error_output);

	    } elseif( is_a( $ProcessForm->getResults(), 'Ctct\Components\Contacts\Contact') ) {
	        $process_class = ' has_success';
	        $success = '<p class="success cc_success">';
	        $success .= esc_html__('Success, you have been subscribed.', 'ctct');
	        $success .= '</p>';
	        $success = apply_filters('constant_contact_form_success', $success);

	        // Force refresh of the form
	    }

	    $form = str_replace('<!-- %%SUCCESS%% -->', $success, $form);
	    $form = str_replace('<!-- %%ERRORS%% -->', $error_output, $form);
	    $form = str_replace('<!-- %%PROCESSED_CLASS%% -->', $process_class, $form);

	    // Generate the current page url, removing the success _GET query arg if it exists
	    $current_page_url = remove_query_arg('success', CTCT_Form_Designer_Helper::current_page_url());
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
	            <input type="hidden" name="cc_referral_post_id" value="'. get_the_ID() .'" />
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

	function json() {

		if( empty( $this->valid_request ) ) {
			exit( 0 );
		}

		$output = array(
			'css' => $this->strip_whitespace( $this->processStyle() ),
			'form' => $this->strip_whitespace( $this->processForm() )
		);

		exit( json_encode( $output ) );
	}

	/**
	 * If we're fetching a specific form (sending a unique form ID with the request),
	 * $currentform checks whether the requested form is the form that we're working with.
	 * @return boolean [description]
	 */
	function is_current_form() {
		return (isset($this->form['cc_request']['uniqueformid']) && $this->form['cc_request']['uniqueformid'] === $this->request['uniqueformid']);
	}

	function get_form_counter() {
		global $cc_signup_count;

		$cc_signup_count = !isset( $cc_signup_count ) ? 0 : $cc_signup_count;

		$cc_signup_count++;

		return $cc_signup_count;
	}

	function processForm() {

		$this->ctctlog('processForm');
		#$data = $this->form;

		if(isset($this->form['f'])) {
			if(isset($this->form['text'])) {
				$this->form['text'] = json_decode(stripslashes_deep($this->form['text']), true);
			}
		}

		$form_id = $this->get_form_counter();

		// If there is more than one form...no repeating IDs
		$form_id_attr = empty( $form_id ) ? 'constant-contact-signup' : 'constant-contact-signup-'.$form_id;

		$this->r('signupcount: '.$form_id.'; $form_id_attr = '.$form_id_attr);


		if( isset( $this->form['form'] ) ) {
			$selector = ' id="cc_form_'.$this->form['form'].'"';
		} else {
			$selector = '';
		}

		$inputfields = '';

		if( is_array( $this->form['f'] ) ) {

			$position = array();

			// Make sure they're in the right order
			$fields = $this->sort_fields( $this->form['f'] );

			$lists_shown = false;
			$kws_hide = '';

			foreach($fields as $field) {

				$field['size'] = NULL;

                $field['form_id'] = $form_id;

                // There's a list field
                if( $field['t'] === 'lists' ) {
                	$lists_shown = true;
                }
                $fieldoutput = $this->render_field($field );
				$inputfields .= $fieldoutput;
            }

            // Make sure the lists fields are shown if not already added.
            if(!$lists_shown) {
            	$inputfields .= '<!-- %%LISTSELECTION%% -->';
            }

            // We need a submit field for some strange reason in some IE versions
            // Can't use a hidden field, either.
            $inputfields .= "\n<input type='submit' style='position:absolute; width:0;height:0;left:-9999px;' name='constant-contact-signup-submit' />";

            $inputfields = "\n".'<div class="kws_input_fields gform_fields">'.$inputfields."\n".'</div>';
        }

        $safesubscribelink = '';
		if( !empty( $this->form['safesubscribe'] ) && $this->form['safesubscribe'] != 'no') {
			$safesubscribelink = '<a href="http://katz.si/safesubscribe" target="_blank" class="cc_safesubscribe safesubscribe_'.$this->form['safesubscribe'].'" rel="nofollow">Privacy by SafeUnsubscribe</a>';
		}

		$processed_class = $errors = $success = $hidden = $action = '';
		if( empty($this->form['output']) || $this->form['output'] === 'html') {

			// If the current form has been submitted, we show the replacement fields
			if( $this->is_current_form() ) {
				$processed_class = '<!-- %%PROCESSED_CLASS%% -->';
				$errors = '<!-- %%ERRORS%% -->';
				$success = '<!-- %%SUCCESS%% -->';
			}

			$action = '<!-- %%ACTION%% -->';
			$hidden = '<!-- %%HIDDEN%% -->';
		}

		if( $this->is_current_form() && !empty( $this->form['cc_success'] ) ) {
			$formInner = $success . $hidden;
		} else {
			$formInner = $errors . $success . $inputfields . $safesubscribelink . $hidden;
		}


$form = <<<EOD
<div class="kws_form gform_wrapper$processed_class"$selector>
<form id="$form_id_attr" action="$action" method="post">
	$formInner
	<div class="kws_clear"></div>
	<!-- Form Generated by the Constant Contact API WordPress Plugin by Katz Web Services, Inc. -->
</form>
</div>
EOD;

		if( !$this->debug ) {
			$form = str_replace(array("\n", "\r", "\t"), ' ', $form);
			$form = preg_replace('/\s\s/ism', ' ', $form);
		}

		return $form;
	}

	function sort_fields( $f ) {

		if( !is_array( $f ) ) {
			return array();
		}

		$position = array();

		foreach ($f as $key => $field) {
			if(!isset($field['pos'])) {
				unset($f[$key]);
				continue;
			}

		    $position[$key] = $field['pos'];
		}

		array_multisort($position, SORT_ASC, $f);

		return $f;

	}

	function processStyle() {

		$this->ctctlog('start processStyle');

		ob_start();
		include 'css.php';
		$css = ob_get_clean();

		$css = apply_filters( 'ctct_form_css', $css, $this );

		return '<style type="text/css">'.$css.'</style>';
	}

	function strip_whitespace( $content ) {
		$content = str_replace("\n", ' ', $content);
		$content = str_replace("\r", ' ', $content);
		$content = str_replace("\t", ' ', $content);
		$content = str_replace('  ', ' ', $content);

		return $content;
	}

	function ctctlog( $info ) {

		if( $this->debug === false ) { return; }

		echo '<h4>'.$info.'</h4>';
	}

	function r($content, $die = false, $echo=true) {

		if( !$this->debug ) { return; }

		try {
			if(!function_exists('htmlentities_recursive')) {
				function htmlentities_recursive($data) {
				    foreach($data as $key => $value) {
				        if (is_array($value)) {
				            $data[$key] = htmlentities_recursive($value);
				        } else {
				        	if(is_string($value)) {
				            $data[$key] = htmlentities($value);
				            }
				        }
				    }
				    return $data;
				}
			}
			if(is_array($content)) { $content = htmlentities_recursive($content); }
			$output = '<pre style="text-align:left; margin:10px; padding:10px; background-color: rgba(255,255,255,.95); border:3px solid rgba(100,100,100,.95); overflow:scroll; max-height:400px; float:left; width:90%; max-width:800px; white-space:pre;">';
			$output .= print_r($content, true); //print_r(mixed expression [, bool return])
			$output .= '</pre>';
			if($echo) {	echo $output; } else { return $output; }
			if($die)  { die(); }
		} catch(Exception $e) {	}
	}

	function get_font_stack( $id = '' ) {

		switch($id) {

			case 'inherit':
				return 'inherit';
				break;
			case 'times':
				return "'Times New Roman', Times, Georgia, serif";
				break;
			case 'georgia':
				return "Georgia,'Times New Roman', Times, serif";
				break;
			case 'palatino':
				return "'Palatino Linotype', Palatino, 'Book Antiqua',Garamond, Bookman, 'Times New Roman', Times, Georgia, serif";
				break;
			case 'garamond':
				return "Garamond,'Palatino Linotype', Palatino, Bookman, 'Book Antiqua', 'Times New Roman', Times, Georgia, serif";
				break;
			case 'bookman':
				return "Bookman,'Palatino Linotype', Palatino, Garamond, 'Book Antiqua','Times New Roman', Times, Georgia, serif";
				break;
			case 'helvetica':
				return "'Helvetica Neue',HelveticaNeue, Helvetica, Arial, Geneva, sans-serif";
				break;
			case 'arial':
				return "Arial, Helvetica, sans-serif";
				break;
			case 'lucida':
				return "'Lucida Grande', 'LucidaGrande', 'Lucida Sans Unicode', Lucida, Verdana, sans-serif";
				break;
			case 'verdana':
				return "Verdana, 'Lucida Grande', Lucida, TrebuchetMS, 'Trebuchet MS',Geneva, Helvetica, Arial, sans-serif";
				break;
			case 'trebuchet':
				return "'Trebuchet MS', Trebuchet, Verdana, sans-serif";
				break;
			case 'tahoma':
				return "Tahoma, Verdana, Arial, sans-serif";
				break;
			case 'franklin':
				return "'Franklin Gothic Medium','FranklinGotITC','Arial Narrow Bold',Arial,sans-serif";
				break;
			case 'impact':
				return "Impact, Chicago, 'Arial Black', sans-serif";
				break;
			case 'arialblack':
				return "'Arial Black',Impact, Arial, sans-serif";
				break;
			case 'gillsans':
				return "'Gill Sans','Gill Sans MT', 'Trebuchet MS', Trebuchet, Verdana, sans-serif";
				break;
			case 'courier':
				return "'Courier New', Courier, Monaco, monospace";
				break;
			case 'lucidaconsole':
				return "'Lucida Console', Monaco, 'Courier New', Courier, monospace";
				break;
			case 'comicsans':
				return "'Comic Sans MS','Comic Sans', Sand, 'Trebuchet MS', cursive";
				break;
			case 'papyrus':
				return "Papyrus,'Palatino Linotype', Palatino, Bookman, fantasy";
				break;
		}
	}

}

CTCT_Form_Designer_Output::getInstance();
