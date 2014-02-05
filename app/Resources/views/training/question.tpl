{if $training->gamer->question}
<div class="game-question-container">
	<div class="game-question">
		<div class="question-body">
			<div class="title">{$training->gamer->question.name}:</div>
			<ul class="question-answer">
				<li><i data-answer-id="1"></i> {$training->gamer->question.answer1}</li>
				<li><i data-answer-id="2"></i> {$training->gamer->question.answer2}</li>
				<li><i data-answer-id="3"></i> {$training->gamer->question.answer3}</li>
				<li><i data-answer-id="4"></i> {$training->gamer->question.answer4}</li>
			</ul>	
		</div>
		<div class="question-footer">
			<button class="btn btn-warning" data-action="answer">ОТВЕТИТЬ</button> 
			<div class="question-timer">Время на ответ: <span id="answer-timer"></span></div>
		</div>
	</div>
</div>
{else}
Ошибка! Вопрос не выбран.
{/if}