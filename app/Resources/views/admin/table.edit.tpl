<div class="tabs">
  <ul>
    <li id="general_tab" class="current"><span><a href="javascript:mcTabs.displayTab('general_tab','general_panel');" onmousedown="return false;">Свойства</a></span></li>
    <li class="" id="fields_tab"><span><a href="javascript:mcTabs.displayTab('fields_tab','fields_panel');" onmousedown="return false;">Поля</a></span></li>
    <li class="" id="rights_tab"><span><a href="javascript:mcTabs.displayTab('rights_tab','rights_panel');" onmousedown="return false;">Права</a></span></li>
  </ul>
</div>
<div class="panel_wrapper">
  <div id="general_panel" class="current">
    <form enctype="multipart/form-data" method="post" name="frmUpdateTable" id="frmUpdateTable" action="{$fullref}&action=update">
      <input type="hidden" name="id" value="{$a.id}">
      <input type="hidden" name="module_id" value="{$a.module_id}">
      <table width="100%" cellspacing="0" class="tprops">
        <tr>
          <td><h4>Свойства</h4>
            <table style="width:350px;margin-left:15px" border="0" cellpadding="2" cellspacing="0">
              <tr>
                <td>Название:<br>
                  <input type="text" name="title" class="field-props" value="{$a.title}" /></td>
              </tr>
              <tr>
                <td>Системное имя:<br>
                  <input type="text" name="name" class="field-props" value="{$a.name}" /></td>
              </tr>
              <tr>
                <td>Сортировка:<br>
                  <input type="text" name="order_by" class="field-props" value="{$a.order_by}" /></td>
              </tr>
              <tr>
                <td><table width="100%" border="0" cellpadding="2" cellspacing="0">
                    <tr>
                      <td><input type="checkbox" name="is_lang" {if $a.is_lang}checked{/if} value="on" />
                        многоязычная</td>
                    </tr>
                    <tr>
                      <td><input type="checkbox" name="is_sort" {if $a.is_sort}checked{/if} value="on" />
                        с полем сортировки</td>
                    </tr>
                    <tr>
                      <td><input type="checkbox" name="is_publish" {if $a.is_publish}checked{/if} value="on" />
                        c полем публикации</td>
                    </tr>
                    <tr>
                      <td><input type="checkbox" name="is_view" {if $a.is_view}checked{/if} value="on" />
                        вид "дерево"</td>
                      <td><input type="checkbox" name="is_search" {if $a.is_search}checked{/if} value="on" />
                        поисковое поле</td>
                    </tr>
                  </table></td>
              </tr>
              <tr>
                <td>Сорт.:<br>
                  <input type="text" name="sort" class="field-props-short" value="{$a.sort}" /></td>
              </tr>
              <tr>
                <td>Активный:<br>
                  <input type="checkbox" name="publish" {if $a.publish}checked{/if} value="on" /></td>
              </tr>
            </table></td>
        </tr>
      </table>
      <div class="ctlbtns">
        <input type="button" class="adm-btn" onClick="" value="Сохранить">
      </div>
    </form>
  </div>
  <div id="fields_panel" class="panel">
    <form enctype="multipart/form-data" method="post" name="frmTableFields" id="frmTableFields" action="{$fullref}&action=update">
      <table width="100%" cellspacing="0" class="tprops">
        <tr>
          <td><h4>Поля</h4>
            <table class="tfields" align="center" width="720" border="0" cellpadding="2" cellspacing="0">
              <tr>
                <th width="2%">ID</th>
                <th width="30%">Название</th>
                <th width="30%">Системное имя</th>
                <th width="30%">Тип</th>
                <th width="8%">Сорт.</th>
                <th width="8%">Изм.</th>
              </tr>
              {foreach from=$fields item=f}
              {if $f.id}
              <tr>
                <td>{$f.id}&nbsp;</td>
                <td><input type="text" name="title_{$f.id}" value="{$f.title}" /></td>
                <td><input type="text" name="name_{$f.id}" value="{$f.name}" /></td>
                <td><select name="type_{$f.id}">
		          	{foreach from=$types item=t}	
                    <option value="$t[1]" {if $t[1] eq $f.type}selected{/if}>{$t[0]}</option>
                    {/foreach}
                  </select></td>
                <td><input type="text" name="ord_{$f.id}" value="{$f.ord}" /></td>
                <td align="center"><a href="#" onclick="xajax_editField({$f.id});return false;"><img src="{$theme_ref}/admin/img/icons/icon_edit.gif" border="0" /></a></td>
              </tr>
              {/if}{/foreach}
            </table></td>
        </tr>
      </table>
      <div class="ctlbtns">
        <input type="button" class="adm-btn" onClick="updateFields({$a.id}, 'frmTableFields');" value="Сохранить">
      </div>
    </form>
  </div>
  <div id="rights_panel" class="panel">
    <form enctype="multipart/form-data" method="post" name="frmTableRights" id="frmTableRights">
      <input type="hidden" name="table_id" value="{$a.id}">
      <table width="100%" cellspacing="0" class="tprops">
        <tr>
          <td><h4>Права</h4>
            <table class="tfields" align="center" width="400" border="0" cellpadding="2" cellspacing="0">
              {foreach from=$groups item=g}
              <tr>
                <td>{$g.name} <a href="/admin/?unit=users&table=groups&action=s_update&id={$g.id}">[{$g.id}]</td>
                <td>
                {if $g.id eq 1}
                По-умолчанию (полный доступ)
                {else}
                <select name="type_{$f.id}">
		          	{foreach from=$rights key=k item=r}	
                    <option value="$k" {if $k eq $f.type}selected{/if}>{$r}</option>
                    {/foreach}
                  	</select>{/if}</td>
              </tr>
              {/foreach}
            </table></td>
        </tr>
      </table>
      <div class="ctlbtns">
        <input type="button" class="adm-btn" onClick="updateRights({$a.id}, 'frmTableRights');" value="Сохранить">
      </div>
    </form>
  </div>
</div>
