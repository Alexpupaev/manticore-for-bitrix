<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог

use Bitrix\Main\Context;
use \Bitrix\Main\Loader;

CJSCore::Init(array("jquery"));

IncludeModuleLangFile(__FILE__);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$APPLICATION->SetTitle('Manticore поиск'); ?>

<?
if (Loader::includeModule('iblock')) {

    //$allowedTypes = array('catalog', 'news');
    $iblocksDB = CIBlock::GetList(array("SORT"=>"ASC"), array('TYPE'=> $allowedTypes,
                                                              'SITE_ID'=>Context::getCurrent()->getSite(),
                                                              'ACTIVE'=>'Y'), true, false);
    while($iblock = $iblocksDB->Fetch()){
        $iblocks[$iblock['ID']] = $iblock;
        $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$iblock['ID'], 'SEARCHABLE' => 'Y'));
        while ($prop_fields = $properties->GetNext())
        {
            $iblocks[$iblock['ID']]['PROPERTIES'][$prop_fields['ID']] = $prop_fields;
        }
    }

}
?>

<table class="adm-detail-content-table edit-table" style="opacity: 1;">
    <tbody>

    <tr>
        <td colspan="2">
            <table border="0" cellspacing="0" cellpadding="0" class="internal">
                <tbody>
                <tr style="display: table-row;" class="show-for-sphinx">
                    <td width="30%" class="adm-detail-content-cell-l">
                        <label for="index_name">Идентификатор индекса:</label>
                    </td>
                    <td width="30%" class="adm-detail-content-cell-r">
                        <input type="text" size="45" maxlength="100" value="bitrix" name="index_name">
                    </td>
                    <td width="10%" style="text-align: center; vertical-align:middle;">
                        <input type="button"
                               style="width: 100%;"
                               title="Нажмите для детального редактирования"
                               class="adm-btn-check-index search"
                               name=""
                               id=""
                               value="Проверить"
                               data-propid="32">
                    </td>
                    <td width="10%" style="text-align: center; vertical-align:middle;">
                        <input type="button"
                               style="width: 100%;"
                               title="Нажмите для детального редактирования"
                               class="adm-btn-create-index search"
                               name=""
                               id=""
                               value="Создать индекс"
                               data-propid="32">
                    </td>

                    <td width="10%" style="text-align: center; vertical-align:middle;">
                        <input type="button"
                               style="width: 100%;"
                               title="Нажмите для детального редактирования"
                               class="adm-btn-clear-index search"
                               name=""
                               id=""
                               value="Очистить индекс"
                               data-propid="32">
                    </td>

                    <td width="10%" style="text-align: center; vertical-align:middle;">
                        <input type="button"
                               style="width: 100%;"
                               title="Нажмите для детального редактирования"
                               class="adm-btn-delete-index search"
                               name=""
                               id=""
                               value="Удалить индекс"
                               data-propid="32">
                    </td>
                </tr>

                <tr style="display: table-row;" class="show-for-sphinx">
                    <td width="30%" class="adm-detail-content-cell-l">
                        <label for="index_name">Поиск в индексе:</label>
                    </td>
                    <td width="30%" class="adm-detail-content-cell-r">
                        <input type="text" size="45" maxlength="100" value="" name="query">
                    </td>
                    <td width="20%" style="text-align: center; vertical-align:middle;">
                        <input type="button"
                               style="width: 100%;"
                               title="Нажмите для детального редактирования"
                               class="adm-btn-query search"
                               name=""
                               id=""
                               value="Полнотекстовый поиск"
                               data-propid="32">
                    </td>
                    <td width="20%" style="text-align: center; vertical-align:middle;">
                        <input type="button"
                               style="width: 100%;"
                               title="Нажмите для детального редактирования"
                               class="adm-btn-title search"
                               name=""
                               id=""
                               value="Поиск по заголовкам"
                               data-propid="32">
                    </td>
                    <td width="0%" style="text-align: center; vertical-align:middle;"></td>
                    <td width="0%" style="text-align: center; vertical-align:middle;"></td>
                </tr>



                </tbody>
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="2"><table border="0" cellspacing="0" cellpadding="0" class="internal">
                <tbody>

                <tr class="heading">
                    <td width="40%" style="text-align: left !important;">Инфоблок</td>
                    <td width="20%" style="text-align: left !important;">Элементы</td>
                    <td width="20%" style="text-align: left !important;">Разделы<br>(не используются)</td>
                    <td width="20%" style="text-align: left !important;">Свойства</td>
                    <td width="20%" style="text-align: left !important;">Изменить</td>
                </tr>

                <? foreach($iblocks as $iblock): ?>
                    <? if($iblock['INDEX_ELEMENT'] != 'Y' && $iblock['INDEX_SECTION'] != 'Y') { continue; }?>

                    <tr>
                        <td><?=$iblock['NAME']?></td>

                        <td>
                            <input disabled <?=($iblock['INDEX_ELEMENT'] == 'Y') ? 'checked' : '' ?>
                                   type="checkbox"
                                   name="element-section-<?=$iblock['ID']?>"
                                   value="Y"
                                   class="adm-designed-checkbox">
                            <label class="adm-designed-checkbox-label" for="element-section-<?=$iblock['ID']?>" title=""></label>
                        </td>

                        <td>
                            <input disabled <?=($iblock['INDEX_SECTION'] == 'Y') ? 'checked' : '' ?>
                                   type="checkbox"
                                   name="index-section-<?=$iblock['ID']?>"
                                   value="Y"
                                   class="adm-designed-checkbox">
                            <label class="adm-designed-checkbox-label" for="index-section-<?=$iblock['ID']?>" title=""></label>
                        </td>

                        <td>
                            <? if($iblock['PROPERTIES']): ?>
                                <? foreach ($iblock['PROPERTIES'] as $property): ?>
                                    <p><?=$property['NAME']?></p>
                                <? endforeach; ?>
                            <? endif; ?>
                        </td>

                        <td style="text-align: center; vertical-align:middle;">
                            <a href="/bitrix/admin/iblock_edit.php?type=<?=$iblock['IBLOCK_TYPE_ID']?>&ID=<?=$iblock['ID']?>">
                                <input type="button"
                                       title="Нажмите для детального редактирования"
                                       name=""
                                       id=""
                                       value="..."
                                       data-propid="32">
                            </a>
                        </td>
                    </tr>
                <? endforeach; ?>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>

