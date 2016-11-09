<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();?>

<? $APPLICATION->IncludeComponent('site:component.detail', '', array('IBLOCK_ID' => $arParams['IBLOCK_ID'],
		'SECTION_ID' => $arResult['VARIABLES']['SECTION_ID'],
		'ELEMENT_ID' => $arResult['VARIABLES']['ELEMENT_ID'],
		'URL_SECTION' => $arResult["FOLDER"] . $arResult['URL_TEMPLATES']['section'],
		"CACHE_TYPE" => $arParams['CACHE_TYPE'],
		'CACHE_TIME' => $arParams['CACHE_TIME'],
	)
); ?>