<?php
/**
 * CTCT passed by kws_ob_include
 */
    $token = $CTCT->oauth->getToken();
    $tokenTime = date_i18n( get_option('date_format'), $CTCT->oauth->getToken('time') );
    $tokenUsername = $CTCT->oauth->getToken('username');

    $token_title = sprintf(__('Your token is %s', 'ctct'), $token);
    $token_tag = sprintf('<span title="%s">%s</span>', $token_title, $tokenUsername );

    echo sprintf('
        <div class="updated inline" style="padding: 10px;">
            <h3 style="margin-top:0">%s</h3>
            <h4><span class="description">%s</span></h4>
            <p>
                <span class="alignleft"><a href="%s" class="button button-primary button-large">%s</a></span>
                <span class="button-group alignright"><a href="%s" class="button button-warning confirm" data-confirm="%s">%s</a>|<a href="%s" class="button button-danger confirm" data-confirm="%s" data-confirm-again="%s">%s</a></span>
            </p>
            <div class="clear"></div>
        </div>
    	',
        sprintf( esc_html__('The plugin is connected to the username %s.', 'ctct'), '<tt>'.$token_tag.'</tt>' ),
        sprintf( esc_html__('The plugin was authorized on %s.', 'ctct'), $tokenTime),
    	$CTCT->oauth->getAuthorizationUrl(),
    	esc_html__('Switch Connected Accounts', 'ctct'),
    	add_query_arg(array('de-authenticate' => wp_create_nonce('de-authenticate')), remove_query_arg(array('error', 'error_description', 'oauth'))),
    	sprintf(esc_html__('Your site will no longer be connected to the %s Constant Contact account. Form configurations will remain intact.', 'ctct'), $tokenUsername),
    	esc_html__('De-Authenticate Plugin', 'ctct'),
    	add_query_arg(array('delete-settings' => wp_create_nonce('delete-settings')), remove_query_arg(array('error', 'error_description', 'oauth'))),
    	esc_js(__('This will remove ALL DATA, including Form Designer forms and account information. Continue?', 'ctct') ),
    	esc_js(__('Are you really sure? All Constant Contact plugin data will be removed and you will have to start from scratch. Continue?', 'ctct') ),
    	esc_html__('Delete All Plugin Settings', 'ctct')
    );