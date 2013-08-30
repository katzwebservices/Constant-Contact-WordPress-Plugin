<?php

/**
 * @package CTCT\Form Designer
 *
 * @todo Add `cc_redirect_url`-type setting
 * @todo Test widget
 * @todo Test backward compatibility
 */
class CTCT_Form_Designer extends CTCT_Admin_Page {

	var $key = 'constant-contact-forms';
	var $title = 'Form Designer';
	var $messages = array();
	var $form_id = -1;

	protected function addIncludes() {
		global $pagenow, $plugin_page;

		if(is_admin() && !empty($plugin_page) && ($plugin_page !== $this->getKey() || empty($plugin_page) && @$_GET['page'] !== $this->getKey())) {
			return;
		}

		define('CC_FORM_GEN_URL', plugin_dir_url(__FILE__));
		define('CC_FORM_GEN_PATH', plugin_dir_path(__FILE__)); // @ Added 2.0 The full URL to this file

		require_once( CC_FORM_GEN_PATH . 'form-designer-functions.php' );
		require_once( CC_FORM_GEN_PATH . 'widget-form-designer.php');

		add_action('init', 'check_ccfg_compatibility');

		add_shortcode('constantcontactapi', 'constant_contact_signup_form_shortcode');

		add_action('widgets_init', 'constant_contact_form_load_widget');

		if(!($pagenow === 'admin.php' && $plugin_page === 'constant-contact-forms')) { return; }

		add_action('admin_print_scripts', 'constant_contact_admin_widget_scripts');
		add_filter( 'teeny_mce_before_init', 'ccfg_tiny_mce_before_init', 10, 2);
		add_filter( 'tiny_mce_before_init', 'ccfg_tiny_mce_before_init', 10, 2);
	}

	public function addScripts($value='') {
		global $plugin_page;

		if(!is_admin() || $plugin_page !== $this->getKey()) { return; }

		wp_enqueue_style('admin');
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_style( 'cc-style', plugin_dir_url(__FILE__).'css/style.css');

		// Otto is the man.
		wp_enqueue_script( 'cc-code', plugin_dir_url(__FILE__).'js/cc-code-dev.js', array('wp-color-picker', 'jquery', 'jquery-ui-core', 'jquery-ui-tabs', 'jquery-ui-sortable'));
		$params = array('path' => plugin_dir_url(__FILE__), 'rand' => mt_rand(0, 100000000), 'text' => cc_form_text());
		wp_localize_script('cc-code', 'ScriptParams', $params);
	}
	protected function add() {}
	protected function edit() {}

	protected function view() {

			$compat = check_ccfg_compatibility();
			if(empty($compat)) { return false; }

			global $cc_form_selected_id;
			$cc_forms = array();


			// Container for any messages displayed to the user
			$messages = array();

			$_form = wp_get_cc_form( $cc_form_selected_id );

			// Container that stores the name of the active menu
			$cc_form_selected_title = constant_contact_get_form_title($_form);


			// Work with the actions and echo a message if there is one.
			$messages = $this->messages;

			// Get all forms
			$cc_forms = wp_get_cc_forms();

			// The menu id of the current menu being edited
			$cc_form_selected_id = cc_form_get_selected_id($cc_forms);

		?>
		<?php

		wp_cc_form_setup($_form);

		include(CC_FORM_GEN_PATH.'views/form-designer.php');

	}

	protected function single() {}

