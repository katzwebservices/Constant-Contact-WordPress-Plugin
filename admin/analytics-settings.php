<?php
	#$this->addActions();
#	r($this, true);

	$notification = (
		isset($_GET['ccStats_error']) ?
			'<span class="error" style="padding:3px;"><strong>Error</strong>: '.esc_html(stripslashes($_GET['ccStats_error'])).'</span>' :
			''
	);


	if (empty($this->ga_token)) {
		$config_warnings = $this->config_warnings();
		if (isset($_GET['ccStats_ga_token_capture_errors'])) {
				// when the attempt to get token fails. most likely point of failure initially.
				$this->show_ga_auth_error('Whoops! <strong>We did not get an authorization token back from Google</strong>.', $_GET['ccStats_ga_token_capture_errors']);
		}
		else if (!empty($config_warnings)) { // have config warnings only
			$this->warning_box('Possible Server Configuration Problem', null, $config_warnings);
		}
	}

	if (empty($_GET['ccStats_revoke_token_chicken_and_egg'])) { ?>
			<h2 id="ccStats-connect-to-google-head" class="ccStats-subhead<?php echo (empty($_GET['ccStats_revoke_token_chicken_and_egg']) && !empty($this->ga_token) ? ' complete' : '') ?>"><?php _e('Connect to Google Analytics', 'constant-contact-api'); ?><?php echo (!empty($this->ga_token) && empty($_GET['ccStats_revoke_token_chicken_and_egg']) ? '<img src="'.plugins_url('lib/constant-analytics/images/check.gif', CTCT_FILE).'" width="30" height="30" alt="Successfully Configured" style="padding-left:10px;"/>' : ''); ?></h2>
<?php
	}

	if (empty($this->ga_token)) { // no token
?>

		<h3><?php _e('Authenticate this site with Google.', 'constant-contact-api'); ?></h3>
		<p><?php _e('Click the button below to be taken to Google&rsquo;s authentication page. <strong>After logging in to Google, choose "Grant Access"</strong>, you will be returned to Constant Analytics.', 'constant-contact-api'); ?></p>
		<p class="submit"><a href="<?php echo $this->google_authentication_url(); ?>" class="button button-primary"><?php _e('Begin Authentication', 'constant-contact-api'); ?></a></p>

<?php
	} else { // token
		if (isset($_GET['ccStats_revoke_token_chicken_and_egg'])) {
			$this->warning_box(
				'<strong>You must have a valid token to revoke a token!</strong>',
				$_GET['ccStats_revoke_token_chicken_and_egg'],
				'Bit of a chicken-and-egg problem, we know. Click the link below to forget this token and start over, if necessary.'
			);
			?>
			<form action="options.php" method="post" class="ccStats-revoke-or-forget">
				<div>
					<?php settings_fields( 'constant-analytics' ); ?>
					<input type="hidden" name="ccStats_action" value="forget_ga_token" />
					<input type="hidden" name="ccStats_nonce" value="<?php echo $this->create_nonce('forget_ga_token'); ?>" />
					<h3><?php _e('Need to forget your Google Analytics authorization token?', 'constant-contact-api'); ?></h3>

						<p><?php _e('You may need to do this if access to this account has been revoked outside of Constant Analytics.', 'constant-contact-api'); ?> </p>
						<p class="submit" style="padding-bottom:0em;"><input id="ccStats-revoke-ga-auth" class="button button-primary" type="submit" value="<?php _e('Forget My Token', 'constant-contact-api'); ?>"/></p>
				</div>
			</form>
		<?php
		}
		else if (!empty($this->ga_auth_error)) {
			$this->show_ga_auth_error('Hmm. <strong>Something went wrong with your Google authentication!</strong>', $this->ga_auth_error);
		}
		else if (isset($connection_errors) && count($connection_errors)) { // have session token; couldn't connect to get profile list
			$this->show_ga_auth_error('Darn! <strong>You should have access to an account, but we couldn\'t connect to Google</strong>!', implode('</br>', $connection_errors));
		}
		else {
?>

		<p><strong><?php _e('Your Google account has been successfully connected.', 'constant-contact-api'); ?></strong></p>

		<form action="<?php echo admin_url('options.php'); ?>" method="post" class="ccStats-revoke-or-forget">
			<div>
				<?php settings_fields( 'constant-analytics' ); ?>
				<input type="hidden" name="ccStats_action" value="revoke_ga_token" />
				<input type="hidden" name="ccStats_nonce" value="<?php echo $this->create_nonce('revoke_ga_token'); ?>" />
				<p><a id="ccStats-revoke-ga-auth-link" href="javascript:;"><?php _e('Want to revoke access to this analytics account?', 'constant-contact-api'); ?></a> <span class="howto"><?php _e('(or switch Google Accounts)', 'constant-contact-api'); ?></span></p>
				<div id="ccStats-revoke-ga-auth-container" style="display:none; border-top:1px solid #ccc; margin-top:5px; padding:10px;">
					<p><?php _e('Press the button below to revoke Constant Contact plugin access to your Google Analytics account: <span class="howto"><strong>You will be able</strong> to re-connect to this account if you want to.</span>', 'constant-contact-api'); ?></p>
					<p><input id="ccStats-revoke-ga-auth" class="button button-primary" type="submit" value="<?php _e('Revoke Access', 'constant-contact-api'); ?>"/></p>
				</div>
			</div>
		</form>

		<h2><?php _e('Choose a Profile to Track', 'constant-contact-api'); ?></h2>

		<?php if (count($this->profiles)) : ?>

			<p>
				You have <?php echo count($this->profiles); ?> profiles in your account.
				Currently you're tracking
				<strong><a href="https://www.google.com/analytics/reporting/?id=<?php echo $this->ga_profile_id; ?>"><?php echo $this->profiles[$this->ga_profile_id]['title']; ?></a></strong><?php echo (count($this->profiles) > 1 ? ', but you can change that if you\'d like.' :'.'); ?>
			</p>

			<?php if (count($this->profiles) > 1) : ?>
					<form action="<?php echo admin_url('options.php'); ?>" method="post">
						<input type="hidden" name="ccStats_action" value="set_ga_profile_id" />
						<input type="hidden" name="ccStats_nonce" value="<?php echo $this->create_nonce('set_ga_profile_id'); ?>" />
						<label for="ccStats-profile-id-select">From now on track:</label>
						<select id="ccStats-profile-id-select" name="profile_id">
							<?php echo implode("\n", $this->profile_options); ?>
						</select>
						<input type="submit" class="button" value="<?php _e('Track This Site', 'constant-contact-api'); ?>" />
					</form>
			<?php endif; ?>

			<h3 style="border-top:1px solid #ccc; padding-top:.75em; margin-top:1em;"><a href="<?php echo admin_url('index.php?page=constant-analytics.php'); ?>">View the Constant Analytics Page &rarr;</a></h3>

		<?php else :  /* if (count($this->profiles)) */ ?>

			<p>
				<?php _e('You do not have any profiles associated with your Google Analytics account. Probably better <a href="https://www.google.com/analytics">head over there</a> and set one up!', 'constant-contact-api'); ?>
			</p>

		<?php endif; /* if (count($this->profiles)) */ ?>

	<?php } /* if (!empty($ga_auth_error)) */ ?>


	<?php
	if (!empty($config_warnings)) { // have config warnings, but we have a token
		$this->warning_box('Possible Server Configuration Problem', null, $config_warnings);
	}
	?>


<?php } /* if (empty($this->ga_token)) */ ?>
		
		
<script>
	jQuery(document).ready(function() {
		jQuery('.ccStats-tabs li').click(function() {
			var id = jQuery(this).attr('id');
			jQuery('.ccStats-tab-contents li').hide('fast');
			jQuery('#' + id.substring(0, id.indexOf('-tab')) + '-content').show('fast');
			jQuery(this).addClass('ccStats-selected').siblings().removeClass('ccStats-selected');
			return false;
		});
		jQuery('#ccStats-revoke-ga-auth-link').click(function() {
			jQuery('#ccStats-revoke-ga-auth-container').slideToggle();
			return false;
		})
	});
</script>