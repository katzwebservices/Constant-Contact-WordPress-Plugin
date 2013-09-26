<?php
	$class = 'cc_event';

	$output = $oddeven = '';

	$settings = shortcode_atts(array(
		'limit' => 3,
		'showtitle' => true,
		'showdescription' => true,
		'datetime' => true,
		'location' => false,
		'calendar' => false,
		'style' => true,
		'id' => false,
		'newwindow' => false,
		'map' => false,
		'onlyactive' => true,
		'sidebar' => false,
		'mobile' => true,
	), $args);

	foreach($settings as $key => $arg) {
		if(strtolower($arg) == 'false' || empty($arg)) {
			$settings["{$key}"] = false;
		}
	}

	extract( $settings );

	// Enqueue the style so that it prints in the footer once.
	if(!empty($style)) {
		@wp_enqueue_style('cc-events', plugin_dir_url(__FILE__).'css/events.css');
	}

	if(empty($id)) {
		$events = constant_contact_old_api_get_all('Events', $this->old_api);
		$class .= ' multiple_events';
	} else {
		$class .= ' single_event';
		$events = array(CTCT_EventSpot::getInstance()->old_api->getEventDetails(new Event(array('link' => sprintf('/ws/customers/%s/events/%s', CTCT_USERNAME, $id)))));
	}

	$class .= ($sidebar) ? ' cc_event_sidebar' : ' cc_event_shortcode';

		if(!empty($events)) {

			$i = 0;
			foreach($events as $event) {

				if($i >= $limit) { continue; }

				$startOut = $descriptionOut = $dateOut = $calendarOut = $locationOut = $titleOut = $endOut = '';

				$oddeven = ($oddeven == ' even-event') ? ' odd-event' : ' even-event';

				$event = $this->old_api->getEventDetails($event, 60*60*24);

				if(empty($event) || (empty($id) && $event->status !== 'ACTIVE')) { continue; }

				extract((array)$event);

				$link = $event->registrationUrl;

				if(!empty($mobile) && function_exists('wp_is_mobile') && wp_is_mobile()) {
					$link = str_replace('/register/eventReg?', '/register/m?', $link);
				}

				$linkTitle = apply_filters('cc_event_linktitle', sprintf( __('View event details for "%s"','constant-contact-api'), $event->title));
				if(!empty($linkTitle)) { $linkTitle = ' title="'.esc_html($linkTitle).'"'; }

				$class = apply_filters('cc_event_class', $class);
				$target = $newwindow ? apply_filters('cc_event_new_window', ' target="_blank"') : '';
				$startOut = '
				<dl class="'.$class.$oddeven.'">';

					if(!empty($showtitle) && !empty($event->title)) {
						$titleOut = '
					<dt class="cc_event_title"><a'.$target.' href="'.$link.'"'.$linkTitle.'>'.$event->title.'</a></dt>';
					}
					if(!empty($showdescription) && !empty($event->description)) {
					$descriptionOut = '
					<dd class="cc_event_description">'.wpautop($event->description).'</dd>';
					}
					if(!empty($datetime)) {
					$dateOut = '
					<dt class="cc_event_startdate_dt">'.apply_filters('cc_event_startdate_dt', __('Start: ','constant-contact-api')).'</dt>
						<dd class="cc_event_startdate_dd">'.apply_filters('cc_event_date', apply_filters('cc_event_startdate', $event->startDate)).'</dd>
					<dt class="cc_event_enddate_dt">'.apply_filters('cc_event_enddate_dt', __('End: ','constant-contact-api')).'</dt>
						<dd class="cc_event_enddate_dd">'.apply_filters('cc_event_date', apply_filters('cc_event_enddate', $event->endDate)).'</dd>
						';
					}
					if(!empty($calendar)) {

						$link = str_replace('/register/event?', '/register/addtocalendar?', $event->registrationUrl);
						$linkTitle = apply_filters('cc_event_linktitle', sprintf( __('Add "%s" to your calendar','constant-contact-api'), $event->title));
						if(!empty($linkTitle)) { $linkTitle = ' title="'.esc_html($linkTitle).'"'; }
						$calendarOut = '
					<dd class="cc_event_calendar"><a'.$target.' href="'.$link.'"'.$linkTitle.'>'.__('Add to Calendar','constant-contact-api').'</a></dd>
						';
					}
					if(!empty($location)) {
						$locationText = constant_contact_create_location($event->eventLocation);
						if(!empty($locationText)) {
							if($map) {
								if(isset($event->eventLocation->location)) {
									$event->eventLocation->newLocation = '('.$event->eventLocation->location.')';
									unset($event->eventLocation->location);
								}

								$locationformap = constant_contact_create_location($event->eventLocation);
								$address_qs = str_replace("<br />", ", ", $locationformap.'<br />'.$event->eventLocation->newLocation); //replacing <br/> with spaces
								$address_qs = urlencode($address_qs);
								$locationText .= "<br/>".apply_filters('cc_event_map_link', "<a href='http://maps.google.com/maps?q=$address_qs'".$target." class='cc_event_map_link'>".__('Map Location','constant-contact-api')."</a>", $event->eventLocation, $address_qs);
							}

						$locationOut = '
						<dt class="cc_event_location cc_event_location_dt">'.apply_filters('cc_event_location_dt', __('Location: ','constant-contact-api')).'</dt>
							<dd class="cc_event_location_dd cc_event_location">'.apply_filters('cc_event_location', $locationText).'</dd>';
						}
					}

				$endOut = '
				</dl>';

				$output .= apply_filters('cc_event_output_single', $startOut.$titleOut.$descriptionOut.$dateOut.$calendarOut.$locationOut.$endOut, array('start'=>$startOut,'title'=>$titleOut,'description'=>$descriptionOut,'date'=>$dateOut,'calendar'=>$calendarOut,'location' => $locationOut, 'end'=>$endOut));

				$i++;
			}

		} else {
			$output = apply_filters('cc_event_no_events_text', '<p>'.__('There are currently no events.','constant-contact-api').'</p>');
		}

	$output = apply_filters('cc_event_output', $output);

	echo $output;