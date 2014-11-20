<?php

class CTCT_Admin_User_Profile extends CTCT_Admin_Page {

	var $subscribe_method;

	function add_menu() { return; }
	function add() {}
	function edit() {}
	function view() {}
	function single() {}
	function processForms() {}

	function addActions() {
		// Get our "User Subscription Method" option value.
		$this->subscribe_method = CTCT_Settings::get('profile_page_form');

		// If it is disabled exit this function
		if($this->subscribe_method) {

			// register user update action
			add_action('profile_update', array(&$this, 'update'));

		}

		// register show user update form action
		add_action('show_user_profile', array(&$this, 'display'), 1);
		add_action('edit_user_profile', array(&$this, 'display'), 1);
	}

	/**
	 * Hook into profile_update action to update our user subscription info if necessary
	 *
	 * @param <type> $user
	 * @return <type>
	 */
	function update($user) {

		r($_POST, true, 'profile.php line 39');

		$email = get_user_option( 'user_email', $user );

		$selected_lists = array();

		if(isset($_POST['cc_newsletter'])) {
			$lists = (is_array($_POST['cc_newsletter'])) ? $_POST['cc_newsletter'] : array();
			$fields = get_option('cc_extra_fields');
			$field_mappings = constant_contact_build_field_mappings();

			// get contact and selected lists
			$contact = $cc->query_contacts($email);

			if($subscribe_method == 'checkbox' && isset($_POST['cc_newsletter']) && !is_array($_POST['cc_newsletter'])) {
				$lists = get_option('cc_lists');
			}

			// parse custom fields
			$extra_fields = array();
			if(is_array($fields)) {
				foreach($fields as $field) {
					$fieldname = str_replace(' ','', $field);
					if(isset($field_mappings[$fieldname]) && isset($_POST[$field_mappings[$fieldname]])) {
						$extra_fields[$fieldname] = $_POST[$field_mappings[$fieldname]];
					}
				}
			}

			// Kind of sanitize the input
			foreach($lists as $key => $list) { if(!is_numeric($list)) { unset($lists["{$key}"]); } }

			$cc->set_action_type('contact');

			if($contact) {
				$status = $cc->update_contact($contact['id'], $email, $lists, $extra_fields);
			} else {
				$status = $cc->create_contact($email, $lists, $extra_fields);
			}
			if(!$status):
				//echo constant_contact_last_error($cc->http_response_code);
				return;
			endif;
		} else {
			$contact = $cc->query_contacts($email);
			$cc->set_action_type('contact');

			if($contact) {
				$status = $cc->update_contact($contact['id'], $email, array());
			}
		}
	}

	/**
	 * Hook into show_user_profile action to display our user subscription settings if necessary
	 *
	 * @global  $cc
	 * @param <type> $user
	 * @return <type>
	 */
	function display($user) {

		$Contact = new KWSContact($this->cc->getContactByEmail($user->data->user_email));

		if($Contact && current_user_can('edit_users') && !isset($_GET['debug-user-display'])) {
			echo sprintf(__('
				<p><img src="%s" width="225" height="33" alt="Constant Contact" class="block" /><a href="%s">Admin-Only: Edit this User\'s Details</a> %s</p>
			', 'ctct'),
				plugins_url('images/admin/logo-horizontal.png', CTCT_FILE),
				admin_url('admin.php?page=constant-contact-contacts&amp;edit='.$Contact->id),
				constant_contact_tip(__('Users will not see this link or the Constant Contact logo.', 'ctct'), false)
			);

		}

		if(!$this->subscribe_method) { return; }

		$register_page_method = CTCT_Settings::get('profile_page_form');

		// Prepare the description from the settings screen
		$signup_description =  CTCT_Settings::get('signup_description');
		if ($signup_description)	:
			$signup_description = wpautop ($signup_description);
			$signup_description = "<div class='description'>$signup_description</div>";
		endif;

	?>
		<h3><?php echo CTCT_Settings::get('signup_title');?></h3>
		<?php echo $signup_description;?>

		<p><?php

			$lists = (array)$Contact->get('lists', true);
			echo KWSContactList::outputHTML('all', array('checked' => $lists));
		?></p>
		<br />
	<?php
	}
}

new CTCT_Admin_User_Profile;