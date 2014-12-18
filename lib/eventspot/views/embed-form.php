<script>

	jQuery(document).ready(function($) {
		$('body').on('change', '#add_event_id', function() {
			$parent = $('#add_events_limit_container');
			if( $(this).val() === '' ) {
				$parent.show();
			} else {
				$parent.hide();
			}
		}).trigger('change');
		$('#insert-eventspot').on('click', function() {

			var event_id = $("#add_event_id").val();

			// Generate the shortcode code
			var settings = [];

			if( event_id !== '' ) {
				settings[0] = 'id="'+event_id+'"';
			} else {
				var limit = $("#add_events_limit").val();
				settings[0] = ( limit === '3' ? '' : 'limit="'+limit+'"');
			}

			settings[1] = ($("#eventspot_display_onlyactive").is(":checked") ? '' : 'onlyactive=0');
			settings[2] = ($("#eventspot_display_description").is(":checked") ? '' : 'showdescription=0');
			settings[3] = ($("#eventspot_display_datetime").is(":checked") ? '' : 'datetime=0');
			settings[4] = ($("#eventspot_display_location").is(":checked") ? 'location=1' : '');
			settings[5] = ($("#eventspot_display_map").is(":checked") ? 'map=1' : '');
			settings[6] = ($("#eventspot_display_calendar").is(":checked") ? 'calendar=1' : '');
			settings[7] = ($("#eventspot_display_newwindow").is(":checked") ? 'newwindow=1' : '');
			settings[8] = ($("#eventspot_display_usestyles").is(":checked") ? '' : 'style=0');
			settings[9] = ($("#eventspot_display_mobile").is(":checked") ? '' : 'mobile=0');

			window.send_to_editor('[eventspot ' + settings.join(' ').replace(/\s{2,}/g, ' ') + '/]');

			$("#add_event_id").val('');

		});
	});

</script>
<div id="select_eventspot_event" style="display:none;">
	<div class="wrap">
		<div>
			<div style="padding:15px 15px 0 15px;">
				<a href="http://katz.si/4o" rel="external"><img src="<?php echo plugins_url('images/eventspot-logo.png', EVENTSPOT_FILE); ?>" alt="EventSpot from Constant Contact" /></a>
			<?php

			try {

				$events = constant_contact_old_api_get_all('Events', CTCT_EventSpot::getInstance()->old_api );

				if(empty($events)) {
					include(EVENTSPOT_FILE_PATH.'/views/promo.php');
				} else {
				?>
					<h2><?php _e("Insert Event(s)", 'ctct'); ?></h2>
					<span>
						<?php _e("Choose to embed multiple events or a single event below. The event(s) will be displayed on your post or page.", 'ctct'); ?>
					</span>
				</div>
				<div style="padding:15px 15px 0 15px;">
					<select id="add_event_id">
						<option value="">  <?php _e("Show Multiple Events", 'ctct'); ?>  </option>
						<?php

							foreach($events['events'] as $event){
								?>
								<option value="<?php echo constant_contact_get_id_from_object($event); ?>"><?php printf('%s (%s)', esc_html($event->title), apply_filters('cc_event_date', apply_filters('cc_event_startdate', $event->startDate)));  ?></option>
								<?php
							}
						?>
					</select>
				</div>

				<div style="padding:15px 15px 0 15px;" id="add_events_limit_container">
					<p style="margin:0 0 .5em 0"><strong><?php _e('<span title="Number">#</span> of Events Shown', 'consatant-contact-api'); ?></strong></p>
					<p class="description"><?php _e('The number of events to show at once.', 'ctct'); ?></p>
					<label>
						<select class="select" id="add_events_limit">
							<option value="0"><?php esc_attr_e('All', 'ctct'); ?></option>
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3" selected="selected">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
							<option value="6">6</option>
							<option value="7">7</option>
							<option value="8">8</option>
							<option value="9">9</option>
							<option value="10">10</option>
						</select>
					</label>
				</div>

				<div style="padding:15px 15px 0 15px;">
					<p style="margin:0 0 .5em 0"><strong><?php _e('Display Options', 'consatant-contact-api'); ?></strong></p>
					<ul>
						<li>
							<label for="eventspot_display_onlyactive" class="block checkbox" style="padding-right:.7em;">
								<input type="checkbox" id="eventspot_display_onlyactive" checked='checked' />
								<?php _e('Only show active events', 'ctct'); ?>
							</label>
						</li>
						<li>
							<label for="eventspot_display_description" style="padding-right:.7em;" class="block checkbox">
								<input type="checkbox" id="eventspot_display_description" checked='checked' />
								<?php _e("Display event description", 'ctct'); ?>
							</label>
						</li>
						<li>
							<label for="eventspot_display_datetime" style="padding-right:.7em;" class="block checkbox">
								<input type="checkbox" id="eventspot_display_datetime" checked="checked" />
								<?php _e("Display event date &amp; time", 'ctct'); ?>
							</label>
						</li>
						<li>
							<label for="eventspot_display_location" style="padding-right:.7em;" class="block checkbox">
								<input type="checkbox" id="eventspot_display_location" />
								<?php _e("Display event location", 'ctct'); ?>
							</label>
						</li>
						<li>
							<label for="eventspot_display_map" style="padding-right:.7em;" class="block checkbox">
								<input type="checkbox" id="eventspot_display_map" />
								<?php _e("Display map link for location (if location is shown)", 'ctct'); ?>
							</label>
						</li>
						<li>
							<label for="eventspot_display_calendar" style="padding-right:.7em;" class="block checkbox">
								<input type="checkbox" id="eventspot_display_calendar" />
								<?php _e("Display \"Add to Calendar\" link", 'ctct'); ?>
							</label>
						</li>
						<li>
							<label for="eventspot_display_newwindow" style="padding-right:.7em;" class="block checkbox">
								<input type="checkbox" id="eventspot_display_newwindow" />
								<?php _e("Open event links in a new window", 'ctct'); ?>
							</label>
						</li>
						<li>
							<label for="eventspot_display_usestyles" style="padding-right:.7em;" class="block checkbox">
								<input type="checkbox" id="eventspot_display_usestyles" checked="checked" />
								<?php _e("Use plugin styles. Disable if you want to use your own styles (CSS)", 'ctct'); ?>
							</label>
						</li>
						<li>
							<label for="eventspot_display_mobile" style="padding-right:.7em;" class="block checkbox">
								<input type="checkbox" id="eventspot_display_mobile" checked="checked" />
								<?php _e("If users are on mobile devices, link to a mobile-friendly registration page?", 'ctct'); ?>
							</label>
						</li>
					</ul>
				</div>
				<div style="padding:15px;" class="submit">
					<input type="button" class="button-primary" id="insert-eventspot" value="<?php _e('Insert Event', 'ctct'); ?>" />
					<a class="button alignright button-secondary" style="color:#ccc;" href="#" onclick="tb_remove(); return false;"><?php _e("Cancel", "constant-contact-api		"); ?></a>
				</div>
			</div>
		<?php } // End empty events
		} catch(Exception $e) {
			// TODO: log this
			echo sprintf(__('<p>There was a problem fetching the Events:</p><pre>%s</pre>', 'ctct'), $e->getMessage());
		} // End events throwing exception
	?>
	</div>
</div>