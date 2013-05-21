<?php


abstract class CTCT_Admin_Page {

    var $key;
    var $title;
    var $permission = 'manage_options';
    var $cc;
    var $oauth;
    var $errors;
    var $can_edit = false;
    var $can_add = false;
    var $component = '';
    var $pointers = array();

    function __construct() {

    	$this->addIncludes();

        $WP_CTCT = WP_CTCT::getInstance();
    	$this->cc = $WP_CTCT->cc;
        $this->oauth = $WP_CTCT->oauth;

        $this->title = $this->getTitle();
    	$this->key = $this->getKey();
    	$this->processForms();

        add_action('admin_menu', array(&$this, 'add_menu'));

        if(is_admin()) {
            add_action('admin_print_scripts', array(&$this, 'print_scripts'));
            add_action('admin_print_styles', array(&$this, 'print_styles'));
        }

        add_filter( 'ctct_admin_pointers-'.$this->getKey(), array(&$this, 'pointer_content'));
        
        add_filter( 'constant_contact_help_tabs', array(&$this, 'help_tabs'));

        $this->addActions();
    }

    protected function addIncludes() {}

    // TODO: Only load on CTCT pages.
    public function print_styles() {
        wp_enqueue_style('qtip', CTCT_FILE_URL.'css/admin/jquery.qtip.min.css');

        wp_enqueue_style('constant-contact-api-admin', CTCT_FILE_URL.'css/admin/constant-contact-admin-css.css', array('thickbox'));
        wp_enqueue_style('alertify-core', CTCT_FILE_URL.'js/alertify.js/themes/alertify.core.css');
        wp_enqueue_style('alertify-default', CTCT_FILE_URL.'js/alertify.js/themes/alertify.default.css');
        wp_enqueue_style('select2', CTCT_FILE_URL.'css/select2/select2.css');
        wp_enqueue_style( 'wp-pointer' );
    }

    public function print_scripts() {
        global $plugin_page;

        wp_enqueue_script('alertify', CTCT_FILE_URL.'js/alertify.js/lib/alertify.min.js', array('jquery'));
        wp_enqueue_script('jquery-cookie', CTCT_FILE_URL.'js/admin/jquery.cookie.js', array('jquery'));
        wp_enqueue_script('select2', CTCT_FILE_URL.'js/select2/select2.min.js', array('jquery'));

        wp_enqueue_script('ctct-admin-page', CTCT_FILE_URL.'js/admin/cc-page.js', array('jquery', 'jquery-effects-highlight', 'jquery-ui-tabs', 'select2', 'thickbox', 'wp-pointer'));

        wp_enqueue_script('qtip', CTCT_FILE_URL.'js/admin/jquery.qtip.pack.js', array('ctct-admin-page'));
        wp_enqueue_script('ctct-admin-fittext', CTCT_FILE_URL.'js/admin/jquery.fittext.js', array('ctct-admin-page'));
        wp_enqueue_script('ctct-admin-equalize', CTCT_FILE_URL.'js/admin/jquery.equalize.min.js', array('ctct-admin-page'));
        wp_enqueue_script('ctct-admin-inlineedit', CTCT_FILE_URL.'js/admin/jquery.inlineedit.js', array('ctct-admin-page'));

        if($plugin_page === $this->key) {
            wp_localize_script( 'ctct-admin-page', 'CTCT', array(
                'component' => $this->component,
                'id' => @$_GET['view'],
                '_wpnonce' => wp_create_nonce('ctct'),
                'pointers' => $this->getPointers(),
            ));
        }

        $this->addScripts();
    }

