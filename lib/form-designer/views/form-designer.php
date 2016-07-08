<?php

	// TODO: Add Gravity Forms Notice
	// constant_contact_add_gravity_forms_notice();

	if(isset($messages) && is_array($messages)) {
		foreach( $messages as $message ) :
			echo $message . "\n";
		endforeach;
	}
	$formURL = '';
	if($cc_form_selected_id != -1) {
		$formURL = '&form='.(int)$cc_form_selected_id;
	}
	?>
	<div class="hide-if-js">
		<div class="widefat form-table">
			<div class="wrap" style="width:60%; padding:10px 15px;">
				<h2><?php _e('This form creator requires Javascript.', 'constant-contact-api'); ?></h2>
				<p class="description"><?php _e('The form designer uses a lot of Javascript to put together the sweet looking forms that it does, so please <a href="https://www.google.com/adsense/support/bin/answer.py?hl=en&answer=12654" target="_blank">turn Javascript on in your browser</a> and let\'s make some forms together!', 'constant-contact-api'); ?></p>
			</div>
		</div>
	</div>
<div class="nav-menus-php">
	<div class="manage-menus">
		<a class="alignright button button-danger confirm" data-confirm="<?php _e('Delete all form Data? All forms and their settings will be deleted. This cannot be undone. Continue?', 'constant-contact-api'); ?>" data-confirm-again="<?php _e('Are you certain? Forms will be PERMANENTLY DELETED. You will have to re-create all your forms. Continue?', 'constant-contact-api'); ?>" href="<?php echo wp_nonce_url( admin_url('admin.php?page=constant-contact-forms&action=delete_all&amp;form=all'), 'delete-all' ); ?>" id="delete_all_forms"><?php _e('Delete All Forms', 'constant-contact-api'); ?></a>
		<form method="get" action="<?php echo admin_url('admin.php'); ?>">
			<input type="hidden" name="action" value="edit" />
			<input type="hidden" name="page" value="constant-contact-forms" />
			<label for="menu" class="selected-menu"><?php esc_html_e('Select a form to edit:', 'constant-contact-api'); ?></label>

			<select name="form" id="menu">
				<option>&mdash;<?php esc_html_e('Select A Form', 'constant-contact-api');?>&mdash;</option>
				<?php
				foreach( (array) $cc_forms as $_cc_form ) {
					if(!isset($_cc_form['cc-form-id'])) { continue; }

					$label = !empty($_cc_form['truncated_name']) ? esc_html( $_cc_form['truncated_name'] ) : sprintf(__('Form #%d', 'constant-contact-api'), ($_cc_form['cc-form-id'] + 1));

					$selected = selected( $cc_form_selected_id, $_cc_form['cc-form-id'], false );

					printf( '<option value="%d"%s>%s</option>', $_cc_form['cc-form-id'], $selected, esc_html( $label ) );
				}
				?>
			</select>

			<span class="submit-btn"><input type="submit" class="button-secondary" value="<?php esc_attr_e('Select', 'constant-contact-api'); ?>"></span>

			<?php

			$add_form_url = esc_url(add_query_arg(
				array(
					'action' => 'edit',
					'form' => -1,
				),
				admin_url( 'admin.php?page=constant-contact-forms' )
			));

			$new_form_link = sprintf( esc_html_x('or %screate a new form%s.', 'The strings are HTML link tags for a link to create a new form.', 'constant-contact-api'), '<a href="'.$add_form_url.'">', '</a>' );
			echo '<span class="add-new-menu-action">
				'.$new_form_link.'
			</span>';

			?>
		</form>
	</div>

	<?php

	$form = wp_get_cc_form($cc_form_selected_id);

	?>

	<form id="cc-form-settings" action="<?php echo esc_url( admin_url( 'admin.php?page=constant-contact-forms'.$formURL ) ); ?>" method="post" enctype="multipart/form-data" class="hide-if-no-js">
	<div id="nav-menus-frame">
	<div id="menu-settings-column" class="metabox-holder">

		<div id="settings">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				<?php do_meta_boxes( 'constant-contact-form', 'core', null ); ?>
				<?php do_accordion_sections( 'constant-contact-form', 'side', null ); ?>
			</div>
		</div>

	</div><!-- /#menu-settings-column -->
	<div id="menu-management-liquid">

		<div id="menu-management">
			<div id="examplewrapper">
				<h3 class="legend"><?php _e('Form Preview', 'constant-contact-api'); ?></h3>
				<div class="grabber"></div>

				<a href="#" id="togglePreview"><?php _e('Toggle Preview', 'constant-contact-api'); ?><span class="spinner" id="ctct-loading-spinner" aria-live="assertive" aria-label="<?php esc_attr_e( 'Form is being updated.', 'constant-contact-api' ); ?>"></span></a>
			</div><!-- end ExampleWrapper -->
			<div class="menu-edit">
				<div id="form-fields">
					<div id="nav-menu-header">
						<?php
						wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
						wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
						wp_nonce_field( 'update-cc-form-'.(int)$cc_form_selected_id, 'update-cc-form-nonce', false );
						?>
						<input type="hidden" name="action" value="update" />
						<input type="hidden" name="cc-form-id" id="cc-form-id" value="<?php echo (int)$cc_form_selected_id; ?>" />

						<div class="major-publishing-actions wp-clearfix">
							<label class="menu-name-label" for="form-name">
								<span><?php esc_html_e('Form Name', 'constant-contact-api'); constant_contact_tip(__('Only for internal use - the outside world won\'t see this name.', 'constant-contact-api'), true ); ?></span>

								<?php $title = esc_attr__('Enter form name here', 'constant-contact-api'); ?>
								<input name="form-name" id="form-name" type="text" class="widefat text menu-name regular-text menu-item-textbox <?php if ( $cc_form_selected_id == -1 ) {  ?> input-with-default-title<?php } ?>" title="<?php echo $title ?>" value="<?php echo isset( $form['form-name'] ) ? esc_attr( $form['form-name']  ) : ''; ?>" />


							</label>
							<div class="publishing-action">
								<input class="button button-primary button-large menu-save" name="save_form" type="submit" value="<?php ($cc_form_selected_id != 0 && empty($cc_form_selected_id)) ? esc_attr_e('Create Form', 'constant-contact-api') : esc_attr_e('Save Form', 'constant-contact-api'); ?>" />
							</div><!-- END .publishing-action -->
						</div>

					</div><!-- END #nav-menu-header -->
					<div id="post-body">
						<div id="post-body-content">
							<?php
								cc_form_meta_box_formfields($form);
							?>
						</div><!-- /#post-body-content -->
					</div><!-- /#post-body -->

					<div id="nav-menu-footer">
						<div class="major-publishing-actions wp-clearfix">
							<span class="delete-action">
								<?php if ( $cc_form_selected_id != -1 ) {  ?>
								<a class="submitdelete deletion menu-delete" href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=constant-contact-forms&action=delete&amp;form=' . $cc_form_selected_id), 'delete-cc_form-' . $cc_form_selected_id ) ); ?>"  onclick="return confirm('<?php _e('Are you sure you want to delete this form? It will be deleted permanently.', 'constant-contact-api'); ?>');"><?php _e('Delete Form', 'constant-contact-api'); ?></a>
							<?php  } ?>
							</span><!-- END .delete-action -->

							<div class="publishing-action">
								<input class="button button-primary button-large menu-save" name="save_form" type="submit" value="<?php ($cc_form_selected_id != 0 && empty($cc_form_selected_id)) ? esc_attr_e('Create Form', 'constant-contact-api') : esc_attr_e('Save Form', 'constant-contact-api'); ?>" />
							</div><!-- END .publishing-action -->
						</div><!-- END .major-publishing-actions -->
					</div>

				</div><!-- /#update-nav-menu -->
			</div><!-- /.menu-edit -->
		</div> <!-- /#menu-management -->
	</div><!-- /#menu-management-liquid -->
	</div><!-- /#nav-menus-frame -->
</form><!-- /#tha-form -->
</div>