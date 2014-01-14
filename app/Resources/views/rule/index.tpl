<div class="accordion" id="accordion2">
	{foreach from=$items item=item name=rule}
	<div class="accordion-group">
		<div class="accordion-heading">
			<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapse{$item.id}">
			  {$item.name}
			</a>
		</div>
		<div id="collapse{$item.id}" class="accordion-body collapse{if $smarty.foreach.rule.index == 0} in{/if}">
			<div class="accordion-inner">
				{$item.description}
			</div>
		</div>
	</div>
	{/foreach}
</div>