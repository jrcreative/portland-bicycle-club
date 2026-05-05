(function($) {
	'use strict';
	$(window).load(function(){
		disable_plugins();
	});

	$(document).ajaxComplete(function(event, request, settings){
		if(settings.data.indexOf('pagenow=plugin-install') != -1 && settings.data.indexOf('action=search-install-plugins') != -1){
			disable_plugins();
		}
	});

	function disable_plugins(){
		$.each(banned.list, function(key, value){
			if($('.plugin-card.plugin-card-' + value).length > 0){
				$('.plugin-card.plugin-card-' + value).find('a.install-now').addClass('disabled');
				$('.plugin-card.plugin-card-' + value).find('a.install-now').attr('href', 'javascript:void(0)');
				$('.plugin-card.plugin-card-' + value).find('a.install-now').attr('disabled', 'disabled');
				$('.plugin-card.plugin-card-' + value).find('a.install-now').removeAttr('data-slug');
				$('.plugin-card.plugin-card-' + value).find('a.open-plugin-details-modal').eq(0).attr('href', 'javascript:void(0)');
				$('.plugin-card.plugin-card-' + value).find('a.open-plugin-details-modal').eq(0).removeClass('thickbox');
				$('.plugin-card.plugin-card-' + value).find('a.open-plugin-details-modal').eq(1).remove();
			}
		});
	}
})(jQuery);