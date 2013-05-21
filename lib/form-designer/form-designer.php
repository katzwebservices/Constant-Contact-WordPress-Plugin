<?php
/**
 * @package CTCT\Form Designer
 */

class CTCT_Form_Designer extends CTCT_Admin_Page {
	
	var $key = 'constant-contact-forms';
	var $title = 'Form Designer';
	var $messages = array();
	var $form_id = -1;

	protected function addIncludes() {
		global $pagenow, $plugin_page;
		
		if(
		   !is_admin() || 
		   (
		    	!empty($plugin_page) && $plugin_page !== $this->getKey() ||
		    	empty($plugin_page) && @$_GET['page'] !== $this->getKey()
		    )
		) { return; }

		define('CC_FORM_GEN_URL', plugin_dir_url(__FILE__));
		define('CC_FORM_GEN_PATH', plugin_dir_path(__FILE__)); // @ Added 2.0 The full URL to this file

		require_once( CC_FORM_GEN_PATH . 'form-designer-functions.php' );
		require_once( CC_FORM_GEN_PATH . 'process-form.php' );
		require_once( CC_FORM_GEN_PATH . 'widget-form-designer.php');

		add_action('init', 'check_ccfg_compatibility');

		add_shortcode('constantcontactapi', 'constant_contact_signup_form_shortcode');

		// TODO: Enable widget
		// TODO: Backward compatibility
		#add_action('widgets_init', 'constant_contact_form_load_widget');

		if(!($pagenow === 'admin.php' && $plugin_page === 'constant-contact-forms')) { return; }

		// Stop the heartbeat!
		remove_action( 'admin_init', 'wp_auth_check_load' );

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

		?>
		
		<a class="alignright confirm" data-confirm="<?php _e('Delete all form Data? All forms and their settings will be deleted. This cannot be undone. Continue?', 'constant-contact-api'); ?>" data-confirm-again="<?php _e('Are you certain? Forms will be PERMANENTLY DELETED. You will have to re-create all your forms. Continue?', 'constant-contact-api'); ?>" href="<?php echo wp_nonce_url( admin_url('admin.php?page=constant-contact-forms&action=delete_all&amp;form=all'), 'delete-all' ); ?>" id="delete_all_forms"><?php _e('Clear All Forms', 'constant-contact-api'); ?></a>

		<?php

			// TODO: Add Gravity Forms Notice
			// constant_contact_add_gravity_forms_notice();

			if(isset($messages) && is_array($messages)) {
				foreach( $messages as $message ) :
					echo $message . "\n";
				endforeach;
			}
			$formURL = '';
			if($cc_form_selected_id != -1) {
				$formURL = '&form='.(int)$cc_form_selected_id;
			}
			?>
			<div class="hide-if-js">
				<div class="widefat form-table">
					<div class="wrap" style="width:60%; padding:10px 15px;">
						<h2><?php _e('This form creator requires Javascript.', 'constant-contact-api'); ?></h2>
						<p class="description"><?php _e('The form designer uses a lot of Javascript to put together the sweet looking forms that it does, so please <a href="https://www.google.com/adsense/support/bin/answer.py?hl=en&answer=12654" target="_blank">turn Javascript on in your browser</a> and let\'s make some forms together!', 'constant-contact-api'); ?></p>
					</div>
				</div>
			</div>
			<form id="cc-form-settings" action="<?php echo admin_url( 'admin.php?page=constant-contact-forms'.$formURL ); ?>" method="post" enctype="multipart/form-data" class="hide-if-no-js">
			<div id="nav-menus-frame">
			<div id="menu-settings-column" class="metabox-holder">

				<div id="settings">
					<div id="side-sortables" class="meta-box-sortables ui-sortable">
						<?php do_meta_boxes( 'constant-contact-form', 'side', null ); ?>
					</div>
				</div>

			</div><!-- /#menu-settings-column -->
			<div id="menu-management-liquid">
				<!-- <div id="menu-management"> -->

					<style type="text/css">
					.nav-tab a {text-decoration: none; width: 100%; height: 100%; display: block;}
					.nav-tab a:link,.nav-tab a:visited {color: #aaa;}
					.nav-tab a:hover, .nav-tab a:active { color: #d54e21; }
					.nav-tab-active a { color: #464646;}
					</style>
					<div class="nav-tabs">
						<h2 class="nav-tab-wrapper">
						<?php

						foreach( (array) $cc_forms as $_cc_form ) {
							if(!isset($_cc_form['cc-form-id'])) { continue; }
							?>
							<a href="<?php
									echo esc_url(add_query_arg(
										array(
											'action' => 'edit',
											'form' => $_cc_form['cc-form-id'],
										),
										admin_url( 'admin.php?page=constant-contact-forms' )
									));
								?>" class="hide-if-no-js nav-tab<?php if ($cc_form_selected_id == $_cc_form['cc-form-id'] ) { echo ' nav-tab-active'; } ?>">
									<?php echo !empty($_cc_form['truncated_name']) ? esc_html( $_cc_form['truncated_name'] ) : sprintf(__('Form #%d', 'constant-contact-api'), ($_cc_form['cc-form-id'] + 1)); ?>
							</a>
							<?php
						}
						if ( -1 == $cc_form_selected_id ) : ?><span class="nav-tab menu-add-new nav-tab-active">
							<?php printf( '<abbr title="%s">+</abbr>', esc_html__( 'Add form','constant-contact-api' ) ); ?>
						</span><?php else : ?><a href="<?php
							echo esc_url(add_query_arg(
								array(
									'action' => 'edit',
									'form' => -1,
								),
								admin_url( 'admin.php?page=constant-contact-forms' )
							));
						?>" class="nav-tab menu-add-new">
							<?php printf( '<abbr title="%s">+</abbr>', esc_html__( 'Add form','constant-contact-api' ) ); ?>
						</a><?php endif; ?>
						</h2>
					</div>
					<div class="menu-edit" style="border:none;">
						<div id="form-fields">
							<div id="nav-menu-header">
								<?php
								wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
								wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
								wp_nonce_field( 'update-cc-form', 'update-cc-form-nonce' );
								?>
								<input type="hidden" name="action" value="update" />
								<input type="hidden" name="cc-form-id" id="cc-form-id" value="<?php echo (int)$cc_form_selected_id; ?>" />
							</div><!-- END #nav-menu-header -->
							<div id="post-body">
								<div id="post-body-content">
									<?php

										$form = wp_get_cc_form($cc_form_selected_id);

										cc_form_meta_box_formfields($form);
									?>
									<div id="examplewrapper">
										<h3 class="legend"><?php _e('Form Preview', 'constant-contact-api'); ?></h3>
										<div class="grabber"></div>

										<a href="#" id="togglePreview"><?php _e('Toggle Preview', 'constant-contact-api'); ?></a>
									</div><!-- end ExampleWrapper -->

								</div><!-- /#post-body-content -->
								<div class="clear"></div>
							</div><!-- /#post-body -->
						</div><!-- /#update-nav-menu -->
					</div><!-- /.menu-edit -->
				<!-- </div> /#menu-management -->
			</div><!-- /#menu-management-liquid -->
			</div><!-- /#nav-menus-frame -->
		</form><!-- /#tha-form -->
		<?php
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

	$success = get_site_transient($unique_id);
	$success = !empty($success);

	$form = get_site_transient("cc_form_$formid");

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
			$set = set_site_transient("cc_form_$formid", $form, 60*60*24*30);
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


