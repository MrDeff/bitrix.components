<?php
/**
 * ALTASIB
 * @site http://www.altasib.ru
 * @email dev@altasib.ru
 *
 * @copyright 2006-2016 ALTASIB
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);

$strSectionEdit = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_EDIT");
$strSectionDelete = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_DELETE");
?>
<div>
	<?foreach ($arResult['SECTIONS'] as $section):
		$this->AddEditAction($section['ID'], $section['EDIT_LINK'], $strSectionEdit);
		$this->AddDeleteAction($section['ID'], $section['DELETE_LINK'], $strSectionDelete);
	?>
	<?endforeach;?>
</div>