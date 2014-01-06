function startTime() {
	if (gamehour === -1) {
		$('#game-time').html('Тренировочная игра');
		return;
	}
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
    $('#game-time').html(hours + ":" + minutes + ":" + seconds);
    setTimeout(startTime, 1000);
}

function vabank() {
	
}

function bet() {
	
}

function check() {
	
}

function fold() {
	
}

function status() {
	
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
	})
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
	var chips = +($('#input_bet').val());
	var target = event.target || event.srcElement;

	while(target != this) { 
		if ($(target).hasClass('btn')) { 
			if ($(target).hasClass('btn-warning')) {
				onClickVaBank();
			} else if (chips > 0 && $(target).hasClass('btn-primary')) {
				onClickBet();
			} else if ($(target).hasClass('btn-success')) {
				onClickCheck();
			} else if ($(target).hasClass('btn-danger')) {
				onClickFold();
			}
			break;
		}
		target = target.parentNode;
	}
}

function onClickVaBank() {
	console.log('vabank');
	return;
}

function onClickBet() {
	console.log('bet');
	return;
}

function onClickCheck() {
	console.log('check');
	return;
}

function onClickFold() {
	$.post('/training/fold', {},
	function(data){
		if (data.ok) {
			window.location.reload();
		}
	}, "json");
}

function startTraining() {
	$('#gamer-cards').on('click', chooseCard);
	$('.gamer-change').on('click', changeCard);
	$('.game-buttons').on('click', makeMove);
}

$(document).ready(function(){
	startTime();
	
	if (gametraining) {
		startTraining();
	}
	
});