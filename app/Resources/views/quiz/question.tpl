<div class="victorina-question-body">
	<div class="title">{$question.name}</div>
	<ul class="question-answer" data-question-id="{$question.id}">
		<li><i data-answer-id="1"></i> {$question.answer1}</li>
		<li><i data-answer-id="2"></i> {$question.answer2}</li>
		<li><i data-answer-id="3"></i> {$question.answer3}</li>
		<li><i data-answer-id="4"></i> {$question.answer4}</li>
	</ul>	
</div>
<div class="victorina-question-footer">
	<button class="btn btn-warning" data-action="answer">ОТВЕТИТЬ</button><br> 
	<a data-action="close">вернуться к вопросу позже</a>
</div>