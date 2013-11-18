<h4>Свойства</h4>
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
                      <td><input type="checkbox" name="is_lang" {if $a.is_lang}checked{/if} value="1" />
                        многоязычная</td>
                    </tr>
                    <tr>
                      <td><input type="checkbox" name="is_sort" {if $a.is_sort}checked{/if} value="1" />
                        с полем сортировки</td>
                    </tr>
                    <tr>
                      <td><input type="checkbox" name="is_publish" {if $a.is_publish}checked{/if} value="1" />
                        c полем публикации</td>
                    </tr>
                    <tr>
                      <td><input type="checkbox" name="is_view" {if $a.is_view}checked{/if} value="1" />
                        вид "дерево"</td>
                      <td><input type="checkbox" name="is_search" {if $a.is_search}checked{/if} value="1" />
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
            </table>