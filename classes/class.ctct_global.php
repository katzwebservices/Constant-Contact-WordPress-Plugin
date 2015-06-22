<?php
/**
 * @package CTCT
 * @version 3.0
 */

/**
 * General class that runs globally (admin/frontend)
 */
final class CTCT_Global {

	function __construct() {
		$this->add_hooks();
	}

	private function add_hooks() {
		add_action( 'ctct_token_updated', array( __CLASS__, 'flush_transients' ) );
	}

	/**
	 * When settings are changed, delete the CTCT transients
	 *
	 * This is a bit better for data security, as it were.
	 *
	 */
	public static function flush_transients($token = array()) {
		global $wpdb;

		$query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}options WHERE `option_name` LIKE %s OR `option_name` LIKE %s", '%transient_ctct%', '%transient_timeout_ctct%');
		$wpdb->query($query);

		$query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}options WHERE `option_name` LIKE %s OR `option_name` LIKE %s", '%transient_cc%', '%transient_timeout_cc%');
		$wpdb->query($query);

	}
}

new CTCT_Global;