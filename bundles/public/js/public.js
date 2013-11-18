/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function sendMessage() {
	var email = $('#messageForm input[name=email]').val();
	var message = $('#messageForm textarea[name=message]').val();
	if (!email || !message) {
		return;
	}
	formdata = $('#messageForm').serialize();
	$.post(prj_ref+"/contacts/message", {formdata: formdata},
	function(data){
		$('#messageResult').hide();
		if (data.error) {
			$('#messageResult').removeClass('label-success');
			$('#messageResult').addClass('label-danger');
		} else {
			$('#messageResult').removeClass('label-danger');
			$('#messageResult').addClass('label-success');
		}
		$('#messageResult').html(data.content);
		$('#messageResult').show();
		$('#messageForm')[0].reset();
	}, "json");
}

function showAddDialog() {
	$.post(prj_ref+"/gifts/form", {},
	function(data){
		$('#myModal .modal-header').html(data.header);
		$('#myModal .modal-body').html(data.content);
		$('#myModal .modal-footer').html(data.footer);
		$('#myModal').modal('show');
	}, "json");
}

function showListDialog(id, node) {
	$.post(prj_ref+"/"+node+"/list", {id: id},
	function(data){
		$('#myModal .modal-body').css('max-height', '370px');
		$('#myModal .modal-header').html(data.header);
		$('#myModal .modal-body').html(data.content);
		$('#myModal .modal-footer').html(data.footer);
		$('#myModal').modal('show');
	}, "json");
}

function sendAdvice() {
	var author = $('#adviceForm input[name=author]').val();
	var message = $('#adviceForm textarea[name=message]').val();
	if (!author || !message) {
		return;
	}
	$('#messageResult').empty();
	$('#messageResult').addClass('hidden');
	$('#messageResult').removeClass('label-danger');
	$('#messageResult').addClass('label-success');
	$('#messageResult').html('Подождите. Идет сохранение информации...');
	$('#messageResult').removeClass('hidden');
	formdata = $('#adviceForm').serialize();
	$.post(prj_ref+"/contacts/advice", {formdata: formdata},
	function(data){
		$('#messageResult').empty();
		$('#messageResult').addClass('hidden');
		$('#adviceForm textarea[name=message]').val('');
		$('#chat').prepend(data.content);
	}, "json");
}

function sendAdviceComment(presentId, adviceId) {
	var author = $('#commentForm'+adviceId+' input[name=author]').val();
	var message = $('#commentForm'+adviceId+' textarea[name=message]').val();
	if (!author || !message) {
		return;
	}
	$('#commentResult'+adviceId).empty();
	$('#commentResult'+adviceId).addClass('hidden');
	$('#commentResult'+adviceId).removeClass('label-danger');
	$('#commentResult'+adviceId).addClass('label-success');
	$('#commentResult'+adviceId).html('Подождите. Идет сохранение информации...');
	$('#commentResult'+adviceId).removeClass('hidden');
	formdata = $('#commentForm'+adviceId).serialize();
	$.post(prj_ref+"/contacts/comment", {formdata: formdata, present_id: presentId, advice_id: adviceId},
	function(data){
		$('#commentResult'+adviceId).empty();
		$('#commentResult'+adviceId).addClass('hidden');
		$('#commentForm'+adviceId+' textarea[name=message]').val('');
		$('#comments'+adviceId).append(data.content);
	}, "json");
}

function getOldAdvices(presentId) {
	$.post(prj_ref+"/contacts/old", {present_id: presentId, advice_id: old_advice},
	function(data){
		if ('' == data.content) {
			$('#more').toggleClass('hidden');
		} else {
			old_advice = data.id;
			$('#chat').append(data.content);
		}
	}, "json");
}

function sendPresent() {
	$('#addForm').submit();
}

function startUpload(){
    $('#messageResult').show();
	name = $('#addForm input[name=name]').val();
	contact = $('#addForm input[name=contact]').val();
	message = $('#addForm textarea[name=message]').val();
	if ('' == name || '' == contact || '' == message) {
		$('#messageResult').removeClass('label-success');
		$('#messageResult').addClass('label-danger');
		$('#messageResult').html('Не заполнены обязательные поля');
		$('#messageResult').removeClass('hidden');
		return false;
	} else {
		$('#messageResult').removeClass('label-danger');
		$('#messageResult').addClass('label-success');
		$('#messageResult').html('Подождите. Идет сохранение информации...');
		$('#messageResult').removeClass('hidden');
		return true;
	}
}

function stopUpload(success, text){
    if (success == 1){
		$('#messageResult').removeClass('label-danger');
		$('#messageResult').addClass('label-success');
		$('#messageResult').html(text);
		$('#addForm')[0].reset();
      }
    else {
		$('#messageResult').removeClass('label-success');
		$('#messageResult').addClass('label-danger');
		$('#messageResult').html('Ошибка отправления информации.');
    }
	return true;   
}

function sendVote() {
	if (!$('#voteForm input[name=present]:checked').length) {
		return false;
	}
	key = $('#voteForm input[name=key]').val();
	present_id = $('#voteForm input[name=present]:checked').val();
	$.post(prj_ref+"/vote/present", {present_id: present_id, key: key},
	function(data){
		if (data.error) {
			$('#myModal2 .modal-content').removeClass('green-body');
			$('#myModal2 .modal-content').addClass('blue-body');
		} else {
			$('#myModal2 .modal-content').removeClass('blue-body');
			$('#myModal2 .modal-content').addClass('green-body');
		}
		$('#myModal2 .modal-body').html(data.message);
		$('#myModal2').modal('show');
		$('#voteForm input[type=radio]').remove();
		$('#voteButton').remove();
//		$(document).scrollTop(0);
	}, "json");
}