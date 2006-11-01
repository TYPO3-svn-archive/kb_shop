<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Kraft Bernhard (kraftb@kraftb.at)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * item proc methods
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

require_once(PATH_t3lib.'class.t3lib_page.php');

require_once($_EXTPATH.'class.tx_kbshop_config.php');
class tx_kbshop_itemproc	{

	function user_TCAitemsProcFunc_propertyParentValue($param)	{
		global $TCA;
		list($parent) = explode(',', $param['row']['parent'], 2);
		$parent = intval($parent);
		$pageObj = t3lib_div::makeInstance('t3lib_pageSelect');
		$this->config = t3lib_div::getUserObj('EXT:kb_shop/class.tx_kbshop_config.php:&tx_kbshop_config');
		$this->config->init($this);
		if ($parent)	{
			$parent = $pageObj->getRawRecord($this->config->propertiesTable, $parent);
			if (is_array($parent))	{
				$flex = t3lib_div::xml2array($parent['flexform']);
				if (is_array($flex))	{
					if (is_array($flex['data']['sDEF'][$this->config->lDEF]['list_value_section']['el']))	{
						foreach ($flex['data']['sDEF'][$this->config->lDEF]['list_value_section']['el'] as $idx => $subarr)	{
							$label = $subarr['list_value_field']['el']['field_value'][$this->config->vDEF];
							$index = $subarr['list_value_field']['el']['field_index'][$this->config->vDEF];
							$param['items'][] = array($label, $index);
						}
					}
				}
			}
		}
		return $param;
	}
	
	function user_TCAitemsProcFunc_propertySysLanguageModeValue($param)	{
//		print_r($param['items']);
//		exit();
	/*
	 * TYPEADD
	 *
	 * Here you can remove values out of the language-mode selector box when editing a property
	 *
	 * The most common thing is to remove the option "prefixLangTitle" when a non-text field has
	 * been choosen
	 *
	 */
		switch ($param['row']['type'])	{
			case 1:		// Select
			case 4:		// Decimal
			case 5:		// Integer
			case 6:		// Checkbox
			case 7:		// Date
			case 12:		// Time
			case 13:		// Timesec
			case 14:		// Date/Time
			case 15:		// Year
			case 9:		// Db-rel
			case 10:		// multi-check
			case 11:		// file
				unset($param['items'][4]);
			break;
			case 2:		// Text
			case 3:		// RTE
			case 8:		// String 
			break;
			case '':
			break;
			default:
				echo 'Undefined property type "'.$param['row']['type'].'" ! ('.__FILE__.':'.__CLASS__.'->'.__FUNCTION__.' @ '.__LINE__.')';
				exit();
			break;
		}
		return $param;
	}


	function setIndex($PA, $pObj)	{
		if (!is_array($GLOBALS['TYPO3_TEMP_VARS']['EXT']['kb_shop']['usedIndexes']))	{
			$GLOBALS['TYPO3_TEMP_VARS']['EXT']['kb_shop']['usedIndexes'] = array();
		}
		if (intval($PA['itemFormElValue']))	{
			$GLOBALS['TYPO3_TEMP_VARS']['EXT']['kb_shop']['usedIndexes'][] = intval($PA['itemFormElValue']);
			return '<input type="hidden" name="'.$PA['itemFormElName'].'" value="'.intval($PA['itemFormElValue']).'" />'.chr(10);
		}
		$flex = t3lib_div::xml2array($PA['row']['flexform']);
		$idx = '';
		if (is_array($flex))	{
			$set = array();
			if (is_array($flex['data']['sDEF'][$this->config->lDEF]['list_value_section']['el']))	{
				foreach ($flex['data']['sDEF'][$this->config->lDEF]['list_value_section']['el'] as $idx => $subarr)	{
					$idx = intval($subarr['list_value_field']['el']['field_index'][$this->config->vDEF]);
					if ($idx)	{
						$set[] = $idx;
					}
				}
			}
			$set = array_unique(array_merge($set, $GLOBALS['TYPO3_TEMP_VARS']['EXT']['kb_shop']['usedIndexes']));
			if (count($set))	{
				sort($set);
				$last = array_pop($set)+1;
			} else	{
				$last = 1;
			}
			$set[] = $last;
			$GLOBALS['TYPO3_TEMP_VARS']['EXT']['kb_shop']['usedIndexes'] = $set;
			return '<input type="hidden" name="'.$PA['itemFormElName'].'" value="'.$last.'" />'.chr(10);
		}
		return '';
	}

}



?>
