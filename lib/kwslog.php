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

	/**
	 * Logging slug to be used for WP_Logging
	 * @var string
	 */
	private static $slug = 'ctct';

	/**
	 * Hold class instance
	 * @var KWSLog
	 */
	private static $instance;

	/**
	 * The enabled logging methods
	 * @var array
	 */
	private static $methods = array('error');

	/**
	 * The current log type being shown on log page
	 * @var string
	 */
	private $current_log_type = 'error';

	/**
	 * Logs to show later
	 * @var array
	 */
	private $stored_logs = array();

	/**
	 * How many logs to show per page?
	 * @var integer
	 */
	private $logs_per_page = 10;

	function load() {
		$KWSLog = new KWSLog;
		self::$instance = $KWSLog;
	}

	function getInstance() {

		if(empty(self::$instance)){
			self::$instance = new KWSLog;
		}

		return self::$instance;
	}

	function __construct() {

		// Load Pippin's logging class
		if( !class_exists( 'WP_Logging') ) {
			include_once CTCT_DIR_PATH.'vendor/pippinsplugins/WP-Logging/WP_Logging.php';
		}

		$slug = sanitize_title( str_replace(array(ABSPATH, 'plugins/', 'wp-content/', 'mu-plugins/', '/lib'), '', __DIR__) );

		$settings = get_option('ctct_settings', array( 'logging' => self::$methods ) );

		// What methods are supported?
		self::$methods = (array)$settings['logging'];

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
		$types[] = 'ctct_activity';
		$types[] = 'ctct_debug';
		$types[] = 'ctct_error';
		$types[] = 'ctct_log';
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
				.ctct_table pre,
				.kwslog-debug pre {
					overflow-x:auto;
					whitespace:pre-line;
					max-height:200px;
					overflow:auto;
					max-width: 400px;
				}

				/* Pagination links in Logging */
				.ctct-pagination {
					float: right;
				}
				.ctct-pagination ul {
					margin: .5em 0;
				}
				.ctct-pagination .page-numbers li,
				.ctct-pagination .page-numbers a {
					display: inline-block;
				}
				.ctct-pagination .page-numbers .page-numbers {
					padding: .25em .5em;
				}
			</style>
			<?php
		}
	}

	/**
	 * Add the admin menu to the Tools menu
	 */
	function log_menu() {
		$menu_title = __('Activity Log', 'ctct');
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
			'post_title'   => is_string( $title ) ? $title : NULL, // Just in case.
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

	function print_navigation() {

		$methods_text = array(
			'activity' => __('Constant Contact Activity', 'ctct'),
			'debug' => __('Debugging Logs', 'ctct'),
			'error' => __('Errors or Exceptions', 'ctct'),
			'log' => __('Notices', 'ctct'),
		);

		$navigation = array();
		foreach ( self::$methods as $method ) {
			$navigation[] = array(
				'val' => 'ctct_'.$method,
				'text' => $methods_text[ $method ],
			);
		}

		// Grab the first active method if the link hasn't been clicked yet
		if( empty( $_GET['log'] ) ) {
			$navigation[0]['val'] = '';
		}

		kws_print_subsub('log', $navigation );

	}

	function print_pagination( $current = 1 ) {

		?>
		<div class="ctct-pagination">

		<?php

			$translated = __( 'Page', 'ctct');

			echo paginate_links( array(
				'base' => esc_url_raw( add_query_arg( array( 'paged' => '%#%' ) ) ),
				'current' => $current,
				'total' => ceil( WP_Logging::get_log_count( 0, $this->current_log_type ) / $this->logs_per_page ),
				'type' => 'list',
			    'before_page_number' => '<span class="screen-reader-text">'.$translated.' </span>'
			) );

		?>
		</div>
		<?php
	}

	/*
	 * Show WP Sync Log data per blog
	 *
	 */
	function log_page() {

		$page = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;

		$this->current_log_type = empty( $_GET['log'] ) ? 'ctct_'.self::$methods[0] : $_GET['log'];

		// trying to acces a log type that's not enabled.
		if( !in_array( str_replace('ctct_', '', $this->current_log_type ), self::$methods ) ) {
			return;
		}

		$args = array(
		    'posts_per_page'=> $this->logs_per_page,
		    'paged' => $page,
		    'log_type' => $this->current_log_type,
		);

		$logs = WP_Logging::get_connected_logs( $args );

	?>
		<div class="wrap">

			<h2><?php esc_html_e('Constant Contact Log', 'ctct'); ?></h2>

			<?php

				$this->print_pagination( $page );

				$this->print_navigation();

			?>
			 <table class="ctct_table widefat">
				<thead>
				<tr>
					<th class="title"><?php esc_html_e('Title', 'kwslog'); ?></th>
					<th class="" style="width: 30%"><?php esc_html_e('Content', 'kwslog'); ?></th>
					<th class="column column-post_date"><?php esc_html_e('Date', 'kwslog'); ?></th>
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

											echo '<pre>';

											ob_start();
											print_r( $item );
											$item_output = ob_get_clean();

											echo esc_html( $item_output );

											echo '</pre>';
										}
									?></td>
									<td><?php echo esc_html( $log->post_date ); ?></td>
								</tr>
							<?php
						}
					} else {
					?>
					<tr>
						<td colspan="3" style="text-align:center;">
						<h4><?php esc_html_e('No activity has been logged.'); ?></h4>
						</td>
					</tr>
					<?php
					}
?>
				</tbody>
			</table>

			<?php
				$this->print_pagination( $page );
			?>

		</div>
	<?php
	}
}