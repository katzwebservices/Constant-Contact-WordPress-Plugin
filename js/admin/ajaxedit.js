jQuery(function($){
	// setup common ajax setting
	$.ajaxSetup({
		url: ajaxurl,
		type: 'POST',
		async: false,
		timeout: 500
	});

	// call inlineEdit
	$('.editable').inlineEdit({
		value: $.ajax({ data: { 'action': 'get' } }).responseText,
		save: function(event, data) {
			var html = $.ajax({
				data: { 'action': 'save', 'value': data.value }
			}).responseText;

			alert("id: " + this.id );

			return html === 'OK' ? true : false;
		}
	});
});