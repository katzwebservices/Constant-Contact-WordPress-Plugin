<?php
/**
 * Add the embed button on the post & page editor for the EventSpot functionality
 */

//Adding "embed form" button
add_action('media_buttons', 'eventspot_add_form_button', 20);
add_action('admin_footer',  'eventspot_add_mce_popup');

/**
 * Action target that adds the "Insert Form" button to the post/page edit screen
 */
function eventspot_add_form_button(){
	global $pagenow;

    $is_post_edit_page = in_array($pagenow, array('post.php', 'page.php', 'page-new.php', 'post-new.php'));
    if(!$is_post_edit_page)
        return;

    // do a version check for the new 3.5 UI
    $version = get_bloginfo('version');

    if ($version < 3.5) {
        // show button for v 3.4 and below
        $image_btn = GFCommon::get_base_url() . "/images/form-button.png";
        echo '<a href="#TB_inline?width=600&amp;height=750&amp;inlineId=select_eventspot_event" class="thickbox" id="add_gform" title="' . __("Add Gravity Form", 'ctct') . '"><img src="'.$image_btn.'" alt="' . __("Add Gravity Form", 'ctct') . '" /></a>';
    } else {
        // display button matching new UI
        echo '<style>
        		.eventspot_media_icon{
                	background:url(' . EVENTSPOT_FILE_URL . '/images/eventspot-icon.png) no-repeat top right;
	                display: inline-block;
	                height: 16px;
	                margin: 0 2px 0 0;
	                vertical-align: text-top;
	                width: 16px;
                }
                .wp-core-ui a.eventspot_media_link{
                 	padding-left: .4em;
                }
                a.eventspot_media_link:hover .eventspot_media_icon {
                	background-position: top left;
                }
             </style>
              <a href="#TB_inline?width=600&amp;height=750&amp;inlineId=select_eventspot_event" class="thickbox button eventspot_media_link" id="add_gform" title="' . __("Add a EventSpot&trade; Event", 'ctct') . '"><span class="eventspot_media_icon"></span> ' . __("Add Event", 'ctct') . '</a>';
    }
}


/**
 * Action target that displays the popup to insert a form to a post/page
 */
function eventspot_add_mce_popup(){
    global $pagenow;

    if( empty( $pagenow ) || !in_array( $pagenow, array('post-new.php', 'post.php' )) ) {
        return;
    }

    echo kws_ob_include(EVENTSPOT_FILE_PATH.'views/embed-form.php');
}