<?php
/**
 * @package Constant Contact
 */

class CTCT_Constant_Analytics extends CTCT_Admin_Page {

	var $key = 'constant-analytics';
	var $title = 'Constant Analytics';
	var $page;
	var $profiles = array();
	var $profile_options = array();
	var $ga_token;
	var $ga_auth_error;
	var $ga_profile_id = NULL;

	function __construct() {

		// We only want this in the admin.
		if( !is_admin() ) { return; }

		parent::__construct();
	}

	protected function add() {}
	protected function edit() {}
	protected function view() {
		include(CTCT_FILE_PATH.'admin/analytics-settings.php');
	}
	protected function single() {}


	protected function processForms() {}

	function addIncludes() {
		global $pagenow, $plugin_page;

		if(!($plugin_page === 'constant-analytics' || isset($_GET['page']) && in_array($_GET['page'], array('constant-analytics', 'constant-analytics.php')))) {
			return;
		}

		$this->ga_auth_error = false;
		$this->ga_token = get_option('ccStats_ga_token');
		$this->ga_profile_id = get_option('ccStats_ga_profile_id');
		$this->generate_google_profiles();
	}

	function addActions() {
		global $pagenow, $plugin_page;

		add_action('admin_init', array(&$this, 'register_setting'));
		add_action('admin_menu', array(&$this, 'admin_menu'));
		add_action('plugins_loaded', array(&$this, 'request_handler'));

		if(!($plugin_page === 'constant-analytics' || isset($_GET['page']) && $_GET['page'] === 'constant-analytics.php')) {
			return;
		}

		add_action('admin_head', array(&$this, 'admin_head'));
		add_filter('plugin_action_links', array(&$this, 'plugin_action_links'), 10, 2);

		add_action('admin_enqueue_scripts', array(&$this, 'enqueue_scripts'));

		if($pagenow == 'admin.php') {
			$this->page = 'settings';
		} else {
			$this->page = 'dashboard';
		}

		if ($this->page == 'dashboard') {
			@header('X-UA-Compatible: IE=7');	// ask ie8 to behave like ie7 for the sake of vml
		}
	}

	function register_setting() {
		register_setting('constant-analytics', 'constant_contact_analytics');
	}

	function enqueue_scripts() {
		if ($this->page == 'dashboard') {
			wp_enqueue_script('jquery');
			wp_enqueue_script('ccStatsdatecoolite', plugin_dir_url(__FILE__).'js/date-coolite.js', array('jquery'));
			wp_enqueue_script('ccStatsdate', plugin_dir_url(__FILE__).'js/date.js', array('jquery', 'ccStatsdatecoolite'));
			wp_enqueue_script('ccStatsdatePicker', plugin_dir_url(__FILE__).'js/jquery.datePicker.js', array('jquery', 'ccStatsdatecoolite', 'ccStatsdate'));
			wp_enqueue_script('ccStatsdatePickerMultiMonth', plugin_dir_url(__FILE__).'js/jquery.datePickerMultiMonth.js', array('jquery', 'ccStatsdatecoolite', 'ccStatsdate', 'ccStatsdatePicker'));
			wp_enqueue_script('ccStats', plugin_dir_url(__FILE__).'js/constant-analytics.js', array('ccStatsdatePickerMultiMonth'));
			wp_enqueue_script('google_jsapi', 'http://www.google.com/jsapi');
		}

		if (!empty($this->page)) {
			wp_enqueue_style('ccStats', plugin_dir_url(__FILE__).'css/ccStats.css');
		}
	}