	protected function processForms() {

			if(!isset($_REQUEST['cc-form-id'])) { return; }

			global $cc_form_selected_id;

			// Allowed actions: add, update, delete
			$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'edit';

			$cc_form_selected_id = cc_form_get_selected_id();

			if($action === 'edit') { return; }

			$messages = array();

			switch ( $action ) {
				case 'delete_all':
					delete_option('cc_form_design');
					break;
				case 'delete':

					$cc_form_selected_id = isset($_REQUEST['form']) ? (int)$_REQUEST['form'] : $cc_form_selected_id;

					if ( $deleted_form = wp_get_cc_form( $cc_form_selected_id ) ) {

						$delete_cc_form = wp_delete_cc_form( $cc_form_selected_id );

						if ( is_wp_error($delete_cc_form) ) {
							$messages[] = '<div id="message" class="error"><p>' . $delete_cc_form->get_error_message() . '</p></div>';
						} else {
							$messages[] = '<div id="message" class="updated"><p>' . __('The form '.$deleted_form['form-name'].' has been successfully deleted.','constant-contact-api') . '</p></div>';
							// Select the next available menu
							$cc_form_selected_id = -1;
							$_cc_forms = wp_get_cc_forms( array('orderby' => 'name') );
							foreach( $_cc_forms as $index => $_cc_form ) {
								if ( $index == count( $_cc_forms ) - 1 ) {
									$cc_form_selected_id = $_cc_form['cc-form-id'];
									break;
								}
							}
						}
						$_REQUEST['deleted'] = 1;
					} else {
						$_REQUEST['deleted'] = 0;
						// Reset the selected menu
						$cc_form_selected_id = -1;
						unset( $_REQUEST['form'] );
						$messages[] = '<div id="message" class="error"><p>'.__('The form could not be deleted. The form may have already been deleted.', 'constant-contact-api').'</p></div>';
					}
					break;

				case 'update':
		#			check_admin_referer( 'update-nav_menu', 'update-nav-menu-nonce' );
					// Add Form
					if ( -1 == $cc_form_selected_id ) {
						$new_form_title = trim( esc_html( $_REQUEST['form-name'] ) );
						if($new_form_title == 'Enter form name here') { $new_form_title = ''; }

							$cc_form_selected_id = wp_create_cc_form();

							if ( is_wp_error( $cc_form_selected_id ) ) {
								$messages[] = '<div id="message" class="error"><p>' . $cc_form_selected_id->get_error_message() . '</p></div>';
							} else {
								$messages[] = '<div id="message" class="updated"><p>' . sprintf( __('The <strong>%s</strong> form has been successfully created.','constant-contact-api'), $new_form_title ) . '</p></div>';
							}

					// update existing form
					} else {
						if(wp_get_cc_form($cc_form_selected_id)) {
							$request = wp_update_cc_form_object($cc_form_selected_id, $_REQUEST);
							if(!is_wp_error($request)) {
								$messages[] = '<div id="message" class="updated after-h2 fade"><p>' . sprintf( __('The <strong>%s</strong> form has been updated.','constant-contact-api'), $request['form-name'] ) . '</p></div>';
							} else {
								$messages[] = '<div id="message" class="error"><p>' . $cc_form_selected_id->get_error_message() . '</p></div>';
							}
						} else {

						}
					}
					break;
			}

			$this->messages = $messages;

	}
}

$CTCT_Form_Designer = new CTCT_Form_Designer;



/**
 * Add a callback to TinyMCE
 *
 * This callback allows for the form designer's `wp_editor` JS code to update the example every time there's a keyup or change
 *
 * @param  array $mceInit   TinyMCE settings array
 * @param  string $editor_id The ID the TinyMCE editor's using
 * @return array            TinyMCE settings array, modified.
 */
function ccfg_tiny_mce_before_init( $mceInit, $editor_id) {
	$mceInit['setup'] = 'function(ed){ ed.onKeyUp.add(window.triggerTextUpdate); ed.onChange.add(window.triggerTextUpdate); }';
	return $mceInit;
}

/**
 * @todo Confirm CTCT exists
 */
function check_ccfg_compatibility() {
	global $cc;
	wp_dequeue_script('heartbeat');
	if(!defined('CTCT_APIKEY')) {
		return null;
	}
	return true;
}

