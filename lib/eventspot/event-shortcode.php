<?php
/**
 * @global CTCT_EventSpot $CTCT
 */
	$output = '';

	$events = $CTCT->settings['events'];
	$class = $CTCT->settings['class'];
	$class .= !empty($CTCT->settings['sidebar']) ? ' cc_event_sidebar' : ' cc_event_shortcode';

	if(!empty($events)) {

		$i = 0;
		/** @var \Ctct\Components\EventSpot\EventSpot $event */
		foreach( (array) $events as $event ) {

			// You can set the limit to 0 and have it show all events.
			if( absint( $CTCT->settings['limit'] ) > 0 && $i >= absint( $CTCT->settings['limit'] ) ) { continue; }

			$startOut = $descriptionOut = $dateOut = $calendarOut = $locationOut = $titleOut = $endOut = '';


			if( empty($event) || ( !empty( $CTCT->settings['onlyactive'] ) && $event->status !== 'ACTIVE' ) ) {
				if( !empty( $CTCT->settings['id'] ) ) {
					$output .= wpautop( sprintf( esc_html__('The event "%s" is no longer active.', 'ctct'), esc_html( $event->title ) ) );
				}
				continue;
			}

			$link = $event->registration_url;

			// If on a mobile phone, use the mobile registration link
			if(!empty($mobile) && function_exists('wp_is_mobile') && wp_is_mobile()) {
				$link = str_replace('/register/event_reg?', '/register/m?', $link);
			}

			$linkTitle = apply_filters('cc_event_linktitle', sprintf( esc_attr__('View event details for "%s"', 'ctct'), $event->title));
			if(!empty($linkTitle)) { $linkTitle = ' title="'.esc_html($linkTitle).'"'; }

			$class = apply_filters('cc_event_class', $class);
			$oddeven = ( isset( $oddeven) && $oddeven === ' odd-event' ) ? ' even-event' : ' odd-event';
			$target = $CTCT->settings['newwindow'] ? apply_filters('cc_event_new_window', ' target="_blank"') : '';
			$startOut = '
			<dl class="'.esc_attr( $class.$oddeven ).'">';

				if(!empty($showtitle) && !empty($event->title)) {
					$titleOut = '<dt class="cc_event_title">';
					if( $link ) {
						$titleOut .= '<a' . $target . ' href="' . esc_url( $link ) . '"' . $linkTitle . '>' . esc_html( $event->title ) . '</a>';
					} else {
						$titleOut .= esc_html($event->title);
					}
					$titleOut .= '</dt>';
				}
				if(!empty($CTCT->settings['showdescription']) && !empty($event->description)) {
				$descriptionOut = '
				<dd class="cc_event_description">'.wpautop(esc_html($event->description)).'</dd>';
				}
				if(!empty($CTCT->settings['datetime'])) {
				$dateOut = '
				<dt class="cc_event_startdate_dt">'.apply_filters('cc_event_startdate_dt', esc_html__('Start: ', 'ctct')).'</dt>
					<dd class="cc_event_startdate_dd">'.apply_filters('cc_event_date', apply_filters('cc_event_startdate', $event->start_date)).'</dd>
				<dt class="cc_event_enddate_dt">'.apply_filters('cc_event_enddate_dt', esc_html__('End: ', 'ctct')).'</dt>
					<dd class="cc_event_enddate_dd">'.apply_filters('cc_event_date', apply_filters('cc_event_enddate', $event->end_date)).'</dd>
					';
				}
				if(!empty($CTCT->settings['calendar'])) {

					$link = str_replace('/register/event?', '/register/addtocalendar?', esc_url( $event->registration_url) );
					$linkTitle = apply_filters('cc_event_linktitle', sprintf( esc_html__('Add "%s" to your calendar', 'ctct'), $event->title));
					if(!empty($linkTitle)) { $linkTitle = ' title="'.esc_attr($linkTitle).'"'; }
					$calendarOut = '
				<dd class="cc_event_calendar"><a'.$target.' href="'.$link.'"'.$linkTitle.'>'.esc_html__('Add to Calendar', 'ctct').'</a></dd>
					';
				}
				if(!empty($CTCT->settings['location'])) {
					$locationText = constant_contact_create_location($event->address);
					if(!empty($locationText)) {
						if(!empty($CTCT->settings['map'])) {
							if(isset($event->location)) {
								$new_location = '('.$event->location.')';
							}

							$locationformap = $event->location;
							$address_qs = str_replace("<br />", ", ", $locationformap.'<br />'.$new_location); //replacing <br/> with spaces
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
				'end' => $endOut,
			);

			$output .= apply_filters('cc_event_output_single', implode( '', $data_for_filter ), $data_for_filter );

			$i++;
		}

	}


	// If there is no output, that means no events were found.
	if( empty( $i ) && empty( $output ) ) {

		$no_events_text = $CTCT->settings['no_events_text'];
		$output = apply_filters('cc_event_no_events_text', wpautop( $no_events_text ) );

	}

	$output = apply_filters('cc_event_output', $output);

	echo $output;