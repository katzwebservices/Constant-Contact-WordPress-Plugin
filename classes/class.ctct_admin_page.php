<?php
/**
 * @package CTCT
 * @version 3.0
 */

abstract class CTCT_Admin_Page {

	var $id;
	var $key;
	var $title;
	var $permission = 'manage_options';

	/**
	 * @var KWSConstantContact
	 */
	var $cc;

	var $oauth;
	var $errors;
	var $can_edit = false;
	var $can_add = false;
	var $component = '';
	var $notices = array();

	function __construct( $force_load = false ) {

		if ( ! is_admin() && ! $force_load ) {
			return;
		}

		$this->addIncludes();

		$WP_CTCT     = WP_CTCT::getInstance();
		$this->cc    = $WP_CTCT->cc;
		$this->oauth = $WP_CTCT->oauth;


		$this->title = $this->getTitle();
		$this->key   = $this->getKey();
		$this->id    = $this->getID();

		if ( is_admin() ) {

			$this->processForms();

			add_action( 'admin_menu', array( &$this, 'add_menu' ) );
			add_action( 'admin_notices', array( &$this, 'print_notices' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'registerScripts' ) );
			add_action( 'admin_print_scripts', array( &$this, 'print_scripts' ) );
			add_action( 'admin_print_scripts', array( &$this, 'addScripts' ), 11 );
			add_action( 'admin_print_styles', array( &$this, 'print_styles' ) );
			add_filter( 'constant_contact_help_tabs', array( &$this, 'help_tabs' ) );
		}

		$this->addActions();
	}

	protected function addIncludes() {
	}

	// TODO: Only load on CTCT pages.
	public function print_styles() {

		wp_enqueue_style( 'ctct-admin-global' );

		// If the current page isn't the page being requested, we don't print those scripts
		if ( empty( $_GET['page'] ) || $this->key !== $_GET['page'] ) {
			return;
		}

		wp_enqueue_style( 'ctct-admin' );
		wp_enqueue_style( 'dashicons' ); // For the plugin status checkboxes
		wp_enqueue_style( 'alertify-core' );
		wp_enqueue_style( 'alertify-default' );
	}

	public function print_scripts() {
		global $plugin_page;

		// If the current page isn't the page being requested, we don't print those scripts
		if ( empty( $_GET['page'] ) || ! isset( $this->key ) || isset( $this->key ) && $this->key !== $_GET['page'] ) {
			return;
		}

		wp_enqueue_script( 'alertify' );
		wp_enqueue_script( 'jquery-cookie' );
		wp_enqueue_script( 'ctct-admin-page' );

		global $is_IE;
		if( $is_IE ) {
			wp_enqueue_script( 'flexibility' );
		}

		wp_localize_script( 'ctct-admin-page', 'CTCT', array(
			'component' => $this->component,
			'_wpnonce'  => wp_create_nonce( 'ctct' ),
			'id'        => $this->id,
			'text'      => array(
				'editable' => esc_js( __( 'Click to Edit', 'constant-contact-api' ) ),
				'request_failed_heading' => __('The request failed.', 'constant-contact-api'),
				'request_nothing_changed' => __('Nothing changed.', 'constant-contact-api'),

				/** translators: {code} and {message} will be dynamically replaced with error details */
				'request_error' => __( 'Error {code}: {message}', 'constant-contact-api'),
			),
		) );

		wp_enqueue_script( 'ctct-admin-inlineedit' );
	}

