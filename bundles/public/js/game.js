function onStart() {
	window.location.reload();
}

function startTime() {
	if (!$.cookie('gamesecond')) {
		$('#game-time').html('Тренировка');
		return;
	}
	
    var hours = +($.cookie('gamehour'));
    var minutes = +($.cookie('gameminute'));
    var seconds = +($.cookie('gamesecond')) + 1;
	
	if (seconds == 60) {
		minutes = minutes + 1;
		seconds = 0;
	}
	if (minutes == 60) {
		hours = hours + 1;
		minutes = 0;
	}
	$.cookie('gamehour', hours);
	$.cookie('gameminute', minutes);
    $.cookie('gamesecond', seconds);

	if (hours < 10) hours = "0" + hours;
    if (minutes < 10) minutes = "0" + minutes;
    if (seconds < 10) seconds = "0" + seconds;
    $('#game-time').html('Тренировка ' + hours + ":" + minutes + ":" + seconds);
}

function startTimer() {
	if (gametimer) {
		clearTimeout(gametimer);
	}
	var timerName = $.cookie('gametimer') || 'game-timer';
	
	if (!$.cookie('timerhandler')) {
		$('#' + timerName).empty();
		return;
	}
    var minutes = +($.cookie('timerminute'));
    var seconds = +($.cookie('timersecond'));
	
//	console.log([minutes, seconds, $.cookie('timerhandler')]);
	
	if (seconds == 0 && minutes > 0) {
		minutes -= 1;
		seconds = 59;
	} else {
		seconds -= 1;
	}
	
	if (seconds < 0 || minutes < 0) {
		$('#'+ timerName).html( "00:00" );
		var timerhandler = $.cookie('timerhandler');
//		console.log(timerhandler);
		$.removeCookie('timerhandler');
		stopTimer();
		window[timerhandler]();
		return;
	}
	
	$.cookie('timerminute', minutes);
    $.cookie('timersecond', seconds);
	
	if (minutes < 10) minutes = "0" + minutes;
    if (seconds < 10) seconds = "0" + seconds;
	
	$('#'+ timerName).html( minutes + ":" + seconds );
	
	gametimer = setTimeout(startTimer, 950);
}

function stopTimer() {
	$('.gamer-card-zoom').hide();
	$.removeCookie('timerhandler');
	$.removeCookie('timerminute');
	$.removeCookie('timersecond');
	clearTimeout(gametimer);
}

$(document).ready(function(){
	initTraining();
});



