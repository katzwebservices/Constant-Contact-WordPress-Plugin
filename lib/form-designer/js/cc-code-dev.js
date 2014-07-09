jQuery.noConflict();

kwsdebug = false;

/**
 * Update the content preview without needing to post the content.
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
	$('#toggledesign').bind('ready change', function() { toggleDesign(); }).trigger('ready');

	// Radio Buttons & Checkboxes
	$('input:checkbox[name^="formfields"]').bind('change', function() {
		showHideFormFields($(this));
		$('textarea.wp-editor').trigger('change');
	});

	if($('input.menu-item-checkbox[value=intro]').is(':checked')) {
		$('.wp-editor-textarea').show();
	}

	$('input:checkbox[name^="f"]').bind('change', function() { updateSortOrder(); updateFormFields(false, false, 'input:checkbox[f] bind'); });

	$('#form-fields ul.menu').bind('sortstop', function() {
		updateSortOrder();
		updateFormFields(false, false, 'formfields drop');
	});

	// Text inputs
	$("input#bgimage").bind('change keyup', function() { updateBackgroundURL(); /* updateCode('style'); */ });
	$('input.labelValue,input.labelDefault', $('#form-fields')).on('change keyup', function() {
		updateFormFields(true, $(this), 'labelDefault change keyup');
	});

	$("#defaultbuttontext,input[name=submitdisplay],input[name=submitposition]").bind('change keyup', function() { updateDefaultButton(); });

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
		$('#borderwidth').bind('change', function() { updateWidthCalculator(); $(window).trigger(resize); });
		$('#borderstyle').change(function() { updateBorderStyle(); });

		$('#borderradius').bind('change', function() { updateBorderRadius(); });
		$('#paddingwidth').bind('change', function() { /* updateBoxWidthandPadding(); */ });
		$('#lpad').bind('change', function() { updateLabelPadding(); });

		$('#lpad').bind('change', function() {
			updateLabelPadding($('#lpad').val());
		});

		$('#size').change(function() { updateTextInputSize($('#size').val()); });

		$('#tfont,#tsize,input[name="talign"]').bind('change', function() {
			if(kwsdebug) { console.log('#tfont,#tsize,input[name="talign"] change'); }
			updateStyle();
		});

		$('#lfont,#lsize,input[name="lalign"]').bind('change', function() { updateLabelStyle(); });
		$('#gradheight,#gradtype').bind('change', function() { updateBackgroundType(); });
		$('#color6,#color2').bind('colorchange', function() { updateBackgroundType(); });
		$('#bordercolor').bind('colorchange', function() { updateBorderColor(); });
		$('#tcolor').bind('colorchange', function() { updateTextColor(); });
		$('#lcolor').bind('colorchange', function() { updateLabelColor(); });
		$('#lpad').change(function() { updateLabelPadding(); });

		$('#presets').change(function() {
			alertify.confirm("Selecting a preset form design will overwrite all of your form customizations. Continue?", function(e) {
				if (e) { // user clicked "ok"
					updatePresets();
				} else { // user clicked "cancel"
					return false;
				}
			});
		});

		$('#bgpos,#bgrepeat').change(function() { updateBackgroundURL(); });

		$('body').on('change', 'input[name=safesubscribe]', function() { updateSafeSubscribe(); });
		$('input[name="backgroundtype"]').bind('change', function() { updateBackgroundType(); });
		$('input[id^=lus]').bind('change', function() { updateLabelSame(); });
		$('input[name=formalign]').bind('change', function() { updateBoxAlign(); });

		$('label.labelStyle').on('click', function() {
			if($('input[type=checkbox]:checked', $(this)).length > 0) { $(this).addClass('checked'); } else { $(this).removeClass('checked'); }
		});

		// Pattern selection
		$("ul#patternList li").click(function(){
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

	/**
	 * Turn on or off styling the forms
	 */
	function toggleDesign() {

		$designdivs = $('#designoptions,#backgroundoptions,#border,#fontstyles,#formdesign,.grabber,#examplewrapper,.labelStyle');

		if($('#toggledesign').is(':checked')) {
			$('#paint-bucket').stop().animate({
				'background-position': '100%'
			}, 500);
			$designdivs.show();
			updateStyle();
			updateColors();
		} else {
			$('#paint-bucket').stop().animate({
				'background-position-x': '0%'
			}, 500);
			$designdivs.hide();
		}
	}

	function useDesign() {
		return $('#toggledesign').is(':checked');
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
			updateCode('style', $('#patternurl'), 'updatePattern');
		}
	}

	function generateForm(textOnly, $changed) {
		var changedLink = '';
		var textOnlyLink = '';
		var styleOnly = false;
		var formFields = '&'+$('input,textarea,select',$('#form-fields')).serialize();
		var styleFields = '';
		$('#side-sortables div.inside').each(function() {
			if($(this).parents('#formfields_select').length === 0) {
				styleFields =  $('input,textarea,select,textarea', $(this)).serialize() + '&'+styleFields;
			}
		});
		if($changed && $($changed,$('.grabber')).length > 0) {	changedLink = '&changed='+$changed.attr('id'); }

		if(textOnly === 'style') {
			textOnlyLink = '&styleOnly='+textOnly;
			styleOnly = true;
			textOnly = false;
			formFields = false;
		} else if(textOnly) {
			textOnlyLink = '&textOnly='+textOnly;
		}
		var fullFormFields = $('form#cc-form-settings').serialize();

		var date = Date.now();
		var verify = ScriptParams.rand + $('#cc-form-id').val() + date;
		var dataString = 'rand='+ScriptParams.rand+'&'+formFields+'&'+styleFields+textOnlyLink+changedLink+'&path='+ScriptParams.path+'&verify='+verify+'&date='+date+'&text='+JSON.stringify(ScriptParams.text); //+'&action=cc_get_form

		var ajaxTime= new Date().getTime();

		if(kwsdebug) { console.log(dataString); }

		$.ajax({
			type: 'POST',
			url: ScriptParams.path + 'form.php', // ScriptParams.adminajax was too slow!
			processData: false,
			isLocal: true,
			data:  dataString,
			success: function(data, textStatus, XMLHttpRequest){
				if(data) {
					var form = false;
					var css = false;
					var input = false;
					var pre = false;

					data = jQuery.parseJSON(data);

					// If we want to pass debug info, this works
					if(!empty(data.pre)) {
						$('body').prepend(data.pre);
					}

					if(!empty(data.input)) {
						input = $(data.input);

						if(input[0].length) {
							inputclass = input[1];
						} else {
							inputclass = input;
						}
						var replaceClass = $(inputclass).attr('class').replace(' kws_input_container', '');
						$('.grabber .kws_form div.'+replaceClass).replaceWith($(input));
						return;
					}
					if(!empty(data.form)) {
						form = data.form;

						// Process the lists
						form = form.replace('<!-- %%LISTSELECTION%% -->', generateLists());

						if($('.grabber .kws_form').length > 0) {
							$('.grabber .kws_form').replaceWith(form);
						} else {
							$('.grabber').append(form);
						}
						if(textOnly) { return; }
					}
					if(!empty(data.css)) {
						css = data.css;
						if($('.grabber style').length > 0) {
							$('.grabber style').addClass('remove').after(css);
							$('.grabber style.remove').remove();
						} else {
							$('.grabber').prepend(css);
						}
						if(styleOnly) { return; }
					}

				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				if(kwsdebug) {
					console.log(XMLHttpRequest, textStatus, errorThrown);
				}
				$('.grabber').css('width','80%').css('margin','0 auto').css('text-align', 'left').html('<h2><em>Eeep! That didn\'t work...</em></h2><h3>The error: <code>'+errorThrown+'</code></h3>');
				return false;
			},
			dataType: 'text'
		}).done(function (data, textStatus, XMLHttpRequest) {
			var totalTime = new Date().getTime()-ajaxTime;
			if(kwsdebug) { console.log("request took %s milliseconds.", totalTime, XMLHttpRequest); }
		});

		return false;

	}

	// When we're changing multiple settings, we don't want to run the process each time.
	function unbindSettings() {
		$("#form-fields ul.menu input, #cc-form-settings .formfields textarea").unbind('change');
		$("#cc-form-settings input, #cc-form-settings textarea, #cc-form-settings select").not('#form-fields ul.menu input').not('#form-fields ul.menu textarea').unbind('change');
	}

	function bindSettings() {
		//unbindSettings();

		// For text only
		$("#form-fields ul.menu input, #cc-form-settings .inside textarea").bind('change', function() {
			updateFormFields(true, $(this), '#cc-form-settings text - '+$(this).attr('id'));
		});
//		$("#settings:not(.formfields input,.formfields textarea)").bind('change', function() {  updateCode(false, false, '#settings'); });

		$("#cc-form-settings").bind('stylechange', function() {
			updateCode(false, false, 'stylechange trigger');
		});

		$("#cc-form-settings input, #cc-form-settings textarea, #cc-form-settings select")
		.not('.inside input')
		.not('.inside textarea')
		.bind('change',
			function() {
				updateFormFields('style', false, '#cc-form-settings style - '+$(this).attr('id'));
			}
		);
	}

	function updatePresets(preset) {

		if(kwsdebug) { console.log('updatePresets'); }

		if(!preset) {
			preset = $('#presets').val();
		}

		updateLabelSame(true);

		//unbindSettings();

		switch(preset) {

			case 'Plain':
				updateTextStyle('helvetica', '24', 'bold');
				updateLabelStyle('helvetica', '16', 'bold');
				updateTextColor('#333333');
				updateBorderColor('#400f0f');
				updateBorderRadius(0);
				updateBorderWidth(0);
				updateBoxWidthandPadding(0);
				updateBorderStyle('none');
				updateBackgroundStyle('transparent');
				updateBackgroundType();
				updateSafeSubscribe('gray');
				updateFormText("<h2>Newsletter signup:</h2>", true);
				updateEmailInput('email@example.com', true);
				updateDefaultButton('Subscribe', true);
				break;

			case 'Army':
				updateTextStyle('courier', '24', 'bold');
				updateLabelStyle('courier', '16', 'bold');
				updateTextColor('#f2d99f');
				updateBorderColor('#400f0f');
				updateBorderRadius(6);
				updateBorderWidth(7);
				updateBoxWidthandPadding(10);
				updateBorderStyle('solid');
				updateBackgroundStyle('gradient');
				updateBackgroundType('#498a2f','#472c0b', '', 100);
				updateSafeSubscribe('white');
				updateFormText("<h2>Receive our newsletter.</h2>\n<p>That&rsquo;s an order!</p>", true);
				updateEmailInput('soldier@yourdivision.com', true);
				updateDefaultButton('Enlist', true);
				break;

			case 'Apple':
				updateTextStyle('helvetica', '24', 'bold');
				updateLabelStyle('helvetica', '16', 'bold');
				updateTextColor('#333333');
				updateBorderColor('#cccccc');
				updateBorderRadius(20);
				updateBorderWidth(6);
				updateBorderStyle('solid');
				updateBackgroundStyle('gradient');
				updateBackgroundType('#ffffff','#cfcfcf', '', 100);
				updateSafeSubscribe('gray');
				updateFormText("<h2>Newsletter signup:</h2>", true);
				updateEmailInput('john.appleseed@apple.com', true);
				updateDefaultButton('iSignUp', true);
				break;

			case 'Jazz':
				updateTextStyle('palatino', '20', 'normal');
				updateLabelStyle('palatino', '16', 'normal');
				updateTextColor('#cfd1b3');
				updateBorderColor('#FFFFFF');
				updateBorderRadius(9);
				updateBorderWidth(4);
				updateBorderStyle('solid');
				updateBackgroundStyle('gradient');
				updateBackgroundType('#595187','#000000', '', 100);
				updateFormText('<h2>Scratch below to catch our newsletter, daddy-o.</h2>', true);
				updateEmailInput('jazzlover@npr.org', true);
				updateDefaultButton('Yeah', true);
				updateSafeSubscribe('white');
				break;

			case 'Impact':
				updateTextStyle('impact', '30', 'normal');
				updateLabelStyle('impact', '20', 'normal');
				updateTextColor('#e61010');
				updateBorderColor('#FFFFFF');
				updateBorderRadius(3);
				updateBorderWidth(10);
				updateBorderStyle('solid');
				updateBackgroundStyle('gradient');
				updateBackgroundType('#707070','#000000', '', 100);
				updateFormText('<h2>Our newsletter rocks!</h2><p>Get updates by email that will rock your world.</p>', true);
				updateEmailInput('uknowuwanna@signup.com', true);
				updateDefaultButton('ADD ME', true);
				break;

			case 'Barbie':
				updateTextStyle('comicsans', '24', 'bold');
				updateLabelStyle('comicsans', '16', 'bold');
				updateTextColor('#12748c');
				updateBorderColor('#f5f7b4');
				updateBorderRadius(20);
				updateBorderWidth(6);
				updateBorderStyle('solid');
				updateBackgroundStyle('gradient');
				updateBackgroundType('#d911d9','#d7cde6', '', 100);
				updateFormText('<h2>Like, do you want updates?</h2><p>You should <em>totally</em> sign up for our newsletter below!</p>', true);
				updateEmailInput('have@fun.com', true);
				updateSafeSubscribe('white');
				updateDefaultButton('Totally!', true);
				break;

			case 'NYC':
				updateTextStyle('georgia', '24', 'normal');
				updateLabelStyle('georgia', '16', 'normal');
				updateTextColor('#000000');
				updateBorderColor('#000000');
				updateBorderRadius(15);
				updateBorderWidth(6);
				updateBorderStyle('dashed');
				updateBackgroundStyle('gradient');
				updateBackgroundType('#ffffff','#f2fa05','', 100);
				updateFormText('<h2>Hey, ye gonna sign up or wat?</h2><p>Dis is our newslettah signup:</p>', true);
				updateEmailInput('take@thexpressway.com', true);
				updateSafeSubscribe('black');
				updateDefaultButton('Beep!', true);
				break;

			default:
				updateTextStyle('helvetica', '20', 'normal');
				updateLabelStyle('helvetica', '16', 'normal');
				updateTextColor('#accbf7');
				updateBorderColor('#000000');
				updateBorderRadius(14);
				updateBackgroundType('#ad0c0c','#000001', '', 100);
				updateFormText('<h2>Sign up for Email Newsletters</h2>', true);
				updateEmailInput('signmeup@example.com', true);
				updateSafeSubscribe('gray');
				updateDefaultButton('Go', true);
				break;
		}
		//updateBackgroundType();
		//updateFormFields('style', false, 'updatePresets');

		//bindSettings();
	}

	function eventKeys(e) {
		var code = (e.keyCode ? e.keyCode : e.which);
		if (code === 37 || code === 38 || code === 39 || code === 40 || code === 46 || code === 8 || code === 16) {
			return false;
		}else {
			return true;
		}
	}

	function updateCode(textOnly, $changed, from) {
		if(kwsdebug) { console.log('updateCode', textOnly, $changed, from); }
		$('#codeSwapLink').remove();
		updateWidthCalculator();
		updateDisabled();
		//updateSortOrder();

		generateForm(textOnly, $changed);
	}

	function updateBoxAlign(align) {
		if(empty(align)) {
			align = $('input[name=formalign]:checked').val();
		}
		if(align == 'center') {
			$('.kws_form').css({ float: 'none', margin : '0 auto'});
		} else {
			$('.kws_form').css({ float: align, margin : 'auto'});
		}

	}

	function updateBoxWidthandPadding(padding) {
		if(empty(padding)) {
			var paddingwidth = $('#paddingwidth').val() + 'px';
			$('.kws_form').css('padding', paddingwidth);
		} else {
			$('#paddingwidth').val(padding);
		}
		return;
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

	function updateInputSize($inputID, size) {
		$inputID.attr('size', size);
	}

	function mySorter(a,b){
		return $("input.position", $(a)).val() > $("input.position", $(b)).val() ? 1 : -1;
	}

	function sortFieldMenu() {
		$('#form-fields ul.menu li.menu-item').sort(mySorter).appendTo('#form-fields ul.menu');
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

	function updateFormFields(textOnly, $changed, from) {
		if(kwsdebug) { console.log('updateFormFields', textOnly,$changed, from); }

		//updateSortOrder();

		if(empty(textOnly) || textOnly === 'style') {
			updateStyle();
			updateColors();
		}
		if(textOnly !== 'style') {
			if(empty($changed)) {
				$('.wp-editor-textarea,ul.menu li.formfield').each(function() {
					updateFormField(textOnly, $(this));
				});
			} else {
				updateFormField(textOnly, $changed);
			}
		}

		updateCode(textOnly, $changed, 'updateFormFields');
	}

	function updateFormField(textOnly, $item) {

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
			if(textOnly !== 'style' || empty(textOnly)) { // This might save some time?
				$item.removeClass('checked').removeClass('ui-state-active');
			}
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

	function updateFormText(defaultformtext, preset) {
		if((preset && $('#pupt').is(':checked')) || (empty(preset) && !empty(defaultformtext))) {
			$('#intro_default').html(defaultformtext);
		}
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

	function updateBorderStyle(borderstyle) {
		if(empty(borderstyle)) {
			borderstyle =  $('#borderstyle').val();
		} else {
			$('#borderstyle').val(borderstyle);
		}
		if(borderstyle === 'none') {
			$('div#border .inside div:not(#borderstyleitem):not(.borderradius)').hide();
		} else {
			$('#border .inside div').show();
		}
	}

	function updateBorderWidth(borderwidth) {
		if(!empty(borderwidth)) {
			$('#borderwidth').val(borderwidth);
		}
		$('.kws_form').css({'border-width':$('#borderwidth').val()+'px'});
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
				$("#bgbottom label").text('Bottom Color:');
				$("#bgtop label").text('Top Color:');
				$("#gradheightli label span").text('Gradient Height:');
			} else {
				$("#bgbottom label").text('Right Color:');
				$("#bgtop label").text('Left Color:');
				$("#gradheightli label span").text('Gradient Width:');
			}
			$('#patternurl,#bgimage').attr('disabled', true);
			$('#color2,#gradheight,#gradwidth,#gradtype,#bgrepeat,#bgpos').removeAttr('disabled');
			updateBackgroundColor(color1,color2);
			updateGradient(color1,color2,url,height,gradtype);
		} else if(selection === 'solid') {
			//console.debug('solid');
			$("#bgtop,#gradheightli,#gradtypeli,#bgpattern,#bgurl").hide();
			$("#bgbottom").show();
			$("#bgbottom label").text('Background Color:');
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
			$("#bgbottom label").text('Background Color:');
			updateBackgroundURL();
		}
		//updateCode('style');
		// alert('c1: '+typeof(bordercolor) + ', c2: '+typeof(color2) + ', tc1: '+typeof() + ', tc2: '+typeof());
	}

	function updateBackgroundColor(color1, color2) {
		if(kwsdebug) { console.log('updateBackgroundColor', color1, color2); }
		if(borderstyle === 'none') {
			$('div#border .inside div:not(#borderstyleitem)').hide();
		} else {
			$('#border .inside div').show();
		}

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

	/**
	 * Generate a gradient using ozhgradient.php
	 *
	 * @todo Now that most browsers don't completely suck, convert to CSS gradients
	 * @param  string color1 The start color
	 * @param  string color2 The end color, in hex format ()
	 * @return boolean  True: success; False; error.
	 */
	function ajaxGradient(color1, color2) {
		if(kwsdebug) { console.log(color1 ,color2);}
		$.ajax({
			type: "POST",
			url: ScriptParams.path + 'ozhgradient.php',
			dataType: "text",
			data: 'start='+color1+'&end='+color2+'&height='+$('#gradheight').val(),
			async: true,
			error: function() { /* console.error('error generating gradient'); */ return false; },
			success: function(msg){
				var getImage = msg;
				var bgRule = '#'+color2+' url('+getImage+') left top repeat-x';
				$('#gradtype').trigger('stylechange');
				return true;
			}
		});
	}

	function updateGradient(color1,color2,url,gradheight, gradtype) {
			if(kwsdebug) { console.log('updateGradient', color1,color2,url,gradheight, gradtype); }
			if(color1 === '1') { return false; }

			color1 = $('#color6').wpColorPicker('color');
			color2 = $('#color2').wpColorPicker('color');

			if(kwsdebug) { console.log('in updateGradient. color1: '+ color1 + '; color2: '+color2); }
			if(empty(gradheight) || typeof(gradheight) === 'object') {
				gradheight = $('#gradheight').val();
			} else {
				$('#gradheight').val(gradheight);
			}
			if(empty(gradtype)) {
				gradtype = $('#gradtype').val();
			} else {
				$('#gradtype').val(gradtype);
			}
			if($('#gradtype').val() === 'vertical') {
				gradwidth = 1;
				$('#gradwidth').val(1);
			} else {
				gradwidth =$('.kws_form').width();
				$('#gradwidth').val(gradwidth);
			}

		ajaxGradient(color1, color2);
	}

	function updateSafeSubscribe(safesubscribe) {
		if( typeof( safesubscribe ) !== 'undefined' ) {
			if(safesubscribe === 'white') { safesubscribe = 'dark'; }
			if(safesubscribe === 'gray' || safesubscribe === 'grey') { safesubscribe = 'light'; }
			$('#constant-contact-signup .cc_safesubscribe').attr('class', null).addClass('cc_safesubscribe').addClass('safesubscribe_'+safesubscribe);
			$('input[name="safesubscribe"][value='+safesubscribe+']').click().attr('checked', true);
		}
		var safesubscribe_color = $('input[name="safesubscribe"]:checked').val();

		$('#constant-contact-signup .cc_safesubscribe').attr('class', null).addClass('cc_safesubscribe').addClass('safesubscribe_'+safesubscribe_color );
	}


	function updateExampleWrapper(examplebgcolor){
		if(kwsdebug) { console.log('updateExampleWrapper', examplebgcolor); }
		$('#examplewrapper').css('background-color', examplebgcolor);
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


	function updateLabelPadding(val) {
		if(kwsdebug) { console.log('updateLabelPadding', val); }
		if(empty(val)) {
			val = $('#lpad').val();
		}
		$('.kws_form .kws_input_container label').css("padding-top",val+'em');
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
	function updateBorderRadius(borderradius) {
		if(kwsdebug) { console.log('updateBorderRadius', borderradius);}
		if(!empty(borderradius)) {
			$("#borderradius").val(borderradius);
		} else {
			borderradius = $("#borderradius").val();
		}
		$('.kws_form').css({
			'-moz-border-radius':borderradius+'px '+ borderradius+'px',
			'-webkit-border-radius':borderradius+'px '+ borderradius+'px',
			'-ie-border-radius':borderradius+'px '+ borderradius+'px',
			'border-radius':borderradius+'px '+ borderradius+'px'
		});
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
		updateLabelPadding();
		updateLabelSame();
		updateBorderStyle();
		updateBoxAlign();
	}

	function updateAll() {
		updateTextInputSize($('#size').val());
		updateStyle();
		updateColors();
		showHideFormFields();
		updateFormFields(false, false, 'updateAll');
		sortFieldMenu();
	}
	updateAll();
	bindSettings();
	setupDesignBindings();



	// processStyle();
	$('form label.error').hide();

   $('label img').click(function(){
		$(this).closest('input[type=radio]').click();
   });


	$("a.toggleMore").on('click', function() {
		$(this).parents('ul').find('.toggleMore:not(a):not(:has(input[type=checkbox]:checked))').toggle('fast');

		var text = $(this).text();
		var text2 = text.replace('Show', 'Hide');
		if(text2 === text) {$(this).text(text.replace('Hide', 'Show')); } else { $(this).text(text2); }
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