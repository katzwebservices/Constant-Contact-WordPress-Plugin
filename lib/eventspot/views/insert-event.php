<h2><?php _e("Insert Event(s)", 'constant-contact-api'); ?></h2>
<span>
						<?php _e("Choose to embed multiple events or a single event below. The event(s) will be displayed on your post or page.", 'constant-contact-api'); ?>
					</span>
</div>
<div style="padding:15px 15px 0 15px;">
	<select id="add_event_id">
		<option value="">  <?php _e("Show Multiple Events", 'constant-contact-api'); ?>  </option>
		<?php

		/**
		 * @var array $events
		 * @var \Ctct\Components\EventSpot\EventSpot $event
		 */
		foreach( $events as $event ){

			// Make sure it's an Event object
			if( !is_object( $event ) || !isset( $event->title ) ) {
				continue;
			}
			?>
			<option value="<?php echo esc_attr( $event->id ); ?>"><?php printf('%s (%s)', esc_html($event->title), apply_filters('cc_event_date', apply_filters('cc_event_startdate', $event->start_date)));  ?></option>
		<?php
		}
		?>
	</select>
</div>

<div style="padding:15px 15px 0 15px;" id="add_events_limit_container">
	<p style="margin:0 0 .5em 0"><strong><?php _e('<span title="Number">#</span> of Events Shown', 'consatant-contact-api', 'constant-contact-api'); ?></strong></p>
	<p class="description"><?php _e('The number of events to show at once.', 'constant-contact-api'); ?></p>
	<label>
		<select class="select" id="add_events_limit">
			<option value="0"><?php esc_attr_e('All', 'constant-contact-api'); ?></option>
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
	<p style="margin:0 0 .5em 0"><strong><?php _e('Display Options', 'consatant-contact-api', 'constant-contact-api'); ?></strong></p>
	<ul>
		<li>
			<label for="eventspot_display_onlyactive" class="block checkbox" style="padding-right:.7em;">
				<input type="checkbox" id="eventspot_display_onlyactive" checked='checked' />
				<?php _e('Only show active events', 'constant-contact-api'); ?>
			</label>
		</li>
		<li>
			<label for="eventspot_display_description" style="padding-right:.7em;" class="block checkbox">
				<input type="checkbox" id="eventspot_display_description" checked='checked' />
				<?php _e("Display event description", 'constant-contact-api'); ?>
			</label>
		</li>
		<li>
			<label for="eventspot_display_datetime" style="padding-right:.7em;" class="block checkbox">
				<input type="checkbox" id="eventspot_display_datetime" checked="checked" />
				<?php _e("Display event date &amp; time", 'constant-contact-api'); ?>
			</label>
		</li>
		<li>
			<label for="eventspot_display_location" style="padding-right:.7em;" class="block checkbox">
				<input type="checkbox" id="eventspot_display_location" />
				<?php _e("Display event location", 'constant-contact-api'); ?>
			</label>
		</li>
		<li>
			<label for="eventspot_display_map" style="padding-right:.7em;" class="block checkbox">
				<input type="checkbox" id="eventspot_display_map" />
				<?php _e("Display map link for location (if location is shown)", 'constant-contact-api'); ?>
			</label>
		</li>
		<li>
			<label for="eventspot_display_calendar" style="padding-right:.7em;" class="block checkbox">
				<input type="checkbox" id="eventspot_display_calendar" />
				<?php _e("Display \"Add to Calendar\" link", 'constant-contact-api'); ?>
			</label>
		</li>
		<li>
			<label for="eventspot_display_newwindow" style="padding-right:.7em;" class="block checkbox">
				<input type="checkbox" id="eventspot_display_newwindow" />
				<?php _e("Open event links in a new window", 'constant-contact-api'); ?>
			</label>
		</li>
		<li>
			<label for="eventspot_display_usestyles" style="padding-right:.7em;" class="block checkbox">
				<input type="checkbox" id="eventspot_display_usestyles" checked="checked" />
				<?php _e("Use plugin styles. Disable if you want to use your own styles (CSS)", 'constant-contact-api'); ?>
			</label>
		</li>
		<li>
			<label for="eventspot_display_mobile" style="padding-right:.7em;" class="block checkbox">
				<input type="checkbox" id="eventspot_display_mobile" checked="checked" />
				<?php _e("If users are on mobile devices, link to a mobile-friendly registration page?", 'constant-contact-api'); ?>
			</label>
		</li>
	</ul>
</div>
<div style="padding:15px;" class="submit">
	<input type="button" class="button-primary" id="insert-eventspot" value="<?php _e('Insert Event', 'constant-contact-api'); ?>" />
	<a class="button alignright button-secondary" style="color:#ccc;" href="#" onclick="tb_remove(); return false;"><?php _e("Cancel", "constant-contact-api		", 'constant-contact-api'); ?></a>
</div>