	function admin_head() {
		global $is_IE;
		if (!empty($this->page)) {
			if(!empty($is_IE)) {
				echo '
					<style> v\:* { behavior: url(#default#VML); } </style>
					<xml:namespace ns="urn:schemas-microsoft-com:vml" prefix="v" >
				';
				echo '
					<!--[if IE]>
						<link rel="stylesheet" href="'.site_url('?ccStats_action=admin_css_ie').'" type="text/css" media="screen" charset="utf-8" />
					<![endif]-->
				';
			}
			if ($this->page == 'dashboard') {
				echo '
					<script>
						if (typeof google !== \'undefined\') {
							google.load("gdata", "1");
							google.load("visualization", "1", {"packages": ["areachart", "table", "piechart", "imagesparkline", "geomap", "columnchart"]});
						}
					</script>
				';
			}
		}

	}

	function troubleshoot_message($error = '') {
		$result = '';
		if (!empty($error)) {
			$result .= '<p>The error message was: <span style="color:red;">'.htmlspecialchars($error).'</span>.</p>';
		}
		$result .= '
			<p>If you\'re having trouble getting up and running, please leave a message on the <a href="http://wordpress.org/tags/constant-contact-api?forum_id=10">plugin support forum</a></p>';
		return $result;
	}

	function check_config() {
		$curl_has_ssl = false;
		$php_has_ssl = false;
		$curl_exists = function_exists('curl_version');
		if ($curl_exists) {
			$curl_info = curl_version();
			if (isset($curl_info['protocols'])) {
				$curl_has_ssl = in_array('https', $curl_info['protocols']);
			}
			else {
				$curl_has_ssl = !empty($curl_info['ssl_version']);
			}
		}
		if (function_exists('stream_get_wrappers')) {
			$php_has_ssl = in_array('https', stream_get_wrappers());
		}
		return compact('curl_has_ssl', 'php_has_ssl', 'curl_exists');
	}

	function warning_box($message, $errors, $extra) {
		echo '
			<div class="error ccStats-warning">
				<h3>'.$message.'</h3>
		';
		if (!empty($errors)) {
			echo '
				<p>The error message was: <span style="color:#900;">'.htmlspecialchars($errors).'</span>.</p>
			';
		}

		echo $extra;

		echo $this->troubleshoot_message();
		echo '</div>';
	}

	function config_warnings() {
		$config_status = $this->check_config();
		$config_warning = '';

		if ($config_status['curl_exists'] && !$config_status['curl_has_ssl']) {
			$config_warning .= '<li>The version of cURL running on this server does not support SSL.</li>';
		}
		else if (!$config_status['curl_exists'] && !$config_status['php_has_ssl']) {
			$config_warning .= '<li>The version of PHP running on this server does not support SSL.</li>';
		}

		if (!empty($config_warning)) {
			$config_warning = '
				<p>We just asked your server about a few things and there\'s a chance you\'ll have problems using Constant Analytics.</p>
				<ul>
					'.$config_warning.'
				</ul>
				<p>Constant Analytics requires an SSL-enabled transport to work with Google Analytics. You may wish to contact your hosting service or server administrator to ensure that this is possible on your configuration.</p>
			';
		}
		return $config_warning;
	}

	function show_ga_auth_error($message, $errors = '') {
		$config_warnings = $this->config_warnings();
		$this->warning_box($message, $errors, $config_warnings);
	}

	function process_all_results_for_email($all_results) {
		foreach($all_results as $filter => $results) {
			if($filter == 'email') { continue; }
			foreach($results as $key => $result) {
				if(isset($result['dimensions']['source'])) {
					if(preg_match('/(\.?mail\.|gmail|email)/ism', $result['dimensions']['source'])) {
						$result['dimensions']['medium'] = 'email';
						$all_results['email'][] = $result;
						if($filter == '*') {
							$all_results[$filter][$key]['dimensions']['medium'] = 'email';
						} else {
							unset($all_results[$filter][$key]);
						}
					}
				}
			}
		}
		return $all_results;
	}

	function google_authentication_url() {
	    return 'https://www.google.com/accounts/AuthSubRequest?'.http_build_query(array(
	        'next' => admin_url('admin.php?ccStats_action=capture_ga_token'),
	        'scope' => 'https://www.google.com/analytics/feeds/',
	        'secure' => is_ssl(),
	        'session' => 1
	    ));
	}

	function request_handler() {
#r($_POST, true);
		if (!empty($_REQUEST['ccStats_action']) && current_user_can('manage_options')) {
			switch ($_REQUEST['ccStats_action']) {

				case 'admin_css_ie':
					header('Content-type: text/css');
					require('css/ccStats-ie.css');
					die();
				break;
				case 'capture_ga_token':
					$args = array();
					parse_str($_SERVER['QUERY_STRING'], $args);

					$token = NULL;
					if (isset($args['token'])) {
						$wp_http = $this->get_wp_http();
						$request_args = array(
							'method' => 'GET',
							'headers' => $this->get_authsub_headers($args['token']),
							'sslverify' => false
						);
						$response = $wp_http->request(
							'https://www.google.com/accounts/AuthSubSessionToken',
							$request_args
						);

						$error_messages = array();
						if (is_wp_error($response)) {
							// couldn't connect
							$error_messages = $response->get_error_messages();
						}
						else if (is_array($response)) {
							$matches = array();
							$found = preg_match('/Token=(.*)/', $response['body'], $matches);
							if ($found) {
								$token = $matches[1];
								$result = update_option('ccStats_ga_token', $token);
							}
							else {
								// connected, but no token in response.
								$error_messages = array($repsonse['body']);
							}
						}
					}

					if (!$token) {
						if (count($error_messages)) {
							$capture_errors .= implode("\n", $error_messages);
						}
						else {
							$capture_errors = 'unknown error';
						}
						$q = http_build_query(array(
							'ccStats_ga_token_capture_errors' => $capture_errors
						));
					}
					else {
						delete_option('ccStats_ga_profile_id');
						$q = http_build_query(array(
							'updated' => true
						));
					}
					wp_redirect(admin_url('admin.php?page=constant-analytics&'.$q));
				break;
				case 'get_wp_posts':
					header('Content-type: text/javascript');

					$start = (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $_REQUEST['start_date']) ? $_REQUEST['start_date'] : '0000-00-00');
					$end = (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $_REQUEST['end_date']) ? $_REQUEST['end_date'] : '0000-00-00');

					$transient_title = 'gwppo'.sha1($this->ga_profile_id.$start.$end.implode('_',$_REQUEST));
					$results = get_transient($transient_title);
					if($results && (!isset($_REQUEST['refresh']))) {
						die(json_encode(array(
							'success' => true,
							'data' => $results,
							'cached' => false
						)));
					}

					add_filter('posts_where', create_function(
						'$where',
						'return $where." AND post_date >= \''.$start.'\' AND post_date < \''.$end.'\'";'
					));
					$results = query_posts('post_status=publish&posts_per_page=999');

					set_transient($transient_title, $results, 60*60);

					die(json_encode(array(
						'success' => true,
						'data' => $results,
						'cached' => false
					)));
				break;
				case 'get_cc_data':
					header('Content-type: text/javascript');

					if(!isset($_REQUEST['data_type'])) { break; }

					switch ($_REQUEST['data_type']) {
						case 'campaigns':
							$start = $end = '';
							if(isset($_REQUEST['start_date'])) {
								$start = explode('-', $_REQUEST['start_date']);
								$start = mktime(0,0,0, (int)$start[1], (int)$start[2], (int)$start[0]);
							}
							if(isset($_REQUEST['end_date'])) {
								$end = explode('-', $_REQUEST['end_date']);
								$end = mktime(0,0,0, (int)$end[1], (int)$end[2], (int)$end[0]);
							}

							$results = array();

							$Campaigns = $this->cc->getAll('EmailCampaigns');

							foreach($Campaigns as $campaign) {

								// If it's not been sent...
								if(empty($campaign->last_sent_date)) { continue; }

								$time = strtotime($campaign->last_sent_date);

								if($time > $start && $time < $end) {
									$results[] = array(
										'send_time' => date('Y-m-d', $time),
										'title' => ($sent['Name'] == 'null') ? '' : $campaign->name,
										'archive_url' => admin_url('admin.php?page=constant-contact-campaigns&view='.$campaign->id)
									);
								}
							}

							if (!empty($results)) {
								die(json_encode(array(
									'success' => true,
									'data' => $results,
									'cached' => false
								)));
							}
							else {
								die(json_encode(array(
									'success' => false,
									'error' => 'No results; something may have gone wrong.'
								)));
							}
						break;
					}
				break;
				case 'get_ga_data':

					$parameters = array(
						'start-date' => $_REQUEST['start_date'],
						'end-date' => $_REQUEST['end_date'],
						'sort' => 'ga:date',
						'ids' => 'ga:'.$this->ga_profile_id
					);

					// split up top referrals by filtering on each medium in turn
					if ($_REQUEST['data_type'] == 'top_referrals') {
						$requests = array(
							'referral' => null,
							'organic' => null,
							'email' => null,
							'cpc' => null,
							'*' => null
						);
						$parameters['dimensions'] = 'ga:medium,ga:source';
						$parameters['metrics'] = 'ga:visits,ga:timeOnSite,ga:pageviews';
						$parameters['sort'] = '-ga:visits';

						$all_results = array();

						foreach ($requests as $filter => $request) {
							$transient_title = 'ggad'.sha1($this->ga_profile_id.$filter.implode('_', $parameters).implode('_',$_REQUEST));
							$results = get_transient($transient_title);
							if($results && (!isset($_REQUEST['refresh']))) { $all_results[$filter] = maybe_unserialize($results); continue; }

							$p = ($filter == '*' ? array('max-results' => 200) : array('filters' => 'ga:medium=='.$filter, 'max-results' => 200));
							$requests[$filter] = $request = $this->get_wp_http();
							$results = $request->request(
								'https://www.google.com/analytics/feeds/data?'.http_build_query(array_merge(
									$parameters,
									$p
								)),
								array(
									'headers' => $this->get_authsub_headers(),
									'timeout' => 30,
									'sslverify' => false
								)
							);
							set_transient($transient_title, maybe_serialize($results), 60*60*6);
							$all_results[$filter] = $results;
						}

						foreach ($all_results as $filter => $results) {
							if (is_wp_error($results)) {
								header('Content-type: text/javascript');
								die(json_encode(array(
									'success' => false,
									'error' => implode('<br/>', $results->get_error_messages())
								)));
							}
							if (substr($results['response']['code'], 0, 1) == '2') {
								$all_results[$filter] = $this->reportObjectMapper($results['body']);
							}
							else {
								header('Content-type: text/javascript');
								die(json_encode(array(
									'success' => false,
									'error' => $results['body']
								)));
							}
						}

						$all_results = $this->process_all_results_for_email($all_results);

						if(isset($_REQUEST['email_only'])) {
							$all_results = $all_results['email'];
						}

						header('Content-type: text/javascript');
						die(json_encode(array(
							'success' => true,
							'data' => $all_results,
							'cached' => false
						)));

					}
					else {
						switch ($_REQUEST['data_type']) {
							case 'visits':
								$parameters['dimensions'] = 'ga:date,ga:medium';
								$parameters['metrics'] = 'ga:visits,ga:bounces,ga:entrances,ga:pageviews,ga:newVisits,ga:timeOnSite';
								//$parameters['filters'] = 'ga:medium==referral,ga:medium==organic,ga:medium==email,ga:medium==cpc';
								//$parameters['sort'] = '-ga:visits';
							break;
							case 'geo':
								$parameters['dimensions'] = 'ga:country';
								$parameters['metrics'] = 'ga:visits';
								$parameters['sort'] = '-ga:visits';
							break;
							case 'top_referrals':
								$parameters['dimensions'] = 'ga:medium,ga:source';
								$parameters['metrics'] = 'ga:visits,ga:timeOnSite,ga:pageviews';
								$parameters['sort'] = '-ga:visits';
								$parameters['filters'] = 'ga:medium==referral,ga:medium==organic,ga:medium==email,ga:medium==cpc';
							break;
							case 'referral_media':
								$parameters['dimensions'] = 'ga:medium';
								$parameters['metrics'] = 'ga:visits';
								$parameters['sort'] = '-ga:visits';
							break;
							case 'top_content':
								$parameters['dimensions'] = 'ga:pagePath';
								$parameters['metrics'] = 'ga:pageviews,ga:uniquePageviews,ga:timeOnPage,ga:exits';
								$parameters['sort'] = '-ga:pageviews';
							break;
							case 'keywords':
								$parameters['dimensions'] = 'ga:keyword';
								$parameters['metrics'] = 'ga:pageviews,ga:uniquePageviews,ga:timeOnPage,ga:exits';
								$parameters['sort'] = '-ga:pageviews';
								$parameters['filters'] = 'ga:source=='.$_REQUEST['source_name'];
							break;
							case 'referral_paths':
								$parameters['dimensions'] = 'ga:source,ga:referralPath';
								$parameters['metrics'] = 'ga:pageviews,ga:uniquePageviews,ga:timeOnPage,ga:exits';
								$parameters['sort'] = '-ga:pageviews';
								$parameters['filters'] = 'ga:source=='.$_REQUEST['source_name'];
							break;
							case 'email_referrals':
								$parameters['dimensions'] = 'ga:campaign';
								$parameters['metrics'] = 'ga:pageviews,ga:uniquePageviews,ga:timeOnPage,ga:exits';
								$parameters['sort'] = '-ga:pageviews';
								$parameters['filters'] = 'ga:medium==email';
							break;
							default:
							break;
						}

						$transient_title = 'ggad'.sha1($this->ga_profile_id.implode('_',$parameters).implode('_',$_REQUEST));
						$results = get_transient($transient_title);
						if($results && (!isset($_REQUEST['refresh']))) {
							$result = maybe_unserialize($results);
						} else {
							$wp_http = $this->get_wp_http();
							$url = 'https://www.google.com/analytics/feeds/data?'.http_build_query($parameters);
							$request_args = array(
								'headers' => $this->get_authsub_headers(),
								'timeout' => 10,
								'sslverify' => false
							);
							$result = $wp_http->request(
								$url,
								$request_args
							);
						}
					}


					if (is_wp_error($result)) {
						header('Content-type: text/javascript');
						die(json_encode(array(
							'success' => false,
							'error' => implode('<br/>', $result->get_error_messages())
						)));
					}

					if (substr($result['response']['code'], 0, 1) == '2') {

						set_transient($transient_title, maybe_serialize($result), 60*60*6);

						$result = $this->reportObjectMapper($result['body']);

						if(empty($result)) {
							$_REQUEST['data_type'] = 'top_referrals';
							$_REQUEST['email_only'] = true;
							$this->request_handler();
						}
	#					$all_results = $this->process_all_results_for_email($all_results);

						header('Content-type: text/javascript');
						die(json_encode(array(
							'success' => true,
							'data' => $result,
							'cached' => false
						)));
					}
					else {
						header('Content-type: text/javascript');
						die(json_encode(array(
							'success' => false,
							'error' => $result['body']
						)));
					}
				break;
			}
		}
		if (!empty($_POST['ccStats_action']) && current_user_can('manage_options')) {
			$this->check_nonce($_POST['ccStats_nonce'], $_POST['ccStats_action']);
			switch ($_POST['ccStats_action']) {
				case 'revoke_ga_token':

					$wp_http = $this->get_wp_http();
					$request_args = array(
						'headers' => $this->get_authsub_headers(),
						'sslverify' => false
					);
					$response = $wp_http->request(
						'https://www.google.com/accounts/AuthSubRevokeToken',
						$request_args
					);
					if ($response['response']['code'] == 200) {
						delete_option('ccStats_ga_token');
						delete_option('ccStats_ga_profile_id');
						wp_redirect(admin_url('admin.php?page=constant-analytics&update=true'));
					}
					else if ($response['response']['code'] == 403) {
						wp_redirect(add_query_arg('ccStats_revoke_token_chicken_and_egg', $response['response']['code'].': '.$response['response']['message'], admin_url('admin.php?page=constant-analytics')));
					}
					else {
						if (is_wp_error($response)) {
							$errors = $response->get_error_messages();
						}
						else {
							$errors = array($response['response']['code'].': '.$response['response']['message']);
						}
						wp_redirect(admin_url('admin.php?page=constant-analytics&'.http_build_query(array(
							'ccStats_error' => implode("\n", $errors)
						))));
					}
				break;
				case 'forget_ga_token':
					delete_option('ccStats_ga_token');
					delete_option('ccStats_ga_profile_id');
					wp_redirect(admin_url('admin.php?page=constant-analytics&update=true'));
				break;
				case 'set_ga_profile_id':
					$result = update_option('ccStats_ga_profile_id', $_POST['profile_id']);
					wp_redirect(admin_url('admin.php?page=constant-analytics&updated=true'));
				break;
			}
			die();
		}
	}


