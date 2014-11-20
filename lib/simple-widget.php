<?php
return;
add_action( 'widgets_init', array('CTCT_Widget_Simple', 'register') );

/**
 * Subscribe to Newsletter Widget
 *
 * @package		Subscibe_to_Newsletter
 * @category	Widgets
 * @author		Katz Web Services, Inc.
 */
class CTCT_Widget_Simple extends WP_Widget {

	function register() {
		register_widget( 'CTCT_Widget_Simple' );
	}

	/** Variables to setup the widget. */
	var $widget_cssclass;
	var $widget_description;
	var $widget_idbase;
	var $widget_name;

	/** constructor */
	function __construct() {
		$this->cc = new KWSConstantContact();
		/* Widget variable settings. */
		$this->widget_cssclass = 'ctct-widget-simple';
		$this->widget_description = __( 'Allow users to subscribe to your Constant Contact .', 'ctct');
		$this->widget_idbase = 'ctct_widget_simple';
		$this->widget_name = __('Subscribe to Newsletter', 'ctct');

		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->widget_cssclass, 'description' => $this->widget_description );

		/* Create the widget. */
		$this->WP_Widget('subscribe_to_newsletter', $this->widget_name, $widget_ops);
	}

	/** @see WP_Widget */
	function widget( $args, $instance ) {
		extract($args);

		$title   = $instance['title'];
		$lists  = $instance['lists'];
		$title   = apply_filters('widget_title', $title, $instance, $this->id_base);

		echo $before_widget;

		if ($title) echo $before_title . $title . $after_title;

		?>
		<form method="post" id="<?php echo $this->get_field_id(''); ?>" action="#<?php echo $this->get_field_id(''); ?>">
			<?php
				$email = '';

				if (isset($_POST['ctct']) && isset($_POST['ctct']['email'])) {

					$email = sanitize_text_field( $_POST['ctct']['email'] );

					if (!is_email($email)) {
						echo '<div class="woocommerce_error">'.__('Please enter a valid email address.', 'ctct').'</div>';
					} else {

						$Contact = $this->cc->addUpdateContact(array('email' => $email, 'lists' => $lists));

						echo '<div class="woocommerce_message">'.__('Thanks for subscribing.', 'ctct').'</div>';
					}
				}
			?>
			<div>
				<label class="screen-reader-text hidden" for="s"><?php _e('Email Address:', 'ctct'); ?></label>
				<input type="text" name="ctct[email]" id="ctct-newsletter-email" placeholder="<?php _e('Your email address', 'ctct'); ?>" value="" />
				<input type="submit" id="newsletter_subscribe" value="<?php _e('Subscribe', 'ctct'); ?>" />
			</div>
		</form>
		<?php

		echo $after_widget;
	}

	/** @see WP_Widget->update */
	function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['lists']  = $new_instance['lists'];
		return $instance;
	}

	/** @see WP_Widget->form */
	function form( $instance ) {
		extract($instance);
 ?>
			<h2><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title:', 'ctct') ?></label></h2>
			<p>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php if ( !empty($title)) { echo esc_attr( $title ); } else { echo __('Newsletter', 'ctct');} ?>" /></p>

			<h2><label for="<?php echo $this->get_field_id('list'); ?>"><?php _e('Newsletter List:', 'ctct') ?></label></h2>
			<span class="description" style="font-style:normal;"><?php _e('Users will be subscribed to the following lists when submitted.', 'ctct'); ?></span>
			<p>
<?php

						$lists = array( '' => __('Select a list...', 'ctct') );
						$lists = $this->cc->getAllLists();
						#IDX_Plus::r($lists);
						if (!$lists) {
							echo '<div class="error"><p>'.__('Unable to load lists() from Constant Contact.', 'ctct').'</p></div>';
						} else {
							$listHTML = KWSContactList::outputHTML($lists, array(
								'type' => 'checkboxes',
								'format' => '%%name%%',
								'name_attr' => $this->get_field_name('lists'),
								'id_attr' => $this->get_field_id('ctct-%%id%%'),
								'checked' => $instance['lists'],
							));
							echo $listHTML;
						}
	}
}