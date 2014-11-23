<?php // $Id$
/**
 * CTCT_Form_Designer_Widget Class
 *
 * @package CTCT\Form Designer
 */

add_action( 'widgets_init', array('CTCT_Form_Designer_Widget', 'register') );

class CTCT_Form_Designer_Widget extends WP_Widget {

	static function register() {
		register_widget( 'CTCT_Form_Designer_Widget' );
	}

    function __construct() {
		add_action( 'wp_enqueue_scripts', array(&$this, 'wp_enqueue_scripts') );

		$this->cc = new KWSConstantContact();

		/* Widget settings. */
        $widget_options = array(
			'description' => 'Displays a Constant Contact signup form to your visitors',
			'classname' => 'constant-contact-form',
		);

        /* Create the widget. */
        $this->WP_Widget('CTCT_Form_Designer_Widget', __('Constant Contact Form Designer Widget', 'ctct'), $widget_options, array('width'=>690));

        add_action('wp_print_styles', array(&$this, 'print_styles'));
    }

    function wp_enqueue_scripts() {
    	wp_enqueue_script( 'cc-widget', plugin_dir_url(__FILE__).'js/cc-widget.js' );
    	wp_enqueue_script( 'placeholder', plugins_url('/js/jquery.placeholder.min.js', __FILE__), array('jquery'), null, true );
    }

	function update( $new_instance, $old_instance ) {
		delete_transient("cc_form_{$old_instance['formid']}");
		delete_transient("cc_form_{$new_instance['formid']}");
		return $new_instance;
	}

	function print_styles() {
			if(is_admin()) { return; }
			$settings = $this->get_settings();
			$usedStyles = array();
			foreach($settings as $instance) {
				extract($instance);
				if(!isset($instance['formid']) || in_array($formid, $usedStyles) || $formid === 0) { continue; }
				$usedStyles[] = $formid; // We don't need to echo the same styles twice
			}
	}

