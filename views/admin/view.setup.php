<?php
/**
 * CTCT passed by kws_ob_include
 */
    $token = $CTCT->oauth->getToken();
    $tokenTime = date_i18n( get_option('date_format'), $CTCT->oauth->getToken('time') );
    $tokenUsername = $CTCT->oauth->getToken('username');

    $token_title = sprintf(__('Your token is %s', 'constant-contact-api'), $token);
    $token_tag = sprintf('<span title="%s">%s</span>', $token_title, $tokenUsername );

    echo sprintf('
        <h3>%s</h3>
        <p>%s</p>
        <p class="submit">
    		<a href="%s" class="button button-primary button-large">%s</a> <span class="button-group alignright"><a href="%s" class="button button-warning confirm" data-confirm="%s">%s</a><a href="%s" class="button button-danger confirm" data-confirm="%s" data-confirm-again="%s">%s</a></span>
    	</p>',
        sprintf( esc_html__('Your plugin is configured for the username %s.', 'constant-contact-api' ), $token_tag ),
        sprintf( esc_html__('The plugin was configured on %s.', 'constant-contact-api'), $tokenTime),
    	$CTCT->oauth->getAuthorizationUrl(),
    	esc_html__('Switch Connected Accounts', 'constant-contact-api'),
    	add_query_arg(array('de-authenticate' => wp_create_nonce('de-authenticate')), remove_query_arg(array('error', 'error_description', 'oauth'))),
    	sprintf(esc_html__('Your site will no longer be connected to the %s Constant Contact account. Form configurations will remain intact.', 'constant-contact-api'), $tokenUsername),
    	esc_html__('De-Authenticate Plugin', 'constant-contact-api'),
    	add_query_arg(array('delete-settings' => wp_create_nonce('delete-settings')), remove_query_arg(array('error', 'error_description', 'oauth'))),
    	esc_js(__('This will remove ALL DATA, including Form Designer forms and account information. Continue?', 'constant-contact-api') ),
    	esc_js(__('Are you really sure? All Constant Contact plugin data will be removed and you will have to start from scratch. Continue?', 'constant-contact-api') ),
    	esc_html__('Delete All Plugin Settings', 'constant-contact-api')
    );