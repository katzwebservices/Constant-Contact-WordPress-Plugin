<a class="alignright button button-danger confirm" data-confirm="<?php _e('Delete all form Data? All forms and their settings will be deleted. This cannot be undone. Continue?', 'constant-contact-api'); ?>" data-confirm-again="<?php _e('Are you certain? Forms will be PERMANENTLY DELETED. You will have to re-create all your forms. Continue?', 'constant-contact-api'); ?>" href="<?php echo wp_nonce_url( admin_url('admin.php?page=constant-contact-forms&action=delete_all&amp;form=all'), 'delete-all' ); ?>" id="delete_all_forms"><?php _e('Delete All Forms', 'constant-contact-api'); ?></a>

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
	<form id="cc-form-settings" action="<?php echo admin_url( 'admin.php?page=constant-contact-forms'.$formURL ); ?>" method="post" enctype="multipart/form-data" class="hide-if-no-js">
	<div id="nav-menus-frame">
	<div id="menu-settings-column" class="metabox-holder">

		<div id="settings">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				<?php do_meta_boxes( 'constant-contact-form', 'side', null ); ?>
			</div>
		</div>

	</div><!-- /#menu-settings-column -->
	<div id="menu-management-liquid">
		<!-- <div id="menu-management"> -->

			<style type="text/css">
			.nav-tab a {text-decoration: none; width: 100%; height: 100%; display: block;}
			.nav-tab a:link,.nav-tab a:visited {color: #aaa;}
			.nav-tab a:hover, .nav-tab a:active { color: #d54e21; }
			.nav-tab-active a { color: #464646;}
			</style>
			<div class="nav-tabs">
				<h2 class="nav-tab-wrapper">
				<?php

				foreach( (array) $cc_forms as $_cc_form ) {
					if(!isset($_cc_form['cc-form-id'])) { continue; }
					?>
					<a href="<?php
							echo esc_url(add_query_arg(
								array(
									'action' => 'edit',
									'form' => $_cc_form['cc-form-id'],
								),
								admin_url( 'admin.php?page=constant-contact-forms' )
							));
						?>" class="hide-if-no-js nav-tab<?php if ($cc_form_selected_id == $_cc_form['cc-form-id'] ) { echo ' nav-tab-active'; } ?>">
							<?php echo !empty($_cc_form['truncated_name']) ? esc_html( $_cc_form['truncated_name'] ) : sprintf(__('Form #%d', 'constant-contact-api'), ($_cc_form['cc-form-id'] + 1)); ?>
					</a>
					<?php
				}
				if ( -1 == $cc_form_selected_id ) : ?><span class="nav-tab menu-add-new nav-tab-active">
					<?php printf( '<abbr title="%s">+</abbr>', esc_html__( 'Add form','constant-contact-api' ) ); ?>
				</span><?php else : ?><a href="<?php
					echo esc_url(add_query_arg(
						array(
							'action' => 'edit',
							'form' => -1,
						),
						admin_url( 'admin.php?page=constant-contact-forms' )
					));
				?>" class="nav-tab menu-add-new">
					<?php printf( '<abbr title="%s">+</abbr>', esc_html__( 'Add form','constant-contact-api' ) ); ?>
				</a><?php endif; ?>
				</h2>
			</div>
			<div class="menu-edit" style="border:none;">
				<div id="form-fields">
					<div id="nav-menu-header">
						<?php
						wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
						wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
						wp_nonce_field( 'update-cc-form', 'update-cc-form-nonce' );
						?>
						<input type="hidden" name="action" value="update" />
						<input type="hidden" name="cc-form-id" id="cc-form-id" value="<?php echo (int)$cc_form_selected_id; ?>" />
					</div><!-- END #nav-menu-header -->
					<div id="post-body">
						<div id="post-body-content">
							<?php

								$form = wp_get_cc_form($cc_form_selected_id);

								cc_form_meta_box_formfields($form);
							?>
							<div id="examplewrapper">
								<h3 class="legend"><?php _e('Form Preview', 'constant-contact-api'); ?></h3>
								<div class="grabber"></div>

								<a href="#" id="togglePreview"><?php _e('Toggle Preview', 'constant-contact-api'); ?></a>
							</div><!-- end ExampleWrapper -->

						</div><!-- /#post-body-content -->
						<div class="clear"></div>
					</div><!-- /#post-body -->
				</div><!-- /#update-nav-menu -->
			</div><!-- /.menu-edit -->
		<!-- </div> /#menu-management -->
	</div><!-- /#menu-management-liquid -->
	</div><!-- /#nav-menus-frame -->
</form><!-- /#tha-form -->