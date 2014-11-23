<?php
/**
 *
 * Just a backward-compatibility placeholder file - this is where a previous sub-plugin file existed.
 *
 * @since  3.1.4
 */

/** If this file is called directly, abort. */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if( function_exists( 'deactivate_plugins' ) ) {
	deactivate_plugins( dirname( plugin_basename( __FILE__ ) ) . '/form-designer.php' );
}