	public function registerScripts() {
		wp_register_style( 'ctct-admin-global', CTCT_FILE_URL . 'css/admin/ctct-admin-global.css' );
		wp_register_style( 'ctct-admin', CTCT_FILE_URL . 'css/admin/ctct-admin.css', array( 'thickbox' ) );
		wp_register_style( 'alertify-core', CTCT_FILE_URL . 'js/alertify.js/themes/alertify.core.css' );
		wp_register_style( 'alertify-default', CTCT_FILE_URL . 'js/alertify.js/themes/alertify.default.css' );
		wp_register_script( 'flexibility', CTCT_FILE_URL . 'vendor/10up/flexibility/flexibility.js' );
		wp_register_script( 'alertify', CTCT_FILE_URL . 'js/alertify.js/lib/alertify.min.js', array( 'jquery' ) );
		wp_register_script( 'jquery-cookie', CTCT_FILE_URL . 'js/admin/jquery.cookie.js', array( 'jquery' ) );

		wp_register_script( 'ctct-admin-page', CTCT_FILE_URL . 'js/admin/cc-page.js', array(
			'jquery',
			'jquery-effects-highlight',
			'jquery-ui-tooltip',
			'jquery-ui-tabs',
			'thickbox'
		) );

		wp_register_script( 'ctct-admin-inlineedit', CTCT_FILE_URL . 'js/admin/jquery.inlineedit.js', array( 'ctct-admin-page' ) );
	}

	public function addScripts() {
	}

	protected function isAdd() {
		return isset( $_GET['add'] );
	}

	protected function isEdit() {
		return isset( $_GET['edit'] ) && isset( $_GET['page'] ) && $_GET['page'] === $this->getKey();
	}

	protected function isNested() {
		return false;
	}

	protected function isSingle() {
		return isset( $_GET['view'] ) && isset( $_GET['page'] ) && $_GET['page'] === $this->getKey();
	}

	protected function isView() {
		return ! isset( $_GET['view'] ) && ! isset( $_GET['edit'] ) && ! isset( $_GET['add'] );
	}

	protected function getID() {

		if ( $this->isEdit() ) {
			return esc_attr( $_GET['edit'] );
		}

		if ( $this->isSingle() ) {
			return esc_attr( $_GET['view'] );
		}

		return NULL;
	}

	protected function getKey() {
		return $this->key;
	}

	/**
	 * Get the title of the page
	 * @return string
	 */
	protected function getTitle() {
		return $this->title;
	}

	/**
	 * Get the title of the page to show in the Admin Menu
	 * @return string
	 */
	protected function getNavTitle() {
		return $this->title;
	}

	abstract protected function add();

	abstract protected function edit();

	abstract protected function view();

	abstract protected function single();

	/**
	 * Process any forms the page has added
	 * @return mixed
	 */
	abstract protected function processForms();

	protected function addActions() {
	}

	protected function content() {
		if ( $this->isAdd() ) {
			$this->add();
		} elseif ( $this->isEdit() ) {
			$this->edit();
		} elseif ( $this->isSingle() ) {
			$this->single();
		} else {
			$this->view();
		}
	}

	/**
	 * Print exceptions
	 * @param \Ctct\Exceptions\CtctException $e
	 */
	protected function show_exception( $e ) {

		$error = KWSConstantContact::convertException( $e );

		kws_print_notices( array( $error ), 'error inline', true, esc_html__( 'There was an error displaying this content:', 'constant-contact-api' ) );
	}

	public function help_tabs( $tabs ) {
		global $pagenow;

		if ( in_array( $pagenow, array( 'edit.php', 'post-new.php' ) ) ) {
			return $tabs;
		}
		if ( ! preg_match( '/constant-contact/', $pagenow ) ) {
			return $tabs;
		}

		foreach ( $tabs as &$tab ) {
			$tab['title'] = str_replace( 'Constant Contact: ', '', $tab['title'] );
		}

		return $tabs;
	}

	public function getPermission() {
	}

	// Common method
	public function add_menu() {

		// Only add the menu if connected to Constant Contact
		if ( is_object( $this->cc ) && ! $this->cc->isConfigured() ) {
			return;
		}

		add_submenu_page( 'constant-contact-api', 'CTCT - ' . htmlentities( $this->title ), '<span id="menu-' . esc_attr( $this->getKey() ) . '">' . htmlentities( $this->getNavTitle() ) . '</span>', $this->permission, $this->key, array(
			&$this,
			'page'
		) );
	}

