<?php

$siteName = ($this->ga_profile_id && isset($this->profiles[$this->ga_profile_id]) && isset($this->profiles[$this->ga_profile_id]['title'])) ? $this->profiles[$this->ga_profile_id]['title'] : '';
?>

<div class="wrap nosubsub">
	<h2 class="cc_logo"><a class="cc_logo" href="<?php echo admin_url('admin.php?page=constant-contact-api'); ?>">Constant Contact Plugin &gt;</a> Constant Analytics</h2>
<?php
		if(empty($this->ga_profile_id)) {
			echo sprintf(__('<div id="message" class="error"><p>Google Analytics integration is not configured. <a href="%s">Configure your Analytics settings</a>.</p></div></div>', 'constant-contact-api'), admin_url('admin.php?page=constant-analytics')); return;
		}

		if(!empty($siteName)) {
			echo '<h2 style="margin-left:1%; padding-top:1px;">';
			_e(sprintf('Analytics for %s', '<a href="http://'.$siteName.'" target="_blank" title="Visit '.$siteName.'">'.$siteName.'</a>'), 'constant-contact-api');
			echo ' <a style="font-size:65%; right:130px; top:0; position:absolute;" href="'.admin_url('admin.php?page=constant-analytics').'">'.__(sprintf('Switch Analytics profile %s', '&rarr;')).'</a>
			</h2>';
		}
?>

	<div id="ccStats-datepicker">
		<div id="ccStats-datepicker-pane" style="display:none;">
			<div id="ccStats-datepicker-calendars"></div>
			<input type="submit" id="ccStats-apply-date-range" class="button" value="<?php _e('Apply', 'constant-contact-api'); ?>" />
			<div id="ccStats-current-date-range-desc"></div>
		</div>
		<div id="ccStats-datepicker-popup">
			<div id="ccStats-current-date-range">
				<div id="ccStats-current-start-date"><input style="display:none;" type="text" /><span><?php _e('Loading', 'constant-contact-api'); ?></span></div> -
				<div id="ccStats-current-end-date"><input style="display:none;" type="text" /><span></span></div>
			</div>
		</div>
	</div>

	<div class="ccStats-box" id="ccStats-box-site-traffic">
		<div class="ccStats-box-header"><h3>Site Traffic</h3><div class="ccStats-box-status"></div><div class="ccStats-box-status-text"></div></div>
		<div class="ccStats-box-content">
			<ul id="ccStats-linechart-legend">
				<li class="blog-post" style="display:none;"><?php _e('blog post', 'constant-contact-api'); ?></li>
				<li class="campaign" style="display:none;"><?php _e('email campaign', 'constant-contact-api'); ?></li>
			</ul>
			<ul class="ccStats-tabs left">
			</ul>
			<ul class="ccStats-tab-contents border">
				<li id="ccStats-all-traffic-container">
					<div id="ccStats-all-traffic-graph">
					</div>
				</li>
				<li id="ccStats-campaign-traffic-container" style="display:none">
				</li>
			</ul>
			<div class="ccStats-stats-container">
				<dl class="ccStats-stats-list" style="display:none;">
					<dt><?php _e('Visits', 'constant-contact-api'); ?></dt>
					<dd>
						<div class="ccStats-stat-spark" id="ccStats-stat-visits-spark"></div>
						<div class="ccStats-stat" id="ccStats-stat-visits"></div>
					</dd>
					<dt><?php _e('Pageviews', 'constant-contact-api'); ?></dt>
					<dd>
						<div class="ccStats-stat-spark" id="ccStats-stat-pageviews-spark"></div>
						<div class="ccStats-stat" id="ccStats-stat-pageviews"></div>
					</dd>
					<dt><?php _e('Pages/Visit', 'constant-contact-api'); ?></dt>
					<dd>
						<div class="ccStats-stat-spark" id="ccStats-stat-pages-per-visit-spark"></div>
						<div class="ccStats-stat" id="ccStats-stat-pages-per-visit"></div>
					</dd>
				</dl>
			</div>
			<div class="ccStats-stats-container">
				<dl class="ccStats-stats-list" style="display:none;">
					<dt><?php _e('Bounce Rate', 'constant-contact-api'); ?></dt>
					<dd>
						<div class="ccStats-stat-spark" id="ccStats-stat-bounce-rate-spark"></div>
						<div class="ccStats-stat" id="ccStats-stat-bounce-rate"></div>
					</dd>
					<dt><?php _e('Avg. Time on Site', 'constant-contact-api'); ?></dt>
					<dd>
						<div class="ccStats-stat-spark" id="ccStats-stat-time-on-site-spark"></div>
						<div class="ccStats-stat" id="ccStats-stat-time-on-site"></div>
					</dd>
					<dt><?php _e('% New Visits', 'constant-contact-api'); ?></dt>
					<dd>
						<div class="ccStats-stat-spark" id="ccStats-stat-new-visits-spark"></div>
						<div class="ccStats-stat" id="ccStats-stat-new-visits"></div>
					</dd>
				</dl>
			</div>
		</div>
	</div>

	<div class="ccStats-box" id="ccStats-box-traffic-by-region">
		<div class="ccStats-box-header"><h3><?php _e('Traffic By Region', 'constant-contact-api'); ?></h3><div class="ccStats-box-status"></div><div class="ccStats-box-status-text"></div></div>
		<div class="ccStats-box-content">
			<div id="ccStats-geo-map"></div>
		</div>
	</div>
	<div class="ccStats-box half" id="ccStats-box-referring-traffic-overview">
		<div class="ccStats-box-header"><h3><?php _e('Referring Traffic Overview', 'constant-contact-api'); ?></h3><div class="ccStats-box-status"></div><div class="ccStats-box-status-text"></div></div>
		<div class="ccStats-box-content">
			<div id="ccStats-referring-traffic-overview-legend"></div>
			<div id="ccStats-referring-traffic-chart"></div>
		</div>
	</div>
	<div class="ccStats-box" id="ccStats-box-top-referrers">
		<div class="ccStats-box-header"><div class="ccStats-breadcrumbs"></div><h3><?php _e('Top Referrers', 'constant-contact-api'); ?></h3><div class="ccStats-box-status"></div><div class="ccStats-box-status-text"></div></div>
		<div class="ccStats-box-content">
			<div class="ccStats-table-container" id="ccStats-top-referrers"></div>
		</div>
	</div>
	<div class="ccStats-box" id="ccStats-box-top-content">
		<div class="ccStats-box-header"><div class="ccStats-breadcrumbs"></div><h3><?php _e('Top Content', 'constant-contact-api'); ?></h3><div class="ccStats-box-status"></div><div class="ccStats-box-status-text"></div></div>
		<div class="ccStats-box-content">
			<div class="ccStats-table-container" id="ccStats-top-content"></div>
		</div>
	</div>
</div>