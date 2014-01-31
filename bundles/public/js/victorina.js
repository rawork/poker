var timerNode = null;
var eventtimerId = null;

function stopGame() {
	$.post('/victorina/stop', {},
	function(data){
		window.location.reload();
	}, "json");
	
}

function startTimer() {
	var timerName = timerNode || 'game-time';
	
	if (!$.cookie('timerhandler')) {
		$('#' + timerName).empty();
		return;
	}
    var minutes = +($.cookie('timerminute'));
    var seconds = +($.cookie('timersecond'));
	
	if (seconds == 0 && minutes > 0) {
		minutes -= 1;
		seconds = 59;
	} else {
		seconds -= 1;
	}
	
	if (seconds < 0 || (seconds <= 0 && minutes <= 0)) {
		$('#'+ timerName).html( "00:00" );
		var timerhandler = $.cookie('timerhandler');
		removeTimer();
		window[timerhandler]();
		return;
	}
	
	$.cookie('timerminute', minutes);
    $.cookie('timersecond', seconds);
	
	if (minutes < 10) minutes = "0" + minutes;
    if (seconds < 10) seconds = "0" + seconds;
	
	$('#'+ timerName).html( minutes + ":" + seconds );
	
	eventtimerId = setTimeout(startTimer, 1000);
}

function removeTimer() {
	clearTimeout(eventtimerId);
	$.removeCookie('timerhandler');
	$.removeCookie('timerminute');
	$.removeCookie('timersecond');
}

$(document).ready(function(){
	startTimer();
	
	$(document).on('click', '.question.active', function() {
		var questionId = $(this).attr('data-question-id');
		$.post('/victorina/question/' + questionId, {},
		function(data){
			if (data.ok) {
				var top = ($(window).height()-370)/2;
				top = top < 0 ? 50 : top;
				$('#victorina-question').html(data.content);
				$('#victorina-question').css('top', top);
				$('#victorina-question').show();
			} else {
				window.location.reload();
			}
		}, "json");
	});
	
	$(document).on('click', 'a[data-action=close]', function() {
		$('#victorina-question').hide();
	});
	
	$(document).on('click', 'button[data-action=answer]', function() {
		var n   = $('.question-answer i.active').attr('data-answer-id');
		if (!n) {
			return;
		}
		var questionId = $('.question-answer').attr('data-question-id');
		$.post('/victorina/answer', {question_id: questionId, answer_no: n},
		function(data){
			if (data.ok) {
				$('#victorina-tooltip').html(data.content);
				$('.question[data-question-id='+questionId+']').removeClass('active');
				$('.question[data-question-id='+questionId+']').html('<img src="/bundles/public/img/cards/'+data.card+'.png"></img>');
				$('#victorina-question').hide();
			} else {
				window.location.reload();
			}
		}, "json");
	});
	
	$(document).on('click', '.question-answer i', function() {
		$('.question-answer i.active').removeClass('active');
		$(this).addClass('active');
	});
});