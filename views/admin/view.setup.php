<?php
/**
 * CTCT passed by kws_ob_include
 */
    $token = $CTCT->oauth->getToken();
    $tokenTime = date_i18n (get_option ('date_format'), $CTCT->oauth->getToken('time'));
    $tokenUsername = $CTCT->oauth->getToken('username');

    if(!$CTCT->cc->isConfigured()) {
    // Not configured.
    ?>

    <style type="text/css">
        #free_trial {
            background: url(<?php echo CTCT_FILE_URL; ?>admin/images/btn_free_trial_green.png) no-repeat 0px 0px;
            margin: 0px 5px 0px 0px;
            width: 246px;
            height: 80px;
        }
        .get-em,
        #grow-with-email,
        a#free_trial,
        a#see_how {
            display:block;
            text-indent:-9999px;
            overflow:hidden;
            float:left;
        }
        a#free_trial:hover,
        a#see_how:hover {
            background-position: 0px -102px;
        }
        #see_how {
            background: url(<?php echo CTCT_FILE_URL; ?>admin/images/btn_see_how_blue.png) no-repeat 0px 0px;
            margin: 0px 10px 0px 0px;
            width: 216px;
            height: 80px;
        }
         {
            text-indent: -9999px;
            overflow: hidden;
            text-align: left;
        }

        #grow-with-email {
            background-image: url(http://img.constantcontact.com/lp/images/standard/bv2/product_pages/test/grow_with_email_text.png);
            height: 91px;
            width: 720px;
        }
        #cc-message-wrap {
            p
            ing: 10px;
            margin-bottom: 20px!important;
            clear: both;
            float: left;
        }
        .get-em {
            float: left;
            clear: left;
            width: 201px;
            height: 81px;
            background: url('http://img.constantcontact.com/lp/images/standard/bv2/product_pages/test/btn_get_email_white.png') left top no-repeat;
        }
        .get-em:hover {
            background-position: left bottom;
        }
        .learn-more {
            margin-left: 15px!important;
        }
    </style>

    <div class="wrap ctct-wrap" style="margin:25px;">
        <div class="hr-divider"></div>
        <img src="<?php echo plugins_url('images/admin/logo-horizontal.png', CTCT_FILE); ?>" width="450" height="66" alt="Constant Contact" />

        <h2 class="clear"><strong><?php _e('Already have a Constant Contact account?', 'constant-contact-api'); ?></strong></h2>
    	<?php printf(__('<h2>To start, <a href="%s">Grant Authorization to this plugin</a> by signing in to Constant Contact.</h2>
    	<p>Sign in to the Constant Contact account you would like to use.</p><p>Once you sign in, you will be redirected back to this page, and plugin features will be available.</p>', 'constant-contact-api'), $CTCT->oauth->getAuthorizationUrl()); ?>
    	<hr />
    	<h2 class="clear"><strong><?php _e('Don\'t have a <a href="http://katz.si/4h" title="Learn more about Constant Contact">Constant Contact</a> account?', 'constant-contact-api'); ?></strong></h2>
    	<h2 class="clear"><?php _e('This plugin requires <a href="http://katz.si/4h" title="Learn more about Constant Contact">Constant Contact</a>.', 'constant-contact-api'); ?></h2>
    	<h3 id="grow-with-email"><?php _e('<strong>Grow with Email Marketing</strong> With Email Marketing, it\'s easy for you to connect with your customers, and for customers to share your message with their networks. And the more customers spread the word about your business, the more you grow', 'constant-contact-api'); ?></h3>
    	<p><a class="get-em" href="http://katz.si/4l"><?php _e('Start Your Free Trial', 'constant-contact-api'); ?></a></p>
    	<h2 class="learn-more alignleft"><?php printf(__('or <a href="%s">Learn More</a>', 'constant-contact-api'), 'http://katz.si/4h'); ?></h2>
    </div>
    <?php
    #    return;
    }
    // It's configured.
    else {
    	echo sprintf(__('
        	<h3>Your plugin is configured for the username <span title="%s">%s</span>.</h3>
        	<p>The plugin was configured on %s.</p>
        	<p class="submit">
        		<a href="%s" class="button button-primary button-large">%s</a> <span class="button-group alignright"><a href="%s" class="button button-warning confirm" data-confirm="%s">%s</a><a href="%s" class="button button-danger confirm" data-confirm="%s" data-confirm-again="%s">%s</a></span>
        	</p>
        ', 'constant-contact-api'), 
        	sprintf(__('Your token is %s', 'constant-contact-api'), $token), 
        	$tokenUsername, 
        	$tokenTime, 
        	$CTCT->oauth->getAuthorizationUrl(), 
        	__('Switch Connected Accounts', 'constant-contact-api'), 
        	add_query_arg(array('delete-settings' => wp_create_nonce('delete-settings')), remove_query_arg(array('error', 'error_description'))), 
        	sprintf(__('Your site will no longer be connected to the %s Constant Contact account.', 'constant-contact-api'), $tokenUsername), 
        	__('De-Authenticate Plugin', 'constant-contact-api'), 
        	add_query_arg(array('delete-settings' => wp_create_nonce('delete-settings')), remove_query_arg(array('error', 'error_description'))), 
        	__('This will remove ALL DATA, including Form Designer forms and account information. Continue?', 'constant-contact-api'),
        	__('Are you really sure? All Constant Contact plugin data will be removed and you will have to start from scratch. Continue?', 'constant-contact-api'),
        	__('Delete All Plugin Settings', 'constant-contact-api')
        );
    }

?>