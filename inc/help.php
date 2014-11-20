<?php
/**
 * Add help menu to the Constant Contact plugin
 * @package WP_CTCT
 */

class CTCT_Help_Menu {

	function __construct() {
		add_action('admin_head', array( $this, 'add_help') );
	}

	function add_help() {
		global $plugin_page, $pagenow;

		$screen = get_current_screen();

		$tabs = array();

		$tabs[] = array(
			'id'	=> 'ctct-insert-form',
			'title'	=> __('Constant Contact: Add a Form to Post or Page', 'ctct'),
			'content'	=> kws_ob_include( CTCT_DIR_PATH.'views/help/insert-form.html')
		);
		$tabs[] = array(
			'id'	=> 'ctct-insert-event',
			'title'	=> __('Constant Contact: Add an Event', 'ctct'),
			'content'	=> kws_ob_include( CTCT_DIR_PATH.'views/help/insert-event.html')
		);
		$tabs[] = array(
		    'id'    => 'ctct-insert-settings-spam',
		    'title' => __('Constant Contact: Settings > Spam Prevention', 'ctct'),
		    'content'   => kws_ob_include( CTCT_DIR_PATH.'views/help/settings-spam.phtml')
		);

		$tabs = apply_filters('constant_contact_help_tabs', $tabs, $screen);
		if(!empty($tabs) && is_object( $screen )) {
	    foreach($tabs as $tab) {

	    	// Wrap the contents in a .wrap class
	    	$screen->add_help_tab($tab);

	    	if(!empty($tab['sidebar'])) {
	    		$screen->set_help_sidebar($tab['sidebar']);
	    	}
	    }}

		return;
	}

}

new CTCT_Help_Menu;