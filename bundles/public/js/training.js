(function( $ ) {
	$.fn.zoomcard = function(options) {

		var $this = this,
			defaults = {}, 
			options = $.extend(defaults, options);

		this.on('mouseover', '#gamer-cards .card', function(){
			var name = $(this).attr('data-card-name');
			var src = $(this).children('img').attr('src');
			if (src == '/bundles/public/img/shirt.png') {
				return;
			}
			var div = $this.children('div[data-name='+name+']');
			if (div.length == 0) {
				div = $('<div><img src="'+src+'"></div>').addClass('gamer-card-zoom').attr('data-name', name);
				$this.append(div);
			}
			div.show();
		});
		
		this.on('mouseout', '#gamer-cards .card', function() {
			var name = $(this).attr('data-card-name');
			$this.children('.gamer-card-zoom[data-name='+name+']').hide();
		});

	};
	
	$.fn.preloadImages = function () {
		if (typeof arguments[arguments.length - 1] == 'function') {
			var callback = arguments[arguments.length - 1];
		} else {
			var callback = false;
		}
		if (typeof arguments[0] == 'object') {
			var images = arguments[0];
			var n = images.length;
		} else {
			var images = arguments;
			var n = images.length - 1;
		}
		var not_loaded = n;
		for (var i = 0; i < n; i++) {
			jQuery(new Image()).attr('src', '/bundles/public/img/cards/'+images[i]+'.png').load(function() {
				if (--not_loaded < 1 && typeof callback == 'function') {
					callback();
				}
			});
		}
	};
})(jQuery);

var gametimer;

function onClickAnswer() {
	var n = $('.question-answer i.active').attr('data-answer-id');
	if (!n) {
		return;
	}
	onAnswer(n);
}

function onNoAnswer() {
	onAnswer(0);
}

