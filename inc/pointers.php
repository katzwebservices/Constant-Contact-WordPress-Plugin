<?php

function ctct_get_pointers() {
	$pointers = array(
		'help' => array(
	        'target' => 'body.toplevel_page_constant-contact-api #contextual-help-link',
	        'options' => array(
	            'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
	                __( 'Check out the "Help" Tab' ,'constant-contact-api'),
	                __( 'The Constant Contact plugin includes a lot of tips hidden in this help tab. If you need help, check here first. <a href="#" rel="wp-help">Open it up and see</a>','constant-contact-api')
	            ),
	            'position' => 'right'
	        ),
	    ),
	    'registration' => array(
			'target' => 'body.toplevel_page_constant-contact-api #setup > div.description',
			'options' => array(
			    'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
			        __( 'Configure Settings' ,'constant-contact-api'),
			        __( 'Navigate the tabs to configure the plugin settings.','constant-contact-api')
			    ),
			    'position' => 'top'
			),
		),
		'formdesigner' => array(
			'target' => '#cc-form-settings .switch',
			'options' => array(
			    'content' => sprintf( '<h3>%s</h3><p>%s</p><p>%s</p>',
			        __( 'Use the Form Styler' ,'constant-contact-api'),
			        __( 'The Form Styler allows you to create fun, colorful forms using the settings below.','constant-contact-api'),
			        __( 'If you want to create a simple-looking form, turn off Form Styler and your theme will take over the style of your form.','constant-contact-api')
			    ),
			    'position' => 'top'
			),
		),
		'linktoformdesigner' => array(
			'target' => 'body.toplevel_page_constant-contact-api #menu-constant-contact-forms',
			'options' => array(
				'content' => sprintf('<h3>%s</h3><p>%s</p>',
	                     __('Form Designer', 'constant-contact-api'),
	                     __('Use the Form Designer to configure forms that you will add in your posts, pages, and sidebar widgets.', 'constant-contact-api')
	            ),
	            'position' => 'left',
			),
		),
		'eventshortcode' => array(
			'target' => 'body.constant-contact_page_constant-contact-events #event-1 .column-shortcode',
			'options' => array(
				'content' => sprintf('<h3>%s</h3><p>%s</p>',
			           __('Event Shortcode', 'constant-contact-api'),
			           __('Add this "shortcode" to your post or page content to embed event details.', 'constant-contact-api')
			  ),
			  'position' => 'top',
			),
		)
	);
	
	return $pointers;
}