
$(document).ready(function() {
	
	//register && edit user data
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
		
});