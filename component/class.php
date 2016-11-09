<?php
/**
 * ALTASIB
 * @site http://www.altasib.ru
 * @email dev@altasib.ru
 *
 * @copyright 2006-2016 ALTASIB
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


class siteComponent extends \CBitrixComponent
{
	
	private $componentPage = 'index';
	private $variables = array();
	private $defaultUrlTemplates404;
	private $componentVariables;
	
	private function setDefParam()
	{
		$this->defaultUrlTemplates404 = [
			'index' => '',
			'section' => '#SECTION_ID#/',
			'detail' => '#SECTION_ID#/#ELEMENT_ID#/'
		];
		$this->componentVariables = [
			'SECTION_ID',
			'SECTION_CODE',
			'SECTION_CODE_PATH',
			'ELEMENT_ID',
			'ELEMENT_CODE'
		];
	}
	
	private function getPage()
	{
		if ($this->arParams['SEF_MODE'] == 'Y') {
			$engine = new \CComponentEngine($this);
			if (\Bitrix\Main\Loader::includeModule('iblock')) {
				$engine->addGreedyPart("#SECTION_CODE_PATH#");
				$engine->setResolveCallback(array("CIBlockFindTools", "resolveComponentEngine"));
			}
			
			$arUrlTemplates = CComponentEngine::makeComponentUrlTemplates($this->defaultUrlTemplates404, $this->arParams["SEF_URL_TEMPLATES"]);
			$arVariableAliases = CComponentEngine::makeComponentVariableAliases(array(), $this->arParams["VARIABLE_ALIASES"]);
			
			$this->componentPage = $engine->guessComponentPath(
				$this->arParams["SEF_FOLDER"],
				$arUrlTemplates,
				$this->variables
			);
			
			if(!$this->componentPage)
				$this->componentPage = 'index';
			
			CComponentEngine::initComponentVariables($this->componentPage, $this->componentVariables, $arVariableAliases, $this->variables);
			$this->arResult = array(
				"FOLDER" => $this->arParams["SEF_FOLDER"],
				"URL_TEMPLATES" => $arUrlTemplates,
				"VARIABLES" => $this->variables,
				"ALIASES" => $arVariableAliases
			);
		} else {
			$arVariableAliases = CComponentEngine::MakeComponentVariableAliases(array(), $this->arParams["VARIABLE_ALIASES"]);
			CComponentEngine::initComponentVariables(false, $this->componentVariables, $arVariableAliases, $this->variables);
		
			if(isset($arVariables["ELEMENT_ID"]) && intval($arVariables["ELEMENT_ID"]) > 0)
				$this->componentPage = "detail";
			elseif(isset($arVariables["ELEMENT_CODE"]) && strlen($arVariables["ELEMENT_CODE"]) > 0)
				$this->componentPage = "detail";
			elseif(isset($arVariables["SECTION_ID"]) && intval($arVariables["SECTION_ID"]) > 0)
				$this->componentPage = "section";
			elseif(isset($arVariables["SECTION_CODE"]) && strlen($arVariables["SECTION_CODE"]) > 0)
				$this->componentPage = "section";
			else
				$this->componentPage = 'index';
			
			global $APPLICATION;
			$this->arResult = array(
				"FOLDER" => "",
				"URL_TEMPLATES" => Array(
					"section" => htmlspecialcharsbx($APPLICATION->GetCurPage()) . "?" . $arVariableAliases["SECTION_ID"] . "=#SECTION_ID#",
					"detail" => htmlspecialcharsbx($APPLICATION->GetCurPage()) . "?" . $arVariableAliases["SECTION_ID"] . "=#SECTION_ID#" . "&" . $arVariableAliases["ELEMENT_ID"] . "=#ELEMENT_ID#",
				),
				"VARIABLES" => $this->variables,
				"ALIASES" => $arVariableAliases
			);
		}
	}
	
	public function executeComponent()
	{
		$this->setDefParam();
		$this->getPage();
		$this->includeComponentTemplate($this->componentPage);
	}
}