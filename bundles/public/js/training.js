var eventtimerId = null;

function onChooseAnswer(event){
	var target = event.target || event.srcElement;

	while(target != this) {
		if (target.nodeName == 'LI') {
			$('.question-answer i.active').removeClass('active');
			$(target).children('i').addClass('active');
			break;
		}
		target = target.parentNode;
	}
}

function nextGame() {
	$.post('/training/next', {},
	function(data){
		if (data.ok) {
			window.location.reload();
		}
	}, "json");
}

function showQuestion() {
	console.log('showQuestion.start');
	$.post('/training/question', {},
	function(data){
		if (data.ok) {
			$('#table').html(data.content);
			$('.question-footer .btn').on('click', onClickAnswer);
			$('.question-answer').on('click', onChooseAnswer);
			gametimer = data.timer.holder;
			startTimer();
		}
	}, "json");
}

function onClickAnswer(event) {
	var n = $('.question-answer i.active').attr('data-answer-id');
	removeTimer();
	$.post('/training/answer', {answer: n},
	function(data){
		if (data.ok) {
			window.location.reload();
		}
	}, "json");
}

function clickNoAnswer(event) {
	removeTimer();
	$.post('/training/answer', {answer: 0},
	function(data){
		if (data.ok) {
			window.location.reload();
		}
	}, "json");
}

function onClickBuyAnswer() {
	var n = $('.question-answer i.active').attr('data-answer-id');
	$.post('/training/buy', {answer: n},
	function(data){
		if (data.ok) {
			window.location.reload();
		}
	}, "json");
}

function onClickNoBuy() {
	$.post('/training/buy', {answer: 0},
	function(data){
		if (data.ok) {
			window.location.reload();
		}
	}, "json");
}

function chooseCard(event){
	var n = $('.card.active').length;
	var target = event.target || event.srcElement;

	while(target != this) { 
		if ($(target).hasClass('card')) { 
			if (n < 2 || $(target).hasClass('active')) {
				$(target).toggleClass('active');
			}
			break;
		}
		target = target.parentNode;
	}
}

function changeCard(event) {
	var n = $('.card.active').length;
	var target = event.target || event.srcElement;

	while(target != this) { 
		if ($(target).hasClass('btn')) { 
			if (n > 0  && $(target).hasClass('btn-primary')) {
				onClickChange();
			} else if ($(target).hasClass('btn-danger')) {
				onClickNoChange();
			}
			break;
		}
		target = target.parentNode;
	}
}

function onClickChange() {
	cards = [];
	$('.card.active').each(function( index ) {
		cards.push($( this ).attr('data-card-id'));
	});
	removeTimer();
	$.post('/training/change', {cards: cards},
	function(data){
		if (data.ok) {
			showQuestion();
		}
	}, "json");
}

function onClickNoChange() {
	$.post('/training/nochange', {},
	function(data){
		if (data.ok) {
			window.location.reload();
		}
	}, "json");
}

function makeMove() {
	var target = event.target || event.srcElement;

	while(target != this) { 
		if ($(target).hasClass('btn')) { 
			if ($(target).attr('data-move') == 'vabank') {
				onClickVaBank();
			} else if ($(target).attr('data-move') == 'bet') {
				onClickBet();
			} else if ($(target).attr('data-move') == 'check') {
				onClickCheck();
			} else if ($(target).attr('data-move') == 'fold') {
				onClickFold();
			} else if ($(target).attr('data-move') == 'new') {
				onClickNew();
			}
			break;
		}
		target = target.parentNode;
	}
}

function onClickVaBank() {
	$('#input_bet').val($('#chips').text());
	var bet = +($('#input_bet').val());
	makeBet(bet);
}

function onClickBet() {
	var chips = +($('#chips').text());
	var bet = +($('#input_bet').val());
	if ( bet > chips || gamemaxbet > bet ) {
		return;
	}
	if ( gameallin ) {
		bet = chips;
	}
	makeBet(bet);
}

function onClickCheck() {
	var chips = +($('#chips').text());
	var bet = 0;
	if (gamemaxbet > gamerbet) {
		needbet = gamemaxbet - gamerbet;
		bet = chips > needbet ? needbet : chips;
	}
	makeBet(bet);
}

function onClickNew() {
	$.post('/training/start', {},
	function(data){
		if (data.ok) {
			window.location.reload();
		}
	}, "json");
}

function onClickStop() {
	$.post('/training/stop', {},
	function(data){
		if (data.ok) {
			window.location.reload();
		}
	}, "json");
}

function makeBet(chips) {
	$.post('/training/bet', {chips: chips},
	function(data){
		if (data.ok) {
			window.location.reload();
		}
	}, "json");
}

function onClickFold() {
	$.post('/training/fold', {},
	function(data){
		if (data.ok) {
			window.location.reload();
		}
	}, "json");
}

function distributeWin() {
	$.post('/training/win', {},
	function(data){
		if (data.ok) {
			window.location.reload();
		}
	}, "json");
}

function nextGame() {
	$.post('/training/next', {},
	function(data){
		if (data.ok) {
			window.location.reload();
		}
	}, "json");
}

function enableButtons() {
	switch (gamestate) {
		case 2:
		case 3:
			$('.game-buttons input').prop('disabled', false);
			break;
		default:
			$('.game-buttons input').prop('disabled', true);
	}
	$('.game-buttons input[data-move=new]').prop('disabled', false);
}

$(document).ready(function(){
	if (gametraining) {
		startTraining();
	}
});

function startTraining() {
	startTime();
	if (gamestate == 0) {
		$('.game-start button').on('click', onClickNew);
	} else if (gamestate == 1) {
		$('.game-message').on('click', changeCard);
		$('.gamer-cards .card').css('cursor', 'pointer');
		$('#gamer-cards').on('click', chooseCard);
	} else if (gamestate == 5) {
		$('.question-footer .btn').on('click', onClickBuyAnswer);
		$('.question-footer a').on('click', onClickNoBuy);
		$('.question-answer').on('click', onChooseAnswer);
	} else if (gamestate == 6) {
		$('.game-message .btn-primary').on('click', onClickNew);
		$('.game-message .btn-danger').on('click', onClickStop); 
	} else if (gamestate == 11) {
		$('.question-footer .btn').on('click', onClickAnswer);
		$('.question-answer').on('click', onChooseAnswer);
	}
	$('.game-buttons').on('click', makeMove);
	enableButtons();
	startTimer();	
}

function startTime() {
	if (!$.cookie('gamesecond')) {
		$('#game-time').html('Тренировка');
		return;
	}
	
    var hours = +($.cookie('gamehour'));
    var minutes = +($.cookie('gameminute'));
    var seconds = +($.cookie('gamesecond')) + 1;
	
	if (!seconds) {
		$('#game-time').html('Тренировка');
		return;
	}
	
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
    setTimeout(startTime, 1000);
}

function startTimer() {
	var timerName = gametimer || 'game-timer';
	
//	console.log($.cookie());
	
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
		console.log(timerhandler);
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