<div class="adm-detail-content-btns">
    <input type="submit" name="save" value="Переиндексация" title="Сохранить и вернуться" class="adm-btn-save reindex">
    <script type="text/javascript">

    </script>
    <!--input type="submit" name="apply" value="Поиск" title="Сохранить и остаться в форме" class="adm-btn-save search">
    <script type="text/javascript"> BXHotKeys.Add("", "var d=BX .findChild(document, {attribute: {\'name\': \'apply\'}}, true );  if (d) d.click();", 19, 'Кнопка Применить в форме редактирования', 0);
    </script>
    <input type="button" value="Отменить" name="cancel" onclick="top.window.location='iblock_admin.php?lang=ru&amp;type=news&amp;admin=Y'" title="Не сохранять и вернуться">
    <script type="text/javascript"> BXHotKeys.Add("", "var d=BX .findChild(document, {attribute: {\'name\': \'cancel\'}}, true );  if (d) d.click();", 20, 'Кнопка Отменить в форме редактирования', 0);
    </script-->
</div>



<script type="text/javascript">

    $(document).ready(function() {

        $(".adm-btn-save.reindex").click(function (e) {
            let index = $("input[name=index_name]").val();
            $.ajax({
                type: "POST",
                url: "/router/search/reindex/",
                data: {index: index},
                success: function (res) {
                    console.log(res);
                    let json = JSON.parse(res);
                    console.log(json);

                }
            });
        });


        $(".adm-btn-query.search").click(function (e) {
            let index = $("input[name=index_name]").val();
            let query = $("input[name=query]").val();
            $.ajax({
                type: "POST",
                url: "/router/search/query/",
                data: {index:index, query:query},
                success: function (res) {
                    let json = JSON.parse(res);
                    console.log(json);

                }
            });
        });



        $(".adm-btn-title.search").click(function (e) {
            let index = $("input[name=index_name]").val();
            let query = $("input[name=query]").val();
            $.ajax({
                type: "POST",
                url: "/router/search/title/",
                data: {index:index, query:query},
                success: function (res) {
                    let json = JSON.parse(res);
                    console.log(json);

                }
            });
        });


        $(".adm-btn-create-index.search").click(function (e) {
            let index = $("input[name=index_name]").val();
            $.ajax({
                type: "POST",
                url: "/router/search/create/",
                data: {index:index},
                success: function (res) {
                    let json = JSON.parse(res);

                    if(!json.success){
                        alert(json.errors.message);
                    }
                    else{
                        alert(json.data);
                    }

                }
            });
        });


        $(".adm-btn-clear-index.search").click(function (e) {
            let index = $("input[name=index_name]").val();
            $.ajax({
                type: "POST",
                url: "/router/search/clear/",
                data: {index:index},
                success: function (res) {
                    let json = JSON.parse(res);

                    if(!json.success){
                        alert(json.errors.message);
                    }
                    else{
                        alert(json.data);
                    }

                }
            });
        });

        $(".adm-btn-delete-index.search").click(function (e) {
            let index = $("input[name=index_name]").val();
            $.ajax({
                type: "POST",
                url: "/router/search/drop/",
                data: {index:index},
                success: function (res) {
                    let json = JSON.parse(res);

                    if(!json.success){
                        alert(json.errors.message);
                    }
                    else{
                        alert(json.data);
                    }

                }
            });
        });



        $(".adm-btn-check-index.search").click(function (e) {
            let index = $("input[name=index_name]").val();
            $.ajax({
                type: "POST",
                url: "/router/search/check/",
                data: {index:index},
                success: function (res) {
                    let json = JSON.parse(res);

                    if(!json.success){
                        alert(json.errors.message);
                    }
                    else{
                        alert(json.data);
                    }

                }
            });
        });




    });

</script>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
