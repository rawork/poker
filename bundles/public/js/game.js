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
var eventTimers = {};

function hasEventTimer(name) {
	if (eventTimers[name] !== undefined) {
		if (Date.now() - eventTimers[name] < 2000) {
//			console.log('loop event ', name);
			return true;
		} else {
			return false;
		}
	} else {
		eventTimers[name] = Date.now();
		return false;
	}
}

function onUpdate() {
	if (hasEventTimer('update')) {
		return;
	}
	var state = +$.cookie('gamestate');
	$.post('/game/update', {},
	function(data){
		if (data.ok) {
			if (state > 0 && +data.state == 0 ) {
				window.location.reload();
			}
			if (+data.state > 0 && data.table) {
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
			$.cookie('gamemover', +data.mover, {path: '/'});
			enableButtons();
			updateRivals(data.rivals);
			if (+data.state == 6) {
				$('.gamer-cards').empty();
				$('.game-min-bet').empty();
				$('.game-main-banks').empty();
				stopTime();
			} else if (+data.state > 0) {
//				gameTimerId = setInterval(startTimer, 1000);
				gameTimerId = setInterval(startTimer, 1000);
			}
		} else {
			alert('Update error');
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
            gameupdated = data.updated;
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
            enableButtons();

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
	stopTimer();
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
	stopTimer();
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
			updateRivals(data.rivals);

			gameTimerId = setInterval(startTimer, 1000);
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onFold() {
	if (hasEventTimer('fold')) {
		return;
	}
	stopTimer();
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
	if (hasEventTimer('distribute')) {
		return;
	}
	stopTimer();
	$('.game-winner').remove();
	$('.gamer-hint').remove();
	$.post('/game/distribute', {},
	function(data){
		if (data.ok) {
			$('#table').html(data.table);
			$('#chips').html(data.chips);
			$('#bet').html(data.bet);
			$('#bank').html(data.bank);
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
			onUpdate();
		}
	}, "json");
}

function onShowBuy() {
	if (hasEventTimer('showbuy')) {
		return;
	}
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
	$('button[data-move=buy]').prop('disabled', true);
	$.post('/game/buy', {},
	function(data){
		if (data.ok) {
			$('#table').html(data.table);
            enableButtons();

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
	if (hasEventTimer('endround')) {
		return;
	}
	$('button[data-move=buy]').prop('disabled', true);
	$.post('/game/endround', {},
	function(data){
		if (data.ok) {
			$('#table').html(data.table);
            enableButtons();

			gameTimerId = setInterval(startTimer, 1000);
		} else {
//			window.location.reload();
		}
	}, "json");
}

function onNext() {
	if (hasEventTimer('next')) {
		return;
	}
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
			enableButtons();
			updateRivals(data.rivals);

			gameTimerId = setInterval(startTimer, 1000);
		} else {
			onUpdate();
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
				$('button[data-action=out]').html('Вне игры');
				enableButtons();
			} else {
				$('.gamer-out').removeClass('closed');
				$('button[data-action=out]').html('В игре');
				enableButtons();
			}
		}
	}, "json");
}

function onSync() {
    $.post('/game/sync2/'+ gameid, {},
        function(data){

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
	var fromtime = new Date();
	fromtime.setTime(+$.cookie('gamefromtime')*1000);
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
	var timerstop = new Date();
	timerstop.setTime(+$.cookie('timerstop')*1000);
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
	
    var timer = timerstop - now.getTime();

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

        if (rivals[i].active == 1 && rivals[i].state == 3) {
			$('.gamer-status[data-bot-id='+i+']').addClass('notready').html('Вне игры');
		} else if (rivals[i].active == 1 && rivals[i].state == 1) {
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
//	var minbet = +$('#min-bet').html();
//	var maxbet = +$.cookie('gamemaxbet');
//	var bet    = +$('#bet').html();
//	var currentBet = +this.val();
}


function enableButtons(state) {
	$('.game-buttons button').prop('disabled', true);
	state = state || +$.cookie('gamestate');
	var gamerstate = +$.cookie('gamerstate');
	var gamemover = +$.cookie('gamemover');
	var minbet = +$('#min-bet').html();
	var maxbet = +$.cookie('gamemaxbet');
	var bet    = +$('#bet').html();
	var bank   = +$('#bank').html();
	var chips  = +$('#chips').html();
	switch (state) {
		case 2:
		case 3:
			if (gamemover == gamerseat && gamerstate == 1) {
				$('.game-buttons button[data-action=fold]').prop('disabled', false);
				$('.game-buttons button[data-action=allin]').prop('disabled', false);
				if (minbet < chips && (maxbet - bet) < chips) {
					if (bet == 0 && maxbet == 0 && state == 2) {
						$('.game-buttons button[data-action=bet]').prop('disabled', false);
					} else if ( maxbet > bet ) {
						if ($('#input_bet').val() < (maxbet - bet)) {
							$('#input_bet').val((maxbet - bet));
						}
						$('.game-buttons button[data-action=bet]').prop('disabled', false);
					} else {
						$('#input_bet').val(minbet);
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
	}
	$('.game-buttons button[data-action=out]').prop('disabled', state == 6);
}

function initGame() {
	stopTime();
    gameTimeId = setInterval(startTime, 1000);
	$(document).on('click', '.choose', onChooseCard);
	$(document).on('click', 'a[data-action=nobuyanswer]',    onClickNoBuyAnswer);
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
//	$(document).on('change', '#input_bet', onChangeBetInput);
	enableButtons();
	gameTimerId = setInterval(startTimer, 1000);
//	setInterval(onUpdate, 3000);
	$('.gamer-container').zoomcard();
	$('.game-board-container').preloadImages(cardimages);
//	console.log($.cookie('timerhandler'));
}

$(document).ready(function(){
	initGame();
});

var gameupdated = 0;

function startUpdate(data) {

    if (data.board.board == gameid && data.board.updated > gameupdated) {
        gameupdated = data.board.updated;
        console.log('update ' + Date.now());
        onWSUpdate(data);
    }
}

function updateWSRivals(rivals, board) {
    for (var j in rivals) {
        $('.gamer-chips[data-bot-id=' + rivals[j].user + ']').html(rivals[j].chips);
        $('.gamer-bet[data-bot-id=' + rivals[j].user + ']').html(rivals[j].bet);

        if (rivals[j].cards){
            $('.gamer-cards[data-bot-id=' + rivals[j].user + ']').empty();
            for (var i in rivals[j].cards) {
                if (board.state == 4) {
                    var src = '/bundles/public/img/cards/' + rivals[j].cards[i].name + '.png';
                } else {
                    var src = '/bundles/public/img/shirt.png';
                }
                var card = $('<div><img src="'+src+'"></div>').addClass('card').attr('data-card-id', i);
                if (board.state == 4 && $.inArray(rivals[j].cards[i].name, board.combination) > -1) {
                    card.addClass('active');
                }

                $('.gamer-cards[data-bot-id=' + rivals[j].user + ']').append(card);
            }
        } else if ($.inArray(board.state, [2, 3, 4]) > -1 && rivals[j].active) {
            $('.gamer-cards[data-bot-id=' + rivals[j].user + ']').html('<div class="pass">ПАС</div>');
        } else {
            $('.gamer-cards[data-bot-id=' + rivals[j].user + ']').empty();
        }

        if (rivals[j].active == 1 && rivals[j].state == 3) {
            $('.gamer-status[data-bot-id='+rivals[j].user+']').addClass('notready').html('Вне игры');
        } else if (rivals[j].active == 1 && rivals[j].state == 1) {
            $('.gamer-status[data-bot-id='+rivals[j].user+']').removeClass('notready').html('Активен');
        } else {
            $('.gamer-status[data-bot-id='+rivals[j].user+']').addClass('notready').html('Не активен');
        }

    }
}

function getRivalPosition(rivalSeat, numOfGamers, seat) {
    if (rivalSeat == seat) {
        return 0;
    }

    var position = leftOffset = rightOffset = 0;

    switch(numOfGamers){
        case 2:
            return 3;
        case 3:
            leftOffset = 1;
            rightOffset = 2;
            break;
        case 4:
            leftOffset = 1;
            rightOffset = 1;
            break;
        case 5:
            leftOffset = 0;
            rightOffset = 1;
            break;
        default:
            leftOffset = 0;
            rightOffset = 0;
            break;
    }
    if (rivalSeat > seat) {
        position = rivalSeat - seat + leftOffset;
    } else {
        position = 6 - (seat - rivalSeat + rightOffset);
    }

    return position;
}

function setTimer(timerData) {
    var currentTimer = $.cookie('timerhandler');
    var currentStop = $.cookie('timerstop');
    if (timerData.length == 1 && currentTimer != timerData[0].handler && currentStop != timerData[0].time) {
        $.cookie('timerhandler', timerData[0].handler, {path: '/'});
        $.cookie('timerholder', timerData[0].holder, {path: '/'});
        $.cookie('timerstop', timerData[0].time, {path: '/'});
        gameTimerId = setInterval(startTimer, 1000);
    }
}


function onWSUpdate(data) {
    stopTimer();

    var state = $.cookie('gamestate');
    if (state > 0 && data.board.state == 0 ) {
        window.location.reload();
    }

    $('#table').html(data.table);

    console.log('GAMESTATE', data.board.state);
    console.log('GAMEMOVER', data.board.mover);
    console.log('GAMERSEAT', data.gamer.seat);

    if (data.gamer.cards){
        $('#gamer-cards').empty();
        for (var i in data.gamer.cards) {
            var src = '/bundles/public/img/cards/' + data.gamer.cards[i].name + '.png';
            var card = $('<div><img src="'+src+'"></div>').addClass('card').attr('data-card-name', data.gamer.cards[i].name).attr('data-card-id', i);
            if (data.board.state == 1) {
                card.addClass('choose');
            }
            if (!data.gamer.winner && $.inArray(data.board.state, [2, 3, 4]) > -1 && $.inArray(data.gamer.cards[i].name, data.gamer.combination) > -1) {
                card.addClass('hint');
            }
            if (data.board.state == 4 && $.inArray(data.gamer.cards[i].name, data.board.combination) > -1) {
                card.addClass('active');
            }

            $('#gamer-cards').append(card);
        }
    } else if ($.inArray(data.board.state, [2, 3, 4])  > -1) {
        $('#gamer-cards').html('<div class="pass">ПАС</div>');
    } else {
        $('#gamer-cards').empty();
    }

    $('#bank').html(data.board.bank);
    $('#bets').html(data.board.bets);
    $('#chips').html(data.gamer.chips);
    $('#bet').html(data.gamer.bet);

    $('.game-winner').remove();
    if (data.board.state == 4) {
        for(var i in data.board.winner) {
            var winner = $('<div></div>')
                .addClass('game-winner winner' + getRivalPosition(data.board.winner[i].seat, data.board.winner[i].numOfGamers, data.gamer.seat))
                .html('Победитель &laquo;'+ data.board.winner[i].name + '&raquo;');
            $('.gamer-container').append(winner);
        }
    }

    $('.gamer-hint').remove();
    if ($.inArray(data.board.state, [2, 3, 4])  > -1 && data.gamer.rank && !data.gamer.winner){
        var hint = $('<div></div>').addClass('gamer-hint hint0').html('&laquo;'+data.gamer.rank+'&raquo;');
        $('.gamer-container').append(hint);
    }

    var bet = +$('#input_bet').val();
    $('#min-bet').html(data.board.minbet);
    if (bet == NaN || data.board.minbet > bet) {
        $('#input_bet').val(data.board.minbet);
    }

    $.cookie('gamerstate', data.gamer.state, {path: '/'});
    $.cookie('gamestate', data.board.state, {path: '/'});
    $.cookie('gamemaxbet', data.board.maxbet, {path: '/'});
    $.cookie('gamemover', data.board.mover, {path: '/'});

    updateWSRivals(data.gamers, data.board);

    if (data.board.state == 6) {
        $('.gamer-cards').empty();
        $('.game-min-bet').empty();
        $('.game-main-banks').empty();
        stopTime();
    } else {
        if ($.inArray(data.board.state, [2,3]) > -1 && data.gamer.seat == data.board.mover) {
            console.log('torgi settimer');
            setTimer(data.board.timer);
        } else if ($.inArray(data.board.state, [2,3]) == -1) {
            setTimer(data.board.timer);
            setTimer(data.gamer.timer);
        }
    }

    enableButtons();

//    console.log($.cookie('timerhandler'));
}

var ws = new WebSocket('ws://' + window.location.hostname + ':3001/game/' + gameid + '/' + gamerid);

ws.onmessage = function (event) {
	startUpdate(JSON.parse(event.data));
};

ws.onopen = function () {
	console.log('Connected');
};

ws.onclose = function (event) {
	if (event.wasClean) {
		console.log('Disconnected clear');
	} else {
		console.log('Disconnect with error');
	}
	console.log('Code ' + event.code);
};

ws.onerror = function (err) {
	
};

