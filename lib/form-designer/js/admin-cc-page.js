jQuery(document).ready(function($) {

	$('.cc_qtip').qtip({
		style: {
	      classes: 'ui-tooltip-light ui-tooltip-shadow',
	      width: '280px'
	 	}
	 });

	$('a.cc_logo').qtip({
	   content: {
	      text: $('.constant_contact_plugin_page_list.cc_hidden')
	   },
	   style: {
	      classes: 'ui-tooltip-light ui-tooltip-shadow',
	      width: '500px',
	      tip: {
	         corner: true,
	         height: 15,
	         width: 15
	      }
	   },
	   position: {
	      my: 'top left',  // Position my top left...
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

});