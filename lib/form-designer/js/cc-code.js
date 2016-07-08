jQuery.noConflict();

kwsdebug = empty( ScriptParams.debug );

/**
 * Update the content preview without needing to post the content.
 *
 * @usedby ccfg_tiny_mce_before_init() PHP
 *
 * @param  {tinymce} inst [description]
 */
function triggerTextUpdate(inst) {
	var $intro = jQuery('.kws_form .cc_intro');
	if( $intro.length > 0) {
		var ed = tinyMCE.activeEditor;
		var content = ed.getContent({format : 'raw'});
		$intro.html(content);
	}
}

jQuery(document).ready(function($) {

	$('a[rel*=external]').attr('target', '_blank');

	$('input.wpcolor').wpColorPicker({
		palettes: false,
		change: function(event, ui){
			if(kwsdebug) { console.log( 'wpColorPicker Change', event ); }
			$(this).data('doingchange', 1);
			$(this).trigger('colorchange');
			$(this).data('doingchange', null);
		}
	});

	$('#delete_all_forms').appendTo( '#screen-meta-links' );

	// Tabbed interface
	$("#tabs,#formfields-select-tabs").tabs({ cookie: { expires: 60 } }).addClass('ui-helper-clearfix');
	$("#tabs li").removeClass('ui-corner-top').addClass('ui-corner-left');
	$("#tabs li li").removeClass('ui-corner-left');

	if($('input.menu-item-checkbox[value=intro]').is(':checked')) {
		$('.wp-editor-textarea').show();
	}

	function cc_create_slider( target_input_id , css_parameter, min, max, step ) {

		var $tt = $('label[for="' + target_input_id.replace( '#', '' ) +'"] tt' );

		if( kwsdebug ) {
			console.log( target_input_id + "-slider", target_input_id , css_parameter, min, max, step );
		}

		$( target_input_id + "-slider" ).slider({
			min: min,
			max: max,
			step: step,
			value: $(target_input_id).val(),

			// Dynamically update the values
			slide: function( event, ui ) {
				$tt.text( ui.value + 'px' );
				$('#examplewrapper').find('.kws_form').css( css_parameter, ui.value );
			},

			// Once landed on, update the value and trigger ajax
			stop: function( event, ui ) {
			  $(target_input_id).val( ui.value ).trigger('change');
			}
		});

	}

	cc_create_slider( '#borderwidth', 'border-width', 0, 50, 1 );
	cc_create_slider( '#borderradius', 'border-radius', 0, 100, 5 );
	cc_create_slider( '#paddingwidth', 'padding', 0, 100, 5 );


///
/// START BODY BINDINGS
///

$('body')

	.on('click', '.selectall, .selectall input', function() {
		$(this).select();
	})

	// Toggle design
	.on('ready change', '#toggledesign', function( event) {
		console.log('toggledesign change');
		event.stopImmediatePropagation();
		toggleDesign();
	})

	// Radio Buttons & Checkboxes
	.on('change', 'input:checkbox[name^="formfields"]', function( event ) {
		event.stopImmediatePropagation();
		showHideFormFields($(this));
		updateSortOrder();
		updateFormFields('change name formfields');
		$('textarea.wp-editor').trigger('change');
	})

	.on('change', 'input[name=list_format]', function( event ) {
		event.stopImmediatePropagation();

		var format = $(this).val();
		var checked = 'checked';

		switch( format ) {
			case 'hidden':
				checked = null;
				break;
			default:
				break;
		}

		$('input[name="formfields[lists]"]')
			.attr('checked', checked )
			.trigger('change');

	})

	.on('change', 'input:checkbox[name^="f"]', function( event ) {
		event.stopImmediatePropagation();
		updateSortOrder();
		updateFormFields('input:checkbox[f] bind');
	})

	// Text inputs
	.on('change keyup', "input#bgimage", function( event ) {
		event.stopImmediatePropagation();
		updateBackgroundURL();
	})

	.on('change', '#form-fields input.labelValue,#form-fields input.labelDefault', function( event ) {
		event.stopImmediatePropagation();
		updateFormFields('labelDefault change keyup');
	})

	.on('click', 'li.menu-item .item-edit', function(e) {
		e.preventDefault();
		$('.menu-item-settings', $(this).parents('li.formfield')).toggle();
		return false;
	})

	.on('change', 'input:not(#examplewrapper input):not(.no-update),select:not(#examplewrapper select):not(.no-update):not(#menu)', function() {
		$('#cc-form-settings').trigger('stylechange');
	})

	.on('click', '#togglePreview', function(e){
		//e.preventDefault();
		$('#togglePreview').toggleClass('hidden');
		$('#examplewrapper .grabber').toggle(0, function() {
			if($('#examplewrapper .grabber').is(':visible')) {

				$('#cc-form-settings').trigger('stylechange', function (  ) {
					$('#examplewrapper .legend').show();
				});

				$(window).trigger('resize');
			} else {
				$('#examplewrapper .legend').hide();
				$('#examplewrapper').css({
					'width': '0',
					'min-width': '200px'
				});
			}
		});
		return false;
	})

	// Select dropdowns
	.on('change', '#borderwidth', function(event ) {
		if(kwsdebug) { console.log('#borderwidth change'); }
		event.stopImmediatePropagation();
		updateWidthCalculator();
		updateBorderWidth();
		$(window).trigger('resize');
	})

	.on('change', '#tfont,#tsize,input[name="talign"]', function(event) {
		if(kwsdebug) { console.log('#tfont,#tsize,input[name="talign"] change'); }
		event.stopImmediatePropagation();
		updateStyle();
	})

	.on('change', '#lfont,#lsize,input[name="lalign"]', function(event) {
		if(kwsdebug) { console.log('#lfont,#lsize,input[name="lalign"] change'); }
		event.stopImmediatePropagation();
		updateLabelStyle();
	})

	.on('change', '#gradtype', function(event) {
		event.stopImmediatePropagation();
		updateBackgroundType('gradtype');
	})

	.on('colorchange', '#color6,#color2', function(event) {
		event.stopImmediatePropagation();
		updateBackgroundType('color6 or color2');
	})

	.on('colorchange', '#bordercolor', function(event) {
		event.stopImmediatePropagation();
		updateBorderColor();
	})

	.on('colorchange', '#tcolor', function(event) {
		event.stopImmediatePropagation();
		updateTextColor();
	})

	.on('colorchange', '#lcolor', function(event) {
		event.stopImmediatePropagation();
		updateLabelColor();
	})

	.on('change', '#bgpos,#bgrepeat', function( event ) {
		event.stopImmediatePropagation();
		updateBackgroundURL();
	})

	.on('change', 'input[name="backgroundtype"]', function( event ) {
		event.stopImmediatePropagation();
		updateBackgroundType('backgroundtype');
	})

	.on('change', 'input[id^=lus]', function( event ) {
		event.stopImmediatePropagation();
		updateLabelSame();
	})

	// The bold and italic label buttons
	.on('click ready', 'label.labelStyle', function( event ) {
		event.stopImmediatePropagation();
		if($('input[type=checkbox]:checked', $(this)).length > 0) {
			$(this).addClass('checked');
		} else {
			$(this).removeClass('checked');
		}

	})

	// Pattern selection
	.on('click', 'ul#patternList li', function( event ){
		event.stopImmediatePropagation();
		updatePattern($(this));
	})

	/**
	 * Make sure that when switching to percentage from px that it's never more than 100%
	 *
	 * Though you can make it more than 100% when in % settings.
	 * @return {void}
	 */
	.on('change', 'input[name=widthtype]', function( e ) {

		// Switched to %
		if( $('input[name=widthtype]').val() === 'per' ) {
			if( ( $('#width').val() * 1 ) > 100 ) {

				// Store backup value in px
				$('#width').attr('data-px', $('#width').val() );

				// Set the value to 100
				$('#width').val( 100 );
			}
		}
		// Switched to px
		else {
			var backup_px = $('#width').attr('data-px');
			if( !empty( backup_px ) ) {
				$('#width').val( backup_px );
				$('#width').attr('data-px', null );
			}
		}

	})

	.on('change keyup', "#paddingwidth,input[name=widthtype],#width", function(e) {
		e.stopImmediatePropagation();

		if(eventKeys(e) || e.keyCode === 46 || e.keyCode === 8) {
			// If it's not an arrow, tab, etc. and not delete or backspace, process the sucker!
			updateWidthCalculator();
		}
	})

	.on('stylechange', "#cc-form-settings", function( event ) {
		event.stopImmediatePropagation();
		generateForm('stylechange triggered by '+ $(event.target).attr('id') );
	})

	.on('change', "#cc-form-settings input:not(#examplewrapper input):not(.no-update), #cc-form-settings textarea:not(#examplewrapper textarea):not(.no-update), #cc-form-settings select:not(#examplewrapper select):not(.no-update):not(#menu)",	function( event ) {
			if(kwsdebug) {
				console.log('Changed settings input', event);
			}
			event.stopImmediatePropagation();
			generateForm('#cc-form-settings style - '+$(this).attr('id'));
		}
	)

	.on('click', 'label img', function(){
		$(this).closest('input[type=radio]').click();
	})

	.on('click', "a.toggleMore", function() {

		$(this).parents('ul').find('.toggleMore:not(a):not(:has(input[type=checkbox]:checked))').toggle('fast');

		var text = $(this).text();

		var text2 = text.replace( ScriptParams.labels.show , ScriptParams.labels.hide );

		if(text2 === text) {
			$(this).text(text.replace(ScriptParams.labels.hide, ScriptParams.labels.show));
		} else {
			$(this).text(text2);
		}

		return false;
	});

///
/// END BODY BINDINGS
///

	function useDesign() {
		return $('#toggledesign').is(':checked');
	}

	/**
	 * Turn on or off styling the forms
	 */
	function toggleDesign( dont_generate_form ) {

		$designdivs = $('#designoptions,#backgroundoptions,#border,#fontstyles,#formdesign,.grabber,#examplewrapper,.labelStyle.mce_bold,.labelStyle.mce_italic');

		$('.safesubscribesample').css('background', 'transparent none');

		if( useDesign() ) {
			$designdivs.show();
			if( empty( dont_generate_form ) ) {
				generateForm();
			}
		} else {
			$designdivs.hide();
		}
	}

	function updatePattern( $clickedLI ) {
		var val = '';

		if(empty($clickedLI)) {

			if($("ul#patternList li.selected").length > 0) {
				$clickedLI = $("ul#patternList li.selected");
				val = $clickedLI.attr('title');
			} else {

				val = $('#patternurl').val();

				// If the saved input has a value, use it
				if( empty( val ) ) {

					if($("#bgpattern ul li.selected").length > 0) {
						val = $("#bgpattern ul li.selected").attr('title');
					} else {
						val = $("#bgpattern ul li:first").attr('title');
					}

				}

				$clickedLI = $("ul#patternList li[title*=\""+val+"\"]");
			}
		}

		$("ul#patternList li").removeClass('selected');
		$clickedLI.addClass('selected');
		var url = $clickedLI.attr("title");
		$('#patternurl').val(url);

		updateBackgroundURL(ScriptParams.path+url);
	}

	function generateForm( from ) {

		if(kwsdebug) {
			console.log('generateForm', from);
		}

		// If Form Styler is not being used, no need to generate a form preview.
		// Also, if the preview is hidden, don't bother.
		if( !useDesign() || $('#togglePreview').is('.hidden') ) {
			if(kwsdebug) {
				console.log('No form was generated; design not being used or preview is hidden');
			}
			return;
		}

		/**
		 * Update which form fields are disabled and which are enabled. This prevents non-active form fields from being posted to the form.php script.
		 */
		$('ul.menu li.formfield').find('input,textarea').prop( 'disabled', true );

		// Loop through the checked fields
		$('#formfields_select').find('input[type=checkbox]:checked').each(function() {
			$('input,textarea', $('ul.menu li.formfield').has('input#'+$(this).val())).removeAttr('disabled');
		});

		var ajaxTime= new Date().getTime();

		var date = Date.now();
		var verify = ScriptParams.rand + $('#cc-form-id').val() + date;
		var post_data = {
			rand: ScriptParams.rand,
			form: $( 'form#cc-form-settings' ).serializeArray(),
			verify: verify,
			date: date,
			text: ScriptParams.text,
			path: ScriptParams.path
		};


		if(kwsdebug) { console.log( 'post_data sent to AJAX request: ', post_data ); }

		var $kws_form = $('.grabber .kws_form');
		var $spinner = $('#ctct-loading-spinner');

		$.ajax({
			type: 'POST',
			url: ajaxurl + '?action=ctct_form_designer',
			processData: false, // Don't send the data as a query string; attach to the $_POST request
			contentType: 'application/json',
			isLocal: true,
			dataType: 'json',
			data:  JSON.stringify( post_data ),
			timeout: 15000,
			async: true,
			beforeSend: function() {

				// Add a loading class for this request
				$kws_form.addClass('ctct-loading').addClass('ctct-loading-' + ajaxTime);

				$spinner.css('visibility', 'visible');
				$('#form-fields').find('ul.menu').sortable( "refreshPositions" );
			},
			success: function( data, textStatus, XMLHttpRequest){
				return ctct_process_ajax_success( data, textStatus, XMLHttpRequest );
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				if(kwsdebug) {
					console.log(XMLHttpRequest, textStatus, errorThrown);
				}

				$('.grabber').html('<div class="ctct-ajax-error"><h2><em>Eeep! That didn\'t work...</em></h2><h3>The error: <code>'+errorThrown+'</code></h3></div>').show();
				return false;
			}
		}).done(function (data, textStatus, XMLHttpRequest) {

			// Remove the loading class for this request
			$kws_form.removeClass('ctct-loading-' + ajaxTime );

			// Make sure all ajax requests are done before hiding the loading icon
			if( 0 === $kws_form.filter('[class*=ctct-loading-]').length ) {
				$kws_form.removeClass('ctct-loading');
				$spinner.css( 'visibility', 'hidden' );
			}

			var totalTime = new Date().getTime()-ajaxTime;

			if( kwsdebug ) {
				console.log("request took %s milliseconds. Status: %s. Data: %s", totalTime, textStatus, data);
			}
		});

		return false;

	}

	function ctct_process_ajax_success( data, textStatus, XMLHttpRequest ) {
		if(data) {
			var html = false;
			var css = false;
			var input = false;
			var pre = false;

			if( typeof( data ) === 'string' ) {
				data = jQuery.parseJSON(data);
			}

			if(kwsdebug) {
				console.log( 'AJAX Data Response', data );
			}

			// If we want to pass debug info, this works
			if( !empty( data.pre ) ) {
				$('body').prepend( data.pre );
			}

			if( !empty( data.form ) ) {

				html = data.css + data.form;

				var lists = getPreviewListsInput();

				// Process the lists
				html = html.replace('<!-- %%LISTSELECTION%% -->', lists );

				// Prevent submit
				html = html.replace('action="<!-- %%ACTION%% -->"', 'onsubmit="return false;"');

				// Remove previous form and style tabs
				$('.grabber .kws_form, .grabber style, .grabber .ctct-ajax-error').remove();

				$('.grabber').append( html );

				if( empty( lists ) ) {
					$('.grabber .cc_lists').hide();
				}
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the Lists input for the Preview.
	 * @since 4.0
	 * @returns {string}
	 */
	function getPreviewListsInput() {
		var lists = '';

		var $lists_settings = $('.ctct-lists');

		if( kwsdebug ) { console.log( '.ctct-lists', $lists_settings ); }

		var format = $('.list-selection-format input:checked').val();

		switch(format) {

			case 'checkbox':
				$lists_settings.find('li').has('input:checked').each(function() {
					checked = $('#checked_by_default').is(':checked') ? ' checked="checked"' : '';
					lists = lists + '<li><label><input type="checkbox" '+checked+' /> '+$(this).text()+'</label></li>';
				});
				lists = '<ul class="checkboxes">'+lists+'</ul>';
				break;
			case 'dropdown':
			case 'multiselect':
				$lists_settings.find('li').has('input:checked').each(function() {
					lists = lists + '<option>'+$('label', $(this)).text()+'</option>';
				});
				if(format === 'multiselect') {
					lists = '<select class="kws_clear" multiple="multiple">'+lists+'</select>';
				} else {
					lists = '<select class="kws_clear">'+lists+'</select>';
				}
				break;
		}

		return lists;
	}

	function eventKeys(e) {
		var code = (e.keyCode ? e.keyCode : e.which);
		if (code === 37 || code === 38 || code === 39 || code === 40 || code === 46 || code === 8 || code === 16) {
			return false;
		}else {
			return true;
		}
	}

	function updateWidthCalculator() {
		if($('input[name=widthtype]:checked').val() === 'px') {
		var borderwidth = $('#borderwidth').val() * 2;
		var paddingwidth = $('#paddingwidth').val() * 2;
		var rawwidth = $('#width').val();
		var setwidth = Math.floor(rawwidth) - Math.floor(paddingwidth) - Math.floor(borderwidth);
		var realwidth = Math.floor(rawwidth) + Math.floor(paddingwidth) + Math.floor(borderwidth);
			$('label[for=width] span.ctct_tip').attr('title', '<p><strong>Actual width is '+ realwidth + 'px.</strong> <em>For an form that is '+rawwidth+'px wide, set Form Width to '+setwidth+'px</em></p>').tooltip();
		} else {
			$('label[for=width] span.ctct_tip').attr('title', '<p><strong>Width depends on the size of the container.</strong> For example, for a form in a sidebar, the form\'s width percentage will be based on the width of the sidebar.</p>');
		}
	}

	/**
	 * Sort items based on the position value of input.position
	 * @param  {selector} a Item A
	 * @param  {select} b Item B
	 * @return {int}   1: Move item up; 0: Don't modify item position, or -1: Move item down
	 */
	function ctctSortFields(a,b){

		$input_a = $("input.position", $(a) ).val();
		$input_b = $("input.position", $(b) ).val();

		// If there's no position key set, don't modify the original order
		if( $input_a.length === 0 || $input_b.length === 0 ) {
			return 0;
		} else {
			return $input_a > $input_b ? 1 : -1;
		}

	}

	/**
	 * Add a sorted field menu to the main stage
	 * @return {void}
	 */
	function sortFieldMenu() {

		$('#form-fields')
			.find('ul.menu li.menu-item')
			.sort(ctctSortFields)
			.appendTo('#form-fields ul.menu');
	}

	/**
	 * Show and hide the form fields in the main editor div based on whether Form Fields checkboxes are checked.
	 * @param {jQuery} $clicked You can pass an object or array of objects that are the ones you want to analyze. Otherwise, it grabs all the checkboxes.
	 */
	function showHideFormFields($clicked) {

		if(!$clicked) {
			$clicked = $('#formfields_select input:checkbox');
		} else {
			if(kwsdebug) {
				console.log($clicked);
			}
		}

		$clicked.each(function() {

			var targetLI = $('#form-fields').find('.menu li').has('#'+$(this).val());
			var checked = ($(this).checked || $(this).prop('checked') || $(this).is(':checked'));

			if(!empty(checked)) {

				// Show the textarea editor
				if($clicked.val() === 'intro') {
					$('.wp-editor-textarea').show();
				}

				// Move to main stage
				targetLI.remove().appendTo($('.menu', '#form-fields')).show();

				targetLI
					.find('input,textarea').each(function() {
						$(this).removeAttr('disabled');
					})
					.find('input.checkbox').prop('checked', checked);

			} else {
				if($clicked.val() === 'intro') {
					$('.wp-editor-textarea').hide();
				}
				targetLI.hide()
					.find('input.checkbox').prop('checked', checked)
					.find('input,textarea').each(function() { $(this).attr('disabled', true); });
			}
		});
	}

	function updateFormFields(from) {
		from = from||'';
		if(kwsdebug) { console.log('updateFormFields', from); }

		$('.wp-editor-textarea,ul.menu li.formfield').each(function() {
			updateFormField($(this));
		});

		generateForm('updateFormFields: ' + from );
	}

	function updateFormField($item) {

		// Get the <li> we're working within.
		if( $item.not('li') ) {
			$item = $item.parents('li.formfield');
		}

		var checkbox = $('input.checkbox', $item);

		if(checkbox.is(':checked')) {
			$('.menu-item-settings', $item).show();

			// Set values
			check = {
				id: checkbox.attr( 'id' ),
				val: checkbox.val(),
				name: checkbox.attr( 'name' ),
				rel: checkbox.attr( 'rel' )
			};

			input = {
				textarea: '',
				label: '',
				value: '',
				html: '',
				bold: '',
				italic: '',
				required: '',
				labelHTML: '',
				size: $( 'input.labelSize', $item ).val()
			};

			if(kwsdebug) {
				console.log('check: %s, input: %s', check, input);
			}

			if($('input.labelValue', $item).length > 0) {
				var tempInput = $('input.labelValue', $item);
				input.label = tempInput.val();
			}

			if(check.rel === 'textarea' || $item.hasClass('wp-editor') || $('textarea.labelValue', $item).length > 0 || $('body.mceContentBody', $item).length > 0) {
				input.textarea = true;
			}

			// Update classes
			$item.addClass('checked').addClass('ui-state-active');

			// For textareas, we need to do it differently
			if($('input.labelDefault', $item).length > 0) {
				input.value = $('input.labelDefault', $item).val();
			} else {
				if(empty(tinyMCE)) {
					input.value = $('textarea.labelDefault', $item).html();
				}
			}

			//alert(check.id);
			if($('#'+check.id+'_bold').is(':checked')) { input.bold = true; }
			if($('#'+check.id+'_italic').is(':checked')) { input.italic = true; }
			if($('#'+check.id+'_required').is(':checked')) { input.required = true; }

			//console.debug(input);
			if(check.rel === 'text') {
				input.html = '<input type="text" value="'+input.value+'" size="' + input.size + '" name="'+check.name+'" class="text" id="cc_'+check.id+'" />';
			}
			if(check.rel === 'button' || check.rel === 'submit') {
				input.html = '<input type="submit" value="'+input.value+'" name="'+check.name+'" id="cc_'+check.id+'" />';
			}
			if(check.rel === 'textarea') {
				input.html = $('<textarea>'+input.value+'</textarea>').attr('name', check.name).attr('id', check.id).removeAttr('disabled');
			}

		} else { // If not checked
			//console.debug('Not checked');
			$item.removeClass('checked').removeClass('ui-state-active');
		}
	}

	function updateSortOrder() {
		$('ul.menu li.formfield', '#cc-form-settings').each(function() {
			// Set the position index
			// Each form field has a hidden input.position that is used to sort the fields in the editor
			$('input.position[type=hidden]', $(this)).val($('ul.menu li.formfield input.position').index($('input.position', $(this))));
		});
	}

	$('#form-fields').find('ul.menu').sortable({
		handle: '.menu-item-handle',
		forceHelperSize: true,
		placeholder: 'sortable-placeholder',
		start: function(e, ui) {
			updateSharedVars( ui );
			// Prevent dragging of text while dragging items
			$(this).disableSelection();
			$('body,#menu-to-edit').disableSelection();
		},
		change: function(e, ui) {
			if( ! ui.placeholder.parent().hasClass('menu') ) {
				if(prev.length) {
					prev.after( ui.placeholder );
				} else {
					api.menuList.prepend( ui.placeholder );
				}
			}
			updateSharedVars( ui );
		},
		update: function(e, ui) {
			$('body,#menu-to-edit').enableSelection();
			$(this).enableSelection();
			$('textarea', $(this)).removeAttr('disabled');
		},
		sort: function(e, ui) {
			updateSharedVars( ui );
		},
		stop: function(e, ui) {
			updateSortOrder();
			generateForm( 'sortable stop' );
		}
	});

	function updateSharedVars(ui) {
		var depth;

		prev = ui.placeholder.prev();
		next = ui.placeholder.next();

		// Make sure we don't select the moving item.
		if( prev[0] === ui.item[0] ) prev = prev.prev();
		if( next[0] === ui.item[0] ) next = next.next();

	}

	function updateLabelStyle(textfont,textsize,fontweight,textpadding,textalign) {
		if(!textfont) { textfont = $('input[name="lfont"]').val();}
		if(!textsize) { textsize = $('input[name="lsize"]').val();}
		if(!fontweight){ fontweight = ''; /* $('input[name="labelweight"]').val(); */ }
		if(!textpadding){ textpadding = $('input[name="lpad"]').val();}
		if(!textalign){ textalign = $('input[name="lalign"]').val();}
		//console.debug('label align: '+textalign);
		updateTextStyle(textfont,textsize,fontweight,textpadding,textalign,'l');
	}

	function updateTextStyle(textfont,textsize,fontweight,textpadding,textalign,prefix) {
		if(kwsdebug) { console.log('updateTextStyle', textfont,textsize,fontweight,textpadding,textalign,prefix); }
		if(empty(prefix)) { prefix = 't';}
		if(empty(textfont)) {
			textfont = $('#'+prefix+'font').val();
		} else {
			textfont = $('select[name="'+prefix+'font"] option[id="'+textfont+'"]').val();
			$('select[name="'+prefix+'font"] option[value="'+textfont+'"]').prop('selected',true);
		}

		if(empty(textsize)) {	textsize = $('#'+prefix+'size').val(); } else {
			$('select#'+prefix+'size option[value="'+textsize+'"]').prop('selected',true);
		}

		if(empty(textalign)) {
			textalign = $('input[name="'+prefix+'align"]').val();
		}

		if(empty(fontweight)) {
			fontweight = $('input[name="'+prefix+'weight"]').val();
		} else {
			$('input[name="'+prefix+'weight"][value="'+fontweight+'"]').prop('checked',true);
		}
		if($('option:selected:contains("*")', '#textfont').length > 0) {
			$('#'+prefix+'options .asterix').show();
		} else {
			$('#'+prefix+'options .asterix').hide();
		}
	}

	function updateBorderWidth(borderwidth) {
		if(!empty(borderwidth)) {
			$('#borderwidth').val(borderwidth);
		}

		borderwidth = $('#borderwidth').val();

		if( empty( borderwidth ) ) {
			$('#bordercoloritem').hide();
		} else {
			$('#bordercoloritem').show();
		}

		$('.kws_form').css({
			'border-width': borderwidth+'px'
		});
	}

	/**
	 * Enable tabbed nav on "Most Used/Other Fields" metabox
	 */
	$('.nav-tab-link').click(function() {
		panelId = /#(.*)$/.exec($(this).attr('href'));
		if ( panelId && panelId[1] )
			panelId = panelId[1];
		else
			return false;

		wrapper = $(this).parents('.inside').first();

		$('.tabs-panel-active', wrapper).removeClass('tabs-panel-active').addClass('tabs-panel-inactive');
		$('#' + panelId, wrapper).removeClass('tabs-panel-inactive').addClass('tabs-panel-active');

		$('.tabs', wrapper).removeClass('tabs');
		$(this).parent().addClass('tabs');

		return false;
	});


	function updateBackgroundType(from){
		if(kwsdebug) { console.log('updateBackgroundType' + from );}

		color1 = $('#color6').wpColorPicker('color');
		color2 = $('#color2').wpColorPicker('color');
		gradtype = $('#gradtype').val();

		var selection = $("input[name=backgroundtype]:checked").val();

		if(selection === 'transparent') {

			// Disable the inputs that aren't being used so they don't get sent via POST
			$("#bgtop,#bgbottom,#gradtype,#bgrepeat,#bgpos,#bgpattern,#patternurl,#bgurl,#color2,#gradheightli,#gradtypeli").attr('disabled', true).hide();

			$('.safesubscribesample').css("background", 'none transparent');

			$('.kws_form').css( 'background', 'transparent none' );
			
		} else if(selection === 'gradient') {

			$("#bgtop,#bgbottom,#gradtype,#bgrepeat,#bgpos,#bgpattern,#patternurl,#bgurl,#color2,#gradheightli,#gradtypeli").attr('disabled', false).show();
			$("#bgpattern,#bgurl").hide();

			$('#patternurl,#bgimage').attr('disabled', true);
			$('#color2,#gradtype,#bgrepeat,#bgpos').removeAttr('disabled');

			updateColor('#color6', color1);
			updateColor('#color2', color2);

			$('.safesubscribesample').css("background", color1 + ' none');

			// VERTICAL GRADIENT
			if($('#gradtype').val() === 'vertical') {
				$("label", '#bgbottom').text(ScriptParams.labels.bottomcolor+':');
				$("label", '#bgtop').text(ScriptParams.labels.topcolor+':');
				$("label span", '#gradheightli').text(ScriptParams.labels.gradientheight+':');

				// VERTICAL
				$('.kws_form').css({
					background: color1,
					background: '-moz-linear-gradient(top, '+color1+' 0%, '+color2+' 100%)',
					background: '-webkit-gradient(linear, left top, left bottom, color-stop(0%,'+color1+'), color-stop(100%,'+color2+'))',
					background: '-webkit-linear-gradient(top, '+color1+' 0%,'+color2+' 100%)',
					background: '-o-linear-gradient(top, '+color1+' 0%,'+color2+' 100%)',
					background: '-ms-linear-gradient(top, '+color1+' 0%,'+color2+' 100%)',
					background: 'linear-gradient(to bottom, '+color1+' 0%,'+color2+' 100%)',
					filter: 'progid:DXImageTransform.Microsoft.gradient( startColorstr=\''+color1+'\', endColorstr=\''+color2+'\',GradientType=0 )'
				});

			}
			// HORIZONTAL GRADIENT
			else {
				$("label", '#bgbottom').text(ScriptParams.labels.rightcolor+':');
				$("label", '#bgtop').text(ScriptParams.labels.leftcolor+':');
				$("label span", '#gradheightli').text(ScriptParams.labels.gradientwidth+':');

				$('.kws_form').css({
					background: color1,
					background: '-moz-linear-gradient(left,  '+color1+' 0%, '+color2+' 100%)',
					background: '-webkit-gradient(linear, left top, right top, color-stop(0%,'+ color1+'), color-stop(100%,'+color2+'))',
					background: '-webkit-linear-gradient(left,  '+color1+' 0%,'+color2+' 100%)',
					background: '-o-linear-gradient(left, '+color1+' 0%,'+color2+' 100%)',
					background: '-ms-linear-gradient(left, '+color1+' 0%,'+color2+' 100%)',
					background: 'linear-gradient(to right,  '+color1+' 0%, '+color2+' 100%)',
					filter: 'progid:DXImageTransform.Microsoft.gradient( startColorstr=\''+color1+'\', endColorstr=\''+color2+'\',GradientType=1 )'
				});
			}

		} else if(selection === 'solid') {

			$("#bgtop,#gradheightli,#gradtypeli,#bgpattern,#bgurl").hide();
			$("#bgbottom").show().find("label").text(ScriptParams.labels.bgcolor+':');
			$('#patternurl,#bgimage,#gradtype,#bgrepeat,#bgpos').attr('disabled', true);
			$('#color2').removeAttr('disabled');

			updateColor('#color6', color1);
			updateColor('#color2', color2);

			$('.kws_form,.safesubscribesample').css("background-color", color2).css("background-image", 'none');
		} else if(selection === 'pattern') {
			$("#bgtop,#gradheightli,#gradtypeli,#bgbottom,#bgurl").hide();
			$("#bgpattern").show();
			$('#color2,#bgimage').attr('disabled', true);
			$('#bgpattern,#patternurl,#bgrepeat,#bgpos').removeAttr('disabled');
			updatePattern();
		} else if(selection === 'url') {
			//console.debug('url');
			$('#patternurl').attr('disabled', true);
			$('#color2,#bgimage,#bgrepeat,#bgpos').removeAttr('disabled');

			$("#bgtop,#gradheightli,#bgpattern").hide();
			$("#bgurl,#bgbottom").show();
			$("label", "#bgbottom").text(ScriptParams.labels.bgcolor+':');
			updateBackgroundURL();
		}
		//generateForm('style');
		// alert('c1: '+typeof(bordercolor) + ', c2: '+typeof(color2) + ', tc1: '+typeof() + ', tc2: '+typeof());
	}

	function updateBackgroundURL(url, color, repeat, position) {
		if(empty(repeat)) { repeat = $('#bgrepeat').val();}
		if(empty(url)) { url = $('input#bgimage').val(); }

		if(url === '' || url === 'http://') { url = ''; } else { url = 'url('+url+')'; }

		if(empty(color)) { color = $('#color2').wpColorPicker('color'); }
		if(empty(position)) { position = $('#bgpos').val(); }

		$('.kws_form,.safesubscribesample').css("background", color+' '+url+' '+position+' '+repeat);
	}

	function updateLabelSame(set) {
		if(kwsdebug) { console.log('updateLabelSame', set); }

		var $lusc = $('#lusc');
		var $labelcolor = $('#labelcolorli');

		$lusc.prop('checked', set );

		// Same Color
		if( $lusc.is(':checked')) {
			$labelcolor.hide();
			$('input', $labelcolor).attr('disabled', true);
		} else {
			$labelcolor.show();
			$('input', $labelcolor).removeAttr('disabled');
		}

		// Same Font
		if($('input#lusf').is(':checked')) {
			$('#lfontli').hide();
			$('#lfont').val($('#tfont').val());
		} else {
			$('#lfontli').show();
		}
	}

	function updateLabelColor(color) {
		if(kwsdebug) { console.log('updateLabelColor', color); }
		if(!color) {
			color = $('#lcolor').wpColorPicker('color');
		}

		updateColor('#lcolor', color, '.kws_form .kws_input_container label', 'color');
	}


	function updateTextColor(color) {
		if(kwsdebug) { console.log('updateTextColor: ' + color);}
		if(!color) {
			color = $('#tcolor').wpColorPicker('color');
		}

		updateColor('#tcolor', color, '.kws_form .cc_intro,.kws_form .cc_intro *', 'color');

		if($('#lusc').is(":checked")) {
			updateLabelColor(color);
		}
	}

	function updateBorderColor(color) {
		if(kwsdebug) { console.log('updateBorderColor', color); }

		if(empty(color)) { color = $('#bordercolor').wpColorPicker('color'); }

		updateColor('#bordercolor', color, '.kws_form', 'border-color');
	}

	function updateColor(target, color, formelement, formcssattr) {

		if(kwsdebug) { console.log('updateColor', target, color, formelement, formcssattr);}

		$(target).val(color);

		if(formcssattr) {
			$(formelement).css(formcssattr, color);
		}
	}

	function updateStyle() {
		if(kwsdebug) { console.log('updateStyle'); }
		updateBackgroundType('updateStyle');
		updateTextStyle();
		updateLabelStyle();
		updateLabelSame();
		$('label.labelStyle').trigger('ready');
	}

	toggleDesign( true );
	updateStyle();
	showHideFormFields();
	sortFieldMenu();
	updateFormFields('updateAll');


	// processStyle();
	$('form label.error').hide();


	$(window).on('ready resize', function() {

		if($('#examplewrapper .grabber').is(':hidden')) { return; }

		box = jQuery('#menu-management-liquid .menu-edit').width();
		menu = jQuery('#menu-to-edit').width();
		available = box - menu;

		var real_width = null;

		if($('input[name=widthtype]:checked').val() === 'px') {
			var borderwidth = $('#borderwidth').val() * 2;
			var paddingwidth = $('#paddingwidth').val() * 2;
			var rawwidth = $('#width').val();
			var setwidth = Math.floor(rawwidth) - Math.floor(paddingwidth) - Math.floor(borderwidth);
			real_width = Math.floor(rawwidth) + Math.floor(paddingwidth) + Math.floor(borderwidth);
		}

		jQuery('#examplewrapper').width((available - 60)).css('min-width', real_width );
	}).trigger('resize');

   jQuery('.toggleMore:not(a)').hide();

}); // End jQuery


function empty (mixed_var) {
	// http://kevin.vanzonneveld.net

	var key;

	if(
	   	mixed_var === 0 ||
		mixed_var === "0"
	) {
		return false;
	}

	if (mixed_var === "" ||
		mixed_var === null ||
		mixed_var === false ||
		mixed_var === 'false' ||
		typeof mixed_var === 'undefined'
	){
		return true;
	}

	if (typeof mixed_var === 'object') {
		for (key in mixed_var) {
			return false;
		}
		return true;
	}

	return false;
}