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

	$('body').on('click', '.kwslog-toggle', function(e) {
		$('.data', $(this).parents('.kwslog-debug')).toggle();
		return false;
	});

	/**
	 * Select the text of an input field on click
	 * @filter default text
	 * @action default text
	 * @param  {[type]}    e     [description]
	 * @return {[type]}          [description]
	 */
	function ctct_select_text(e) {
	    e.preventDefault();

	    $(this).focus().select();

	    return false;
	}

	$('.ctct_table input[readonly], input[readonly].select-text').on('click', ctct_select_text );

	function ctct_pointers(target) {

    	if(typeof CTCT == 'undefined' || !CTCT || !CTCT.pointers || !CTCT.pointers.pointers) { return; }

    	CTCT.pointers.pointers.forEach(function(pointer, index, array) {
			ctct_pointer(pointer);
        });
    }

    function ctct_pointer(pointer) {

    	hide_pointers = $.cookie('ctct_hide_pointers');
    	hide_pointers_array = hide_pointers ? hide_pointers.split(/,/) : [];

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
    $('body').on('click', 'a[rel="wp-help"]', function() {

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

	$('#constant-contact_page_constant-contact-forms').ready(function() {
		ctct_pointers();
	});

	$('#ctct-settings-tabs').tabs({
		show: false,
		create: function(event, ui) {
			ctct_pointers();
			ctct_set_referrer();
		},
		activate: function(event, ui) {
			ctct_pointers();

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

	$('.inline-edit-update').on('click submit', function() {
		$(this).parent('span').addClass('submitting-in-progress');
	});

	/**
	 * Inline Edit
	 * @see KWSAjax::processAjax()
	 */
	$('.editable').attr('title', CTCT.text.editable ).inlineEdit({
		buttons: '<button class="save button button-primary inline-edit-update">Update</button> <button class="cancel button button-secondary">Cancel</button>',
		cancelOnBlur: true,
		placeholder: CTCT.text.editable,
		editInProgress: 'edit-in-progress',
		save: function(event, data, widget ) {
			var $that = $(this);

			$that.removeClass("edit-in-progress").addClass('saving-in-progress');

			ctct_ajax(data, $that).done(function ( success ) {

				$that.removeClass('saving-in-progress');

				if(success) {
					$that.effect("highlight", {color: '#e99e23'}, 3000);
				} else {
					$that.effect("highlight", {color: 'red'}, 3000);
				}
			});
		}
	});

	$('.constant-contact_page_constant-contact-contacts .ctct-lists').on('change', function() {

		// If we're on add/edit, don't run this AJAX stuff.
		if($('.edit-new-h2').length === 0) { return; }

		var data = {
			value : $('input', $(this)).serializeArray(),
			field : 'lists'
		};

		ctct_ajax(data, $(this));

	});

	function ctct_ajax(data, $object) {

		var id = ( typeof( CTCT ) !== 'undefined' && CTCT.id ) ? CTCT.id : $object.data('id');
		var field = $object.data('name') ? $object.data('name') : data.field;
		var parent = $object.data('parent') ? $object.data('parent') : data.parent;


		var result = $.Deferred();

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			async: true,
			isLocal: true,
			timeout: 15000, // 15 seconds is way more time than should be necessary.
			data: {
				'action': 'ctct_ajax',
				'_wpnonce': CTCT._wpnonce,
				'value': data.value,
				'id': id,
				'component': CTCT.component,
				'field': field,
				'parent': parent
			}
		})
		.done( function ( data, textStatus, jqXHR ) {

			var message = CTCT.text.request_nothing_changed;

			// Content has changed.
			if( 'nocontent' !== textStatus ) {
				var responseText = $.parseJSON( data );
				message = responseText.message;
			}

			// Just a friendly note it went well.
			alertify.log( message );

			result.resolve( true );
		})
		.fail( function ( data, textStatus, jqXHR ) {

			var responseText = $.parseJSON( data.responseText );

			var error_template = '<h3>{heading}</h3><p>{error_message}</p>';

			// If a CtctException, it's a message array. Otherwise, it's a string.
			var message = ( typeof( responseText.message ) === 'string' ) ? responseText.message : responseText.message[0].error_message.replace(/^.*?:/ig, '');
			var request_error = CTCT.text.request_error
				.replace('{code}', responseText.code )
				.replace('{message}', message );

			error_template = error_template
				.replace('{heading}', CTCT.text.request_failed_heading )
				.replace('{error_message}', request_error );

			// A full modal showing it didn't work.
			alertify.alert( error_template ).set('basic', true);

			result.resolve( false );
		});

		return result.promise();
	}

	// Set up support tooltips
	$('.cc_tip,.ctct_tip').each(function() {
		$(this).tooltip({
	        content: function () {
	            return $(this).prop('title');
	        }
	    });
     });


	$( document ).on( 'ready', function (e ) {
		$('.constant-contact-api-toggle[rel][type=radio]').filter(':checked').trigger('ctct_ready');
	} );

	$('.constant-contact-api-toggle[rel]').on('click ready ctct_ready save', function(e) {
		CTCTToggleVisibility($(this), e);
	});

	function CTCTToggleVisibility($that, event) {
		var speed = 'fast';
		if(typeof(event) === 'object' && (event.type === 'save-widget' || event.type === 'ctct_ready')) { speed = 0; }

		if($that.attr('rel') && $that.attr('rel') !== '') {

			var rel = $that.attr( 'rel' );
			var type = $that.parents( '.form-table' ).length ? 'tr' : 'div.ctct_setting';
			var checked = $that.is( ':checked' );
			var visible = $that.is( ':visible' );
			var $row = $( '.toggle_' + rel );

			if ( (checked && visible) || (checked && $that.parents( '.widget' ).length > 0) ) {

				if ( $that.attr( 'type' ) === 'radio' ) {
					// Process all the radio options in the same <tr>
					$( '.constant-contact-api-toggle', $that.parents( type ) ).each( function () {
						var $thisrel = $( '.toggle_' + $( this ).attr( 'rel' ) );
						var $thisrelparents = $thisrel.parents( type ).removeClass( 'ctct-closed' );
						if ( $( this ).attr( 'rel' ) !== 'false' ) {
							$thisrel.not( '.toggle_' + rel ).parents( type ).hide().addClass( 'ctct-closed' );
						}
						$thisrelparents.not( '.ctct-closed' ).CTCTShow( speed );
					} );
				} else {
					$that.attr( 'rel', false );
					$row.each( function () {
						var $thisrow = $( this );
						var show = true;
						// If the input is in multiple togglegroups, deal with that.
						var matches = $( this ).attr( 'class' ).match( /(toggle_.*?\b)+/gi );
						if ( matches && !$( 'body' ).hasClass( 'widgets-php' ) ) {
							$.each( matches, function ( k, v ) {
								var $input = $( 'input[rel=' + v.replace( 'toggle_', '' ) + ']' );
								if ( $input.length > 0 && !$input.attr( 'checked' ) ) {
									show = false;
								}
							} );
						}
						if ( show === true ) {
							$( this ).parents( type ).CTCTShow( speed );
						}
					} );
				}
			} else {
				$row.each( function () {
					$( this ).parents( type ).CTCTHide( speed );
				} );
			}

			$that.attr( 'rel', rel );
		}
	}

});