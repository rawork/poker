<div class="question-body">
	<div class="title">{$question.name}:</div>
	<ul class="question-answer">
		<li><i data-answer-id="1"></i> {$question.answer1}</li>
		<li><i data-answer-id="2"></i> {$question.answer2}</li>
		<li><i data-answer-id="3"></i> {$question.answer3}</li>
		<li><i data-answer-id="4"></i> {$question.answer4}</li>
	</ul>	
</div>
<div class="question-footer">
	<input class="btn btn-warning" value="ОТВЕТИТЬ" /> {if $buying}<a href="javascript:void(0)">без ответа</a>{/if} 
	{if $buying}
	<div class="row-fluid">
		<div class="span4 question-timer">Время на ответ: <span id="question-timer"></span></div>
		<div class="span8 question-times">Попытка <span id="question-times">{$times}</span> из <span id="question-max-times">3</span></div>
	</div>
	{else}
	<div class="question-timer">Время на ответ: <span id="question-timer"></span></div>
	{/if}
</div>