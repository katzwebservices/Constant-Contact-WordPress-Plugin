<?php
/**
 * All the Events functionality, since it's not in the new API, we segment it out.
 * @package CTCT
 */

use \Ctct\Components\EventSpot\EventSpot;
use \Ctct\Exceptions\CtctException;

define('EVENTSPOT_FILE_PATH', dirname(__FILE__) . '/');
define('EVENTSPOT_FILE_URL', plugin_dir_url(__FILE__));
define('EVENTSPOT_FILE', __FILE__);

add_action('plugins_loaded', array('CTCT_EventSpot', 'setup'));

/**
 * Generate the settings page and manage the settings.
 * @package CTCT
 */
class CTCT_EventSpot extends CTCT_Admin_Page {

	var $settings;

	static $instance;

	/**
	 * Instantiate the class
	 */
	static function setup() {
		new self( true );
	}

	function addIncludes() {

		require_once( EVENTSPOT_FILE_PATH . 'event-functions.php' );
		include_once( EVENTSPOT_FILE_PATH . 'event-embed.php' );

		if(!class_exists('constant_contact_events_widget')) {
			require_once EVENTSPOT_FILE_PATH . 'widget-events.php';
		}

	}

	/**
	 * Set the submenu anchor text
	 * @return string "Events"
	 */
	protected function getNavTitle() {
		return __('Events', 'constant-contact-api');
	}

	function add() {}
	function edit() {}

	static function getInstance() {

		if(empty(self::$instance)) {
			self::$instance = new CTCT_EventSpot;
		}

		return self::$instance;
	}

	function addActions() {

		add_action('wp_dashboard_setup', array(&$this, 'dashboard_setup'));

		// Create events shortcode
		add_shortcode('ccevents', array(&$this, 'events_shortcode_output'));
		add_shortcode('constantcontactevents', array(&$this, 'events_shortcode_output'));
		add_shortcode('eventspot', array(&$this, 'events_shortcode_output'));

		add_action('eventspot_output',  array(&$this, 'events_output'), 10, 3);

		add_filter('cc_event_registrationdate', 'constant_contact_event_date');
		add_filter('cc_event_startdate', 'constant_contact_event_date');
		add_filter('cc_event_enddate', 'constant_contact_event_date');
	}

	function print_scripts() {
		global $pagenow;

		if( !in_array( $pagenow, array('post.php', 'post-new.php', 'index.php') ) ) {
			return;
		}

		wp_print_scripts( 'thickbox' );

	}

	/**
	 * View all events
	 */
	function view() {

		$events = $this->cc->getAll( 'Events' );
		
		if( $events instanceof CtctException ) {
			$this->show_exception( $events );
			return;
		} elseif(empty($events) || !is_array($events)) {
			include( EVENTSPOT_FILE_PATH . '/views/promo.php' );
		} else {

			kws_print_subsub('status', array(
			    array('val' => '', 'text' => 'All'),
			    array('val' => 'ACTIVE', 'text' => 'Active'),
			    array('val' => 'DRAFT', 'text' => 'Draft'),
			    array('val' => 'COMPLETE', 'text' => 'Complete'),
			    array('val' => 'CANCELLED', 'text' => 'Cancelled'),
			));

			/** Populate the $events var with only events matching the filtered status */
			if(isset($_GET['status']) && in_array($_GET['status'], array('ACTIVE', 'COMPLETE', 'DRAFT', 'CANCELLED'))) {
				$ACTIVE = $COMPLETE = $DRAFT = $CANCELLED = array();
				foreach($events as $id => $v) {	${$v->status}[] = $v; }
				$events = ${$_GET['status']};
			}

			$this->make_table($events, __('Events', 'constant-contact-api'));
			?>
			<?php
		}

	}

	/**
	 * View a single event or registrant
	 */
	function single() {

		if(isset($_GET['registrant'])) {
			$this->singleRegistrant();
		} else {
			$this->singleEvent();
		}
	}

	function singleEvent() {
		$id = isset( $_GET['view'] ) ? esc_attr( $_GET['view'] ) : false;

		$Event = $this->cc->getEvent( CTCT_ACCESS_TOKEN, $id );

		$completed = ( 'COMPLETE' === $Event->status );

		include( EVENTSPOT_FILE_PATH . 'views/view.event-view.php' );
	}

