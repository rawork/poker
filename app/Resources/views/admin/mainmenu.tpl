{foreach from=$modules item=mod}
  <div><a href="javascript:getTableList('{$state}', '{$mod.name}');"><img border="0" src="{$theme_ref}/admin/img/icons/icon_folder_{$state}.gif"> {$mod.title}</a></div>
  <div class="admin-submenu{if $module ne $mod.name} closed{/if}" id="tableMenu_{$mod.name}">{$mod.tablelist}</div>
{/foreach}
