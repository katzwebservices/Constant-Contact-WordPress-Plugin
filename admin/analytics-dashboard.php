<?php

$siteName = ($this->ga_profile_id && isset($this->profiles[$this->ga_profile_id]) && isset($this->profiles[$this->ga_profile_id]['title'])) ? $this->profiles[$this->ga_profile_id]['title'] : '';
?>

<div class="wrap nosubsub">
	<h2 class="cc_logo"><a class="cc_logo" href="<?php echo admin_url('admin.php?page=constant-contact-api'); ?>">Constant Contact Plugin &gt;</a> Constant Analytics</h2>
<?php
		if(empty($this->ga_profile_id)) {
			$ga_not_configured = esc_html__('Google Analytics integration is not configured.', 'ctct');
			$configure_settings = esc_html__('Configure your Analytics settings.', 'ctct');
			printf('<div id="message" class="error"><p>%s <a href="%s">%s</a></p></div></div>', $ga_not_configured, admin_url('admin.php?page=constant-analytics'), $configure_settings); return;
		}

		if(!empty($siteName)) {
			echo '<h2 style="margin-left:1%; padding-top:1px;">';
			printf( esc_html__('Analytics for %s', 'ctct'), '<a href="http://'.$siteName.'" target="_blank" title="Visit '.$siteName.'">'.$siteName.'</a>' );
			echo ' <a style="font-size:65%; right:130px; top:0; position:absolute;" href="'.admin_url('admin.php?page=constant-analytics').'">'.esc_html__('Switch Analytics profile &rarr;', 'ctct' ).'</a>
			</h2>';
		}
?>

	<div id="ccStats-datepicker">
		<div id="ccStats-datepicker-pane" style="display:none;">
			<div id="ccStats-datepicker-calendars"></div>
			<input type="submit" id="ccStats-apply-date-range" class="button" value="<?php esc_html_e('Apply', 'ctct'); ?>" />
			<div id="ccStats-current-date-range-desc"></div>
		</div>
		<div id="ccStats-datepicker-popup">
			<div id="ccStats-current-date-range">
				<div id="ccStats-current-start-date"><input style="display:none;" type="text" /><span><?php esc_html_e('Loading', 'ctct'); ?></span></div> -
				<div id="ccStats-current-end-date"><input style="display:none;" type="text" /><span></span></div>
			</div>
		</div>
	</div>

	<div class="ccStats-box" id="ccStats-box-site-traffic">
		<div class="ccStats-box-header"><h3>Site Traffic</h3><div class="ccStats-box-status"></div><div class="ccStats-box-status-text"></div></div>
		<div class="ccStats-box-content">
			<ul id="ccStats-linechart-legend">
				<li class="blog-post" style="display:none;"><?php esc_html_e('blog post', 'ctct'); ?></li>
				<li class="campaign" style="display:none;"><?php esc_html_e('email campaign', 'ctct'); ?></li>
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
					<dt><?php esc_html_e('Visits', 'ctct'); ?></dt>
					<dd>
						<div class="ccStats-stat-spark" id="ccStats-stat-visits-spark"></div>
						<div class="ccStats-stat" id="ccStats-stat-visits"></div>
					</dd>
					<dt><?php esc_html_e('Pageviews', 'ctct'); ?></dt>
					<dd>
						<div class="ccStats-stat-spark" id="ccStats-stat-pageviews-spark"></div>
						<div class="ccStats-stat" id="ccStats-stat-pageviews"></div>
					</dd>
					<dt><?php esc_html_e('Pages/Visit', 'ctct'); ?></dt>
					<dd>
						<div class="ccStats-stat-spark" id="ccStats-stat-pages-per-visit-spark"></div>
						<div class="ccStats-stat" id="ccStats-stat-pages-per-visit"></div>
					</dd>
				</dl>
			</div>
			<div class="ccStats-stats-container">
				<dl class="ccStats-stats-list" style="display:none;">
					<dt><?php esc_html_e('Bounce Rate', 'ctct'); ?></dt>
					<dd>
						<div class="ccStats-stat-spark" id="ccStats-stat-bounce-rate-spark"></div>
						<div class="ccStats-stat" id="ccStats-stat-bounce-rate"></div>
					</dd>
					<dt><?php esc_html_e('Avg. Time on Site', 'ctct'); ?></dt>
					<dd>
						<div class="ccStats-stat-spark" id="ccStats-stat-time-on-site-spark"></div>
						<div class="ccStats-stat" id="ccStats-stat-time-on-site"></div>
					</dd>
					<dt><?php esc_html_e('% New Visits', 'ctct'); ?></dt>
					<dd>
						<div class="ccStats-stat-spark" id="ccStats-stat-new-visits-spark"></div>
						<div class="ccStats-stat" id="ccStats-stat-new-visits"></div>
					</dd>
				</dl>
			</div>
		</div>
	</div>

	<div class="ccStats-box" id="ccStats-box-traffic-by-region">
		<div class="ccStats-box-header"><h3><?php esc_html_e('Traffic By Region', 'ctct'); ?></h3><div class="ccStats-box-status"></div><div class="ccStats-box-status-text"></div></div>
		<div class="ccStats-box-content">
			<div id="ccStats-geo-map"></div>
		</div>
	</div>
	<div class="ccStats-box half" id="ccStats-box-referring-traffic-overview">
		<div class="ccStats-box-header"><h3><?php esc_html_e('Referring Traffic Overview', 'ctct'); ?></h3><div class="ccStats-box-status"></div><div class="ccStats-box-status-text"></div></div>
		<div class="ccStats-box-content">
			<div id="ccStats-referring-traffic-overview-legend"></div>
			<div id="ccStats-referring-traffic-chart"></div>
		</div>
	</div>
	<div class="ccStats-box" id="ccStats-box-top-referrers">
		<div class="ccStats-box-header"><div class="ccStats-breadcrumbs"></div><h3><?php esc_html_e('Top Referrers', 'ctct'); ?></h3><div class="ccStats-box-status"></div><div class="ccStats-box-status-text"></div></div>
		<div class="ccStats-box-content">
			<div class="ccStats-table-container" id="ccStats-top-referrers"></div>
		</div>
	</div>
	<div class="ccStats-box" id="ccStats-box-top-content">
		<div class="ccStats-box-header"><div class="ccStats-breadcrumbs"></div><h3><?php esc_html_e('Top Content', 'ctct'); ?></h3><div class="ccStats-box-status"></div><div class="ccStats-box-status-text"></div></div>
		<div class="ccStats-box-content">
			<div class="ccStats-table-container" id="ccStats-top-content"></div>
		</div>
	</div>
</div>