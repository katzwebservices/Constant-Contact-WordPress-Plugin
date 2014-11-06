<?php
/**
 * @package CTCT\Form Designer
 */
include_once('form-designer-functions.php');

function cc_form_meta_box_actions($post, $metabox=array()) {
	global $cc_form_selected_id;
	$form = $metabox['args'][0];
	?>
	<div id="submitpost" class="submitbox">
		<div id="minor-publishing">
			<p><label class="form-preview-label menu-name-label open-label" for="form-name">
				<span><?php _e('Form Name'); constant_contact_tip(__('Only for internal use - the outside world won\'t see this name.', 'constant-contact-api')); ?></span>
				<input name="form-name" id="form-name" type="text" class="widefat text <?php if ( $cc_form_selected_id == -1 ) {  ?> input-with-default-title<?php } ?>" title="<?php echo esc_attr_e('Enter form name here', 'constant-contact-api'); ?>" value="<?php echo isset( $form['form-name'] ) ? esc_attr( $form['form-name']  ) : ''; ?>" />
			</label></p>
			<?php if($cc_form_selected_id >= 0) { ?>

			<div class="block">
				<h4 class="smallmarginbottom"><?php _e('Form ID:'); ?> <code style="font-size:1.2em;"><?php _e($cc_form_selected_id); ?></code></h4>
				<span class="howto"><?php echo sprintf(__('In a post or page, add the following code: <code>[constantcontactapi formid="%d"]</code> <a href="#tab-panel-ctct-insert-form" rel="wp-help">Learn More</a></span>', 'constant-contact-api'), (int)$cc_form_selected_id); ?>
			</div>
			<?php } ?>
			<div class="block">
				<h4 class="large smallmarginbottom"><?php _e('Use Form Styler?', 'constant-contact-api'); constant_contact_tip(__('Use the form styler to change colors and design of your form. If not used, the form will be styled using your theme\'s&nbsp;defaults.', 'constant-contact-api')); ?></h4>
				<div class="switch">

						<input type="checkbox" name="toggledesign" id="toggledesign" <?php ctct_check_checkbox($form, 'toggledesign', 'yes', 'yes'); ?> />
						<label for="toggledesign"><span class="hide-if-js"><?php _e('Customize Form Style?', 'constant-contact-api'); ?></span><i></i></label>
				</div>
			</div>

			<div class="clear"></div>
		</div>
		<div id="major-publishing-actions">
			<div id="publishing-action">
				<input class="button button-primary button-large menu-save" name="save_form" type="submit" value="<?php ($cc_form_selected_id != 0 && empty($cc_form_selected_id)) ? esc_attr_e('Create Form', 'constant-contact-api') : esc_attr_e('Save Form', 'constant-contact-api'); ?>" />
			</div><!-- END .publishing-action -->
			<?php if ( $cc_form_selected_id != -1 ) {  ?>
			<div id="delete-action">
				<a class="submitdelete deletion menu-delete" href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=constant-contact-forms&action=delete&amp;form=' . $cc_form_selected_id), 'delete-cc_form-' . $cc_form_selected_id ) ); ?>"  onclick="return confirm('<?php _e('Are you sure you want to delete this form? It will be deleted permanently.', 'constant-contact-api'); ?>');"><?php _e('Delete Form', 'constant-contact-api'); ?></a>
			</div><!-- END .delete-action -->
			<?php  } ?>
			<div class="clear"></div>
		</div><!-- END .major-publishing-actions -->
	</div><!-- END #submitpost .submitbox -->
	<?php
}

function cc_form_meta_box_formlists_select($post, $metabox=array()) {
	$form = $metabox['args'][0];

	$checkedArray = !empty($form['lists']) ? $form['lists'] : NULL;

	$output = KWSContactList::outputHTML('all', array('checked' => $checkedArray, 'type' => 'checkboxes'));
?>
<div class="posttypediv lists-meta-box">
	<h4 class="smallmarginbottom"><?php _e('Lists', 'consatnt-contact-api'); constant_contact_tip(sprintf('Contacts will be added to the selected lists by default. You can override this selection when you configure a Form Designer widget. You can also specify different list IDs when inserting a form into content using the <code>[constantcontactapi%s]</code> shortcode.', ($form['cc-form-id'] > -1 ? ' formid="'.$form['cc-form-id'].'"' : ''))); ?></h4>
	<div id="formfields-select-most" class="tabs-panel tabs-panel-active">
		<ul id="formfieldslist-most" class="categorychecklist form-no-clear">
		<?php
			echo $output;
		?>
		</ul>
	</div>
	<h4 class="smallmarginbottom"><?php _e('List Selection Format', 'constant-contact-api'); ?></h4>
	<ul class="list-selection-format">
		<li><label><input type="radio" name="list_format" <?php ctct_check_radio($form,'list_format', 'checkbox'); ?> /> <?php _e('Opt-in Checkbox', 'consatnt-contact-api'); ?></label></li>
		<li><label><input type="radio" name="list_format" <?php ctct_check_radio($form,'list_format', 'dropdown'); ?> /> <?php _e('Dropdown List', 'consatnt-contact-api'); ?></label></li>
		<li><label><input type="radio" name="list_format" <?php ctct_check_radio($form,'list_format', 'hidden', true); ?> /> <?php _e('Hidden', 'consatnt-contact-api'); ?></label></li>
	</ul>
	<p class="description"><?php _e('This controls what kind of list is shown. <a href="#listTypeInfo" class="moreInfo">More info</a>', 'constant-contact-api'); ?></p>

	<h4 class="smallmarginbottom"><?php _e('Checked by default?', 'constant-contact-api'); ?></h4>
	<label for="checked_by_default" class="description toggle_comment_form">
		<input name="checked_by_default" id="checked_by_default" type="checkbox" value="true" checked="checked" style="margin-right:.25em;" /><?php _e('Should the checkbox be checked by default?', 'constant-contact-api'); ?>
	</label>

</div>
<?php
}

