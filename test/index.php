<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Поиск");

use \Bitrix\Main\Loader; 
use BitrixManticore\Services\Search\Search;

$query = $_REQUEST['q'];
$query = htmlspecialchars($query);

if ($query) {
    $searchService = new Search('bitrix');
    $resultID        = $searchService->query($query);
        if (Loader::includeModule('iblock') && Loader::includeModule('catalog') && $resultID) {
        $arSelect = Array("ID", "NAME", "PREVIEW_PICTURE", "DETAIL_PICTURE", "DETAIL_PAGE_URL", "IBLOCK_ID");
        $arNav = array(
          'nTopCount' => false,
          'nPageSize' => 10,
          //'iNumPage' => 2,
          'checkOutOfRange' => true
        );
        $result = CIBlockElement::GetList(array(), array("ID" => $resultID, 'ACTIVE' => 'Y'), false, $arNav, array(), $arSelect);
        while($arElement = $result->GetNext()) {
            $arElements[] = $arElement;
        }
        $arResult["NAV_STRING"] = $result->GetPageNavStringEx($navComponentObject, "", 's-modern');
    }
}
?>
	<? if ($arElements): ?>
        <ul>
            <? foreach ($arElements as $arElement): ?>
                <li class="search__item"></li>
            <? endforeach; ?>
        </ul>
        <?=$arResult["NAV_STRING"];?>
	<? else: ?>
        <p>По заданным условиям ничего не найдено.</p>
	<? endif; ?>
    
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>