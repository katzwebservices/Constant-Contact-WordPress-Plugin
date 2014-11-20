<?php
/**
 * Generate the settings page and manage the settings.
 * @package CTCT
 * @version 3.0
 */


/**
 * Generate the settings page and manage the settings.
 * @package CTCT
 * @version 2.0.37
 */
class CTCT_Settings extends CTCT_Admin_Page {

	/**
	 * Plugin info, including version, key, etc. Used to check if update exists.
	 * @var array
	 */
	protected static $version_info;

	/**
	 * Options array stored in object to reduce potential DB queries (which are slower, even if cached)
	 * @var array
	 */
	protected static $options = array();

	function add() {}
    function edit() {}
    function view() {}
    function add_menu() {}
    function content() {}
    function single() { }
    function processForms() { }

    function addActions() {

    	/**
    	 * Filter the settings to use the $_GET request to modify plugins
    	 */
    	add_filter('ctct_settings_filter', array(&$this,'debug_modify_settings'));

    	add_action('admin_init', array(&$this, 'settings_init') );

    	add_action('ctct_token_updated', array( __CLASS__, 'flush_transients') );

    }

    function print_styles() {
    	wp_enqueue_script( 'thickbox' );

    	parent::print_styles();
    }

    /**
	 * When settings are changed, delete the CTCT transients
	 *
	 * This is a bit better for data security, as it were.
	 *
	 */
	static function flush_transients($token = array()) {
		global $wpdb;
		$query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}options WHERE `option_name` LIKE %s OR `option_name` LIKE %s", '%transient_ctct%', '%transient_timeout_ctct%');
		$wpdb->query($query);
	}

	/**
	 * Override settings if logged in as admin by passing URL args
	 * The key should be the ID of the setting and the value should be the value of the setting (serialized data if an array)
	 * @see CTCT_Settings::settings_init() Use the `id` of the setting as the key of the $_GET param
	 * @param  array $settings The array of settings being filtered.
	 * @return array           The modified array.
	 */
	static function debug_modify_settings($settings) {

		$debug = current_user_can('manage_options') && isset($_GET['debug']);

		// if not logged in as admin, return $settings
		if(!$debug) { return $settings; }

		foreach($_GET as $k => $v) {
			if(isset($settings[$k])) {
				$settings[$k] = maybe_unserialize( $v );
			}
		}

		return $settings;
	}

	/**
	 * Check if specified plugin is active, inactive or not installed
	 *
	 * @access public
	 * @static
	 * @param string $location (default: '')
	 * @return void
	 */
	static function get_plugin_status( $location = '' ) {

		if( ! function_exists('is_plugin_active') ) {
			include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}

		if( is_plugin_active( $location ) ) {
			return true;
		}

		if( !file_exists( trailingslashit( WP_PLUGIN_DIR ) . $location ) ) {
			return false;
		}

		if( is_plugin_inactive( $location ) ) {
			return 'inactive';
		}
	}

	/**
	 * Show different messages for the different statuses of supporting plugins
	 *
	 * Such as "Install Akismet", "Activate Akismet - Installed but inactive", and "Installed and active"
	 *
	 * @param  string $message_key Key of plugin: `wangguard` or `akismet`
	 * @return string|null              If plugin message exists, show the message. Otherwise, null.
	 */
	static function get_plugin_status_message( $message_key ) {

		$plugins = array(
			'wangguard' => array(
				'name' => 'WangGuard',
				'path' => 'wangguard/wangguard-admin.php'
			),
			'akismet' => array(
				'name' => 'Akismet',
				'path' => 'akismet/akismet.php'
			),
		);

		$plugin_messages = array();

		foreach ($plugins as $key => $plugin ) {

			$status = self::get_plugin_status( $plugin['path'] );

			if( $status === true ) {
				$message = esc_html__('(Installed and active)', 'ctct');

			} elseif( $status === false ) {

				$title = sprintf( esc_html__('Install %s', 'ctct'), $plugin['name'] );
				$message = sprintf('(<a class="thickbox" href="%s" class="thickbox">%s</a>)', admin_url('plugin-install.php?tab=plugin-information&amp;plugin='.urlencode($key).'&amp;TB_iframe=true&amp;width=640&amp;height=808'), $title );
			} else {

				$activate_message = sprintf( esc_html__( 'Activate %s', 'ctct'), $plugin['name'] );

				$message = sprintf( esc_html__('(%s - Installed, but inactive)', 'ctct'), '<a href="'.wp_nonce_url( admin_url( 'plugins.php?action=activate&amp;plugin='.urlencode($plugin['path']) ), 'activate-plugin_'.$plugin['path']).'">'.$activate_message.'</a>' );

			}

			$plugin_messages[ $key ] = ' <span class="description">'.$message.'</span>';
		}

		return isset( $plugin_messages[ $message_key ] ) ? $plugin_messages[ $message_key ] : null;

	}

	/**
	 * Get a setting value by key
	 * @param  string $key           The name of the setting
	 * @param  boolean $trim          Whether or not to trim the value of the setting
	 * @param  boolean $checkingisset Check if the setting is set (not what the value is)
	 * @return mixed                 Returns the value of the key, false of the key is not set, and NULL if $checkingisset and the key is empty.
	 */
	public static function get_setting($key = false, $trim = false, $checkingisset = false) {
		if(!empty($key)) {
			$options = self::get_settings();
			if(isset($options[$key])) {
				if($options[$key] === 'false') { return false; }
				if($options[$key] === 'true') { return true; }
				if(is_array($options[$key]) && sizeof($options[$key]) == 1) {
					$return = $options[$key][0];
				} else {
					$return = $options[$key];
				}

				if($trim) { $return = trim(rtrim($return)); }

				return $return;
			} elseif($checkingisset) {
				return NULL;
			}
			return false;
		}
		return self::get_settings();
	}

	/**
	 * Alias for get_setting()
	 * @uses CTCT_Settings::get_setting()
	 * @see CTCT_Settings::get_setting()
	 * @param  string $key           The name of the setting
	 * @param  boolean $trim          Whether or not to trim the value of the setting
	 * @param  boolean $checkingisset Check if the setting is set (not what the value is)
	 * @return mixed                 Returns the value of the key, false of the key is not set, and NULL if $checkingisset and the key is empty.
	 */
	public static function get($key = false, $trim = false, $checkingisset = false) {
		return self::get_setting($key, $trim);
	}

	/**
	 * Generate the settings page layout and settings fields
	 *
	 * @filter CTCT_Settings_array
	 * @action CTCT_Settings_sections
	 * @uses register_setting()
	 * @uses add_settings_section()
	 * @uses add_settings_field()
	 * @return array Array of settings to be shown in the admin.
	 */
	function settings_init() {
		global $plugin_page;

		register_setting( 'ctct_settings', 'ctct_settings');

		if( $plugin_page !== 'constant-contact-api' ) { return; }

		// We don't need this yet unless it's configured.
		if( !$this->cc->isConfigured() ) {
			return;
		}

		add_settings_section('setup', '<i class="dashicons dashicons-admin-settings"></i> '.__('Setup', 'ctct'), '', 'constant-contact-api');

		// Hook in here for more tabs.
		do_action('ctct_settings_sections');

		add_settings_section('registration', '<i class="dashicons dashicons-forms"></i> '.__('Registration Form', 'ctct'), '<h3>'.esc_html__('Configure how users sign up.', 'ctct').'</h3>', 'constant-contact-api');
		add_settings_section('comments', '<i class="dashicons dashicons-admin-comments
"></i> '.__('Comment Form', 'ctct'), '<h3>'.esc_html__('Configure how users sign up.', 'ctct').'</h3>', 'constant-contact-api');
		add_settings_section('spam', '<i class="dashicons dashicons-trash"></i> '.__('Spam Prevention', 'ctct'), '<h3>'.esc_html__('How do you want to prevent spam?', 'ctct').'</h3>', 'constant-contact-api');

		$groups = array(
			'setup' => array(
				array(
				    'type' => 'html',
				    'content' => kws_ob_include(CTCT_DIR_PATH.'views/admin/view.setup.php', $this)
				),
				array(
					'type' => 'checkboxes',
					'id' => 'logging',
					'options' => array(
						'activity' => __('Log Constant Contact Activity', 'ctct'),
						'error' => __('Log Errors & Exceptions', 'ctct'),
						'debug' => __('Detailed Debugging Logs (Do not leave enabled! Server-intensive.)', 'ctct'),
					),
					'desc' => __('Activity Logs', 'ctct'),
					'label' => __('Log different activity from the plugin, including form submissions and the results ("Constant Contact Activity").', 'ctct'),
				),
			),
			'registration' => array(
			    array(
			    	'type' => 'heading',
			    	'desc' => __('WordPress Registration Form', 'ctct'),
			    	'label' => sprintf(__('Add signup options to WordPress\' <a href="%s" rel="external">registration form.</a>', 'ctct'), site_url('wp-login.php?action=register')),
			    ),
				array(
					'type' => 'radio',
					'id' => 'register_page_method',
					'options' => array(
						'none' => __('Disabled', 'ctct'),
						'checkbox' => __('Single Checkbox - show users a checkbox which, if ticked, will automatically subscribe them to the lists you select below in the "Active Contact Lists" section.', 'ctct'),
						'checkboxes' => __('List of Checkboxes - show a bullet list with the name of the list and a checkbox option for them to sign up', 'ctct'),
						'dropdown' => __('Dropdown List', 'ctct'),
					),
					'toggle' => 'registration',
					'desc' => __('User Subscription Method', 'ctct'),
				),
				array(
					'id' => 'default_opt_in',
					'togglegroup' => 'registration_checkbox registration_checkboxes',
					'type' => 'checkbox',
					'desc' => __('Opt-in users by default?', 'ctct'),
					'label' => __('Should the opt-in checkbox(es) be checked by default? If using the "List Selection" method, should lists be pre-selected by default.', 'ctct'),
				),
				array(
					'type' => 'lists',
					'id' => 'registration_checkbox_lists',
					'togglegroup' => 'registration_checkbox',
					'desc' => __('Lists for Registration', 'ctct'),
					'label' => __('<strong>Checkbox:</strong> What lists will users be added to when checking the opt-in box?<br />Others: What lists will users be presented with?', 'ctct'),
					'options' => KWSContactList::outputHTML('all', array(
							'type' => 'checkboxes',
							'format' => '%%name%%',
							'name_attr' => 'ctct_settings[registration_checkbox_lists]',
							'id_attr' => 'constant-contact-api_registration_checkbox_lists_%%id%%',
							'checked' => self::get('registration_checkbox_lists'),
							'class' => 'toggle_registration_checkbox toggle_registration_checkboxes toggle_registration_dropdown',
						)),
					'help' => __('When users sign up for your newsletter while registering for a WordPress account, they will be added to the following lists.', 'ctct'),
				),
				array(
					'id' => 'signup_description',
					'togglegroup' => 'registration_checkbox registration_checkboxes registration_dropdown',
					'type' => 'textarea',
					'desc' => __('Signup Description', 'ctct'),
					'label' => __('Signup form description text displayed on the registration screen and user profile setting, if enabled. HTML is allowed. Paragraphs will be added automatically like in posts.', 'ctct'),
				),
				array(
					'type' => 'radio',
					'togglegroup' => 'registration_checkbox registration_checkboxes registration_dropdown',
					'id' => 'signup_description_position',
					'options' => array(
						'before' => __('Before the Opt-in', 'ctct'),
						'after' => __('After the Opt-in', 'ctct'),
					),
					'desc' => __('Signup Description Position', 'ctct')
				),
				array(
					'id' => 'signup_title',
					'togglegroup' => 'registration_checkbox registration_checkboxes registration_dropdown',
					'type' => 'text',
					'desc' => __('Signup Title', 'ctct'),
					'label' => __('Title for the signup form displayed on the registration screen and user profile settings if enabled.', 'ctct'),
				),
				array(
				    'type' => 'text',
				    'togglegroup' => 'registration_dropdown',
					'id' => 'default_select_option_text',
					'desc' => __('Default Option Text', 'ctct'),
					'label' => __('If "Opt-in users by default" (below) is not checked, this will be the default option in the dropdown menu. Leave blank to not show this option.', 'ctct')
				),
				array(
					'type' => 'heading',
					'desc' => __('Profile Page', 'ctct')
				),
					array(
						'type' => 'checkbox',
						'id' => 'profile_page_form',
						'label' => __('Allow users to modify their subscription on their WordPress profile page', 'ctct'),
						'desc' => __('Show Form on Profile Page?', 'ctct'),
						'help' => __('Do you want users to be able to update their subscriptions inside WordPress?', 'ctct'),
					),
			),
			'comments' => array(
				array(
					'type' => 'heading',
					'desc' => __('Comment Form', 'ctct')
				),
					array(
						'type' => 'checkbox',
						'id' => 'comment_form_signup',
						'label' => __('Add a checkbox for subscribing to a newsletter below a comment form', 'ctct'),
						'toggle' => 'comment_form',
						'desc' => __('Comment Form Signup', 'ctct'),
					),
					array(
					    'togglegroup' => 'comment_form',
						'type' => 'checkbox',
						'id' => 'comment_form_default',
						'desc' => __('Checked by default?', 'ctct'),
						'label' => __('Should the checkbox be checked by default?', 'ctct'),
					),
					array(
						'type' => 'lists',
						'id' => 'comment_form_lists',
						'togglegroup' => 'comment_form',
						'desc' => __('Lists for Comment Form', 'ctct'),
						'label' => __('What lists will users be added to when signing up with the Comment Form?', 'ctct'),
						'options' => KWSContactList::outputHTML('all', array(
								'type' => 'checkboxes',
								'format' => '%%name%%',
								'name_attr' => 'ctct_settings[comment_form_lists]',
								'id_attr' => 'constant-contact-api_comment_form_lists_%%id%%',
								'class' => 'toggle_comment_form',
								'checked' => self::get('comment_form_lists')
							)),
					),
					array(
					    'togglegroup' => 'comment_form',
						'type' => 'text',
						'id' => 'comment_form_check_text',
						'desc' => __('Subscribe Message', 'ctct')
					),
					array(
					    'togglegroup' => 'comment_form',
						'type' => 'text',
						'id' => 'comment_form_subscribed_text',
						'desc' => __('Already Subscribed Message', 'ctct')
					),
					array(
					    'togglegroup' => 'comment_form',
						'type' => 'checkbox',
						'id' => 'comment_form_clear',
						'label' => __('Uncheck if this causes layout issues', 'ctct'),
						'desc' => __('Add a CSS \'clear\' to the checkbox?', 'ctct'),
					),
			),
			'spam' => array(
				array(
					'type' => 'checkboxes',
					'id' => 'spam_methods',
					'toggle' => 'spam_methods',
					'options' => array(
						'datavalidation' => __('Verify Email Addresses with <a href="http://katz.si/datavalidation" rel="external">DataValidation.com</a>', 'ctct').constant_contact_tip(__('DataValidation.com is the best way to verify that when users submit a form, the submitted email address is valid.', 'ctct'), false),
						'akismet' => __('Akismet', 'ctct').self::get_plugin_status_message( 'akismet' ),
						'wangguard' => __('WangGuard WordPress Plugin', 'ctct').self::get_plugin_status_message( 'wangguard' ),
						'smtp' => __('Validate Email Addresses Via SMTP (<a href="http://katz.si/smtpvalidation" rel="external">See the project</a>)', 'ctct').constant_contact_tip(__('Uses server methods to verify emails: checks for a valid domain, then sends a request for a read receipt.', 'ctct'), false),
					),
					'desc' => __('What services do you want to use to prevent spam submissions of your forms?', 'ctct')
				),
				array(
					'type' => 'heading',
					'togglegroup' => 'spam_methods_datavalidation',
					'desc' => __('DataValidation.com Settings', 'ctct'),
				),
				array(
					'type' => 'text',
					'togglegroup' => 'spam_methods_datavalidation',
					'id' => 'datavalidation_api_key',
					'desc' => __('DataValidation.com: API Key', 'ctct'),
					'label' => sprintf( __('Enter your DataValidation.com API key. %sSign up for a key here%s.', 'ctct'), '<a href="https://developer.datavalidation.com" rel="external">', '</a>' )
				),
				array(
					'type' => 'checkbox',
					'togglegroup' => 'spam_methods_datavalidation',
					'id' => 'datavalidation_prevent_ambiguous',
					'desc' => __('DataValidation.com: Should "ambiguous" responses be blocked?', 'ctct'),
					'label' => __('Ambiguous Responses basically mean that the email looks good, it has valid DNS, it has a valid MX record, it even has an email server, but for a myriad of reasons it does not accept any connections to it. Could be connection refused, could be the server is down, etc.', 'ctct')
				),
			),
			'forms' => array(
				array(
					'type' => 'checkboxes',
					'id' => 'forms',
					'options' => array(
						'formstack' => __('<a href="http://www.formstack.com/r/31575458">Formstack</a>', 'ctct'),
						'after' => __('After the Opt-in', 'ctct'),
					),
					'desc' => __('Forms', 'ctct')
				),
			)
		);

		$groups = apply_filters('ctct_settings_array', $groups);
		$i = 0;

		if(empty($groups)) {
			return;
		}

		foreach($groups as $group => $settings) {
			foreach($settings as $setting) {
				$i++;
				$setting['page'] = isset($setting['page']) ? $setting['page'] : 'constant-contact-api';
				$setting['callback'] = isset($setting['callback']) ? $setting['callback'] :  array('CTCT_Settings', 'setting_input_generator');
				$setting['desc'] = isset($setting['desc']) ? $setting['desc'] : '';
				if(isset($setting['type']) && $setting['type'] === 'heading') { $setting['id'] = sanitize_title($setting['desc']); }
				$setting['id'] = isset($setting['id']) ? $setting['id'] : '';
				extract($setting);
				unset($setting['callback']);
				add_settings_field($id, $desc, $callback, $page, $group, $setting);
			}
		}

	}

	/**
	 * Set the default settings. Each setting has a key value in an array and the default is the value.
	 * Settings can be accessed here without necessarily being visible in get_settings(). get_settings() is only for UI
	 * @filter ctct_default_settings
	 * @filter ctct_settings_filter
	 * @return array $settings array
	 */
	static private function set_settings() {
		$option = get_option('ctct_settings');

		$defaults = apply_filters('ctct_default_settings', array(
			'general' => '',
			'logging' => array('activity'),
			'register_page_method' => 'none',
			'list_selection_format' => 'checkbox',
			'default_opt_in' => 1,
			'signup_title' => __('Receive our Newsletter', 'ctct'),
			'signup_description' => __('Subscribe to the Newsletter', 'ctct'),
			'default_select_option_text' => __('Select a List&hellip;', 'ctct'),
			'signup_description_position' => 'before',
			'comment_form_signup' => true,
			'comment_form_check_text' => __('Subscribe me to your mailing list', 'ctct'),
			'comment_form_subscribed_text' => __('You are currently subscribed to our mailing list', 'ctct'),
			'comment_form_admin_text' => __('You are the administrator - no need to subscribe you to the mailing list', 'ctct'),
			'comment_form_clear' => true,
			'spam_methods' => array( 'akismet' ),
		));

		/**
		 * When there is an array of checkboxes and none are checked,
		 * the field isn't set and so it reverts to defaults. That's not ideal.
		 * The sizeof() check is to get the defaults even when the license key is set.
		 */
		foreach($defaults as $k => $v) {
			if(!empty($option) && is_array($option) && (sizeof($option) > 10) && is_array($v) && !isset($option[$k])) { $option[$k] = array(); }
		}

		$options = apply_filters('ctct_settings_filter', wp_parse_args($option, $defaults));

		self::$options = $options;

		return $options;
	}

	/**
	 * Get the settings array
	 * If they settings haven't yet been saved, use set_settings to generate defaults.
	 * @filter ctct_settings_filter
	 * @uses CTCT_Settings::set_settings()
	 * @return array Settings array
	 */
	public static function get_settings() {
		if(empty(self::$options)) {
			self::set_settings();
		}
		return apply_filters('ctct_settings_filter', self::$options);
	}

	/**
	 * Create the HTML output for the settings.
	 * @modified 2.0.37 Added ability for `options` to be an array with `desc` and `label`
	 * @filter ctct_admin_wp_editor_settings
	 * @param  array $settings Array of settings
	 * @todo Add a "reset to default" link for most field types
	 */
	static function setting_input_generator($settings) {
		global $pagenow;
		$output = '';

		extract($settings);

		if(!isset($settings['id']) || empty($id)) {
			$id = 1;
		}

		if(!empty($atts)) {
			$atts = wp_parse_args($atts, array(
				'sortable' => false,
				'sortid' => $id
			));
		}

		$desc = isset($desc) ? $desc : '';
		$content = isset($content) ? $content : '';
		$label = isset($label) ? $label : '';
		$name = isset($name) ? $name : 'ctct_settings['.$id.']';
		$class = isset($class) ? $class : '';
		if(isset($togglegroup)) {
			$togglegroup = ' toggle_'.implode(' toggle_', is_array($togglegroup) ? $togglegroup : explode(' ', $togglegroup));
		} else { $togglegroup = ''; }
		$value = isset($value) ? $value : self::get($id);
		$page = isset($page) ? $page : '';
		$type = isset($type) ? $type : '';

		if($type == 'heading' && !empty($desc)) {
			$desc = '<h4 class="field_heading'.$togglegroup.'">'.$desc.'</h4>';
			$desc .= isset($label) ? '<p class="description">'.$label.'</p>' : '';
			echo $desc; return;
		}

		if($type == 'html' && !empty($content)) {
			echo $content; return;
		}

		if($pagenow == 'widgets.php' || $pagenow == 'admin-ajax.php') {
			$output .= '<label for="'.$page.'_'.$id.'" class="ctct_setting_label '.$type.'_label" id="label_for_'.$id.'">'.$desc.'</label>';
		}

		$output .= '
	 	<label for="'.$page.'_'.$id.'" class="description'.$togglegroup.' '.$type.'">';
	 		switch($type) {
	 			case "text":
	 			case "number":
	 			case "password":
	 				$img = '';
	 				$value = str_replace('"', '&quot;', $value);
	 				$size = ($type === 'number') ? 5 : 90;
	 				$output .= '<input name="'.$name.'" autocomplete="off" id="'.$page.'_'.$id.'" type="'.$type.'" value="'.$value.'" class="'.$class.'" size="'.$size.'" />'.$img.' <span style="display:block;">'.$label.'</span></label>';
	 				break;
	 			case "textarea":
	 				$cols = isset($cols) ? $cols : '90';
	 				$rows = isset($rows) ? $rows : '7';
	 				$class = isset($class) ? $class : 'textarea';

	 				$output .= '<span style="display:block;">'.$label.'</span></label>';
	 				if(!isset($rich) || !empty($rich)) {
		 				wp_editor(
		 					$value,  // Content
		 					$page.'_'.$id, // #id
		 					apply_filters('ctct_admin_wp_editor_settings', array(
								'textarea_name' => $name,
								'textarea_rows' => $rows,
								'media_buttons' => false,
								'class' => $class,
								'teeny' => isset($teeny) ? $teeny : false,
								'quicktags' => true,
							))
						);
					} else {
	 				 	$output .= '<textarea name="'.$name.'" rows="'.$rows.'" class="'.$class.'" id="'.$page.'_'.$id.'" cols="'.$cols.'">'.$value.'</textarea></label>';
	 				}
	 				break;
	 			case "multiple":
	 			case "select":
	 				$multiple = '';
	 				if($type === 'multiple') {
	 					$multiple = ' multiple="multiple" size="5" style="height:10em; width:100%; display:block; font-size:1.1em;"';
	 					$name .= '[]';
	 				} else {
	 					$multiple = ' style="display:block;"';
	 				}
	 				$output .= "\n".$label.'</label>
	 				<select class="'.$class.'" name="'.$name.'" id="'.$page.'_'.$id.'"'.$multiple.'>';
	 				foreach($options as $key => $optiontitle) {
	 					$selected = ($type === 'multiple') ? selected(in_array((string)$key, $value), true, false ) : selected((string)$value, (string)$key, false );
	 					$output .= "\n\t".'<option value="'.(string)$key.'"' . $selected . '>'.(string)$optiontitle.'</option>';
	 				}
	 				$output .= "\n".'</select>';
	 				break;
	 			case "checkboxes":
	 			case "lists":
	 			case "radio":
	 				if(is_string($options)) { $output = $options; break; }
	 				if(!is_array($value)) { $value = array($value); }
	 				$output .= $label.'</label>';
	 				if(@$atts['sortable']) {
	 					$output .= '<input name="ctct_settings['.$id.'_sortorder]" type="hidden" value="'.self::get($id.'_sortorder').'" />';
	 					$output .= '<ul>';
	 				} else {
	 					$output .= '<ul>';
	 				}
	 				if(in_array($type, array('lists', 'checkboxes'))) { $type = 'checkbox'; $name .= '[]'; } else { $output .= '<input name="'.$name.'" type="hidden" value="0" />'; }

	 				// If there is a custom sort order, let's get it and re-arrange the options list.
	 				if((!isset($_GET['reset-order']) || (isset($_GET['reset-order']) && $_GET['reset-order'] !== $atts['sortid']))) {
	 					$sort = self::get($id.'_sortorder');
	 					if(!empty($sort)) {
		 					@parse_str($sort, $sortorder);
		 					if(is_array($sortorder) && !empty($sortorder) && isset($sortorder[$atts['sortid']])) {
		 						$sortorder = $sortorder[$atts['sortid']];

		 						foreach($sortorder as $key) {
		 							$newoptions[$key] = $options[$key];
		 							unset($options[$key]);
		 						}

		 						// Missed any new options?
		 						foreach($options as $k => $v) {
		 							$newoptions[$k] = $v;
		 						}

								$options = $newoptions;
		 					}
		 				}
	 				}

	 				foreach($options as $key => $option) {
	 					$optiontitle = $optiondesc = '';
	 					if(is_array($option) && isset($option['label'])) {
	 						$optiontitle = $option['label'];
	 						$optiondesc = isset($option['desc']) ? sprintf('<span class="howto" style="margin-left:1.25em; font-style:normal;">%s</span>', $option['desc']) : '';
	 					} else if(is_string($option)) {
	 						$optiontitle = $option;
	 					} else if(is_a($option, 'Ctct\Components\Contacts\ContactList') ) {
	 						$optiontitle = $option->name;
	 						$key = $option->id;
	 					}

	 					// When switching from Pro to standard, the options will stay set but be empty.
	 					// This fixes that issue.
	 					if(empty($optiontitle)) { continue; }

	 					$reltoggle = isset($toggle) ? 'rel="'.$toggle.'_'.$key.'"' : '';
	 					$output .= "\n\t".'<li id="'.@$atts['sortid'].'='.$key.'"><label for="'.$page.'_'.$id.'_'.$key.'"><input name="'.$name.'" id="'.$page.'_'.$id.'_'.$key.'" '.$reltoggle.' class="constant-contact-api-toggle" type="'.$type.'" value="'.(string)$key.'"' . checked(in_array((string)$key, $value) || ($key === 'disclaimer' || $key === 'listing_source'), true, false ) . ' /> '.$optiontitle.'</label>'.$optiondesc.'</li>';
	 				}
	 				$output .= '</ul>';
	 				break;

	 			case "checkbox":
	 			default:
	 				$toggle = isset($toggle) ? 'rel="'.$toggle.'"' : '';
	 				$output .= '
	 				<input name="'.$name.'" type="hidden" value="false" />
	 				<input name="'.$name.'" id="'.$page.'_'.$id.'" type="checkbox" value="true" class="constant-contact-api-toggle '.$class.'" '.$toggle.' ' . checked( 1, !empty($value) && $value !== 'false', false ) . ' style="margin-right:.25em;" />'.$label.'</label>';
	 				break;
	 		}
	 	echo $output ."\n";
	 }

	 static function do_settings_sections($page) {
	 	global $wp_settings_sections, $wp_settings_fields;

	 	if ( !isset($wp_settings_sections) || !isset($wp_settings_sections[$page]) )
	 		return;

	 	?>
	 	<div id="ctct-settings-tabs">
	 		<h2 class="nav-tab-wrapper"><ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
	 	<?php
	 	foreach ( (array) $wp_settings_sections[$page] as $section ) {
	 		echo '<li class="ui-state-default ui-corner-top"><a class="nav-tab" href="#'.sanitize_title($section['title']).'" id="'.sanitize_title($section['title']).'-link">'.$section['title'].'</a></li>';
	 	}
	 	?>
	 	</ul></h2>
	 	<?php
	 	foreach ( (array) $wp_settings_sections[$page] as $section ) {
	 		echo '<div id="'.sanitize_title($section['title']).'">';
	 		#echo "<h2>{$section['title']}</h2>\n";

	 		echo '<div class="description">';
	 		if((is_string($section['callback']) && strpos($section['callback'], ' ')) && !is_array($section['callback'])) {
	 			echo wpautop($section['callback']);
	 		}elseif(!empty($section['callback'])) {
	 			call_user_func($section['callback'], $section);
	 		}
	 		echo '</div>';
	 		if ( !isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']]) )
	 			continue;
	 		echo '<table class="form-table">';
	 		self::do_settings_fields($page, $section['id']);
	 		echo '</table>
	 		</div>
	 		';
	 	}
	 	?>
	 	</div>
	 	<p class="submit"><input name="Submit" id="ctct-save-settings" class="button button-primary button-hero" type="submit" value="<?php esc_html_e('Save Settings', 'ctct'); ?>" /></p>
	 	<?php
	 }

	 static function do_settings_fields($page, $section) {
	 	 global $wp_settings_fields;

	 	 if ( !isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section]) )
	 			return;
	 	$i = 0;
	 	 foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
	 	 	$class = ''; //$alt ? 'alt ' : '';
	 	 		if($i === 0) { $class = 'border_none'; }
	 	 		if(
	 	 		   	(
	 	 		   	 	!isset($field['args']['type']) ||
	 	 		    	(isset($field['args']['type']) &&
	 	 		    	 	// If it's a heading or HTML, we span both TDs
	 	 		    		($field['args']['type'] !== 'heading' && $field['args']['type'] !== 'html')
	 	 		    	)
	 	 		    ) &&
	 	 			(
	 	 			 	!isset($field['args']['type']) ||
	 	 				(isset($field['args']['type']) && $field['args']['type'] !== 'separator')
	 	 			)
	 	 		) {
	 	 			echo '<tr valign="top" class="constant-contact-api_tr_'.$field['args']['id'].' '.$class.'constant-contact-api_tr constant-contact-api_tr_type_'.@$field['args']['type'].'">';
	 	 			$alt = empty($alt) ? true : false;
	 	 			if(@$field['args']['type'] !== 'checkboxes' && @$field['args']['type'] !== 'radio') {
	 	 				// Make labels clickable
	 	 				$field['args']['label_for'] = 'constant-contact-api_'.$field['args']['id'];
	 	 			}

					$help = isset($field['args']['help']) ? '<span class="ctct_help cc_tip" title="'.str_replace('"', '&quot;', $field['args']['help']).'">?</span>' : '';

	 				if ( !empty($field['args']['label_for']) ) {
	 					echo '<th scope="row"><label for="' . $field['args']['label_for'] . '">' . $field['title'] . '</label>'.$help.'</th>';
	 				} else {
	 					echo '<th scope="row">' . $field['title'] . $help .'</th>';
	 				}
	 				echo '<td>';
	 				call_user_func($field['callback'], $field['args']);
	 				echo '</td>';
	 				$i++;
	 			} else {
	 				echo '<tr valign="top" class="constant-contact-api_tr_'.$field['args']['id'].' constant-contact-api_tr constant-contact-api_tr_type_'.$field['args']['type'].'">';
	 				$alt = false;
	 	 			echo '<td colspan="2" class="nopadding">';
	 	 			call_user_func($field['callback'], $field['args']);
	 				echo '</td>';
	 				$i = 0;
	 	 		}
	 			echo '</tr>';
	 	 }
	 }
}

new CTCT_Settings;
