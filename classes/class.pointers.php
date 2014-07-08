<?php
/**
 * @package Admin
 */

if ( !class_exists( 'WP_CTCT' ) ) {
	header('HTTP/1.0 403 Forbidden');
	wp_die();
}

/**
 * This class handles the pointers used in the introduction tour.
 *
 * @todo Add an introdutory pointer on the edit post page too.
 */
class CTCT_Pointers {

	/**
	 * Class constructor.
	 */
	function __construct() {
		global $wp_version;

		// Make sure WordPress supports pointers
		if ( version_compare($wp_version, '3.4', '<') ) {
			return false;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueue styles and scripts needed for the pointers.
	 */
	function enqueue() {
		if ( ! current_user_can('manage_options') )
			return;

		$options = get_option( 'ctct_pointers', array());
		#if ( !isset( $options['ctct_tracking'] ) || ( !isset( $options['ignore_tour'] ) || !$options['ignore_tour'] ) ) {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'jquery-ui' );
			wp_enqueue_script( 'wp-pointer' );
			wp_enqueue_script( 'utils' );
		#}
		#if ( !isset( $options['tracking_popup'] ) && !isset( $_GET['allow_tracking'] ) ) {
		#	add_action( 'admin_print_footer_scripts', array( $this, 'tracking_request' ) );
		#} else if ( !isset( $options['ignore_tour'] ) || !$options['ignore_tour'] ) {
			add_action( 'admin_print_scripts', array( $this, 'intro_tour' ), 100);
			#add_action( 'admin_print_footer_scripts', array( $this, 'intro_tour' ) );
			add_action( 'admin_head', array( $this, 'admin_head' ) );
		#}
	}

	/**
	 * Shows a popup that asks for permission to allow tracking.
	 */
	function tracking_request() {
		$id    = '#wpadminbar';
		$nonce = wp_create_nonce( 'ctct_activate_tracking' );

		$content = '<h3>' . __( 'Help improve the Constant Contact plugin', 'constant-contact-api' ) . '</h3>';
		$content .= '<p>' . __( 'You\'ve just installed Constant Contact WordPress Plugin by Katz Web Services. Please helps us improve it by allowing us to gather anonymous usage stats so we know which configurations, plugins and themes to test with.', 'constant-contact-api' ) . '</p>';
		$opt_arr   = array(
			'content'  => $content,
			'position' => array( 'edge' => 'top', 'align' => 'center' )
		);
		$button2   = __( 'Allow tracking', 'constant-contact-api' );

		$function2 = 'ctct_store_answer("yes","'.$nonce.'")';
		$function1 = 'ctct_store_answer("no","'.$nonce.'")';

		$this->print_scripts( $id, $opt_arr, __( 'Do not allow tracking', 'constant-contact-api' ), $button2, $function2, $function1 );
	}

	/**
	 * Load the introduction tour
	 */
	function intro_tour() {
		global $pagenow, $current_user, $plugin_page;

		$adminpages = array(
			'constant-contact-api'	=> array(
				'setup' => array(
				    'target' => '#setup-link',
					'content'  => '<h3>' . __( 'Setup', 'constant-contact-api' ) . '</h3><p>' . __( 'This is where you connect', 'constant-contact-api' ) . '</p>'
						. '<p><strong>' . __( 'More WordPress SEO', 'constant-contact-api' ) . '</strong><br/>' . sprintf( __( 'There\'s more to learn about WordPress & SEO than just using this plugin. Read our article %1$sthe definitive guide to WordPress SEO%2$s.', 'constant-contact-api' ), '<a target="_blank" href="http://yoast.com/articles/wordpress-seo/#utm_source=wpadmin&utm_medium=ctct_tour&utm_term=link&utm_campaign=ctctplugin">', '</a>' ) . '</p>'
						. '<p><strong>' . __( 'Webmaster Tools', 'constant-contact-api' ) . '</strong><br/>' . __( 'Underneath the General Settings, you can add the verification codes for the different Webmaster Tools programs, I highly encourage you to check out both Google and Bing\'s Webmaster Tools.', 'constant-contact-api' ) . '</p>'
						. '<p><strong>' . __( 'About This Tour', 'constant-contact-api' ) . '</strong><br/>' . __( 'Clicking Next below takes you to the next page of the tour. If you want to stop this tour, click "Close".', 'constant-contact-api' ) . '</p>'
						. '<p><strong>' . __( 'Like this plugin?', 'constant-contact-api' ) . '</strong><br/>' . sprintf( __( 'If you like this plugin, please %srate it 5 stars on WordPress.org%s and consider making a donation by clicking the button on the right!', 'constant-contact-api' ), '<a target="_blank" href="http://wordpress.org/extend/plugins/wordpress-seo/">', '</a>' ) . '</p>' .
						'<p><strong>' . __( 'Newsletter', 'constant-contact-api' ) . '</strong>',
					'button2'  => __( 'Next', 'constant-contact-api' ),
					'function' => 'jQuery(this).parents(".wp-pointer:visible").hide(); jQuery("#registration-link").click();'
				),
				'registration' => array(
					'content'  => "<h3>" . __( "Title &amp; Description settings", 'constant-contact-api' ) . "</h3>"
						. "<p>" . __( "This is where you set the templates for your titles and descriptions of all the different types of pages on your blog, be it your homepage, posts & pages (under post types), category or tag archives (under taxonomy archives), or even custom post type archives and custom posts: all of that is done from here.", 'constant-contact-api' ) . "</p>"
						. "<p><strong>" . __( "Templates", 'constant-contact-api' ) . "</strong><br/>"
						. __( "The templates are built using variables, the help tab for all the different variables available to you to use in these.", 'constant-contact-api' ) . "</p>"
						. "<p><strong>" . __( "Sitewide settings", 'constant-contact-api' ) . "</strong><br/>"
						. __( "You can also set some settings for the entire site here to add specific meta tags or to remove some unneeded cruft.", 'constant-contact-api' ) . "</p>",
					'button2'  => __( 'Next', 'constant-contact-api' ),
					'function' => 'window.location="' . admin_url( 'admin.php?page=ctct_social' ) . '";'
				),
				'ctct_social'         => array(
					'content'  => "<h3>" . __( "Social settings", 'constant-contact-api' ) . "</h3>"
						. "<p><strong>" . __( 'Facebook OpenGraph', 'constant-contact-api' ) . '</strong><br/>'
						. __( "On this page you can enable the OpenGraph functionality from this plugin, as well as assign a Facebook user or Application to be the admin of your site, so you can view the Facebook insights.", 'constant-contact-api' ) . "</p>"
						. '<p>' . sprintf( __( 'Read more about %1$sFacebook OpenGraph%2$s.', 'constant-contact-api' ), '<a target="_blank" href="http://yoast.com/facebook-open-graph-protocol/#utm_source=wpadmin&utm_medium=ctct_tour&utm_term=link&utm_campaign=ctctplugin">', '</a>' ) . "</p>"
						. "<p><strong>" . __( 'Twitter Cards', 'constant-contact-api' ) . '</strong><br/>'
						. sprintf( __( 'This functionality is currently in beta, but it allows for %1$sTwitter Cards%2$s.', 'constant-contact-api' ), '<a target="_blank" href="http://yoast.com/twitter-cards/#utm_source=wpadmin&utm_medium=ctct_tour&utm_term=link&utm_campaign=ctctplugin">', '</a>' ) . "</p>",
					'button2'  => __( 'Next', 'constant-contact-api' ),
					'function' => 'window.location="' . admin_url( 'admin.php?page=ctct_xml' ) . '";'
				),
				'ctct_xml'            => array(
					'content'  => '<h3>' . __( 'XML Sitemaps', 'constant-contact-api' ) . '</h3><p>' . __( 'This plugin adds an XML sitemap to your site. It\'s automatically updated when you publish a new post, page or custom post and Google and Bing will be automatically notified.', 'constant-contact-api' ) . '</p><p>' . __( 'Be sure to check whether post types or taxonomies are showing that search engines shouldn\'t be indexing, if so, check the box before them to hide them from the XML sitemaps.', 'constant-contact-api' ) . '</p>',
					'button2'  => __( 'Next', 'constant-contact-api' ),
					'function' => 'window.location="' . admin_url( 'admin.php?page=ctct_permalinks' ) . '";'
				),
				'ctct_permalinks'     => array(
					'content'  => '<h3>' . __( 'Permalink Settings', 'constant-contact-api' ) . '</h3><p>' . __( 'All of the options here are for advanced users only, if you don\'t know whether you should check any, don\'t touch them.', 'constant-contact-api' ) . '</p>',
					'button2'  => __( 'Next', 'constant-contact-api' ),
					'function' => 'window.location="' . admin_url( 'admin.php?page=ctct_internal-links' ) . '";'
				),
				'ctct_internalLinks' => array(
					'content'  => '<h3>' . __( 'Breadcrumbs Settings', 'constant-contact-api' ) . '</h3><p>' . sprintf( __( 'If your theme supports my breadcrumbs, as all Genesis and WooThemes themes as well as a couple of other ones do, you can change the settings for those here. If you want to modify your theme to support them, %sfollow these instructions%s.', 'constant-contact-api' ), '<a target="_blank" href="http://yoast.com/wordpress/breadcrumbs/#utm_source=wpadmin&utm_medium=ctct_tour&utm_term=link&utm_campaign=ctctplugin">', '</a>' ) . '</p>',
					'button2'  => __( 'Next', 'constant-contact-api' ),
					'function' => 'window.location="' . admin_url( 'admin.php?page=ctct_rss' ) . '";'
				),
				'ctct_rss'            => array(
					'content'  => '<h3>' . __( 'RSS Settings', 'constant-contact-api' ) . '</h3><p>' . __( 'This incredibly powerful function allows you to add content to the beginning and end of your posts in your RSS feed. This helps you gain links from people who steal your content!', 'constant-contact-api' ) . '</p>',
					'button2'  => __( 'Next', 'constant-contact-api' ),
					'function' => 'window.location="' . admin_url( 'admin.php?page=ctct_import' ) . '";'
				),
				'ctct_import'         => array(
					'content'  => '<h3>' . __( 'Import &amp; Export', 'constant-contact-api' ) . '</h3><p>' . __( 'Just switched over from another SEO plugin? Use the options here to switch your data over. If you were using some of my older plugins like Robots Meta &amp; RSS Footer, you can import the settings here too.', 'constant-contact-api' ) . '</p><p>' . __( 'If you have multiple blogs and you\'re happy with how you\'ve configured this blog, you can export the settings and import them on another blog so you don\'t have to go through this process twice!', 'constant-contact-api' ) . '</p>',
					'button2'  => __( 'Next', 'constant-contact-api' ),
					'function' => 'window.location="' . admin_url( 'admin.php?page=ctct_files' ) . '";'
				),
				'ctct_files'          => array(
					'content'  => '<h3>' . __( 'File Editor', 'constant-contact-api' ) . '</h3><p>' . __( 'Here you can edit the .htaccess and robots.txt files, two of the most powerful files in your WordPress install. Only touch these files if you know what you\'re doing!', 'constant-contact-api' ) . '</p>'
						. '<p>' . sprintf( __( 'The tour ends here, thank you for using my plugin and good luck with your SEO!<br/><br/>Best,<br/>Joost de Valk - %1$sYoast.com%2$s', 'constant-contact-api' ), '<a target="_blank" href="http://yoast.com/#utm_source=wpadmin&utm_medium=ctct_tour&utm_term=link&utm_campaign=ctctplugin">', '</a>' ) . '</p>',
				),
			),
		);

		if ( ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) || ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) ) {
			unset( $adminpages['ctct_files'] );
			$adminpages['ctct_import']['function'] = '';
			unset( $adminpages['ctct_import']['button2'] );
			$adminpages['ctct_import']['content'] .= '<p>' . sprintf( __( 'The tour ends here,thank you for using my plugin and good luck with your SEO!<br/><br/>Best,<br/>Joost de Valk - %1$sYoast.com%2$s', 'constant-contact-api' ), '<a href="http://yoast.com/">', '</a>' ) . '</p>';
		}
		$page = '';
		if ( isset( $_GET['page'] ) )
			$page = $_GET['page'];


		$function = '';
		$button2  = '';
		$opt_arr  = array();
		$target   = '#ctct-settings-tabs ul.ui-tabs-nav li.ui-tabs-active';
		if ( 'admin.php' != $pagenow || !array_key_exists( $page, $adminpages ) ) {
			$target      = 'li.toplevel_page_constant-contact-api';
			$content = '<h3>' . __( 'Congratulations!', 'constant-contact-api' ) . '</h3>';
			$content .= '<p>' . __( 'You\'ve just installed Constant Contact WordPress Plugin! Click "Start Tour" to view a quick introduction of this plugin\'s core functionality.', 'constant-contact-api' ) . '</p>';
			$opt_arr  = array(
				'content'  => $content,
				'position' => array( 'edge' => 'top', 'align' => 'center' )
			);
			$button2  = __( "Start Tour", 'constant-contact-api' );
			$function = 'document.location="' . admin_url( 'admin.php?page=constant-contact-api' ) . '";';
			$this->print_scripts('', $target, $opt_arr, __( "Close", 'constant-contact-api' ), $button2, $function );
		} else {
			if ( '' != $page && in_array( $page, array_keys( $adminpages ) ) ) {
				#r($adminpages[$page], true);
				foreach ($adminpages[$page] as $key => $pointer) {
					$target = !empty($pointer['target']) ? $pointer['target'] : $target;
					$opt_arr  = array(
						'content'      => $pointer['content'],
						'position'     => array( 'edge' => 'top', 'align' => 'left' ),
						'pointerWidth' => 400
					);
					$button2  = @$pointer['button2'];
					$function = @$pointer['function'];
					$this->print_scripts( $key, $target, $opt_arr, __( "Close", 'constant-contact-api' ), $button2, $function );
				}
			}
		}
	}

