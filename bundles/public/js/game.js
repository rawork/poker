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

var gameTimerId;
var gameTimeId;
var gameUpdateId;

function onUpdate() {
	var state = +$.cookie('gamestate');
	$.post('/game/update', {},
	function(data){
		if (data.ok) {
			if (state > 0 && +data.state == 0 ) {
				console.log('sdfsdfsdfds');
				window.location.reload();
			}
			if (data.table) {
				$('#table').html(data.table);
			}
			if (+data.state != 1 || $('#gamer-cards').html() == ''){
				$('#gamer-cards').html(data.cards);
			}
			$('#bank').html(data.bank);
			$('#bets').html(data.bets);
			$('#chips').html(data.chips);
			$('#bet').html(data.bet);
			$('.game-winner').remove();
			if (data.winner){
				$('.gamer-container').append(data.winner);
			}
			$('.gamer-hint').remove();
			if (data.hint){
				$('.gamer-container').append(data.hint);
			}
			
			var bet = +$('#input_bet').val();
			$('#min-bet').html(data.minbet);
			if (bet == NaN || data.minbet > bet) {
				$('#input_bet').val(data.minbet);
			}
			
			$.cookie('gamerstate', +data.gamerstate, {path: '/'});
			$.cookie('gamestate', +data.state, {path: '/'});
			$.cookie('gamemaxbet', +data.maxbet, {path: '/'});
			$.cookie('gamermover', +data.mover, {path: '/'});
			enableButtons();
			updateRivals(data.rivals);
			if (+data.state == 6) {
				$('.gamer-cards').empty();
				$('.game-min-bet').empty();
				$('.game-main-banks').empty();
				stopTime();
			} else if (+data.state > 0) {
//				gameTimerId = setInterval(startTimer, 1000);
				startTimer();
			}
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

function onClickChange() {
	var n = $('.card.active').length;
	if (!n) {
		return;
	}
	stopTimer();
	var card_no = $('.card.active').attr('data-card-id');
	$('#gamer-cards .card').removeClass('choose active');
	$('.game-message .btn').prop('disabled', true);
	
	$.post('/game/change', {card_no: card_no},
	function(data){
		if (data.ok) {
			$('#table').html(data.table);
			gameTimerId = setInterval(startTimer, 1000);
		}
	}, "json");
}

function onClickNoChange() {
	stopTimer();
	$('#gamer-cards .card').removeClass('choose active');
	$('.game-message .btn').prop('disabled', true);
	$.post('/game/nochange', {},
	function(data){
		if (data.ok) {
			$('#table').html(data.table);
			if (data.hint){
				$('.gamer-container').append(data.hint);
			}
			gameTimerId = setInterval(startTimer, 1000);
		}  else {
//			window.location.reload();
		}
	}, "json");
}

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
	stopTimer();
	$('.question-footer .btn').prop('disabled', true);
	$.post('/game/answer', {answer: n},
	function(data){
		if (data.ok) {
			$('#table').html(data.table);
			$('#chips').html(data.chips);
			$('#gamer-cards').html(data.cards);
			if (data.hint){
				$('.gamer-container').append(data.hint);
			}
			gameTimerId = setInterval(startTimer, 1000);
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onClickBet() {
	var minbet = +$('#min-bet').html();
	var maxbet = +$.cookie('gamemaxbet');
	var chips = +$('#chips').html();
	var existbet = +($('#bet').html());
	var bet = +($('#input_bet').val());
	if ( bet > chips ) {
		alert('Ставка  больше, чем у вас есть фишек');
		return;
	}
	if (maxbet > existbet) {
		if ((maxbet - existbet) > bet) {
			alert('Ставка должна быть не меньше ' + ((maxbet - existbet)*2));
			return;
		}
	} else {
		if (minbet > bet) {
			alert('Ставка должна быть не меньше минимальной ставки');
			return;
		}
	}
	onBet(bet);
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
	$.post('/game/bet', {chips: chips},
	function(data){
		if (data.ok) {
			$('#table').html(data.board);
			$('#bet').html(data.bet);
			$('#chips').html(data.chips);
			$('#gamer-cards').html(data.cards);
			$('#bank').html(data.bank);
			$('#bets').html(data.bets);
			updateRivals(data.rivals);
			gameTimerId = setInterval(startTimer, 1000);
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onClickCheck() {
	enableButtons(4);
	$('.gamer-card-zoom').hide();
	$('.gamer-hint').remove();
	$('#input_bet').val($('#min-bet').html());
	$.post('/game/check', {},
	function(data){
		if (data.ok) {
			$('#table').html(data.board);
			$('#bet').html(data.bet);
			$('#chips').html(data.chips);
			$('#gamer-cards').html(data.cards);
			$('#bank').html(data.bank);
			$('#bets').html(data.bets);
			$('.gamer-container').append(data.winner);
			$('.gamer-container').append(data.hint);
			updateRivals(data.rivals);
			gameTimerId = setInterval(startTimer, 1000);
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onFold() {
	$('.gamer-card-zoom').hide();
	$('.gamer-hint').remove();
	enableButtons(4);
	$.post('/game/fold', {},
	function(data){
		if (data.ok) {
			updateRivals(data.rivals);
			$('#table').html(data.table);
			$('#gamer-cards').html(data.cards);
			$('.gamer-hint').remove();
			gameTimerId = setInterval(startTimer, 1000);
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onDistribute() {
	$('.game-winner').remove();
	$('.gamer-hint').remove();
	stopTimer();
	$.post('/game/distribute', {},
	function(data){
		if (data.ok) {
			$('#table').html(data.table);
			$('#chips').html(data.chips);
			$('#bet').html(data.bet);
			$('#bank').html(data.bank);
			$('#bank').html(data.bank);
			$('#gamer-cards').empty();
			updateRivals(data.rivals);
			if (data.state == 1) {
				$('#gamer-cards').html(data.cards);
			} else if (data.state == 6) {
				$('.gamer-cards').empty();
				$('.game-min-bet').empty();
				$('.game-main-bank').empty();
			}
			enableButtons();
			gameTimerId = setInterval(startTimer, 1000);
		} else {
			enableButtons();
			gameTimerId = setInterval(startTimer, 1000);
		}
	}, "json");
}

function onShowBuy() {
	stopTimer();
	$.post('/game/prebuy', {},
	function(data){
		if (data.ok) {
			$('#table').html(data.table);
			if (data.state == 1) {
				$('#gamer-cards').html(data.cards);
			} else if (data.state == 6) {
				$('.gamer-cards').empty();
				$('.game-min-bet').empty();
				$('.game-main-bank').empty();
			}
			updateRivals(data.rivals);
			enableButtons();
			gameTimerId = setInterval(startTimer, 1000);
		}
	}, "json");
}

function onBuy() {
	console.log('onBuy');
	$('button[data-move=buy]').prop('disabled', true);
	$.post('/game/buy', {},
	function(data){
		if (data.ok) {
			$('#table').html(data.table);
			gameTimerId = setInterval(startTimer, 1000);
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
	$.post('/game/buyanswer', {answer: n},
	function(data){
		if (data.ok) {
			$('#table').html(data.table);
			$('#chips').html(data.chips);
			enableButtons();
			gameTimerId = setInterval(startTimer, 1000);
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onEndRound() {
	$('button[data-move=buy]').prop('disabled', true);
	$.post('/game/endround', {},
	function(data){
		if (data.ok) {
			$('#table').html(data.table);
			gameTimerId = setInterval(startTimer, 1000);
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onNext() {
	stopTimer();
	$.post('/game/next', {},
	function(data){
		if (data.ok) {
			if (data.table) {
				$('#table').html(data.table);
			}
			if (data.cards){
				$('#gamer-cards').html(data.cards);
			}
			$('.game-winner').remove();
			$('.gamer-hint').remove();
			enableButtons();
			updateRivals(data.rivals);
			gameTimerId = setInterval(startTimer, 1000);
		} else {
			gameTimerId = setInterval(startTimer, 1000);
		}
	}, "json");
}

function onStart() {
	$.post('/game/start', {},
	function(data){
		if (data.ok) {
			$.cookie('gamestate', data.state, {path: '/'});
			window.location.reload();
		}
	}, "json");
}

function onToggleOut() {
	var state = $('.gamer-out').hasClass('closed') ? 3 : 1;
	$.post('/game/out', {state: state},
	function(data){
		if (data.ok) {
			$.cookie('gamerstate', state, {path: '/'});
			if (data.state == 1) {
				$('.gamer-out').addClass('closed');
				$('a[data-action=out]').html('Вне игры');
				enableButtons();
			} else {
				$('.gamer-out').removeClass('closed');
				$('a[data-action=out]').html('В игре');
				enableButtons();
			}
		}
	}, "json");
}

function stopTime() {
	var highestIntervalId = setInterval(";");
	for (var i = 0 ; i <= highestIntervalId ; i++) {
		clearInterval(i); 
	}
}

function startTime() {
	if (!$.cookie('gamefromtime')) {
		$('#game-time').html($.cookie('gamename'));
		return;
	}
	
//	console.log($.cookie());
	
	var now = new Date();
	var fromtime = new Date($.cookie('gamefromtime'));
	if (fromtime > now) {
		$('#game-time').html($.cookie('gamename'));
		return;
	}
	
	var timer = now.getTime() - fromtime.getTime();

	var seconds = timer % 3600000;
    var hours = parseInt((timer - seconds) / 3600000);
    var minutes = parseInt((seconds - seconds % 60000) / 60000);
    var seconds = parseInt((seconds - minutes * 60000) / 1000);
	
	if (hours < 10) hours = "0" + hours;
    if (minutes < 10) minutes = "0" + minutes;
    if (seconds < 10) seconds = "0" + seconds;
    $('#game-time').html( $.cookie('gamename') + ' ' + hours + ":" + minutes + ":" + seconds );
}

function startTimer() {
	var timerName = $.cookie('timerholder') || 'game-timer';
	
	if (!$.cookie('timerhandler')) {
		$('#' + timerName).empty();
		return;
	}
	
	var now = new Date();
	var timerstop = new Date($.cookie('timerstop'));
	if (timerstop < now) {
		$('#'+ timerName).html( "00:00:00" );
		var timerhandler = $.cookie('timerhandler');
		console.log(timerhandler);
		$.cookie('timerhandler', '', {path: '/'});
		stopTimer();
		if (timerhandler !== 'null') {
			window[timerhandler]();
		}
		return;
	}
	
    var timer = timerstop.getTime() - now.getTime();

	var seconds = timer % 3600000;
    var hours = parseInt((timer - seconds) / 3600000);
    var minutes = parseInt((seconds - seconds % 60000) / 60000);
    var seconds = parseInt((seconds - minutes * 60000) / 1000);
	
	if (hours < 10) hours = "0" + hours;
	if (minutes < 10) minutes = "0" + minutes;
    if (seconds < 10) seconds = "0" + seconds;
	
	$('#'+ timerName).html( hours + ":" + minutes + ":" + seconds );
}

function stopTimer() {
	$('.gamer-card-zoom').hide();
	$.removeCookie('timerhandler', {path: '/'});
	$.removeCookie('timerholder', {path: '/'});
	$.removeCookie('timerstop', {path: '/'});
	clearInterval(gameTimerId); 
	gameTimerId = null;
}

function updateRivals(rivals) {
	for (var i in rivals) {
		if (rivals[i].chips !== undefined) {
			$('.gamer-chips[data-bot-id='+i+']').html(rivals[i].chips);
		}
		if (rivals[i].cards !== undefined) {
			$('.gamer-cards[data-bot-id='+i+']').html(rivals[i].cards);
		}
		if (rivals[i].active) {
			$('.gamer-status[data-bot-id='+i+']').removeClass('notready').html('Активен');
		} else {
			$('.gamer-status[data-bot-id='+i+']').addClass('notready').html('Не активен');
		}
		if (rivals[i].bet !== undefined) {
			$('.gamer-bet[data-bot-id='+i+']').html(rivals[i].bet);
		}
	}
}

function onChangeBetInput() {
	var minbet = +$('#min-bet').html();
	var maxbet = +$.cookie('gamemaxbet');
	var bet    = +$('#bet').html();
	var currentBet = +this.val();
}


function enableButtons(state) {
	$('.game-buttons button').prop('disabled', true);
	state = state || +$.cookie('gamestate');
	var gamerstate = +$.cookie('gamerstate');
	var gamermover = +$.cookie('gamermover');
	var minbet = +$('#min-bet').html();
	var maxbet = +$.cookie('gamemaxbet');
	var bet    = +$('#bet').html();
	var chips  = +$('#chips').html();
	switch (state) {
		case 2:
		case 3:
			$('.game-buttons button[data-action=bet]').html('&nbsp;');
			$('.game-buttons button[data-action=check]').html('&nbsp;');
			if (gamermover == 1 && gamerstate == 1) {
				$('.game-buttons button[data-action=fold]').prop('disabled', false);
				$('.game-buttons button[data-action=allin]').prop('disabled', false);
				if (minbet > chips || (maxbet - bet) > chips) {
					
				} else {
					if (bet == 0 && maxbet == 0) {
						$('.game-buttons button[data-action=bet]').html('Ставка('+ minbet +')');
						$('.game-buttons button[data-action=check]').html('Чек');
						$('.game-buttons button[data-action=bet]').prop('disabled', false);
					} else if ( maxbet > bet ) {
						if ($('#input_bet').val() < (maxbet - bet)*2) {
							$('#input_bet').val((maxbet - bet)*2);
						}
						$('.game-buttons button[data-action=bet]').html('Рейз ('+ ((maxbet - bet)*2) +')');
						$('.game-buttons button[data-action=check]').html('Колл ('+ (maxbet - bet) +')');
						$('.game-buttons button[data-action=check]').prop('disabled', false);
						$('.game-buttons button[data-action=bet]').prop('disabled', false);
					} else {
						$('#input_bet').val(minbet);
						$('.game-buttons button[data-action=bet]').html('Ставка('+ minbet +')');
						$('.game-buttons button[data-action=check]').html('Чек');
						$('.game-buttons button[data-action=check]').prop('disabled', false);
						$('.game-buttons button[data-action=bet]').prop('disabled', false);
					}
				}
			}
			break;	
		case 5:
			$('.game-buttons button[data-action=buy]').prop('disabled', false);
			break;
		default:
			$('.game-buttons button[data-action=bet]').html('&nbsp;');
			$('.game-buttons button[data-action=check]').html('&nbsp;');
	}
	$('.game-buttons button[data-action=out]').prop('disabled', state == 6);
}

function initGame() {
	stopTime();
	setInterval(startTime, 1000);
	$(document).on('click', '.choose', onChooseCard);
//	$(document).on('click', 'a[data-action=nobuyanswer]',    onClickNoBuyAnswer);
	$(document).on('click', '.question-answer',              onChooseAnswer);
	$(document).on('click', 'button[data-action=answer]',    onClickAnswer);
	$(document).on('click', 'button[data-action=change]',    onClickChange);
	$(document).on('click', 'button[data-action=nochange]',  onClickNoChange);
	$(document).on('click', 'button[data-action=buyanswer]', onClickBuyAnswer);
	$(document).on('click', 'button[data-action=allin]',     onClickAllIn);
	$(document).on('click', 'button[data-action=bet]',       onClickBet);
	$(document).on('click', 'button[data-action=check]',     onClickCheck);
	$(document).on('click', 'button[data-action=fold]',      onFold);
	$(document).on('click', 'button[data-action=buy]',       onBuy);
	$(document).on('click', 'button[data-action=out]',       onToggleOut);
	$(document).on('change', '#input_bet', onChangeBetInput);
	enableButtons();
	setInterval(startTimer, 1000);
//	setInterval(onUpdate, 3000);
	$('.gamer-container').zoomcard();
	$('.game-board-container').preloadImages(cardimages);
	console.log($.cookie('timerhandler'));
}

$(document).ready(function(){
	initGame();
});
