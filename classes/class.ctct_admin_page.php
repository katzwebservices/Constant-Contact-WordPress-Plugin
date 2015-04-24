<?php


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

        if( !is_admin() && !$force_load ) { return; }

    	$this->addIncludes();

        $WP_CTCT = WP_CTCT::getInstance();
    	$this->cc = $WP_CTCT->cc;
        $this->oauth = $WP_CTCT->oauth;


        $this->title = $this->getTitle();
        $this->key = $this->getKey();
        $this->id = $this->getID();

        if( is_admin() ) {

            $this->processForms();

            add_action('admin_menu', array(&$this, 'add_menu'));
            add_action('admin_notices', array(&$this, 'print_notices'));
            add_action('admin_print_scripts', array(&$this, 'print_scripts'));
            add_action('admin_print_scripts', array(&$this, 'addScripts'), 11 );
            add_action('admin_print_styles', array(&$this, 'print_styles'));
            add_filter( 'constant_contact_help_tabs', array(&$this, 'help_tabs'));
        }

        $this->addActions();
    }

    protected function addIncludes() {}

    // TODO: Only load on CTCT pages.
    public function print_styles() {

        // If the current page isn't the page being requested, we don't print those scripts
        if( empty($_GET['page']) || $this->key !== $_GET['page'] ){ return; }

        wp_enqueue_style('constant-contact-api-admin', CTCT_FILE_URL.'css/admin/constant-contact-admin-css.css', array('thickbox'));
        wp_enqueue_style('dashicons'); // For the plugin status checkboxes
        wp_enqueue_style('alertify-core', CTCT_FILE_URL.'js/alertify.js/themes/alertify.core.css');
        wp_enqueue_style('alertify-default', CTCT_FILE_URL.'js/alertify.js/themes/alertify.default.css');
        wp_enqueue_style('select2', CTCT_FILE_URL.'vendor/nineinchnick/select2/assets/select2.css');
    }

    public function print_scripts() {
        global $plugin_page;

        // If the current page isn't the page being requested, we don't print those scripts
        if( empty($_GET['page']) || $this->key !== $_GET['page'] ){ return; }

        wp_enqueue_script('alertify', CTCT_FILE_URL.'js/alertify.js/lib/alertify.min.js', array('jquery'));
        wp_enqueue_script('jquery-cookie', CTCT_FILE_URL.'js/admin/jquery.cookie.js', array('jquery'));
        wp_enqueue_script('select2', CTCT_FILE_URL.'vendor/nineinchnick/select2/assets/select2.min.js', array('jquery'));

        wp_enqueue_script('ctct-admin-page', CTCT_FILE_URL.'js/admin/cc-page.js', array('jquery', 'jquery-effects-highlight', 'jquery-ui-tooltip', 'jquery-ui-tabs', 'select2', 'thickbox'));

        wp_localize_script( 'ctct-admin-page', 'CTCT', array(
            'component' => $this->component,
            '_wpnonce' => wp_create_nonce( 'ctct' ),
            'id' => $this->id,
            'text' => array(
                'editable' => esc_js( __('Click to Edit', 'ctct') ),
            ),
        ));

        wp_enqueue_script('ctct-admin-fittext', CTCT_FILE_URL.'js/admin/jquery.fittext.js', array('ctct-admin-page'));
        wp_enqueue_script('ctct-admin-equalize', CTCT_FILE_URL.'js/admin/jquery.equalize.min.js', array('ctct-admin-page'));
        wp_enqueue_script('ctct-admin-inlineedit', CTCT_FILE_URL.'js/admin/jquery.inlineedit.js', array('ctct-admin-page'));
    }

    public function addScripts() {}
    protected function isAdd() { return isset($_GET['add']); }
    protected function isEdit() { return isset($_GET['edit']); }
    protected function isSingle() { return isset($_GET['view']); }
    protected function isView() { return !isset($_GET['view']) && !isset($_GET['edit']) && !isset($_GET['add']); }

    protected function getID() {

        if( $this->isEdit() ) {
            return esc_attr( $_GET['edit'] );
        }

        if( $this->isSingle() ) {
            return esc_attr( $_GET['view'] );
        }

        return null;
    }

    protected function getKey() {
        return $this->key;
    }
    protected function getTitle() {
        return $this->title;
    }
    protected function getNavTitle() {
        return $this->title;
    }

    abstract protected function add();
    abstract protected function edit();
    abstract protected function view();
    abstract protected function single();
    abstract protected function processForms();
    protected function addActions() {}

    protected function content() {
        if($this->isAdd()) {
        	$this->add();
    	} else if($this->isEdit()) {
        	$this->edit();
    	} else if($this->isSingle()) {
        	$this->single();
    	} else {
            $this->view();
        }
    }

    public function help_tabs($tabs) {
        global $pagenow;

        if( in_array( $pagenow, array( 'edit.php', 'post-new.php' )) ) {
            return $tabs;
        }
        if( !preg_match('/constant-contact/', $pagenow ) ) {
            return $tabs;
        }

    	foreach($tabs as &$tab) {
    		$tab['title'] = str_replace('Constant Contact: ', '', $tab['title']);
    	}
    	return $tabs;
    }

    public function getPermission() {}

    // Common method
    public function add_menu() {

        // Only add the menu if connected to Constant Contact
        if( is_object( $this->cc ) && !$this->cc->isConfigured() ) { return; }

    	add_submenu_page( 'constant-contact-api', 'CTCT - '.htmlentities($this->title), '<span id="menu-'.esc_attr($this->getKey()).'">'.htmlentities($this->getNavTitle()).'</span>', $this->permission, $this->key, array(&$this, 'page'));
    }

    public function print_notices() {

        if( empty($this->notices) ) { return; }

        echo '
            <div id="message" class="container alert-error errors error notice-dismiss">';
            foreach($this->notices as $key => $notice ) {

	            /** @var WP_Error $notice */
	            if( is_wp_error( $notice ) ) {
		            $notice = esc_html( $notice->get_error_message() ).' (<code>error code: '.esc_html($notice->get_error_code()).'</code>)';
	            }

                echo wpautop( $notice );
            }
        echo '
            </div>';

        $this->notices = array();
    }

    protected function print_errors() {

        if(empty($this->errors)) { return; }

        echo '
            <div id="message" class="container alert-error errors error">
                <h3>';
                _e(sprintf('%s occurred:', _n( 'An error', 'Errors', sizeof($this->errors), 'ctct')), 'ctct');
        echo ' </h3>
                <ul class="ul-square">
        ';
            foreach($this->errors as $key => $error) {

	            if( is_wp_error( $error ) ) {
		            $message = esc_html($error->get_error_message()).' (<code>error code: '.esc_html($error->get_error_code()).'</code>)';
	            } else {
		            continue;
	            }
                echo '<li>'.$message.'</li>';
            }
        echo '
                </ul>
            </div>';
    }


    // Common method
    public function page() { ?>
        <div class="wrap">
            <h2 class="cc_logo"><a class="cc_logo" href="<?php echo admin_url('admin.php?page=constant-contact-api'); ?>"><?php _e('Constant Contact', 'ctct'); ?></a></h2>
	<?php

        if(!$this->isView()) {
            $breadcrumb[] = '<a href="'.esc_url( remove_query_arg(array('view', 'edit', 'add'))).'">'.$this->getNavTitle().'</a>';
        }

        if($this->isEdit()) {
            $breadcrumb[] = '<a href="'.esc_url( add_query_arg(array('view' => $_GET['edit']), remove_query_arg(array('edit')))).'">'.$this->getTitle('single').'</a>';
        }

        $breadcrumb[] = $this->getTitle();

        $button = '';
        if($this->isSingle() && $this->can_edit) {
            $button = ' <a href="'.esc_url( add_query_arg(array('edit' => $_GET['view']), remove_query_arg('view'))).'" class="button clear edit-new-h2" title="edit">'.__('Edit', 'ctct').'</a>';
        }
        if($this->isView() && $this->can_add) {
            $button = ' <a href="'.esc_url( add_query_arg(array('add' => 1), remove_query_arg('status'))).'" class="button clear edit-new-h2" title="Add" id="ctct-add-new-item">'.sprintf(_x('Add %s', 'General button text for adding a new Contact or List, for example.', 'ctct'), $this->getTitle('single')).'</a>';
        }

    	echo '<h2 class="ctct-page-name">'.implode(' &raquo; ', $breadcrumb).$button.'</h2>';

        $this->print_errors();

        $this->print_notices();

        // Show the content that's ready.
        flush();

 		$this->content();

 	?>
 		</div>
 	<?php
    }
}