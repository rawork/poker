<div class="game-question-container">
	<div class="game-question">
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
			<button class="btn btn-warning" data-action="buying">ОТВЕТИТЬ</button> <a data-action="nobuying">без ответа</a> 
			<div class="row-fluid">
				<div class="span6 question-timer">Время на ответ: <span id="question-timer"></span></div>
				<div class="span6 question-times">Попытка <span id="question-times">{$question.number}</span> из <span id="question-max-times">3</span></div>
			</div>
		</div>
	</div>
</div>