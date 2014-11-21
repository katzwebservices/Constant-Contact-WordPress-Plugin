<?php

	$output = '';

	extract( $CTCT->settings );

	// Enqueue the style so that it prints in the footer once.
	if( !empty( $style ) ) {
		@wp_enqueue_style('cc-events', plugin_dir_url(__FILE__).'css/events.css');
	}

	$class .= !empty($sidebar) ? ' cc_event_sidebar' : ' cc_event_shortcode';

	if(!empty($events)) {

		$i = 0;
		foreach( (array) $events as $partial_event ) {

			// You can set the limit to 0 and have it show all events.
			if( absint( $limit ) > 0 && $i >= absint( $limit ) ) { continue; }

			$startOut = $descriptionOut = $dateOut = $calendarOut = $locationOut = $titleOut = $endOut = '';

			$oddeven = ( isset( $oddeven) && $oddeven === ' odd-event' ) ? ' even-event' : ' odd-event';

			$event = $CTCT->old_api->getEventDetails( $partial_event, DAY_IN_SECONDS );

			if( empty($event) || ( !empty( $onlyactive ) && $event->status !== 'ACTIVE' ) ) { continue; }

			extract((array)$event);

			$link = $event->registrationUrl;

			// If on a mobile phone, use the mobile registration link
			if(!empty($mobile) && function_exists('wp_is_mobile') && wp_is_mobile()) {
				$link = str_replace('/register/eventReg?', '/register/m?', $link);
			}

			$linkTitle = apply_filters('cc_event_linktitle', sprintf( esc_attr__('View event details for "%s"', 'ctct'), $event->title));
			if(!empty($linkTitle)) { $linkTitle = ' title="'.esc_html($linkTitle).'"'; }

			$class = apply_filters('cc_event_class', $class);
			$target = $newwindow ? apply_filters('cc_event_new_window', ' target="_blank"') : '';
			$startOut = '
			<dl class="'.$class.$oddeven.'">';

				if(!empty($showtitle) && !empty($event->title)) {
					$titleOut = '
				<dt class="cc_event_title"><a'.$target.' href="'.$link.'"'.$linkTitle.'>'.esc_html($event->title).'</a></dt>';
				}
				if(!empty($showdescription) && !empty($event->description)) {
				$descriptionOut = '
				<dd class="cc_event_description">'.wpautop(esc_html($event->description)).'</dd>';
				}
				if(!empty($datetime)) {
				$dateOut = '
				<dt class="cc_event_startdate_dt">'.apply_filters('cc_event_startdate_dt', esc_html__('Start: ', 'ctct')).'</dt>
					<dd class="cc_event_startdate_dd">'.apply_filters('cc_event_date', apply_filters('cc_event_startdate', $event->startDate)).'</dd>
				<dt class="cc_event_enddate_dt">'.apply_filters('cc_event_enddate_dt', esc_html__('End: ', 'ctct')).'</dt>
					<dd class="cc_event_enddate_dd">'.apply_filters('cc_event_date', apply_filters('cc_event_enddate', $event->endDate)).'</dd>
					';
				}
				if(!empty($calendar)) {

					$link = str_replace('/register/event?', '/register/addtocalendar?', esc_url( $event->registrationUrl) );
					$linkTitle = apply_filters('cc_event_linktitle', sprintf( esc_html__('Add "%s" to your calendar', 'ctct'), $event->title));
					if(!empty($linkTitle)) { $linkTitle = ' title="'.esc_attr($linkTitle).'"'; }
					$calendarOut = '
				<dd class="cc_event_calendar"><a'.$target.' href="'.$link.'"'.$linkTitle.'>'.esc_html__('Add to Calendar', 'ctct').'</a></dd>
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

							/**
							 * Modify the map link format. Passes the eventLocation object and the address query string.
							 */
							$locationText .= "<br/>".apply_filters('cc_event_map_link', "<a href='http://maps.google.com/maps?q=$address_qs'".$target." class='cc_event_map_link'>".esc_html__('Map Location', 'ctct')."</a>", $event->eventLocation, $address_qs);
						}

					$locationOut = '
					<dt class="cc_event_location cc_event_location_dt">'.apply_filters('cc_event_location_dt', esc_html__('Location: ', 'ctct')).'</dt>
						<dd class="cc_event_location_dd cc_event_location">'.apply_filters('cc_event_location', $locationText).'</dd>';
					}
				}

			$endOut = '
			</dl>';

			$data_for_filter = array(
				'start' => $startOut,
				'title' => $titleOut,
				'description' => $descriptionOut,
				'date' => $dateOut,
				'calendar' => $calendarOut,
				'location' => $locationOut,
				'end' => $endOut
			);

			$output .= apply_filters('cc_event_output_single', $startOut.$titleOut.$descriptionOut.$dateOut.$calendarOut.$locationOut.$endOut, $data_for_filter );

			$i++;
		}

	}


	// If there is no output, that means no events were found.
	if( empty( $i ) && empty( $output ) ) {

		$output = apply_filters('cc_event_no_events_text', wpautop( $no_events_text ) );

	}

	$output = apply_filters('cc_event_output', $output);

	echo $output;