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

class siteComponentSection extends \CBitrixComponent{
	protected $cacheAddon = array();
	protected $cacheKeys = array('SECTION');
	protected $navParams = array();
	protected $navigation = array();
	public function onPrepareComponentParams($params)
	{
		//todo: add path chain
		$result = [
			'IBLOCK_TYPE' => trim($params['IBLOCK_TYPE']),
			'IBLOCK_ID' => (int) $params['IBLOCK_ID'],
			'SECTION_ID' => (int) $params['SECTION_ID'],
			'SECTION_CODE' => trim($params['SECTION_CODE']),
			'URL_DETAIL' => trim($params['URL_DETAIL']),
			'URL_SECTION' => trim($params['URL_SECTION']),
			'PAGE_ELEMENT_COUNT' => (int)$params['PAGE_ELEMENT_COUNT'] >0 ? $params['PAGE_ELEMENT_COUNT'] : 10,
			'SET_TITLE' => ($params['SET_TITLE']=='Y'),
			'CACHE_TYPE' => $params['CACHE_TYPE'],
			'CACHE_TIME' => intval($params['CACHE_TIME']) > 0 ? intval($params['CACHE_TIME']) : 86400,
		];
		
		$this->navParams = array(
			"nPageSize" => $result['PAGE_ELEMENT_COUNT'],
			"bDescPageNumbering" => 'N',
			"bShowAll" => 'N',
		);
		$this->navigation = CDBResult::GetNavParams($this->navParams);
		
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
		
		return !($this->startResultCache(false, array($this->cacheAddon,$this->navigation)));
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
		
		$obSection = CIBlockSection::GetList(array(), $filterSection, false, array('ID','IBLOCK_ID','NAME'));
		$obSection->SetUrlTemplates("", $this->arParams["URL_SECTION"]);
		if(!$this->arResult['SECTION'] = $obSection->GetNext(true,false))
			throw new Exception('section null');
		else{
			$ipropValues = new Iblock\InheritedProperty\SectionValues($this->arResult['SECTION']["IBLOCK_ID"], $this->arResult['SECTION']["ID"]);
			$this->arResult['SECTION']["IPROPERTY_VALUES"] = $ipropValues->getValues();
			$this->arParams['SECTION_ID'] = $this->arResult['SECTION']['ID'];
		}
		
		$this->arResult['SECTION']['PATH'] = array();
		$obPath = CIBlockSection::GetNavChain($this->arResult['SECTION']["IBLOCK_ID"], $this->arParams['SECTION_ID'],array('ID','IBLOCK_ID','NAME','CODE','SECTION_PAGE_URL'));
		$obPath->SetUrlTemplates("", $this->arParams["URL_SECTION"], '');
		while ($arPath = $obPath->GetNext(true,false)) {
			$ipropValues = new Iblock\InheritedProperty\SectionValues($this->arParams["IBLOCK_ID"], $arPath["ID"]);
			$arPath["IPROPERTY_VALUES"] = $ipropValues->getValues();
			$this->arResult["SECTION"]["PATH"][] = $arPath;
		}
		
		$sort = array('SORT'=>'ASC');
		$filter = array(
			'IBLOCK_TYPE'=>$this->arParams['IBLOCK_TYPE'],
			'IBLOCK_ID'=>$this->arParams['IBLOCK_ID'],
			'ACTIVE' => 'Y',
			'GLOBAL_ACTIVE'=>'Y',
			'SECTION_ID' => $this->arParams['SECTION_ID']
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
			'DETAIL_PAGE_URL',
			'DETAIL_PICTURE',
			'PREVIEW_PICTURE',
			'PROPERTY_*'
		);

		$element = CIBlockElement::GetList($sort,$filter,false,$this->navParams,$select);
		while ($obElement = $element->GetNextElement(true,false)){
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
				$this->arResult['SECTION']["ID"],
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
			$this->arResult['ELEMENTS'][] = $dataElement;
		}
		//todo: название постаринчки
		$this->arResult["NAV_STRING"] = $element->GetPageNavStringEx($navComponentObject, '', '', false,$this);
		$this->arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();
		$this->arResult["NAV_RESULT"] = $element;
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
				$this->after();
			}
		}
		catch (Exception $e)
		{
			$this->abortResultCache();
			ShowError($e->getMessage());
		}
	}
	
	private function after(){
		
		global $USER,$APPLICATION;
		
		if($USER->IsAuthorized() && $APPLICATION->GetShowIncludeAreas() && Main\Loader::includeModule("iblock")){
			$buttons = CIBlock::GetPanelButtons(
				$this->arParams["IBLOCK_ID"],
				0,
				$this->arResult["SECTION"]["ID"],
				array("RETURN_URL" =>  array(
					"add_section" => ($this->arParams["URL_SECTION"] != '' ? $this->arParams["URL_SECTION"]: CIBlock::GetArrayByID($this->arParams["IBLOCK_ID"], "SECTION_PAGE_URL")),
				),'SECTION_BUTTONS'=>true)
			);
			$this->addIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $buttons));
		}
		
		if($this->arParams['SET_TITLE']) {
			$arTitleOptions = array(
				'ADMIN_EDIT_LINK' => '',
				'PUBLIC_EDIT_LINK' => "",
				'COMPONENT_NAME' => $this->getName(),
			);
			if ($this->arResult['SECTION']["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"] != "")
				$APPLICATION->SetTitle($this->arResult['SECTION']["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"], $arTitleOptions);
			else
				$APPLICATION->SetTitle($this->arResult['SECTION']['NAME'], $arTitleOptions);
			
			//todo: set seo
		}
		
		//todo: params
		foreach($this->arResult['SECTION']['PATH'] as $path)
		{
				$APPLICATION->AddChainItem($path['NAME'], $path['SECTION_PAGE_URL']);
		}
	}
}