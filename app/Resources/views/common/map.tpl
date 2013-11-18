<div class="map{$block}_links">
{foreach from=$nodes item=node}
<a href="{$node.ref}"><span>&gt;</span> {if $node.title}{$node.title}{else}{$node.name}{/if}</a>
{$node.sub}
{/foreach}
</div>