	function check_nonce($nonce, $action_name) {
		if (wp_verify_nonce($nonce, $action_name) === false) {
			wp_die('The page with the command you submitted has expired. Please try again.');
		}
	}
	function create_nonce($action_name) {
		return wp_create_nonce($action_name);
	}


	/**
	 * Work around a bug in WP 2.7's implementation of WP_Http running on cURL.
	 */
	function get_authsub_headers($token = null) {
		global $wp_version;
		static $use_assoc = null;
		if (is_null($use_assoc)) {
			if (version_compare($wp_version, '2.8', '<')) {
				$use_assoc = false;
			}
			else {
				$use_assoc = true;
			}
		}
		$token = (is_null($token) ? $this->ga_token : $token);
		if (!$use_assoc) {
			return array('Authorization: AuthSub token="'.$token.'"');
		}
		return array('Authorization' => 'AuthSub token="'.$token.'"');
	}

	function admin_menu() {
		add_dashboard_page(
			__('Dashboard', 'ctct'),
			__('Constant Analytics', 'ctct'),
			'manage_options',
			basename(__FILE__),
			array(&$this, 'dashboard')
		);
	}

	function plugin_action_links($links, $file) {
		$plugin_file = basename(__FILE__);
		if (basename($file) == $plugin_file) {
			$settings_link = '<a href="admin.php?page=constant-analytics">'.__('Settings', 'ctct').'</a>';
			array_unshift($links, $settings_link);
		}
		return $links;
	}