function cc_form_meta_box_formfields_select($post, $metabox=array()) {

	$form = $metabox['args'][0];
	$checkedArray = !empty($form['formfields']) ? $form['formfields'] : array();
	$checkedArray['email_address'] = 'email_address';
?>
<div class="posttypediv">
	<ul id="formfields-select-tabs" class="formfields-select-tabs add-menu-item-tabs">
		<li class="tabs"><a href="#formfields-select-most" class="nav-tab-link"><?php _e('Most Used', 'constant-contact-api'); ?></a></li>
		<li><a href="#formfields-select-all" class="nav-tab-link"><?php _e('Other Fields', 'constant-contact-api'); ?></a></li>
	</ul>
	<div id="formfields-select-most" class="tabs-panel tabs-panel-active">
		<ul id="formfieldslist-most" class="categorychecklist form-no-clear">
		<?php
			$formfields = array();
			$formfields[] = array('email_address', __('Email Address', 'constant-contact-api'), true);
			$formfields[] = array('intro', __('Form Text', 'constant-contact-api'), true);
			$formfields[] = array('first_name', __('First Name', 'constant-contact-api'), true);
			$formfields[] = array('last_name', __('Last Name', 'constant-contact-api'), true);
			$formfields[] = array('Go', __('Submit', 'constant-contact-api'), true);
			$formfields[] = array('home_phone', __('Home Phone', 'constant-contact-api'), false);
			$formfields[] = array('work_phone', __('Work Phone', 'constant-contact-api'), false);
			$formfields[] = array('lists', __('Lists', 'constant-contact-api'), true);
			echo make_formfield_list_items($formfields, $checkedArray, 'formfields');
		?>
		</ul>
	</div>
	<div id="formfields-select-all" class="tabs-panel">
		<ul id="formfieldslist-all" class="categorychecklist form-no-clear">
		<?php
			$formfields = array();
			$formfields[] = array('middle_name', __('Middle Name', 'constant-contact-api'), false);
			$formfields[] = array('company_name', __('Company Name', 'constant-contact-api'), false);
			$formfields[] = array('job_title', __('Job Title', 'constant-contact-api'), false);
			$formfields[] = array('address_line1', __('Address Line 1', 'constant-contact-api'), false);
			$formfields[] = array('address_line2', __('Address Line 2', 'constant-contact-api'), false);
			$formfields[] = array('address_line3', __('Address Line 3', 'constant-contact-api'), false);
			$formfields[] = array('address_city', __('City Name', 'constant-contact-api'), false);
			$formfields[] = array('address_state_code', __('State Code', 'constant-contact-api'), false);
			$formfields[] = array('address_state_name', __('State Name', 'constant-contact-api'), false);
			$formfields[] = array('address_country_code', __('Country Code', 'constant-contact-api'), false);
			$formfields[] = array('address_postal_code', __('ZIP Code', 'constant-contact-api'), false);
			$formfields[] = array('address_sub_postal_code', __('Sub ZIP Code', 'constant-contact-api'), false);
			$formfields[] = array('CustomField1', __('Custom Field 1', 'constant-contact-api'), false);
			$formfields[] = array('CustomField2', __('Custom Field 2', 'constant-contact-api'), false);
			$formfields[] = array('CustomField3', __('Custom Field 3', 'constant-contact-api'), false);
			$formfields[] = array('CustomField4', __('Custom Field 4', 'constant-contact-api'), false);
			$formfields[] = array('CustomField5', __('Custom Field 5', 'constant-contact-api'), false);
			$formfields[] = array('CustomField6', __('Custom Field 6', 'constant-contact-api'), false);
			$formfields[] = array('CustomField7', __('Custom Field 7', 'constant-contact-api'), false);
			$formfields[] = array('CustomField8', __('Custom Field 8', 'constant-contact-api'), false);
			$formfields[] = array('CustomField9', __('Custom Field 9', 'constant-contact-api'), false);
			$formfields[] = array('CustomField10', __('Custom Field 10', 'constant-contact-api'), false);
			$formfields[] = array('CustomField11', __('Custom Field 11', 'constant-contact-api'), false);
			$formfields[] = array('CustomField12', __('Custom Field 12', 'constant-contact-api'), false);
			$formfields[] = array('CustomField13', __('Custom Field 13', 'constant-contact-api'), false);
			$formfields[] = array('CustomField14', __('Custom Field 14', 'constant-contact-api'), false);
			$formfields[] = array('CustomField15', __('Custom Field 15', 'constant-contact-api'), false);
			echo make_formfield_list_items($formfields, $checkedArray, 'formfields');
		?>
		</ul>
	</div>

	<div class="block">
		<label class="block"><?php esc_html_e( 'Required Fields', 'constant-contact-api' ); ?></label>
		<label for="reqast" class="howto checkbox block"><input type="checkbox" class="checkbox" name="reqast" id="reqast" <?php ctct_check_checkbox($form, 'reqast', '1', true); ?> /> <span>Add asterisk if field is required.</span></label>
	</div>

	<div class="block">
		<label class="block"><span><?php _e('SafeUnsubscribe', 'constant-contact-api'); ?></span></label>
		<ul>
			<li><label for="safesubscribelight"><input type="radio" <?php ctct_check_radio($form,'safesubscribe', 'light', true); ?> name="safesubscribe" id="safesubscribelight" /> <img src="<?php echo CC_FORM_GEN_URL; ?>images/safesubscribe-light-2x.gif" alt="<?php esc_html_e('SafeUnsubscribe Gray', 'constant-contact-api'); ?>" width="168" height="14" id="safesubscribelightimg" class="safesubscribesample" title="<?php esc_attr_e('Gray', 'constant-contact-api'); ?>"/></label></li>
			<li><label for="safesubscribedark"><input type="radio" <?php ctct_check_radio($form,'safesubscribe', 'dark'); ?> name="safesubscribe" id="safesubscribedark" /> <img src="<?php echo CC_FORM_GEN_URL; ?>images/safesubscribe-dark-2x.gif" alt="<?php esc_html_e('SafeUnsubscribe White', 'constant-contact-api'); ?>" width="168" height="14" id="safesubscribedarkimg" class="safesubscribesample" title="<?php esc_attr_e('White', 'constant-contact-api'); ?>"/></label></li>
			<li><label for="safesubscribeblack"><input type="radio" <?php ctct_check_radio($form,'safesubscribe', 'black'); ?> name="safesubscribe" id="safesubscribeblack" /> <img src="<?php echo CC_FORM_GEN_URL; ?>images/safesubscribe-black-2x.gif" alt="<?php esc_html_e('SafeUnsubscribe Black', 'constant-contact-api'); ?>" width="168" height="14" id="safesubscribeblackimg" class="safesubscribesample" title="<?php esc_attr_e('Black', 'constant-contact-api'); ?>"/></label></li>
			<li><label for="safesubscribeno"><input type="radio" <?php ctct_check_radio($form,'safesubscribe', 'no'); ?> name="safesubscribe" id="safesubscribeno" /> <?php _e('Do Not Display', 'constant-contact-api'); ?></label></li>
		</ul>
	</div>

</div>
<?php
}

