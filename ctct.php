<?php

/** If this file is called directly, abort. */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

add_action('plugins_loaded', 'ctct_migrate_to_constant_contact_api_php_file_name');

/**
 * The main plugin file changed from `constant-contact-api.php` to `ctct.php` in 3.1. This changes it back.
 *
 * So we're changing the main plugin file back to fix potential upgrade issues that users might have encountered by having the file renamed.
 *
 * @since  3.1.4
 * @return void
 */
function ctct_migrate_to_constant_contact_api_php_file_name() {

	if( !function_exists( 'activate_plugin' ) ) { return; }

	$result = activate_plugin( dirname( plugin_basename(__FILE__) ) . '/constant-contact-api.php' );

	deactivate_plugins( dirname( plugin_basename(__FILE__) ) . '/ctct.php' );

}