	public function dashboard() {
		include_once(CTCT_FILE_PATH.'admin/analytics-dashboard.php');
	}


	/**
	 * Adapted from:
	 *
	 * GAPI - Google Analytics PHP Interface
	 * http://code.google.com/p/gapi-google-analytics-php-interface/
	 * @copyright Stig Manning 2009
	 * @author Stig Manning <stig@sdm.co.nz>
	 * @version 1.3
	 */
	function reportObjectMapper($xml_string) {
		$xml = simplexml_load_string($xml_string);


		$results = null;
		$results = array();

		$report_root_parameters = array();
		$report_aggregate_metrics = array();

		//Load root parameters

		$report_root_parameters['updated'] = strval($xml->updated);
		$report_root_parameters['generator'] = strval($xml->generator);
		$report_root_parameters['generatorVersion'] = strval($xml->generator->attributes());

		$open_search_results = $xml->children('http://a9.com/-/spec/opensearchrss/1.0/');

		foreach($open_search_results as $key => $open_search_result) {
			$report_root_parameters[$key] = intval($open_search_result);
		}

		$google_results = $xml->children('http://schemas.google.com/analytics/2009');

		foreach($google_results->dataSource->property as $property_attributes) {
			$attr = $property_attributes->attributes();
			$report_root_parameters[str_replace('ga:','',$attr->name)] = strval($attr->value);
		}

		$report_root_parameters['startDate'] = strval($google_results->startDate);
		$report_root_parameters['endDate'] = strval($google_results->endDate);

		//Load result aggregate metrics

		foreach($google_results->aggregates->metric as $aggregate_metric) {
			$attr = $aggregate_metric->attributes();
			$metric_value = strval($attr->value);
			$name = $attr->name;
			//Check for float, or value with scientific notation
			if(preg_match('/^(\d+\.\d+)|(\d+E\d+)|(\d+.\d+E\d+)$/',$metric_value)) {
				$report_aggregate_metrics[str_replace('ga:','',$name)] = floatval($metric_value);
			}
			else {
				$report_aggregate_metrics[str_replace('ga:','',$name)] = intval($metric_value);
			}
		}

		//Load result entries

		foreach($xml->entry as $entry) {
			$metrics = array();
			$children = $entry->children('http://schemas.google.com/analytics/2009');
			foreach($children->metric as $metric) {
				$attr = $metric->attributes();
				$metric_value = strval($attr->value);
				$name = $attr->name;

				//Check for float, or value with scientific notation
				if(preg_match('/^(\d+\.\d+)|(\d+E\d+)|(\d+.\d+E\d+)$/',$metric_value)) {
					$metrics[str_replace('ga:','',$name)] = floatval($metric_value);
				}
				else {
					$metrics[str_replace('ga:','',$name)] = intval($metric_value);
				}
			}

			$dimensions = array();
			$children = $entry->children('http://schemas.google.com/analytics/2009');
			foreach($children->dimension as $dimension) {
				$attr = $dimension->attributes();
				$dimensions[str_replace('ga:','',$attr->name)] = strval($attr->value);
			}

			$results[] = array('metrics' => $metrics, 'dimensions' => $dimensions);
		}

		return $results;
	}

