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
 * TCA Management class.
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

$_EXTPATH = t3lib_extMgm::extPath('kb_shop');
require_once(PATH_t3lib.'class.t3lib_befunc.php');
require_once($_EXTPATH.'class.tx_kbshop_config.php');
require_once($_EXTPATH.'class.tx_kbshop_abstract.php');
require_once($_EXTPATH.'class.tx_kbshop_misc.php');

class tx_kbshop_tcamgm	{
	var $config = false;
	var $pageId = false;
	var $cacheFile = false;
	var $allowTableOnPages = array();


	function tx_kbshop_tcamgm()	{
	}


	function getExtTablesCacheFile($virtual=0)	{
		$this->config = t3lib_div::getUserObj('EXT:kb_shop/class.tx_kbshop_config.php:&tx_kbshop_config');
		$this->config->init($this);	

		$allowTableOnPages = array();
		$cacheFile = PATH_site.$this->config->typo3tempPath.'kb_shop_ext_tables_cache.ser';
		if (@file_exists($cacheFile)&&!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['dontCache'])	{	
			$tcaArr = unserialize(t3lib_div::getURL($cacheFile));
			return array($tcaArr['tca'], $tcaArr['allowTableOnPages']);
		} else	{
			$tca = array();
			$tables = tx_kbshop_abstract::getRecordsByField($this->config->categoriesTable, 'parent', 0, '', ' AND sys_language_uid=0 AND virtual='.$virtual);
			if (is_array($tables)&&count($tables))	{
				t3lib_div::loadTCA($this->config->categoriesTable);
				foreach ($tables as $trow)	{
					$tkey = tx_kbshop_misc::getKey($trow);
					$tcaFile = $this->config->configExtBasePath.'tca_'.$tkey.'.php';
					$labels = t3lib_div::trimExplode(',', $trow['labelProperty']);
					$labelProp = tx_kbshop_abstract::getRecord($this->config->propertiesTable, $labels[0]);
					if (is_array($labelProp))	{
						$lkey = tx_kbshop_misc::getKey($labelProp);
						$label = $this->config->fieldPrefix.$lkey;
					} else	{
						$label = 'uid';
					}
					$labels_alt = '';
					foreach ($labels as $idx => $lab)	{
						if (!$idx)	continue;
						$lProp = tx_kbshop_abstract::getRecord($this->config->propertiesTable, $lab);
						$lkey = tx_kbshop_misc::getKey($lProp);
						$labels_alt .= (strlen($labels_alt)?',':'').$this->config->fieldPrefix.$lkey;
					}
					$this->LLBuffer[0]['table_'.$tkey] = $trow['title'];
					$localTables = tx_kbshop_abstract::getRecordsByField($this->config->categoriesTable, 'l18n_parent', $trow['uid']);
					if (is_array($localTables))	{
						foreach ($localTables as $ltrow)	{
							$this->LLBuffer[$ltrow['sys_language_uid']]['table_'.$tkey] = $ltrow['title'];
						}
					}
						// --- T3-BUGFIX --- begin ---
					$alabel = $label;
					if (strlen($labels_alt))	{
						$label = 'uid';
					}
						// --- T3-BUGFIX --- end ---
					$subtca = array(
						'ctrl' => Array (
//							'title' => tx_kbshop_abstract::csConv($trow['title'], $this->config->currentCharset, 'iso-8859-1'),
							'title' => 'LLL:EXT:'.$this->config->configExt.'/locallang_dyn.xml:table_'.$tkey,
							'label' => $label,
							'tstamp' => 'tstamp',
							'crdate' => 'crdate',
							'cruser_id' => 'cruser_id',
							'delete' => 'deleted',
							'requestUpdate' => 'category',
							'tableCategoryUid' => $trow['uid'],
							'enablecolumns' => Array (		
								'disabled' => 'hidden',	
								'starttime' => 'starttime',	
								'endtime' => 'endtime',	
								'fe_group' => 'fe_group',
							),
							'dynamicConfigFile' => $tcaFile,
							'iconfile' => $trow['icon']?('../'.$GLOBALS['TCA'][$this->config->categoriesTable]['columns']['icon']['config']['uploadfolder'].'/'.$trow['icon']):(t3lib_extMgm::extRelPath('kb_shop').'icon_tx_kbshop_category.gif'),
						),
						'feInterface' => Array (
							'fe_admin_fieldList' => 'hidden, starttime, endtime, fe_group, title',
						)
					);
					if (strlen($labels_alt))	{
						$subtca['ctrl']['label_alt'] = $alabel.','.$labels_alt;
						$subtca['ctrl']['label_alt_force'] = 1;
					}
					if (count($localTables))	{
							// Localize table.
						$subtca['ctrl']['languageField'] = 'sys_language_uid';
						$subtca['ctrl']['transOrigPointerField'] = 'l18n_parent';
						$subtca['ctrl']['transOrigDiffSourceField'] = 'l18n_diffsource';
						$this->config->localizeTable[$tkey] = 1;
					}
					$sortProp = tx_kbshop_abstract::getRecord($this->config->propertiesTable, $trow['sortingProperty']);
					$sortDir = $trow['sortingDirection']?'DESC':'ASC';
					if (is_array($sortProp))	{
						$skey = tx_kbshop_misc::getKey($sortProp);
						$sorting = $this->config->fieldPrefix.$skey;
						$subtca['ctrl']['default_sortby'] = 'ORDER BY '.$sorting.' '.$sortDir;
					} else	{
						switch ($trow['sortingProperty'])	{
							case -1:
								$subtca['ctrl']['default_sortby'] = 'ORDER BY tstamp '.$sortDir;
							break;
							case -2:
								$subtca['ctrl']['default_sortby'] = 'ORDER BY crdate '.$sortDir;
							break;
							default:
								if (!intval($trow['virtual']))	{
									$subtca['ctrl']['sortby'] = 'sorting';
								}
							break;
						}
					}
					$tca[$this->config->entriesTablePrefix.$tkey] = $subtca;
					$tca_php = '<?php
if (!defined (\'TYPO3_MODE\')) 	die (\'Access denied.\');
$tcaObj = t3lib_div::getUserObj(\'EXT:kb_shop/class.tx_kbshop_tcamgm.php:&tx_kbshop_tcamgm\');
$tca = $tcaObj->getTCACacheFile('.$trow['uid'].');
if (is_array($tca))	{
	$GLOBALS[\'TCA\'][\''.$this->config->entriesTablePrefix.$tkey.'\'] = $tca;
}
?>';
					if ($trow['allowOnPages'])	{
						$this->allowTableOnPages[$this->config->entriesTablePrefix.$tkey] = 1;
					}
						// Let the tables get generated.
					$GLOBALS['TCA'][$this->config->entriesTablePrefix.$tkey] = $subtca;
					$this->getTCACacheFile($trow['uid']);
					t3lib_div::writeFile($tcaFile, $tca_php);
				}
			}
			$allowTableOnPages = array_keys($this->allowTableOnPages);
			if (!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['dontCache'])	{
				t3lib_div::writeFile($cacheFile, serialize(array('tca' => $tca, 'allowTableOnPages' => $allowTableOnPages)));
			}
			$this->config->updateLLLfile($this->LLBuffer);
			return  array($tca, $allowTableOnPages);
		}
		return false;
	}

	function setPageId()	{
		if (TYPO3_MODE=='BE')	{
			$this->pageId = intval(t3lib_div::_GP('id'));
			if (!$this->pageId)	{
				$edit = t3lib_div::_GP('edit');
				if (is_array($edit)&&count($edit))	{
					reset($edit);
					$table = key($edit);
					$edit = array_shift($edit);
					reset($edit);
					$recId = intval(key($edit));
					$mode = array_shift($edit);
					if ($mode=='new')	{
						$this->pageId = $recId;
					} else	{
						$rec = tx_kbshop_abstract::getRecord($table, $recId);
						$this->pageId = $rec['pid'];
					}
				}
			}
		} elseif (TYPO3_MODE=='FE')	{
			$this->pageId = $GLOBALS['TSFE']->id;
			/*
			$this->setDummySections();
			return array(
				'columns' => array(),
			);
			*/
		}
	}

	function getTCACacheFile($baseCatUid)	{
		$this->config = t3lib_div::getUserObj('EXT:kb_shop/class.tx_kbshop_config.php:&tx_kbshop_config');
		$this->config->init($this);	
		$this->propsPid = $this->config->getPropertiesPage();
		$category = tx_kbshop_abstract::getRecord($this->config->categoriesTable, $baseCatUid);
		$tkey = tx_kbshop_misc::getKey($category);
		if ($this->propsPid)	{
			$this->cacheFile = PATH_site.$this->config->typo3tempPath.'kb_shop_TCA_cache_'.$baseCatUid.'.ser';
			if (@file_exists($this->cacheFile)&&!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['dontCache'])	{
				$tca = unserialize(t3lib_div::getURL($this->cacheFile));
				return $tca;
			} 
				// We have to generate the TCA.
			require_once($_EXTPATH.'class.tx_kbshop_category.php');
			require_once($_EXTPATH.'class.tx_kbshop_tcagen.php');
			require_once($_EXTPATH.'class.tx_kbshop_tcagen_tca.php');
			$catObj = t3lib_div::makeInstance('tx_kbshop_category');
			$catObj->init($this->config);
			$tcagen = t3lib_div::makeInstance('tx_kbshop_tcagen_tca');
			$tcagen->init($this->config);
			$allProps = array();
			$allCats = $catObj->getCategoriesRec($baseCatUid);
			if (is_array($allCats))	{
				foreach ($allCats as $actCat)	{
					$catObj->getCategoryRootline($actCat);
					if (is_array($catObj->categoryRootline)&&count($catObj->categoryRootline))	{
						$allProps[$actCat] = $catObj->getPropertyTree($this->propsPid);
					}
				}
			}
			$tca = $tcagen->renderTCA($allProps, $tkey);
			if (is_array($tca)&&count($tca))	{
				if (!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['dontCache'])	{
					t3lib_div::writeFile($this->cacheFile, serialize($tca));
				}
				tx_kbshop_misc::loadSectionTCA($tca['columns']);
					// Create required directories
				tx_kbshop_misc::createDirs($tcagen->uploadFolders);
					// Update SQL (if necessary)
				require_once($_EXTPATH.'class.tx_kbshop_sqlengine.php');
				$sqlengine = t3lib_div::makeInstance('tx_kbshop_sqlengine');
				$sqlengine->init($this->config);
				$sqlengine->writeExtTablesSQL($tcagen->MMtables, $tkey);
				$ext_section_tables = '<?
if (!defined (\'TYPO3_MODE\')) 	die (\'Access denied.\');
';
				if (is_array($sqlengine->relTables))	{
					$subtca = $this->loadTCA($this->config->configExtBasePath.'ext_section_tables.php');
					foreach ($sqlengine->relTables as $table => $arr)	{
						if ($this->allowTableOnPages[$this->config->entriesTablePrefix.$tkey])	{
							$this->allowTableOnPages[$table] = 1;
						}
						unset($subtca[$table]);
						$subtca[$table] = array(
							'ctrl' => $GLOBALS['TCA'][$table]['ctrl'],
						);
						$sectionTCAFile = $this->config->configExtBasePath.'tca_section_'.$table.'.php';
						$sectionCacheFile = PATH_site.$this->config->typo3tempPath.'kb_shop_TCA_section_cache_'.$table.'.ser';
						$subtca[$table]['ctrl']['dynamicConfigFile'] = $sectionTCAFile;
						if (!is_file($sectionTCAFile))	{
							$sphp = '<?php
if (!defined (\'TYPO3_MODE\')) 	die (\'Access denied.\');
$GLOBALS[\'TCA\'][\''.$table.'\'] = unserialize(t3lib_div::getURL(\''.$sectionCacheFile.'\'));
?>';
							t3lib_div::writeFile($sectionTCAFile, $sphp);
							t3lib_div::writeFile($sectionCacheFile, serialize($GLOBALS['TCA'][$table]));
						}
					}
					if (is_array($subtca))	{
						foreach ($subtca as $table => $subtcaArr)	{
							$ext_section_tables .= $this->genTCACode($subtcaArr, $table);
						}
					}
					$ext_section_tables .= '
?>';
					t3lib_div::writeFile($this->config->configExtBasePath.'ext_section_tables.php', $ext_section_tables);
				}
				return $tca;
			}
		} 
			// Return dummy array
		$this->setDummySections();
		return array(
			'ctrl' => $GLOBALS['TCA'][$this->config->entriesTablePrefix.$tkey]['ctrl'],
			'columns' => array(),
		);
	}

	function setDummySections()	{
		require(t3lib_extMgm::extPath('kb_shop').'ext_section_tables.php');
		if (is_array($TCA))	{
			foreach ($TCA as $section_table => $arr)	{
				$GLOBALS['TCA'][$section_table]['columns'] = array();
			}
		}
	}

	
	function genTCACode($tca, $table, $level = 0, $variable = '')	{
		if (!$level)	{
			if ($variable)	{
				$code .= $variable.' = Array ('.chr(10);
			} else	{
				$code .= '$GLOBALS[\'TCA\'][\''.$table.'\'] = Array ('.chr(10);
			}
		}
		$level++;
		foreach ($tca as $idx => $sub)	{
			$code .= str_repeat(chr(9), $level).'\''.$idx.'\' => ';
			if (is_string($sub))	{
				$sub = str_replace('\'', '\\\'', $sub);
				$code .= '\''.$sub.'\','.chr(10);
			} elseif (is_int($sub))	{
				$code .= $sub.','.chr(10);
			} elseif (is_bool($sub))	{
				$code .= ($sub?'true':'false').','.chr(10);
			} elseif (is_array($sub))	{
				$code .= 'Array ('.chr(10);
				$code .= $this->genTCACode($sub, $table, $level);
			}
		}
		$level--;
		$code .= str_repeat(chr(9), $level).')';
		if (!$level)	{
			$code .= ';'.chr(10);
		} else	{
			$code .= ','.chr(10);
		}
		return $code;
	}
					

	function loadTCA($file)	{
		@include($file);
		$data = t3lib_div::getURL($file);
		$subtca = array();
		if (preg_match_all('/^\$GLOBALS\[\'TCA\'\]\[\'([a-zA-Z0-9_]+)\'\]/m', $data, $matches, PREG_SET_ORDER)>0)	{
			foreach ($matches as $match)	{
				$subtca[$match[1]] = $GLOBALS['TCA'][$match[1]];
			}
		}
		return $subtca;
	}

	function getStoragePid()	{
		$page = tx_kbshop_abstract::getPage($this->pageId);
		if ($page['storage_pid'])	{
			return $page['storage_pid'];
		}
		return false;
	}


}


?>