function constant_contact_form_load_widget() {

	if(!check_ccfg_compatibility()) { return; }

	// Instead of forcing paragraphs, we're adding a filter that can be removed and modified.
	add_filter('cc_widget_description', 'wpautop');

	require_once('widget-form-designer.php');
	register_widget( 'constant_contact_form_widget' );
}

function constant_contact_admin_widget_scripts() {
	global $pagenow;
	if($pagenow == 'widgets.php' && !empty($compat)) {
		wp_enqueue_script( 'admin-cc-widget', plugin_dir_url(__FILE__).'js/admin-cc-widget.js' );
	}
}

function is_widget_active_in_sidebar($name) {
	foreach($GLOBALS['_wp_sidebars_widgets'] as $key => $widgetarea) {
		if($key != 'wp_inactive_widgets') {
			if(is_array($widgetarea) && !empty($widgetarea)) {
				$length = strlen($name);
				foreach($widgetarea as $widget) {
					if(substr($widget, 0, $length) == $name) { return true; }
				}
			}
		}
	}
	return false;
}

function constant_contact_retrieve_form($formid, $force_update=false, $unique_id = '', $lists = array()) {

	@$_GET['cc_signup_count']++;

	$formid = (int)$formid;

	$success = get_transient($unique_id);
	$success = !empty($success);

	$form = get_transient("cc_form_$formid");

	// If it is an array and we are not forcing an update, return the data
	if(!empty($form) && !$success && !$force_update && !isset($_GET['asdasdas'])) {
		do_action('ctct_debug', 'Returning Cached Form #'.$formid, $form);

		// Basic form validation - make sure it's got basic
		if(preg_match('/kws_form/m', $form)) {
			return $form;
		}
	}

	$form = wp_get_cc_form($formid);

	if($form && is_array($form)) {
		// Just the items we need, please.
		unset($form['_wp_http_referer'], $form['update-cc-form-nonce'], $form['meta-box-order-nonce'], $form['closedpostboxesnonce'], $form['save_form'], $form['page'], $form['form-name'], $form['action'], $form['form-style'], $form['presets'], $form['truncated_name']);
	} else {
		$form = array('formOnly'=>true);
	}

	$form['output'] = 'html';
	$form['time'] = time();
	$form['verify'] = sha1($unique_id.$form['time']);
	$form['echo'] = true;
	$form['path'] = CC_FORM_GEN_URL;
	$form['cc_success'] = $success;
	$form['cc_signup_count'] = $_GET['cc_signup_count'];
	$form['cc_request'] = empty($_REQUEST['uniqueformid']) ? array() : $_REQUEST;
	$form['uniqueformid'] = $unique_id;
	$form['debug'] = (current_user_can('manage_options') && isset($_GET['debug']));

	// Get the form from form.php
	$response = wp_remote_request( CC_FORM_GEN_URL.'form.php', array(
	               'method' => 'POST',
	               'timeout' => 20,
	               'body' => $form,
	               'sslverify' => false,
	               'compress' => true,
	               'local' => true
	           ));

	do_action('ctct_debug', 'Requesting Form Data for Form #'.$formid, array('POST body' => $form, 'POST response:' => $response));

	if( is_wp_error( $response ) ) {
		do_action('ctct_log', $response, 'error');
		if(current_user_can('manage_options')) {
			echo '<!-- Constant Contact API Error: `wp_remote_post` failed with this error: '.$response->get_error_message().' -->';
		}
		return false;
	} else {
		$form = $response['body'];
		if(empty($_POST) && !$success) {
			// Save the array into the cc_form_id transient with a 30 day expiration
			$set = set_transient("cc_form_$formid", $form, 60*60*24*30);
			if(!$set) {
				do_action('ctct_log', 'Setting cc_form_'.$formid.' Transient failed', $form);
			}
		}

		return $form;
	}
}

