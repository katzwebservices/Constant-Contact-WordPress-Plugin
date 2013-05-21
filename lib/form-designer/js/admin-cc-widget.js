jQuery.noConflict();

jQuery(document).ready(function($) {
	$('.widget div.moreInfo').hide();
	
	$('.widget a.moreInfo').live('click', function(e) {
		e.preventDefault();
		$(this).parents('div.widget').find('div#'+$(this).attr('href')).slideToggle();
		return false;
	})
	
	$('input[type=checkbox].list-selection').live('load change click ccwidgetsaved', function() {
		var $that = $(this);
		var $widget = $that.parents('div.widget');
		
		if($(this).is(':checked')) {
			$('tr.list-selection', $widget).show();
			$('tr.list-selection', $widget).find('input, select, textarea').each(function() { $(this).attr('disabled', false); });
			$('tr.contact-lists th label span').text('Provide Users with List Options');
			$('tr.contact-lists p.description').html('Lists selected above will be shown to the user to choose from. <strong>If you do not select any lists above</strong> the user will be able to choose from all of your contact lists, apart from those set to be hidden using the setting below.');
		} else {
			$('tr.contact-lists th label span').text('Add Users to Lists');
			$('tr.list-selection', $widget).find('input, select, textarea').each(function() { $(this).attr('disabled', true); });
			$('tr.contact-lists p.description').html('<strong>Users will be automatically subscribed</strong> to all lists selected above.');
			$('tr.list-selection', $widget).hide();
		}
	});
	
	$('input[type=checkbox].list-selection').trigger('load');
});

jQuery(document).ajaxSuccess(function(e, xhr, settings) {
	var widget_id_base = 'constant_contact_form_widget';

	if((settings.data.search('action=save-widget') != -1 && settings.data.search('id_base=' + widget_id_base) != -1) ||
	   (settings.data.search('action=add-widget') != -1 && settings.data.search('id_base=' + widget_id_base) != -1)
	  ) {
		jQuery('input[type=checkbox].list-selection').change();
	}
		
});