<?php

final class CTCT_OAuth_Migration {

	function __construct() {

		// When token is added, delete previous settings
		add_action( 'ctct_token_updated', array( $this, 'token_updated') );

		// If CTCT is already configured, no need for this.
		if( WP_CTCT::getInstance()->cc->isConfigured() ) { return; }

		add_action( 'admin_notices', array( $this, 'admin_notice') );

	}

	/**
	 * When an oauth token is processed, delete the username and password settings
	 * @param  array|boolean $token Array if success, false if not successful token
	 * @return void
	 */
	function token_updated( $token ) {

		// Make sure Version 2.x options are removed
		delete_option('cc_username');
		delete_option('cc_password');

	}

	/**
	 * Show notices everywhere. It's immediately necessary for them to update.
	 * @return void
	 */
	function admin_notice() {
		global $pagenow;

		// Don't show the notice on the settings page
		if( $pagenow === 'admin.php' && !empty( $_GET['page'] ) && $_GET['page'] === 'constant-contact-api' ) {
			return;
		}

		$CTCT = WP_CTCT::getInstance();

		?>
		<div class="error">
			<p><img src="<?php echo plugins_url('images/admin/logo-horizontal.png', CTCT_FILE); ?>" width="225" height="33" alt="" /></p>
			<h3><?php esc_html_e('The Constant Contact plugin isn\'t connected.', 'ctct'); ?></h3>
			<p><?php esc_html_e('Please log in to Constant Contact using the button below and your site will be connected.', 'ctct'); ?></p>
			<p><a href="<?php echo $CTCT->oauth->getAuthorizationUrl(); ?>" class="button button-primary button-hero"><?php esc_html_e('Authorize the Plugin on ConstantContact.com', 'ctct'); ?></a></p>
		</div>
		<?php
	}

}

new CTCT_OAuth_Migration;