(function( $ ) {
	$.fn.poker = function(options) {

		// Создаём настройки по-умолчанию, расширяя их с помощью параметров, которые были переданы
		var settings = $.extend( {
			'state'				: 0,
			'location'          : 'top',
			'background-color'  : 'blue'
		}, options);

	};
})(jQuery);