    protected function addScripts() {}
    protected function isAdd() { return isset($_GET['add']); }
    protected function isEdit() { return isset($_GET['edit']); }
    protected function isSingle() { return isset($_GET['view']); }
    protected function isView() { return !isset($_GET['view']) && !isset($_GET['edit']) && !isset($_GET['add']); }

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
        Exceptional::$controller = $this->title;
    	if($this->isAdd()) {
            Exceptional::$action = 'add';
    		$this->add();
    	} else if($this->isEdit()) {
            Exceptional::$action = 'edit';
    		$this->edit();
    	} else if($this->isSingle()) {
            Exceptional::$action = 'single';
    		$this->single();
    	} else {
            Exceptional::$action = 'view';
            $this->view();
        }
    }

    public function help_tabs($tabs) {
    	foreach($tabs as &$tab) {
    		$tab['title'] = str_replace('Constant Contact: ', '', $tab['title']);
    	}
    	return $tabs; 
    }

    public function pointer_content() {
    	global $plugin_page;

    	include_once(CTCT_DIR_PATH.'/inc/pointers.php');

    	$pointers = ctct_get_pointers();

    	if(isset($_GET['pointers']) && $_GET['pointers'] === 'debug' && current_user_can('manage_options')){
	    	// TODO: REmove this; it's only for debug
	    	foreach((array)$pointers as $k => $v) {
	    		$pointers[rand(0,1000).$k] = $v;
	    		unset($pointers[$k]);
	    	}
    	}
    	return apply_filters('constant_contact_pointers', $pointers);

    }

    public function getPointers( $hook_suffix = '' ) {
    	global $plugin_page;
    	if($plugin_page !== $this->getKey()) { return; }

       // Get pointers for this screen
        $pointers = apply_filters( 'ctct_admin_pointers-' . $this->getKey(), $this->pointers );
        
        if ( ! $pointers || ! is_array( $pointers ) )
            return;

        // Get dismissed pointers
        $dismissed = explode( ',', (string)get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
        $valid_pointers =array();

        // Check pointers and remove dismissed ones.
        foreach ( $pointers as $pointer_id => $pointer ) {

            // Sanity check
            if ( in_array( $pointer_id, $dismissed ) || empty( $pointer )  || empty( $pointer_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) )
                continue;

            $pointer['pointer_id'] = $pointer_id;

            // Add the pointer to $valid_pointers array
            $valid_pointers['pointers'][] =  $pointer;
        }
        #r($valid_pointers, true);
        
        return (array)$valid_pointers;
    }

    public function getPermission() {}

    // Common method
    public function add_menu() {

        // Only add the menu if connected to Constant Contact
        if(!$this->cc->isConfigured()) { return; }

    	add_submenu_page( 'constant-contact-api', 'CTCT - '.htmlentities($this->title), '<span id="menu-'.esc_attr($this->getKey()).'">'.htmlentities($this->getNavTitle()).'</span>', $this->permission, $this->key, array(&$this, 'page'));
    }

    protected function print_errors() {

        if(empty($this->errors)) { return; }

        echo '
            <div id="message" class="container alert-error errors error">
                <h3>';
                _e(sprintf('%s occurred:', _n( 'An error', 'Errors', sizeof($this->errors), 'constant-contact-api' )), 'constant-contact-api');
        echo ' </h3>
                <ul class="ul-square">
        ';
            foreach($this->errors as $key => $error) {

                echo '<li>'.esc_html($error->get_error_message()).' (<code>error code: '.esc_html($error->get_error_code()).'</code>)</li>';
            }
        echo '
                </ul>
            </div>';
    }


    // Common method
    public function page() { ?>

        <div class="wrap">
            <h2 class="cc_logo"><a class="cc_logo" href="<?php echo admin_url('admin.php?page=constant-contact-api'); ?>"><?php _e('Constant Contact', 'constant-contact-api'); ?></a></h2>
	<?php

        if(!$this->isView()) {
            $breadcrumb[] = '<a href="'.remove_query_arg(array('view', 'edit', 'add')).'">'.$this->getNavTitle().'</a>';
        }

        if($this->isEdit()) {
            $breadcrumb[] = '<a href="'.add_query_arg(array('view' => $_GET['edit']), remove_query_arg(array('edit'))).'">'.$this->getTitle('single').'</a>';
        }

        $breadcrumb[] = $this->getTitle();

        $button = '';
        if($this->isSingle() && $this->can_edit) {
            $button = ' <a href="'.add_query_arg(array('edit' => $_GET['view']), remove_query_arg('view')).'" class="button clear edit-new-h2" title="edit">'.__('Edit', 'constant-contact-api').'</a>';
        }
        if($this->isView() && $this->can_add) {
            $button = ' <a href="'.add_query_arg(array('add' => 1), remove_query_arg('status')).'" class="button clear edit-new-h2" title="Add" id="ctct-add-new-item">'.sprintf(__('Add %s', 'constant-contact-api'), $this->getTitle('single')).'</a>';
        }

    	echo '<h2>'.implode(' &raquo; ', $breadcrumb).$button.'</h2>';
            $this->print_errors();

 			$this->content();

 	?>
 		</div>
 	<?php
        include(CTCT_DIR_PATH.'views/admin/view.page-menu.php');
    }
}