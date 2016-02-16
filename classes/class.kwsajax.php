<?php
/**
 * @package CTCT
 * @version 3.0
 */

/**
 * Handle AJAX calls, mainly for inline admin editing
 */
class KWSAJAX {

	function __construct() {
		add_action('wp_ajax_ctct_ajax', array(&$this, 'processAjax'));
		add_action('wp_ajax_nopriv_ctct_ajax', array(&$this, 'processAjax'));
	}

	function processAjax() {
		global $wpdb; // this is how you get access to the database

		$request = stripslashes_deep( $_REQUEST );

		$id = isset( $request['id'] ) ? intval( $request['id'] ) : NULL;
		$component = isset( $request['component'] ) ? esc_html( $request['component'] ) : NULL;
		$field = isset( $request['field'] ) ? esc_attr( $request['field'] ) : NULL;
		$value = isset( $request['value'] ) ? $request['value'] : NULL;
		$parent = isset( $request['parent'] ) ? esc_attr( $request['parent'] ) : NULL;
		$parent = !empty($parent) ? $parent.'_' : NULL;

		if(!isset($request['_wpnonce']) || isset($request['_wpnonce']) && !wp_verify_nonce($request['_wpnonce'], 'ctct') && !defined('DOING_AJAX')) {
			$response['errors'] = __('You\'re not authorized to be here.', 'constant-contact-api');
		} elseif(empty($field)) {
			$response['errors'] = __('There is no field defined.', 'constant-contact-api');
		} elseif(!isset($request['value'])) {
			$response['errors'] = __('There is no value defined.', 'constant-contact-api');
		} else {
			$KWSConstantContact = new KWSConstantContact();

			switch ($component) {
				case 'Contact':
					try {

						$KWSContact = new KWSContact($KWSConstantContact->getContact(CTCT_ACCESS_TOKEN, $id));

						// Did anything change?
						// Check unformattet, then formatted.
						$nothingChanged = ($value === $KWSContact->get($parent.$field) || $value === $KWSContact->get($parent.$field, true) );

						// Lists need to be handled slightly differently.
						if($parent.$field === 'lists') {

							// Get the lists for the contact
							$existingLists = $KWSContact->get($parent.$field, true);

							$items = $value;
							$value = array();
							$compareLists = array();
							foreach ($items as $key => $item) {
								$value[] = new KWSContactList(array('id' => $item['value']));
								$compareLists[] = $item['value'];
							}

							// If nothing changed, the arrays should be the same
							// and the diff should be empty
							$diff = kws_array_diff($existingLists, $compareLists);
							$nothingChanged = empty($diff);
						}

						if($nothingChanged) {
							$response['message'] = __('Nothing changed.', 'constant-contact-api');
							$response['code'] = 204;
						} else {
							$updatable = $KWSContact->set($parent.$field, $value);
							if(!$updatable) {
								$response['message'] = __('This field is not updatable.', 'constant-contact-api');
								$response['code'] = 400;
							} else {
								$fetch = $KWSConstantContact->updateContact(CTCT_ACCESS_TOKEN, $KWSContact);
								$response['message'] = __('Successfully updated.', 'constant-contact-api');
								$response['code'] = 200;

								delete_transient('ctct_all_contacts');

								/**
								 * Set this so that next time the user refreshes the contact page,
								 * CTCT_Admin_Contacts::single() will catch it and force refresh.
								 *
								 * @see CTCT_Admin_Contacts::single()
								 */
								add_option('ctct_refresh_contact_'.$KWSContact->get('id'), 1);
							}
						}
					} catch(Exception $e) {
						$response['message'] = $e->getErrors();
						$response['code'] = 400;
					}
					break;
				case 'ContactList':
					try {
						$KWSList = new KWSContactList($KWSConstantContact->getList(CTCT_ACCESS_TOKEN, $id));
						if($value === $KWSList->get($field)) {
							$response['message'] = __('Nothing changed.', 'constant-contact-api');
							$response['code'] = 204;
						} else {
							$updatable = $KWSList->set($field, $value);
							if(!$updatable) {
								$response['message'] = __('This field is not updatable.', 'constant-contact-api');
								$response['code'] = 400;
							} else {
								$fetch = $KWSConstantContact->updateList(CTCT_ACCESS_TOKEN, $KWSList);
								$response['message'] = __('Successfully updated.', 'constant-contact-api');
								$response['code'] = 200;

								delete_transient('ctct_all_lists');
							}
						}
					} catch(\Ctct\Exceptions\CtctException $e) {
						$response['message'] = $e->getErrors();
						$response['code'] = 400;
					}
					break;
				default:
					$response['message'] = __('There is no component defined.', 'constant-contact-api');
					$response['code'] = 400;
					break;
			}
		}

		wp_die(json_encode($response));
	}

}

new KWSAJAX();