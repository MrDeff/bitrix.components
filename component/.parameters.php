<?php
/**
 * ALTASIB
 * @site http://www.altasib.ru
 * @email dev@altasib.ru
 *
 * @copyright 2006-2016 ALTASIB
 */
if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

use Bitrix\Iblock;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if(!\Bitrix\Main\Loader::includeModule('iblock'))
	return;

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$iblockFilter = (
!empty($arCurrentValues['IBLOCK_TYPE'])
	? array('IBLOCK_TYPE_ID' => $arCurrentValues['IBLOCK_TYPE'], 'ACTIVE' => 'Y')
	: array('ACTIVE' => 'Y')
);
$iblocks = array();

$dataIblock = Iblock\IblockTable::getList(array('filter'=>$iblockFilter));
while ($iblock = $dataIblock->fetch())
	$iblocks[$iblock['ID']] = '['.$iblock['ID'].'] '.$iblock['NAME'];

$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
		"VARIABLE_ALIASES" => array(
			"ELEMENT_ID" => array(
				"NAME" => GetMessage("SITE_COMP_COMP_PARAM_VAR_ELEMENT_ID"),
			),
			"SECTION_ID" => array(
				"NAME" => GetMessage("SITE_COMP_COMP_PARAM_VAR_SECTION_ID"),
			),
		
		),
		"AJAX_MODE" => array(),
		"SEF_MODE" => array(
			"section" => array(
				"NAME" => GetMessage("SECTION_PAGE"),
				"DEFAULT" => "#SECTION_ID#/",
				"VARIABLES" => array(
					"SECTION_ID",
					"SECTION_CODE",
				),
			),
			"detail" => array(
				"NAME" => GetMessage("DETAIL_PAGE"),
				"DEFAULT" => "#SECTION_ID#/#ELEMENT_ID#/",
				"VARIABLES" => array(
					"ELEMENT_ID",
					"ELEMENT_CODE",
					"SECTION_ID",
					"SECTION_CODE",
				),
			),
		),
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $iblocks,
			"REFRESH" => "Y",
		),
		"PAGE_ELEMENT_COUNT" => array(
			"PARENT" => "DATA",
			"NAME" => GetMessage("PAGE_ELEMENT_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "30",
		),
		"SET_TITLE" => array(),
		"CACHE_TIME"  =>  array("DEFAULT"=>86400),
	),
);