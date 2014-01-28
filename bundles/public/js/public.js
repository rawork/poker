var input_ajax = false;
var messageDiv = null;
var range  = null;
var loadScrolled = false;
var page = 2;


function getCaretPos(){
	var sel = window.getSelection(); 
	if (sel.getRangeAt && sel.rangeCount) {
		range = sel.getRangeAt(0);
		start = range.startOffset;
		end   = range.endOffset;
		startNode = range.startContainer;
		endNode = range.endContainer;
		selectedText = range.toString();
	}
}

function restoreCaretPos(){
	selection = window.getSelection();
	if (selection.rangeCount > 0) {
		selection.removeAllRanges();
		selection.addRange(range);
	}
}

function setCaretPos(startNodeIndex, endNodeIndex, start, end){
	range = document.createRange();
	var editableContainer = document.getElementById(containerId);
	range.setStart(editableContainer.childNodes[startNodeIndex].firstChild,start);
	range.setEnd(editableContainer.childNodes[endNodeIndex].firstChild,end);
	var selection = window.getSelection();
	selection.addRange(range);
}

jQuery.fn.insertHtmlAtCursor = function (html) {
    var sel, range, node;
    if (window.getSelection) {
        sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
            range = sel.getRangeAt(0);
            range.deleteContents();
            node = range.createContextualFragment(html);
            range.insertNode(node);
			
			range.collapse(false);
			sel = window.getSelection();
			sel.removeAllRanges();
			sel.addRange(range);
        }
    } else if (document.selection && document.selection.createRange) {
        document.selection.createRange().pasteHTML(html);
    }
}