function onAnswer(n) {
	$('.question-footer .btn').prop('disabled', true);
	stopTimer();
	$.post('/training/answer', {answer: n},
	function(data){
		if (data.ok) {
			$('#table').html(data.board);
			$('#chips').html(data.chips);
			$('#gamer-cards').html(data.cards);
			$('.gamer-container').append(data.hint);
			enableButtons();
			startTimer();
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onBuy() {
	$('button[data-move=buy]').prop('disabled', true);
	$.post('/training/buy', {},
	function(data){
		if (data.ok) {
			$('#table').html(data.board);
			updateBots(data.bots);
			$('#gamer-cards').html(data.cards);
			startTimer();
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onClickBuyAnswer() {
	var n = $('.question-answer i.active').attr('data-answer-id');
	if (!n) {
		return;
	}
	onBuyAnswer(n);
}

function onClickNoBuyAnswer() {
	onBuyAnswer(0);
}

function onBuyAnswer(n) {
	$('.question-footer .btn').prop('disabled', true);
	$.post('/training/buyanswer', {answer: n},
	function(data){
		if (data.ok) {
			$('#table').html(data.board);
			$('#chips').html(data.chips);
			if (data.last) {
				updateBots(data.bots);
				$('#gamer-cards').html(data.cards);
				startTimer();
			} else {
				$('.question-footer .btn').prop('disabled', false);
			}
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onChooseCard(){
	$(this).siblings('div.card').removeClass('active');
	$(this).addClass('active');
}

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

function onNext() {
	$('button[data-move=buy]').prop('disabled', true);
	$.post('/training/next', {},
	function(data){
		if (data.ok) {
			$('#table').html(data.board);
			$('#gamer-cards').html(data.cards);
			updateBots(data.bots);
			startTimer();
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onEnd() {
	onEndRound();
}

function onEndRound() {
	$('button[data-move=buy]').prop('disabled', true);
	$('.game-message .btn').prop('disabled', true);
	$.post('/training/endround', {},
	function(data){
		if (data.ok) {
			$('#table').html(data.board);
			startTimer();
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onClickBet() {
	var minbet = $('#min-bet').html();
	var maxbet = $.cookie('gamemaxbet');
	var chips = $('#chips').html();
	var bet = +($('#input_bet').val());
	if ( bet > chips ) {
		alert('Ставка  больше, чем у вас есть');
		return;
	}
	if (minbet > bet) {
		alert('Ставка должна быть не меньше минимальной ставки за столом');
		return;
	}
	if (maxbet > bet) {
		alert('Ставка должна быть не меньше ' + maxbet);
		return;
	}
	onBet(bet);
}

function onClickChange() {
	var n = $('.card.active').length;
	if (!n) {
		return;
	}
	stopTimer();
	var card_no = $('.card.active').attr('data-card-id');
	$('#gamer-cards .card').removeClass('choose active');
	$('.game-message .btn').prop('disabled', true);
	
	$.post('/training/change', {card_no: card_no},
	function(data){
		if (data.ok) {
			$('#table').html(data.board);
			startTimer();
		}
	}, "json");
}

function onClickCheck() {
	enableButtons(4);
	$('.gamer-card-zoom').hide();
	$('.gamer-hint').remove();
	$('#input_bet').val($('#min-bet').html());
	$.post('/training/check', {},
	function(data){
		if (data.ok) {
			$('#table').html(data.board);
			$('#bet').html(data.bet);
			$('#chips').html(data.chips);
			$('#gamer-cards').html(data.cards);
			$('.game-main-bank').html(data.bank);
			$('.gamer-container').append(data.winner);
			$('.gamer-container').append(data.hint);
			updateBots(data.bots);
			enableButtons();
			startTimer();
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onFold() {
	$('.gamer-card-zoom').hide();
	$('.gamer-hint').remove();
	enableButtons(4);
	$.post('/training/fold', {},
	function(data){
		if (data.ok) {
			updateBots(data.bots);
			$('#table').html(data.board);
			$('#gamer-cards').html(data.cards);
			$('.gamer-container').append(data.winner);
			$('.game-main-bank').html(data.bank);
			startTimer();
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onClickNoChange() {
	$('#gamer-cards .card').removeClass('choose active');
	$('.game-message .btn').prop('disabled', true);
	stopTimer();
	$.post('/training/nochange', {},
	function(data){
		if (data.ok) {
			$('#table').html(data.board);
			$('#gamer-cards').html(data.cards);
			$('.gamer-container').append(data.hint);
			enableButtons();
			startTimer();
		}  else {
//			window.location.reload();
		}
	}, "json");
}

function onClickStart() {
	$.post('/training/start', {},
	function(data){
		if (data.ok) {
			startTime();
			$('.gamer-container').empty().append(data.bots).append(data.gamer);
			$('.game-min-bet').html(data.minbet);
			$('.game-main-bank').html(data.bank);
			$('#table').html(data.board);
			enableButtons();
			startTimer();
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onClickStop() {
	$.post('/training/stop', {},
	function(data){
		if (data.ok) {
			$('.gamer-container').empty();
			$('.game-min-bet').empty();
			$('.game-main-bank').empty();
			$('#table').html(data.board);
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onClickAllIn() {
	$('#input_bet').val(+$('#chips').text());
	var chips = +($('#input_bet').val());
	onBet(chips);
}

function onBet(chips) {
	enableButtons(4);
	$('.gamer-card-zoom').hide();
	$('.gamer-hint').remove();
	$('#input_bet').val($('#min-bet').html());
	$.post('/training/bet', {chips: chips},
	function(data){
		if (data.ok) {
			$('#table').html(data.board);
			$('#bet').html(data.bet);
			$('#chips').html(data.chips);
			$('#gamer-cards').html(data.cards);
			$('.game-main-bank').html(data.bank);
			$('.gamer-container').append(data.winner);
			$('.gamer-container').append(data.hint);
			updateBots(data.bots);
			enableButtons();
			startTimer();
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onDistribute() {
	$.post('/training/distribute', {},
	function(data){
		if (data.ok) {
			updateBots(data.bots);
			$('#gamer-cards').empty();
			$('.game-winner').remove();
			$('.gamer-hint').remove();
			$('.game-main-bank').html(data.bank);
			$('#chips').html(data.chips);
			$('#bet').html(data.bet);
			$('#table').html(data.board);
			if (data.state == 1) {
				$('#gamer-cards').html(data.cards);
			} else if (data.state == 6) {
				$('.gamer-bot').remove();
				$('.gamer-cards').empty();
				$('.game-min-bet').empty();
				$('.game-main-bank').empty();
			}
			enableButtons();
			startTimer();
		}
	}, "json");
}

function onShowPrebuy() {
	stopTimer();
	$.post('/training/prebuy', {},
	function(data){
		if (data.ok) {
			updateBots(data.bots);
			$('#gamer-cards').empty();
			$('.game-winner').remove();
			$('.gamer-hint').remove();
			$('.game-main-bank').html(data.bank);
			$('#chips').html(data.chips);
			$('#bet').html(data.bet);
			$('#table').html(data.board);
			if (data.state == 1) {
				$('#gamer-cards').html(data.cards);
			} else if (data.state == 6) {
				$('.gamer-bot').remove();
				$('.gamer-cards').empty();
				$('.game-min-bet').empty();
				$('.game-main-bank').empty();
			}
			enableButtons();
			startTimer();
		}
	}, "json");
}

function onCheckMinBet() {
	$.post('/training/minbet', {},
	function(data){
		if (data.ok) {
			var bet = +$('#input_bet').val();
			$('#min-bet').html(data.minbet);
			if (data.minbet > bet) {
				$('#input_bet').val(data.minbet);
			}
		}
	}, "json");
}

function updateBots(bots) {
	for (i in bots) {
		if (bots[i].chips !== undefined) {
			$('.gamer-chips[data-bot-id='+i+']').html(bots[i].chips);
		}
		if (bots[i].cards !== undefined) {
			$('.gamer-cards[data-bot-id='+i+']').html(bots[i].cards);
		}
		if (bots[i].active) {
			$('.gamer-status[data-bot-id='+i+']').removeClass('notready').html('Активен');
		} else {
			$('.gamer-status[data-bot-id='+i+']').addClass('notready').html('Не активен');
		}
		if (bots[i].bet !== undefined) {
			$('.gamer-bet[data-bot-id='+i+']').html(bots[i].bet);
		}
	}
}

function enableButtons(state) {
	var state = state || + $.cookie('gamestate');
	switch (state) {
		case 2:
			$('.game-buttons button').prop('disabled', false);
			$('.game-buttons button[data-action=check]').prop('disabled', true);
			$('.game-buttons button[data-action=buy]').prop('disabled', true);
			var minbet = +$('#min-bet').html();
			var maxbet = +$.cookie('gamemaxbet');
			var chips = +$('#chips').html();
			if (minbet > chips || maxbet > chips) {
				$('.game-buttons button[data-action=bet]').prop('disabled', true);
			}
			break;
		case 3:
			$('.game-buttons button').prop('disabled', false);
			$('.game-buttons button[data-action=buy]').prop('disabled', true);
			var minbet = +$('#min-bet').html();
			var maxbet = +$.cookie('gamemaxbet');
			var chips = +$('#chips').html();
			if (minbet > chips || maxbet > chips) {
				$('.game-buttons button[data-action=check]').prop('disabled', true);
				$('.game-buttons button[data-action=bet]').prop('disabled', true);
			}
			break;
		case 42:
			$('.game-buttons button').prop('disabled', true);
			$('.game-buttons button[data-action=buy]').prop('disabled', false);
			break;
		default:
			$('.game-buttons button').prop('disabled', true);
	}
	$('.game-buttons button[data-action=new]').prop('disabled', false);
}

function initTraining() {
	setInterval(startTime, 980);
	$(document).on('click', '.choose', onChooseCard);
	$(document).on('click', 'a[data-action=nobuyanswer]',    onClickNoBuyAnswer);
	$(document).on('click', '.question-answer',              onChooseAnswer);
	$(document).on('click', 'button[data-action=change]',    onClickChange);
	$(document).on('click', 'button[data-action=nochange]',  onClickNoChange);
	$(document).on('click', 'button[data-action=buyanswer]', onClickBuyAnswer);
	$(document).on('click', 'button[data-action=start]',     onClickStart);
	$(document).on('click', 'button[data-action=stop]',      onClickStop);
	$(document).on('click', 'button[data-action=next]',      onNext);
	$(document).on('click', 'button[data-action=answer]',    onClickAnswer);
	$(document).on('click', 'button[data-action=vabank]',    onClickAllIn);
	$(document).on('click', 'button[data-action=bet]',       onClickBet);
	$(document).on('click', 'button[data-action=check]',     onClickCheck);
	$(document).on('click', 'button[data-action=fold]',      onFold);
	$(document).on('click', 'button[data-action=prebuy]',    onShowPrebuy);
	$(document).on('click', 'button[data-action=buy]',       onBuy);
	$(document).on('click', 'button[data-action=new]',       onClickStart);
	enableButtons();
	startTimer();
	setInterval(onCheckMinBet, 5000);
	$('.gamer-container').zoomcard();
	$('.game-board-container').preloadImages(cardimages);
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



