<?php
/**
 * ALTASIB
 * @site http://www.altasib.ru
 * @email dev@altasib.ru
 *
 * @copyright 2006-2016 ALTASIB
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
use \Bitrix\Main\Localization\Loc;
$this->setFrameMode(true);
$strElementEdit = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT");
$strElementDelete = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE");
?>
<div>
	<?foreach ($arResult['ELEMENTS'] as $element):
		$this->AddEditAction($element['ID'], $element['EDIT_LINK'], $strElementEdit);
		$this->AddDeleteAction($element['ID'], $element['DELETE_LINK'], $strElementDelete);
	?>
	<div id="<?=$this->GetEditAreaId($element['ID'])?>">

	</div>
	<?endforeach;?>
	<?=$arResult["NAV_STRING"]?>
</div>