	/**
	 * Show the details of a single registrant
	 */
	function singleRegistrant() {

		$event_id = esc_attr( $_GET['view'] );
		$Registrant = $this->cc->getEventRegistrant( CTCT_ACCESS_TOKEN, $event_id, esc_attr( $_GET['registrant'] ) );
		$event = $this->cc->getEvent( CTCT_ACCESS_TOKEN, $event_id );

		include( EVENTSPOT_FILE_PATH . 'views/event-registrant.php' );
	}

	function processForms() { }

	protected function getKey() {
		return "constant-contact-events";
	}

	protected function isNested() {
		if( ! empty( $_GET['registrant'] ) ) {
			return 'registrant';
		}
		return false;
	}

	protected function getTitle($value = '') {

		$title = __('Events', 'constant-contact-api');

		if(empty($value) && $this->isEdit() || $value == 'edit') {
			$title = __("Edit Event", 'constant-contact-api');
		}

		if(empty($value) && $this->isSingle() || $value == 'single') {

			$id = esc_attr( $_GET['view'] );
			$event = $this->cc->getEvent( CTCT_ACCESS_TOKEN, $id );

			if( is_object( $event ) && ! empty( $event->title ) ) {
				/** translators: %s is the campaign name, %d is the list ID */
				$title = sprintf( __( 'Event: "%s"', 'constant-contact-api' ), esc_html( $event->title ) );
			} else {
				/** translators: %d is the campaign ID */
				$title = sprintf( __( 'Event #%s', 'constant-contact-api' ), $id );
			}
		}

		if( $this->isNested() && $value === '' ) {
			$title = __('Event Registrant', 'constant-contact-api');
		}

		return $title;
	}

	/**
	 * Handle the shortcode output
	 * @uses CTCT_EventSpot::events_output()
	 * @param  array  $args    Settings passed by the shortcode
	 * @param  string $content Content inside shortcode. Ignored.
	 * @param  string $tag     Tag used in shortcode.
	 * @return string          Output of shortcode.
	 */
	function events_shortcode_output($args = array(), $content = null, $tag = '') {
		return $this->events_output($args, false);
	}

	/**
	 * Get the URL to the event registration page
	 *
	 * If there's a registration URL set in the event, use it. Otherwise, generate a link using the standard CTCT format.
	 *
	 * @param EventSpot $event
	 * @param bool $registration Link directly to registration?
	 * @param bool $mobile If true, return the mobile registration link. Otherwise, 'desktop' version.
	 *
	 * @return false|string False if $event->id isn't set. URL to the event registration page otherwise.
	 */
	public static function get_event_registration_url( $event, $registration = false, $mobile = false ) {

		$return = false;

		if( is_object( $event ) && isset( $event->id ) ){

			// If set on the event level, use it.
			if( ! empty( $event->registration_url ) ) {
				$return = $event->registration_url;
			} else {

				$event_id = $event->id;

				// Link to the form anchor on the mobile page (no direct link to registration form)
				if( $mobile && $registration ) {
					$event_id .= '#command';
				}

				// Otherwise, generate one
				$format = $mobile ? 'm' : ( $registration ? 'eventReg' : 'event' );

				$return = sprintf( 'http://events.constantcontact.com/register/%s?oeidk=%s', $format, $event_id );
			}
		}

		return $return;
	}

	/**
	 * Get the URL to download the event calendar ICS file
	 *
	 * @since 4.0
	 * 
	 * @param EventSpot $event
	 *
	 * @return false|string False if $event->id isn't set. URL to the event registration page otherwise.
	 */
	public static function get_event_calendar_url( $event ) {

		$return = false;

		if( is_object( $event ) && isset( $event->id ) ){
			$return = sprintf( 'http://events.constantcontact.com/register/addtocalendar?oeidk=%s', $event->id );
		}

		return $return;
	}

