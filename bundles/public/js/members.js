var input_ajax = false;

$(document).ready(function() {
		
	// members
	$(document).on('click', 'button[data-action=like]', function () {
		var memberId = $(this).parents('.member, .user-card').attr('data-member-id');
		$.post('/members/like/' + memberId, {},
		function(data){
			if (data.ok) {
				$('span[data-like-id='+memberId+']').html(data.content);
			} else {
				alert(data.content);
			}
		}, "json");
	});
	
	$(document).on('click', 'button[data-action=bet]', function () {
		alert($(this).parents('.member').attr('data-member-id'));
	});
	
	$(document).on('click', 'a[data-action=card]', function () {
		var memberId = $(this).parents('.member').attr('data-member-id');
		
		$.post('/members/card/' + memberId, {},
		function(data){
			if (data.ok) {
				var top = ($(window).height()-590)/2;
				top = top < 0 ? 50 : top;
				$('.member-card').html(data.content);
				$('.member-card').css('top', top);
				$('.member-card a.close').on('click', function() {
					$('.member-card').hide();
				});
				$('.member-card').show();
			} else {
				alert(data.content);
			}
		}, "json");
	});
	
	$(document).on('keyup', '.member-search input', function(event) {
		input_ajax = true;
	});
	
	$('.member-search input').idle({
		onIdle: function(){
			if (!input_ajax) {
				return;
			}
			var text = $('.member-search input').val();
			if (!text) {
				text = '1=1';
			}
			input_ajax = false;
			$.post('/members', {text: text},
			function(data){
				if (data.ok) {
					$('#members').html(data.content);
				} else {
					alert(data.content);
				}
			}, "json");
		},
		events: 'keyup',
		idle: 1000
	});
		
});