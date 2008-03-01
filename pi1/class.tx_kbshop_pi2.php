<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Bernhard Kraft (kraftb@kraftb.at)
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
 * Plugin 'Shop (Uncached)' for the 'kb_shop' extension.
 *
 * @author	Bernhard Kraft <kraftb@kraftb.at>
 */

require_once (t3lib_extMgm::extPath('kb_shop').'pi1/class.tx_kbshop_pi1.php');

class tx_kbshop_pi2 extends tx_kbshop_pi1	{
	var $isSearch = true;
	var $searchParts = array();


	function setSearchCriteria()	{
		global $TCA;
		t3lib_div::loadTCA($this->config->entriesTable);
		if (!$crits)	{
			$crits = tx_kbshop_abstract::getFlexformChilds($this->cObj->data['pi_flexform'], 'list_searchfield_section', array('list_searchfield_item'), '*', 'search', $this->config->lDEF, $this->config->vDEF);
		}
		if (is_array($crits)&&count($crits))	{
			foreach ($crits as $idx => $crit)	{
				$value = $this->piVars['s'];
				$f = $crit['field_search_field'];
				if (substr($f, 0, $l = strlen($this->config->piComparePrefix))===$this->config->piComparePrefix)	{
					$f = substr($f, $l);
					$dynField = true;
				} 
				list($f, $t) = explode('___', $f, 2);
				if (!strlen($t))	{
					$t = $this->config->entriesTable;
					$sectionField = '';
				} else	{
					list(, $tmp) = explode('___', $t, 2);
					$sectionField = substr($tmp, 0, -strlen($this->configSectionTablePostfix));
				}
					// Generate hash of table and field:
				if ($this->config->pi_md5TableAndFieldNames)	{
					$thash = substr(md5($t), 0, $this->config->pi_md5TableAndFieldNames);
					$fhash = substr(md5($f), 0, $this->config->pi_md5TableAndFieldNames);
				} else	{
					$thash = $t;
					$fhash = $f;
				}
				$fArr = $this->getField($f, $sectionField);
				switch ($fArr['type'])	{
					case 9:		// dbrel
						$ot = $t;
						$t = $this->pi_getFFvalue($fArr['flexform'], 'field_table', 'sDEF', $this->config->lDEF, $this->config->vDEF);
						$of = $f;
						$f = $TCA[$t]['ctrl']['label'];
						$compareStr = '###LABELFIELDNAME### LIKE \'%###VALUE###%\'';
					break;
					case 8:		// String
					case 2:		// Text
					case 3:		// RTE
					case 11:		// File
						$compareStr = '###FIELDNAME### LIKE \'%###VALUE###%\'';
					break;
					case 4:		// Decimal
					case 5:		// Integer
						$compareStr = '###FIELDNAME### = \'###VALUE###\'';
					break;
					default:
							return str_replace('###TYPE###', 'Z'.$fArr['type'], $this->pi_getLL('error_invalid_criteria_field_unknown'));
					break;
				}
				$value = $this->cObj->insertData($value);
				$compareStr = str_replace('###VALUE###', $GLOBALS['TYPO3_DB']->quoteStr($GLOBALS['TYPO3_DB']->escapeStrForLike($value, $t), $t), $compareStr);
				$compareStr = str_replace('###RAWVALUE###', str_replace('\\%', '%', $GLOBALS['TYPO3_DB']->quoteStr($value, $t)), $compareStr);
				$this->searchParts[$idx] = array(
					'table' => $t,
					'orig_table' => $ot,
					'field' => $f,
					'orig_field' => $of,
					'compareStr' => $compareStr,
					'row' => $fArr,
					'value' => $value,
					'MM' => $mmt,
				);
			}
		}
	}


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/'.$_EXTKEY.'/pi1/class.tx_'.$_EXTKEY_.'_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/'.$_EXTKEY.'/pi1/class.tx_'.$_EXTKEY_.'_pi2.php']);
}



?>
