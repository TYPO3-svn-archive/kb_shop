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
 * Hook for t3lib_BEfunc
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

$_EXTPATH = t3lib_extMgm::extPath('kb_shop');
require_once($_EXTPATH.'class.tx_kbshop_config.php');
require_once($_EXTPATH.'class.tx_kbshop_abstract.php');

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['DSCache']))	{
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['DSCache'] = array();
}

class tx_kbshop_t3libbefunc	{

	function tx_kbshop_t3libbefunc()	{
		$this->config = t3lib_div::getUserObj('EXT:kb_shop/class.tx_kbshop_config.php:&tx_kbshop_config');
		$this->config->init($this);	
	}


	function getFlexFormDS_postProcessDS(&$dataStructArray, $conf, $row, $table, $fieldName)	{
		$this->saveSQLdebug = $GLOBALS['TYPO3_DB']->debugOutput;
		$GLOBALS['TYPO3_DB']->debugOutput = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['SQLdebug'];
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['DSCache'][$table.'|'.$row['uid']]))	{
			$dataStructArray = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['DSCache'][$table.'|'.$row['uid']];
			$GLOBALS['TYPO3_DB']->debugOutput = $this->saveSQLdebug;
			return;
		}
		if ((substr($table, 0, strlen($this->config->entriesTablePrefix))==$this->config->entriesTablePrefix)&&(!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['TCAmode']))	{
			$this->processEntriesDS($dataStructArray, $conf, $row, $table, $fieldName);
			$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['DSCache'][$table.'|'.$row['uid']] = $dataStructArray;
		} elseif (($table=='tt_content')&&($row['CType']=='list')&&($row['list_type']=='kb_shop_pi1'))	{
			$this->processFEPluginDS($dataStructArray, $conf, $row, $table, $fieldName, true);		// Cached
			$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['DSCache'][$table.'|'.$row['uid']] = $dataStructArray;
		} elseif (($table=='tt_content')&&($row['CType']=='list')&&($row['list_type']=='kb_shop_pi2'))	{
			$this->processFEPluginDS($dataStructArray, $conf, $row, $table, $fieldName, false);		// Uncached
			$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['DSCache'][$table.'|'.$row['uid']] = $dataStructArray;
		}
		$GLOBALS['TYPO3_DB']->debugOutput = $this->saveSQLdebug;
	}

	function processFEPluginDS(&$dataStructArray, $conf, $row, $table, $fieldName, $cached)	{
		$this->propsPid = $this->config->getPropertiesPage();
		$catPidStr = $this->config->getCategoryPages($row['pid']);
		if (is_string($row['flexform']))	{
			$tmpXml = $row['flexform'];
		} elseif (is_array($row['flexform']))	{
			$tmpXml = t3lib_div::array2xml($row['flexform']);
		} elseif (is_string($row['pi_flexform']))	{
			$tmpXml = $row['pi_flexform'];
		} elseif (is_array($row['pi_flexform']))	{
			$tmpXml = t3lib_div::array2xml($row['pi_flexform']);
		} else	{
			$tmpXml = '';
		}
		preg_match('/<field\s+index\="field_table">\s+<value\s+index\="vDEF">(.*)<\/value>/sU', $tmpXml, $matches);
		$tableUid = intval($matches[1]);
		if ($this->propsPid&&$tableUid)	{
			$this->cacheFile = PATH_site.$this->config->typo3tempPath.'kb_shop_pi_DS_cache_'.$tableUid.'_'.($cached?'1':'0').'.ser';
			if (@file_exists($this->cacheFile)&&!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['dontCache'])	{
				$xml = unserialize(t3lib_div::getURL($this->cacheFile));
				if (is_array($xml))	{
					$dataStructArray = $xml;
					$GLOBALS['TCA']['tt_content']['ctrl']['requestUpdate'] .= ($GLOBALS['TCA']['tt_content']['ctrl']['requestUpdate']?',':'').$xml['requestUpdate'];
					return;
				}
			}
			require_once($_EXTPATH.'class.tx_kbshop_category.php');
			$catObj = t3lib_div::makeInstance('tx_kbshop_category');
			$catObj->init($this->config);
			$trow = tx_kbshop_abstract::getRecord($this->config->categoriesTable, $tableUid);
			$tkey = tx_kbshop_misc::getKey($trow);
			$catList = $catObj->getCategoriesRec($tableUid);
			if (is_array($catList)&&count($catList))	{
				require_once($_EXTPATH.'class.tx_kbshop_tcagen.php');
				require_once($_EXTPATH.'class.tx_kbshop_tcagen_pi1.php');
				if (!$cached)	{
					require_once($_EXTPATH.'class.tx_kbshop_tcagen_pi2.php');
				}
				$allProps = array();
				foreach ($catList as $category)	{
					$tcagen = t3lib_div::makeInstance('tx_kbshop_tcagen_pi');
					$tcagen->init($this->config);
					$catObj->getCategoryRootline($category);
					if (is_array($catObj->categoryRootline)&&count($catObj->categoryRootline))	{
						$allProps[$category] = $catObj->getPropertyTree($this->propsPid);
					}
				}
				$tcagen->tableKey = $tkey;
				$xml = $tcagen->renderTCA($allProps);
				$this->config->updateLLLfile($tcagen->LLBuffer);
				if (is_array($xml)&&count($xml))	{
					if (!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['dontCache'])	{
						t3lib_div::writeFile($this->cacheFile, serialize($xml));
					}
					$dataStructArray = $xml;
				}
			}
		}
	}

	function processEntriesDS(&$dataStructArray, $conf, $row, $table, $fieldName)	{
		$this->propsPid = $this->config->getPropertiesPage();
		if ($this->propsPid)	{

			$categories = t3lib_div::intExplode(',', $row['category']);
			sort($categories);
			$md5 = md5(serialize($categories));
			$cache_file = PATH_site.$config->typo3tempPath.'kb_shop_DS_cache_'.substr($md5, 0, 10).'.xml';
			if (@file_exists($cache_file)&&!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['dontCache'])	{
				$xml = unserialize(t3lib_div::getURL($cache_file));
				if (is_array($xml))	{
					t3lib_div::loadTCA($this->config->entriesTable);
					$dataStructArray = $xml;
					$GLOBALS['TCA'][$this->config->entriesTable]['ctrl']['requestUpdate'] .= ($GLOBALS['TCA'][$this->config->entriesTable]['ctrl']['requestUpdate']?',':'').$xml['requestUpdate'];
					return;
				}
			} 
			require_once($_EXTPATH.'class.tx_kbshop_category.php');
			$allProps = array();
			foreach ($categories as $category)	{
				$catObj = t3lib_div::makeInstance('tx_kbshop_category');
				$catObj->init($config);
				$catObj->getCategoryRootline($category);
				if (is_array($catObj->categoryRootline)&&count($catObj->categoryRootline))	{
					$allProps = array_merge($allProps, $catObj->getPropertyTree($this->propsPid));
				}
			}

				// Create required objects.
			require_once($_EXTPATH.'class.tx_kbshop_tcagen.php');
			require_once($_EXTPATH.'class.tx_kbshop_tcagen_flex.php');
			$tcagen = t3lib_div::makeInstance('tx_kbshop_tcagen_flex');
			$tcagen->init($config);

			$xml = $tcagen->renderTCA($allProps);
			if (!is_array($xml))	{
				return;
			}
			if (!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['dontCache'])	{
				t3lib_div::writeFile($cache_file, serialize($xml));
			}
			if ($tcagen->MMtables)	{
				require_once($_EXTPATH.'class.tx_kbshop_sqlengine.php');
				$sqlengine = t3lib_div::makeInstance('tx_kbshop_sqlengine');
				$sqlengine->init($config);
				$data = t3lib_div::getURL(t3lib_extMgm::extPath('kb_shop').'ext_tables.sql');
				$mod = $sqlengine->setMMtableCode($data, $tcagen->MMtables);
				if (strlen($mod)&&strcmp($data, $mod))	{
						// TODO: Respect configuration extension
					t3lib_div::writeFile(t3lib_extMgm::extPath('kb_shop').'ext_tables.sql', $mod);
					$sqlengine->performDBupdates();
				}
			}
			$dataStructArray = $xml;
		}
	}

}

?>
