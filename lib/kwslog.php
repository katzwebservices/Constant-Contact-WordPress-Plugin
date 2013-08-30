<?php
/**
 * Katz Web Services, Inc.'s logger of choice!
 *
 * Requires WordPress
 *
 * Easy to drop in to plugins. Pretty sweet.
 */

// Check WordPress
if(!function_exists('add_action')) {
	throw new Exception('KWSLog needs to be included after WordPress has loaded', 1);
	return;
}

if(defined('KWS_LOG_AUTOLOAD')) {
	if(did_action('plugins_loaded')) {
		KWSLog::load();
	} else {
		add_action('plugins_loaded', array('KWSLog', 'load'));
	}
}

if(class_exists('KWSLog')) { return; }

class KWSLog {

	private static $name = 'Constant Contact';
	private static $slug = 'ctct';
	private static $tablename;
	private static $instance;
	private $logs = array();

	function load() {
		$KWSLog = new KWSLog();
		self::$instance = $KWSLog;
	}

	function getInstance() {

		if(empty(self::$instance)){
			self::$instance = new KWSLog();
		}

		return self::$instance;
	}

	function __construct($name = 'Constant Contact') {

		$slug = sanitize_title( str_replace(array(ABSPATH, 'plugins/', 'wp-content/', 'mu-plugins/', '/lib'), '', __DIR__) );


		self::$slug = $slug;
		self::$tablename = $slug.'_log';

		/**
		 * Register the action for the logger.
		 *
		 * Trigger a log item using `do_action('$tablename', $message, $loglevel);`
		 */
		add_action(self::$tablename, array(&$this, "log_message") ,1,3);
		add_action(self::$slug.'_debug', array(&$this, "debug"));

		add_action('admin_menu', array(&$this, 'log_menu'));

		add_action('wp_enqueue_scripts', array(&$this, 'wp_enqueue_scripts'));
		add_action('wp_head', array(&$this, 'wp_head'));
		add_action('shutdown', array(&$this, 'print_logs'), 10000);
	}

	function wp_enqueue_scripts() {
		if(current_user_can('manage_options')) {
			wp_enqueue_script('jquery');
		}
	}

	function wp_head() {
		if(current_user_can('manage_options')) {
			?>
			<script>
				jQuery('.kwslog-toggle').live('click', function(e) {
					jQuery('.data', jQuery(this).parents('.kwslog-debug')).toggle();
					return false;
				});
			</script>
			<style type="text/css">
				.kwslog-debug {
					margin:10px 0 20px;
					padding:10px;
					border-top: 1px solid #ccc;
					border-bottom: 1px solid #ccc;
					background: rgba(240,240,240,.5);
				}
				.kwslog-debug pre {
					overflow-x:auto; whitespace:pre-line
				}
			</style>
			<?php
		}
	}

	/**
	 * Add the admin menu to the Tools menu
	 */
	function log_menu() {
		add_management_page(__(self::$name, 'kwslog'), sprintf(__('%s Log', 'kwslog'), self::$name), 'manage_options', __FILE__, array(&$this, 'log_page'));
	}

