<script>

	jQuery(document).ready(function($) {
		$( 'body' ).on( 'change', '#add_event_id', function () {
			$parent = $( '#add_events_limit_container' );
			if ( $( this ).val() === '' ) {
				$parent.show();
			} else {
				$parent.hide();
			}
		} ).trigger( 'change' );

		$( '#insert-eventspot' ).on( 'click', function () {

			var $add_event_id = $( "#add_event_id" );

			// Generate the shortcode code
			var settings = [];

			if ( $add_event_id.val() !== '' ) {
				settings[ 0 ] = 'id="' + $add_event_id.val() + '"';
			} else {
				var limit = $( "#add_events_limit" ).val();
				settings[ 0 ] = ( limit === '5' ? '' : 'limit="' + limit + '"');
			}

			settings[ 1 ] = ($( "#eventspot_display_onlyactive" ).is( ":checked" ) ? '' : 'onlyactive="0"');
			settings[ 2 ] = ($( "#eventspot_display_description" ).is( ":checked" ) ? '' : 'showdescription="0"');
			settings[ 3 ] = ($( "#eventspot_display_datetime" ).is( ":checked" ) ? '' : 'datetime="0"');
			settings[ 4 ] = ($( "#eventspot_display_location" ).is( ":checked" ) ? 'location="1"' : '');
			settings[ 5 ] = ($( "#eventspot_display_map" ).is( ":checked" ) ? 'map="1"' : '');
			settings[ 6 ] = ($( "#eventspot_display_calendar" ).is( ":checked" ) ? 'calendar="1"' : '');
			settings[ 7 ] = ($( "#eventspot_display_newwindow" ).is( ":checked" ) ? 'newwindow="1"' : '');
			settings[ 8 ] = ($( "#eventspot_display_usestyles" ).is( ":checked" ) ? '' : 'style="0"');
			settings[ 9 ] = ($( "#eventspot_display_mobile" ).is( ":checked" ) ? '' : 'mobile="0"');

			window.send_to_editor( '[eventspot ' + settings.join(' ').replace( /\s{1,}/g, ' ' ) + '/]' );

			// Reset the Event ID setting
			$add_event_id.val('');
		} );
	});

</script>
<div id="select_eventspot_event" style="display:none;">
	<div class="wrap">
		<div style="padding:15px 15px 0 15px;">

			<a href="http://katz.si/4o" rel="external"><img src="<?php echo plugins_url('images/eventspot-logo.png', EVENTSPOT_FILE); ?>" alt="EventSpot from Constant Contact" /></a>

		<?php

			$has_events = KWSConstantContact::getInstance()->hasEvents();

			if( ! $has_events ) {
				include( EVENTSPOT_FILE_PATH . '/views/promo.php');
			} else {
				$events = KWSConstantContact::getInstance()->getAll('Events');
				include( EVENTSPOT_FILE_PATH . '/views/insert-event.php' );
			} // End empty events
		?>
		</div>
	</div>
</div>