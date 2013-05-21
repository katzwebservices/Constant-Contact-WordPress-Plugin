(function( $ ) {
	jQuery.fn.CTCTHide = function(speed) {
		if(typeof(speed) === 'undefined') { speed = 'fast'; }
		this.fadeOut(speed, function() { $(this).addClass('idx-closed'); });
	};
	jQuery.fn.CTCTShow = function(speed) {
		if(typeof(speed) === 'undefined') { speed = 'fast'; }
		this.fadeIn(speed, function() { $(this).removeClass('idx-closed'); });
	};
})( jQuery );

jQuery(document).ready(function($) {

	
	function ctct_pointers(target) {

    	if(typeof CTCT == 'undefined' || !CTCT || !CTCT.pointers.pointers) { return; }
    	
    	CTCT.pointers.pointers.forEach(function(pointer, index, array) {
			ctct_pointer(pointer);
        });
    }

    function ctct_pointer(pointer) {
    	
    	hide_pointers = $.cookie('ctct_hide_pointers');
    	hide_pointers_array = hide_pointers ? hide_pointers.split(/,/) : new Array();
    	
    	if($.inArray(pointer.pointer_id, hide_pointers_array) >= 0) { return; }
    	
		options = $.extend( pointer.options, {
    	    close: function(event) {
    			    	
    	    	hide_pointers_array.push(pointer.pointer_id);

    	    	$.cookie('ctct_hide_pointers', hide_pointers_array.join(','));
    	    	
    	    	$.post( ajaxurl, {
    	            pointer: pointer.pointer_id,
    	            action: 'dismiss-wp-pointer'
    	        });
    	    }
    	});
    	
    	$(pointer.target).data('pointer_id', pointer.pointer_id).pointer( options );

		if($(pointer.target).is(':visible')) {
    		$(pointer.target).pointer('open');
    	} else {
    		$(pointer.target).pointer('destroy');
    	}

    }

    /**
     * Toggle the WP help menu tab by linking to them
     */
    $('a[rel="wp-help"]').live('click', function() {
    	
    	if($('#screen-meta').is(':hidden')) {
    		$('#contextual-help-link').click();
    	}
    	
    	$('#screen-meta a[href*="' + $(this).attr('href').replace('#', '') +'"]').click();
    	
    	return false;
    });

	// Fix the issue caused by having plugin status down there.
	$('#wpbody').css('padding-bottom', $('#wpfooter').height());

	$('a[rel~=external]').attr('target', '_blank');

	$('.tablink').click(function(e) {
		e.preventDefault();
		$($(this).attr('href') + '-link').click();
		return false;
	});

	$('.confirm').click(function() {
	
		var confirm1 = confirm($(this).data('confirm'));
		var confirm2 = $(this).data('confirm-again');
	
		if(confirm1) {
			if(confirm2) { return confirm(confirm2); }
			return true;
		} else {
			return false;
		}
	});

	$('.constant-contact_page_constant-contact-contacts .subsubsub a').click(function(e) {
		e.preventDefault();

		// Hide the "no contacts with this status" message
		$('.show-if-empty').hide();

		$('.subsubsub li a').removeClass('current');
		$(this).addClass('current');

		$('.ctct_table tr:not(.show-if-empty)').show();

		var text = $(this).text().toUpperCase();

		// The first link ("all") is always show all.
		if($('.subsubsub li a').index(this) > 0) {
			$('.ctct_table td.column-status').not(':contains('+text+')').not(':contains('+text.replace('-', '_')+')').not(':contains('+text.replace('-', '')+')').parents('tr').hide();
		}
		if($('.ctct_table td.column-status:visible').length === 0) {
			$('.show-if-empty').show();
		}
	});

	$(".select2").select2();

	$('#constant-contact_page_constant-contact-forms').ready(function() {
		ctct_pointers();
	});

	function ctct_hide_save() {
		if($('#ctct-settings-tabs #setup').is(':visible')) {
			$('p.submit .button-primary').hide();
		} else {
			$('p.submit .button-primary').show();
		}
	}

	$('#ctct-settings-tabs').tabs({
		show: false,
		create: function(event, ui) {
			ctct_pointers();
			ctct_set_referrer();
			ctct_hide_save();
		},
		activate: function(event, ui) {
			ctct_pointers();
			ctct_hide_save();

			$(this).addClass('size-'+$('.ui-tabs-nav', $(this)).length);
			event.preventDefault();

			var hash = $('a', ui.newTab).attr('href').replace( /^#/, '' );
			var fx, node = $( '#' + hash );

			if ( node.length ) {
				node.attr( 'id', '' );

				fx = $( '<div></div>' )
					.css({
						position:'absolute',
						visibility:'hidden',
						top: $(document).scrollTop() + 'px'
					})
					.attr( 'id', hash )
					.prependTo( $('body') );
			}

			document.location.hash = hash;

			if ( node.length ) {
				fx.remove();
				node.attr( 'id', hash );
			}

			ctct_set_referrer();

			$('.constant-contact-api-toggle[rel]').trigger('ctct_ready');
			
			return false;
		},
		cookie: { expires: 1 }
	});

	function ctct_set_referrer() {
		
		var $referrer = $('input[name=_wp_http_referer]', '.toplevel_page_constant-contact-api');
		
		if($referrer.length) {
			$referrer.val($referrer.val().replace(/(#.+)$/gi, '') + document.location.hash);
		}
	}

	// setup common ajax setting
	$.ajaxSetup({
		url: ajaxurl,
		type: 'POST',
		async: false,
		timeout: 500
	});

	$('.inline-edit-update').on('click submit', function() {
		$(this).parent('span').addClass('submitting-in-progress');
	});

	/**
	 * Inline Edit
	 * @see KWSAjax::processAjax()
	 */
	$('.editable').attr('title', 'Click to Edit').inlineEdit({
		buttons: '<button class="save button button-primary inline-edit-update">Update</button> <button class="cancel button button-secondary">Cancel</button>',
		cancelOnBlur: true,
		valeu: function() {
			console.log($(this));
		},
		editInProgress: 'edit-in-progress',
		save: function(event, data) {
			var $that = $(this);

			$that.removeClass("edit-in-progress").addClass('saving-in-progress');

			var success = ctct_ajax(data, $that);

			if(success) {
				$('*[data-name='+$that.data('name')+'][data-id='+$that.data('id')+']').not($that).html(data.value).each(function() {
					$(this).effect("highlight", {color: '#e99e23'}, 3000);
				});
			}

			$(this).removeClass('saving-in-progress');

			return success;
		}
	});

	$('.constant-contact_page_constant-contact-contacts .ctct-lists').on('change', function() {

		var data = {
			value : $('input', $(this)).serialize(),
			field : 'lists'
		};

		ctct_ajax(data, $(this));

	});

	function ctct_ajax(data, $object) {

		var id = CTCT.id ? CTCT.id : $object.data('id');
		var field = $object.data('name') ? $object.data('name') : data.field;
		var parent = $object.data('parent') ? $object.data('parent') : data.parent;

		var response = $.ajax({
			data: {
				'action': 'ctct_ajax',
				'_wpnonce': CTCT._wpnonce,
				'value': data.value,
				'id': id,
				'component': CTCT.component,
				'field': field,
				'parent': parent
			}
		});

		var responseText = $.parseJSON(response.responseText);

		if(parseInt(responseText.code, 10) === 400) {
			$object.effect("highlight", {color: 'red'}, 2000);
			alertify.alert('<h3>The request failed.</h3><p>Error ' + responseText.code + ': '+responseText.message[0].error_message.replace(/^.*:/ig, '')+'</p>');
			return false;
		}

		alertify.log(responseText.message);

		return true;
	}

	// For support
	$('.cc_qtip,.ctct_qtip').qtip({
		style: {
			classes: 'ui-tooltip-light ui-tooltip-shadow ctct-tooltip',
			width: '280px'
		}
	});

	// For logo dropdown
	$('a.cc_logo').qtip({
		content: {
		text: $('.constant_contact_plugin_page_list.cc_hidden')
	},
	style: {
		classes: 'ui-tooltip-light ui-tooltip-shadow ctct-tooltip',
		width: '500px',
		tip: {
			corner: true,
			height: 15,
			width: 15
		}
	},
	position: {
		my: 'top left',// Position my top left...
		at: 'bottom left', // at the bottom right of...
		target: $('a.cc_logo'), // my target
		adjust: {
			x: 20
		}
	},
	show: {
		target: $('a.cc_logo') // my target
	},
	hide: {
		fixed: true,
		delay: 500
	}
	});

	// For component summary boxes
	$('.fittext').fitText(1.2, {maxFontSize: 50});
	$(window).on('resize ready', function() {
		$(".component-summary dd").fitText('.9', {maxFontSize: 120, minFontSize: 48});
		$('.component-summary').equalize({children: 'dl', reset: true});
	});


	$('.constant-contact-api-toggle[rel]').on('click ready ctct_ready save', function(e) {
		CTCTToggleVisibility($(this), e);
	}).each(function(e) {
		CTCTToggleVisibility($(this), e);
	});

	function CTCTToggleVisibility($that, event) {
		var speed = 'fast';
		if(typeof(event) === 'object' && (event.type === 'save-widget' || event.type === 'ctct_ready')) { speed = 0; }

		if($that.attr('rel') && $that.attr('rel') !== '') {

			var rel = $that.attr('rel');
			var type = $that.parents('.form-table').length ? 'tr' : 'div.ctct_setting';
			var checked = $that.is(':checked');
			var visible = $that.is(':visible');
			var $row = $('.toggle_'+rel);

			if((checked && visible) || (checked && $that.parents('.widget').length > 0)) {

				if($that.attr('type') === 'radio') {
					// Process all the radio options in the same <tr>
					$('.constant-contact-api-toggle', $that.parents(type)).each(function() {
						var $thisrel = $('.toggle_'+$(this).attr('rel'));
						var $thisrelparents = $thisrel.parents(type).removeClass('ctct-closed');
						if($(this).attr('rel') !== 'false') {
							$thisrel.not('.toggle_'+rel).parents(type).hide().addClass('ctct-closed');
						}
						$thisrelparents.not('.ctct-closed').CTCTShow(speed);
					});
				} else {
					$that.attr('rel', false);
					$row.each(function() {
						var $thisrow = $(this); var show = true;
						// If the input is in multiple togglegroups, deal with that.
						var matches = $(this).attr('class').match(/(toggle_.*?\b)+/gi);
						if(matches && !$('body').hasClass('widgets-php')) {
							$.each(matches, function(k, v) {
								var $input = $('input[rel='+v.replace('toggle_', '')+']');
								if($input.length > 0 && !$input.attr('checked')) { show = false; }
							});
						}
						if(show === true) { $(this).parents(type).CTCTShow(speed); }
					});
				}
			} else {
				$row.each(function() { $(this).parents(type).CTCTHide(speed); });
			}

			$that.attr('rel', rel);
		}
	}

});