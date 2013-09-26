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
		}
	}

	function add_menu() {
		// create new top-level menu
		add_menu_page(__('Constant Contact API', 'constant-contact-api'), __('Constant Contact', 'constant-contact-api'), 'manage_options', 'constant-contact-api', array(&$this, 'page'), CTCT_FILE_URL.'images/admin/constant-contact-admin-icon.png');
	}

	function addActions() {
		$CTCT_Settings = new CTCT_Settings();

		add_filter('admin_footer_text', array(&$this, 'pluginStatus'));
	}

	function pluginStatus($content = '') {
		global $plugin_page;
		if($plugin_page === $this->getKey()) {
			return kws_ob_include(CTCT_FILE_PATH.'/views/admin/view.plugin-status.php', $CTCT);
		}
		return $content;
	}

	function page() {

		if(!$this->cc->isConfigured()) {
			echo kws_ob_include(CTCT_DIR_PATH.'views/admin/view.setup.php', $this);
		} else {
			parent::page();
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
