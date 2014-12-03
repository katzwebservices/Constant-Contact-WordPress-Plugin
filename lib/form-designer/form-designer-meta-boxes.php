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
			<?php if($cc_form_selected_id >= 0) { ?>

				<h4 class="smallmarginbottom"><?php _e('Form ID:'); ?> <tt class="large"><?php _e($cc_form_selected_id); ?></tt></h4>
				<p class="howto">
					<span><?php printf(__('To embed this form in a post or page, add the following code. %s', 'ctct'), '<input type="text" class="code widefat block select-text" readonly="readonly" value="'.esc_attr('[constantcontactapi formid="'.(int)$cc_form_selected_id.'"]').'" />' ); ?> <a href="#tab-panel-ctct-insert-form" rel="wp-help"><?php esc_html_e('Learn More', 'ctct'); ?></a>
					</span>
				</p>

			<?php } ?>
			<div class="block">
				<h4 class="large smallmarginbottom"><?php _e('Use Form Styler?', 'ctct'); constant_contact_tip(__('Use the form styler to change colors and design of your form. If not used, the form will be styled using your theme\'s&nbsp;defaults.', 'ctct')); ?></h4>
				<div class="switch">

						<input type="hidden" name="toggledesign" value="" />
						<input type="checkbox" name="toggledesign" id="toggledesign" <?php ctct_check_checkbox($form, 'toggledesign', 'yes', 'yes'); ?> />
						<label for="toggledesign"><span class="hide-if-js"><?php _e('Customize Form Style?', 'ctct'); ?></span><i></i></label>
				</div>
			</div>

			<div class="clear"></div>
		</div>
	</div><!-- END #submitpost .submitbox -->
	<?php
}

function cc_form_meta_box_formlists_select($post, $metabox=array()) {
	$form = $metabox['args'][0];

	$checked_lists = !empty($form['lists']) ? $form['lists'] : NULL;

	$output = KWSContactList::outputHTML('all', array('checked' => $checked_lists, 'type' => 'checkboxes'));
?>
<div class="posttypediv lists-meta-box">
	<h4 class="smallmarginbottom"><?php esc_html_e('Lists', 'ctct'); constant_contact_tip( __('Contacts will be added to the selected lists by default. You can override this selection when you configure a Form Designer widget. You can also specify different list IDs when inserting a form into content using the shortcode.', 'ctct') ); ?></h4>
	<div class="tabs-panel tabs-panel-active" id="ctct-form-list-select">
		<ul class="ctct-checkboxes categorychecklist form-no-clear">
		<?php
			echo $output;
		?>
		</ul>
	</div>
	<h4 class="smallmarginbottom"><?php esc_html_e('List Selection Format', 'ctct'); ?></h4>
	<ul class="list-selection-format">
		<li><label><input type="radio" name="list_format" <?php ctct_check_radio($form,'list_format', 'checkbox'); ?> /> <?php esc_html_e('Checkbox List', 'ctct'); ?></label></li>
		<li><label><input type="radio" name="list_format" <?php ctct_check_radio($form,'list_format', 'dropdown'); ?> /> <?php esc_html_e('Dropdown Field', 'ctct'); ?></label></li>
		<li><label><input type="radio" name="list_format" <?php ctct_check_radio($form,'list_format', 'hidden', true); ?> /> <?php esc_html_e('Hidden', 'ctct'); ?></label></li>
	</ul>

	<h4 class="smallmarginbottom"><?php esc_html_e('Checked by default?', 'ctct'); ?></h4>
	<label for="checked_by_default" class="checkbox toggle_comment_form">
		<input name="checked_by_default" id="checked_by_default" type="checkbox" value="true" checked="checked" style="margin-right:.25em;" /><?php esc_html_e('Should the list checkboxes be checked by default?', 'ctct'); ?>
	</label>

</div>
<?php
}

