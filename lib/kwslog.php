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
	private static $instance;
	private static $methods = array();
	private $stored_logs = array();

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

		if( !class_exists( 'CTCT_Settings' )) { return; }

		// Load Pippin's logging class
		if( !class_exists( 'WP_Logging') ) {
			include_once CTCT_DIR_PATH.'vendor/pippinsplugins/WP-Logging/WP_Logging.php';
		}

		$slug = sanitize_title( str_replace(array(ABSPATH, 'plugins/', 'wp-content/', 'mu-plugins/', '/lib'), '', __DIR__) );

		// What methods are supported?
		self::$methods = (array)CTCT_Settings::get('logging');

		self::$slug = $slug;

		add_action('ctct_debug', array(&$this, "debug"), 10, 3);
		add_action('ctct_log', array(&$this, "debug"), 10, 3);
		add_action('ctct_error', array(&$this, "error"), 10, 3);
		add_action('ctct_activity', array(&$this, "activity"), 10, 3);

		add_action( 'init', array(&$this, 'process_stored_logs'));

		add_action('admin_menu', array(&$this, 'log_menu'), 10000 );

		add_filter( 'wp_logging_should_we_prune', '__return_true', 10 );
		add_filter( 'wp_log_types', array( $this, 'wp_logging_log_types'));

		add_action('wp_enqueue_scripts', array(&$this, 'wp_enqueue_scripts'));
		add_action('admin_head', array(&$this, 'wp_head'));
		add_action('wp_head', array(&$this, 'wp_head'));
	}

	function wp_logging_log_types( $types ) {
		$types[] = 'ctct_debug';
		$types[] = 'ctct_log';
		$types[] = 'ctct_error';
		$types[] = 'ctct_activity';
		return $types;
	}

	function wp_enqueue_scripts() {
		global $pagenow;

		if(current_user_can('manage_options')) {
			wp_enqueue_script('jquery');
		}

	}

	function wp_head() {
		if(current_user_can('manage_options')) {
			?>
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
		$menu_title = __('Activity Log', 'constant-contact-api' );
		add_submenu_page( 'constant-contact-api', $menu_title, $menu_title, 'manage_options', 'constant-contact-log', array(&$this, 'log_page'));
	}

	/**
	 * Handle stored logs that weren't able to be output yet.
	 * @return void
	 */
	function process_stored_logs() {
		if( !empty( $this->stored_logs ) ) {
			foreach($this->stored_logs as $log ) {
				$this->insert_log( $log['type'], $log['title'], $log['message'], $log['data'] );
			}
		}
	}

	function insert_log( $type = 'debug', $title = NULL, $message = NULL, $data = NULL ) {
		global $wp_rewrite;

		// If we're not logging for certain types, then do not insert log.
		if( !in_array( $type, self::$methods) ) { return; }

		// This debug call is being called before a bunch of necessary stuff is loaded.
		// We store it, then call it later.
		if ( empty( $wp_rewrite ) ) {
			$this->stored_logs[] = compact( 'type', 'title', 'message', 'data' );
			return;
		}

		$log_data = array(
			'post_title'   => $title, // Just in case.
			'post_content' => is_string( $message ) ? $message : NULL,
			'log_type'     => 'ctct_'.$type,
		);

		$meta = array();
		if( !is_string( $message ) ) {
			$meta['message'] = $message;
		}
		if( !empty( $data ) ) {
			$meta['data'] = $data;
		}

		// Use instead of add(); so we get access to meta.
		$debug_post_id = WP_Logging::insert_log( $log_data, $meta );

	}

	function debug( $title = NULL, $message = NULL, $data = NULL ) {

		$this->insert_log( 'debug', $title, $message, $data );
	}

	function error( $title = NULL, $message = NULL, $data = NULL ) {

		if( !in_array( 'error', self::$methods) ) { return; }

		$this->insert_log( 'error', $title, $message, $data );
	}

	function activity( $title = NULL, $message = NULL, $data = NULL ) {

		$this->insert_log( 'activity', $title, $message, $data );
	}


	/*
	 * Show WP Sync Log data per blog
	 *
	 */
	function log_page() {

		$args = array(
		    'posts_per_page'=> -1,
		    'paged'         => get_query_var( 'paged' ),
		    'log_type'      => isset($_GET['log']) ? esc_attr( $_GET['log'] ) : 'ctct_debug',
		);

		$logs = WP_Logging::get_connected_logs( $args );
	?>
		<div class="wrap">
			<h2><?php _e(sprintf('%s Log', self::$name), 'kwslog'); ?></h2>

			<?php
				kws_print_subsub('log', array(
				    array('val' => 'ctct_activity', 'text' => 'Constant Contact Activity'),
				    array('val' => 'ctct_debug', 'text' => 'Debugging Logs'),
				    array('val' => 'ctct_log', 'text' => 'Notices'),
				    array('val' => 'ctct_error', 'text' => 'Errors or Exceptions')
				));
			?>
			 <table class="ctct_table widefat">
				<thead>
				<tr>
					<th class="title"><?php _e('Title', 'kwslog')?></th>
					<th class="" style="width: 30%"><?php _e('Content', 'kwslog')?></th>
					<th class="column column-post_date"><?php _e('Date', 'kwslog')?></th>
				</tr>
				</thead>
					<tbody>
					<?php

					if ( $logs ) {
						foreach ( $logs as $log ) {
							?>
								<tr>
									<td><?php echo get_the_title( $log ); ?></td>
									<td><?php

										$content = $log->post_content;
										$message = get_post_meta( $log->ID, '_wp_log_message', true );
										$data = get_post_meta( $log->ID, '_wp_log_data', true );

										foreach ( array( $content, $message, $data) as $key => $item ) {

											if( empty( $item ) ) {
												continue;
											}

											$item = maybe_unserialize( $item );

											echo '<pre style="max-height:300px; overflow:auto; max-width: 400px;">';
											if( is_string( $item ) ){
												print( htmlentities2( $item ) );
											} else {
												print_r( $item ) ;
											}
											echo '</pre>';
										}
									?></td>
									<td><?php echo $log->post_date; ?></td>
								</tr>
							<?php
						}
					}
?>
				</tbody>
			</table>
		</div>

	<?php
	}
}