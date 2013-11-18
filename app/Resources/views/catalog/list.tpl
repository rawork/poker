<div id="messageResult" class="label hidden"></div>
{foreach from=$items item=item}
<div class="row">
	<div class="col-md-8">
		<label>{if $key}<input type="radio" name="present" value="{$item.id}">{/if} {$item.name}
		<div class="help-block">{$item.description}</div>
		</label>
	</div>
	<div class="col-md-4 vote-pad"><a href="{raURL node=$item.blagodom_id_value.item.node_id_value.name method=advices prms=$item.id}" class="pull-right btn btn-default"><img src="{$theme_ref}/public/img/star-small.png"> {"Recommend"|t}</a></div>
</div>
{/foreach}