	function generate_google_profiles() {

	    if (!empty($this->ga_token) && empty($this->profiles)) {
	    	$profiles = $this->profiles;
	    	$profile_options = $this->profile_options;
	    	$ga_profile_id = $this->ga_profile_id;
	        $url = 'https://www.googleapis.com/analytics/v2.4/management/accounts/~all/webproperties/~all/profiles';

	        $wp_http = $this->get_wp_http();
	        $request_args = array(
	            'timeout' => 10,
	            'headers' => $this->get_authsub_headers(),
	            'sslverify' => false,
	            'redirections' => 0,
	        );
	        $result = $wp_http->request(
	            $url,
	            $request_args
	        );

	        if (is_wp_error($result)) {
	            $connection_errors = $result->get_error_messages();
	        }
	        else {
	            $http_code = $result['response']['code'];

	            if ($http_code != 200) {
	                $this->ga_auth_error = $result['response']['code'].': '.$result['response']['message'];
	            }
	            else {
	                $xml = simplexml_load_string($result['body']);
	                $profiles = array();
	                foreach($xml->entry as $entry) {
	                    $properties = array();
	                    $children = $entry->children('http://schemas.google.com/analytics/2009');
	                    foreach($children->property as $property) {
	                        $attr = $property->attributes();
	                        $properties[str_replace('ga:', '', $attr->name)] = strval($attr->value);
	                    }
	                    $properties['title'] = strval($entry->title);
	                    $properties['updated'] = strval($entry->updated);
	                    $profiles[$properties['profileId']] = $properties;
	                }
	                if (count($profiles)) {
	                    if (empty($this->ga_profile_id)) {
	                        $ga_profile_id = $properties['profileId'];  // just use the last one
	                        update_option('ccStats_ga_profile_id', $ga_profile_id);
	                    } else {
	                    	$ga_profile_id = $this->ga_profile_id;
	                    }
	                    if (count($profiles) > 1) {
	                        $profile_options = array();
	                        foreach ($profiles as $id => $profile) {
	                            $profile_options[$profile['title']] = '<option value="'.$id.'"'.($ga_profile_id == $id ? 'selected="selected"' : '').'>'.$profile['title'].'</option>';
	                        }
	                    }
	                }
	            }
	        }

	        $this->profiles = $profiles;
	        ksort($profile_options);
	        $this->profile_options =  $profile_options;
	        $this->ga_profile_id = $ga_profile_id;
	    }
	}

	function get_wp_http() {
		if (!class_exists('WP_Http')) {
			include_once(ABSPATH.WPINC.'/class-http.php');
		}
		return new WP_Http();
	}
}

new CTCT_Constant_Analytics;
