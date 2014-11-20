<?php

class CTCT_Admin extends CTCT_Admin_Page {

	var $key = 'constant-contact-api';
	var $title = 'Settings';
	protected function add() {}
	protected function edit() {}
	protected function view() {}
	protected function single() {}
	protected function processForms() {
		if(isset($_GET['error']) && isset($_GET['error_description'])) {
			$this->errors[] = new WP_Error($_GET['error'], $_GET['error_description']);
		}
		if(isset($_GET['de-authenticate']) && wp_verify_nonce( $_GET['de-authenticate'], 'de-authenticate' )) {
			$this->oauth->deleteToken();
			delete_option('ccStats_ga_token');
			delete_option('ccStats_ga_profile_id');
		}
		if(isset($_GET['delete-settings']) && wp_verify_nonce( $_GET['delete-settings'], 'delete-settings' )) {
			CTCT_Settings::flush_transients();
			$this->oauth->deleteToken();
			delete_option('ccStats_ga_token');
			delete_option('ccStats_ga_profile_id');
			delete_option('cc_form_increment');
			delete_option('cc_form_design');
			delete_option('ctct_configured');
			delete_option('ctct_settings');
			delete_option('cc_username');
			delete_option('cc_password');
		}
	}

	function add_menu() {
		// create new top-level menu
		add_menu_page(__('Constant Contact API', 'ctct'), __('Constant Contact', 'ctct'), 'manage_options', 'constant-contact-api', array(&$this, 'page'), CTCT_FILE_URL.'images/admin/constant-contact-admin-icon.png');
	}

	function addActions() {
		$CTCT_Settings = new CTCT_Settings;

		add_filter('admin_footer_text', array(&$this, 'pluginStatus'));

		add_action('constant_contact_add_notice', array(&$this, 'add_notice'));
		add_action('admin_notices', array(&$this, 'admin_notice' ));
	}

	public function add_notice( $notice ) {

	    $this->notices[] = $notice;

	    // Already printed the notice, so we're going to print it again.
	    if( did_action( 'admin_notices' ) ) {
	        do_action( 'admin_notices' );
	    }
	}

	/**
	 * Print notices, if any
	 * @return [type] [description]
	 */
	public function admin_notice() {

	    if( !empty( $this->notices ) ) {

	        echo '<div class="updated">';
	        foreach ( (array)$this->notices as $key => $notice ) {

	        	if( is_wp_error( $notice ) ) {

	        		echo '<h3>'.esc_html( sprintf( __('Error: %s', 'ctct'), $notice->get_error_code() ) ).'</h3>';

	        		echo wpautop( esc_html( $notice->get_error_message() ) );

	        	} else {
	        		echo wpautop( esc_html( $notice ) );
	        	}

	        }
	        echo '</div>';
	    }

	    $this->notices = array();
	}

	function pluginStatus($content = '') {
		global $plugin_page;
		if($plugin_page === $this->getKey()) {
			return kws_ob_include(CTCT_FILE_PATH.'/views/admin/view.plugin-status.php', $CTCT);
		}
		return $content;
	}

	function page() {

		parent::page();

		if(!$this->cc->isConfigured()) {
			echo kws_ob_include(CTCT_DIR_PATH.'views/admin/view.connect-account.php', $this);
		}
	}

	function content() {
	?>
		<form action="options.php" method="post">
		<?php
			settings_fields('ctct_settings');

			CTCT_Settings::do_settings_sections('constant-contact-api');

		?>
		</form>
	<?php
	}
}

$CTCT_Admin = new CTCT_Admin();
