<?php
/*
Plugin Name: 		Constant Contact Plugin for WordPress
Plugin URI: 		https://github.com/katzwebservices/Constant-Contact-WordPress-Plugin
Description: 		Powerfully integrate <a href="http://katz.si/6e" target="_blank">Constant Contact</a> into your WordPress website.
Author: 			Katz Web Services, Inc.
Version: 			3.1.6
Author URI: 		http://katz.co
Text Domain: 		ctct
Domain Path: 		/languages
License:           	GPLv2 or later
License URI: 		http://www.gnu.org/licenses/gpl-2.0.html
*/

/** If this file is called directly, abort. */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Delete old username & password on activation
register_deactivation_hook( __FILE__, array( 'WP_CTCT', 'deactivate' ) );

final class WP_CTCT {

	const version = '3.1.6';
	public $cc = NULL;
	public $oauth = NULL;
	public $log = NULL;
	private static $instance = NULL;

	function __construct() {

		// Allow users to override with their own keys in wp-config.php
		if(!defined('CTCT_APIKEY')) {
			define("CTCT_APIKEY", "hu2nnqvtd3gt82uwkr7z565t");
			define("CTCT_APISECRET", "z39WYdrXu7tuEtaJcGPzN3dF");
		}

		if(!defined('CTCT_DIR_PATH')) {

			define('CTCT_FILE', __FILE__);
			define('CTCT_FILE_PATH', dirname(__FILE__) . '/');
			define('CTCT_FILE_URL', plugin_dir_url(__FILE__));
			define('CTCT_DIR_PATH', plugin_dir_path(__FILE__));

			load_plugin_textdomain( 'ctct', false, dirname( plugin_basename( CTCT_FILE ) ) . '/languages/' );

			/**
			 * If the server doesn't support PHP 5.3, sorry, but you're outta luck.
			 */
			if(version_compare(phpversion(), '5.3') <= 0) {
				include CTCT_DIR_PATH.'inc/incompatible.php';
				return;
			}

			add_action('plugins_loaded', array(&$this, 'setup'), 1);
			add_action('plugins_loaded', array(&$this, 'include_files'), 5);

		} else {
			$this->setup();
		}
	}

	/**
	 * Delete authentication and cached data on de-activation of the plugin
	 *
	 * @return void
	 */
	static function deactivate() {

		// Clear OAuth tokens
		delete_option( 'ctct_token_response' );
		delete_option( 'ctct_configured' );

		// CTCT_Settings is only loaded for 5.3+
		// If they don't have 5.3, they never had the transients in the first place.
		if( class_exists('CTCT_Settings') ) {

			// Flush stored transient data
			CTCT_Settings::flush_transients();

		}

	}

	function setup() {

		require_once CTCT_DIR_PATH.'vendor/autoload.php';

		if( !class_exists( 'KWSOAuth2' ) ) {

			include CTCT_DIR_PATH.'classes/class.kwsrestclient.php';
			include CTCT_DIR_PATH.'classes/class.kwsoauth2.php';
			include CTCT_DIR_PATH.'classes/class.kwsconstantcontact.php';

			$this->oauth = new KWSOAuth2();

			$token = $this->oauth->getToken();

			define("CTCT_ACCESS_TOKEN", $token);

			define("CTCT_USERNAME", $this->oauth->getToken('username'));
		}

		if(is_null($this->oauth)) { $this->oauth = new KWSOAuth2(); }

		$this->cc = new KWSConstantContact();

		include CTCT_DIR_PATH.'classes/class.oauth-migration.php';
	}

	static function getInstance() {

		if(empty(self::$instance)){
			self::$instance = new WP_CTCT;
		}

		return self::$instance;
	}

	function include_files() {

		/** Helpers */
		include_once CTCT_DIR_PATH.'lib/cache-http.php';
		include_once CTCT_DIR_PATH.'inc/table.php';
		include_once CTCT_DIR_PATH.'inc/functions.php';

		// TODO: Flesh out the help tabs
		include_once CTCT_DIR_PATH.'inc/help.php';

		/** Classes */
		include_once CTCT_DIR_PATH.'classes/class.ctct_process_form.php';
		include_once CTCT_DIR_PATH.'classes/class.kwscontact.php';
		include_once CTCT_DIR_PATH.'classes/class.kwscontactlist.php';
		include_once CTCT_DIR_PATH.'classes/class.kwscampaign.php';
		include_once CTCT_DIR_PATH.'classes/class.kwsajax.php';
		include_once CTCT_DIR_PATH.'classes/class.ctct_admin_page.php';
		include_once CTCT_DIR_PATH.'classes/class.ctct_settings.php';
		include_once CTCT_DIR_PATH.'classes/class.ctct_admin.php';

		include_once CTCT_DIR_PATH.'lib/kwslog.php';

		$this->log = new KWSLog('ctct', 'Constant Contact');

		/** Admin pages */
		include_once CTCT_DIR_PATH.'admin/profile.php';
		include_once CTCT_DIR_PATH.'admin/campaigns.php';
		include_once CTCT_DIR_PATH.'admin/contacts.php';
		include_once CTCT_DIR_PATH.'admin/lists.php';

		// If the plugin is not configured, don't do anything else.
		if(!is_ctct_configured()) { return; }

		/** Modules */
		include_once CTCT_DIR_PATH.'lib/registration.php';
		include_once CTCT_DIR_PATH.'lib/constant-analytics/constant-analytics.php';
		include_once CTCT_DIR_PATH.'lib/comment-form-signup.php';
		include_once CTCT_DIR_PATH.'lib/simple-widget.php';

		if( CTCT_Settings::get('eventspot') ) {
			include_once CTCT_DIR_PATH.'lib/eventspot/eventspot.php';
		}

		include_once CTCT_DIR_PATH.'lib/form-designer/form-designer.php';
	}
}

WP_CTCT::getInstance();