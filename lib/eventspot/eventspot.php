<?php
/**
 * All the Events functionality, since it's not in the new API, we segment it out.
 * @package CTCT
 */

define('EVENTSPOT_FILE_PATH', dirname(__FILE__) . '/');
define('EVENTSPOT_FILE_URL', plugin_dir_url(__FILE__));
define('EVENTSPOT_FILE', __FILE__);

add_action('plugins_loaded', array('CTCT_EventSpot', 'setup'));

/**
 * Generate the settings page and manage the settings.
 * @package CTCT
 */
class CTCT_EventSpot extends CTCT_Admin_Page {

	var $old_api;

	var $settings;

	static $instance;

	function __construct() {
		parent::__construct( true );
	}

	/**
	 * Set the submenu anchor text
	 * @return string "Events"
	 */
	protected function getNavTitle() {
		return __('Events', 'ctct');
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

		require_once(EVENTSPOT_FILE_PATH.'functions.php');
		require_once(EVENTSPOT_FILE_PATH.'ctct_php_library/ConstantContact.php');
		require_once(EVENTSPOT_FILE_PATH.'class.kwsv1api.php');
		include_once(EVENTSPOT_FILE_PATH.'embed.php');

		$this->old_api = new KWS_V1API('oauth2', CTCT_APIKEY, CTCT_USERNAME, CTCT_ACCESS_TOKEN);

		if(!class_exists('constant_contact_events_widget')) {
			require_once EVENTSPOT_FILE_PATH . 'widget-events.php';
		}


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

	/**
	 * View all events
	 */
	function view() {


		$events = constant_contact_old_api_get_all('Events', $this->old_api);

		if(empty($events) || !is_array($events)) {
			constant_contact_get_signup_message('events');
			constant_contact_admin_refresh();
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

			$this->make_table($events, __('Events', 'ctct'));
			?>
			<p class="submit"><a href="<?php echo add_query_arg('refresh', 'events'); ?>" class="button-secondary alignright" title="<?php echo sprintf( esc_attr__('Event registrants data is stored for %s hours. Refresh data now.', 'ctct'), round(KWS_V1API::$event_cache_age / 3600)); ?>"><?php esc_html_e('Refresh Events', 'ctct'); ?></a></p>
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
		$v = $this->old_api->getEventDetails(new Event(array('link' => sprintf('/ws/customers/%s/events/%s', CTCT_USERNAME, $_GET['view']))));
		$completed = strtotime($v->endDate) <= time();

		include(EVENTSPOT_FILE_PATH.'views/event.php');
	}

	/**
	 * Show the details of a single registrant
	 */
	function singleRegistrant() {

		$v = $this->old_api->getRegistrantDetails(new Registrant(array('link' => sprintf('/ws/customers/%s/events/%s/registrants/%s', CTCT_USERNAME, $_GET['view'], $_GET['registrant']))));
		$event = $this->old_api->getEventDetails(new Event(array('link' => sprintf('/ws/customers/%s/events/%s', CTCT_USERNAME, $_GET['view']))));

		include(EVENTSPOT_FILE_PATH.'views/registrant.php');
	}

	function processForms() { }

	protected function getKey() {
		return "constant-contact-events";
	}

	protected function getTitle($value = '') {
		if(empty($value) && $this->isEdit() || $value == 'edit')
			return "Edit Event";
		if(empty($value) && $this->isSingle() || $value == 'single')
			return 'Event';

		return 'Events';
	}

	/**
	 * Instantiate the class
	 */
	static function setup() {
		$CTCT_EventSpot = new CTCT_EventSpot;
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
			'class' => 'cc_event',
			'no_events_text' => __('There are no active events.', 'ctct'),
		), $args);

		foreach($settings as $key => $arg) {
			if(strtolower($arg) == 'false' || empty($arg)) {
				$settings["{$key}"] = false;
			}
		}

		if( empty( $settings['id'] ) ) {
			$settings['events'] = constant_contact_old_api_get_all('Events', $this->old_api);
			$settings['class'] .= ' multiple_events';
		} else {
			$settings['class'] .= ' single_event';
			$settings['events'] = array(CTCT_EventSpot::getInstance()->old_api->getEventDetails(new Event(array('link' => sprintf('/ws/customers/%s/events/%s', CTCT_USERNAME, $settings['id'] )))));
		}

		$this->settings = $settings;

		$output = kws_ob_include(EVENTSPOT_FILE_PATH.'shortcode.php', $this );

		if($echo) {
			echo $output;
		} else {
			return $output;
		}

	}

	/**
	 * Get the date of the latest registration activity for an event.
	 * @param  Event  $event         The event object
	 * @return string                The date of the last registration activity
	 */
	function latest_registrant($event) {

		$_registrants = $this->old_api->getRegistrants($event);

		foreach($_registrants['registrants'] as $key => &$reg) {
			$latest = 0;
			if(isset($reg->registrationStatus) && strtolower($reg->registrationStatus) !== 'cancelled') {
				$timestamp = strtotime($reg->registrationDate);
				$reg->registrationTimestamp = $timestamp;
				if($timestamp > $latest) { $latest = $timestamp; }
			} else {
				unset($_registrants['registrants'][$key]);
			}
		}
		if(empty($timestamp)) {
			return __('N/A', 'ctct');
		} else {
			return apply_filters('cc_event_registrationdate', $timestamp);
		}
	}

	/**
	 * Add the WP Admin dashboard widget with upcoming events details
	 */
	function dashboard_setup() {
		wp_add_dashboard_widget( 'constant_contact_events_dashboard', __( 'EventSpot', 'ctct'), array(&$this, 'events_dashboard') );
	}

	/**
	 * Generate a table of events from an events array
	 * @param string $title Title of the header
	 * @param array $events Array of events
	 */
	function dashboard_make_table($title = 'Events', $events = array()) {
		include(EVENTSPOT_FILE_PATH.'views/dashboard-table.php');
	}

	/**
	 * Generate the content for the Events dashboard widget
	 */
	function events_dashboard() {

		$_events = constant_contact_old_api_get_all('Events', $this->old_api);

		if(!empty($_events) && is_array($_events)) {
			$draft = $active = array();
			foreach($_events as $k => $v) {
				if($v->status === 'ACTIVE') {
					$active[$v->id] = $v;
				} elseif($v->status === 'DRAFT') {
					$draft[$v->id] = $v;
				}
			}
			if(!empty($active)) { $this->dashboard_make_table(__('Active Events', 'ctct'), $active); }
			if(!empty($draft)) { $this->dashboard_make_table(__('Draft Events', 'ctct'), $draft); }
		?>
			<p class="textright">
				<a class="button" href="<?php echo admin_url('admin.php?page=constant-contact-events'); ?>"><?php _e('View All Events', 'ctct'); ?></a>
			</p>
	<?php
		} else {
	?>
		<p><?php _e(sprintf("You don't have any events. Did you know that Constant Contact offers %sEvent Marketing%s?", '<a href="http://katz.si/4o" title="Learn more about Constant Contact Event Marketing">', '</a>'), 'constant_contact_api'); ?></p>
	<?php
		}
		return true;
	}

	function make_table($events = array(), $title = '') {
		include(EVENTSPOT_FILE_PATH.'views/events.php');
	}
}