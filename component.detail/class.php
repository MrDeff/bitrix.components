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

class siteComponentDetail extends \CBitrixComponent{
	protected $cacheAddon = array();
	protected $cacheKeys = array();
	public function onPrepareComponentParams($params)
	{
		$result = [
			'IBLOCK_ID' => (int) $params['IBLOCK_ID'],
			'ELEMENT_ID' => (int)$params['ELEMENT_ID'],
			'URL_SECTION' => $params['URL_SECTION'],
			'CACHE_TYPE' => $params['CACHE_TYPE'],
			'CACHE_TIME' => intval($params['CACHE_TIME']) > 0 ? intval($params['CACHE_TIME']) : 86400,
		];
		
		if($result['ELEMENT_ID']==0)
			throw new Main\ArgumentNullException('Element id');
		
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
		/*
		global $APPLICATION;
		
		$arTitleOptions = array(
			'ADMIN_EDIT_LINK' => '',
			'PUBLIC_EDIT_LINK' => "",
			'COMPONENT_NAME' => $this->getName(),
		);
		$APPLICATION->SetTitle($title, $arTitleOptions);
		
		foreach($dataPath as $path)
		{
				$APPLICATION->AddChainItem($name, $url);
		}
		*/
	}
}