function cc_form_meta_box_formfields_select($post, $metabox=array()) {

	$form = $metabox['args'][0];

	$default_checked = array(
		'email_address',
		'Go'
	);

	$checked_fields = !empty($form['formfields']) ? $form['formfields'] : $default_checked;
?>
<div class="posttypediv">
	<ul id="formfields-select-tabs" class="formfields-select-tabs add-menu-item-tabs">
		<li class="tabs"><a href="#formfields-select-most" class="nav-tab-link"><?php esc_html_e('Most Used', 'ctct'); ?></a></li>
		<li><a href="#formfields-select-all" class="nav-tab-link"><?php esc_html_e('Other Fields', 'ctct'); ?></a></li>
	</ul>
	<div id="formfields-select-most" class="tabs-panel tabs-panel-active">
		<ul id="formfieldslist-most" class="categorychecklist form-no-clear">
		<?php
			$formfields = array(
				array('email_address', __('Email Address', 'ctct'), true),
				array('intro', __('Custom Text', 'ctct'), true),
				array('first_name', __('First Name', 'ctct'), true),
				array('last_name', __('Last Name', 'ctct'), true),
				array('Go', __('Submit', 'ctct'), true),
				array('home_phone', __('Home Phone', 'ctct'), false),
				array('work_phone', __('Work Phone', 'ctct'), false),
				array('lists', __('Lists', 'ctct'), true),
			);
			echo ctct_make_formfield_list_items($formfields, $checked_fields, 'formfields');
		?>
		</ul>
	</div>
	<div id="formfields-select-all" class="tabs-panel">
		<ul id="formfieldslist-all" class="categorychecklist form-no-clear">
		<?php
			$formfields = array(
				array('middle_name', __('Middle Name', 'ctct'), false),
				array('company_name', __('Company Name', 'ctct'), false),
				array('job_title', __('Job Title', 'ctct'), false),
				array('address_line1', __('Address Line 1', 'ctct'), false),
				array('address_line2', __('Address Line 2', 'ctct'), false),
				array('address_line3', __('Address Line 3', 'ctct'), false),
				array('address_city', __('City Name', 'ctct'), false),
				array('address_state_code', __('State Code', 'ctct'), false),
				array('address_state_name', __('State Name', 'ctct'), false),
				array('address_country_code', __('Country Code', 'ctct'), false),
				array('address_postal_code', __('ZIP Code', 'ctct'), false),
				array('address_sub_postal_code', __('Sub ZIP Code', 'ctct'), false),
				array('CustomField1', __('Custom Field 1', 'ctct'), false),
				array('CustomField2', __('Custom Field 2', 'ctct'), false),
				array('CustomField3', __('Custom Field 3', 'ctct'), false),
				array('CustomField4', __('Custom Field 4', 'ctct'), false),
				array('CustomField5', __('Custom Field 5', 'ctct'), false),
				array('CustomField6', __('Custom Field 6', 'ctct'), false),
				array('CustomField7', __('Custom Field 7', 'ctct'), false),
				array('CustomField8', __('Custom Field 8', 'ctct'), false),
				array('CustomField9', __('Custom Field 9', 'ctct'), false),
				array('CustomField10', __('Custom Field 10', 'ctct'), false),
				array('CustomField11', __('Custom Field 11', 'ctct'), false),
				array('CustomField12', __('Custom Field 12', 'ctct'), false),
				array('CustomField13', __('Custom Field 13', 'ctct'), false),
				array('CustomField14', __('Custom Field 14', 'ctct'), false),
				array('CustomField15', __('Custom Field 15', 'ctct'), false),
			);
			echo ctct_make_formfield_list_items($formfields, $checked_fields, 'formfields');
		?>
		</ul>
	</div>

	<div class="block">
		<label class="block"><?php esc_html_e( 'Required Fields', 'ctct'); ?></label>
		<label for="reqast" class="howto checkbox block"><input type="checkbox" class="checkbox" name="reqast" id="reqast" <?php ctct_check_checkbox($form, 'reqast', '1', true); ?> /> <span><?php esc_html_e('Add asterisk if field is required.', 'ctct'); ?></span></label>
	</div>

	<div class="block">
		<label class="block"><span><?php esc_html_e('SafeUnsubscribe', 'ctct'); ?></span></label>
		<ul>
			<li><label for="safesubscribelight"><input type="radio" <?php ctct_check_radio($form,'safesubscribe', 'light', true); ?> name="safesubscribe" id="safesubscribelight" /> <img src="<?php echo CC_FORM_GEN_URL; ?>images/safesubscribe-light-2x.gif" alt="<?php esc_html_e('SafeUnsubscribe Gray', 'ctct'); ?>" width="168" height="14" id="safesubscribelightimg" class="safesubscribesample" title="<?php esc_attr_e('Gray', 'ctct'); ?>"/></label></li>
			<li><label for="safesubscribedark"><input type="radio" <?php ctct_check_radio($form,'safesubscribe', 'dark'); ?> name="safesubscribe" id="safesubscribedark" /> <img src="<?php echo CC_FORM_GEN_URL; ?>images/safesubscribe-dark-2x.gif" alt="<?php esc_html_e('SafeUnsubscribe White', 'ctct'); ?>" width="168" height="14" id="safesubscribedarkimg" class="safesubscribesample" title="<?php esc_attr_e('White', 'ctct'); ?>"/></label></li>
			<li><label for="safesubscribeblack"><input type="radio" <?php ctct_check_radio($form,'safesubscribe', 'black'); ?> name="safesubscribe" id="safesubscribeblack" /> <img src="<?php echo CC_FORM_GEN_URL; ?>images/safesubscribe-black-2x.gif" alt="<?php esc_html_e('SafeUnsubscribe Black', 'ctct'); ?>" width="168" height="14" id="safesubscribeblackimg" class="safesubscribesample" title="<?php esc_attr_e('Black', 'ctct'); ?>"/></label></li>
			<li><label for="safesubscribeno"><input type="radio" <?php ctct_check_radio($form,'safesubscribe', 'no'); ?> name="safesubscribe" id="safesubscribeno" /> <?php esc_html_e('Do Not Display', 'ctct'); ?></label></li>
		</ul>
	</div>

</div>
<?php
}

