<?php
/**
 * @package CTCT\Form Designer
 */

// TODO: Convert to class
function constant_contact_is_spam() {
    global $akismet_api_host, $akismet_api_port;

    if(!function_exists('akismet_http_post') || apply_filters('disable_constant_contact_akismet', false)) { return false; }

    $fields = constant_contact_get_akismet_fields();

    //Submitting info do Akismet
    $response = akismet_http_post($fields, $akismet_api_host, '/1.1/comment-check', $akismet_api_port );

    return ( 'true' == $response[1] );
}

/**
 * Manage the results of signup forms submissions from the widget or shortcode.
 *
 * @global cc $cc
 * @return <type>
 */
function constant_contact_handle_public_signup_form() {
    global $cc;

    /**
     * Check that the form was submitted and we have an email value, otherwise return false
     */
    if(!isset($_POST['uniqueformid'], $_POST['fields']['email_address'])) {
        return false;
    }

    $form_id = isset($_POST['uniqueformid']) ? esc_html($_POST['uniqueformid']) : 0;

    /**
     * $errors array - this will contain any errors we want to add to our global for showing to the user
     */
    $errors = array();

    /**
     * $fields - Contains extra meta fields about this subscriber to send to the API
     */
    $fields = array();

    $is_spam = constant_contact_is_spam();
    if($is_spam) {
        $errors[] = array('Your submission has been identified as spam.');
    }

    $post = $_POST;
    foreach($post['fields'] as $key => $field) {
        $fields[$key] = isset($field['value']) ? esc_attr($field['value']) : esc_attr($field);
    }
    $fields['lists'] = $post['lists'];

    $Contact = new KWSContact($fields);

    foreach($_POST['fields'] as $key => $field) {

        $value = isset($field['value']) ? esc_attr($field['value']) : '';

        // If the field is required...
        if(isset($field['req']) && $field['req'] == 1) {
            if(tempty($value)) {
                if(isset($field['label']) && !empty($field['label'])) {
                    $errors[] = array('Please enter your '.$field['label'], $key);
                } else {
                    $errors[] = array('Please enter all required fields', $key);
                }
            }
        }

        if(!empty($field['value']) && $key == 'email_address' && (!is_email($field['value']) || !constant_contact_domain_exists($field['value']))) {
            $errors[] = array('Please enter a valid email address', 'constant-contact-api');
        }

    }

    if(isset($fields['StateCode']) && $fields['StateName']) {
        unset($fields['StateCode']);
    }
    if(isset($fields['CountryCode']) && strtolower($fields['CountryCode'] == 'usa')) {
        $fields['CountryCode'] = 'us';
    }

    /**
     * If we have registered errors then return them now and exit
     */
    if($errors) {
        $GLOBALS['cc_errors_'.$form_id] = $errors;
        return;
    }

    // URL to send user to upon successful subscription
    $redirect_to = !empty($_POST['cc_redirect_url']) ? urldecode($_POST['cc_redirect_url']) : false; // Added logic and urldecode in 2.1.3

    /**
     * Determine $subscribe_lists - flat array of IDs of lists that we will subscribe this user to
     */
    $subscribe_lists = array();

    $subscribe_lists = $_POST['lists'];

    /**
     * If we have nothing in $list_id's return an error and exit
     */
    if(empty($subscribe_lists)) {
        set_transient($form_id, new WP_Error('select a list', 'Please select at least 1 list.'));
        return;
    }

#   For rapid testing purposes only.
#   $fields['EmailAddress'] = str_replace('@', rand(0,22222).'@', $fields['EmailAddress']);

    /**
     * Connect to CC API and add/update the email address with the new subscriptions
     */
    $cc->set_action_type('contact'); /* important, tell CC that the contact made this action */
    $contact = $cc->query_contacts($fields['EmailAddress']);

    if($contact):
        $contact = $cc->get_contact($contact['id']);
        $status = $cc->update_contact($contact['id'], $fields['EmailAddress'], $subscribe_lists, $fields);
        $updated = true;
    else:
        $updated = false;
        $status = $cc->create_contact($fields['EmailAddress'], $subscribe_lists, $fields);
    endif;

    /**
     * If the call was unsuccessful show a generic error.
     */
    if(!$status && (int)$cc->http_response_code !== 0) {
        set_transient($form_id, new WP_Error('http response', 'Sorry there was a problem, please try again later'));
        return;
    } elseif($redirect_to) {
        set_transient($form_id, 'success');
        $redirect_to = apply_filters('constant_contact_add_success_param', true) ? add_query_arg('success', true, $redirect_to) : $redirect_to;
        header("Location: {$redirect_to}");
        exit;
    } else {
        set_transient($form_id, 'success');
    }

    // return false so we display no errors when viewing the form
    // the script should not get this far
    return false;
}


function constant_contact_domain_exists($email,$record = 'MX') {
    if(apply_filters('constant_contact_validate_email_domain', 1) && function_exists('checkdnsrr')) {
        list($user,$domain) = split('@',$email);
        return checkdnsrr($domain,$record);
    }
    return true;
}