	/**
	 * Load a tiny bit of CSS in the head
	 */
	function admin_head() {
		?>
	<style type="text/css" media="screen">
		#pointer-primary {
			margin: 0 5px 0 0;
		}
	</style>
	<?php
	}

	/**
	 * Prints the pointer script
	 *
	 * @param string      $key              The name of the pointer
	 * @param string      $selector         The CSS selector the pointer is attached to.
	 * @param array       $options          The options for the pointer.
	 * @param string      $button1          Text for button 1
	 * @param string|bool $button2          Text for button 2 (or false to not show it, defaults to false)
	 * @param string      $button2_function The JavaScript function to attach to button 2
	 * @param string      $button1_function The JavaScript function to attach to button 1
	 */
	function print_scripts( $key, $selector, $options, $button1, $button2 = false, $button2_function = '', $button1_function = '' ) {
		$key = empty($key) ? $key : $key.'_';
		?>
	<script>
		//<![CDATA[
		(function ($) {
			var <?php _e($key); ?>ctct_pointer_options = <?php echo json_encode( $options ); ?>, <?php _e($key); ?>setup;

			<?php _e($key); ?>ctct_pointer_options = $.extend(<?php _e($key); ?>ctct_pointer_options, {
				buttons:function (event, t) {
					button = jQuery('<a id="pointer-close" style="margin-left:5px" class="button-secondary">' + '<?php echo $button1; ?>' + '</a>');
					button.bind('click.pointer', function () {
						t.element.pointer('close');
					});
					return button;
				},
				close:function () {
				}
			});


			<?php _e($key); ?>setup = function () {
				jQuery('<?php echo $selector; ?>').pointer(<?php _e($key); ?>ctct_pointer_options).pointer('open');
				<?php if ( $button2 ) { ?>
					jQuery('#pointer-close').after('<a id="pointer-primary" class="button-primary">' + '<?php echo $button2; ?>' + '</a>');
					jQuery('#pointer-primary').click(function () {
						<?php echo $button2_function; ?>
					});
					jQuery('#pointer-close').click(function () {
						<?php if ( $button1_function == '' ) { ?>
							ctct_setIgnore("tour", "wp-pointer-0", "<?php echo wp_create_nonce( 'ctct-ignore' ); ?>");
							<?php } else { ?>
							<?php echo $button1_function; ?>
							<?php } ?>
					});
					<?php } ?>
			};

			if (<?php _e($key); ?>ctct_pointer_options.position && <?php _e($key); ?>ctct_pointer_options.position.defer_loading)
				jQuery(window).bind('load.wp-pointers', <?php _e($key); ?>setup);
			else
				jQuery(document).ready(<?php _e($key); ?>setup);
		})(jQuery);
		//]]>
	</script>
	<?php
	}
}

$CTCT_Pointers = new CTCT_Pointers;
