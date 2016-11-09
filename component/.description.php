<?php
/**
 * ALTASIB
 * @site http://www.altasib.ru
 * @email dev@altasib.ru
 *
 * @copyright 2006-2016 ALTASIB
 */
if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$arComponentDescription = array(
    'NAME' => Loc::getMessage('SITE_COMP_COMP_NAME'),
    'DESCRIPTION' => Loc::getMessage('SITE_COMP_COMP_NAME_DESCRIPTION'),
    'SORT' => 10,
    'PATH' => array(
        'ID' => 'site',
        'NAME' => Loc::getMessage('SITE_COMP'),
        'SORT' => 10,
        'CHILD' => array(
            'ID' => 'cmp',
            'NAME' => Loc::getMessage('SITE_COMP_COMP'),
            'SORT' => 10
        )
    )
);