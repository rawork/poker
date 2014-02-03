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

var eventtimerId = null;

function onShowQuestion() {
	$.post('/training/question', {},
	function(data){
		if (data.ok) {
			$('#table').html(data.board);
			startTimer();
		}
	}, "json");
}

function onClickAnswer(event) {
	$('.question-footer .btn').prop('disabled', true);
	var n = $('.question-answer i.active').attr('data-answer-id');
	stopTimer();
	$.post('/training/answer', {answer: n},
	function(data){
		if (data.ok) {
			$('#table').html(data.board);
			$('#chips').html(data.chips);
			$('#gamer-cards').html(data.cards);
			enableButtons();
			startTimer();
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onClickNoAnswer(event) {
	$('.question-footer .btn').prop('disabled', true);
	stopTimer();
	$.post('/training/answer', {answer: 0},
	function(data){
		if (data.ok) {
			$('#table').html(data.board);
			$('#chips').html(data.chips);
			enableButtons();
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
	onBuy(n);
}

function onClickNoBuy() {
	onBuy(0);
}

function onBuy(n) {
	$.post('/training/buy', {answer: n},
	function(data){
		if (data.ok) {
			$('#table').html(data.board);
			$('#chips').html(data.chips);
			if (data.last) {
				for (i in data.cards0) {
					$('.gamer-cards[data-bot-id='+i+']').html(data.cards0[i]);
				}
				$('#gamer-cards').html(data.cards);
				startTimer();
			}
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onChooseCard(event){
	var n = $('.card.active').length;
	if (n < 2 || $(this).hasClass('active')) {
		$(this).toggleClass('active');
	}
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

function onClickNext() {
	$.post('/training/next', {},
	function(data){
		if (data.ok) {
			$('#table').html(data.board);
			for (i in data.cards0) {
				$('.gamer-cards[data-bot-id='+i+']').html(data.cards0[i]);
			}
			$('#gamer-cards').html(data.cards);
			startTimer();
		} else {
//			window.location.reload();
		}
	}, "json");
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
	if (bet < +$('#min-bet').html()) {
		alert('Ставка должна быть не меньше минимальной ставки за столом');
		return;
	}
	onBet(bet);
}

function onClickChange() {
	var n = $('.card.active').length;
	if (!n) {
		return;
	}
	$('.game-message .btn').prop('disabled', true);
	$('#gamer-cards .card').css('cursor', 'default');
	stopTimer();
	var cards = [];
	$('.card.active').each(function( index ) {
		cards.push($( this ).attr('data-card-id'));
	});
	
	$.post('/training/change', {cards: cards},
	function(data){
		if (data.ok) {
			onShowQuestion();
		}
	}, "json");
}

function onClickCheck() {
	var chips = +($('#chips').text());
	var bet = 0;
	if (gamemaxbet > gamerbet) {
		needbet = gamemaxbet - gamerbet;
		bet = chips > needbet ? needbet : chips;
	}
	onBet(bet);
}

function onClickFold() {
	$.post('/training/fold', {},
	function(data){
		if (data.ok) {
			enableButtons();
			$('#table').html(data.board);
			for (i in data.chips0) {
				$('.gamer-chips[data-bot-id='+i+']').html(data.chips0[i]);
			}
			for (i in data.cards0) {
				$('.gamer-cards[data-bot-id='+i+']').html(data.cards0[i]);
			}
			$('#gamer-cards').empty();
			$('.gamer-container').append(data.winner);
			$('.game-main-bank').html(data.bank);
			startTimer();
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onClickNoChange() {
	$('#gamer-cards .card').removeClass('choose');
	$('#gamer-cards .card').removeClass('active');
	$('.game-message .btn').prop('disabled', true);
	stopTimer();
	$.post('/training/nochange', {},
	function(data){
		if (data.ok) {
			$('#table').html(data.board);
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
			$('.game-start button').on('click', onClickStart);
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onClickVaBank() {
	$('#input_bet').val(+$('#chips').text());
	var chips = +($('#input_bet').val());
	if (chips < +$('#min-bet').html()) {
		alert('Ставка должна быть не меньше минимальной ставки за столом');
		return;
	}
	onBet(chips);
}

function onBet(chips) {
	$('#input_bet').val($('#min-bet').html());
	$.post('/training/bet', {chips: chips},
	function(data){
		if (data.ok) {
			$('#table').html(data.board);
			$('#chips').html(data.chips);
			$('#gamer-cards').html(data.cards);
			$('.game-main-bank').html(data.bank);
			$('.gamer-container').append(data.winner);
			for (i in data.chips0) {
				$('.gamer-chips[data-bot-id='+i+']').html(data.chips0[i]);
			}
			for (i in data.cards0) {
				$('.gamer-cards[data-bot-id='+i+']').html(data.cards0[i]);
			}
			enableButtons();
			startTimer();
		} else {
			window.location.reload();
		}
	}, "json");
}

function onDistributeWin() {
	$.post('/training/win', {},
	function(data){
		if (data.ok) {
			$('.gamer-cards').empty();
			$('#gamer-cards').empty();
			$('.game-winner').remove();
			for (i in data.chips0) {
				$('.gamer-chips[data-bot-id='+i+']').html(data.chips0[i]);
			}
			$('.game-main-bank').html(data.bank);
			$('#chips').html(data.chips);
			$('#table').html(data.board);
			if (data.state == 1) {
				$('#gamer-cards .card').css('cursor', 'pointer');
				for (i in data.cards0) {
					$('.gamer-cards[data-bot-id='+i+']').html(data.cards0[i]);
				}
				$('#gamer-cards').html(data.cards);
			} else if (data.state == 6) {
				$('.gamer-bot').remove();
				$('.gamer-cards').empty();
				$('.game-min-bet').empty();
				$('.game-main-bank').empty();
				stopTimer();
			}
			startTimer();
		}
	}, "json");
}

function onCheckMinBet() {
	$.post('/training/minbet', {},
	function(data){
		if (data.ok) {
			$('#min-bet').html(data.minbet);
			$('#input_bet').val(data.minbet);
		}
	}, "json");
}

function enableButtons() {
	var state = +$.cookie('gamestate');
	switch (state) {
		case 2:
			$('.game-buttons button').prop('disabled', false);
			$('.game-buttons button[data-move=check]').prop('disabled', true);
			break;
		case 3:
			$('.game-buttons button').prop('disabled', false);
			break;
		default:
			$('.game-buttons button').prop('disabled', true);
	}
	$('.game-buttons button[data-move=new]').prop('disabled', false);
}

function initTraining() {
	setInterval(startTime, 990);
	$(document).on('click', 'button[data-action=change]', onClickChange);
	$(document).on('click', 'button[data-action=nochange]', onClickNoChange);
	$(document).on('click', '.choose', onChooseCard);
	$(document).on('click', 'button[data-action=buying]', onClickBuyAnswer);
	$(document).on('click', 'a[data-action=nobuying]', onClickNoBuy);
	$(document).on('click', '.question-answer', onChooseAnswer);
	$(document).on('click', 'button[data-action=start]', onClickStart);
	$(document).on('click', 'button[data-action=stop]', onClickStop); 
	$(document).on('click', 'button[data-action=answer]', onClickAnswer);
	$(document).on('click', 'button[data-move=vabank]', onClickVaBank);
	$(document).on('click', 'button[data-move=bet]', onClickBet);
	$(document).on('click', 'button[data-move=check]', onClickCheck);
	$(document).on('click', 'button[data-move=fold]', onClickFold);
	$(document).on('click', 'button[data-move=new]', onClickStart);
	$('#gamer-cards .card').css('cursor', 'pointer');
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
	if (eventtimerId) {
		clearTimeout(eventtimerId);
	}
	var timerName = $.cookie('gametimer') || 'game-timer';
	
//	console.log($.cookie());
	
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
		stopTimer();
		window[timerhandler]();
		return;
	}
	
	$.cookie('timerminute', minutes);
    $.cookie('timersecond', seconds);
	
	if (minutes < 10) minutes = "0" + minutes;
    if (seconds < 10) seconds = "0" + seconds;
	
	$('#'+ timerName).html( minutes + ":" + seconds );
	
	eventtimerId = setTimeout(startTimer, 950);
}

function stopTimer() {
	$('.gamer-card-zoom').hide();
	clearTimeout(eventtimerId);
	$.removeCookie('timerhandler');
	$.removeCookie('timerminute');
	$.removeCookie('timersecond');
}

$(document).ready(function(){
	initTraining();
});



