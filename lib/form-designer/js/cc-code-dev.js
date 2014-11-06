jQuery.noConflict();

kwsdebug = true;

/**
 * Update the content preview without needing to post the content.
 *
 * @usedby ccfg_tiny_mce_before_init() PHP
 *
 * @param  {tinymce} inst [description]
 */
function triggerTextUpdate(inst) {
	if(jQuery('.kws_form .cc_intro').length > 0) {
		var ed = tinyMCE.activeEditor;
		var content = ed.getContent({format : 'raw'});
		jQuery('.kws_form .cc_intro').html(content);
	}
}

jQuery(document).ready(function($) {

	$('a[rel*=external]').attr('target', '_blank');

	$('input.wpcolor').wpColorPicker({
		palettes: false,
		change: function(event, ui){
			$(this).data('doingchange', 1);
			$(this).trigger('colorchange');
			$(this).data('doingchange', null);
		}
	});

	// Tabbed interface
	$("#tabs,#formfields-select-tabs").tabs({ cookie: { expires: 60 } }).addClass('ui-helper-clearfix');
	$("#tabs li").removeClass('ui-corner-top').addClass('ui-corner-left');
	$("#tabs li li").removeClass('ui-corner-left');

	$('.selectall, .selectall input').on('click', function() { $(this).select(); });

	// Toggle design
	$('#toggledesign').bind('ready change', function( event) {
		console.log('toggledesign change');
		event.stopImmediatePropagation();
		toggleDesign();
	}).trigger('ready');

	// Radio Buttons & Checkboxes
	$('input:checkbox[name^="formfields"]').bind('change', function() {
		showHideFormFields($(this));
		$('textarea.wp-editor').trigger('change');
	});

	if($('input.menu-item-checkbox[value=intro]').is(':checked')) {
		$('.wp-editor-textarea').show();
	}

	$('input:checkbox[name^="f"]').bind('change', function( event ) {
		event.stopImmediatePropagation();
		updateSortOrder();
		updateFormFields('input:checkbox[f] bind');
	});

	$('#form-fields ul.menu').bind('sortstop', function( event ) {
		event.stopImmediatePropagation();
		updateSortOrder();
		updateFormFields('formfields drop');
	});

	// Text inputs
	$("input#bgimage").bind('change keyup', function( event ) {
		event.stopImmediatePropagation();
		updateBackgroundURL();
	});
	$('input.labelValue,input.labelDefault', $('#form-fields')).on('change keyup', function() {
		event.stopImmediatePropagation();
		updateFormFields('labelDefault change keyup');
	});

	$("#defaultbuttontext,input[name=submitdisplay],input[name=submitposition]").bind('change keyup', function() {
		updateDefaultButton();
	});

	$('input.menu-save').on('click submit', function() {
		$('#examplewrapper').hide();
	});

	$('li.menu-item .item-edit').on('click', function(e) {
		e.preventDefault();
		$('.menu-item-settings', $(this).parents('li.formfield')).toggle();
		return false;
	});

	function setupDesignBindings() {

		$('input,select').on('change', function() {
			$('#cc-form-settings').trigger('stylechange');
		});

		$('#togglePreview').on('click', function(e){
			//e.preventDefault();
			$('#togglePreview').toggleClass('hidden');
			$('#examplewrapper .grabber').toggle(0, function() {
				if($('#examplewrapper .grabber').is(':visible')) {
					$('#examplewrapper').css({
						padding: '10px'
					});
					$('#examplewrapper .legend').show();
					$(window).trigger('resize');
					$('#togglePreview').css('text-align', 'right');
				} else {
					$('#examplewrapper .legend').hide();
					$('#examplewrapper').css({
						'width': '0',
						'min-width': '200px',
						padding: '0'
					});
					$('#togglePreview').css('text-align', 'center');
				}
			});
			return false;
		});

		// Select dropdowns
		$('#borderwidth').bind('change', function(event ) {
			if(kwsdebug) { console.log('#borderwidth change'); }
			event.stopImmediatePropagation();
			updateWidthCalculator();
			updateBorderWidth();
			$(window).trigger('resize');
		});

		$('#size').change(function(event) {
			event.stopImmediatePropagation();
			updateTextInputSize($('#size').val());
		});

		$('#tfont,#tsize,input[name="talign"]').bind('change', function(event) {
			if(kwsdebug) { console.log('#tfont,#tsize,input[name="talign"] change'); }
			event.stopImmediatePropagation();
			updateStyle();
		});

		$('#lfont,#lsize,input[name="lalign"]').bind('change', function(event) {
			if(kwsdebug) { console.log('#lfont,#lsize,input[name="lalign"] change'); }
			event.stopImmediatePropagation();
			updateLabelStyle();
		});
		$('#gradheight,#gradtype').bind('change', function(event) {
			event.stopImmediatePropagation();
			updateBackgroundType();
		});
		$('#color6,#color2').bind('colorchange', function(event) {
			event.stopImmediatePropagation();
			updateBackgroundType();
		});
		$('#bordercolor').bind('colorchange', function(event) {
			event.stopImmediatePropagation();
			updateBorderColor();
		});
		$('#tcolor').bind('colorchange', function(event) {
			event.stopImmediatePropagation();
			updateTextColor();
		});
		$('#lcolor').bind('colorchange', function(event) {
			event.stopImmediatePropagation();
			updateLabelColor();
		});

		$('#bgpos,#bgrepeat').change(function( event ) {
			event.stopImmediatePropagation();
			updateBackgroundURL();
		});

		$('input[name="backgroundtype"]').bind('change', function( event ) {
			updateBackgroundType();
		});

		$('input[id^=lus]').bind('change', function( event ) {
			event.stopImmediatePropagation();
			updateLabelSame();
		});

		$('label.labelStyle').on('click', function( event ) {

			if($('input[type=checkbox]:checked', $(this)).length > 0) {
				$(this).addClass('checked');
			} else {
				$(this).removeClass('checked');
			}

		});

		// Pattern selection
		$("ul#patternList li").click(function( event ){
			event.stopImmediatePropagation();
			updatePattern($(this));
		});

		$("#paddingwidth,.input input[name=widthtype],#width").bind('change keyup', function(e) {
			if(eventKeys(e) || e.keyCode === 46 || e.keyCode === 8) {
				// If it's not an arrow, tab, etc. and not delete or backspace, process the sucker!
				updateWidthCalculator();
				$('#cc-form-settings').trigger('stylechange');
			}
		});
	}

	function useDesign() {
		return $('#toggledesign').is(':checked');
	}

	/**
	 * Turn on or off styling the forms
	 */
	function toggleDesign() {

		$designdivs = $('#designoptions,#backgroundoptions,#border,#fontstyles,#formdesign,.grabber,#examplewrapper,.labelStyle.mce_bold,.labelStyle.mce_italic');

		$('.safesubscribesample').css('background', 'transparent none');

		if( useDesign() ) {
			$designdivs.show();
			updateStyle();
			//updateColors();
		} else {
			$designdivs.hide();
		}
	}

	function updatePattern($clickedLI, update) {
		var val = '';

		if(empty($clickedLI)) {
			if($("ul#patternList li.selected").length > 0) {
				$clickedLI = $("ul#patternList li.selected");
				val = $clickedLI.attr('title');
			} else {
				val = $('#patternurl').val();
				$clickedLI = $("ul#patternList li[title*=\""+val+"\"]");
			}
		}
		$("ul#patternList li").removeClass('selected');
		$clickedLI.addClass('selected');
		var url = $clickedLI.attr("title");
		$('#patternurl').val(url);

		updateBackgroundURL(ScriptParams.path+url);
		if(update !== false) {
			updateCode('updatePattern');
		}
	}

	function generateForm() {

		// If Form Styler is not being used, no need to generate a form preview.
		if( !useDesign() ) {
			return;
		}

		var date = Date.now();
		var verify = ScriptParams.rand + $('#cc-form-id').val() + date;
		var data = {
			rand: ScriptParams.rand,
			form: $('form#cc-form-settings').serializeArray(),
			verify: verify,
			date: date,
			text: ScriptParams.text,
			path: ScriptParams.path
		};

		var ajaxTime= new Date().getTime();

		if(kwsdebug) { console.log(data); }

		$.ajax({
			type: 'POST',
			url: ajaxurl,
			processData: false,
			isLocal: true,
			dataType: 'json',
			data:  'action=ctct_form_designer&data=' +  encodeURIComponent( JSON.stringify( data ) ),
			success: function(data, textStatus, XMLHttpRequest){
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

						var lists = generateLists();

						// Process the lists
						html = html.replace('<!-- %%LISTSELECTION%% -->', lists );


						$('.grabber .kws_form, .grabber style').remove();

						$('.grabber').append( html );

						if( empty( lists ) ) {
							$('.grabber .cc_lists').hide();
						}
					}

				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				if(kwsdebug) {
					console.log(XMLHttpRequest, textStatus, errorThrown);
				}
				$('.grabber').css('width','80%').css('margin','0 auto').css('text-align', 'left').html('<h2><em>Eeep! That didn\'t work...</em></h2><h3>The error: <code>'+errorThrown+'</code></h3>');
				return false;
			}
		}).done(function (data, textStatus, XMLHttpRequest) {
			var totalTime = new Date().getTime()-ajaxTime;

			if( kwsdebug ) {
				console.log("request took %s milliseconds.", totalTime, XMLHttpRequest);
			}
		});

		return false;

	}

	function bindSettings() {

		// For text only
		$("#form-fields ul.menu input, #cc-form-settings .inside textarea").bind('change', function( event ) {
			event.stopImmediatePropagation();
			updateFormFields('#cc-form-settings text - '+$(this).attr('id'));
		});

		$("#cc-form-settings").bind('stylechange', function( event ) {
			event.stopImmediatePropagation();
			updateCode('stylechange trigger');
		});

		$("#cc-form-settings input, #cc-form-settings textarea, #cc-form-settings select")
		.not('.inside input')
		.not('.inside textarea')
		.bind('change',
			function( event ) {
				console.log('Changed settings input', event);
				event.stopPropagation();
				updateCode('#cc-form-settings style - '+$(this).attr('id'));
			}
		);
	}

	function eventKeys(e) {
		var code = (e.keyCode ? e.keyCode : e.which);
		if (code === 37 || code === 38 || code === 39 || code === 40 || code === 46 || code === 8 || code === 16) {
			return false;
		}else {
			return true;
		}
	}

	function updateCode(from) {
		if(kwsdebug) { console.log('updateCode', from); }
		$('#codeSwapLink').remove();
		updateWidthCalculator();
		updateDisabled();
		generateForm();
	}

	function getRealWidth() {
		if($('input[name=widthtype]:checked').val() === 'px') {
			var borderwidth = $('#borderwidth').val() * 2;
			var paddingwidth = $('#paddingwidth').val() * 2;
			var rawwidth = $('#width').val();
			var setwidth = Math.floor(rawwidth) - Math.floor(paddingwidth) - Math.floor(borderwidth);
			var realwidth = Math.floor(rawwidth) + Math.floor(paddingwidth) + Math.floor(borderwidth);
			return realwidth;
		}
		return false;
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

	function mySorter(a,b){
		return $("input.position", $(a)).val() > $("input.position", $(b)).val() ? 1 : -1;
	}

	function sortFieldMenu() {

		$('#form-fields ul.menu li.menu-item')
			.sort(mySorter)
			.appendTo('#form-fields ul.menu');
	}


	function generateLists() {
		var output = '';

		var format = $('.list-selection-format input:checked').val();

		switch(format) {

			case 'checkbox':
				$('.ctct-lists li').has('input:checked').each(function() {
					checked = $('#checked_by_default').is(':checked') ? ' checked="checked"' : '';
					output = output + '<li><label><input type="checkbox" '+checked+' /> '+$('span', $(this)).html()+'</label></li>';
				});
				output = '<ul class="checkboxes">'+output+'</ul>';
				break;
			case 'dropdown':
			case 'multiselect':
				$('.ctct-lists li').has('input:checked').each(function() {
					output = output + '<option>'+$('span', $(this)).text()+'</option>';
				});
				if(format === 'multiselect') {
					output = '<select class="kws_clear" multiple="multiple">'+output+'</select>';
				} else {
					output = '<select class="kws_clear">'+output+'</select>';
				}
				break;
		}

		return output;
	}

	/**
	 * Show and hide the form fields in the main editor div based on whether Form Fields checkboxes are checked.
	 * @param  jQuery Object $clicked You can pass an object or array of objects that are the ones you want to analyze. Otherwise, it grabs all the checkboxes.
	 */
	function showHideFormFields($clicked) {

		if(!$clicked) {
			$clicked = $('#formfields_select input:checkbox');
		} else {
			console.log($clicked);
		}

		$clicked.each(function() {
			//$('#form-fields .menu li').has('#'+$(this).val()).find('input.checkbox').prop('checked', $(this).prop('checked'));
			var targetLI = $('#form-fields .menu li').has('#'+$(this).val());
			var checked = ($(this).checked || $(this).prop('checked') || $(this).is(':checked'));
			if(!empty(checked)) {
				if($clicked.val() === 'intro') {
					$('.wp-editor-textarea').show();
				}
				targetLI.remove().appendTo($('#form-fields .menu')).show();

				targetLI.find('input,textarea').each(function() { $(this).removeAttr('disabled'); })
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

		//$('#formfields_select').trigger('change');
	}

	function updateFormFields(from) {
		if(kwsdebug) { console.log('updateFormFields', from); }

		$('.wp-editor-textarea,ul.menu li.formfield').each(function() {
			updateFormField($(this));
		});

		updateCode('updateFormFields');
	}

	function updateFormField($item) {

		// Get the <li> we're working within.
		if($item.not('li')) { $item = $item.parents('li.formfield'); }

		var checkbox = $('input.checkbox', $item);
		if(checkbox.is(':checked')) {
			$('.menu-item-settings', $item).show();

			// Set values
			check = {};
				check.id = checkbox.attr('id');
				check.val = checkbox.val();
				check.name = checkbox.attr('name');
				check.rel = checkbox.attr('rel');

			input = {};
				input.textarea = '';
				input.label = '';
				input.value = '';
				input.html = '';
				input.bold = '';
				input.italic = '';
				input.required = '';
				input.labelHTML = '';
				input.size = $('input.labelSize', $item).val();
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

	// In field section
	// <input type="checkbox" class="menu-item-checkbox" name="formfields[first_name]" value="first_name" checked="checked">
	// In .formfield:
	// <input type="checkbox" name="f[2][n]" id="first_name" value="f[2]" 1="" class="checkbox hide-if-js" rel="text">
	/**
	 * Update which form fields are disabled and which are enabled. This prevents non-active form fields from being posted to the form.php script.
	 */
	function updateDisabled() {

		$('ul.menu li.formfield').each(function() {
			// Disable the inputs
			$('input,textarea', $(this)).attr('disabled', true);
		});

		// Loop through the checked fields
		$('#formfields_select input[type=checkbox]:checked').each(function() {
			$('input,textarea', $('ul.menu li.formfield').has('input#'+$(this).val())).removeAttr('disabled');
		});
	}

	function updateSortOrder() {
		$('ul.menu li.formfield').each(function() {
			// Set the position index
			// Each form field has a hidden input.position that is used to sort the fields in the editor
			$('input.position[type=hidden]', $(this)).val($('ul.menu li.formfield input.position').index($('input.position', $(this))));
		});
	}

	$('#form-fields ul.menu').sortable({
		handle: '.menu-item-handle',
		forceHelperSize: true,
		placeholder: 'sortable-placeholder',
		start: function(e, ui) {
			var taHeight = $('textarea', $(this)).outerHeight();
			updateSharedVars( ui );
			// Prevent dragging of text while dragging items
			$(this).disableSelection();
			$('body,#menu-to-edit').disableSelection();
		},
		change: function(e, ui) {
			if( ! ui.placeholder.parent().hasClass('menu') ) {
				if(prev.length) { prev.after( ui.placeholder ); } else { api.menuList.prepend( ui.placeholder ); }
			}
			updateSharedVars( ui );
		},
		update: function(e, ui) {
			//updateAll();
		},
		sort: function(e, ui) {
			updateSharedVars( ui );
		},
		stop: function(e, ui) {
			$('body,#menu-to-edit').enableSelection();
			$(this).enableSelection();
			generateForm(true, false, '.sortable');
			$('textarea', $(this)).removeAttr('disabled');
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

	function updateDefaultButton(defaultbuttontext, preset) {
		if((preset && $('#pupt').is(':checked')) || (empty(preset) && !empty(defaultbuttontext))) {
			$('#Go_default').val(defaultbuttontext);
		}
	}

	function updateEmailInput(defaulttext, preset) {
		if(empty(defaulttext)) {
			defaulttext = $("#email_address_default").val();
		}
		if((preset && $('#pupt').is(':checked')) || (empty(preset) && !empty(defaulttext))) {
			$('#ea').val(defaulttext).attr('defaultValue', defaulttext);
			$('#email_address_default').val(defaulttext);
		}
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
		textfont = findFont(textfont);
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
		if($('#textfont option:selected:contains("*")').length > 0) {
			$('#'+prefix+'options .asterix').show();
		} else {
			$('#'+prefix+'options .asterix').hide();
		}
	}


	function findFont(id) {
		switch(id) {

			case 'times':
				return "'Times New Roman', Times, Georgia, serif";
			case 'georgia':
				return "Georgia,'Times New Roman', Times, serif";
			case 'palatino':
				return "'Palatino Linotype', Palatino, 'Book Antiqua',Garamond, Bookman, 'Times New Roman', Times, Georgia, serif";
			case 'garamond':
				return "Garamond,'Palatino Linotype', Palatino, Bookman, 'Book Antiqua', 'Times New Roman', Times, Georgia, serif";
			case 'bookman':
				return "Bookman,'Palatino Linotype', Palatino, Garamond, 'Book Antiqua','Times New Roman', Times, Georgia, serif";
			case 'helvetica':
				return "'Helvetica Neue',HelveticaNeue, Helvetica, Arial, Geneva, sans-serif";
			case 'arial':
				return "Arial, Helvetica, sans-serif";
			case 'lucida':
				return "'Lucida Grande', 'LucidaGrande', 'Lucida Sans Unicode', Lucida, Verdana, sans-serif";
			case 'verdana':
				return "Verdana, 'Lucida Grande', Lucida, TrebuchetMS, 'Trebuchet MS',Geneva, Helvetica, Arial, sans-serif";
			case 'trebuchet':
				return "'Trebuchet MS', Trebuchet, Verdana, sans-serif";
			case 'tahoma':
				return "Tahoma, Verdana, Arial, sans-serif";
			case 'franklin':
				return "'Franklin Gothic Medium','FranklinGotITC','Arial Narrow Bold',Arial,sans-serif";
			case 'impact':
				return "Impact, Chicago, 'Arial Black', sans-serif";
			case 'arialblack':
				return "'Arial Black',Impact, Arial, sans-serif";
			case 'gillsans':
				return "'Gill Sans','Gill Sans MT', 'Trebuchet MS', Trebuchet, Verdana, sans-serif";
			case 'courier':
				return "'Courier New', Courier, Monaco, monospace";
			case 'lucidaconsole':
				return "'Lucida Console', Monaco, 'Courier New', Courier, monospace";
			case 'comicsans':
				return "'Comic Sans MS','Comic Sans', Sand, 'Trebuchet MS', cursive";
			case 'papyrus':
				return "Papyrus,'Palatino Linotype', Palatino, Bookman, fantasy";
		}
	}

	function updateTextInputSize(textinputsize) {
		if(textinputsize !== 0) {
			$('#defaulttext, label[for=defaulttext], li.defaulttext').show();
			if($('.grabber .kws_form input[type=text]').length > 0) {
				$('.grabber .kws_form input[type=text]').attr('size',textinputsize);
			}
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

		return;
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


	function updateBackgroundType(color1,color2,url, height, gradtype){
		if(kwsdebug) { console.log('updateBackgroundType', color1,color2,url, height, gradtype);}
		//updateBackgroundType('#707070','#000000', '', 100);
		//alert(acolor1+' '+acolor2);

		if(empty(color1)) { color1 = $('#color6').wpColorPicker('color'); }
		if(empty(color2)) { color2 = $('#color2').wpColorPicker('color'); }

		if(empty(gradtype)) { gradtype = $('#gradtype').val(); }
		var selection = $("input[name=backgroundtype]:checked").val();
		if(selection === 'transparent') {
			$("#bgtop,#bgbottom,#gradwidth,#gradtype,#gradheight,#bgrepeat,#bgpos,#bgpattern,#patternurl,#bgurl,#color2,#gradheightli,#gradtypeli").attr('disabled', true).hide();

			$('.safesubscribesample').css("background", 'none transparent');

		} else if(selection === 'gradient') {
			if(empty(height)) { height = $('#gradheight').val(); }
//			console.debug('gradient');
			$("#bgtop,#bgbottom,#gradwidth,#gradtype,#gradheight,#bgrepeat,#bgpos,#bgpattern,#patternurl,#bgurl,#color2,#gradheightli,#gradtypeli").attr('disabled', false).show();
			$("#bgpattern,#bgurl").hide();
			if($('#gradtype').val() === 'vertical') {
				$("#bgbottom label").text(ScriptParams.labels.bottomcolor+':');
				$("#bgtop label").text(ScriptParams.labels.topcolor+':');
				$("#gradheightli label span").text(ScriptParams.labels.gradientheight+':');
			} else {
				$("#bgbottom label").text(ScriptParams.labels.rightcolor+':');
				$("#bgtop label").text(ScriptParams.labels.leftcolor+':');
				$("#gradheightli label span").text(ScriptParams.labels.gradientwidth+':');
			}
			$('#patternurl,#bgimage').attr('disabled', true);
			$('#color2,#gradheight,#gradwidth,#gradtype,#bgrepeat,#bgpos').removeAttr('disabled');
			updateBackgroundColor(color1,color2);
			$('#gradtype').trigger('stylechange');
			$('.safesubscribesample').css("background-color", color1).css("background-image", 'none');
		} else if(selection === 'solid') {
			//console.debug('solid');
			$("#bgtop,#gradheightli,#gradtypeli,#bgpattern,#bgurl").hide();
			$("#bgbottom").show();
			$("#bgbottom label").text(ScriptParams.labels.bgcolor+':');
			$('#patternurl,#bgimage,#gradwidth,#gradtype,#gradheight,#bgrepeat,#bgpos').attr('disabled', true);
			$('#color2').removeAttr('disabled');
			updateBackgroundColor(color1,color2);
			$('.kws_form,.safesubscribesample').css("background-color", color2).css("background-image", 'none');
		} else if(selection === 'pattern') {
			$("#bgtop,#gradheightli,#gradtypeli,#bgbottom,#bgurl").hide();
			$("#bgpattern").show();
			$('#color2,#bgimage').attr('disabled', true);
			$('#bgpattern,#patternurl,#bgrepeat,#bgpos').removeAttr('disabled');

			var bgTitle = '';
			// If the saved input has a value, use it
			if($('#patternurl').val() !== '') { bgTitle = $('#patternurl').val(); }
			else if($("#bgpattern ul li.selected").length > 0) { bgTitle = $("#bgpattern ul li.selected").attr('title'); }
			else { bgTitle = $("#bgpattern ul li:first").attr('title'); }
			updatePattern(null, false);
		} else if(selection === 'url') {
			//console.debug('url');
			$('#patternurl').attr('disabled', true);
			$('#color2,#bgimage,#bgrepeat,#bgpos').removeAttr('disabled');

			$("#bgtop,#gradheightli,#bgpattern").hide();
			$("#bgurl,#bgbottom").show();
			$("#bgbottom label").text(ScriptParams.labels.bgcolor+':');
			updateBackgroundURL();
		}
		//updateCode('style');
		// alert('c1: '+typeof(bordercolor) + ', c2: '+typeof(color2) + ', tc1: '+typeof() + ', tc2: '+typeof());
	}

	function updateBackgroundColor(color1, color2) {
		if(kwsdebug) { console.log('updateBackgroundColor', color1, color2); }

		updateColor('#color6', color1);
		updateColor('#color2', color2);
	}
	function updateBackgroundStyle(style) {
		if(kwsdebug) { console.log('updateBackgroundStyle', style); }
		if(style) {
			$('input[name=backgroundtype][value='+style+']').prop('checked',true).parents('label').click();
		}
		return;
	}

	function updateBackgroundURL(url, color, repeat, position) {
		if(empty(repeat)) { repeat = $('#bgrepeat').val();}
		if(empty(url)) { url = $('input#bgimage').val(); }

		if(url === '' || url === 'http://') { url = ''; } else { url = 'url('+url+')'; }

		if(empty(color)) { color = $('#color2').wpColorPicker('color'); }
		if(empty(position)) { position = $('#bgpos').val(); }
		//console.log(color+' '+url+' '+position+' '+repeat);
		$('.kws_form,.safesubscribesample').css("background", color+' '+url+' '+position+' '+repeat);
	}

	function updateLabelSame(set) {
		if(kwsdebug) { console.log('updateLabelStame', set); }
		if(set) {
			$('input#lusc').prop('checked', true);
		} else if(set === false) {
			$('input#lusc').prop('checked', false);
		}

		// Same Color
		if($('input#lusc').is(':checked')) {
			$('#labelcolorli').hide();
			$('#labelcolorli input').attr('disabled', true);
		} else {
			$('#labelcolorli').show();
			$('#labelcolorli input').removeAttr('disabled');
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

		//color = '#'+color.replace(/#+?/g,'');

		if(!$(target).data('doingchange')) {
			$(target).wpColorPicker('color', color);
		}
		$(target).val(color);
		if(formcssattr) {
			$(formelement).css(formcssattr, color);
		}
	}

	function updateColors() {
		if(kwsdebug) { console.log('updateColors');}
		updateTextColor();
		updateLabelColor();
		updateBorderColor();
	}

	function updateStyle() {
		if(kwsdebug) { console.log('updateStyle'); }
		updateBackgroundType();
		updateTextStyle();
		updateLabelStyle();
		updateLabelSame();
	}

	function updateAll() {
		updateTextInputSize($('#size').val());
		updateStyle();
		//updateColors();
		showHideFormFields();
		updateFormFields('updateAll');
		sortFieldMenu();
	}

	updateAll();

	// Have individual bindings set up before general so propogation works properly
	setupDesignBindings();

	bindSettings();



	// processStyle();
	$('form label.error').hide();

   $('label img').click(function(){
		$(this).closest('input[type=radio]').click();
   });


	$("a.toggleMore").on('click', function() {

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

	$(window).on('ready resize', function() {

		if($('#examplewrapper .grabber').is(':hidden')) { return; }

		box = jQuery('#menu-management-liquid .menu-edit').width();
		menu = jQuery('#menu-to-edit').width();
		available = box - menu;
		jQuery('#examplewrapper').width((available - 60)).css('min-width', getRealWidth());
	});

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