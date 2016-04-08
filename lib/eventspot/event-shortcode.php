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
				// If the user is an admin and it's a draft event, allow them to see it.
				if( 'DRAFT' === $event->status && current_user_can('manage_options') ) {
					$output .= wpautop( '<strong>'.esc_html__( 'Note: This is a draft event and is only visible because you are logged-in as an administrator.', 'constant-contact-api' ).'</strong>' );
				} else {
					if( !empty( $CTCT->settings['id'] ) ) {
						$output .= wpautop( sprintf( esc_html__( 'The event "%s" is no longer active.', 'constant-contact-api' ), esc_html( $event->title ) ) );
					}
					continue;
				}
			}

			$registration = ! empty( $CTCT->settings['directtoregistration'] );
			$is_mobile = ( ! empty($mobile) && function_exists('wp_is_mobile') && wp_is_mobile() );
			
			$link = CTCT_EventSpot::get_event_registration_url( $event, $registration, $is_mobile );

			$linkTitle = apply_filters('cc_event_linktitle', sprintf( esc_attr__('View event details for "%s"', 'constant-contact-api'), $event->title));
			if(!empty($linkTitle)) { $linkTitle = ' title="'.esc_html($linkTitle).'"'; }

			$class = apply_filters('cc_event_class', $class);
			$oddeven = ( isset( $oddeven) && $oddeven === ' odd-event' ) ? ' even-event' : ' odd-event';
			$target = $CTCT->settings['newwindow'] ? apply_filters('cc_event_new_window', ' target="_blank"') : '';
			$startOut = '
			<dl class="'.esc_attr( $class.$oddeven ).'">';

				if( !empty( $CTCT->settings['showtitle'] ) && !empty($event->title)) {
					$titleOut = '<dt class="cc_event_title">';
					if( $link ) {
						$titleOut .= sprintf( '<a%s href="%s"%s>%s</a>', $target, esc_url( $link ), $linkTitle, esc_html( $event->title ) );
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
				<dt class="cc_event_startdate_dt">'.apply_filters('cc_event_startdate_dt', esc_html__('Start: ', 'constant-contact-api')).'</dt>
					<dd class="cc_event_startdate_dd">'.apply_filters('cc_event_date', apply_filters('cc_event_startdate', $event->start_date)).'</dd>
				<dt class="cc_event_enddate_dt">'.apply_filters('cc_event_enddate_dt', esc_html__('End: ', 'constant-contact-api')).'</dt>
					<dd class="cc_event_enddate_dd">'.apply_filters('cc_event_date', apply_filters('cc_event_enddate', $event->end_date)).'</dd>
					';
				}
				if( ! empty($CTCT->settings['calendar']) && $calendar_link = CTCT_EventSpot::get_event_calendar_url( $event ) ) {
					$linkTitle = apply_filters('cc_event_linktitle', sprintf( esc_html__('Add "%s" to your calendar', 'constant-contact-api'), $event->title));
					if(!empty($linkTitle)) { $linkTitle = ' title="'.esc_attr($linkTitle).'"'; }
					$calendarOut = sprintf( '<dd class="cc_event_calendar"><a%s href="%s" %s>%s</a></dd>', $target, esc_url( $calendar_link ), $linkTitle, esc_html__('Add to Calendar', 'constant-contact-api') );
				}
				if(!empty($CTCT->settings['location'])) {
					if(!empty($event->address)) {

						$locationText = constant_contact_create_location($event->address, $event->location, false );

						if(!empty($CTCT->settings['map'])) {

							$address_url = constant_contact_get_map_url_from_address( $event->address );

							/**
							 * Modify the map link format. Passes the event object and the address query string.
							 */
							$locationText .= "\n".apply_filters('cc_event_map_link', "<a href='$address_url'".$target." class='cc_event_map_link'>".esc_html__('Map Location', 'constant-contact-api')."</a>", $event );
						}

						$locationText = wpautop( $locationText );

						$locationOut = '
					<dt class="cc_event_location cc_event_location_dt">'.apply_filters('cc_event_location_dt', esc_html__('Location: ', 'constant-contact-api')).'</dt>
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