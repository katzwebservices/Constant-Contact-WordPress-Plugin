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
	 * @see CTCT_Admin_Page::$component
	 * @param string $component_type Pass a compenent type to flush, like `Lists`.
	 */
	public static function flush_transients( $component_type = null ) {
		global $wpdb;

		// When triggered from `ctct_token_updated`, the passed parameter will be a token array
		if ( is_array( $component_type ) ) {
			return;
		}

		$transient_name = "transient_ctct";
		$transient_timeout_name = "transient_timeout_ctct";

		if( $component_type ) {
			$component_type = strtolower( $component_type );
			$transient_name = sprintf( "transient_ctct_%s", $component_type );
			$transient_timeout_name = sprintf( "transient_timeout_ctct_%s", $component_type );
		}

		$query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}options WHERE `option_name` LIKE %s OR `option_name` LIKE %s", "%{$transient_name}%", "%{$transient_timeout_name}%");
		$wpdb->query($query);

		$query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}options WHERE `option_name` LIKE %s OR `option_name` LIKE %s", '%transient_cc%', '%transient_timeout_cc%');
		$wpdb->query($query);

	}
}

new CTCT_Global;