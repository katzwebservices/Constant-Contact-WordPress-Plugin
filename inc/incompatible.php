<?php

add_action('admin_print_styles', 'ctct_compatibility_styles');
add_action('admin_notices', 'ctct_compatibility_notice');
function ctct_compatibility_styles() {
	wp_enqueue_style('constant-contact-api-admin', CTCT_FILE_URL.'css/admin/constant-contact-admin-css.css', false, false, 'all');
}
function ctct_compatibility_notice() {
	include_once CTCT_DIR_PATH.'views/admin/view.php-compatibility-notice.php';
}
