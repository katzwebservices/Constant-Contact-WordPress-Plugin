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

        add_filter( 'constant_contact_help_tabs', array(&$this, 'help_tabs'));

        $this->addActions();
    }

    protected function addIncludes() {}

    // TODO: Only load on CTCT pages.
    public function print_styles() {
        wp_enqueue_style('constant-contact-api-admin', CTCT_FILE_URL.'css/admin/constant-contact-admin-css.css', array('thickbox'));
        wp_enqueue_style('alertify-core', CTCT_FILE_URL.'js/alertify.js/themes/alertify.core.css');
        wp_enqueue_style('alertify-default', CTCT_FILE_URL.'js/alertify.js/themes/alertify.default.css');
        wp_enqueue_style('select2', CTCT_FILE_URL.'vendor/nineinchnick/select2/assets/select2.css');
    }

    public function print_scripts() {
        global $plugin_page;

        wp_enqueue_script('alertify', CTCT_FILE_URL.'js/alertify.js/lib/alertify.min.js', array('jquery'));
        wp_enqueue_script('jquery-cookie', CTCT_FILE_URL.'js/admin/jquery.cookie.js', array('jquery'));
        wp_enqueue_script('select2', CTCT_FILE_URL.'vendor/nineinchnick/select2/assets/select2.min.js', array('jquery'));

        wp_enqueue_script('ctct-admin-page', CTCT_FILE_URL.'js/admin/cc-page.js', array('jquery', 'jquery-effects-highlight', 'jquery-ui-tooltip', 'jquery-ui-tabs', 'select2', 'thickbox'));

        wp_enqueue_script('ctct-admin-fittext', CTCT_FILE_URL.'js/admin/jquery.fittext.js', array('ctct-admin-page'));
        wp_enqueue_script('ctct-admin-equalize', CTCT_FILE_URL.'js/admin/jquery.equalize.min.js', array('ctct-admin-page'));
        wp_enqueue_script('ctct-admin-inlineedit', CTCT_FILE_URL.'js/admin/jquery.inlineedit.js', array('ctct-admin-page'));

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

        include(CTCT_DIR_PATH.'views/admin/view.page-menu.php');

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

        // Show the content that's ready.
        flush();

 		$this->content();

 	?>
 		</div>
 	<?php
    }
}