$(document).ready(function() {
	
	//register && edit user data
	$('.register-avatar input[type=file]').on('change', function(event) {
		var pathname = $(this).val();
		var pos = pathname.lastIndexOf('/') > 0 ? pathname.lastIndexOf('/') : pathname.lastIndexOf('\\');
		pathname = pathname.substring(pos+1);
		$('.register-avatar').append($('<span></span>').addClass('file-title').html(pathname));
		$('.register-avatar a.remove-icon').show();
	});
	
	$('.register-avatar a.remove-icon').on('click', function(event) {
		$('.register-avatar input[type=file]').val('');
		$('.register-avatar .file-title').remove();
		$(this).hide();
	});
	
	// members
	$(document).on('click', 'button[data-action=like]', function () {
		var memberId = $(this).parents('.member, .user-card').attr('data-member-id');
		$.post('/members/like/' + memberId, {},
		function(data){
			if (data.ok) {
				$('span[data-like-id='+memberId+']').html(data.content);
			} else {
				alert(data.content);
			}
		}, "json");
	});
	
	$(document).on('click', 'button[data-action=bet]', function () {
		alert($(this).parents('.member').attr('data-member-id'));
	});
	
	$(document).on('click', 'a[data-action=card]', function () {
		var memberId = $(this).parents('.member').attr('data-member-id');
		
		$.post('/members/card/' + memberId, {},
		function(data){
			if (data.ok) {
				var top = ($(window).height()-590)/2;
				top = top < 0 ? 50 : top;
				$('.member-card').html(data.content);
				$('.member-card').css('top', top);
				$('.member-card a.close').on('click', function() {
					$('.member-card').hide();
				});
				$('.member-card').show();
			} else {
				alert(data.content);
			}
		}, "json");
	});
	
	$(document).on('keyup', '.member-search input', function(event) {
		input_ajax = true;
	});
	
	$('.member-search input').idle({
		onIdle: function(){
			if (!input_ajax) {
				return;
			}
			var text = $('.member-search input').val();
			if (!text) {
				text = '1=1';
			}
			input_ajax = false;
			$.post('/members', {text: text},
			function(data){
				if (data.ok) {
					$('#members').html(data.content);
				} else {
					alert(data.content);
				}
			}, "json");
		},
		events: 'keyup',
		idle: 1000
	});
	
	// club
	$(document).on('click', 'button[data-action=like-message]', function () {
		var messageId = $(this).siblings('span').attr('data-message-id');
		$.post('/club/like/' + messageId, {},
		function(data){
			console.log(data);
			if (data.ok) {
				$('span[data-message-id='+messageId+']').html(data.content);
			} else {
				alert(data.content);
			}
		}, "json");
	});
	
	$(document).on('click', 'a[data-action=card2]', function() {
//		alert('card2');
	});
	
	$(document).on('click', 'a[data-action=comments]', function() {
		if ($(this).parents('.club-show-link').siblings('.club-comments').html()) {
			$(this).parents('.club-show-link').siblings('.club-add-comment, .club-comments').show();
			$(this).parents('.club-show-link').siblings('.club-hide-link').show();
			$(this).parents('.club-show-link').hide();
			return;
		}
		var messageId = $(this).parents('.club-chat').attr('data-message-id');
		$.post('/club/comments/' + messageId, {},
		function(data){
			if (data.ok) {
				$('#message' + messageId).append(data.content);
				$(this).parents('.club-show-link').siblings('.club-add-comment, .club-comments, .club-hide-link').show();
				$(this).parents('.club-show-link').hide();
			} else {
				alert(data.content);
			}
		}, "json");
	});
	
	$(document).on('click', 'a[data-action=hide]', function() {
		$(this).parents('.club-hide-link').siblings('.club-show-link').show();
		$(this).parents('.club-hide-link').siblings('.club-add-comment, .club-comments').hide();
		$(this).parents('.club-hide-link').hide();
	});
	
	$(document).on('click', 'button[data-action=comment]', function() {
		var that = this;
		var messageId = $(that).parents('.club-chat').attr('data-message-id');
		var message = $(that).parents('.club-add-comment').children('.text').html();
		if (!message) {
			return;
		}
		$.post('/club/comment', {message: message, message_id: messageId},
		function(data){
			if (data.ok) {
				$('#comments' + messageId).append(data.content);
				$(that).parents('.club-add-comment').children('.text').empty();
				$(that).parents('.club-add-comment').siblings('.club-show-link').children('a').html(data.counter);
			} else {
				alert(data.content);
			}
		}, "json");
	});
	
	$(document).on('focus', '.club-add-message .text, .club-add-comment .text', function() {
		$('.smiles-container').hide();
	});
	
	$(document).on('keydown, keypress, mousemove', '.club-add-message .text, .club-add-comment .text', function() {
		getCaretPos();
	});
	
	$(document).on('click', 'a[data-action=show-smiles]', function() {
		var position = $(this).position();
		messageDiv = $(this).parents('.club-add-message, .club-add-comment').children('.text');
		$('.smiles-container').css('top', position.top-155).css('left', position.left-20);
		$('.smiles-container').show();
	});
	
	$(document).on('click', '.smiles i.smile', function() {
		if (messageDiv) {
			node = $('<img src="/bundles/public/img/0.gif"></img>').attr('class', $(this).attr('class'));
			messageDiv.focus();
			restoreCaretPos();
			messageDiv.insertHtmlAtCursor(node[0].outerHTML);
			caretPos = null;
		}
		$('.smiles-container').hide();
		messageDiv = null;
	});
	
	$(document).on('click', 'button[data-action=message]', function() {
		var message = $('.club-add-message .text').html();
		if (!message) {
			return;
		}
		$.post('/club/message', {message: message},
		function(data){
			if (data.ok) {
				$('#messages').prepend(data.content);
				$('.club-add-message .text').empty();
			} else {
				alert(data.content);
			}
		}, "json");
	});
	
	$(window).scroll(function(event) {
		var docHeight = $(document).height();
		var scrolled  = $(document).scrollTop();
//		console.log(docHeight-scrolled);
		if (docHeight-scrolled > 800 || loadScrolled) {
			return;
		}
		loadScrolled = true;
		$.post('/club/more', {page: page},
		function(data){
			if (data.ok) {
				$('#messages').append(data.content);
				page +=1;
				loadScrolled = false;
			}
		}, "json");
	});
	
});