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

	function __construct( $form = array() ) {

		if( !empty( $form ) ) {
			$this->handle_manual( $form );
		}

		add_action( 'wp_ajax_ctct_form_designer', array(&$this, 'handle_ajax') );
		add_action( 'wp_ajax_nopriv_ctct_form_designer', array(&$this, 'handle_ajax') );

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

		$this->valid_request = true; ///// $valid;

		$form = array();

		if( is_array( $data['form'] ) ) {
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

	function render_field( $field, $cc_request, $currentform ) {

		$fields = $asterisk = $bold = $italic = $required = $requiredFields = $val = $size = '';

		$data = $this->data;

		if(!isset($field['id'])) { return ''; }

		if( is_array($field) ) {
			$field = stripslashes_deep( $field );
			extract($field);
		}

		// Unique ID for labels, etc.
		$uniqute_form_id = isset( $this->request['uniqueformid'] ) ? $this->request['uniqueformid'] : '';

		$this->ctctlog('makeFormField');
		$this->r($field);

		$placeholder = '';

		if(isset($cc_request['fields'][$field['id']]['value']) && $currentform) {
			$val = esc_html( $cc_request['fields'][$field['id']]['value'] );
		} else {

			if( !empty( $field['val'] ) ) {
				$placeholder = " placeholder='".esc_attr( $field['val'] )."'";
			}
			if(!($t == 'b' || $t == 's' || $t == 'ta')) {
				$val = '';
			}
		}

		$label = empty( $field['label'] ) ? '' : esc_html( $field['label'] );

		// If this is the submit button, we add list selection
		$fields .= "<div class='cc_$id kws_input_container gfield'>";

		$class = '';
		if(!empty($bold)) { $class .= ' kws_bold'; }
		if(!empty($italic)) { $class .= ' kws_italic'; }
		$name = "fields[$id]";

		// The field is required
		if( !empty( $field['required'] ) ) {
			$required = " required";
			$reqlabel = isset( $data['text']['reqlabel'] ) ? htmlentities($data['text']['reqlabel']) : __('The %s field is required', 'constant-contact-api');
			$asterisk = !empty( $this->form['reqast'] ) ? '<span class="req gfield_required" title="'.sprintf($reqlabel, $label).'">*</span>' : '';
		}

		switch ( $t ) {

			// It's a textarea (which is the HTML "Form Text" field)
			case 'ta':

				// We allow HTML in the textarea; un-escape the $val
				$val = html_entity_decode( $val );

				// The text entered into the textarea isn't wrapped in <p>
				$val = wpautop( $val );

				// Strip non-standard tags, for a little more security
				$fields .= strip_tags( $val, '<b><strong><em><i><span><u><ul><li><ol><div><attr><cite><a><style><blockquote><q><p><form><br><meta><option><textarea><input><select><pre><code><s><del><small><table><tbody><tr><th><td><tfoot><thead><u><dl><dd><dt><col><colgroup><fieldset><address><button><aside><article><legend><label><source><kbd><tbody><hr><noscript><link><h1><h2><h3><h4><h5><h6><img>');

				break;

			// It's a button (submit)
			case 'b':
			case 's':
				if(!empty($label)) {
					$fields .= "\n<label for='cc_{$uniqute_form_id}{$id}' class='$class'>$label</label>\n";
				} else {
					$fields .= "\n<label for='cc_{$uniqute_form_id}{$id}' class='$class'>\n";
				}

				$fields .= "\n<input type='submit' value='$val' class='$t button' id='cc_{$uniqute_form_id}{$id}' name='constant-contact-signup-submit' >\n<div class='kws_clear'></div>";

				if(empty($label)) { $fields .= "\n</label>"; }
				break;

			// Lists selection
			case 'lists':
				if(!empty($label)) {
					$fields .= "\n<label class='$class'>$label</label>\n";
				}
				$fields .= '<!-- %%LISTSELECTION%% -->';
				break;

			// It's a text field
			default:
				if(!empty($label)) { $fields .= "<label for='cc_{$uniqute_form_id}{$id}' class='$class  gfield_label'>\n$label{$asterisk}</label>"; }
				if(!empty($size) && $t == 't') { $size = " size=\"$size\"";}
				$fields .= "<input type='text' value='$val'$size $placeholder name='".$name."[value]' class='{$t} $class{$required}' id='cc_{$uniqute_form_id}{$id}' />\n";

				if( !empty( $required ) ) {
					$requiredFields = "\n".'<input type="hidden" name="'.$name.'[req]" value="1" />';
				}

				break;
		}

		if( !empty($label) ) {
			$fields .= "\n".'<input type="hidden" name="'.$name.'[label]" value="'.htmlentities($label).'" />';
		}

		$fields .= $requiredFields;
		$fields .= "\n</div>";

		return $fields;

	}

	function html() {

		$data = $this->form;

		// Some very basic verification. Not secure, but better than nothing.
		if( !$this->valid_request ) {

			$this->ctctlog('The form was requested without authentication');

			$output = '<!-- Constant Contact: The form was requested without verification. -->';

			return $output;
		}


		if( !isset($data['form']) && !isset( $data['cc-form-id'] ) ) {

			$this->ctctlog('form does not exist');

			$output = '<!-- Constant Contact: The form you requested does not exist -->';

		} else {

			$output = !empty( $data['toggledesign'] ) ? $this->processStyle().$this->processForm() : $this->processForm();

		}

		return $output;
	}

	function json() {

		if( empty( $this->valid_request ) ) {
			exit( 0 );
		}

		$data = $this->form;

		$output = array(
			'css' => $this->strip_whitespace( $this->processStyle() ),
			'form' => $this->strip_whitespace( $this->processForm() )
		);

		exit( json_encode( $output ) );
	}


	function processForm() {

		$cc_request = array();
		$this->ctctlog('processForm');
		$data = $this->form;

		if(isset($data['f'])) {
			if(isset($data['text'])) {
				$data['text'] = json_decode(stripslashes_deep($data['text']), true);
			}
		}

		extract($data);

		// If there is more than one form...no repeating IDs
		$form_id_attr = 'constant-contact-signup';
		if(isset($cc_signup_count) && $cc_signup_count > 1) {
			$form_id_attr = 'constant-contact-signup-'.$cc_signup_count;
			$this->r('signupcount: '.$cc_signup_count.'; $form_id_attr = '.$form_id_attr);
		} else {
			$cc_signup_count = 0;
		}

		// If we're fetching a specific form (sending a unique form ID with the request),
		// $currentform checks whether the requested form is the form that we're working with.
		$currentform = (isset($cc_request['uniqueformid']) && $cc_request['uniqueformid'] === $this->request['uniqueformid']);

		if(isset($form)) { $selector = ' id="cc_form_'.$form.'"'; } else { $selector = ''; }

		$inputfields = '';

		if( is_array( $data['f'] ) ) {

			$position = array();

			// Make sure they're in the right order
			$f = $this->sort_fields( $data['f'] );

			$lists_shown = false;
			$kws_hide = '';

			foreach($f as $field) {

				$field['size'] = NULL;

                $field['form_id'] = $cc_signup_count;

                // There's a list field
                if( $field['t'] === 'lists' ) {
                	$lists_shown = true;
                }

				$fieldoutput = $this->render_field($field, $cc_request, $currentform);
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
		if($safesubscribe != 'no') {
			$safesubscribelink = '<a href="http://katz.si/safesubscribe" target="_blank" class="cc_safesubscribe safesubscribe_'.$safesubscribe.'" rel="nofollow">Privacy by SafeUnsubscribe</a>';
		}

		$processed_class = $errors = $success = $hidden = $action = '';
		if( empty($data['output']) || $data['output'] === 'html') {

			// If the current form has been submitted, we show the replacement fields
			if( $currentform ) {
				$processed_class = '<!-- %%PROCESSED_CLASS%% -->';
				$errors = '<!-- %%ERRORS%% -->';
				$success = '<!-- %%SUCCESS%% -->';
			}

			$action = '<!-- %%ACTION%% -->';
			$hidden = '<!-- %%HIDDEN%% -->';
		}

		if( empty( $data['cc_success'] ) ) {
			$formInner = $errors . $success . $inputfields . $safesubscribelink . $hidden;
		} else {
			$formInner = $success . $hidden;
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

new CTCT_Form_Designer_Output;