function cc_form_meta_box_formfields($_form_object) {
	?>
		<div class="wp-editor-textarea">
			<input type="checkbox" class="checkbox hide-if-js" name="f[0][n]" value="f[0]" checked="checked" />

				<h3><i class="dashicons dashicons-clipboard"></i> <?php esc_html_e('Custom Text', 'ctct'); ?></h3>

				<div id="menu-instructions" class="drag-instructions post-body-plain">
				<?php
				echo wpautop( esc_html__('The content below be placed where the "Custom Text Placeholder" field is. Edit the Custom Text below. &darr;', 'ctct') );
				?>
				</div>

				<?php

				$default = isset($_form_object['f'][0]['val']) ? html_entity_decode( stripslashes($_form_object['f'][0]['val'])) :  __('Custom Text Placeholder', 'ctct');

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
		</div>

		<ul class="menu" id="menu-to-edit">
		<?php

			$formfields = array(
				ctct_make_formfield($_form_object, '', 'intro', '<i class="dashicons dashicons-clipboard"></i> '.__('Custom Text', 'ctct'), true, '', 'textarea'),
				ctct_make_formfield($_form_object, '', 'email_address', __('Email Address', 'ctct'), true, 'example@tryme.com'),
				ctct_make_formfield($_form_object, '', 'first_name', __('First Name', 'ctct'), true),
				ctct_make_formfield($_form_object, '', 'last_name', __('Last Name', 'ctct'), true),
				ctct_make_formfield($_form_object, '', 'Go', __('Submit', 'ctct'), true, __('Subscribe', 'ctct'), 'submit'),
				ctct_make_formfield($_form_object, 'more', 'lists', __('Lists', 'ctct'), false, '', 'lists'),
				ctct_make_formfield($_form_object, 'more', 'middle_name', __('Middle Name', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'company_name', __('Company Name', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'job_title', __('Job Title', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'home_phone', __('Home Phone', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'work_phone', __('Work Phone', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'address_line1', __('Address Line 1', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'address_line2', __('Address Line 2', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'address_line3', __('Address Line 3', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'address_city', __('City', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'address_state_code', __('State Code', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'address_state_name', __('State Name', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'address_country_code', __('Country Code', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'address_postal_code', __('ZIP Code', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'address_sub_postal_code', __('Sub ZIP Code', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'CustomField1', __('Custom Field 1', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'CustomField2', __('Custom Field 2', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'CustomField3', __('Custom Field 3', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'CustomField4', __('Custom Field 4', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'CustomField5', __('Custom Field 5', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'CustomField6', __('Custom Field 6', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'CustomField7', __('Custom Field 7', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'CustomField8', __('Custom Field 8', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'CustomField9', __('Custom Field 9', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'CustomField10', __('Custom Field 10', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'CustomField11', __('Custom Field 11', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'CustomField12', __('Custom Field 12', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'CustomField13', __('Custom Field 13', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'CustomField14', __('Custom Field 14', 'ctct'), false),
				ctct_make_formfield($_form_object, 'more', 'CustomField15', __('Custom Field 15', 'ctct'), false)
			);

			foreach($formfields as $formfield) {
				echo $formfield;
			}
		?>
	</ul>
<?php
}

function cc_form_meta_box_backgroundoptions($post, $metabox=array()) {
	$form = $metabox['args'][0];
	?>
				<input type="hidden" name="backgroundgradienturl" id="backgroundgradienturl" value="" />
				<label for="backgroundtype" class="howto hide"><span><?php esc_html_e('Background Type:', 'ctct'); ?></span></label>
					<div class="tabs-panel tabs-panel-active clear" style="background-color:transparent;">
						<ul class="categorychecklist">
							<li><label for="backgroundtransparent" class="menu-item-title backgroundtype"><input type="radio" class="menu-item-checkbox" name="backgroundtype" id="backgroundtransparent" <?php ctct_check_radio($form,'backgroundtype', 'transparent', true); ?> /> <span><?php esc_html_e('Transparent', 'ctct'); ?></span></label></li>
							<li><label for="backgroundgradient" class="menu-item-title backgroundtype"><input type="radio" class="menu-item-checkbox" name="backgroundtype" id="backgroundgradient" <?php ctct_check_radio($form,'backgroundtype', 'gradient', true); ?> /> <span><?php esc_html_e('Gradient', 'ctct'); ?></span></label></li>
							<li><label for="backgroundsolid" class="backgroundtype"><input type="radio" class="menu-item-checkbox" <?php ctct_check_radio($form,'backgroundtype', 'solid'); ?>  name="backgroundtype" id="backgroundsolid" /> <span><?php esc_html_e('Solid Color', 'ctct'); ?></span></label></li>
							<li><label for="backgroundpattern" class="backgroundtype"><input type="radio" class="menu-item-checkbox" <?php ctct_check_radio($form,'backgroundtype', 'pattern'); ?> name="backgroundtype" id="backgroundpattern" /> <span><?php esc_html_e('Image Pattern', 'ctct'); ?></span></label></li>
							<li><label for="backgroundurl" class="backgroundtype"><input type="radio" class="menu-item-checkbox" <?php ctct_check_radio($form,'backgroundtype', 'url'); ?> name="backgroundtype" id="backgroundurl" /> <span><?php esc_html_e('URL (External Image)', 'ctct'); ?></span></label></li>
						</ul>
					</div>

				<div id="gradtypeli" class="block">
					<label class="howto" for="gradtype">
						<span class="block"><?php esc_html_e('Gradient Type:', 'ctct'); ?></span>

						<select id="gradtype" name="gradtype">
						  <option <?php ctct_check_select($form,'gradtype', 'vertical'); ?>><?php esc_html_e('Vertical', 'ctct'); ?></option>
						  <option <?php ctct_check_select($form,'gradtype', 'horizontal'); ?>><?php esc_html_e('Horizontal', 'ctct'); ?></option>
						</select>
					</label>
					<input type="hidden" id="gradwidth" name="gradwidth" value="1" />
				</div>

				<div class="block" id="bgtop">
						<label for="color6" class="howto block"><span><?php esc_html_e('Top Color:', 'ctct'); ?></span></label>
						<input type="hidden" id="color6" name="color6" class="wpcolor" value="<?php ctct_input_value($form, 'color6', '#ad0c0c'); ?>" />
				</div>
				<div class="block" id="bgbottom">
						<label class="howto block"><span><?php esc_html_e('Bottom Color:', 'ctct'); ?></span></label>
						<input type="hidden" id="color2" name="color2" class="wpcolor" value="<?php ctct_input_value($form, 'color2', '#000001'); ?>" />
				</div>
				<div class="form-item" id="bgurl">
					<p class="link-to-original">For inspiration, check out <a href="http://www.colourlovers.com/patterns/most-loved/all-time/meta" rel="external">Colourlovers Patterns</a>.</p>
					<p><label for="bgimage"><span class="howto">Background Image:</span>
					<input type="text" class="code widefat" id="bgimage" name="bgimage" value="<?php ctct_input_value($form, 'bgimage', 'http://colourlovers.com.s3.amazonaws.com/images/patterns/90/90096.png'); ?>" />
					</label></p>

					<p><label class="howto" for="bgrepeat"><span><?php esc_html_e('Background Repeat:', 'ctct'); ?></span>
						<select name="bgrepeat" id="bgrepeat">
							<option <?php ctct_check_select($form,'bgrepeat', 'repeat',true); ?> value="repeat"><?php esc_html_e('Repeat', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'bgrepeat', 'no-repeat'); ?> value="no-repeat"><?php esc_html_e('No Repeat', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'bgrepeat', 'repeat-x'); ?> value="repeat-x"><?php esc_html_e('Repeat-X (Horizontal)', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'bgrepeat', 'repeat-y'); ?> value="repeat-y"><?php esc_html_e('Repeat-Y (Vertical)', 'ctct'); ?></option>
						</select>
					</label></p>
					<!-- <p class="howto">Choose the background alignment: Horizontal / Vertical</p> -->
					<p><label class="howto" for="bgpos"><span><?php esc_html_e('Background Position:', 'ctct'); ?></span>
						<select name="bgpos" id="bgpos">
							<option <?php ctct_check_select($form,'bgpos', 'left top',true); ?> value="left top"><?php esc_html_e('Left/Top', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'bgpos', 'center top'); ?> value="center top"><?php esc_html_e('Center/Top', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'bgpos', 'right top'); ?> value="right top"><?php esc_html_e('Right/Top', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'bgpos', 'left center'); ?> value="left center"><?php esc_html_e('Left/Center', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'bgpos', 'center center'); ?> value="center center"><?php esc_html_e('Center/Center', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'bgpos', 'right center'); ?> value="right center"><?php esc_html_e('Right/Center', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'bgpos', 'left bottom'); ?> value="left bottom"><?php esc_html_e('Left/Bottom', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'bgpos', 'center bottom'); ?> value="center bottom"><?php esc_html_e('Center/Bottom', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'bgpos', 'right bottom'); ?> value="right bottom"><?php esc_html_e('Right/Bottom', 'ctct'); ?></option>
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

	<div id="bordercoloritem" class="block">
		<label for="bordercolor" class="howto block"><span><?php esc_html_e('Border Color:', 'ctct'); ?></span></label>
		<div class="input">
			<input type="hidden" id="bordercolor" name="bordercolor" class="wpcolor" value="<?php ctct_input_value($form, 'bordercolor', '#000000'); ?>" />
		</div>
	</div>

	<div id="borderwidthitem" class="block cc-has-slider">
		<label for="borderwidth" class="howto">

			<span><?php esc_html_e('Border Width', 'ctct'); ?><tt><?php ctct_input_value($form, 'borderwidth', '4'); ?>px</tt></span>
			<div class="block" id="borderwidth-slider"></div>
			<input id="borderwidth" name="borderwidth" type="hidden" value="<?php ctct_input_value($form, 'borderwidth', '4'); ?>" />
		</label>
	</div>

	<div class="borderradius cc-has-slider">
		<label for="borderradius" class="howto block"><span><?php esc_html_e('Rounded Corner Radius', 'ctct'); ?><tt><?php ctct_input_value($form, 'borderradius', '0'); ?>px</tt></span>

			<div class="block" id="borderradius-slider"></div>
			<input id="borderradius" name="borderradius" type="hidden" value="<?php ctct_input_value($form, 'borderradius', '0'); ?>" />
		</label>
	</div>
<?php
}

function cc_form_meta_box_formdesign($post, $metabox=array()) {
	$form = $metabox['args'][0];
	?>		<div class="cc-has-slider">
				<label for="paddingwidth" class="howto block"><span><?php esc_html_e('Form Padding', 'ctct'); ?><tt><?php ctct_input_value($form, 'paddingwidth', '10'); ?>px</tt></span>
					<?php constant_contact_tip(__('Padding is the space between the outside of the form and the content inside the form; it\'s visual insulation.', 'ctct')); ?>
					<div class="block" id="paddingwidth-slider"></div>
					<input id="paddingwidth" name="paddingwidth" type="hidden" value="<?php ctct_input_value($form, 'paddingwidth', '10'); ?>" />
				</label>
			</div>
			<div class="alignleft">
				<label for="width" class="howto block"><span><?php esc_html_e('Form Width', 'ctct'); ?></span> <?php constant_contact_tip(''); ?></label>
				<input type="text" class="" id="width" name="width" value="<?php ctct_input_value($form, 'width', '100'); ?>" size="12" />
				<label for="widthtypeper" style="display:inline;" title="<?php esc_html_e('percent of container width', 'ctct'); ?>"><input type="radio" name="widthtype" id="widthtypeper" <?php ctct_check_radio($form,'widthtype', 'per', true); ?>/>%</label>
				<label for="widthtypepx" style="display:inline;" title="<?php esc_html_e('pixels', 'ctct'); ?>"><input type="radio" name="widthtype" id="widthtypepx" <?php ctct_check_radio($form,'widthtype', 'px'); ?> />px</label>
			</div>

		<div class="clear">
			<label for="lalign" class="howto block"><span><?php esc_html_e('Form Content Alignment', 'ctct'); constant_contact_tip(__('Align the form fields and labels inside the form. <strong>Note:</strong> you can change the alignment of the Custom Text separately inside the Custom Text editor.', 'ctct')); ?></span></label>
			<ul class="categorychecklist form-no-clear">
				<li><label for="lalignleft" class="menu-item-title"><span><input type="radio" id="lalignleft" name="talign" <?php ctct_check_radio($form,'talign', 'left'); ?> /> <?php esc_html_e('Left', 'ctct'); ?></span></label></li>
				<li><label for="laligncenter" class="menu-item-title"><span><input type="radio" id="laligncenter" name="talign" <?php ctct_check_radio($form,'talign', 'center',true); ?> /> <?php esc_html_e('Center', 'ctct'); ?></span></label></li>
				<li><label for="lalignright" class="menu-item-title"><span><input type="radio" id="lalignright" name="talign" <?php ctct_check_radio($form,'talign', 'right'); ?> /> <?php esc_html_e('Right', 'ctct'); ?></span></label></li>
			</ul>
		</div>
		<div>
			<label for="formalign" class="howto block"><span><?php esc_html_e('Form Alignment', 'ctct'); constant_contact_tip(__('Align the form inside your widget or page content. Also called "floating" to the left or right.', 'ctct')); ?></span></label>
			<ul>
				<li><label class="menu-item-title" for='formalignleft'><input type="radio" id="formalignleft" name="formalign" <?php ctct_check_radio($form,'formalign', 'left'); ?> /> <?php esc_html_e('Left', 'ctct'); ?></label></li>
				<li><label class="menu-item-title" for='formaligncenter'><input type="radio" id="formaligncenter" name="formalign" <?php ctct_check_radio($form,'formalign', 'center',true); ?> /> <?php esc_html_e('Center', 'ctct'); ?></label></li>
				<li><label class="menu-item-title" for='formalignright'><input type="radio" id="formalignright" name="formalign" <?php ctct_check_radio($form,'formalign', 'right'); ?> /> <?php esc_html_e('Right', 'ctct'); ?></label></li>
			</ul>
		</div>
<?php
}


function cc_form_meta_box_fontstyles($post, $metabox=array()) {
	$form = $metabox['args'][0];
?>
<fieldset>
				<legend><?php esc_html_e('Text', 'ctct'); ?></legend>
				<p class="description"><?php esc_html_e('These settings are for the Custom Text field. If the checkboxes are checked, the settings also apply to the input labels.', 'ctct'); ?></p>
				<div class="block">
					<label for="tcolor" class="block"><span><?php esc_html_e('Text Color:', 'ctct'); ?></span></label>
					<div class="input">
						<input type="hidden" id="tcolor" name="tcolor" class="wpcolor" value="<?php ctct_input_value($form, 'tcolor', '#accbf7'); ?>" />
					</div>

					<label for="lusc" class="checkbox block howto"><input type="checkbox" class="checkbox" name="lusc" id="lusc" <?php ctct_check_checkbox($form, 'lusc', 'yes', true); ?> /> <span><?php esc_html_e('Use Same Color for Labels', 'ctct'); ?></span></label>
				</div>

				<div class="block">

					<label for="tfont" class="block"><span><?php esc_html_e('Font Family', 'ctct'); constant_contact_tip(__('* next to a font means that the font may not be available on all users\' computers. If not, a similar font will be used.', 'ctct') ); ?></span></label>

					<select id="tfont" name="tfont" class="inline">
						<option <?php ctct_check_select($form,'tfont', 'inherit'); ?> style="font-family: inherit;" id="inherit"><?php esc_html_e('Use Theme Font', 'ctct'); ?></option>
							<optgroup label="Serif">
								<option <?php ctct_check_select($form,'tfont', 'times'); ?> style="font-family: 'Times New Roman', Times, Georgia, serif;" id="times"><?php echo 'Times New Roman'; ?></option>
								<option <?php ctct_check_select($form,'tfont', 'georgia'); ?> style="font-family: Georgia, 'Times New Roman', Times, serif;" id="georgia"><?php echo 'Georgia'; ?></option>
								<option <?php ctct_check_select($form,'tfont', 'palatino'); ?> style="font-family: 'Palatino Linotype', Palatino, 'Book Antiqua',Garamond, Bookman, 'Times New Roman', Times, Georgia, serif" id="palatino"><?php echo 'Palatino *'; ?></option>
								<option <?php ctct_check_select($form,'tfont', 'garamond'); ?> style="font-family: Garamond,'Palatino Linotype', Palatino, Bookman, 'Book Antiqua', 'Times New Roman', Times, Georgia, serif" id="garamond"><?php echo 'Garamond *'; ?></option>
								<option <?php ctct_check_select($form,'tfont', 'bookman'); ?> style="font-family: Bookman,'Palatino Linotype', Palatino, Garamond, 'Book Antiqua','Times New Roman', Times, Georgia, serif" id="bookman"><?php echo 'Bookman *'; ?></option>
							</optgroup>
							<optgroup label="Sans-Serif">
								<option <?php ctct_check_select($form,'tfont', 'helvetica',true); ?> style="font-family: 'Helvetica Neue', Arial, Helvetica, Geneva, sans-serif;" id="helvetica"><?php echo 'Helvetica'; ?></option>
								<option <?php ctct_check_select($form,'tfont', 'arial'); ?> style="font-family:Arial, Helvetica, sans-serif;" id="arial"><?php echo 'Arial'; ?></option>
								<option <?php ctct_check_select($form,'tfont', 'lucidagrande'); ?> style="font-family: 'Lucida Grande', 'Lucida Sans Unicode', Lucida, Verdana, sans-serif;" id="lucida"><?php echo 'Lucida Grande'; ?></option>
								<option <?php ctct_check_select($form,'tfont', 'verdana'); ?> style="font-family: Verdana, 'Lucida Grande', Lucida, TrebuchetMS, 'Trebuchet MS', Helvetica, Arial, sans-serif;" id="bookman"><?php echo 'Verdana'; ?></option>
								<option <?php ctct_check_select($form,'tfont', 'trebuchet'); ?> style="font-family:'Trebuchet MS', Trebuchet, sans-serif;" id="trebuchet"><?php echo 'Trebuchet MS'; ?></option>
								<option <?php ctct_check_select($form,'tfont', 'tahoma'); ?> style="font-family:Tahoma, Verdana, Arial, sans-serif;" id="tahoma"><?php echo 'Tahoma'; ?></option>
								<option <?php ctct_check_select($form,'tfont', 'franklin'); ?> style="font-family:'Franklin Gothic Medium','Arial Narrow Bold',Arial,sans-serif;" id="franklin"><?php echo 'Franklin Gothic'; ?></option>
								<option <?php ctct_check_select($form,'tfont', 'impact'); ?> style="font-family:Impact, Chicago, 'Arial Black', Arial, sans-serif;" id="impact"><?php echo 'Impact *'; ?></option>
							  	<option <?php ctct_check_select($form,'tfont', 'arialblack'); ?> style="font-family:'Arial Black',Impact, Arial, sans-serif;" id="arial-black"><?php echo 'Arial Black'; ?></option>
								<option <?php ctct_check_select($form,'tfont', 'gillsans'); ?> style="font-family:'Gill Sans','Gill Sans MT', 'Trebuchet MS', Trebuchet, Verdana, sans-serif;" id="gill"><?php echo 'Gill Sans *'; ?></option>
							</optgroup>
							<optgroup label="Mono">
								<option <?php ctct_check_select($form,'tfont', 'courier'); ?> style="font-family: 'Courier New', Courier, monospace;" id="courier"><?php echo 'Courier New'; ?></option>
								<option <?php ctct_check_select($form,'tfont', 'lucidaconsole'); ?> style="font-family: 'Lucida Console', Monaco, monospace;" id="lucida-console"><?php echo 'Lucida Console'; ?></option>
							</optgroup>
							<optgroup label="Cursive">
								<option <?php ctct_check_select($form,'tfont', 'comicsans'); ?> style="font-family:'Comic Sans MS','Comic Sans', Sand, 'Trebuchet MS', Verdana, sans-serif" id="comicsans"><?php echo 'Comic Sans MS'; ?></option>
							</optgroup>
							<optgroup label="Fantasy">
								<option <?php ctct_check_select($form,'tfont', 'papyrus'); ?> style="font-family: Papyrus, 'Palatino Linotype', Palatino, Bookman, fantasy" id="papyrus"><?php echo 'Papyrus'; ?></option>
							</optgroup>
					</select>

					<label for="lusf" class="block howto checkbox"><input type="checkbox" name="lusf" class="checkbox" id="lusf" rel="lfont" <?php ctct_check_checkbox($form, 'lusf', 'yes', true); ?> /> <span><?php esc_html_e('Use Same Font for Labels', 'ctct'); ?></span></label>
				</div>
			</fieldset>
			<fieldset>
				<legend><?php esc_html_e('Label', 'ctct'); ?></legend>

				<p class="description"><?php esc_html_e('These settings apply to the label text above the inputs.', 'ctct'); ?></p>
				<div id="labelcolorli" class="block">
					<label for="tcolor" class="howto block"><span><?php esc_html_e('Label Color:', 'ctct'); ?></span></label>
					<div class="input"><input type="hidden" id="lcolor" name="lcolor" class="wpcolor" value="<?php ctct_input_value($form, 'lcolor', '#accbf7'); ?>" /></div>
				</div>

				<div class="block">
					<label for="lpad" class="howto">
						<span class="block"><?php esc_html_e('Label Padding', 'ctct'); constant_contact_tip(__('One "em" is equal to the height of the current font size.', 'ctct')); ?></span>
						<div class="block">
						<select id="lpad" name="lpad">
							<option<?php ctct_check_select($form,'lpad', '0'); ?> value="0"><?php esc_html_e('None', 'ctct'); ?></option>
							<option<?php ctct_check_select($form,'lpad', '.25'); ?> value=".25"><?php echo '.2 em'; ?></option>
							<option<?php ctct_check_select($form,'lpad', '.5'); ?> value=".5"><?php echo '.5 em'; ?></option>
							<option<?php ctct_check_select($form,'lpad', '.75', true); ?> value=".75"><?php echo '.75 em'; ?></option>
							<option<?php ctct_check_select($form,'lpad', '1'); ?> value="1"><?php echo '1 em'; ?></option>
							<option<?php ctct_check_select($form,'lpad', '1.25'); ?> value="1.25"><?php echo '1.25 em'; ?></option>
							<option<?php ctct_check_select($form,'lpad', '1.5'); ?> value="1.5"><?php echo '1.5 em'; ?></option>
						</select>
						</div>
					</label>
				</div>

				<div id="lfontli">
					<label for="lfont" id="lfontlabel" class="howto block"><span><?php esc_html_e('Label Font', 'ctct'); ?></span></label>
					<select id="lfont" name="lfont" class="inline">
						<option <?php ctct_check_select($form,'tfont', 'inherit'); ?> style="font-family: inherit;" id="inherit"><?php esc_html_e('Use Theme Font', 'ctct'); ?></option>
						<optgroup label="Serif">
							<option <?php ctct_check_select($form,'lfont', 'times'); ?> style="font-family: 'Times New Roman', Times, Georgia, serif;" id="times"><?php esc_html_e('Times New Roman', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'georgia'); ?> style="font-family: Georgia, 'Times New Roman', Times, serif;" id="georgia"><?php esc_html_e('Georgia', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'palatino'); ?> style="font-family: 'Palatino Linotype', Palatino, 'Book Antiqua',Garamond, Bookman, 'Times New Roman', Times, Georgia, serif" id="palatino"><?php esc_html_e('Palatino *', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'garamond'); ?> style="font-family: Garamond,'Palatino Linotype', Palatino, Bookman, 'Book Antiqua', 'Times New Roman', Times, Georgia, serif" id="garamond"><?php esc_html_e('Garamond *', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'bookman'); ?> style="font-family: Bookman,'Palatino Linotype', Palatino, Garamond, 'Book Antiqua','Times New Roman', Times, Georgia, serif" id="bookman"><?php esc_html_e('Bookman *', 'ctct'); ?></option>
						</optgroup>
						<optgroup label="Sans-Serif">
							<option <?php ctct_check_select($form,'lfont', 'helvetica'); ?> style="font-family: 'Helvetica Neue', Arial, Helvetica, Geneva, sans-serif;" id="helvetica"><?php esc_html_e('Helvetica', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'arial'); ?> style="font-family:Arial, Helvetica, sans-serif;" id="arial"><?php esc_html_e('Arial', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'lucidagrande'); ?> style="font-family: 'Lucida Grande', 'Lucida Sans Unicode', Lucida, Verdana, sans-serif;" id="lucida"><?php esc_html_e('Lucida Grande', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'verdana'); ?> style="font-family: Verdana, 'Lucida Grande', Lucida, TrebuchetMS, 'Trebuchet MS', Helvetica, Arial, sans-serif;" id="bookman"><?php esc_html_e('Verdana', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'trebuchet'); ?> style="font-family:'Trebuchet MS', Trebuchet, sans-serif;" id="trebuchet"><?php esc_html_e('Trebuchet MS', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'tahoma'); ?> style="font-family:Tahoma, Verdana, Arial, sans-serif;" id="tahoma"><?php esc_html_e('Tahoma', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'franklin'); ?> style="font-family:'Franklin Gothic Medium','Arial Narrow Bold',Arial,sans-serif;" id="franklin"><?php esc_html_e('Franklin Gothic', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'impact'); ?> style="font-family:Impact, Chicago, 'Arial Black', Arial, sans-serif;" id="impact"><?php esc_html_e('Impact *', 'ctct'); ?></option>
						  	<option <?php ctct_check_select($form,'lfont', 'arialblack'); ?> style="font-family:'Arial Black',Impact, Arial, sans-serif;" id="arial-black"><?php esc_html_e('Arial Black', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'gillsans'); ?> style="font-family:'Gill Sans','Gill Sans MT', 'Trebuchet MS', Trebuchet, Verdana, sans-serif;" id="gill"><?php esc_html_e('Gill Sans *', 'ctct'); ?></option>
						</optgroup>
						<optgroup label="Mono">
							<option <?php ctct_check_select($form,'lfont', 'courier'); ?> style="font-family: 'Courier New', Courier, monospace;" id="courier"><?php esc_html_e('Courier New', 'ctct'); ?></option>
							<option <?php ctct_check_select($form,'lfont', 'lucidaconsole'); ?> style="font-family: 'Lucida Console', Monaco, monospace;" id="lucida-console"><?php esc_html_e('Lucida Console', 'ctct'); ?></option>
						</optgroup>
						<optgroup label="Cursive">
							<option <?php ctct_check_select($form,'lfont', 'comicsans'); ?> style="font-family:'Comic Sans MS','Comic Sans', Sand, 'Trebuchet MS', Verdana, sans-serif" id="comicsans"><?php esc_html_e('Comic Sans MS', 'ctct'); ?></option>
						</optgroup>
						<optgroup label="Fantasy">
							<option <?php ctct_check_select($form,'lfont', 'papyrus'); ?> style="font-family: Papyrus, 'Palatino Linotype', Palatino, Bookman, fantasy" id="papyrus"><?php esc_html_e('Papyrus', 'ctct'); ?></option>
						</optgroup>
					</select>
					<small class="asterix"><?php esc_html_e('* This font is popular, but not a "web-safe" font. If not available on an user\'s computer, it will default to a similar font.', 'ctct'); ?></small>
				</div>

				<label for="lsize" class="howto block"><span><?php esc_html_e('Label Font Size', 'ctct'); ?></span></label>
				<select id="lsize" class="nomargin" name="lsize">
				<?php

					$i = 7; $default = 12;
					while( $i < 50 ) {
						?>
						<option<?php ctct_check_select($form,'lsize', $i, ($i === $default) ); ?> value="<?php echo $i; ?>"><?php printf( '%d px', $i ); ?></option>
						<?php
						$i++;
					}
				?>
				</select>
		</fieldset>
<?php
}