	/**
	 * Output events.
	 *
	 * Pass $args as an array with the following settings.
	 * 'id' // REQUIRED if you want to show a single event! The ID is the ID of the event. Looks like a18g4v1b611561nn40b. If empty, show a list of events.
	 * 'limit' // If you want to embed a list of events, limit the list to this number. You can set the limit to 0 and have it show all events (not ideal if you have a bunch of events). Default: 3; Type: number
	 * 'showtitle' // Show the title of the event. Default: true
	 * 'showdescription' // Show the description of the event. Default: true
	 * 'datetime' // Show the date and time of the event. Default: true
	 * 'location' // Show the location of the event. Default: false
	 * 'map' // Show a link to the map. Default: false
	 * 'calendar' // Show a link to add the event to calendar. Default: false
	 * 'style' // Style the event listing with some basic styles? Default: true
	 * 'newwindow' // Open the links in a new window? Default: false
	 * 'onlyactive' // Only show active events? Default: true
	 * 'mobile' // If users are on mobile devices, link to a mobile-friendly registration page? Default: true,
	 * 'no_events_text' // Text to display when there are no events shown.
	 * @param  array   $args    Output settings. See function description.
	 * @param  boolean $echo    Echo or return events output
	 * @return [type]           [description]
	 */
	function events_output($args = array(), $echo = false) {

		$settings = shortcode_atts(array(
			'limit' => 5,
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
			'class' => 'cc_event',
			'directtoregistration' => false,
			'no_events_text' => __('There are no active events.', 'constant-contact-api'),
		), $args);

		foreach($settings as $key => $arg) {
			if(strtolower($arg) == 'false' || empty($arg)) {
				$settings["{$key}"] = false;
			}
		}

		if( empty( $settings['id'] ) ) {

			$events = KWSConstantContact::getInstance()->getAll( 'Events' );

			if( $settings['onlyactive'] ) {
				$events = wp_list_filter( $events, array( 'status' => 'ACTIVE' ) );
			} else {
				$events = wp_list_filter( $events, array( 'status' => 'DRAFT' ), 'NOT' );
			}

			if( ! empty( $limit ) ) {
				$events = array_splice( $events, 0, intval( $settings['limit'] ) );
			}

			$settings['events'] = $events;
			$settings['class'] .= ' multiple_events';

		} else {
			$settings['class'] .= ' single_event';
			$settings['events'] = array( KWSConstantContact::getInstance()->getEvent( CTCT_ACCESS_TOKEN, $settings['id'] ) );
		}

		$this->settings = $settings;

		// Enqueue the style so that it prints in the footer once.
		if( ! empty( $settings['style'] ) ) {
			wp_enqueue_style( 'cc-events', plugin_dir_url( __FILE__ ) . 'css/events.css' );
		}

		$output = kws_ob_include(EVENTSPOT_FILE_PATH.'event-shortcode.php', $this );

		if($echo) {
			echo $output;
		} else {
			return $output;
		}

	}

	/**
	 * Add the WP Admin dashboard widget with upcoming events details
	 */
	function dashboard_setup() {
		wp_add_dashboard_widget( 'constant_contact_events_dashboard', __( 'EventSpot', 'constant-contact-api'), array( $this, 'events_dashboard') );
	}

	/**
	 * Generate a table of events from an events array
	 * @param string $title Title of the header
	 * @param array $events Array of events
	 */
	function dashboard_make_table($title = 'Events', $events = array()) {
		include( EVENTSPOT_FILE_PATH . 'views/event-dashboard-table.php' );
	}

	/**
	 * Generate the content for the Events dashboard widget
	 */
	function events_dashboard() {

		$hidden = get_hidden_meta_boxes( 'dashboard' );
		if( in_array( 'constant_contact_events_dashboard', $hidden ) ) {
			esc_html_e( 'The widget is hidden. Un-hide the widget by going to the top of the page, clicking "Screen Options", then checking the "EventSpot" checkbox. Once you have done that, refresh the page to view this widget.', 'constant-contact-api' );
			return;
		}

		$events = $this->cc->getAll( 'Events' );

		if( !empty( $events ) ) {

			$active = wp_list_filter( $events, array( 'status' => 'ACTIVE' ) );
			$draft = wp_list_filter( $events, array( 'status' => 'DRAFT' ) );

			if ( ! empty( $active ) ) {
				$this->dashboard_make_table( __( 'Active Events', 'constant-contact-api' ), $active );
			}
			if ( ! empty( $draft ) ) {
				$this->dashboard_make_table( __( 'Draft Events', 'constant-contact-api' ), $draft );
			}
			?>
			<p class="textright">
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=constant-contact-events' ) ); ?>"><?php _e( 'View All Events', 'constant-contact-api' ); ?></a>
			</p>
			<?php
		} else {
	?>
		<p><?php _e(sprintf("You don't have any active or draft events. Did you know that Constant Contact offers %sEvent Marketing%s?", '<a href="http://katz.si/4o" title="Learn more about Constant Contact Event Marketing">', '</a>'), 'constant_contact_api', 'constant-contact-api'); ?></p>
	<?php
		}
		return true;
	}

	function make_table($events = array(), $title = '') {
		include( EVENTSPOT_FILE_PATH . 'views/view.events-view.php' );
	}
}