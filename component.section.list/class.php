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

class siteComponentSectionList extends \CBitrixComponent{
	
	protected $cacheAddon = array();
	protected $cacheKeys = array('PARENT_SECTION');
	public function onPrepareComponentParams($params)
	{
		$result = [
			'IBLOCK_TYPE' => trim($params['IBLOCK_TYPE']),
			'IBLOCK_ID' => (int) $params['IBLOCK_ID'],
			'SECTION_ID' => (int) $params['SECTION_ID'],
			'SECTION_CODE' => trim($params['SECTION_CODE']),
			'URL_SECTION' => trim($params['URL_SECTION']),
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
		$filter = array(
			'IBLOCK_TYPE'=>$this->arParams['IBLOCK_TYPE'],
			'IBLOCK_ID'=>$this->arParams['IBLOCK_ID'],
			'ACTIVE' => 'Y',
			'GLOBAL_ACTIVE'=>'Y',
		);
		if($this->arParams['SECTION_ID'] == 0 && strlen($this->arParams['SECTION_CODE'])>0){
			$this->arResult['PARENT_SECTION'] = CIBlockSection::GetList(array(), array_merge($filter,array('=CODE'=>$this->arParams['SECTION_CODE'])), false, array('ID','IBLOCK_ID','NAME'))->Fetch();
			$this->arParams['SECTION_ID'] = $this->arResult['PARENT_SECTION']['ID'];
		}
		$dataSections = CIBlockSection::GetList(Array('left_margin'=>'ASC'), array_merge($filter,array('SECTION_ID'=>$this->arParams['SECTION_ID'])), false,
			array(
				'ID',
				'CODE',
				'IBLOCK_ID',
				'IBLOCK_SECTION_ID',
				'NAME',
				'PICTURE',
				'DESCRIPTION',
				'DESCRIPTION_TYPE',
				'DEPTH_LEVEL',
				'SECTION_PAGE_URL',
				'DETAIL_PICTURE'
			
			)
		);
		$dataSections->SetUrlTemplates("", $this->arParams["URL_SECTION"]);
		while ($section = $dataSections->GetNext(true,false))
		{
			$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($section["IBLOCK_ID"], $section["ID"]);
			$section["IPROPERTY_VALUES"] = $ipropValues->getValues();
			
			Iblock\Component\Tools::getFieldImageData(
				$section,
				array('PICTURE','DETAIL_PICTURE'),
				Iblock\Component\Tools::IPROPERTY_ENTITY_SECTION,
				'IPROPERTY_VALUES'
			);
			
			$buttons = CIBlock::GetPanelButtons(
				$section["IBLOCK_ID"],
				0,
				$section["ID"],
				array("SESSID"=>false)
			);
			$section["EDIT_LINK"] = $buttons["edit"]["edit_section"]["ACTION_URL"];
			$section["DELETE_LINK"] = $buttons["edit"]["delete_section"]["ACTION_URL"];
			
			$this->arResult['SECTIONS'][] = $section;
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
		}
		catch (Exception $e)
		{
			$this->abortResultCache();
			ShowError($e->getMessage());
		}
	}
	
	protected function after(){
		global $USER,$APPLICATION;
		if($USER->IsAuthorized() && $APPLICATION->GetShowIncludeAreas() && Main\Loader::includeModule("iblock")){
			//todo: мне не нравяться эти педали, возвращают и делают слишком много... надо что-то придумать
			$buttons = CIBlock::GetPanelButtons(
				$this->arParams["IBLOCK_ID"],
				0,
				$this->arResult["PARENT_SECTION"]["ID"],
				array("RETURN_URL" =>  array(
					"add_section" => ($this->arParams["URL_SECTION"] != '' ? $this->arParams["URL_SECTION"]: CIBlock::GetArrayByID($this->arParams["IBLOCK_ID"], "SECTION_PAGE_URL")),
				),'SECTION_BUTTONS'=>true)
			);
			$this->addIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $buttons));
		}
	}
}