	public function print_notices() {

		if ( empty( $this->notices ) ) {
			return;
		}

		echo '<div class="inline errors error notice is-dismissable">';
		foreach ( $this->notices as $key => $notice ) {

			/** @var WP_Error $notice */
			if ( is_wp_error( $notice ) ) {
				$notice = esc_html( $notice->get_error_message() ) . ' (<code>error code: ' . esc_html( $notice->get_error_code() ) . '</code>)';
			}

			echo wpautop( $notice );
		}
		echo '
            </div>';

		$this->notices = array();
	}

	/**
	 * Print errors for the page
	 * @return void
	 */
	protected function print_errors() {

		if ( empty( $this->errors ) ) {
			return;
		}

		echo '
            <div id="message" class="container alert-error errors error">
                <h3>';
		_e( sprintf( '%s occurred:', _n( 'An error', 'Errors', sizeof( $this->errors ), 'constant-contact-api' ) ), 'ctct' );
		echo ' </h3>
                <ul class="ul-square">
        ';
		foreach ( $this->errors as $key => $error ) {

			if ( is_wp_error( $error ) ) {
				/** @var WP_Error $error */
				$message = esc_html( $error->get_error_message() ) . ' (<code>error code: ' . esc_html( $error->get_error_code() ) . '</code>)';
			} else {
				continue;
			}
			echo '<li>' . $message . '</li>';
		}
		echo '
                </ul>
            </div>';
	}


	/**
	 * Print output for the page
	 *
	 * @uses print_errors
	 * @uses print_notices
	 * @uses content
	 *
	 * @return void
	 */
	public function page() { ?>
		<div class="wrap ctct-wrap">
			<h2 class="cc_logo"><a class="cc_logo"
			                       href="<?php echo admin_url( 'admin.php?page=constant-contact-api' ); ?>"><?php esc_html_e( 'Constant Contact', 'constant-contact-api' ); ?></a>
			</h2>
			<?php

			echo '<h2 class="ctct-page-name">' . $this->get_page_heading() . '</h2>';

			$this->print_errors();

			$this->print_notices();

			// Show the content that's ready.
			flush();

			$this->content();

			?>
		</div>
	<?php
	}

	/**
	 * Generate the content inside the .ctct-page-name page heading (breadcrumbs and button)
	 * @since 3.2
	 * @return string HTML output for breadcrumbs and button
	 */
	private function get_page_heading() {

		$breadcrumb = array();
		$nested = $this->isNested();

		if ( ! $this->isView() ) {
			$remove_args = array( 'view', 'edit', 'add' );
			if( $nested ) { $remove_args[] = $nested; }

			$breadcrumb[] = '<a href="' . esc_url( remove_query_arg( $remove_args ) ) . '">' . $this->getNavTitle() . '</a>';
		}

		if ( $this->isEdit() || $this->isNested() ) {
			if( $this->isEdit() ) {
				$add_args = array( 'view' => $_GET['edit'] );
				$remove_args = array( 'edit' );
			} else {
				$add_args = array();
				$remove_args = array( $nested );
			}
			$breadcrumb[] = '<a href="' . esc_url( add_query_arg( $add_args, remove_query_arg( $remove_args ) ) ) . '">' . $this->getTitle( 'single' ) . '</a>';
		}

		$breadcrumb[] = $this->getTitle();

		$button = '';
		if ( $this->isSingle() && $this->can_edit ) {
			$button = ' <a href="' . esc_url( add_query_arg( array( 'edit' => $_GET['view'] ), remove_query_arg( 'view' ) ) ) . '" class="button clear edit-new-h2" title="edit">' . __( 'Edit', 'constant-contact-api' ) . '</a>';
		}
		if ( $this->isView() && $this->can_add ) {
			$button = ' <a href="' . esc_url( add_query_arg( array( 'add' => 1 ), remove_query_arg( 'status' ) ) ) . '" class="button clear edit-new-h2" title="Add" id="ctct-add-new-item">' . sprintf( _x( 'Add %s', 'General button text for adding a new Contact or List, for example.', 'constant-contact-api' ), $this->getTitle( 'single' ) ) . '</a>';
		}

		return implode( ' &raquo; ', $breadcrumb ) . $button;
	}
}