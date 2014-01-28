$(document).ready(function() {
	
	$('.register-avatar input[type=file]').on('change', function(event) {
		var pathname = $(this).val();
		var pos = pathname.lastIndexOf('/') > 0 ? pathname.lastIndexOf('/') : pathname.lastIndexOf('\\');
		pathname = pathname.substring(pos+1);
		$('.register-avatar').append($('<span></span>').addClass('file-title').html(pathname));
		$('.register-avatar a.remove-icon').show();
	});
	
	$('.register-avatar a.remove-icon').on('click', function(event) {
		$('.register-avatar input[type=file]').val('');
		$('.register-avatar .file-title').remove();
		$(this).hide();
	});
	
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
				$('.member-card').html(data.content);
				$('.member-card a.close').on('click', function() {
					$('.member-card').hide();
				});
				$('.member-card').show();
			} else {
				alert(data.content);
			}
		}, "json");
	});
});