   /**
    * @see WP_Widget::widget
    */
    function widget($args = array(), $instance = array(), $echo = true) {

    	// Passing that this is from the widget
    	$instance['widget'] = true;

		$form = constant_contact_public_signup_form($instance, false);

		if(!$form) {
			if((is_user_logged_in() && current_user_can('install_plugins'))) {
				_e(sprintf('<div style="background-color: #FFEBE8; padding:10px 10px 0 10px; font-size:110%%; border:3px solid #c00; margin:10px 0;">
				   <h3><strong>Admin-only Notice</strong></h3>
				   <p>The Form Designer is not working.</p>
				   <p>Your form may not exist. The widget is trying to find a form with the ID "<code>%s</code>" (that should be a number!). Please visit your <a href="%s">Widgets page</a> and re-save the widget.</p>
				   <p>Alternatively, this may be because of server configuration issues. Contact your web host and request that they "whitelist your domain for ModSecurity".</p>
				  </div>', $instance['formid'], admin_url('widgets.php')));
			} else {
				_e('<!-- Form triggered error. Log in and refresh to see additional information. -->');
			}
			return false;
		}

		$output = '';

		/**
		 * Extract $args array into individual variables
		 */
    		extract( $args );

    	/**
		 * Extract $instance array into individual variables
		 */
    		extract( $instance );

		/**
		 * Prepare the widget title and description
		 */
		$widget_title = empty($title) ? '' : apply_filters('widget_title', $title);

		/**
		 * Begin HTML output of widget
		 */
		$output .= (isset($before_widget)) ? $before_widget : '';
		$output .= (isset($before_title, $after_title)) ? $before_title : '<h2>';
		$output .= (isset($widget_title)) ? $widget_title : '';
		$output .= (isset($after_title, $before_title)) ? $after_title : '</h2>';

		$output .= apply_filters('cc_widget_description', $description);

		/**
		 * Display the public signup form
		 * Pass in widget $args, they should match the ones expected by constant_contact_public_signup_form()
		 */

		$output .= $form;


		$output .= (isset($after_widget)) ? $after_widget : '';


		echo $output;
    }


	function r($content, $kill = false) {
		echo '<pre>'.print_r($content,true).'</pre>';
		if($kill) { die(); }
	}

	function get_value($field, $instance) {
		if (isset ( $instance[$field])) { return esc_attr( $instance[$field] );}
		return false;
	}

	/**
	 * Generate a drop-down of available form designer forms
	 * @param  array $instance The current widget instance
	 * @return string           HTML <select> output
	 */
	function get_form_list_select($instance) {
		$forms = get_option('cc_form_design');

		$output = '';
		$output .= '<select name="'.$this->get_field_name('formid').'" id="'.$this->get_field_id('formid').'">';
		$output .= '<option value="">'.__('Select a Form Design', 'ctct').'</option>';

		if(!empty($forms)) {
			$previous_names = array();
			foreach($forms as $form) {

				$name = isset($form['form-name']) ? $form['form-name'] : 'Form '+$key;

				$form['truncated_name'] = stripcslashes(trim( wp_html_excerpt( $name, 50 ) ));
				if ( isset($form['form-name']) && $form['truncated_name'] != $form['form-name'])
					$form['truncated_name'] .= '&hellip;';

				if(!in_array(sanitize_key( $name ), $previous_names)) {
					$previous_names[] = sanitize_key( $name );
				} else {
					$namekey = sanitize_key( $name );
					$previous_names[$namekey] = isset($previous_names[$namekey]) ? ($previous_names[$namekey] + 1) : 1;
					$form['truncated_name'] .= ' ('.$previous_names[$namekey].')';
				}

				if(!empty($form)) {
					$output .= "<option value=\"{$form['cc-form-id']}\"".selected($this->get_value('formid', $instance), $form['cc-form-id'], false).">{$form['truncated_name']}</option>";
				}
			}
		}
		$output .= '</select>';

		return $output;
	}

    /** @see WP_Widget::form */
    function form($instance) {
		$instance = wp_parse_args( (array) $instance, array(
			'show_firstname' => 1,
			'show_lastname' => 1,
			'description' => false,
			'title' => __('Sign Up for Our Newsletter', 'ctct'),
			'list_selection_title' => 'Sign me up for:',
			'list_selection_format' => 'checkbox',
			'formid' => 0,
		));

		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$description = isset( $instance['description'] ) ? $instance['description'] : '';
	?>
	<?php
	/**
	 * If there are no forms configured yet, we can't really do anything here.
	 */
	if(!get_option('cc_form_design')) {
	?>
	<h2><?php _e("You're in the right spot, but&hellip;", 'ctct'); ?></h2>
	<h3><?php echo sprintf(__('You must create a form on the <a href="%s">Form Design page</a> first.', 'ctct'), admin_url('admin.php?page=constant-contact-forms')); ?></h3>
	<p class="description"><?php echo sprintf(__('This widget displays forms created on the <a href="%s">Form Design page</a>. Go there, create a form, then come back here.', 'ctct'), admin_url('admin.php?page=constant-contact-forms')); ?></p>
	<?php
	return;
	}
	?>
	<h3><?php _e('Signup Widget Settings', 'ctct'); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><p><label for="<?php echo $this->get_field_id('title');?>"><span><?php _e('Signup Widget Title', 'ctct'); ?></span></label></p></th>
			<td>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" value="<?php echo $title; ?>" size="50" />
			<p class="description"><?php _e('The title text for the this widget.', 'ctct'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><p><label for="<?php echo $this->get_field_id('description');?>"><span><?php _e('Signup Widget Description', 'ctct'); ?></span></label></p></th>
			<td>
			<textarea class="widefat" name="<?php echo $this->get_field_name('description');?>" id="<?php echo $this->get_field_id('description');?>" cols="50" rows="4"><?php echo $description; ?></textarea>
			<p class="description"><?php _e('The description text displayed in the sidebar widget before the form. HTML allowed. Paragraphs will be added automatically like in posts.', 'ctct'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><p><label for="<?php echo $this->get_field_id('formid');?>"><span><?php _e('Form Fields &amp; Design', 'ctct'); ?></span></label></p></th>
			<td>
			<?php echo $this->get_form_list_select($instance); ?>
			<p class="description"><?php echo sprintf(__('Create your form on the <a href="%s">Form Design page</a>, then select it here.', 'ctct'), admin_url('admin.php?page=constant-contact-forms')); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><p><label for="<?php echo $this->get_field_id('redirect_url');?>"><span><?php _e('Signup Widget Thanks Page', 'ctct'); ?></span></label></p></th>
			<td>
			<input type="text" class="widefat code" name="<?php echo $this->get_field_name('redirect_url');?>"  id="<?php echo $this->get_field_id('redirect_url');?>" value="<?php echo $this->get_value('redirect_url', $instance); ?>" size="50" />
			<p class="description"><?php _e('Enter a url above to redirect new registrants to a thank you page upon successfully submitting the signup form. Use the full URL/address including <strong>http://</strong> Leave this blank for no redirection (page will reload with success message inside widget).', 'ctct'); ?></p>
			</td>
		</tr>
	</table>
	<?php
    }

} // class CTCT_Form_Designer_Widget

