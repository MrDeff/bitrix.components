<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
?>
<? $APPLICATION->IncludeComponent('site:component.section', '', array('IBLOCK_ID' => $arParams['IBLOCK_ID'],
		'SECTION_ID' => $arResult['VARIABLES']['SECTION_ID'],
		'URL_SECTION' => $arResult["FOLDER"] . $arResult['URL_TEMPLATES']['section'],
		'URL_DETAIL' => $arResult["FOLDER"] . $arResult['URL_TEMPLATES']['detail'],
		"CACHE_TYPE" => $arParams['CACHE_TYPE'],
		'CACHE_TIME' => $arParams['CACHE_TIME'],
	)
); ?>