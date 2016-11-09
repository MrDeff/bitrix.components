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
Loc::loadMessages(__FILE__);

class siteComponentSectionList extends \CBitrixComponent{
	
	protected $cacheAddon = array();
	protected $cacheKeys = array();
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
		
		$dataSections = CIBlockSection::GetList(Array('ID'=>'ASC'), array(
			'IBLOCK_TYPE'=>$this->arParams['IBLOCK_TYPE'],
			'IBLOCK_ID'=>$this->arParams['IBLOCK_ID'],
			'SECTION_ID'=>$this->arParams['SECTION_ID'],
			'ACTIVE' => 'Y',
			'GLOBAL_ACTIVE'=>'Y',
			), false,
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
				
			));
		$dataSections->SetUrlTemplates("", $this->arParams["URL_SECTION"]);
		while ($section = $dataSections->GetNext(true,false))
		{
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
}