<?php
/*
Plugin Name: Official Constant Contact Plugin
Plugin URI: http://www.katzwebservices.com
Description: Powerfully integrate <a href="http://katz.si/6e" target="_blank">Constant Contact</a> into your WordPress website.
Author: Katz Web Services, Inc.
Version: 3.0
Author URI: http://www.katzwebservices.com
*/

class WP_CTCT {

	public $cc = NULL;
	public $oauth = NULL;
	public $log = NULL;
	private static $instance = NULL;

	function __construct() {

		if(!defined('CTCT_VERSION')) {

			define('CTCT_VERSION', '3.0');
			define('CTCT_FILE', __FILE__); // The full path to this file
			define('CTCT_FILE_PATH', dirname(__FILE__) . '/'); // The full path to this file
			define('CTCT_FILE_URL', plugin_dir_url(__FILE__)); // @ Added 2.0 The full URL to this file
			define('CTCT_DIR_PATH', plugin_dir_path(__FILE__)); // @ Added 2.0 The full URL to this file

			/**
			 * If the server doesn't support PHP 5.3, sorry, but you're outta luck.
			 */
			if(version_compare(phpversion(), '5.3') <= 0) {
				include CTCT_DIR_PATH.'inc/incompatible.php';
				return;
			}

			include CTCT_DIR_PATH.'lib/kwslog.php';
			$this->log = new KWSLog('ctct', 'Constant Contact');

			require CTCT_DIR_PATH.'lib/exceptional-php/exceptional.php';
			#Exceptional::setup('9135dc8782a0e5f1b72b51a73b0382982521f943', true);
			#Exceptional::setup('', true);

			Exceptional::$controller = 'WP_CTCT';

			add_action('plugins_loaded', array(&$this, 'setup'), 1);
			add_action('plugins_loaded', array(&$this, 'include_files'), 5);

		} else {
			$this->setup();
		}
	}

	function setup() {

		Exceptional::$controller = 'setup';

		if(!defined('CTCT_APIKEY')) {

			if(!class_exists('Ctct\SplClassLoader')) {
				require CTCT_DIR_PATH.'lib/Ctct/autoload.php';
			}

			include CTCT_DIR_PATH.'classes/class.kwsrestclient.php';
			include CTCT_DIR_PATH.'classes/class.kwsoauth2.php';
			include CTCT_DIR_PATH.'classes/class.kwsconstantcontact.php';

			define("CTCT_APIKEY", "hu2nnqvtd3gt82uwkr7z565t");
			define("CTCT_APISECRET", "z39WYdrXu7tuEtaJcGPzN3dF");
			$this->oauth = new KWSOAuth2();
			$token = $this->oauth->getToken();
			define("CTCT_ACCESS_TOKEN", $token);
			define("CTCT_USERNAME", $this->oauth->getToken('username'));
		}
		
		if(is_null($this->oauth)) { $this->oauth = new KWSOAuth2(); }

		$this->cc = new KWSConstantContact();
	}

	static function getInstance() {

		if(empty(self::$instance)){
			self::$instance = new WP_CTCT(false);
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
		#include_once CTCT_DIR_PATH.'inc/pointers.php';
		#include_once CTCT_DIR_PATH.'classes/class.pointers.php';

		/** Classes */
		include_once CTCT_DIR_PATH.'classes/class.ctct_process_form.php';
		include_once CTCT_DIR_PATH.'classes/class.kwscontact.php';
		include_once CTCT_DIR_PATH.'classes/class.kwscontactlist.php';
		include_once CTCT_DIR_PATH.'classes/class.kwscampaign.php';
		include_once CTCT_DIR_PATH.'classes/class.kwsajax.php';
		include_once CTCT_DIR_PATH.'classes/class.ctct_admin_page.php';
		include_once CTCT_DIR_PATH.'classes/class.ctct_settings.php';
		include_once CTCT_DIR_PATH.'classes/class.ctct_admin.php';

		/** Admin pages */
		include_once CTCT_DIR_PATH.'admin/profile.php';
		include_once CTCT_DIR_PATH.'admin/registration.php';
		include_once CTCT_DIR_PATH.'admin/campaigns.php';
		include_once CTCT_DIR_PATH.'admin/contacts.php';
		include_once CTCT_DIR_PATH.'admin/lists.php';

		// If the plugin is not configured, don't do anything else.
		if(!is_ctct_configured()) { return; }

		/** Modules */
		include_once CTCT_DIR_PATH.'lib/constant-analytics/constant-analytics.php';
		include_once CTCT_DIR_PATH.'lib/comment-form-signup.php';
		include_once CTCT_DIR_PATH.'lib/simple-widget.php';
		include_once CTCT_DIR_PATH.'lib/eventspot/eventspot.php';
		include_once CTCT_DIR_PATH.'lib/form-designer/form-designer.php';
	}
}
$CTCT = new WP_CTCT;