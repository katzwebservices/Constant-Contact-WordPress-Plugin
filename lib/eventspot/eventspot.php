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
	static $instance;

	/**
	 * Set the submenu anchor text
	 * @return string "Events"
	 */
	protected function getNavTitle() {
		return __('Events', 'constant-contact-api');
	}

	function add() {}
	function edit() {}

	function getInstance() {

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
		add_shortcode('ccevents', array(&$this, 'events_output'));
		add_shortcode('constantcontactevents', array(&$this, 'events_output'));
		add_shortcode('eventspot', array(&$this, 'events_output'));
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

			$this->make_table($events, __('Events','constant-contact-api'));
			?>
			<p class="submit"><a href="<?php echo add_query_arg('refresh', 'events'); ?>" class="button-secondary alignright" title="<?php echo sprintf('Event registrants data is stored for %s hours. Refresh data now.', round(KWS_V1API::$event_cache_age / 3600)); ?>">Refresh Events</a></p>
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

	static function setup() {
		$CTCT_EventSpot = new CTCT_EventSpot;
	}


	function events_output($args = array(), $content = null, $echo = false) {
		require(EVENTSPOT_FILE_PATH.'shortcode.php');
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
			return __('N/A','constant-contact-api');
		} else {
			return apply_filters('cc_event_registrationdate', $timestamp);
		}
	}

	function dashboard_setup() {
		wp_add_dashboard_widget( 'constant_contact_events_dashboard', __( 'EventSpot','constant-contact-api'), array(&$this, 'events_dashboard') );
	}

	function dashboard_make_table($title = 'Events', $events = array()) {
		include(EVENTSPOT_FILE_PATH.'views/dashboard-table.php');
	}

	function events_dashboard() {

		$_events = $this->old_api->getEvents();

		if(!empty($_events) && is_array($_events) && !empty($_events['events'])) {
			$draft = $active = array();
			foreach($_events['events'] as $k => $v) {
				if($v->status === 'ACTIVE') {
					$active[$v->id] = $v;
				} elseif($v->status === 'DRAFT') {
					$draft[$v->id] = $v;
				}
			}
			if(!empty($active)) { $this->dashboard_make_table(__('Active Events','constant-contact-api'), $active); }
			if(!empty($draft)) { $this->dashboard_make_table(__('Draft Events','constant-contact-api'), $draft); }
		?>
			<p class="textright">
				<a class="button" href="<?php echo admin_url('admin.php?page=constant-contact-events'); ?>">View All Events</a>
			</p>
	<?php
		} else {
	?>
		<p style='font-size:12px;'><?php _e(sprintf("You don't have any events. Did you know that Constant Contact offers %sEvent Marketing%s?", '<a href="http://conta.cc/hB5lnC" title="Learn more about Constant Contact Event Marketing">', '</a>'), 'constant_contact_api'); ?></p>
	<?php
		}
		return true;
	}

	function make_table($events = array(), $title = '') {
		include(EVENTSPOT_FILE_PATH.'views/table.php');
	}
}