<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

$APPLICATION->IncludeComponent('site:component.detail', '', array(
		'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
		'IBLOCK_ID' => $arParams['IBLOCK_ID'],
		'SECTION_ID' => $arResult['VARIABLES']['SECTION_ID'],
		'SECTION_CODE' => $arResult['VARIABLES']['SECTION_CODE'],
		'ELEMENT_ID' => $arResult['VARIABLES']['ELEMENT_ID'],
		'ELEMENT_CODE' => $arResult['VARIABLES']['ELEMENT_CODE'],
		'URL_SECTION' => $arResult["FOLDER"] . $arResult['URL_TEMPLATES']['section'],
		'URL_DETAIL' => $arResult["FOLDER"] . $arResult['URL_TEMPLATES']['detail'],
		'SET_TITLE' => $arParams['SET_TITLE'],
		"CACHE_TYPE" => $arParams['CACHE_TYPE'],
		'CACHE_TIME' => $arParams['CACHE_TIME'],
	)
);