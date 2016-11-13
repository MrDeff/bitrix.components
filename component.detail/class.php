<?php
/**
 * ALTASIB
 * @site http://www.altasib.ru
 * @email dev@altasib.ru
 *
 * @copyright 2006-2016 ALTASIB
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use \Bitrix\Main;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Iblock;
Loc::loadMessages(__FILE__);

class siteComponentDetail extends \CBitrixComponent{
	protected $cacheAddon = array();
	protected $cacheKeys = array('ID','IBLOCK_ID','IBLOCK_SECTION_ID','NAME','LIST_PAGE_URL','CANONICAL_PAGE_URL','SECTION','IPROPERTY_VALUES'/*,'PROPERTIES'*/);
	public function onPrepareComponentParams($params)
	{
		$result = [
			'IBLOCK_TYPE' => trim($params['IBLOCK_TYPE']),
			'IBLOCK_ID' => (int) $params['IBLOCK_ID'],
			'SECTION_ID' => (int) $params['SECTION_ID'],
			'SECTION_CODE' => trim($params['SECTION_CODE']),
			'ELEMENT_ID' => (int) $params['ELEMENT_ID'],
			'ELEMENT_CODE' => trim($params['ELEMENT_CODE']),
			'URL_SECTION' => trim($params['URL_SECTION']),
			'URL_DETAIL' =>  trim($params['URL_DETAIL']),
			'SET_TITLE' => ($params['SET_TITLE']=='Y'),
			'CACHE_TYPE' => $params['CACHE_TYPE'],
			'CACHE_TIME' => intval($params['CACHE_TIME']) > 0 ? intval($params['CACHE_TIME']) : 86400,
		];
		
		return $result;
	}
	protected function checkModules()
	{
		if (!Main\Loader::includeModule('iblock'))
			throw new Main\LoaderException(Loc::getMessage('IBLOCK_MODULE_NOT_INSTALLED'));
	}
	protected function readDataFromCache()
	{
		if ($this->arParams['CACHE_TYPE'] == 'N')
			return false;
		
		return !($this->startResultCache(false, $this->cacheAddon));
	}
	
	protected function putDataToCache()
	{
		if (is_array($this->cacheKeys) && sizeof($this->cacheKeys) > 0)
		{
			$this->setResultCacheKeys($this->cacheKeys);
		}
	}
	
	protected function getResult()
	{
		//todo: SECTION_CODE_PATH
		$filterSection = array(
			'IBLOCK_TYPE'=>$this->arParams['IBLOCK_TYPE'],
			'IBLOCK_ID'=>$this->arParams['IBLOCK_ID'],
			'ACTIVE' => 'Y',
			'GLOBAL_ACTIVE'=>'Y',
		);
		
		if($this->arParams['SECTION_ID']>0){
			$filterSection['ID'] = $this->arParams['SECTION_ID'];
		}
		elseif (strlen($this->arParams['SECTION_CODE'])>0)
			$filterSection['=CODE'] = $this->arParams['SECTION_CODE'];
		else
			throw new Exception('section param null');
		
		$obSection = CIBlockSection::GetList(array(), $filterSection, false, array('ID','IBLOCK_ID','NAME','CODE','SECTION_PAGE_URL'));
		$obSection->SetUrlTemplates("", $this->arParams["URL_SECTION"]);
		if(!$dataSection = $obSection->GetNext(true,false))
			throw new Exception('section null');
		else{
			$ipropValues = new Iblock\InheritedProperty\SectionValues($dataSection["IBLOCK_ID"], $dataSection["ID"]);
			$dataSection["IPROPERTY_VALUES"] = $ipropValues->getValues();
			$this->arParams['SECTION_ID'] = $dataSection['ID'];
		}
		
		$dataSection['PATH'] = array();
		$obPath = CIBlockSection::GetNavChain($dataSection["IBLOCK_ID"], $this->arParams['SECTION_ID'],array('ID','IBLOCK_ID','NAME','CODE','SECTION_PAGE_URL'));
		$obPath->SetUrlTemplates("", $this->arParams["URL_SECTION"]);
		while ($arPath = $obPath->GetNext(true,false)) {
			$ipropValues = new Iblock\InheritedProperty\SectionValues($this->arParams["IBLOCK_ID"], $arPath["ID"]);
			$arPath["IPROPERTY_VALUES"] = $ipropValues->getValues();
			$this->arResult["SECTION"]["PATH"][] = $arPath;
		}
		
		if($this->arParams["ELEMENT_ID"] <= 0)
		{
			$findFilter = array(
				"IBLOCK_ID" => $this->arParams["IBLOCK_ID"],
				"IBLOCK_LID" => SITE_ID,
				"IBLOCK_ACTIVE" => "Y",
				"ACTIVE_DATE" => "Y",
			);
			$this->arParams["ELEMENT_ID"] = CIBlockFindTools::GetElementID(
				$this->arParams["ELEMENT_ID"],
				$this->arParams["ELEMENT_CODE"],
				false,
				false,
				$findFilter
			);
		}
		
		if($this->arParams["ELEMENT_ID"] <= 0)
			throw new Exception('element null');
		
		$filter = array(
			'IBLOCK_TYPE'=>$this->arParams['IBLOCK_TYPE'],
			'IBLOCK_ID'=>$this->arParams['IBLOCK_ID'],
			'ACTIVE' => 'Y',
			'GLOBAL_ACTIVE'=>'Y',
			'SECTION_ID' => $this->arParams['SECTION_ID'],
			'ID' => $this->arParams["ELEMENT_ID"]
		);
		$select = array(
			'ID',
			'IBLOCK_ID',
			'CODE',
			'XML_ID',
			'NAME',
			'ACTIVE',
			'DATE_ACTIVE_FROM',
			'DATE_ACTIVE_TO',
			'SORT',
			'PREVIEW_TEXT',
			'PREVIEW_TEXT_TYPE',
			'DETAIL_TEXT',
			'DETAIL_TEXT_TYPE',
			'TIMESTAMP_X',
			'IBLOCK_SECTION_ID',
			'LIST_PAGE_URL',
			'DETAIL_PAGE_URL',
			'DETAIL_PICTURE',
			'PREVIEW_PICTURE',
			'PROPERTY_*'
		);
		
		$element = CIBlockElement::GetList(array(),$filter,false,false,$select);
		$element->SetUrlTemplates($this->arParams['URL_DETAIL']);
		$element->SetSectionContext($dataSection);
		if ($obElement = $element->GetNextElement(true,false)){
			$dataElement = $obElement->GetFields();
			$dataElement["PROPERTIES"] = $obElement->GetProperties();
			
			//todo: to params format date
			if(strlen($dataElement["ACTIVE_FROM"])>0)
				$dataElement["DISPLAY_ACTIVE_FROM"] = CIBlockFormatProperties::DateFormat('j F Y', MakeTimeStamp($dataElement["ACTIVE_FROM"], CSite::GetDateFormat()));
			else
				$dataElement["DISPLAY_ACTIVE_FROM"] = "";
			
			if(strlen($dataElement["ACTIVE_TO"])>0)
				$dataElement["DISPLAY_ACTIVE_TO"] = CIBlockFormatProperties::DateFormat('j F Y', MakeTimeStamp($dataElement["ACTIVE_TO"], CSite::GetDateFormat()));
			else
				$dataElement["DISPLAY_ACTIVE_TO"] = "";
			
			$buttons = CIBlock::GetPanelButtons(
				$dataElement["IBLOCK_ID"],
				$dataElement["ID"],
				$dataSection["ID"],
				array("SECTION_BUTTONS"=>false, "SESSID"=>false/*, "CATALOG"=>true*/)
			);
			$dataElement["EDIT_LINK"] = $buttons["edit"]["edit_element"]["ACTION_URL"];
			$dataElement["DELETE_LINK"] = $buttons["edit"]["delete_element"]["ACTION_URL"];
			
			$ipropValues = new Iblock\InheritedProperty\ElementValues($dataElement["IBLOCK_ID"], $dataElement["ID"]);
			$dataElement["IPROPERTY_VALUES"] = $ipropValues->getValues();
			
			Iblock\Component\Tools::getFieldImageData(
				$dataElement,
				array('PREVIEW_PICTURE', 'DETAIL_PICTURE'),
				Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
				'IPROPERTY_VALUES'
			);
			//todo: prop display value
			$this->arResult = $dataElement;
			$this->arResult['SECTION'] = $dataSection;
		}
	}
	
	public function executeComponent()
	{
		try
		{
			$this->checkModules();
			if (!$this->readDataFromCache())
			{
				$this->getResult();
				$this->putDataToCache();
				$this->includeComponentTemplate();
			}
			$this->after();
		}
		catch (Exception $e)
		{
			$this->abortResultCache();
			ShowError($e->getMessage());
		}
	}
	
	private function after(){
		global $USER,$APPLICATION;
		
		/*if (Main\Loader::includeModule('iblock'))
			CIBlockElement::CounterInc($this->arResult["ID"]);*/
		
		if($USER->IsAuthorized() && $APPLICATION->GetShowIncludeAreas() && Main\Loader::includeModule("iblock")){
			$buttons = CIBlock::GetPanelButtons(
				$this->arParams["IBLOCK_ID"],
				$this->arResult['ID'],
				$this->arResult["SECTION"]["ID"],
				array("RETURN_URL" =>  array(
					"add_element" => CIBlock::GetArrayByID($this->arParams["IBLOCK_ID"], "DETAIL_PAGE_URL"),
					"add_section" => ($this->arParams["URL_SECTION"] != '' ? $this->arParams["URL_SECTION"]: CIBlock::GetArrayByID($this->arParams["IBLOCK_ID"], "SECTION_PAGE_URL")),
				),'SECTION_BUTTONS'=>true)
			);
			$this->addIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $buttons));
		}
		$arTitleOptions = array(
			'ADMIN_EDIT_LINK' => '',
			'PUBLIC_EDIT_LINK' => "",
			'COMPONENT_NAME' => $this->getName(),
		);
		if($this->arParams["SET_TITLE"])
		{
			if ($this->arResult["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"] != "")
				$APPLICATION->SetTitle($this->arResult["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"], $arTitleOptions);
			else
				$APPLICATION->SetTitle($this->arResult["NAME"], $arTitleOptions);
		}
		foreach($this->arResult['SECTION']['PATH'] as $path)
		{
			if ($path["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"] != "")
				$APPLICATION->AddChainItem($path["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"], $path["SECTION_PAGE_URL"]);
			else
				$APPLICATION->AddChainItem($path["NAME"], $path["SECTION_PAGE_URL"]);
		}
		
		//todo: seo
	}
}