	/*
	 *  Add the log table to the installation
	 */
	function activate_plugin() {
		global $wpdb;


		$tablename = $wpdb->prefix . self::$tablename;

		if($wpdb->get_var("SHOW TABLES LIKE '{$tablename}'") != $tablename ) {

			$sql = "CREATE TABLE IF NOT EXISTS `{$tablename}` (
	  				`log_id` int(11) NOT NULL AUTO_INCREMENT,
	  				`message` text NOT NULL,
	  				`level` varchar(20) NOT NULL,
	  				`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	  				 PRIMARY KEY (`log_id`)
				);";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
	}


	/**
	 * @todo - Add delete table SQL
	 */
	function log_deactivate_plugin() {

	}

	function debug($message = '') {

		if(!function_exists('current_user_can') || !current_user_can('manage_options')) { return; }

		$bt = debug_backtrace();

		foreach($bt as $call) {
			if($call['function'] === 'do_action' && $call['args'][0] === self::$slug.'_debug') {
				$caller = $call;
				// do_action and title
				unset($caller['args'][0]);
				if(is_string($caller['args'][1])) {
					unset($caller['args'][1]);
				}
				break;
			}
			if(isset($call['line']) && $call['function'] === 'debug' && $call['class'] === 'KWSLog') {
				$caller = $call;
				if(!empty($message)) {
					unset($caller['args'][0]);
				}
				break;
			}
		}

		if(!empty($message) && is_string($message)) {
			$message = '<h4>'.$message.'</h4>';
			$hidedata = 'display:none;';
		} elseif(!is_string($message)){
			$message = '';
			$hidedata = '';
		}

		$additional_data = '';
		if(!empty($caller['args'])) {
			foreach($caller['args'] as &$arg) {
				if(is_string($arg)){
					$arg = htmlentities2($arg);
				}
			}
			$additional_data = '<div><a href="#" class="kwslog-toggle">View Data</a></div><div class="data" style="'.$hidedata.'"><pre>'.print_r($caller['args'], true).'</pre></div>';
		}

		$debug_message = '<div class="kwslog-debug">'.$message.'<p><code>'.str_replace(ABSPATH, '', $caller['file']).'</code>, line <code>'.$caller['line'].'</code></p>'.$additional_data.'</div>';

		$logs = (array)$this->logs;
		$logs[] = $debug_message;
		$this->logs = $logs;

	}

	/**
	 * Output logs that were generated while the page was being generated
	 */
	function print_logs() {
		if(!current_user_can('manage_options') || defined('DOING_AJAX')) { return; }

		if(empty($this->logs)) { return; }
		foreach($this->logs as $log) {
			echo $log;
		}
	}

	/**
	 * Create a log message. If the log table doesn't exist (it should be created on activation), create the log table.
	 * @param  mixed  $message   What the log message should include.
	 * @param  string  $level     Debug level. Default: 'debug'
	 * @param  boolean $recursive Whether or not the message is being called by itself. Only here to prevent infinite loop.
	 */
	function log_message($message, $data = '', $level = 'debug', $recursive = false) {
		global $wpdb;

		$message = is_string($message) ? $message : print_r($message, true);
		$data = is_string($data) ? $data : print_r($data, true);
		$message = $message."<br />".$data;

		$values = array(
			'message' => $message,
			'level' => $level
		);

		$wpdb->insert($wpdb->prefix.self::$tablename, $values, array('%s','%d'));

		if(!empty($wpdb->last_error) && preg_match('/doesn\'t\ exist/ism', $wpdb->last_error) && !$recursive) {
			$this->activate_plugin();
			$this->log_message($message, $data, $level, true);
		}
	}


	/*
	 * Return Array of log messages
	*/
	function get_log_messages($start = 0,$limit = 50, $recursive = false) {
		global $wpdb;
		$tablename = self::$tablename;
		$results = $wpdb->get_results($wpdb->prepare("SELECT message, level, date
			FROM  `{$wpdb->prefix}{$tablename}`
			ORDER BY date DESC
			LIMIT %d,%d", $start,$limit));

		if(!empty($wpdb->last_error) && preg_match('/doesn\'t\ exist/ism', $wpdb->last_error) && !$recursive) {
			$this->activate_plugin();
			$this->get_log_messages($start, $limit, true);
		}

		return $results;
	}



	/*
	 * Show WP Sync Log data per blog
	 *
	 */
	function log_page()
	{

		if (!is_super_admin())
			redirect_post();

		$logdata = $this->get_log_messages(0,40);
	?>
		<div class="wrap">
			<h2><?php _e(sprintf('%s Log', self::$name), 'kwslog'); ?></h2>

			 <table class="widefat post">
				<thead>
				<tr>
					<th class="title"><?php _e('Message', 'kwslog')?></th>
					<th class=""><?php _e('Level', 'kwslog')?></th>
					<th class="date"><?php _e('Date', 'kwslog')?></th>

				</tr>
				</thead>
					<tbody>
					<?php foreach ($logdata as $logEntry) {?>
						<tr>
							<td><pre><?php echo $logEntry->message ?></pre></td>
							<td><?php echo $logEntry->level ?></td>
							<td><?php echo $logEntry->date ?></td>
						</tr>
					<?php }?>
				</tbody>
			</table>
		</div>

	<?php
	}
}