function startTime() {
    var hours = gamehour;
    var minutes = gameminute;
    var seconds = gamesecond + 1;
	if (seconds == 60) {
		minutes = minutes + 1;
		seconds = 0;
	}
	if (minutes == 60) {
		hours = hours + 1;
		minutes = 0;
	}
	gamehour = hours;
    gameminute = minutes;
    gamesecond = seconds;
    if (hours < 10) hours = "0" + hours;
    if (minutes < 10) minutes = "0" + minutes;
    if (seconds < 10) seconds = "0" + seconds;
    $('#game-time').html('Тренировка ' + hours + ":" + minutes + ":" + seconds);
    setTimeout(startTime, 1000);
}

function startTimer() {
	if (timerfunc === '') {
		return;
	}
	
    var minutes = timerminute;
    var seconds = timersecond - 1;
	
	if (seconds == 0 && minutes == 0) {
		$('#game-timer').empty();
		window[timerfunc]();
		return;
	} 
	
	if (seconds == 0) {
		minutes = minutes - 1;
		seconds = 59;
	}
	
    timerminute = minutes;
    timersecond = seconds;
    if (minutes < 10) minutes = "0" + minutes;
    if (seconds < 10) seconds = "0" + seconds;
    $('#game-timer').html( minutes + ":" + seconds );
	setTimeout(startTimer, 1000);
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
		console.log($( this ).attr('data-card-id'))
		cards.push($( this ).attr('data-card-id'));
	});
	$.post('/training/change', {cards: cards},
	function(data){
		if (data.ok) {
			window.location.reload();
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
			} else if ($(target).attr('data-move') == 'update') {
				onClickUpdate();
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

function onClickUpdate() {
	$.post('/training/update', {},
	function(data){
		if (data.ok) {
			window.location.reload();
		}
	}, "json");
}

function makeBet(bet) {
	var chips = +($('#chips').text());
	
	$.post('/training/bet', {bet: bet},
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

function enableButtons() {
	switch (gamestate) {
		case 2:
		case 3:
			$('.game-buttons input').prop('disabled', false);
			break;
		default:
			$('.game-buttons input').prop('disabled', true);
	}
}

function startTraining() {
	if (gamestate == 1) {
		$('.gamer-cards .card').css('cursor', 'pointer');
		$('#gamer-cards').on('click', chooseCard);
	} else {
		$('.gamer-cards .card').css('cursor', 'default');
		$('#gamer-cards').off('click', chooseCard);
	}
	$('.game-change').on('click', changeCard);
	$('.game-buttons').on('click', makeMove);
	enableButtons();
}

$(document).ready(function(){
	startTime();
	startTimer();
	
	if (gametraining) {
		startTraining();
	}
	
});