function cc_form_get_selected_id($allForms = array()) {

	if(isset($_REQUEST['form']) && (empty($_REQUEST['action']) || @$_REQUEST['action'] === 'edit' || @$_REQUEST['action'] === 'update')) {
		return intval($_REQUEST['form']);
	}


	if(empty($allForms)) {
		$cc_form_selected_id = isset($_REQUEST['cc-form-id']) ? (int)$_REQUEST['cc-form-id'] : -1;
	} else {
		$lastForm = end($allForms);
		$cc_form_selected_id = $lastForm['cc-form-id'];

		// Intstead of always showing new form, show last form possible.
		$_REQUEST['form'] = (int)$cc_form_selected_id;
		$_REQUEST['action'] = 'edit';
	}

	return $cc_form_selected_id;
}

// register admin menu action



if(!function_exists('wp_dequeue_script')) {
	function wp_dequeue_script( $handle ) {
	    global $wp_scripts;
	    if ( !is_a($wp_scripts, 'WP_Scripts') )
	        $wp_scripts = new WP_Scripts();
 		$wp_scripts->dequeue( $handle );
 	}
}
if(!function_exists('wp_dequeue_style')) {
	function wp_dequeue_style( $handle ) {
	    global $wp_styles;
	    if ( !is_a($wp_styles, 'WP_Styles') )
	        $wp_styles = new WP_Styles();
 		$wp_styles->dequeue( $handle );
 	}
}

function cc_form_text() {
	return apply_filters('constant_contact_form_design_custom_text', array(
		'reqlabel' => __('The %s field is required', 'constant-contact-api'),
	));
}

function wp_cc_form_setup($form = false) {
	global $cc, $cc_form_selected_id;

	require_once( 'form-designer-meta-boxes.php' );

	$form = empty($form) ? wp_get_cc_form($cc_form_selected_id) : $form;

	$getHolder = $_GET;
	add_meta_box( 'formname', __( 'Form Name','constant-contact-api' ), 'cc_form_meta_box_actions' , 'constant-contact-form', 'side', 'default', array($form));
	add_meta_box( 'formlists_select', __( 'Default Newsletters','constant-contact-api' ), 'cc_form_meta_box_formlists_select' , 'constant-contact-form', 'side', 'default', array($form));
	add_meta_box( 'formfields_select', __( 'Form Fields','constant-contact-api' ), 'cc_form_meta_box_formfields_select' , 'constant-contact-form', 'side', 'default', array($form));
	#add_meta_box( 'usedesign', __( 'Style the Form?','constant-contact-api' ), 'cc_form_meta_box_styleform' , 'constant-contact-form', 'side', 'default', array($form));
	add_meta_box( 'designoptions', __( 'Design Presets','constant-contact-api' ), 'cc_form_meta_box_designoptions' , 'constant-contact-form', 'side', 'default', array($form));
	add_meta_box( 'backgroundoptions', __('Background','constant-contact-api'), 'cc_form_meta_box_backgroundoptions' , 'constant-contact-form', 'side', 'default', array($form));
	add_meta_box( 'border', __('Border','constant-contact-api'), 'cc_form_meta_box_border' , 'constant-contact-form', 'side', 'default', array($form));
	add_meta_box( 'fontstyles', __('Text Styles & Settings','constant-contact-api'), 'cc_form_meta_box_fontstyles' , 'constant-contact-form', 'side', 'default', array($form));
	add_meta_box( 'formdesign', __('Padding & Align','constant-contact-api'), 'cc_form_meta_box_formdesign' , 'constant-contact-form', 'side', 'default', array($form));
	$_GET = $getHolder;
}

function constant_contact_get_form_title($form = false) {
	global $cc_form_selected_id;

	if(empty($form)){
		$form = wp_get_cc_form( $cc_form_selected_id );
	}

	return !empty($form['form-name']) ? $form['form-name'] : apply_filters('constant_contact_default_form_name', __('Enter form name here', 'constant-contact-api'));
}