function cc_form_meta_box_formfields($_form_object) {
	?>
		<div class="wp-editor-textarea">
			<input type="checkbox" class="checkbox hide-if-js" name="f[0][n]" value="f[0]" checked="checked" />
			<label for="form_text" class="labelDefault howto">
				<h3 class="description"><?php _e('Form Text', 'constant-contact-api'); ?></h3>
				<?php

				$default = isset($_form_object['f'][0]['val']) ? html_entity_decode( stripslashes($_form_object['f'][0]['val'])) :  __('Form Text Placeholder', 'constant-contact-api');

				echo wp_editor(
 				    $default,
 					'form_text', // #id
 					apply_filters('ctct_admin_wp_editor_settings', array(
						'textarea_name' => 'f[0][val]',
						'textarea_rows' => 4,
						'textarea_cols' => 4,
						'media_buttons' => true,
						'class' => 'hide-if-js',
						'teeny' => true,
						'quicktags' => true,
					))
				);
				?>
			</label>
		</div>

		<ul class="menu" id="menu-to-edit">
		<?php

			$formfields = array(
				make_formfield($_form_object, '', 'intro', __('Form Text Placeholder', 'constant-contact-api'), true, '', 'textarea'),
				make_formfield($_form_object, '', 'email_address', __('Email Address', 'constant-contact-api'), true, 'example@tryme.com'),
				make_formfield($_form_object, '', 'first_name', __('First Name', 'constant-contact-api'), true),
				make_formfield($_form_object, '', 'last_name', __('Last Name', 'constant-contact-api'), true),
				make_formfield($_form_object, '', 'Go', __('Submit', 'constant-contact-api'), true, 'Subscribe', 'submit'),
				make_formfield($_form_object, 'more', 'lists', __('Lists', 'constant-contact-api'), false, '', 'lists'),
				make_formfield($_form_object, 'more', 'middle_name', __('Middle Name', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'company_name', __('Company Name', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'job_title', __('Job Title', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'home_phone', __('Home Phone', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'work_phone', __('Work Phone', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'address_line1', __('Address Line 1', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'address_line2', __('Address Line 2', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'address_line3', __('Address Line 3', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'address_city', __('City', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'address_state_code', __('State Code', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'address_state_name', __('State Name', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'address_country_code', __('Country Code', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'address_postal_code', __('ZIP Code', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'address_sub_postal_code', __('Sub ZIP Code', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'CustomField1', __('Custom Field 1', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'CustomField2', __('Custom Field 2', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'CustomField3', __('Custom Field 3', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'CustomField4', __('Custom Field 4', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'CustomField5', __('Custom Field 5', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'CustomField6', __('Custom Field 6', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'CustomField7', __('Custom Field 7', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'CustomField8', __('Custom Field 8', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'CustomField9', __('Custom Field 9', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'CustomField10', __('Custom Field 10', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'CustomField11', __('Custom Field 11', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'CustomField12', __('Custom Field 12', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'CustomField13', __('Custom Field 13', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'CustomField14', __('Custom Field 14', 'constant-contact-api'), false),
				make_formfield($_form_object, 'more', 'CustomField15', __('Custom Field 15', 'constant-contact-api'), false)
			);

			foreach($formfields as $formfield) { echo $formfield; }
		?>
	</ul>
<?php
}

function cc_form_meta_box_backgroundoptions($post, $metabox=array()) {
	$form = $metabox['args'][0];
	?>
				<input type="hidden" name="backgroundgradienturl" id="backgroundgradienturl" value="" />
				<label for="backgroundtype" class="howto hide"><span><?php _e('Background Type:', 'constant-contact-api'); ?></span></label>
					<div class="tabs-panel tabs-panel-active clear" style="background-color:transparent;">
						<ul class="categorychecklist">
							<li><label for="backgroundtransparent" class="menu-item-title backgroundtype"><input type="radio" class="menu-item-checkbox" name="backgroundtype" id="backgroundtransparent" <?php ctct_check_radio($form,'backgroundtype', 'transparent', true); ?> /> <span><?php _e('Transparent', 'constant-contact-api'); ?></span></label></li>
							<li><label for="backgroundgradient" class="menu-item-title backgroundtype"><input type="radio" class="menu-item-checkbox" name="backgroundtype" id="backgroundgradient" <?php ctct_check_radio($form,'backgroundtype', 'gradient', true); ?> /> <span><?php _e('Gradient', 'constant-contact-api'); ?></span></label></li>
							<li><label for="backgroundsolid" class="backgroundtype"><input type="radio" class="menu-item-checkbox" <?php ctct_check_radio($form,'backgroundtype', 'solid'); ?>  name="backgroundtype" id="backgroundsolid" /> <span><?php _e('Solid Color', 'constant-contact-api'); ?></span></label></li>
							<li><label for="backgroundpattern" class="backgroundtype"><input type="radio" class="menu-item-checkbox" <?php ctct_check_radio($form,'backgroundtype', 'pattern'); ?> name="backgroundtype" id="backgroundpattern" /> <span><?php _e('Image Pattern', 'constant-contact-api'); ?></span></label></li>
							<li><label for="backgroundurl" class="backgroundtype"><input type="radio" class="menu-item-checkbox" <?php ctct_check_radio($form,'backgroundtype', 'url'); ?> name="backgroundtype" id="backgroundurl" /> <span><?php _e('URL (External Image)', 'constant-contact-api'); ?></span></label></li>
						</ul>
					</div>

				<div id="gradtypeli" class="block">
					<label class="howto" for="gradtype">
						<span class="block"><?php _e('Gradient Type:', 'constant-contact-api'); ?></span>

						<select id="gradtype" name="gradtype">
						  <option <?php ctct_check_select($form,'gradtype', 'vertical'); ?>><?php _e('Vertical', 'constant-contact-api'); ?></option>
						  <option <?php ctct_check_select($form,'gradtype', 'horizontal'); ?>><?php _e('Horizontal', 'constant-contact-api'); ?></option>
						</select>
					</label>
					<input type="hidden" id="gradwidth" name="gradwidth" value="1" />
				</div>

				<div class="block" id="bgtop" class="block">
						<label for="color6" class="howto block"><span><?php _e('Top Color:', 'constant-contact-api'); ?></span></label>
						<input type="hidden" id="color6" name="color6" class="wpcolor" value="<?php ctct_input_value($form, 'color6', '#ad0c0c'); ?>" />
				</div>
				<div class="block" id="bgbottom">
						<label class="howto block"><span><?php _e('Bottom Color:', 'constant-contact-api'); ?></span></label>
						<input type="hidden" id="color2" name="color2" class="wpcolor" value="<?php ctct_input_value($form, 'color2', '#000001'); ?>" />
				</div>
				<div class="form-item" id="bgurl">
					<p class="link-to-original">For inspiration, check out <a href="http://www.colourlovers.com/patterns/most-loved/all-time/meta">Colourlovers Patterns</a>.</p>
					<p><label for="bgimage"><span class="howto">Background Image:</span>
					<input type="text" class="code widefat" id="bgimage" name="bgimage" value="<?php ctct_input_value($form, 'bgimage', 'http://colourlovers.com.s3.amazonaws.com/images/patterns/90/90096.png'); ?>" />
					</label></p>

					<p><label class="howto" for="bgrepeat"><span><?php _e('Background Repeat:', 'constant-contact-api'); ?></span>
						<select name="bgrepeat" id="bgrepeat">
							<option <?php ctct_check_select($form,'bgrepeat', 'repeat',true); ?> value="repeat"><?php _e('Repeat', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'bgrepeat', 'no-repeat'); ?> value="no-repeat"><?php _e('No Repeat', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'bgrepeat', 'repeat-x'); ?> value="repeat-x"><?php _e('Repeat-X (Horizontal)', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'bgrepeat', 'repeat-y'); ?> value="repeat-y"><?php _e('Repeat-Y (Vertical)', 'constant-contact-api'); ?></option>
						</select>
					</label></p>
					<!-- <p class="howto">Choose the background alignment: Horizontal / Vertical</p> -->
					<p><label class="howto" for="bgpos"><span><?php _e('Background Position:', 'constant-contact-api'); ?></span>
						<select name="bgpos" id="bgpos">
							<option <?php ctct_check_select($form,'bgpos', 'left top',true); ?> value="left top"><?php _e('Left/Top', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'bgpos', 'center top'); ?> value="center top"><?php _e('Center/Top', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'bgpos', 'right top'); ?> value="right top"><?php _e('Right/Top', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'bgpos', 'left center'); ?> value="left center"><?php _e('Left/Center', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'bgpos', 'center center'); ?> value="center center"><?php _e('Center/Center', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'bgpos', 'right center'); ?> value="right center"><?php _e('Right/Center', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'bgpos', 'left bottom'); ?> value="left bottom"><?php _e('Left/Bottom', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'bgpos', 'center bottom'); ?> value="center bottom"><?php _e('Center/Bottom', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'bgpos', 'right bottom'); ?> value="right bottom"><?php _e('Right/Bottom', 'constant-contact-api'); ?></option>
						</select>
					</label></p>
				</div>
				<div class="form-item block" id="bgpattern">
					<label class="howto">Background Image Pattern:</label>
					<p class="description">Click a pattern to apply. Patterns by <a href="http://www.squidfingers.com/patterns/" rel="nofollow external">Squidfingers</a>.</p>
					<input type="hidden" id="patternurl" name="patternurl" value="<?php ctct_input_value($form, 'patternurl', '');?>" />
					<ul id="patternList">
						<?php
						$i = 1;
						$output = '';
						while($i < 159) {
							if($i === 88) { $i++; }
							$output .= sprintf('<li title="patterns/pattern_%1$s.gif" style="background-image: url('.CC_FORM_GEN_URL.'patterns/pattern_%1$s.gif)"></li>', str_pad($i, 3, '0', STR_PAD_LEFT));
							$i++;
						}
						echo $output;
						?>
					</ul>
		</div>
<?php
}

function cc_form_meta_box_border($post, $metabox=array()) {
	$form = $metabox['args'][0];
?>
	<div id="borderitem" class="block">
		<label for="borderwidth" class="howto">

			<span><?php esc_html_e('Border Width', 'constant-contact-api'); ?></span>
			<select id="borderwidth" name="borderwidth">
				  <option <?php ctct_check_select($form, 'borderwidth', '0',false); ?>><?php esc_html_e('No Border', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '1',false); ?>><?php esc_html_e('1 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '2',false); ?>><?php esc_html_e('2 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '3',false); ?>><?php esc_html_e('3 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '4',true); ?>><?php esc_html_e('4 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '5',false); ?>><?php esc_html_e('5 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '6',false); ?>><?php esc_html_e('6 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '7',false); ?>><?php esc_html_e('7 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '8',false); ?>><?php esc_html_e('8 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '9',false); ?>><?php esc_html_e('9 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '10',false); ?>><?php esc_html_e('10 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '11',false); ?>><?php esc_html_e('11 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '12',false); ?>><?php esc_html_e('12 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '13',false); ?>><?php esc_html_e('13 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '14',false); ?>><?php esc_html_e('14 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '15',false); ?>><?php esc_html_e('15 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '16',false); ?>><?php esc_html_e('16 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '17',false); ?>><?php esc_html_e('17 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '18',false); ?>><?php esc_html_e('18 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '19',false); ?>><?php esc_html_e('19 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '20',false); ?>><?php esc_html_e('20 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '25',false); ?>><?php esc_html_e('25 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '30',false); ?>><?php esc_html_e('30 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '35',false); ?>><?php esc_html_e('35 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '40',false); ?>><?php esc_html_e('40 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '45',false); ?>><?php esc_html_e('45 px', 'constant-contact-api'); ?></option>
				  <option <?php ctct_check_select($form, 'borderwidth', '50',false); ?>><?php esc_html_e('50 px', 'constant-contact-api'); ?></option>
			</select>
		</label>
	</div>

	<div id="bordercoloritem" class="block">
		<label for="bordercolor" class="howto inline"><span><?php esc_html_e('Border Color:', 'constant-contact-api'); ?></span></label>
		<div class="input">
			<input type="hidden" id="bordercolor" name="bordercolor" class="wpcolor" value="<?php ctct_input_value($form, 'bordercolor', '#000000'); ?>" />
		</div>
	</div>

	<div class="borderradius">
		<label for="borderradius" class="howto block"><span><?php esc_html_e('Rounded Corner Radius', 'constant-contact-api'); ?></span>
			<select id="borderradius" name="borderradius">
			  <option <?php ctct_check_select($form, 'borderradius', '0',false); ?>><?php esc_html_e('None (Square Corners)', 'constant-contact-api'); ?></option>
			  <option <?php ctct_check_select($form, 'borderradius', '5',true); ?>><?php esc_html_e('5 px', 'constant-contact-api'); ?></option>
			  <option <?php ctct_check_select($form, 'borderradius', '10',false); ?>><?php esc_html_e('10 px', 'constant-contact-api'); ?></option>
			  <option <?php ctct_check_select($form, 'borderradius', '15',false); ?>><?php esc_html_e('15 px', 'constant-contact-api'); ?></option>
			  <option <?php ctct_check_select($form, 'borderradius', '20',false); ?>><?php esc_html_e('20 px', 'constant-contact-api'); ?></option>
			  <option <?php ctct_check_select($form, 'borderradius', '25',false); ?>><?php esc_html_e('25 px', 'constant-contact-api'); ?></option>
			  <option <?php ctct_check_select($form, 'borderradius', '30',false); ?>><?php esc_html_e('30 px', 'constant-contact-api'); ?></option>
			  <option <?php ctct_check_select($form, 'borderradius', '35',false); ?>><?php esc_html_e('35 px', 'constant-contact-api'); ?></option>
			  <option <?php ctct_check_select($form, 'borderradius', '40',false); ?>><?php esc_html_e('40 px', 'constant-contact-api'); ?></option>
			  <option <?php ctct_check_select($form, 'borderradius', '45',false); ?>><?php esc_html_e('45 px', 'constant-contact-api'); ?></option>
			  <option <?php ctct_check_select($form, 'borderradius', '50',false); ?>><?php esc_html_e('50 px', 'constant-contact-api'); ?></option>
			  <option <?php ctct_check_select($form, 'borderradius', '60',false); ?>><?php esc_html_e('60 px', 'constant-contact-api'); ?></option>
			  <option <?php ctct_check_select($form, 'borderradius', '75',false); ?>><?php esc_html_e('75 px', 'constant-contact-api'); ?></option>
			  <option <?php ctct_check_select($form, 'borderradius', '100',false); ?>><?php esc_html_e('100 px', 'constant-contact-api'); ?></option>
			</select>
		</label>
	</div>
<?php
}

function cc_form_meta_box_formdesign($post, $metabox=array()) {
	$form = $metabox['args'][0];
	?>		<div>
				<label for="isize" class="howto block"><span><?php esc_html_e('Form Padding', 'constant-contact-api'); ?></span>
					<?php constant_contact_tip(__('Padding is the space between the outside of the form and the content inside the form; it\'s visual insulation.', 'constant-contact-api')); ?>
					<select id="paddingwidth" name="paddingwidth">
						<option<?php ctct_check_select($form,'paddingwidth', '0',false); ?> value="0"><?php esc_html_e('No Padding', 'constant-contact-api'); ?></option>
						<option<?php ctct_check_select($form,'paddingwidth', '5',false); ?> value="5"><?php esc_html_e('5 px', 'constant-contact-api'); ?></option>
						<option<?php ctct_check_select($form,'paddingwidth', '10', true); ?> value="10"><?php esc_html_e('10 px', 'constant-contact-api'); ?></option>
						<option<?php ctct_check_select($form,'paddingwidth', '15', false); ?> value="15"><?php esc_html_e('15 px', 'constant-contact-api'); ?></option>
						<option<?php ctct_check_select($form,'paddingwidth', '20', false); ?> value="20"><?php esc_html_e('20 px', 'constant-contact-api'); ?></option>
						<option<?php ctct_check_select($form,'paddingwidth', '25', false); ?> value="25"><?php esc_html_e('25 px', 'constant-contact-api'); ?></option>
						<option<?php ctct_check_select($form,'paddingwidth', '30', false); ?> value="30"><?php esc_html_e('30 px', 'constant-contact-api'); ?></option>
						<option<?php ctct_check_select($form,'paddingwidth', '35', false); ?> value="35"><?php esc_html_e('35 px', 'constant-contact-api'); ?></option>
						<option<?php ctct_check_select($form,'paddingwidth', '40', false); ?> value="40"><?php esc_html_e('40 px', 'constant-contact-api'); ?></option>
						<option<?php ctct_check_select($form,'paddingwidth', '45', false); ?> value="45"><?php esc_html_e('45 px', 'constant-contact-api'); ?></option>
						<option<?php ctct_check_select($form,'paddingwidth', '50', false); ?> value="50"><?php esc_html_e('50 px', 'constant-contact-api'); ?></option>
						<option<?php ctct_check_select($form,'paddingwidth', '60', false); ?> value="60"><?php esc_html_e('60 px', 'constant-contact-api'); ?></option>
						<option<?php ctct_check_select($form,'paddingwidth', '70', false); ?> value="70"><?php esc_html_e('70 px', 'constant-contact-api'); ?></option>
						<option<?php ctct_check_select($form,'paddingwidth', '80', false); ?> value="80"><?php esc_html_e('80 px', 'constant-contact-api'); ?></option>
					</select>
				</label>
			</div>
			<div class="alignleft">
				<label for="width" class="howto block"><span><?php esc_html_e('Form Width', 'constant-contact-api'); ?></span> <?php constant_contact_tip(''); ?></label>
				<input type="text" class="" id="width" name="width" value="<?php ctct_input_value($form, 'width', '300'); ?>" size="12" />
				<label for="widthtypeper" style="display:inline;" title="<?php esc_html_e('percent of container width', 'constant-contact-api'); ?>"><input type="radio" name="widthtype" id="widthtypeper" <?php ctct_check_radio($form,'widthtype', 'per'); ?>/>%</label>
				<label for="widthtypepx" style="display:inline;" title="<?php esc_html_e('pixels', 'constant-contact-api'); ?>"><input type="radio" name="widthtype" id="widthtypepx" <?php ctct_check_radio($form,'widthtype', 'px', true); ?> />px</label>
			</div>

		<div class="clear">
			<label for="lalign" class="howto block"><span><?php esc_html_e('Form Content Alignment', 'constant-contact-api'); constant_contact_tip(__('Align the form fields and labels inside the form. <strong>Note:</strong> you can change the alignment of the Form Text separately inside the Form Text editor.', 'constant-contact-api')); ?></span></label>
			<ul class="categorychecklist form-no-clear">
				<li><label for="lalignleft" class="menu-item-title"><span><input type="radio" id="lalignleft" name="talign" <?php ctct_check_radio($form,'talign', 'left'); ?> /> <?php esc_html_e('Left', 'constant-contact-api'); ?></span></label></li>
				<li><label for="laligncenter" class="menu-item-title"><span><input type="radio" id="laligncenter" name="talign" <?php ctct_check_radio($form,'talign', 'center',true); ?> /> <?php esc_html_e('Center', 'constant-contact-api'); ?></span></label></li>
				<li><label for="lalignright" class="menu-item-title"><span><input type="radio" id="lalignright" name="talign" <?php ctct_check_radio($form,'talign', 'right'); ?> /> <?php esc_html_e('Right', 'constant-contact-api'); ?></span></label></li>
			</ul>
		</div>
		<div>
			<label for="formalign" class="howto block"><span><?php esc_html_e('Form Alignment', 'constant-contact-api'); constant_contact_tip(__('Align the form inside your widget or page content. Also called "floating" to the left or right.', 'constant-contact-api')); ?></span></label>
			<ul>
				<li><label class="menu-item-title" for='formalignleft'><input type="radio" id="formalignleft" name="formalign" <?php ctct_check_radio($form,'formalign', 'left'); ?> /> <?php esc_html_e('Left', 'constant-contact-api'); ?></label></li>
				<li><label class="menu-item-title" for='formaligncenter'><input type="radio" id="formaligncenter" name="formalign" <?php ctct_check_radio($form,'formalign', 'center',true); ?> /> <?php esc_html_e('Center', 'constant-contact-api'); ?></label></li>
				<li><label class="menu-item-title" for='formalignright'><input type="radio" id="formalignright" name="formalign" <?php ctct_check_radio($form,'formalign', 'right'); ?> /> <?php esc_html_e('Right', 'constant-contact-api'); ?></label></li>
			</ul>
		</div>
<?php
}


function cc_form_meta_box_fontstyles($post, $metabox=array()) {
	$form = $metabox['args'][0];
?>
<fieldset>
				<legend><?php esc_html_e('Text', 'constant-contact-api'); ?></legend>
				<p class="description"><?php esc_html_e('These settings are for the Form Text field. If the checkboxes are checked, the settings also apply to the input labels.', 'constant-contact-api'); ?></p>
				<div class="block">
					<label for="tcolor" class="howto inline"><span><?php esc_html_e('Text Color:', 'constant-contact-api'); ?></span></label>
					<div class="input"><input type="hidden" id="tcolor" name="tcolor" class="wpcolor" value="<?php ctct_input_value($form, 'tcolor', '#accbf7'); ?>" /></div>

					<label for="lusc" class="howto checkbox block"><input type="checkbox" class="checkbox" name="lusc" id="lusc" <?php ctct_check_checkbox($form, 'lusc', 'yes', true); ?> /> <span><?php esc_html_e('Use Same Color for Labels', 'constant-contact-api'); ?></span></label>
				</div>

				<p>
					<label for="tfont" class="howto block"><span><?php esc_html_e('Text Font &amp; Size', 'constant-contact-api'); ?></span></label>
					<select id="tfont" name="tfont" class="inline">
						<option <?php ctct_check_select($form,'tfont', 'inherit'); ?> style="font-family: inherit;" id="inherit"><?php esc_html_e('Use Theme Font', 'constant-contact-api'); ?></option>
							<optgroup label="Serif">
								<option <?php ctct_check_select($form,'tfont', 'times'); ?> style="font-family: 'Times New Roman', Times, Georgia, serif;" id="times"><?php esc_html_e('Times New Roman', 'constant-contact-api'); ?></option>
								<option <?php ctct_check_select($form,'tfont', 'georgia'); ?> style="font-family: Georgia, 'Times New Roman', Times, serif;" id="georgia"><?php esc_html_e('Georgia', 'constant-contact-api'); ?></option>
								<option <?php ctct_check_select($form,'tfont', 'palatino'); ?> style="font-family: 'Palatino Linotype', Palatino, 'Book Antiqua',Garamond, Bookman, 'Times New Roman', Times, Georgia, serif" id="palatino"><?php esc_html_e('Palatino *', 'constant-contact-api'); ?></option>
								<option <?php ctct_check_select($form,'tfont', 'garamond'); ?> style="font-family: Garamond,'Palatino Linotype', Palatino, Bookman, 'Book Antiqua', 'Times New Roman', Times, Georgia, serif" id="garamond"><?php esc_html_e('Garamond *', 'constant-contact-api'); ?></option>
								<option <?php ctct_check_select($form,'tfont', 'bookman'); ?> style="font-family: Bookman,'Palatino Linotype', Palatino, Garamond, 'Book Antiqua','Times New Roman', Times, Georgia, serif" id="bookman"><?php esc_html_e('Bookman *', 'constant-contact-api'); ?></option>
							</optgroup>
							<optgroup label="Sans-Serif">
								<option <?php ctct_check_select($form,'tfont', 'helvetica',true); ?> style="font-family: 'Helvetica Neue', Arial, Helvetica, Geneva, sans-serif;" id="helvetica"><?php esc_html_e('Helvetica', 'constant-contact-api'); ?></option>
								<option <?php ctct_check_select($form,'tfont', 'arial'); ?> style="font-family:Arial, Helvetica, sans-serif;" id="arial"><?php esc_html_e('Arial', 'constant-contact-api'); ?></option>
								<option <?php ctct_check_select($form,'tfont', 'lucidagrande'); ?> style="font-family: 'Lucida Grande', 'Lucida Sans Unicode', Lucida, Verdana, sans-serif;" id="lucida"><?php esc_html_e('Lucida Grande', 'constant-contact-api'); ?></option>
								<option <?php ctct_check_select($form,'tfont', 'verdana'); ?> style="font-family: Verdana, 'Lucida Grande', Lucida, TrebuchetMS, 'Trebuchet MS', Helvetica, Arial, sans-serif;" id="bookman"><?php esc_html_e('Verdana', 'constant-contact-api'); ?></option>
								<option <?php ctct_check_select($form,'tfont', 'trebuchet'); ?> style="font-family:'Trebuchet MS', Trebuchet, sans-serif;" id="trebuchet"><?php esc_html_e('Trebuchet MS', 'constant-contact-api'); ?></option>
								<option <?php ctct_check_select($form,'tfont', 'tahoma'); ?> style="font-family:Tahoma, Verdana, Arial, sans-serif;" id="tahoma"><?php esc_html_e('Tahoma', 'constant-contact-api'); ?></option>
								<option <?php ctct_check_select($form,'tfont', 'franklin'); ?> style="font-family:'Franklin Gothic Medium','Arial Narrow Bold',Arial,sans-serif;" id="franklin"><?php esc_html_e('Franklin Gothic', 'constant-contact-api'); ?></option>
								<option <?php ctct_check_select($form,'tfont', 'impact'); ?> style="font-family:Impact, Chicago, 'Arial Black', Arial, sans-serif;" id="impact"><?php esc_html_e('Impact *', 'constant-contact-api'); ?></option>
							  	<option <?php ctct_check_select($form,'tfont', 'arialblack'); ?> style="font-family:'Arial Black',Impact, Arial, sans-serif;" id="arial-black"><?php esc_html_e('Arial Black', 'constant-contact-api'); ?></option>
								<option <?php ctct_check_select($form,'tfont', 'gillsans'); ?> style="font-family:'Gill Sans','Gill Sans MT', 'Trebuchet MS', Trebuchet, Verdana, sans-serif;" id="gill"><?php esc_html_e('Gill Sans *', 'constant-contact-api'); ?></option>
							</optgroup>
							<optgroup label="Mono">
								<option <?php ctct_check_select($form,'tfont', 'courier'); ?> style="font-family: 'Courier New', Courier, monospace;" id="courier"><?php esc_html_e('Courier New', 'constant-contact-api'); ?></option>
								<option <?php ctct_check_select($form,'tfont', 'lucidaconsole'); ?> style="font-family: 'Lucida Console', Monaco, monospace;" id="lucida-console"><?php esc_html_e('Lucida Console', 'constant-contact-api'); ?></option>
							</optgroup>
							<optgroup label="Cursive">
								<option <?php ctct_check_select($form,'tfont', 'comicsans'); ?> style="font-family:'Comic Sans MS','Comic Sans', Sand, 'Trebuchet MS', Verdana, sans-serif" id="comicsans"><?php esc_html_e('Comic Sans MS', 'constant-contact-api'); ?></option>
							</optgroup>
							<optgroup label="Fantasy">
								<option <?php ctct_check_select($form,'tfont', 'papyrus'); ?> style="font-family: Papyrus, 'Palatino Linotype', Palatino, Bookman, fantasy" id="papyrus"><?php esc_html_e('Papyrus', 'constant-contact-api'); ?></option>
							</optgroup>
						</select>
					<small class="asterix"><?php esc_html_e('<strong>* This font is popular, but not a "web-safe" font.</strong> If not available on an user\'s computer, it will default to a similar font.', 'constant-contact-api'); ?></small>
					<label for="lusf" class="howto checkbox"><input type="checkbox" name="lusf" id="lusf" rel="lfont" <?php ctct_check_checkbox($form, 'lusf', 'yes', true); ?> /> <span><?php esc_html_e('Use Same Font for Labels', 'constant-contact-api'); ?></span></label>
				</p>
			</fieldset>
			<fieldset>
				<legend><?php esc_html_e('Label', 'constant-contact-api'); ?></legend>

				<p class="description"><?php esc_html_e('These settings apply to the label text above the inputs.', 'constant-contact-api'); ?></p>
				<div id="labelcolorli" class="block">
					<label for="tcolor" class="howto inline"><span><?php esc_html_e('Label Color:', 'constant-contact-api'); ?></span></label>
					<div class="input"><input type="hidden" id="lcolor" name="lcolor" class="wpcolor" value="<?php ctct_input_value($form, 'lcolor', '#accbf7'); ?>" /></div>
				</div>

				<div class="block">
				<label for="lpad" class="howto block"><span><?php esc_html_e('Label Padding', 'constant-contact-api'); ?></span>
				<select id="lpad" name="lpad">
				  <option<?php ctct_check_select($form,'lpad', '0'); ?> value="0"><?php _e('None', 'constant-contact-api'); ?></option>
				  <option<?php ctct_check_select($form,'lpad', '.25'); ?> value=".25"><?php _e('.2 em', 'constant-contact-api'); ?></option>
				  <option<?php ctct_check_select($form,'lpad', '.5'); ?> value=".5"><?php _e('.5 em', 'constant-contact-api'); ?></option>
				  <option<?php ctct_check_select($form,'lpad', '.75', true); ?> value=".75"><?php _e('.75 em', 'constant-contact-api'); ?></option>
				  <option<?php ctct_check_select($form,'lpad', '1'); ?> value="1"><?php _e('1 em', 'constant-contact-api'); ?></option>
				  <option<?php ctct_check_select($form,'lpad', '1.25'); ?> value="1.25"><?php _e('1.25 em', 'constant-contact-api'); ?></option>
				  <option<?php ctct_check_select($form,'lpad', '1.5'); ?> value="1.5"><?php _e('1.5 em', 'constant-contact-api'); ?></option>
				</select>
				</label>
				</div>

				<div id="lfontli">
					<label for="lfont" id="lfontlabel" class="howto block"><span><?php esc_html_e('Label Font', 'constant-contact-api'); ?></span></label>
					<select id="lfont" name="lfont" class="inline">
						<optgroup label="Serif">
							<option <?php ctct_check_select($form,'lfont', 'times'); ?> style="font-family: 'Times New Roman', Times, Georgia, serif;" id="times"><?php esc_html_e('Times New Roman', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'georgia'); ?> style="font-family: Georgia, 'Times New Roman', Times, serif;" id="georgia"><?php esc_html_e('Georgia', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'palatino'); ?> style="font-family: 'Palatino Linotype', Palatino, 'Book Antiqua',Garamond, Bookman, 'Times New Roman', Times, Georgia, serif" id="palatino"><?php esc_html_e('Palatino *', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'garamond'); ?> style="font-family: Garamond,'Palatino Linotype', Palatino, Bookman, 'Book Antiqua', 'Times New Roman', Times, Georgia, serif" id="garamond"><?php esc_html_e('Garamond *', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'bookman'); ?> style="font-family: Bookman,'Palatino Linotype', Palatino, Garamond, 'Book Antiqua','Times New Roman', Times, Georgia, serif" id="bookman"><?php esc_html_e('Bookman *', 'constant-contact-api'); ?></option>
						</optgroup>
						<optgroup label="Sans-Serif">
							<option <?php ctct_check_select($form,'lfont', 'helvetica'); ?> style="font-family: 'Helvetica Neue', Arial, Helvetica, Geneva, sans-serif;" id="helvetica"><?php esc_html_e('Helvetica', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'arial'); ?> style="font-family:Arial, Helvetica, sans-serif;" id="arial"><?php esc_html_e('Arial', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'lucidagrande'); ?> style="font-family: 'Lucida Grande', 'Lucida Sans Unicode', Lucida, Verdana, sans-serif;" id="lucida"><?php esc_html_e('Lucida Grande', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'verdana'); ?> style="font-family: Verdana, 'Lucida Grande', Lucida, TrebuchetMS, 'Trebuchet MS', Helvetica, Arial, sans-serif;" id="bookman"><?php esc_html_e('Verdana', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'trebuchet'); ?> style="font-family:'Trebuchet MS', Trebuchet, sans-serif;" id="trebuchet"><?php esc_html_e('Trebuchet MS', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'tahoma'); ?> style="font-family:Tahoma, Verdana, Arial, sans-serif;" id="tahoma"><?php esc_html_e('Tahoma', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'franklin'); ?> style="font-family:'Franklin Gothic Medium','Arial Narrow Bold',Arial,sans-serif;" id="franklin"><?php esc_html_e('Franklin Gothic', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'impact'); ?> style="font-family:Impact, Chicago, 'Arial Black', Arial, sans-serif;" id="impact"><?php esc_html_e('Impact *', 'constant-contact-api'); ?></option>
						  	<option <?php ctct_check_select($form,'lfont', 'arialblack'); ?> style="font-family:'Arial Black',Impact, Arial, sans-serif;" id="arial-black"><?php esc_html_e('Arial Black', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'gillsans'); ?> style="font-family:'Gill Sans','Gill Sans MT', 'Trebuchet MS', Trebuchet, Verdana, sans-serif;" id="gill"><?php esc_html_e('Gill Sans *', 'constant-contact-api'); ?></option>
						</optgroup>
						<optgroup label="Mono">
							<option <?php ctct_check_select($form,'lfont', 'courier'); ?> style="font-family: 'Courier New', Courier, monospace;" id="courier"><?php esc_html_e('Courier New', 'constant-contact-api'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'lucidaconsole'); ?> style="font-family: 'Lucida Console', Monaco, monospace;" id="lucida-console"><?php esc_html_e('Lucida Console', 'constant-contact-api'); ?></option>
						</optgroup>
						<optgroup label="Cursive">
							<option <?php ctct_check_select($form,'lfont', 'comicsans'); ?> style="font-family:'Comic Sans MS','Comic Sans', Sand, 'Trebuchet MS', Verdana, sans-serif" id="comicsans"><?php esc_html_e('Comic Sans MS', 'constant-contact-api'); ?></option>
						</optgroup>
						<optgroup label="Fantasy">
							<option <?php ctct_check_select($form,'lfont', 'papyrus'); ?> style="font-family: Papyrus, 'Palatino Linotype', Palatino, Bookman, fantasy" id="papyrus"><?php esc_html_e('Papyrus', 'constant-contact-api'); ?></option>
						</optgroup>
					</select>
				<small class="asterix"><?php esc_html_e('* This font is popular, but not a "web-safe" font. If not available on an user\'s computer, it will default to a similar font.', 'constant-contact-api'); ?></small>
				</div>

				<label for="lsize" class="howto block"><span><?php esc_html_e('Label Font Size', 'constant-contact-api'); ?></span></label>
				<select id="lsize" class="nomargin" name="lsize">
					  <option<?php ctct_check_select($form,'lsize', '7'); ?> value="7"><?php esc_html_e('7 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '8'); ?> value="8"><?php esc_html_e('8 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '9'); ?> value="9"><?php esc_html_e('9 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '10'); ?> value="10"><?php esc_html_e('10 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '11'); ?> value="11"><?php esc_html_e('11 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '12',true); ?> value="12"><?php esc_html_e('12 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '13'); ?> value="13"><?php esc_html_e('13 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '14'); ?> value="14"><?php esc_html_e('14 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '15'); ?> value="15"><?php esc_html_e('15 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '16'); ?> value="16"><?php esc_html_e('16 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '17'); ?> value="17"><?php _e('17 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '18'); ?> value="18"><?php _e('18 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '19'); ?> value="19"><?php _e('19 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '20'); ?> value="20"><?php _e('20 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '22'); ?> value="22"><?php _e('22 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '24'); ?> value="24"><?php _e('24 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '28'); ?> value="28"><?php _e('28 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '32'); ?> value="32"><?php _e('32 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '36'); ?> value="36"><?php _e('36 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '40'); ?> value="40"><?php _e('40 px', 'constant-contact-api'); ?></option>
					  <option<?php ctct_check_select($form,'lsize', '48'); ?> value="48"><?php _e('48 px', 'constant-contact-api'); ?></option>
				</select>
		</fieldset>
<?php
}

