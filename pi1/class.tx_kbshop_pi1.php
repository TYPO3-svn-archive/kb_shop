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
 * Plugin 'sdfds' for the 'kb_shop' extension.
 *
 * @author	Bernhard Kraft <kraftb@kraftb.at>
 */


$_EXTKEY = 'kb_shop';
$_EXTKEY_ = str_replace('_', '', $_EXTKEY);
$_EXTPATH = t3lib_extMgm::extPath($_EXTKEY);
require_once(PATH_tslib.'class.tslib_pibase.php');
require_once($_EXTPATH.'class.tx_'.$_EXTKEY_.'_abstract.php');
require_once($_EXTPATH.'class.tx_'.$_EXTKEY_.'_misc.php');
require_once($_EXTPATH.'class.tx_'.$_EXTKEY_.'_config.php');
require_once($_EXTPATH.'class.tx_'.$_EXTKEY_.'_category.php');
require_once($_EXTPATH.'class.tx_'.$_EXTKEY_.'_tcagen.php');
require_once($_EXTPATH.'pi1/class.tx_'.$_EXTKEY_.'_t3tt.php');
require_once($_EXTPATH.'class.tx_'.$_EXTKEY_.'_t3lib_tcemain.php');
require_once (PATH_t3lib.'class.t3lib_tceforms.php');
require_once (PATH_t3lib.'class.t3lib_iconworks.php');
require_once (PATH_t3lib.'class.t3lib_loaddbgroup.php');
require_once (PATH_t3lib.'class.t3lib_transferdata.php');
require_once (PATH_t3lib.'class.t3lib_userauthgroup.php');
require_once (t3lib_extMgm::extPath('lang').'lang.php');

class tx_kbshop_pi1 extends tslib_pibase {
	var $prefixId = 'tx_kbshop_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_kbshop_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'kb_shop';	// The extension key.
	var $_extKey = 'kbshop';	// The extension key.
	var $pi_checkCHash = TRUE;
	var $properties = false;
	var $whereParts = array();
	var $criteriaItems = array();
	var $doCache = true;
	var $resetPIvars = array('p' => false, 'c' => false, 'v' => false);
	var $tskey = 'listView';
	var $onlyIdx = 0;
	var $onlyRes = false;
	var $OLmode = 'hideNonTranslated';
	var $doublePostDelay = 600;
	var $alreadyPosted = 0;
	var $savedRecords = 0;
	var $hsc = true;
	var $hookObjects = array();
	var $labelCache = array();
	var $labelCacheMM = array();
	var $formTables = false;

	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{
		$this->saveSQLdebug = $GLOBALS['TYPO3_DB']->debugOutput;
		$GLOBALS['TYPO3_DB']->debugOutput = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['SQLdebug'];
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_initPIflexForm();
		$this->pi_loadLL();
		$this->cObj->origData = $this->cObj->data;
		$this->pluginPageRec = tx_kbshop_abstract::getRecord('pages', $this->cObj->data['pid']);

		$this->config = t3lib_div::getUserObj('EXT:'.$_EXTKEY.'/class.tx_'.$this->_extKey.'_config.php:&tx_'.$this->_extKey.'_config');
		$subconf = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'field_subconf', 'sDEF', $this->config->lDEF, $this->config->vDEF);
		if (strlen($subconf)&&is_array($conf[$subconf.'.']))	{
			$this->conf = $conf = $conf[$subconf.'.'];
		}
		$this->config->init($this);	

		$this->initHooks();

		if ($d = intval($this->conf['forms.']['doublePostDelay']))	{
			$this->doublePostDelay = $d;
		}
	
		$this->prodPagesStr = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'field_pages', 'sDEF', $this->config->lDEF, $this->config->vDEF);
		$recursive = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'field_pages_recursive', 'sDEF', $this->config->lDEF, $this->config->vDEF));
		if ($recursive)	{
			$this->prodPagesStr = $this->pi_getPidList($this->prodPagesStr, $recursive);
		}
		$this->prodPages = t3lib_div::intExplode(',', $this->prodPagesStr);

		$tuid = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'field_table', 'sDEF', $this->config->lDEF, $this->config->vDEF));
		$this->listTable = tx_kbshop_abstract::getRecord($this->config->categoriesTable, $tuid);
		if (!(is_array($this->listTable)&&count($this->listTable)))	{
			$GLOBALS['TYPO3_DB']->debugOutput = $this->saveSQLdebug;
			return $this->pi_getLL('error_no_table');
		}
		$this->tableKey = tx_kbshop_misc::getKey($this->listTable);
		$this->config->entriesTable = $this->config->entriesTablePrefix.$this->tableKey;

		$content = '';	
		$fatalError = '';	

		$this->localcObj = clone($this->cObj);

		$this->showUid = intval($this->piVars['v']);
		$disableSingle = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'field_disableSingleView', 'sDEF', $this->config->lDEF, $this->config->vDEF));
		if (!$disableSingle && $this->showUid)	{
			$this->tskey = 'singleView';
		}
		
		$this->checkPiVars();

		$this->catObj = t3lib_div::makeInstance('tx_'.$this->_extKey.'_category');
		$this->catObj->init($this->config);	
		$this->tcaObj = t3lib_div::makeInstance('tx_'.$this->_extKey.'_tcagen');
		$this->tcaObj->init($this->config);	

		if (!$fatalError)	{
			$fatalError = $this->initCategoriesAndProperties();
		};

		$this->formItems = tx_kbshop_abstract::getFlexformChilds($this->cObj->data['pi_flexform'], 'list_forms_section', 'list_form_item', array('field_form_table', 'field_save_table', 'field_transferts'), 'form', $this->config->lDEF, $this->config->vDEF);
		if (is_array($this->formItems)&&count($this->formItems))	{
			$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
			$GLOBALS['LANG']->init($GLOBALS['TSFE']->sys_language_isocode);
			$GLOBALS['TSFE']->includeTCA($TCAloaded);
			if ($this->doProcessData())	{
				$this->processData();
				if ((!$this->formError) && is_array($this->data) && count($this->data))	{
					$formTarget = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'field_submitTarget', 'form', $this->config->lDEF, $this->config->vDEF));
					if (is_array($this->conf['forms.']['formTarget.']))	{
						$formTarget = $this->cObj->stdWrap($formTarget, $this->conf['forms.']['formTarget.']);
					}
					if ($this->cObj->stdWrap($this->conf['doSave'], $this->conf['doSave.'])) {
							// We save the record after checking for double posts.
						$ok = $this->recordSetExists();
						if ($ok)	{
							$GLOBALS['TSFE']->register['kbshop_alreadyPosted'] = $this->alreadyPosted = 1;
						} else	{
							$this->saveTables();
							$this->hook('postSave');
							if (!intval($this->conf['forms.']['dontClearBasket']))	{
								$this->sessionData['basket'] = array();
							}
							$GLOBALS['TSFE']->register['kbshop_savedRecords'] = $this->savedRecords = 1;
							$GLOBALS['TSFE']->fe_user->setKey('ses', $this->prefixId, $this->sessionData);
							$GLOBALS['TSFE']->fe_user->storeSessionData();
						}
					}
					if ($formTarget)	{
						foreach ($this->data as $table => $tableArr)	{
							foreach ($tableArr as $vUid => $rrow)	{
								$this->sessionData['basket'][$table][$vUid] = $this->data[$table][$vUid];
							}
						}
						$GLOBALS['TSFE']->fe_user->setKey('ses', $this->prefixId, $this->sessionData);
						$GLOBALS['TSFE']->fe_user->storeSessionData();
						if ($formTarget!=$GLOBALS['TSFE']->id)	{
							$url = $this->pi_linkTP_keepPIvars_url(array(), $this->conf[$this->tskey.'.']['noCache']?0:1, 0, $formTarget);
							$url = t3lib_div::locationHeaderUrl($url);
							header('Location: '.$url);
							exit();
						}
					}
				}
			}
		} else	{
			$this->sessionData = $GLOBALS['TSFE']->fe_user->getKey('ses', $this->prefixId);
			$this->data = $this->sessionData['basket'];
		}



		if (!$fatalError)	{
			$this->showUid = intval($this->piVars['v']);
			$disableSingle = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'field_disableSingleView', 'sDEF', $this->config->lDEF, $this->config->vDEF));
			if (!$disableSingle && $this->showUid)	{
				list($c, $e) = $this->singleView();
			} else	{
				$disableList = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'field_disableListView', 'listView', $this->config->lDEF, $this->config->vDEF));
				if (!$disableList)	{
					list($c, $e) = $this->listView();
				}
			}
			$content .= $this->preJS.chr(10).$c.chr(10).$this->postJS;
			$fatalError .= $e;
		}

		if ($this->conf['stdWrap.'])	{	
			$content = $this->cObj->stdWrap($content, $this->conf['stdWrap.']);
		}

		if (intval($this->conf['dontWrapInBaseClass']))	{
			$GLOBALS['TYPO3_DB']->debugOutput = $this->saveSQLdebug;
			return $fatalError?$fatalError:$content;
		} else	{
			$GLOBALS['TYPO3_DB']->debugOutput = $this->saveSQLdebug;
			return $this->pi_wrapInBaseClass($fatalError?$fatalError:$content);
		}
	}


	function formTag()	{
		return '<form action="'.$this->pi_linkTP_keepPIvars_url(array(), $this->conf[$this->tskey.'.']['noCache']?0:1).'" name="editform" method="POST" target="_top" enctype="multipart/form-data" '.$this->conf['forms.']['formAddParams'].'>';
	}
	
	function initHooks()	{
		$cnt = 0;
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['FE_HookClasses'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['FE_HookClasses'] as $_classRef) {
				$hookObj = &t3lib_div::getUserObj($_classRef);
				if (is_object($hookObj))	{
					$this->hookObjects[$cnt] = &$hookObj;
					$this->hookObjects[$cnt]->init($this, $cnt);
					$cnt++;
				}
			}
		}
	}


	function hook($name, $args = array())	{
		$f = $this->config->hookMethodPrefix.$name;
		foreach ($this->hookObjects as $hookObjIdx => $hookObj)	{
			$hookObj = &$this->hookObjects[$hookObjIdx];
			if (method_exists($hookObj, $f))	{
				$ret = $hookObj->$f($this, $args);
				if (is_array($ret)&&$ret['break'])	{
					unset($ret['break']);
					break;
				}
			}
		}
		return $ret;
	}

	function initCategoriesAndProperties()	{
		$propPid = $this->config->getPropertiesPage($GLOBALS['TSFE']->id);
		if (!$propPid)	{
			return $this->pi_getLL('error_no_prop_pid');
		}
		$cacheFile = PATH_site.$this->config->typo3tempPath.'kb_shop_PI_cache_'.$this->listTable['uid'].'.ser';
		if (@file_exists($cacheFile)&&!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['dontCache'])	{
			$this->properties = unserialize(t3lib_div::getURL($cacheFile));
		}
		if (!$this->properties)	{
			$categories = $this->catObj->getCategoriesRec($this->listTable['uid']);
			if (!(is_array($categories)&&count($categories)))	{
				return $this->pi_getLL('error_no_cat_found');
			}
			$this->properties = array();
			$callback = array(
				'object' => &$this,
				'method' => 'propertyLoad_callback',
			);
			foreach ($categories as $category)	{
				$this->catObj->getCategoryRootline($category);
				if (is_array($this->catObj->categoryRootline)&&count($this->catObj->categoryRootline))	{
					$this->properties = t3lib_div::array_merge_recursive_overrule($this->properties, $this->catObj->getPropertyTree($propPid, $callback));
				}
			}
		}
		$this->propertiesMerged = array();
		if (is_array($this->properties)&&count($this->properties))	{
			foreach ($this->properties as $tab => $tabArr)	{
				$this->propertiesMerged = t3lib_div::array_merge_recursive_overrule($this->propertiesMerged, $tabArr['props']);
			}
		}
		if (!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['dontCache'])	{
			t3lib_div::writeFile($cacheFile, serialize($this->properties));
		}
	}

	function checkRecordExists($table, $record, $override)	{
		if (!count($record))	{
			return false;
		}
		$where = array();
		$override['crdate'] = false;
		foreach ($record as $field => $val)	{
			if (isset($override[$field]))	{
				if ($override[$field])	{
					$where[] = $field.'='.$GLOBALS['TYPO3_DB']->fullQuoteStr($override[$field], $table);
				}
			} else	{
				$where[] = $field.'='.$GLOBALS['TYPO3_DB']->fullQuoteStr($val, $table);
			}
		}
		$where[] = 'deleted=0 AND ((crdate>'.($record['crdate']-intval($this->doublePostDelay/2)).') AND (crdate<'.($record['crdate']+intval($this->doublePostDelay/2)).'))';
		$wStr = implode(' AND ', $where);
		$recs = tx_kbshop_abstract::getRecordsByField($table, 'deleted', 0, '', ' AND '.$wStr);
		return (is_array($recs)&&count($recs))?$recs:false;
	}

	function recordSetExists()	{
		$isdoublepost = false;
		$sectionTables = array();
		foreach ($this->formTables as $table => $tca)	{
			if (!$tca['ctrl']['parentTable'])	continue;
			$sectionTables[$tca['ctrl']['parentTable']][$table] = $this->data[$table];
		}
		foreach ($this->formTables as $table => $tca)	{
			if ($tca['ctrl']['parentTable'])	continue;
			if ($this->data[$table])	{
				foreach ($this->data[$table] as $tmp => $dArr)	{
					$sdArr = array();
					if (is_array($sectionTables[$table]))	{
						// Join section records.
						foreach ($sectionTables[$table] as $sTable => $sArr)	{
							foreach ($sArr as $sRow)	{
								if ($sRow['parent']==$dArr['uid'])	{
									$sdArr[$sTable][] = $sRow;
								}
							}
						}
					}
					if ($mapArr = $this->transferMapping[$table])	{
						$transferConf = $this->getTransferConf($this->transferMapping[$table]['TS']);
						$dArr = $this->transferFields($dArr, $mapArr['source'], $mapArr['target'], $transferConf);
						$this->transData[$table][$dArr['uid']] = $dArr;
						$recordexists = $this->checkRecordExists($mapArr['target'], $dArr, array('uid' => false, 'tstamp' => false, 'fe_group' => false));
						foreach ($sdArr as $sTable => $sArr)	{
							$tsTable = $this->getSectionTableMappedName($sTable, $table);
							foreach ($sArr as $sRow)	{
								$sRow = $this->transferFields($sRow, $sTable, $tsTable, $transferConf);
								$this->transData[$sTable][$sRow['uid']] = $sRow;

							}
						}
						if ($recordexists)	{
							foreach ($recordexists as $eRec)	{
								$allexist = true;
								foreach ($sdArr as $sTable => $sArr)	{
									$tsTable = $this->getSectionTableMappedName($sTable, $table);
									foreach ($this->transData[$sTable] as $sRow)	{
										$allexist &= $this->checkRecordExists($tsTable, $sRow, array('uid' => false, 'tstamp' => false, 'parent' => $eRec['uid'], 'fe_group' => false));
									}
								}
								$isdoublepost |= $allexist;
								if ($isdoublepost)	{
									break;
								}
							}
						}
					}
					if ($isdoublepost)	{
						break;
					}
				}
			}
			if ($isdoublepost)	{
				break;
			}
		}
		return $isdoublepost;
	}

	function getTransferConf($setup)	{
		$parser = t3lib_div::makeInstance('t3lib_TSparser');
		$parser ->setup = array();
		$matchObj = t3lib_div::makeInstance('t3lib_matchCondition');
		$parser->parse($setup, $matchObj);
		return $parser->setup;
	}

	function transferFields($row, $source, $target, $conf)	{
		if (!@is_array($conf[$target.'.'])) return $row;
		$localcObj = clone($this->cObj);
		$localcObj->start($row);
		foreach ($row as $field => $value)	{
			if ($conf[$target.'.'][$field] || $conf[$target.'.'][$field.'.'])	{
				$localcObj->setCurrentVal($value);
				$row[$field] = $localcObj->stdWrap($conf[$target.'.'][$field], $conf[$target.'.'][$field.'.']);
				unset($conf[$target.'.'][$field]);
				unset($conf[$target.'.'][$field.'.']);
			}
		}
		foreach ($conf[$target.'.'] as $field => $cval)	{
			if (substr($field, -1)=='.')	{
				$field = $substr($field, 0, -1);
			}
			$row[$field] = $localcObj->stdWrap($conf[$target.'.'][$field], $conf[$target.'.'][$field.'.']);
		}
		return $row;
	}

	function getSectionTableMappedName($sectionTable, $sourceTable)	{
		$sectionTable = str_replace($this->config->sectionTablePrefix, $this->config->entriesTablePrefix, $sectionTable);
		$sectionTable = str_replace($sourceTable, $this->transferMapping[$sourceTable]['target'], $sectionTable);
		$sectionTable = str_replace($this->config->entriesTablePrefix, $this->config->sectionTablePrefix, $sectionTable);
		return $sectionTable;
	}

	function saveTables()	{
		$sectionTables = array();
		foreach ($this->formTables as $table => $tca)	{
			if (!$tca['ctrl']['parentTable'])	continue;
			$sectionTables[$tca['ctrl']['parentTable']][$table] = $this->transData[$table];
		}
		foreach ($this->formTables as $table => $tca)	{
			if ($tca['ctrl']['parentTable'])	continue;
			if ($this->data[$table])	{
				foreach ($this->transData[$table] as $tmp => $dArr)	{
					$sdArr = array();
					if (is_array($sectionTables[$table]))	{
						// Join section records.
						foreach ($sectionTables[$table] as $sTable => $sArr)	{
							foreach ($sArr as $sRow)	{
								if ($sRow['parent']==$dArr['uid'])	{
									$sdArr[$sTable][] = $sRow;
								}
							}
						}
					}
					unset($dArr['uid']);
					if ($mapArr = $this->transferMapping[$table])	{
						$GLOBALS['TYPO3_DB']->exec_INSERTquery($mapArr['target'], $dArr);
						$insertId = $GLOBALS['TYPO3_DB']->sql_insert_id();
						foreach ($sdArr as $sTable => $sArr)	{
							$tsTable = $this->getSectionTableMappedName($sTable, $table);
							foreach ($sArr as $sRow)	{
								$sRow['parent'] = $insertId;
								unset($sRow['uid']);
								$GLOBALS['TYPO3_DB']->exec_INSERTquery($tsTable, $sRow);
							}
						}
					}
				}
			}
		}
	}

	function propertyLoad_callback(&$row)	{
		$row['flexform'] = t3lib_div::xml2array($row['flexform']);
		$a = preg_replace('/[^a-z0-9_]/', '', strtolower($row['alias']));
		$row['__key'] = $a?$a:$row['uid'];
		if ($row['type']==1)	{
			$row['__selectItems'] = $this->getSelectBoxItems($row['flexform']);
		} elseif ($row['type']==10)	{
			$row['__selectItems'] = tx_kbshop_abstract::getFlexformChilds($row['flexform'], 'list_value_section', 'list_value_label', 'field_label', 'sDEF', $this->config->lDEF, $this->config->vDEF);
		}
		/*
		 * TYPEADD
		 */
	}

	function singleView()	{
			// Do it the easy way :)
		$this->tskey = 'singleView';
		$this->onlyUid = $this->showUid;
		$result = $this->getList(array($this->onlyUid));
		if (is_array($result)&&is_array($result['rows'][0]))	{
			$this->cObj->data = array_merge($this->cObj->data, $result['rows'][0]);
			return $this->listView('singleView', false, false);
		}
	}

	function listView($htmltmpl = 'listView', $renderCS = true, $renderPB = true, $overrideKey = '')	{
		list($tmpl, $e) = $this->loadTemplate($this->tskey);
		if ($e)	{
			return array('', $tmpl);
		}
		$key = $overrideKey?$overrideKey:$this->cObj->stdWrap($this->conf['subpart.'][$this->tskey], $this->conf['subpart.'][$this->tskey.'.']);
		$key = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
		$content = '';
		if (strlen($key))	{
			$content = $this->cObj->getSubpart($tmpl, '###TMPL_'.$htmltmpl.'_'.$key.'###');
		}
		if (!strlen($content))	{
			$content = $this->cObj->getSubpart($tmpl, $subkey = ('###TMPL_'.$htmltmpl.'###'));
		}
		if (!strlen($content))	{
			return array('', str_replace('###SUBPART###', $subkey, $this->pi_getLL('error_subpart_not_found')).'<br />'.chr(10));
		}
		if ($this->conf[$this->tskey.'.']['noCache'] || (is_array($this->formItems) && count($this->formItems)))	{
			$GLOBALS['TSFE']->set_no_cache();
		}
		$this->localcObj = clone($this->cObj);
		$this->localcObj->LOAD_REGISTER('', 'LOAD_REGISTER');

			// Set user-defined and pre-defined criteria values
		if ($e = $this->setDefinedCriteria())	{
			return array('', $e);
		}

		$markerTree = array();
		$this->t3tt = t3lib_div::makeInstance('tx_kbshop_t3tt');
		$this->t3tt->init($this->config);

			// Render criteria selector
		if ($renderCS&&intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'field_showCriteriaSelector', 'criteria', $this->config->lDEF, $this->config->vDEF)))	{
			$this->renderCriteriaSelector($markerTree);
		} else	{
			$markerTree['_SUBPARTS']['###PART_criteriaSelector###'] = false;
			$markerTree['_SUBPARTS']['###PART_criteriaSelectorEmpty###'] = true;
		}

			// Set the field label markers
		$this->setLabelMarkers($markerTree['_MARKERS']);
		
		$this->showPB = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'field_showPagebrowser', 'listView', $this->config->lDEF, $this->config->vDEF));
		if (!$this->showPB)	{
			$this->piVars['p'] = 0;
		}

			// Set the number of found items
		$this->itemCount = t3lib_div::intInRange(intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'field_itemsPerPage', 'listView', $this->config->lDEF, $this->config->vDEF)), 0, 100000, 0);
		if ($this->itemCount)	{
			$this->setLinkMarkers($markerTree['_MARKERS'], $markerTree['_SUBPARTS']);
			$this->items = $this->queryUidList(-1);
			$markerTree['_MARKERS']['###PAGE_current###'] = intval($this->piVars['p'])+1;
			$markerTree['_MARKERS']['###PAGE_all###'] = intval((intval($this->items)-1)/intval($this->itemCount))+1;
			$markerTree['_MARKERS']['###ITEMS_found###'] = $this->items;
			$markerTree['_MARKERS']['###ITEMS_index_begin###'] = (intval($this->piVars['p'])*$this->itemCount)+1;
			$markerTree['_MARKERS']['###ITEMS_index_end###'] = ((intval($this->piVars['p'])+1)*$this->itemCount)>$this->items?$this->items:((intval($this->piVars['p'])+1)*$this->itemCount);
			$markerTree['_MARKERS']['###ITEMS_shown###'] = $markerTree['_MARKERS']['###ITEMS_index_end###']-$markerTree['_MARKERS']['###ITEMS_index_begin###']+1;
	

			$this->renderForms($markerTree['_MARKERS'], $markerTree['_SUBPARTS'], $content, $this->conf[$this->tskey.'.']['forms.']);
				
			$args = array(
				'markerTree' => &$markerTree,
				'content' => &$content,
			);
			$this->hook('renderListing', $args);
	
			if ($this->items||$this->onlyUid)	{
				$this->renderItemList($markerTree, $content);
			} else	{
				$markerTree['_SUBPARTS']['###PART_listing###'] = false;
				$markerTree['_SUBPARTS']['###PART_listingEmpty###'] = true;
			}
		}

			// Render cObjects 
		$listingCObjects = array();
		if (preg_match_all('/###COBJ_LISTING_([a-zA-Z0-9_]+)###/', $content, $matches)>0)	{
			$listingCObjects = $matches[1];
		}
		$this->renderCObjects($markerTree['_MARKERS'], 'LISTING', $this->conf[$this->tskey.'.']['cObjects.'], $listingCObjects);
			// Render pagebrowser
		if (($this->items>$this->itemCount)&&$renderPB&&$this->showPB)	{
			$firstlast = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'field_showFirstLast', 'listView', $this->config->lDEF, $this->config->vDEF));
			$prevnext = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'field_showPrevNext', 'listView', $this->config->lDEF, $this->config->vDEF));
			$p = intval($this->piVars['p']);
			$last = intval((intval($this->items)-1)/intval($this->itemCount));
			$markerList = $this->getBrowsePages();
			$itemsSubpart = array(
				'_MULTIPLE_MARKERS' => true,
				'_MARKERS' => $markerList,
			);
			$cnt = count($markerList);
			$cnt += ($firstlast?2:0);
			$cnt += ($prevnext?2:0);
			$markerTree['_SUBPARTS']['###PART_pageBrowser###'] = array(
				'_MARKERS' => array(
					'###ITEM_count###' => $cnt,
					'###ITEM_equalWidth###' => intval(100/$cnt),
				),
				'_SUBPARTS' => array(
					'###BROWSE_itemFirst###' => $firstlast?$this->getBrowseSubpart(0):false,
					'###BROWSE_itemFirstEmpty###' => $firstlast?false:true,
					'###BROWSE_itemPrevious###' => ($prevnext&&($p>0))?$this->getBrowseSubpart($p-1):false,
					'###BROWSE_itemPreviousEmpty###' => ($prevnext&&($p>0))?false:true,
					'###BROWSE_itemList###' => $itemsSubpart,
					'###BROWSE_itemListEmpty###' => count($markerList)?false:true,
					'###BROWSE_itemNext###' => ($prevnext&&($p<$last))?$this->getBrowseSubpart($p+1):false,
					'###BROWSE_itemNextEmpty###' => ($prevnext&&($p<$last))?false:true,
					'###BROWSE_itemLast###' => $firstlast?$this->getBrowseSubpart($last):false,
					'###BROWSE_itemLastEmpty###' => $firstlast?false:true,
				),
			);
			$markerTree['_SUBPARTS']['###PART_pageBrowserEmpty###'] = false;
		} else	{
			$markerTree['_SUBPARTS']['###PART_pageBrowser###'] = false;
			$markerTree['_SUBPARTS']['###PART_pageBrowserEmpty###'] = true;
		}

		$markerTree['_MARKERS']['###FORM_TAG###'] = $this->formTag();
		$content = $this->t3tt->substituteSubpartsAndMarkers_Tree($content, $markerTree);
	
		if (intval(t3lib_div::_GP('debug')))	{
			$content .= t3lib_div::print_array($markerTree);
		}

		$this->localcObj->LOAD_REGISTER('', 'RESTORE_REGISTER');
		
		if ($this->conf[$this->tskey.'.']['stdWrap.'])	{	
			$content = $this->cObj->stdWrap($content, $this->conf[$this->tskey.'.']['stdWrap.']);
		}

		return array($content, '');
	}

	function renderForms(&$markers, &$subparts, $content, $conf, $type = 'listing', $actUid = 0)	{
		if (!$this->tceforms)	{
			$this->tceforms = t3lib_div::makeInstance('t3lib_TCEforms');
			$this->tceforms->doSaveFieldName = 'doSave';
			$this->tceforms->backPath = 'typo3/';
//			$this->tceforms->returnUrl = $this->R_URI;
			$this->tceforms->palettesCollapsed = 0;
			$this->tceforms->disableRTE = 0;
			$this->tceforms->enableClickMenu = 0;
			$this->tceforms->enableTabMenu = 0;
			$this->tceforms->fieldTemplate = '###FIELD_ITEM###';
		}
		$this->tmpData = $this->data;
		$formVarsInserted = array();
		$this->hiddenFieldAccum = array();
		if (is_array($conf)&&count($conf))	{
			if (preg_match_all('/###INPUT_'.strtoupper($type).'_([a-zA-Z0-9_\|]+)###/', $content, $matches, PREG_SET_ORDER)>0)	{
				if (!is_array($this->formTables))	{
					$this->loadFormTables();
				}
				foreach ($matches as $match)	{
					list($table, $field, $subfield) = explode('|', $match[1]);
					if (!$this->checkIf($conf['fields.'][($subfield?$subfield:$field).'.']['if.']))	continue;

					$this->BE_USER = $this->formTableUsers[$this->config->entriesTablePrefix.$table];
					if ($fConf = $GLOBALS['TCA'][$this->config->entriesTablePrefix.$table]['columns'][$this->config->fieldPrefix.$field])	{
						$origTable = $table;
						$origField = $field;
						if ($subfield)	{
							$table = $this->config->sectionTablePrefix.$origTable.$this->config->sectionTableCenter.$field.$this->config->sectionTablePostfix;
							$field = $this->config->fieldPrefix.$subfield;
							$vUid = $actUid;
						} else	{
							$table = $this->config->entriesTablePrefix.$table;
							$field = $this->config->fieldPrefix.$field;
							$vUid = 1;
						}
						$sBEU = $GLOBALS['BE_USER'];
						$GLOBALS['BE_USER'] = $this->BE_USER;
						if (!is_array($this->data[$table][$vUid]))	{
//							$this->tmpData[$table][$vUid] = $this->initTableRow($table, $vUid, $conf);
							$this->tmpData[$table][$vUid] = $this->data[$table][$vUid] = $this->initTableRow($table, $vUid, $conf);
						} elseif (!intval($this->data[$table][$vUid]['pid']))	{
							$this->tmpData[$table][$vUid] = $this->data[$table][$vUid] = $this->updateTableRow($table, $vUid, $conf, $this->data[$table][$vUid]);
						} else	{
//							$this->tmpData[$table][$vUid] = $this->updateTableRow($table, $vUid, $conf, $this->data[$table][$vUid]);
							$this->tmpData[$table][$vUid] = $this->data[$table][$vUid] = $this->updateTableRow($table, $vUid, $conf, $this->data[$table][$vUid], $field);
						}
						$this->sessionData['basket'][$table][$vUid] = $this->data[$table][$vUid];
						$GLOBALS['TSFE']->fe_user->setKey('ses', $this->prefixId, $this->sessionData);
						$PA = $this->getPA($table, $field, $this->tmpData[$table][$vUid]);
						$this->tceforms->initDefaultBEMode();
						$fCode = '';
						if ($PA)	{
							$this->tceforms->cachedTSconfig[$table.':'.$this->tmpData[$table][$vUid]['uid']] = array();
							$formVarsInserted[$table][$vUid][$field] = 1;
							$fCode .= $this->tceforms->getSingleField_SW($table, $field, $this->tmpData[$table][$vUid], $PA);
							$markers[$match[0]] = $fCode;
						}
						$GLOBALS['BE_USER'] = $sBEU;
					}
				}
				$this->preJS = $this->tceforms->printNeededJSFunctions_top();
				$this->postJS = $this->tceforms->printNeededJSFunctions();
			}
			if (preg_match_all('/###REQUIRED_FIELD_'.strtoupper($type).'_([a-zA-Z0-9_\|]+)###/', $content, $matches, PREG_SET_ORDER)>0)	{
				if (!is_array($this->formTables))	{
					$this->loadFormTables();
				}
				$this->preJS = $this->tceforms->printNeededJSFunctions_top();
				foreach ($matches as $match)	{
					list($table, $field, $subfield) = explode('|', $match[1]);
					if ($fConf = $GLOBALS['TCA'][$this->config->entriesTablePrefix.$table]['columns'][$this->config->fieldPrefix.$field])	{
						$origTable = $table;
						$origField = $field;
						if ($subfield)	{
							$table = $this->config->sectionTablePrefix.$origTable.$this->config->sectionTableCenter.$field.$this->config->sectionTablePostfix;
							$field = $this->config->fieldPrefix.$subfield;
							$vUid = $actUid;
						} else	{
							$table = $this->config->entriesTablePrefix.$table;
							$field = $this->config->fieldPrefix.$field;
							$vUid = 1;
						}
						if ($m = $this->errorFields[$table][$field])	{
							if (is_string($m)&&strlen($m))	{
								$subparts[$match[0]] = $m;
							} else	{
								$subparts[$match[0]] = true;
							}
						} elseif (!$this->data[$table][$vUid][$field])	{
							if (!isset($this->postedData[$table][$vUid][$field]))	{
								$subparts[$match[0]] = false;
							} else	{
								$subparts[$match[0]] = true;
							}
						} else	{
							$subparts[$match[0]] = false;
						}
					}
				}
			}
		}

		$hidden = '';
		/*
		foreach ($formVarsInserted as $fTable => $fArr)	{
			foreach ($fArr as $fUid => $fRow)	{
				foreach ($this->tmpData[$fTable][$fUid] as $fField => $fVal)	{
					if (!$fRow[$fField])	{
						$hidden .= '<input type="hidden" name="'.$this->tceforms->prependFormFieldNames.'['.$fTable.']['.$fUid.']['.$fField.']" value="'.$fVal.'" />'.chr(10);
					}
				}
				$hidden .= '<input type="hidden" name="'.$this->tceforms->prependFormFieldNames.'['.$fTable.']['.$fUid.'][__type]" value="'.$type.'" />'.chr(10);
			}
		}
		*/
		$submitButton = $this->localcObj->stdWrap($conf['submitButton'], $conf['submitButton.']);
		if (!strlen($submitButton))	{
			$submitButton = '<input type="submit" value="Save" name="'.$this->tceforms->doSaveFieldName.'" />';
		} else	{
			$submitButton = str_replace('###NAME###', $this->tceforms->doSaveFieldName, $submitButton);
		}
		$markers['###ACTION_submit###'] = $submitButton;
		$markers['###HIDDENFIELDS###'] = $hidden.implode(chr(10), $this->tceforms->hiddenFieldAccum);
	}

	function updateTableRow($table, $uid, $conf, $row, $only_field = false)	{
		$trData = t3lib_div::makeInstance('t3lib_transferData');
		$trData->addRawData = TRUE;
		foreach ($GLOBALS['TCA'][$table]['columns'] as $field => $fieldConf)	{
			if (($conf[$table.'.']['defVals.'][$field] || $conf[$table.'.']['defVals.'][$field.'.']) && !strlen($row[$field]))	{
				$trData->defVals[$table][$field] = $this->localcObj->stdWrap($conf[$table.'.']['defVals.'][$field], $conf[$table.'.']['defVals.'][$field.'.']);
			} else	{
				$trData->defVals[$table][$field] = $row[$field];
			}
		}
		$pid = $this->localcObj->stdWrap($conf[$table.'.']['pid'], $conf[$table.'.']['pid.']);
		if (!$pid)	{
			$pid = $this->localcObj->stdWrap($conf['recordPid'], $conf['recordPid.']);
		}
		if ($only_field)	{
			$rec = array_merge($row, $trData->defVals[$table]);
			$rec['pid'] = $pid;
			return $rec;
		}
		$trData->lockRecords = 1;
		$trData->disableRTE = 0;
		$trData->fetchRecord($table, $pid, 'new');	// 'new'
		reset($trData->regTableItems_data);
		$rec = current($trData->regTableItems_data);
		$rec['uid'] = $uid;
		$rec['pid'] = $pid;
		return $rec;
	}

	function initTableRow($table, $uid, $conf)	{
		$trData = t3lib_div::makeInstance('t3lib_transferData');
		$trData->addRawData = TRUE;
		foreach ($GLOBALS['TCA'][$table]['columns'] as $field => $fieldConf)	{
			if ($conf[$table.'.']['defVals.'][$field] || $conf[$table.'.']['defVals.'][$field.'.'])	{
				$trData->defVals[$table][$field] = $this->localcObj->stdWrap($conf[$table.'.']['defVals.'][$field], $conf[$table.'.']['defVals.'][$field.'.']);
			}
		}
		$trData->lockRecords = 1;
		$trData->disableRTE = 0;
		$pid = $this->localcObj->stdWrap($conf[$table.'.']['pid'], $conf[$table.'.']['pid.']);
		if (!$pid)	{
			$pid = intval($this->localcObj->stdWrap($conf['recordPid'], $conf['recordPid.']));
		}
		$trData->fetchRecord($table, $pid, 'new');	// 'new'
		reset($trData->regTableItems_data);
		$rec = current($trData->regTableItems_data);
		$rec['uid'] = $uid;
		$rec['pid'] = $pid;
		return $rec;
	}

	function getPA($table, $field, $row)	{
		$PA = array();
		$PA['palette'] = 0;
		$PA['fieldChangeFunc'] = array();
		$PA['fieldConf'] = $GLOBALS['TCA'][$table]['columns'][$field];
		$PA['fieldConf']['config']['form_type'] = $PA['fieldConf']['config']['form_type'] ? $PA['fieldConf']['config']['form_type'] : $PA['fieldConf']['config']['type'];	// Using "form_type" locally in this script
		if (	is_array($PA['fieldConf']) &&
				$PA['fieldConf']['config']['form_type']!='passthrough' &&
				($this->RTEenabled || !$PA['fieldConf']['config']['showIfRTE']) &&
				(!$PA['fieldConf']['displayCond'] || $this->isDisplayCondition($PA['fieldConf']['displayCond'],$row)) &&
				(!$GLOBALS['TCA'][$table]['ctrl']['languageField'] || $PA['fieldConf']['l10n_display'] || strcmp($PA['fieldConf']['l10n_mode'],'exclude') || $row[$GLOBALS['TCA'][$table]['ctrl']['languageField']]<=0) &&
				(!$GLOBALS['TCA'][$table]['ctrl']['languageField'] || !$this->localizationMode || $this->localizationMode===$PA['fieldConf']['l10n_cat'])
			)	{
				// Fetching the TSconfig for the current table/field. This includes the $row which means that

				// If the field is NOT disabled from TSconfig (which it could have been) then render it
			if (!$PA['fieldTSConfig']['disabled'])	{

					// Init variables:
				$PA['itemFormElName']=$this->tceforms->prependFormFieldNames.'['.$table.']['.$row['uid'].']['.$field.']';		// Form field name
				$PA['itemFormElName_file']=$this->tceforms->prependFormFieldNames_file.'['.$table.']['.$row['uid'].']['.$field.']';	// Form field name, in case of file uploads
				$PA['itemFormElValue']=$row[$field];		// The value to show in the form field.

					// set field to read-only if configured for translated records to show default language content as readonly
				if ($PA['fieldConf']['l10n_display'] AND t3lib_div::inList($PA['fieldConf']['l10n_display'], 'defaultAsReadonly') AND $row[$GLOBALS['TCA'][$table]['ctrl']['languageField']]) {
					$PA['fieldConf']['config']['readOnly'] =  true;
					$PA['itemFormElValue'] = $this->defaultLanguageData[$table.':'.$row['uid']][$field];
				}
					// Render as a hidden field?
				if (in_array($field,$this->tceforms->hiddenFieldListArr))	{
					$this->hiddenFieldAccum[]='<input type="hidden" name="'.$PA['itemFormElName'].'" value="'.htmlspecialchars($PA['itemFormElValue']).'" />';
				} else {	// Render as a normal field:

						// onFocus attribute to add to the field:
					$PA['onFocus'] = ($palJSfunc && !$BE_USER->uc['dontShowPalettesOnFocusInAB']) ? ' onfocus="'.htmlspecialchars($palJSfunc).'"' : '';
					$PA['label'] = $PA['altName'] ? $PA['altName'] : $PA['fieldConf']['label'];
					$PA['label'] = tx_kbshop_abstract::sL($PA['label']);
						// JavaScript code for event handlers:
					return $PA;
				}
			}
		}
		return false;
	}

	function loadFormTables()	{
		if (!$this->formTables)	{
			$this->formTables = array();
			$this->saveTables = array();
			$this->transferMapping = array();
			foreach ($this->formItems as $idx => $tmpArr)	{
				list($skey, $tca) = $this->loadTable($tmpArr['field_form_table'], true);
				if (strlen($skey))	{
					$trec = tx_kbshop_abstract::getRecord($this->config->categoriesTable, intval($tmpArr['field_save_table']));
					if ($trec)	{
						$tkey = tx_kbshop_misc::getKey($trec);
						$this->transferMapping[$this->config->entriesTablePrefix.$skey] = array(
							'source' => $this->config->entriesTablePrefix.$skey,
							'target' => $this->config->entriesTablePrefix.$tkey,
							'TS' => $tmpArr['field_transferts'],
						);
					}
				}
			}
		}
	}

	function loadTable($table, $virtual)	{
		$trow= tx_kbshop_abstract::getRecord($this->config->categoriesTable, intval($table));
		$propsPid = $this->config->getPropertiesPage();
		$cacheFile = PATH_site.$this->config->typo3tempPath.'kb_shop_vtbl_'.intval($table).'_cache.ser';
		if (@file_exists($cacheFile)&&!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['dontCache'])	{	
			$tcaArr = unserialize(t3lib_div::getURL($cacheFile));
			foreach ($tcaArr['tca'] as $tkey => $formTableArr)	{
				$this->formTables[$tkey] = $tcaArr['tca'][$tkey];
				$GLOBALS['TCA'][$tkey] = $tcaArr['tca'][$tkey];
				$this->formTableUsers[$tkey] = $tcaArr['asUser'];
			}
			return array($tcaArr['tkey'], $tcaArr['tca'][$this->config->entriesTablePrefix.$tcaArr['tkey']]);
		}
		if ($trow && $propsPid)	{
			t3lib_div::loadTCA($this->config->categoriesTable);
			$tkey = tx_kbshop_misc::getKey($trow);
			$tca = array();
			$labelProp = tx_kbshop_abstract::getRecord($this->config->propertiesTable, $trow['labelProperty']);
			if (is_array($labelProp))	{
				$lkey = tx_kbshop_misc::getKey($labelProp);
				$label = $this->config->fieldPrefix.$lkey;
			} else	{
				$label = 'uid';
			}
			$subtca = array(
				'ctrl' => Array (
					'title' => tx_kbshop_abstract::csConv($trow['title'], $this->config->currentCharset, 'iso-8859-1'),
					'label' => $label,
					'tstamp' => 'tstamp',
					'crdate' => 'crdate',
					'cruser_id' => 'cruser_id',
					'delete' => 'deleted',
					'requestUpdate' => 'category',
					'tableCategoryUid' => $trow['uid'],
					'virtual' => 1,
					'enablecolumns' => Array (		
						'disabled' => 'hidden',	
						'starttime' => 'starttime',	
						'endtime' => 'endtime',	
						'fe_group' => 'fe_group',
					),
					'iconfile' => $trow['image']?('../'.$GLOBALS['TCA'][$this->config->categoriesTable]['columns']['image']['config']['uploadfolder'].'/'.$trow['image']):(t3lib_extMgm::extRelPath('kb_shop').'icon_tx_kbshop_category.gif'),
				),
				'feInterface' => Array (
					'fe_admin_fieldList' => 'hidden, starttime, endtime, fe_group, title',
				)
			);
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
						$subtca['ctrl']['sortby'] = 'sorting';
					break;
				}
			}
			$tca[$this->config->entriesTablePrefix.$tkey] = $subtca;
			if ($trow['allowOnPages'])	{
				$allowTableOnPages[] = $this->config->entriesTablePrefix.$tkey;
			}
			$GLOBALS['TCA'][$this->config->entriesTablePrefix.$tkey] = $subtca;
			require_once($GLOBALS['_EXTPATH'].'class.tx_kbshop_category.php');
			require_once($GLOBALS['_EXTPATH'].'class.tx_kbshop_tcagen.php');
			require_once($GLOBALS['_EXTPATH'].'class.tx_kbshop_tcagen_tca.php');
			$catObj = t3lib_div::makeInstance('tx_kbshop_category');
			$catObj->init($this->config);
			$tcagen = t3lib_div::makeInstance('tx_kbshop_tcagen_tca');
			$tcagen->init($this->config);
			$allProps = array();
			$allCats = $catObj->getCategoriesRec($trow['uid']);
			if (is_array($allCats))	{
				foreach ($allCats as $actCat)	{
					$catObj->getCategoryRootline($actCat);
					if (is_array($catObj->categoryRootline)&&count($catObj->categoryRootline))	{
						$allProps[$actCat] = $catObj->getPropertyTree($propsPid);
					}
				}
			}
			$tca = $tcagen->renderTCA($allProps, $tkey);
			if (is_array($tca)&&count($tca))	{
				tx_kbshop_misc::loadSectionTCA($tca['columns']);
					// Create required directories
				tx_kbshop_misc::createDirs($tcagen->uploadFolders);
				require_once($GLOBALS['_EXTPATH'].'class.tx_kbshop_sqlengine.php');
				$sqlengine = t3lib_div::makeInstance('tx_kbshop_sqlengine');
				$sqlengine->init($this->config);
				$dbTables = $sqlengine->getDBTables(1);
				$relTables = $sqlengine->getRelTables($dbTables, $tkey);
				$GLOBALS['TCA'][$this->config->entriesTablePrefix.$tkey] = $tca;
				$this->formTables[$this->config->entriesTablePrefix.$tkey] = $tca;
				$simUser = $this->pluginPageRec['cruser_id'];
				$beUrec= tx_kbshop_abstract::getRecord('be_users', $simUser);
				$tempBE_USER = t3lib_div::makeInstance('t3lib_userAuthGroup');	// New backend user object
				$tempBE_USER->OS = TYPO3_OS;
				$tempBE_USER->user = $beUrec;
				$tempBE_USER->groupData['tables_modify'] .= ','.$this->config->entriesTablePrefix.$tkey;
				$tempBE_USER->fetchGroupData();
				$this->formTableUsers[$this->config->entriesTablePrefix.$tkey] = $tempBE_USER;
				foreach ($relTables as $rTable => $rTableConf)	{
					$tempBE_USER->groupData['tables_modify'] .= ','.$rTable;
					$this->formTableUsers[$rTable] = $tempBE_USER;
					$this->formTables[$rTable] = $GLOBALS['TCA'][$rTable];
				}
				if (!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['dontCache'])	{	
					$sArr = array(
						'tkey' => $tkey,
						'tca' => $this->formTables,
						'asUser' => $tempBE_USER,
					);
					t3lib_div::writeFile($cacheFile, serialize($sArr));
				}
				return array($tkey, $tca);
			}
		}
		return false;
	}


	function renderItemList(&$markerTree, $content)	{
			// Query a list of items to display
		if ($this->onlyUid)	{
			$uidList = $this->querySingleUid($this->onlyUid);
		} else	{
			$uidList = $this->queryUidList($this->itemCount, intval($this->piVars['p']));
		}
			// Get the result of items to display
		if (count($uidList))	{
			$result = $this->getList($uidList);
		}
			
			// Render items
		$this->renderRows($markerTree, $content, $result['rows'], $result['fields'], $result);
	}


	function renderRows(&$markerTree, $content, $rows, $fields, $resultArr, $cObjMarker = 'ITEM', $section = '', $table = '')	{
		if (!$table)	{
			$table = $this->config->entriesTable;
		}
		$listItems = array();
		$prevData = $this->localcObj->data;
		$this->localcObj->setParent($prevData, '');
		$itemCObjects = array();
		if (preg_match_all('/###COBJ_'.strtoupper($cObjMarker).'_([a-zA-Z0-9_]+)###/', $content, $matches)>0)	{
			$itemCObjects = $matches[1];
		}
		$lKey = 'item';
		$cnt = 1;
		$fArr = array();
		foreach ($fields as $key => $field)	{
			$fArr[$key] = $this->getField($field);
			if (!$fArr[$key]['type'])	{
				$fArr[$key] = $this->getDefaultType($field);
			}
		}
		if (is_array($rows)&&count($rows))	{
			foreach ($rows as $row)	{
				$ma = array();
				$sp = array();
				$tmp = array();
				$fields['uid'] = 'uid';
				$this->localcObj->data = array_merge($prevData, $row);
				foreach ($fields as $key => $field)	{
					$tmp[$key] = $row[$field];
					list($value) = $this->getFieldRenderValue($fArr[$key], $row[$field], $this->conf[$this->tskey.'.']['itemList.']['uidLists']);
					$proc['proc_'.$key] = $value;
				}
				$this->localcObj->data = array_merge($prevData, $tmp, $proc);
				$this->localcObj->data[$section.($section?'_':'').'index0'] = $cnt-1;
				$this->localcObj->data[$section.($section?'_':'').'index1'] = $cnt;
				$this->localcObj->data['__section'] = $section;
				if (is_array($section?$this->conf[$this->tskey.'.']['itemList.'][$section.'.']['if.']:$this->conf[$this->tskey.'.']['itemList.']['if.']))	{
					if (!$this->checkIf($section?$this->conf[$this->tskey.'.']['itemList.'][$section.'.']['if.']:$this->conf[$this->tskey.'.']['itemList.']['if.']))	{
						continue;
					}
				}
				$this->insertRenderFields($ma, $sp, $fields, $row, $section?$section.'_':'', $section);
				$this->insertMarkers($ma, $section?$this->conf[$this->tskey.'.']['itemList.'][$section.'.']['marks.']:$this->conf[$this->tskey.'.']['itemList.']['marks.']);
				$this->insertSubparts($sp, $section?$this->conf[$this->tskey.'.']['itemList.'][$section.'.']['subparts.']:$this->conf[$this->tskey.'.']['itemList.']['subparts.']);
				$ma['###'.$cObjMarker.'_even###'] = ($cnt%2)?0:1;
				$ma['###'.$cObjMarker.'_uneven###'] = ($cnt%2)?1:0;
				$ma['###'.$cObjMarker.'_index0###'] = $cnt-1;
				$ma['###'.$cObjMarker.'_index1###'] = $cnt;
				if (count($resultArr['sectionFieldsArray']))	{
					foreach ($resultArr['sectionFieldsArray'] as $keySect => $sectionFields)	{
						$skey = $resultArr['sectionFields'][$keySect];
						$subma = array();
						$this->renderRows($subma, $content, $row[$skey], $sectionFields, false, strtoupper($keySect), $keySect, $this->config->sectionTablePrefix.$this->config->entriesTable.$this->config->sectionTableCenter.$keySect.$this->config->sectionTablePostfix);
						$sp = array_merge($sp, $subma['_SUBPARTS']);
						$this->localcObj->data = array_merge($prevData, $tmp, $proc);
						$this->localcObj->data[$section.($section?'_':'').'index0'] = $cnt-1;
						$this->localcObj->data[$section.($section?'_':'').'index1'] = $cnt;
					}
				} 
				if ($section)	{
					$lKey = $section;
				}
				$this->localcObj->data = array_merge($prevData, $tmp, $proc);
				$this->localcObj->data[$section.($section?'_':'').'index0'] = $cnt-1;
				$this->localcObj->data[$section.($section?'_':'').'index1'] = $cnt;
				$this->localcObj->data['__section'] = $section;
				$args = array(
					'MARKERS' => &$ma,
					'SUBPARTS' => &$sp,
					'fields' => &$fields,
					'row' => &$row,
					'index' => &$cnt,
					'content' => &$content,
					'cObjMarker' => &$cObjMarker,
					'section' => &$section,
					'table' => &$table,
					'data' => $this->localcObj->data,
				);
				$this->hook('renderRow', $args);
				$this->renderCObjects($ma, strtoupper($cObjMarker), $section?$this->conf[$this->tskey.'.']['itemList.'][$section.'.']['cObjects.']:$this->conf[$this->tskey.'.']['itemList.']['cObjects.'], $itemCObjects);
				$this->renderForms($ma, $sp, $content, $this->conf[$this->tskey.'.']['itemList.']['forms.'], 'item', $row['uid']);
				if (is_array($sconf = $this->conf[$this->tskey.'.']['itemList.']['overlay.'])&&$this->checkIf($sconf['if.']))	{
					$this->renderOverlay($sconf, $sp, $ma, $content);
				}
				if (is_array($sconfArr = $this->conf[$this->tskey.'.']['itemList.']['overlays.']))	{
					foreach ($sconfArr as $key => $sconf)	{
						if (substr($key, -1)==='.')	{
							$ikey = substr($key, 0, -1);
							if (t3lib_div::testInt($ikey))	{
								if ($this->checkIf($sconf['if.']))	{
									$this->renderOverlay($sconf, $sp, $ma, $content);
								}
							}
						}
					}
				}
				if (is_array($subma['_SUBPARTS']))	{
					$sp = array_merge($sp, $subma['_SUBPARTS']);
				}
				$listItems['###LIST_'.$lKey.'###'][] = array(
					'_MARKERS' => $ma,
					'_SUBPARTS' => $sp,
				);
				$cnt++;
			}
			$markerTree['_SUBPARTS']['###PART_'.($section?$section:'listing').'###'] = array(
				'_MULTIPLE_SUBPARTS' => true,
				'_SUBPARTS' => $listItems,
			);
			$markerTree['_SUBPARTS']['###PART_'.($section?$section:'listing').'Empty###'] = false;
		} else	{
			$markerTree['_SUBPARTS']['###PART_'.($section?$section:'listing').'###'] = false;
			$markerTree['_SUBPARTS']['###PART_'.($section?$section:'listing').'Empty###'] = true;
		}
	}

	function renderOverlay($sconf, &$sp, &$ma, $content)	{
		$oresult = false;
		$oldConf = $this->conf;
		unset($this->conf[$this->tskey.'.']['itemList.']['overlay.']);
		unset($this->conf[$this->tskey.'.']['itemList.']['overlays.']);
		if (is_array($sconf['conf.']))	{
			$this->conf = t3lib_div::array_merge_recursive_overrule($this->conf, $sconf['conf.']);
		}
		$oldEntriesTable = $this->config->entriesTable;
		$oldListTable = $this->listTable;
		$oldTableKey = $this->tableKey;
		$oldProperties = $this->properties;
		$oldPropertiesMerged = $this->propertiesMerged;
		$oldLocalCobj = clone($this->localcObj);
		$tuid = $this->localcObj->stdWrap($sconf['table'], $sconf['table.']);
		$marker = $this->localcObj->stdWrap($sconf['marker'], $sconf['marker.']);
		$usemarker = $marker?$marker:$tuid;
		if (t3lib_div::testInt($tuid))	{
			$this->listTable = tx_kbshop_abstract::getRecord($this->config->categoriesTable, $tuid);
			if (!(is_array($this->listTable)&&count($this->listTable)))	{
				echo $this->pi_getLL('error_no_table');
				exit();
			}
			$this->tableKey = tx_kbshop_misc::getKey($this->listTable);
			$this->config->entriesTable = $this->config->entriesTablePrefix.$this->tableKey;
			$ouid = $this->localcObj->stdWrap($sconf['uid'], $sconf['uid.']);
			$this->properties = false;
			$this->initCategoriesAndProperties();
			$this->setLabelMarkers($ma, false, $usemarker.'_');
			$oresult = $this->getList(array($ouid));
		} elseif (is_array($GLOBALS['TCA'][$tuid]['ctrl']))	{
			t3lib_div::loadTCA($tuid);
			$ouid = $this->localcObj->stdWrap($sconf['uid'], $sconf['uid.']);
			$rec = tx_kbshop_abstract::getRecord($tuid, $ouid);
			$ofields = array('pid' => 'pid', 'uid' => 'uid', 'crdate' => 'crdate', 'tstamp' => 'tstamp', 'cruser_id' => 'cruser_id');
			if ($df = $GLOBALS['TCA'][$tuid]['ctrl']['delete'])	{
				$ofields[$df] = $df;
			}
			foreach ($GLOBALS['TCA'][$tuid]['columns'] as $fkey => $fa)	{
				$ofields[$fkey] = $fkey;
			}
			if (is_array($rec)&&count($rec))	{
				$oresult = array(
					'rows' => array(
						$rec,
					),
					'fields' => $ofields,
				);
			}
		}
		// Render items
		$subma = array();
		if (is_array($oresult['rows'])&&count($oresult['rows']))	{
			$this->renderRows($subma, $content, $oresult['rows'], $oresult['fields'], $oresult, strtoupper($usemarker), $usemarker);
			$sp = array_merge($sp, $subma['_SUBPARTS']['###PART_'.$usemarker.'###']['_SUBPARTS']['###LIST_'.$usemarker.'###'][0]['_SUBPARTS']);
			$ma = array_merge($ma, $subma['_SUBPARTS']['###PART_'.$usemarker.'###']['_SUBPARTS']['###LIST_'.$usemarker.'###'][0]['_MARKERS']);
		}
		$this->localcObj = $oldLocalCobj;
		$this->config->entriesTable = $oldEntriesTable;
		$this->tableKey = $oldTableKey;
		$this->listTable = $oldListTable;
		$this->properties = $oldProperties;
		$this->propertiesMerged = $oldPropertiesMerged;
		$this->conf = $oldConf;
	}

	function insertMarkers(&$ma, $conf)	{
		if (is_array($conf))	{
			foreach ($conf as $key => $subconf)	{
				if (strpos($key, '.')!==false)	{
					$ma['###'.$key.'###'] = $this->localcObj->cObjGetSingle($subconf, $conf[$key.'.']);
				}
			}
		}
	}
	function insertSubparts(&$sp, $conf)	{
		if (is_array($conf))	{
			foreach ($conf as $key => $subconf)	{
				if (strpos($key, '.')===false)	{
					$sp['###'.$key.'###'] = $this->localcObj->cObjGetSingle($subconf, $conf[$key.'.']);
				}
			}
		}
	}

	function insertRenderFields(&$ma, &$sp, $fields, $row, $prefix = '', $sectionKey = '')	{
		foreach ($fields as $key => $field)	{
			$ma['###FIELD_RAWVALUE_'.$prefix.$key.'###'] = $row[$field];
			if (strlen(($sectionKey && ($this->conf[$this->tskey.'.']['itemList.'][$sectionKey.'.']['fields.'][$key]) || is_array($this->conf[$this->tskey.'.']['itemList.'][$sectionKey.'.']['fields.'][$key.'.']))) || (!$sectionKey && (strlen($this->conf[$this->tskey.'.']['itemList.']['fields.'][$key]) || is_array($this->conf[$this->tskey.'.']['itemList.']['fields.'][$key.'.']))))	{
				$this->localcObj->setCurrentVal($row[$field]);
				if ($sectionKey)	{
					$conf = $this->conf[$this->tskey.'.']['itemList.'][$sectionKey.'.']['fields.'];
				} else	{
					$conf = $this->conf[$this->tskey.'.']['itemList.']['fields.'];
				}
				$fArr = $this->getField($field);
				if (!$fArr['type'])	{
					$fArr = $this->getDefaultType($field);
				}
				if ($fArr['type']==9)	{
					if (intval($fArr['flexform']['data']['sDEF'][$this->config->lDEF]['field_maxitems'][$this->config->vDEF])>1)	{
						$mmtable = $this->config->mmRelationTablePrefix.$fArr['__key'].$this->config->mmRelationTablePostfix;
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_foreign', $mmtable, 'uid_local='.$row['uid'], '', 'sorting');
						$str = '';
						if ($res)	{
							while ($tmprow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
								$str .= ($str?',':'').$tmprow['uid_foreign'];
							}
							$GLOBALS['TYPO3_DB']->sql_free_result($res);
						}
						$this->localcObj->setCurrentVal($str);
					}
				}
				$ma['###FIELD_VALUE_'.$prefix.$key.'###'] = $set = $this->localcObj->stdWrap($conf[$key], $conf[$key.'.']);
			} else	{
				$set = $this->setFieldRenderValue($ma, $sp, $row, $key, $field, $prefix);
			}
			if ($set)	{
				$sp['###FIELD_SUBPART_'.$prefix.$key.'###'] = true;
				$sp['###FIELD_SUBPART_'.$prefix.$key.'Empty###'] = false;
			} else	{
				$sp['###FIELD_SUBPART_'.$prefix.$key.'###'] = false;
				$sp['###FIELD_SUBPART_'.$prefix.$key.'Empty###'] = true;
			}
		}
	}

	function setFieldRenderValue(&$ma, &$sp, $row, $key, $field, $prefix = '')	{
		$fArr = $this->getField($field);
		if (!$fArr['type'])	{
			$fArr = $this->getDefaultType($field);
		}
		if ($field=='uid')	{
			$this->setLinkMarkers($ma, $sp, $row, $prefix);
		}
		list($value, $set) = $this->getFieldRenderValue($fArr, $row[$field]);
		$ma['###FIELD_VALUE_'.$prefix.$key.'###'] = $value;
		return $set;
	}


	function getFieldRenderValue($fArr, $value, $uidlists = false)	{
		$res = '';
		$set = false;
		switch ($fArr['type'])	{
			case 1:		// Select
				if (strlen($value))	{
					$set = true;
				}
				$label = $fArr['__selectItems'][$value];
				$res = htmlspecialchars($label);
			break;
			case 9:		// dbrel
				if (strlen($value))	{
					$set = true;
				}
				if (intval($fArr['flexform']['data']['sDEF'][$this->config->lDEF]['field_maxitems'][$this->config->vDEF])>1)	{
					$set = false;
					if (intval($value))	{
						$set = true;
					}
				}
				$label = $this->getDBRelLabel($fArr['flexform']['data']['sDEF'][$this->config->lDEF], $value, $fArr['alias'], $fArr);
				if ($uidlists && t3lib_div::inList($uidlists, $fArr['__key']))	{
					$res = $this->lastRelUids;
				} else	{
					$res = htmlspecialchars($label);
				}
			break;
			case 10:		// Multi Check
				if (intval($value))	{
					$set = true;
				}
				$val = $this->getMultiCheckLabel($fArr['__selectItems'], $value);
				$res = htmlspecialchars($val);
			break;
			case 3:		// RTE
				if (strlen(strip_tags($value)))	{
					$set = true;
				}
				$conf = array(
					'value' => $value,
					'parseFunc.' => $GLOBALS['TSFE']->tmpl->setup['lib.']['parseFunc_RTE.'],
				);
				if (is_array($this->conf[$this->tskey.'.']['fieldConfig.']['rte.']['parseFunc_RTE.']))	{
					$conf['parseFunc.'] = $this->conf[$this->tskey.'.']['fieldConfig.']['rte.']['parseFunc_RTE.'];
				} elseif (is_array($GLOBALS['TSFE']->tmpl->setup['lib.']['parseFunc_RTE.']))	{
					$conf['parseFunc.'] = $GLOBALS['TSFE']->tmpl->setup['lib.']['parseFunc_RTE.'];
				} elseif (is_array($GLOBALS['TSFE']->tmpl->setup['tt_content.']['text.']['20.']['parseFunc.']))	{
					$conf['parseFunc.'] = $GLOBALS['TSFE']->tmpl->setup['tt_content.']['text.']['20.']['parseFunc.'];
				}
				$res = $this->localcObj->cObjGetSingle('TEXT', $conf);
			break;
			case 7:		// Date
				if (intval($value))	{
					$set = true;
				}
				if (is_array($this->conf[$this->tskey.'.']['fieldConfig.']['date.']))	{
					$res = $this->localcObj->stdWrap($value, $this->conf[$this->tskey.'.']['fieldConfig.']['date.']);
				} else	{
					$res = strftime('%Y-%m-%d', $value);
				}
			break;
			case 12:		// Time
				if (intval($value))	{
					$set = true;
				}
				if (is_array($this->conf[$this->tskey.'.']['fieldConfig.']['time.']))	{
					$res = $this->localcObj->stdWrap($value, $this->conf[$this->tskey.'.']['fieldConfig.']['time.']);
				} else	{
					$res = intval($value/3600).':'.intval(($value%3600)/60);
				}
			break;
			case 13:		// Timesec
				if (intval($value))	{
					$set = true;
				}
				if (is_array($this->conf[$this->tskey.'.']['fieldConfig.']['timesec.']))	{
					$res = $this->localcObj->stdWrap($value, $this->conf[$this->tskey.'.']['fieldConfig.']['timesec.']);
				} else	{
					$res = intval($value/3600).':'.intval(($value%3600)/60).':'.intval($value%60);
				}
			break;
			case 14:		// DateTime
				if (intval($value))	{
					$set = true;
				}
				if (is_array($this->conf[$this->tskey.'.']['fieldConfig.']['datetime.']))	{
					$res = $this->localcObj->stdWrap($value, $this->conf[$this->tskey.'.']['fieldConfig.']['datetime.']);
				} else	{
					if ($set)	{
						$res = strftime('%Y-%m-%d, %H:%M:%S', $value);
					}
				}
			break;
			case 15:		// Year 
				if (intval($value))	{
					$set = true;
				}
				if (is_array($this->conf[$this->tskey.'.']['fieldConfig.']['year.']))	{
					$res = $this->localcObj->stdWrap($value, $this->conf[$this->tskey.'.']['fieldConfig.']['Year.']);
				} else	{
					$res = strftime('%Y', $value);
				}
			break;
			/*
			 * TYPEADD
			 */
			case 2:		// Text
			case 8:		// String
			case 11:		// File
				if (strlen($value))	{
					$set = true;
				}
			case 4:		// Decimal
			case 5:		// Integer
			case 6:		// Check
				if (intval($value))	{
					$set = true;
				}
				$res = htmlspecialchars($value);
			default:
				if ($value)	{
					$set = true;
				}
				$res = htmlspecialchars($value);
			break;
		}
		return array($res, $set);
	}

	function setLinkMarkers(&$ma, &$sp, $row = false, $prefix = '')	{
		if (is_array($this->conf[$this->tskey.'.']['listViewLink.']['extra.']))	{
			$GLOBALS['TSFE']->register['linkViewUid'] = 0;
			$GLOBALS['TSFE']->register['linkPageNumber'] = intval($this->piVars['p']);
			foreach ($this->conf[$this->tskey.'.']['listViewLink.']['extra.'] as $key => $eConf)	{
				if (t3lib_div::testInt($key))	{
					$eConf = $this->conf[$this->tskey.'.']['listViewLink.']['extra.'][$key.'.'];
					$eConf['additionalParams'] .= t3lib_div::implodeArrayForUrl('', array('tx_kbshop_pi1' => array('v' => '', 'p' => $this->piVars['p'])), '', 1);
					$url = $this->localcObj->typoLink_URL($eConf);
					$ma['###LINK_'.$prefix.'listView_HREF_'.$key.'###'] = $this->hsc?htmlspecialchars($url):$url;
				}
			}
		}
		if ($row)	{
				// Single view link.
			$ma['###LINK_'.$prefix.'singleView_HREF###'] = $this->hsc?htmlspecialchars($this->singleViewLink($row['uid'])):$this->singleViewLink($row['uid']);
			if (is_array($this->conf[$this->tskey.'.']['singleViewLink.']['extra.']))	{
				$GLOBALS['TSFE']->register['linkViewUid'] = $row['uid'];
				$GLOBALS['TSFE']->register['linkPageNumber'] = intval($this->piVars['p']);
				foreach ($this->conf[$this->tskey.'.']['singleViewLink.']['extra.'] as $key => $eConf)	{
					if (t3lib_div::testInt($key))	{
						$url = $this->localcObj->typoLink_URL($this->conf[$this->tskey.'.']['singleViewLink.']['extra.'][$key.'.'], array('v' => $row['uid'], 'p' => intval($this->piVars['p'])));
						$ma['###LINK_'.$prefix.'singleView_HREF_'.$key.'###'] = $this->hsc?htmlspecialchars($url):$url;
					}
				}
			}
		}
			// Link to reset all values. 
		$altPageId = 0;
		if ($this->conf[$this->tskey.'.']['listViewLink.']['page'] || $this->conf[$this->tskey.'.']['listViewLink.']['page.'])	{
			$altPageId = intval($this->localcObj->stdWrap($this->conf[$this->tskey.'.']['listViewLink.']['page'], $this->conf[$this->tskey.'.']['listViewLink.']['page.']));
		}
		$ma['###LINK_'.$prefix.'resetCriteria_HREF###']  = $this->hsc?htmlspecialchars($this->pi_linkTP_keepPIvars_url($this->resetPIvars(),  $this->doCache, 0, $altPageId)):$this->pi_linkTP_keepPIvars_url($this->resetPIvars(),  $this->doCache, 0, $altPageId);
		if ($this->onlyUid)	{
			$p = intval($this->piVars['p']);
				// Link to previous matching item
			if ($this->previousItem)	{
				$sp['###LINK_'.$prefix.'singleViewPrevious###'] = true;
				$sp['###LINK_'.$prefix.'singleViewPreviousEmpty###'] = false;
				$page = $p-((!($this->onlyIdx%$this->itemCount))?1:0);
				$ma['###LINK_'.$prefix.'singleViewPrevious_HREF###'] = $this->hsc?htmlspecialchars($this->singleViewLink($this->previousItem, $page)):$this->singleViewLink($this->previousItem, $page);
			} else	{
				$sp['###LINK_'.$prefix.'singleViewPrevious###'] = false;
				$sp['###LINK_'.$prefix.'singleViewPreviousEmpty###'] = true;
			}

				// Link to next matching item
			if ($this->nextItem)	{
				$sp['###LINK_'.$prefix.'singleViewNext###'] = true;
				$sp['###LINK_'.$prefix.'singleViewNextEmpty###'] = false;
				$page = $p+((!(($this->onlyIdx+1)%$this->itemCount))?1:0);
				$ma['###LINK_'.$prefix.'singleViewNext_HREF###'] = $this->hsc?htmlspecialchars($this->singleViewLink($this->nextItem, $page)):$this->singleViewLink($this->nextItem, $page);
			} else	{
				$sp['###LINK_'.$prefix.'singleViewNext###'] = false;
				$sp['###LINK_'.$prefix.'singleViewNextEmpty###'] = true;
			}

		} 
			// Link back to list view
		$ma['###LINK_'.$prefix.'listView_HREF###'] = $this->hsc?htmlspecialchars($this->singleViewLink(0, intval($this->onlyIdx/$this->itemCount))):$this->singleViewLink(0, intval($this->onlyIdx/$this->itemCount));
	}

	function renderCriteriaSelector(&$markerTree)	{
		if (is_array($this->criteriaItems)&&count($this->criteriaItems))	{
			$selectors = array();
			foreach ($this->criteriaItems as $idx => $critItem)	{
				list($sel, $e) = $this->getCriteriaSelector($critItem, $idx);
				if ($e)	{
					return array('', $e);
				}
				$selectors = array_merge_recursive($selectors, $sel);
			}
			$markerTree['_SUBPARTS']['###PART_criteriaSelector###'] = array(
				'_MULTIPLE_SUBPARTS' => true,
				'_SUBPARTS' => $selectors,
			);
			$markerTree['_SUBPARTS']['###PART_criteriaSelectorEmpty###'] = false;
		} else	{
			$markerTree['_SUBPARTS']['###PART_criteriaSelectorCheckbox###'] = false;
			$markerTree['_SUBPARTS']['###PART_criteriaSelector###'] = false;
			$markerTree['_SUBPARTS']['###PART_criteriaSelectorEmpty###'] = true;
		}
	}


	function renderCObjects(&$markers, $type, $conf, $listingCObjects)	{
		foreach ($listingCObjects as $key)	{
			if (strlen($t = $conf[$key])&&is_array($c = $conf[$key.'.']))	{
				$markers['###COBJ_'.$type.'_'.$key.'###'] = $this->localcObj->cObjGetSingle($t, $c);
			} else	{
				$markers['###COBJ_'.$type.'_'.$key.'###'] = '';
			}
		}
	}


	function resetPIvars($pi = false)	{
		if (!$pi)	{
			$pi = $this->piVars;
		}
		$r = array();
		foreach ($pi as $key => $value)	{
			if (is_array($value))	{
				$r[$key] = $this->resetPIvars($value);
			} else	{
				$r[$key] = '';
			}
		}
		return $r;
	}

	function getBrowsePages()	{
		$pages = t3lib_div::intInRange($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'field_pagesInBrowser', 'listView', $this->config->lDEF, $this->config->vDEF), 1, 1000, 5);
		$p = intval($this->piVars['p']);
		$l = intval((intval($this->items)-1)/intval($this->itemCount));
		$ph = intval($pages/2);//+1
		$start = $p-$ph;
		if ($start<0)	{
			$start = 0;
		}
		$stop = $start+($pages-1);
		if ($stop>$l)	{
			$stop = $l;
			$start = $stop-($pages-1);
			if ($start<0)	{
				$start = 0;
			}
		}
		$ma = array();
		for ($x = $start; $x <= $stop; $x++)	{
			$tmp = $this->getBrowseSubpart($x);
			$ma[] = $tmp['_MARKERS'];
		}
		return $ma;
	}

	function getBrowseSubpart($page)	{
		if (!$page)	{
			$page = '';
		}
		$ma = array(
			'_MARKERS' => array(
				'###LINK_HREF###' => $this->hsc?htmlspecialchars($this->pi_linkTP_keepPIvars_url(array('p' => $page, 'v' => ''),  $this->doCache)):$this->pi_linkTP_keepPIvars_url(array('p' => $page, 'v' => ''),  $this->doCache),
				'###LINK_SELECTED###' => (intval($page)==intval($this->piVars['p']))?$this->pi_getLL('text_browser_selected'):'',
				'###LABEL_first###' => $this->pi_getLL('label_browser_first'),
				'###LABEL_previous###' => $this->pi_getLL('label_browser_previous'),
				'###LABEL_next###' => $this->pi_getLL('label_browser_next'),
				'###LABEL_last###' => $this->pi_getLL('label_browser_last'),
			),
		);
		$pl = '';
		if (intval($page)==intval($this->piVars['p']))	{
			$pl = str_replace('###PAGE###', $page+1, $this->pi_getLL('label_browser_page_active'));
		}
		if (!$pl)	{
			$pl = str_replace('###PAGE###', $page+1, $this->pi_getLL('label_browser_page'));
		}
		if (intval($page)==intval($this->piVars['p']))	{
			$pl = str_replace('###MARKER_BEGIN###', '<span>', $pl);
			$pl = str_replace('###MARKER_END###', '</span>', $pl);
		} else	{
			$pl = str_replace('###MARKER_BEGIN###', '', $pl);
			$pl = str_replace('###MARKER_END###', '', $pl);
		}
		$ma['_MARKERS']['###LABEL_item###'] = $pl;
		return $ma;
	}

	function setLabelMarkers(&$ma, $sub = false, $prefix = '')	{
		$start = false;
		if (!$sub)	{
			$sub = $this->propertiesMerged;
			if (!$prefix)	{
				$start = true;
			}
		}
		foreach ($sub as $field => $prop)	{
			$key = substr($field, strlen($this->config->fieldPrefix));
			if ($GLOBALS['TSFE']->sys_language_content && ($ll = $prop['_LANG_ROWS'][$GLOBALS['TSFE']->sys_language_content]['title']))	{
				$ma['###LABEL_'.$prefix.$key.'###'] = $ll;
			} else	{
				$ma['###LABEL_'.$prefix.$key.'###'] = $prop['title'];
			}
			if ($prop['_SUBPROPS'])	{
				$this->setLabelMarkers($ma, $prop['_SUBPROPS']);
			}
		}
		if ($start)	{
			$Lkey = ($this->LLkey=='en')?'default':$this->LLkey;
			foreach ($this->LOCAL_LANG[$Lkey] as $llkey => $llval)	{
				$ma['###LLLABEL_'.$llkey.'###'] = $this->pi_getLL($llkey);
			}
		}
	}

	function getList($uidList)	{
		global $TCA;
		$c = array();
		$c['fields'] = array('uid' => 'uid', 'category' => 'category');
		$c['selectFields'] = array('uid' => $this->config->entriesTable.'.uid AS uid', 'category' => $this->config->entriesTable.'.category AS category');
		if (($lf = $GLOBALS['TCA'][$this->config->entriesTable]['ctrl']['languageField']) && ($origp = $GLOBALS['TCA'][$this->config->entriesTable]['ctrl']['transOrigPointerField']))	{
			$c['selectFields']['pid'] = $this->config->entriesTable.'.pid AS pid';
			$c['selectFields'][$lf] = $this->config->entriesTable.'.'.$lf.' AS '.$lf;
			$c['selectFields'][$origp] = $this->config->entriesTable.'.'.$origp.' AS '.$origp;
		}
		$c['joins'] = array(0 => $this->config->entriesTable);
		$c['where'] = array($this->config->entriesTable.'.uid IN ('.($ul = implode(',', $uidList)).')', $this->config->entriesTable.'.pid IN ('.$this->prodPagesStr.')');
		$c['sectionFields'] = array();
		$c['sectionFieldsArray'] = array();
		$this->getFieldsJoinsWhere($c);
		$c['rows'] = array();
		if (intval($this->listTable['virtual']))	{
			$this->loadFormTables();
			if (is_array($this->data[$this->config->entriesTable]))	{
				foreach ($uidList as $uid)	{
					$row = $this->data[$this->config->entriesTable][$uid];
					if (count($c['sectionFieldsArray']))	{
						foreach ($c['sectionFieldsArray'] as $key => $fields)	{
							$skey = $c['sectionFields'][$key];
							$row[$skey] = $this->getSectionList($key, $fields, $row['uid']);
						}
					}
					$c['rows'][] = $row;
				}
			}
			return $c;
		}

		$query = 'SELECT '.(implode(',', $c['selectFields'])).' FROM '.implode(', ', $c['joins']).' WHERE '.implode(' AND ', $c['where']).' ORDER BY FIND_IN_SET('.$this->config->entriesTable.'.uid, \''.$ul.'\')';
		if ($GLOBALS['TYPO3_DB']->debugOutput || $GLOBALS['TYPO3_DB']->store_lastBuiltQuery)	{
			$GLOBALS['TYPO3_DB']->debug_lastBuiltQuery = $query;
		}
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			if (($lf = $GLOBALS['TCA'][$this->config->entriesTable]['ctrl']['languageField']) && $GLOBALS['TSFE']->sys_language_content)	{
				if ($row[$lf])	{
					$l10p = $GLOBALS['TCA'][$this->config->entriesTable]['ctrl']['transOrigPointerField'];
					$row = $GLOBALS['TSFE']->sys_page->getRawRecord($this->config->entriesTable, $row[$l10p]);
				}
				$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay($this->config->entriesTable, $row, $GLOBALS['TSFE']->sys_language_content);
			}
			if (count($c['sectionFieldsArray']))	{
				foreach ($c['sectionFieldsArray'] as $key => $fields)	{
					$skey = $c['sectionFields'][$key];
					$row[$skey] = $this->getSectionList($key, $fields, $row['uid']);
				}
			}
			$c['rows'][] = $row;
		}
		return $c;
	}

	function getSectionList($key, $fields, $uid)	{
		global $TCA;
		$table = $this->config->sectionTablePrefix.$this->tableKey.$this->config->sectionTableCenter.$key.$this->config->sectionTablePostfix;
		$ret = array();
		if (intval($GLOBALS['TCA'][$table]['ctrl']['virtual']))	{
			if (is_array($this->data[$table]))	{
				foreach ($this->data[$table] as $curUid => $row)	{
					if ($row['parent']==$uid)	{
						$ret[] = $row;
					}
				}
			}
		} else	{
			$fields['uid'] = 'uid';
			$sFields = array();
			foreach ($fields as $key => $field)	{
				$sFields[$key] = $table.'.'.$field.' AS '.$field;
			}
			if  (!$GLOBALS['TCA'][$table])	{
				include($this->config->configExtBasePAth.'ext_section_tables.php');
				t3lib_div::loadTCA($table);
			}
			$where = $table.'.parent='.intval($uid).' AND '.tx_kbshop_abstract::enableFields($table);
			list($sf) = t3lib_div::trimExplode(',', tx_kbshop_abstract::getSortFields($table), 1);
			list($o_join, $o_where, $o_table) = tx_kbshop_misc::getSectionOrder($table, $sf);
			list($sf) = t3lib_div::trimExplode(',', tx_kbshop_abstract::getSortFields($o_table), 1);
			$query = 'SELECT '.implode(',', $sFields).' FROM '.$table.$o_join.' WHERE '.$where.($o_where?' AND ':'').$o_where.' ORDER BY '.$o_table.'.'.$sf.';';
			if ($GLOBALS['TYPO3_DB']->debugOutput || $GLOBALS['TYPO3_DB']->store_lastBuiltQuery)	{
				$GLOBALS['TYPO3_DB']->debug_lastBuiltQuery = $query;
			}
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				if ($lf = $GLOBALS['TCA'][$table]['ctrl']['languageField'] && $GLOBALS['TSFE']->sys_language_content)	{
					if ($row[$lf])	{
						$l10p = $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'];
						$row = $GLOBALS['TSFE']->sys_page->getRawRecord($table, $row[$l10p]);
					}
					$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay($table, $row, $GLOBALS['TSFE']->sys_language_content);
				}
				$ret[] = $row;
			}
		}
		return $ret;
	}

	function getFieldsJoinsWhere(&$c, $sub = false, $sect = false)	{
		if (!$sub)	{
			$sub = $this->propertiesMerged;
			if (!intval($this->listTable['virtual']))	{
				$c['where'][] = tx_kbshop_abstract::enableFields($this->config->entriesTable);
			}
			$c['where'][] = $this->config->entriesTable.'.pid IN ('.$this->prodPagesStr.')';
		}
		foreach ($sub as $field => $prop)	{
			$tsect = $sect;
			switch ($prop['type'])	{
				/*
				 * TYPEADD
				 */
				case 200:
					$key = substr($field, strlen($this->config->fieldPrefix));
					$c['sectionFields'][$key] = $field;
					$tsect = $key;
					/*
						We want no joined fields. Else only those entrires having something related would get shown.
					$key = substr($field, strlen($this->config->fieldPrefix));
					$st = $this->config->sectionTablePrefix.$key.$this->config->sectionTablePostfix;
					$j[] = $st;
					$sf[] = $field;
					$w[$st] = tx_kbshop_abstract::enableFields($st).' AND '.$st.'.parent='.$this->config->entriesTable.'.uid';
					$f[] = $st.'.uid AS SECT_'.$field.'___uid';
					*/
				break;
				default:
					$skey = substr($sect , strlen($this->config->fieldPrefix));
					$key = substr($field, strlen($this->config->fieldPrefix));
					if ($sect)	{
						$c['sectionFieldsArray'][$sect][$key] = $field;
					} else	{
						$c['selectFields'][$key] = ($sect?$this->config->sectionTablePrefix.$this->tableKey.$this->config->sectionTableCenter.$skey.$this->config->sectionTablePostfix:$this->config->entriesTable).'.'.$field.' AS '.($sect?'SECT_'.$sect.'___':'').$field;
						$c['fields'][$key] = ($sect?'SECT_'.$sect.'___':'').$field;
					}
				break;
			}
			if ($prop['_SUBPROPS'])	{
				$this->getFieldsJoinsWhere($c, $prop['_SUBPROPS'], $tsect);
			}
		}
	}


	function getUidQueryWhereStr($whereParts, $as_postfix = '', $onStr = '', $noUserValue = false, $addLanguage = true)	{
		$wp = array();
		$wp_base = array();
		$join = array();
		$ef = array();
		$joinNoEF = array();
		$unset_idx = false;
		if ($as_postfix===-1)	{
			$as_postfix = '';
			$unset_idx = true;
		}
		foreach ($whereParts as $idx => $part)	{
			if (is_array($part['subWhere'])&&count($part['subWhere'])&&$part['connector'])	{
				list($sub_wp, $sub_join, $sub_ef, $wp_base_sub) = $this->getUidQueryWhereStr($part['subWhere'], $as_postfix, $onStr, $noUserValue, $addLanguage);
				$wp[] = '('.implode($part['connector'], $sub_wp).')';
				$joinNoEF = array_merge($joinNoEF, $sub_join);
				$ef = array_merge_recursive($ef, $sub_ef);
				$wp_base = array_merge_recursive($wp_base, $wp_base_sub);
				continue;
			}
			if ($unset_idx)	{
				$idx = '';
			}
			if ($noUserValue && $part['userValue'])	continue;
			if ($part['table']==$this->config->entriesTable)	{
				if ($part['MM'])	{
					$joinNoEF[$part['MM'].$idx.$as_postfix] = ', '.$part['MM'].' AS '.$part['MM'].$idx.$as_postfix;
					$c = str_replace('###FIELDNAME###', $part['MM'].$idx.$as_postfix.'.uid_foreign', $part['compareStr']);
					$c = $this->replaceSortFields($c, $whereParts);
					if ((strpos($c, '###VALUE###')!==false)&&$part['value'])	{
						$c = str_replace('###VALUE###', $GLOBALS['TYPO3_DB']->quoteStr($part['value'], $part['table']), $c);
					}
					$c = str_replace('###TABLE###', $part['table'].$as_postfix, $c);
					$c = str_replace('###ENTRIES_TABLE###', $this->config->entriesTable.$as_postfix, $c);
					$wp[] = $c;
					$wp_base['__join_'.md5(rand(0, 0x7fffffff))] = $part['MM'].$idx.$as_postfix.'.uid_local='.$this->config->entriesTable.$as_postfix.'.uid';
				} else	{
					$c = str_replace('###FIELDNAME###', $this->config->entriesTable.$as_postfix.'.'.$part['field'], $part['compareStr']);
					if ((strpos($c, '###VALUE###')!==false)&&$part['value'])	{
						$c = str_replace('###VALUE###', $GLOBALS['TYPO3_DB']->quoteStr($part['value'], $part['table']), $c);
					}
					$c = str_replace('###TABLE###', $part['table'], $c);
					$c = str_replace('###ENTRIES_TABLE###', $this->config->entriesTable.$as_postfix, $c);
					if ($onStr)	{
						$onStr .= ' AND '.$c;
					} else	{
						$wp[] = $c;
					}
				}
			} else	{
				if ($part['MM'])	{
					if (substr($part['MM'], -strlen($this->config->mmRelationTablePostfix))!=$this->config->mmRelationTablePostfix)	{
						$sidx = '';
					} else	{
						$sidx = $idx;
					}
					$join[$part['table']] = ', '.$part['table'].' AS '.$part['table'].$as_postfix;
					$c = str_replace('###FIELDNAME###', $part['MM'].$sidx.$as_postfix.'.uid_foreign', $part['compareStr']);
					$c = $this->replaceSortFields($c, $whereParts);
				} else	{
					$join[$part['table']] = ', '.$part['table'].' AS '.$part['table'].$as_postfix;
					$c = str_replace('###FIELDNAME###', $part['table'].$as_postfix.'.'.$part['field'], $part['compareStr']);
				}
				if ((strpos($c, '###VALUE###')!==false)&&$part['value'])	{
					$c = str_replace('###VALUE###', $GLOBALS['TYPO3_DB']->quoteStr($part['value'], $part['table']), $c);
				}
				$c = str_replace('###TABLE###', $part['table'].$as_postfix, $c);
				$c = str_replace('###ENTRIES_TABLE###', $this->config->entriesTable.$as_postfix, $c);
				if ($onStr)	{
					$onStr .= ' AND '.$c;
					$onStr .= ' AND '.$part['table'].$as_postfix.'.parent='.$this->config->entriesTable.$as_postfix.'.uid';
					if ($part['MM'])	{
						$join[$part['MM']] = ' LEFT JOIN '.$part['MM'].' AS '.$part['MM'].$idx.$as_postfix.' ON '.$part['table'].$as_postfix.'.uid='.$part['MM'].$idx.$as_postfix.'.uid_local';
					}
				} else	{
					if ($part['MM'])	{
						$wp[] = $c;
						$join[$sidx.$part['MM']] = ', '.$part['MM'].' AS '.$part['MM'].$sidx.$as_postfix;
						$wp_base['__join_'.md5(rand(0, 0x7fffffff))] = $part['table'].$as_postfix.'.uid='.$part['MM'].$sidx.$as_postfix.'.uid_local';
					} else	{
						$wp[] = '('.$c.' AND '.$part['table'].$as_postfix.'.parent='.$this->config->entriesTable.$as_postfix.'.uid)';
					}
				}
			}
		}
		$base = array();
		if (strlen($as_postfix))	{
			$base[$this->config->entriesTable] = (strlen($onStr)?' LEFT JOIN ':'').$this->config->entriesTable.' AS '.$this->config->entriesTable.$as_postfix.(strlen($onStr)?(' ON '.$onStr):'');
		} else	{
			$base[$this->config->entriesTable] = $this->config->entriesTable;
		}
		$rj = array();
		$lpf = $GLOBALS['TCA'][$this->config->entriesTable]['ctrl']['transOrigPointerField'];
		$lf = $GLOBALS['TCA'][$this->config->entriesTable]['ctrl']['languageField'];
		if ($addLanguage && $GLOBALS['TSFE']->sys_language_content)	{
			$joinNoEF[$this->config->entriesTable.'_localized'] = 
				( ($this->OLmode=='hideNonTranslated') ? ', ' : ' LEFT JOIN ' ).
				$this->config->entriesTable.' AS '.$this->config->entriesTable.'_localized'.
				( ($this->OLmode=='hideNonTranslated') ? '' : 
					(' ON '.$this->config->entriesTable.$as_postfix.'.uid='.$this->config->entriesTable.'_localized.'.$lpf.
					' AND '.$this->config->entriesTable.'_localized.'.$lf.'='.$GLOBALS['TSFE']->sys_language_content) );
			if ($this->OLmode=='hideNonTranslated')	{
				$wp_base['__join_'.md5(rand(0, 0x7fffffff))] = $this->config->entriesTable.$as_postfix.'.uid='.$this->config->entriesTable.'_localized.'.$lpf;
				$wp_base['__join_'.md5(rand(0, 0x7fffffff))] = $this->config->entriesTable.'_localized.'.$lf.'='.$GLOBALS['TSFE']->sys_language_content;
			}
			$ef[$this->config->entriesTable.'_localized'] = str_replace($this->config->entriesTable, $this->config->entriesTable.'_localized', tx_kbshop_abstract::enableFields($this->config->entriesTable));
		}
		$join = array_merge($base, $join);
		foreach ($join as $table => $str)	{
			if (substr($table, -strlen($this->config->mmRelationTablePostfix))!=$this->config->mmRelationTablePostfix)	{
				$ef[$table.$as_postfix] = str_replace($table, $table.$as_postfix, tx_kbshop_abstract::enableFields($table));
			}
			$rj[$table.$as_postfix] = $str;
		}
		$ef[$this->config->entriesTable.$as_postfix.'.pid'] = $this->config->entriesTable.$as_postfix.'.pid IN ('.$this->prodPagesStr.')';
		/*
		if ($addLanguage && $lf)	{
			$ef .= ' AND '.$this->config->entriesTable.$as_postfix.'.sys_language_uid='.((($this->OLmode==='hideNonTranslated')&&$GLOBALS['TSFE']->sys_language_content)?'1':'0');
		}
		*/
		$ef[$this->config->entriesTable.$as_postfix.'.sys_language_uid'] = $this->config->entriesTable.$as_postfix.'.sys_language_uid=0';
		$join = array_merge($rj, $joinNoEF);
		return array($wp, $join, $ef, $wp_base);
	}

	function replaceSortFields($c, $whereParts)	{
		if (preg_match_all('/###SORTFIELD_([0-9]+)###/', $c, $matches, PREG_SET_ORDER)>0)	{
			foreach ($matches as $match)	{
				$mi = $match[1];
				$p = $whereParts[$mi];
				if ($p['MM'])	{
					$c = str_replace('###SORTFIELD_'.$mi.'###', $p['MM'].$mi.'.sorting', $c);
				}
			}
		}
		return $c;
	}
	
	function querySingleUid($singleUid = 0, $whereParts = false)	{
			// Get requested row with values of order fields
		if ($GLOBALS['TCA'][$this->config->entriesTable]['ctrl']['virtual'])	{
			return array(intval($singleUid));
		}
		list($order, , $sortFields) = $this->getOrderString('__1', 'x');
		list($wp, $join, $ef, $wp_base) = $this->getUidQueryWhereStr(is_array($whereParts)?$whereParts:$this->whereParts, '__1');
		$ef = implode(' AND ', $ef);
		$sort = array();
		foreach ($sortFields as $field)	{
			$sort[] = $field['table'].'__1.'.$field['field'].' AS '.$field['table'].'___'.$field['field'];
		}
		$query = 'SELECT '.$this->config->entriesTable.'__1.uid as uid, '.implode(', ', $sort).' FROM '.implode('', $join).' WHERE '.$ef.(strlen($ef)&&count($wp_base)?' AND ':'').implode(' AND ', $wp_base).((strlen($ef)||count($wp_base))&&count($wp)?' AND ':'').implode($this->criteriaConnector, $wp).' AND '.$this->config->entriesTable.'__1.uid='.intval($singleUid).' ORDER BY '.$order.';';
		if ($GLOBALS['TYPO3_DB']->debugOutput || $GLOBALS['TYPO3_DB']->store_lastBuiltQuery)	{
			$GLOBALS['TYPO3_DB']->debug_lastBuiltQuery = $query;
		}
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		if (!$row)	{
			return;
		}
	
			// Fill out array with values of order fields
		$valArr = array();
		foreach ($sortFields as $field)	{
			$valArr[$field['table']][$field['field']] = $row[$field['table'].'___'.$field['field']];
		}

		list($order, $where) = $this->getOrderString('__1', $valArr, true);
		$wStr = $ef;
		$wStr .= ((strlen($wStr)&&count($wp_base))?' AND ':'').implode(' AND ', $wp_base);
		$wStr .= ((strlen($wStr)&&count($wp))?' AND ':'').implode($this->criteriaConnector, $wp);
		$wStrS = $wStr.((strlen($wStr)&&strlen($where))?' AND ':'').$where;
		$query = 'SELECT count(*) AS count FROM '.implode('', $join).' WHERE '.$wStrS.' ORDER BY '.$order.' LIMIT 1;';
		if ($GLOBALS['TYPO3_DB']->debugOutput || $GLOBALS['TYPO3_DB']->store_lastBuiltQuery)	{
			$GLOBALS['TYPO3_DB']->debug_lastBuiltQuery = $query;
		}
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		if ($row)	{
			$this->onlyIdx = $row['count'];
		}
		$query = 'SELECT '.$this->config->entriesTable.'__1.uid AS uid FROM '.implode('', $join).' WHERE '.$wStrS.' ORDER BY '.$order.' LIMIT 1;';
		if ($GLOBALS['TYPO3_DB']->debugOutput || $GLOBALS['TYPO3_DB']->store_lastBuiltQuery)	{
			$GLOBALS['TYPO3_DB']->debug_lastBuiltQuery = $query;
		}
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		if ($row)	{
			$this->previousItem = $row['uid'];
		}

		list($order, $where) = $this->getOrderString('__1', $valArr);
		$wStrS = $wStr.((strlen($wStr)&&strlen($where))?' AND ':'').$where;
		$query = 'SELECT '.$this->config->entriesTable.'__1.uid AS uid FROM '.implode('', $join).' WHERE '.$wStrS.' ORDER BY '.$order.' LIMIT 1;';
		if ($GLOBALS['TYPO3_DB']->debugOutput || $GLOBALS['TYPO3_DB']->store_lastBuiltQuery)	{
			$GLOBALS['TYPO3_DB']->debug_lastBuiltQuery = $query;
		}
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		if ($row)	{
			$this->nextItem = $row['uid'];
		}

		return array(intval($singleUid));
	}


	function execQuery($query, $returnCount = false)	{
		if ($GLOBALS['TYPO3_DB']->debugOutput || $GLOBALS['TYPO3_DB']->store_lastBuiltQuery)	{
			$GLOBALS['TYPO3_DB']->debug_lastBuiltQuery = $query;
		}
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		$ret = false;
		if ($returnCount)	{
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$ret = $row['uid'];
		} else	{
			$ret = array();
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				$ret[] = $row['uid'];
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $ret;
	}

	function queryUidList($itemCount = 0, $pointer = 0, $debug = 0)	{
		if (intval($this->listTable['virtual']))	{
			$this->loadFormTables();
			$rows = $this->data[$this->config->entriesTable];
			$ret = array();
			if (is_array($rows))	{
				foreach ($rows as $uid => $row)	{
					$ret[] = $uid;
				}
			}
			return $ret;
		} elseif (is_array($itemCount)&&($t = $itemCount['table'])&&($f = $itemCount['field']))	{
			$asp = '';
			if ($itemCount['MM'])	{
				$asp = -1;
			}
			list($wp, $join, $tmp1, $wp_base) = $this->getUidQueryWhereStr($this->whereParts, $asp, '', true);
			if ($this->conf['criteriaSelector.']['dependency'])	{
				list($wp_f, $join_f, $ef, $wp_base_f) = $this->getUidQueryWhereStr($this->criteriaItems, $asp, '', true);
			} else	{
				list($wp_f, $join_f, $ef, $wp_base_f) = $this->getUidQueryWhereStr(array($itemCount), $asp, '', true);
			}
			$ef = implode(' AND ', $ef);
			unset($join_f[$this->config->entriesTable]);
			$join = array_merge($join, $join_f);
			foreach ($wp_f as $key => $wp_i)	{
				if (strpos($wp_i, '###VALUE###')===false)	{
					if ($this->conf['criteriaSelector.']['dependency'])	{
						$wp[] = $wp_i;
					} elseif (substr($key, 0, strlen('__join'))==='__join')	{	
						$wp[] = $wp_i;
					}
				}
			}
			foreach ($wp_base_f as $key => $wp_i)	{
				if (strpos($wp_i, '###VALUE###')===false)	{
					if ($this->conf['criteriaSelector.']['dependency'])	{
						$wp_base[] = $wp_i;
					} elseif (substr($key, 0, strlen('__join'))==='__join')	{	
						$wp_base[] = $wp_i;
					}
				}
			}
			list($order, $join_o, $where_o) = $this->getDistinctOrder($itemCount);
			$join[] = $join_o;
			if (strlen($where_o))	{
				$wp[] = $where_o;
			}
			if ($itemCount['MM'])	{
				$fi = $itemCount['MM'].'.uid_foreign';
			} else	{
				$fi = $t.'.'.$f;
			}
			$query = 'SELECT DISTINCT('.$fi.') AS uid FROM '.implode('', $join).' WHERE '.$ef.(count($wp_base)?' AND '.implode(' AND ', $wp_base):'').(count($wp)?' AND ('.implode($this->criteriaConnector, $wp).')':'').' ORDER BY '.$order.';';
		} else	{
			list($wp, $join, $ef, $wp_base) = $this->getUidQueryWhereStr($this->whereParts);
			$ef = implode(' AND ', $ef);
			$offset = $itemCount*$pointer;
			if ($itemCount<0)	{
				$order = $this->getOrderString();
				$order = strlen($order)?(' ORDER BY '.$order):'';
				$query = 'SELECT count(DISTINCT '.$this->config->entriesTable.'.uid) AS uid FROM '.implode('', $join).' WHERE '.$ef.(count($wp_base)?' AND '.implode(' AND ', $wp_base):'').(count($wp)?' AND ('.implode($this->criteriaConnector, $wp).')':'').$order;
			} else	{
				list($order, $orderMinMax, $ojoin) = $this->getOrderString(false, false, false, true);
				$join = array_merge($join, $ojoin);
				$order = strlen($order)?(' ORDER BY '.$order):'';
				$query = 'SELECT '.$this->config->entriesTable.'.uid AS uid'.($orderMinMax?',':'').$orderMinMax.' FROM '.implode('', $join).' WHERE '.$ef.(count($wp_base)?' AND '.implode(' AND ', $wp_base):'').(count($wp)?' AND ('.implode($this->criteriaConnector, $wp).')':'').' GROUP BY '.$this->config->entriesTable.'.uid '.$order.(($itemCount>0)?(' LIMIT '.$offset.', '.$itemCount):'');
			}
		}
		return $this->execQuery($query, (is_int($itemCount)&&($itemCount<0))?true:false);
	}

	function getDistinctOrder($field)	{
		$join = '';
		$where = '';
		$order = $field['table'].'.'.$field['field'];
		switch ($field['row']['type'])	{
			case 9:		// dbrel
					// We sort DB-rel after their relations-sorting-order
				$fTable = $field['row']['flexform']['data']['sDEF'][$this->config->lDEF]['field_table'][$this->config->vDEF];
				$join = ', '.$fTable;
				t3lib_div::loadTCA($fTable);
				$o = $GLOBALS['TCA'][$fTable]['ctrl']['sortby'];
				if (!$o)	{
					$o = $GLOBALS['TCA'][$fTable]['ctrl']['default_sortby'];
					$o = trim(preg_replace('/(^|\s+)ORDER\s+BY(\s+|$)/siU', ' ', $o));
				}
				if (!$o)	{
					$o= $GLOBALS['TCA'][$fTable]['ctrl']['label'];
				}
				if ($o)	{
					$order = $fTable.'.'.$o;
				}
				if ($field['MM'])	{
					$where = $field['MM'].'.uid_foreign='.$fTable.'.uid';
				} else	{
					$where = $field['table'].'.'.$field['field'].'='.$fTable.'.uid';
				}
			break;
		}
		return array($order, $join, $where);
	}


	function getCriteriaSelector($critItem, $idx)	{
		list($opts, $e) = $this->getCriteriaOptionsMarkerArray($critItem, $idx);
		if ($e)	{
			return array('', $e);
		}
		if (($critItem['row']['type']=='10')&&$critItem['renderCheck'])	{
			$setSP = '###CriteriaSelectorCheckbox###';
			$unsetSP = '###CriteriaSelectorOption###';
			$marker = '###CriteriaItemCheckbox###';
			$otherMarker = '###CriteriaItem###';
		} else	{
			$setSP = '###CriteriaSelectorOption###';
			$unsetSP = '###CriteriaSelectorCheckbox###';
			$marker = '###CriteriaItem###';
			$otherMarker = '###CriteriaItemCheckbox###';
		}
		$selector[$marker] = array(
			0 => array(
				'_MARKERS' => array(
					'###TITLE###' => $critItem['row']['title'],
					'###ELEMENT_NAME###' => $this->prefixId.'[c]['.$critItem['thash'].']['.$critItem['fhash'].']'.(intval($critItem['row']['flexform']['data']['sDEF'][$this->config->lDEF]['multiple'][$this->config->vDEF])?'['.$idx.']':''),
				),
			),
		);
		$selector[$marker][0]['_SUBPARTS']	= array(
			$unsetSP => false,
			$setSP => array(
				'_MULTIPLE_MARKERS' => true,
				'_MARKERS' => $opts,
			),
		);
		return array($selector, '');
	}

	function getCriteriaOptionsMarkerArray($critItem, $idx)	{
		$markerArray = array();
		if (!(($critItem['row']['type']=='10')&&$critItem['renderCheck']))	{
			if ($critItem['defaultNone'])	{
				$markerArray['__ALL__'] = array(
					'###LABEL###' => $this->pi_getLL('label_show_none'),
					'###VALUE###' => '__ALL__',
					'###LINK_HREF###' => $this->hsc?htmlspecialchars($this->getCriteriaLink($critItem, '__ALL__', $idx)):$this->getCriteriaLink($critItem, '__ALL__', $idx),
					'###SELECTED###' => (!isset($this->piVars['c'][$critItem['thash']][$critItem['fhash']])||$this->piVars['c'][$critItem['thash']][$critItem['fhash']]=='__ALL__')?($this->conf['criteriaSelector.']['selectedValue']?$this->conf['criteriaSelector.']['selectedValue']:'selected="selected"'):'',
				);
			} else	{
				$markerArray['__ALL__'] = array(
					'###LABEL###' => $this->pi_getLL('label_show_all'),
					'###VALUE###' => '__ALL__',
					'###LINK_HREF###' => $this->hsc?htmlspecialchars($this->getCriteriaLink($critItem, '__ALL__', $idx)):$this->getCriteriaLink($critItem, '__ALL__', $idx),
					'###SELECTED###' => (!isset($this->piVars['c'][$critItem['thash']][$critItem['fhash']])||$this->piVars['c'][$critItem['thash']][$critItem['fhash']]=='__ALL__')?($this->conf['criteriaSelector.']['selectedValue']?$this->conf['criteriaSelector.']['selectedValue']:'selected="selected"'):'',
				);
			}
		};
		foreach ($critItem['values'] as $md5 => $value)	{
			list($key, $label, $e) = $this->getCriteriaOption($critItem, $md5, $value);
			if (strlen($e))	{
				return array('', $e);
			}
			$set = false;
			$iss = false;
			if (intval($critItem['row']['flexform']['data']['sDEF'][$this->config->lDEF]['multiple'][$this->config->vDEF]))	{
				$v = $this->piVars['c'][$critItem['thash']][$critItem['fhash']][$idx];
				if (isset($this->piVars['c'][$critItem['thash']][$critItem['fhash']][$idx]))	{
					$iss = true;
					if (intval($this->piVars['c'][$critItem['thash']][$critItem['fhash']][$idx]) & $value)	{
						$set = true;
					}
				}
			} else	{
				$v = $this->piVars['c'][$critItem['thash']][$critItem['fhash']];
				if (isset($this->piVars['c'][$critItem['thash']][$critItem['fhash']]))	{
					$iss = true;
					if (intval($this->piVars['c'][$critItem['thash']][$critItem['fhash']]) & $value)	{
						$set = true;
					}
				}
			}
			if ($critItem['row']['type']==10)	{
				$markerArray[$md5] = array(
					'###LABEL###' => $label,
					'###ELEMENT_NAME###' => $this->prefixId.'[c]['.$critItem['thash'].']['.$critItem['fhash'].']['.$value.']'.(intval($critItem['row']['flexform']['data']['sDEF'][$this->config->lDEF]['multiple'][$this->config->vDEF])?'['.$idx.']':''),
					'###CHECKED###' => $set?($this->conf['criteriaSelector.']['checkedValue']?$this->conf['criteriaSelector.']['checkedValue']:' checked="checked"'):'',
					'###LINK_HREF###' => $this->hsc?htmlspecialchars($this->getCriteriaLink($critItem, $key, $idx)):$this->getCriteriaLink($critItem, $key, $idx),
				);
			} else	{
				$markerArray[$md5] = array(
					'###LABEL###' => $label,
					'###VALUE###' => $key,
					'###LINK_HREF###' => $this->hsc?htmlspecialchars($this->getCriteriaLink($critItem, $key, $idx)):$this->getCriteriaLink($critItem, $key, $idx),
					'###SELECTED###' => ($iss&&!strcmp($v, $key))?($this->conf['criteriaSelector.']['selectedValue']?$this->conf['criteriaSelector.']['selectedValue']:'selected="selected"'):'',
				);
			}
		}
		return array($markerArray, '');
	}

	function getCriteriaOption($critItem, $md5, $value)	{
		$key = '';
		$label = '';
		switch ($critItem['row']['type'])	{
			/*
			 * TYPEADD
			 */
			case 8:			// String
				if ($this->config->pi_md5StringOptionValues)	{
					$key = substr(md5($value), 0, $this->config->pi_md5StringOptionValues);
				} else	{
					$key = $value;
				}
				$label = $value;
			break;
			case 1:		// Select
				$key = $value;
				$label = $critItem['row']['__selectItems'][$key];
			break;
			case 9:		// dbrel
				$key = $value;
				$label = $this->getDBRelLabel($critItem['row']['flexform']['data']['sDEF'][$this->config->lDEF], $value, $critItem['row']['alias']);
			break;
			default:
				$key = $value;
			break;
			case 4:		// Decimal
			case 5:		// Integer
				$key = $value;
				$label = $value;
			break;
			case 10:		// Multi Check
				$key = $value;
				$label = $this->getMultiCheckLabel($critItem['row']['__selectItems'], $value);
			break;
			case 7:		// Date
				$key = $value;
				$label = strftime($this->conf['criteriaSelector.']['dateFormat']?$this->conf['criteriaSelector.']['dateFormat']:'Y-m-d', $value);
			break;
			case 12:		// Time
				$key = $value;
				$label = strftime($this->conf['criteriaSelector.']['timeFormat']?$this->conf['criteriaSelector.']['timeFormat']:'H:M', $value);
			break;
			case 13:		// Timesec
				$key = $value;
				$label = strftime($this->conf['criteriaSelector.']['timesecFormat']?$this->conf['criteriaSelector.']['timesecFormat']:'H:M,S', $value);
			break;
			case 14:		// DateTime
				$key = $value;
				$label = strftime($this->conf['criteriaSelector.']['datetimeFormat']?$this->conf['criteriaSelector.']['datetimeFormat']:'Y-m-d H:M,S', $value);
			break;
			case 15:		// Year
				$key = $value;
				$label = strftime($this->conf['criteriaSelector.']['yearFormat']?$this->conf['criteriaSelector.']['yearFormat']:'Y', $value);
			break;
			case 6:		// Check
				$key = intval($value)?1:0;
				$tskey = $key?'yes':'no';
				$label = $this->cObj->stdWrap($this->conf['criteriaSelector.']['checkbox.'][$tskey], $this->conf['criteriaSelector.']['checkbox.'][$tskey.'.']);
				if (!$label)	{
									$label = $this->pi_getLL('criteriaSelector_checkbox_'.$tskey);
				}
			break;
			case 200:		// container
			case 2:		// Text
			case 3:		// RTE
				return array('', '', $this->pi_getLL('error_invalid_criteria_field_usercriteria'));
			break;
			default:
				return array('', '', str_replace('###TYPE###', 'Y'.$critItem['row']['type'], $this->pi_getLL('error_invalid_criteria_field_unknown')));
			break;
		}
		return array($key, $label, '');
	}

	function singleViewLink($uid, $p = false)	{
		$page = intval($this->piVars['p']);
		if ($p===false)	{
			$p = $page;
		}
		$uid = intval($uid);
		if (!$uid)	{
			$uid = '';
		}
		if (!$p)	{
			$p = '';
		}
		$altPageId = 0;
		if ($uid)	{
			if ($this->conf[$this->tskey.'.']['singleViewLink.']['page'] || $this->conf[$this->tskey.'.']['singleViewLink.']['page.'])	{
				$altPageId = intval($this->localcObj->stdWrap($this->conf[$this->tskey.'.']['singleViewLink.']['page'], $this->conf[$this->tskey.'.']['singleViewLink.']['page.']));
			}
		} else	{
			if ($this->conf[$this->tskey.'.']['listViewLink.']['page'] || $this->conf[$this->tskey.'.']['listViewLink.']['page.'])	{
				$altPageId = intval($this->localcObj->stdWrap($this->conf[$this->tskey.'.']['listViewLink.']['page'], $this->conf[$this->tskey.'.']['listViewLink.']['page.']));
			}
		}
		return $this->pi_linkTP_keepPIvars_url(array('v' => $uid, 'p' => $p),  $this->doCache, 0, $altPageId);
	}

	function getMultiCheckLabel($items, $value)	{
		$l = array();
		if (is_array($items))	{
			foreach ($items as $bit => $label)	{
				if ($value&(1<<$bit))	{
					$l[] = $label;
				}
			}
		}
		if (!count($l))	{
			$l = array($this->conf['fieldTypes.']['multiCheck.']['nothingSet']?$this->conf['fieldTypes.']['multiCheck.']['nothingSet']:$this->pi_getLL('label_none'));
		}
		$jc = $this->conf['fieldTypes.']['multiCheck.']['implodeString']?$this->conf['fieldTypes.']['multiCheck.']['implodeString']:' / ';
		$label = implode($jc, $l);
		return $label;
	}

	function getCriteriaLink($critItem, $key, $idx)	{
		$pi = array();
		if ($key=='__ALL__')	{
			if (intval($critItem['row']['flexform']['data']['sDEF'][$this->config->lDEF]['multiple'][$this->config->vDEF]))	{
				$pi['c'][$critItem['thash']][$critItem['fhash']][$idx] = '';
			} else	{
				$pi['c'][$critItem['thash']][$critItem['fhash']] = '';
			}
		} else	{
			if (($critItem['row']['type']==10)&&$critItem['renderCheck'])	{
				$value = intval($this->piVars['c'][$critItem['thash']][$critItem['fhash']]);
				$pi['c'][$critItem['thash']][$critItem['fhash']][$value^$key] = '1';
			} else	{
				if (intval($critItem['row']['flexform']['data']['sDEF'][$this->config->lDEF]['multiple'][$this->config->vDEF]))	{
					$pi['c'][$critItem['thash']][$critItem['fhash']][$idx] = $key;
				} else	{
					$pi['c'][$critItem['thash']][$critItem['fhash']] = $key;
				}
			}
		}
		$pi['p'] = '';
		$pi['v'] = '';
		$href = $this->pi_linkTP_keepPIvars_url($pi,  $this->doCache);
		return $href;
	}

	function getSelectBoxItems($flex)	{
		$selectItems = array();
		$tmp = tx_kbshop_abstract::getFlexformChilds($flex, 'list_value_section', 'list_value_field', array('field_value', 'field_index'), 'sDEF', $this->config->lDEF, $this->config->vDEF);
		if (is_array($tmp)&&count($tmp))	{
			foreach ($tmp as $tmpArr)		{
				$selectItems[$tmpArr['field_index']] = $tmpArr['field_value'];
			}
		}
		return $selectItems;
	}

	function getDBRelLabel($flex, $value, $fieldname, $fArr = false)	{
		$table = $flex['field_table'][$this->config->vDEF];
		if (is_array($fArr)&&(intval($flex['field_maxitems'][$this->config->vDEF])>1))	{
			if ($tmp = $this->labelCacheMM[$table][$fArr['__key'].'_'.$this->localcObj->data['uid']])	{
				$this->lastRelUids = $tmp[0];
				return $tmp[1];
			}
		} else	{
			if ($this->labelCache[$table][$value])	{
				return $this->labelCache[$table][$value];
			}
		}
		$this->lastRelUids = '';
		if (is_array($fArr)&&(intval($flex['field_maxitems'][$this->config->vDEF])>1))	{
			$mmtable = $this->config->mmRelationTablePrefix.$fArr['__key'].$this->config->mmRelationTablePostfix;
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_foreign', $mmtable, 'uid_local='.$this->localcObj->data['uid'], '', 'sorting');
			$str1 = '';
			$str2 = '';
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				$str1 .= ($str1?',':'').$row['uid_foreign'];
				$str2 .= ($str2?', ':'').$this->getDBRelLabel($flex, $row['uid_foreign'], $fieldname);
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
			if (strlen($str1)&&strlen($str2))	{
				$this->labelCacheMM[$table][$fArr['__key'].$this->localcObj->data['uid']] = array($str1, $str2);
			}
			$this->lastRelUids = $str1;
			return $str2;
		}
		$rec = tx_kbshop_abstract::getRecord($table, $value);
		if (is_array($rec)&&count($rec))	{
			if ($this->conf[$this->tskey.'.']['dbRelLabels.'][$fieldname]||is_array($this->conf[$this->tskey.'.']['dbRelLabels.'][$fieldname.'.']))	{
				if (!is_object($this->dbRelcObj))	{
					$this->dbRelcObj = clone($this->localcObj);
				}
				$this->dbRelcObj->start($rec, $table);
				$this->labelCache[$table][$value] = $this->dbRelcObj->stdWrap($this->conf[$this->tskey.'.']['dbRelLabels.'][$fieldname], $this->conf[$this->tskey.'.']['dbRelLabels.'][$fieldname.'.']);
			} else	{
				$this->labelCache[$table][$value] = tx_kbshop_abstract::getRecordTitle($table, $rec);
			}
			return $this->labelCache[$table][$value];
		}
		return '';
	}

	function loadTemplate($tskey)	{
		$data = '';
		$file = t3lib_div::getFileAbsFileName($this->cObj->stdWrap($this->conf['template.'][$tskey], $this->conf['template.'][$tskey.'.']));
		if (is_readable($file))	{
			$data = t3lib_div::getURL($file);
		}
		if (!strlen($data))	{
			$file = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'field_template', 'sDEF', $this->config->lDEF, $this->config->vDEF);
			$file = t3lib_div::getFileAbsFileName($file);
			if (is_readable($file))	{
				$data = t3lib_div::getURL($file);
			}
		}
		if (!strlen($data))	{
			$file = t3lib_div::getFileAbsFileName('EXT:'.$this->extKey.'/res/default_template.html');
			if (is_readable($file))	{
				$data = t3lib_div::getURL($file);
			}
		}
		if (!strlen($data))	{
			return array(str_replace('###TSKEY###', $tskey, $this->pi_getLL('error_no_template_accessible')).'<br />'.chr(10), true);
		}
		return array($data, false);
	}

	function error($code, $vars)	{
		list($ret, $e) = $this->loadTemplate('error');
		if ($e)	{
			return array('', $ret);
		}
		$tmpl = $this->cObj->getSubpart($ret, '###ERROR_tmpl_'.$code.'###');
		if (!strlen($tmpl))	{
			$tmpl = $this->cObj->getSubpart($ret, '###ERROR_tmpl###');
		}
		if (!strlen($tmpl))	{
			return array('', $this->pi_getLL('error_no_error_subpart').'<br />'.chr(10));
		}
		$markerArray = array(
			'###ERROR_MSG###' => $this->pi_getLL('error_KEY_'.$code),
			'###ERROR_CODE###' => $code,
		);
		$tmpl = $this->cObj->substituteMarkerArray($tmpl, $markerArray);
		$part = $this->cObj->getSubpart($tmpl, '###DEBUG_VARS###');
		if (strlen($part))	{
			$items = '';
			foreach ($vars as $var)	{
				$markerArray = array(
					'###DEBUG_LABEL###' => $var[0],
					'###DEBUG_VALUE###' => $var[1],
				);
				$items .= $this->cObj->substituteMarkerArray($part, $markerArray);
			}
			$tmpl = $this->cObj->substituteSubpart($tmpl, '###DEBUG_VARS###', $items);
		}
		return array($tmpl, '');
	}

	

	function getOrderString($as_postfix = false, $orig_postfix = false, $invert = false, $minmax = false)	{
		$order = tx_kbshop_abstract::getFlexformChilds($this->cObj->data['pi_flexform'], 'list_order_section', 'list_order_item', array('field_list_order', 'field_order_custom', 'field_list_direction'), 'listView', $this->config->lDEF, $this->config->vDEF);
		$ret = '';
		$where = '';
		$oldEqual = '';
		$mmStr = '';
		$sortArr = array();
		$join = array();
		foreach ($order as $oArr)	{
			$f = $oArr['field_list_order'];
			if (substr($f, 0, $l = strlen($this->config->piComparePrefix))===$this->config->piComparePrefix)	{
				$f = substr($f, $l);
			} 
			list($f, $t) = explode('___', $f, 2);
			if (!strlen($t))	{
				$t = $this->config->entriesTable;
			}
			$sortArr[] = array(
				'table' => $t,
				'field' => $f,
			);
			$origT = $t;
			if (strlen($as_postfix))	{
				$origT = $t;
				$t = $t.$as_postfix;
			}
			$to = $oArr['field_order_custom'];
			$to = str_replace('###ENTRIES_TABLE###', $this->config->entriesTable.$as_postfix, $to);
			$to = str_replace('###TABLE###', $t, $to);
			$to = str_replace('###FIELD###', $f, $to);
			if ($minmax)	{
				$ret .= (strlen($ret)?', ':'').$t.'___'.$f.'___order '.(($invert xor (strtolower($oArr['field_list_direction'])=='asc'))?'ASC':'DESC');
				if (($mm = $GLOBALS['TCA'][$origT]['columns'][$f]['config']['MM'])&&($ft = $GLOBALS['TCA'][$origT]['columns'][$f]['config']['foreign_table']))	{
					$join[$t.'.'.$f] = ' LEFT JOIN '.$mm.' AS '.$mm.'_'.$f.' ON '.$t.'.uid='.$mm.'_'.$f.'.uid_local LEFT JOIN '.$ft.' AS '.$ft.'_'.$f.' ON '.$mm.'_'.$f.'.uid_foreign='.$ft.'_'.$f.'.uid';
					$ftsf = tx_kbshop_abstract::getSortingField($ft);
					if (preg_match('/^\s*ORDER\s+BY\s+(.*)$/is', $ftsf, $matches)>0)	{
						$ftsf = $matches[1];
					}
					list($ftsf) = t3lib_div::trimExplode(',', $ftsf, 1);
					if (strlen($to))	{
						$mmStr .= (strlen($mmStr)?', ':'').(($invert xor (strtolower($oArr['field_list_direction'])=='asc'))?'min(':'max(').$to.') AS '.$t.'___'.$f.'___order';
					} else	{
						$mmStr .= (strlen($mmStr)?', ':'').(($invert xor (strtolower($oArr['field_list_direction'])=='asc'))?'min(':'max(').$ft.'_'.$f.'.'.$ftsf.') AS '.$t.'___'.$f.'___order';
					}
				} else	{
					if (strlen($to))	{
						$mmStr .= (strlen($mmStr)?', ':'').(($invert xor (strtolower($oArr['field_list_direction'])=='asc'))?'min(':'max(').$to.') AS '.$t.'___'.$f.'___order';
					} else	{
						$mmStr .= (strlen($mmStr)?', ':'').(($invert xor (strtolower($oArr['field_list_direction'])=='asc'))?'min(':'max(').$t.'.'.$f.') AS '.$t.'___'.$f.'___order';
					}
				}
			} else	{
				if (strlen($to))	{
					$ret .= (strlen($ret)?', ':'').$to.' '.(($invert xor (strtolower($oArr['field_list_direction'])=='asc'))?'ASC':'DESC');
				} else	{
					$ret .= (strlen($ret)?', ':'').$t.'.'.$f.' '.(($invert xor (strtolower($oArr['field_list_direction'])=='asc'))?'ASC':'DESC');
				}
			}
			if (strlen($as_postfix)&&(is_array($orig_postfix)||strlen($orig_postfix)))	{
				$where .= 	(strlen($where)?' OR ':'').
										'('.
											($oldEqual?($oldEqual.' AND '):'').
											$t.'.'.$f.
												(($invert xor (strtolower($oArr['field_list_direction'])=='asc'))?'>':'<').
											(is_array($orig_postfix)?
												$GLOBALS['TYPO3_DB']->fullQuoteStr($orig_postfix[$origT][$f], $origT):
												($origT.$orig_postfix.'.'.$f)
											).
										')';
				$oldEqual .= ($oldEqual?' AND ':'').
										$t.'.'.$f.
											'='.
										(is_array($orig_postfix)?
											$GLOBALS['TYPO3_DB']->fullQuoteStr($orig_postfix[$origT][$f], $origT):
											$origT.$orig_postfix.'.'.$f
										);
			}
		}
		$ret .= (strlen($ret)?', ':'').$this->config->entriesTable.$as_postfix.'.uid'.($invert?' DESC':' ASC');
		$sortArr[] = array(
			'table' => $this->config->entriesTable,
			'field' => 'uid',
		);
		if (strlen($as_postfix)&&(is_array($orig_postfix)||strlen($orig_postfix)))	{
			$where .= 	(strlen($where)?' OR ':'').
									'('.
										($oldEqual?($oldEqual.' AND '):'').
										$this->config->entriesTable.$as_postfix.'.uid'.
											($invert?'<':'>').
										(is_array($orig_postfix)?
											$GLOBALS['TYPO3_DB']->fullQuoteStr($orig_postfix[$this->config->entriesTable]['uid'], $this->config->entriesTable):
											$this->config->entriesTable.$orig_postfix.'.uid'
										).
									')';
		}
		if ($ret)	{
			if (strlen($as_postfix)&&(is_array($orig_postfix)||strlen($orig_postfix)))	{
				return array($ret, '('.$where.')', $sortArr);
			} elseif ($minmax)	{
				return array($ret, $mmStr, $join);
			} else	{
				return $ret;
			}
		}
		return '';
	}

	function setDefinedCriteria($crits = false)	{
		global $TCA;
		t3lib_div::loadTCA($this->config->entriesTable);
		if (!$crits)	{
			$this->criteriaConnector = ' '.$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'field_criteriaConnector', 'criteria', $this->config->lDEF, $this->config->vDEF).' ';
			$crits = tx_kbshop_abstract::getFlexformChilds($this->cObj->data['pi_flexform'], 'list_criteria_section', array('list_criteria_item', 'list_subcriteria'), '*', 'criteria', $this->config->lDEF, $this->config->vDEF);
			$crits = tx_kbshop_abstract::getFlexformChilds($this->cObj->data['pi_flexform'], 'list_criteria_section', array('list_criteria_item', 'list_subcriteria'), '*', 'criteria', $this->config->lDEF, $this->config->vDEF);
		}
		$multiSortFields[$thash][$fhash] = array();
		foreach ($crits as $idx => $crit)	{
			$subWhere = false;
			if ($crit['field_criteriaConnector'])	{
				$oldWhereParts = $this->whereParts;
				$oldCriteriaItems = $this->criteriaItems;
				$this->whereParts = array();
				$this->criteriaItems = array();
				$subcrits = tx_kbshop_abstract::getFlexformChilds($crit, 'list_criteria_section', array('list_criteria_item', 'list_subcriteria'), '*', 'criteria', $this->config->lDEF, $this->config->vDEF, true);
				$this->setDefinedCriteria($subcrits);
				$subWhere = $this->whereParts;
				$this->whereParts = $oldWhereParts;
				$this->criteriaItems = array_merge($oldCriteriaItems, $this->criteriaItems);
				$this->whereParts[$idx] = array(
					'subWhere' => $subWhere,
					'connector' => ' '.$crit['field_criteriaConnector'].' ',
				);
			} else	{
				$f = $crit['field_compare_field'];
				$renderCheck = false;
				$dynField = false;
				if (substr($f, 0, $l = strlen($this->config->piComparePrefix))===$this->config->piComparePrefix)	{
					$f = substr($f, $l);
					$dynField = true;
				} 
				$userValue = false;
				list($f, $t) = explode('___', $f, 2);
				if (!strlen($t))	{
					$t = $this->config->entriesTable;
					$sectionField = '';
				} else	{
					list(, $tmp) = explode('___', $t, 2);
					$sectionField = substr($tmp, 0, -strlen($this->configSectionTablePostfix));
////				$sectionField = $this->config->fieldPrefix.substr($t, strlen($this->config->sectionTablePrefix), strlen($t)-(strlen($this->config->sectionTablePrefix)+strlen($this->config->sectionTablePostfix)));
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
				$value = false;
				$allowUsersel = false;
				$disableNegate = false;
				$compareStr = false;
				$mmt = false;
				if (!$dynField)	{
					$fArr = $this->getDefaultType($f);
				}
				switch ($fArr['type'])	{
					case 9:		// dbrel
					case 1:		// Select
						$maxitems = intval($this->pi_getFFvalue($fArr['flexform'], 'field_maxitems', 'sDEF', $this->config->lDEF, $this->config->vDEF));
////					$value = $crit[$this->config->fieldPrefix.$fArr['__key'].$this->fieldPostfix];
						$value = $crit['field_compare_value_'.$f];
							// Trick #17
						if (($fArr['type']==9)&&intval($this->pi_getFFvalue($fArr['flexform'], 'multiple', 'sDEF', $this->config->lDEF, $this->config->vDEF)))	{
							$GLOBALS['TSFE']->register[$thash.'_'.$fhash] = intval($this->piVars['c'][$thash][$fhash][$idx]);
						} else	{
							$GLOBALS['TSFE']->register[$thash.'_'.$fhash] = intval($this->piVars['c'][$thash][$fhash]);
						}
						if ($maxitems > 1)	{
							$mmt = $this->config->mmRelationTablePrefix.$fArr['__key'].$this->config->mmRelationTablePostfix;
						}
						$allowUsersel = true;
						$compareStr = '###FIELDNAME### IN (###VALUE###)';
					break;
					case 8:		// String
						$allowUsersel = true;
					case 2:		// Text
					case 3:		// RTE
						switch ($crit['field_compare_string'])	{
							case 'begins_with':
								$compareStr = '###FIELDNAME### LIKE \'###VALUE###%\'';
							break;
							case 'ends_with':
								$compareStr = '###FIELDNAME### LIKE \'%###VALUE###\'';
							break;
							case 'contains':
								$compareStr = '###FIELDNAME### LIKE \'%###VALUE###%\'';
							break;
							default:
							case 'equals':
								$compareStr = '###FIELDNAME### = \'###VALUE###\'';
							break;
							case 'like':
								$compareStr = '###FIELDNAME### LIKE \'###RAWVALUE###\'';
							break;
						}
						$value = $crit[$this->config->piComparePrefix.'string'];
					break;
					case 4:		// Decimal
						$value = doubleval($crit[$this->config->piComparePrefix.'double']);
					case 5:		// Integer
						if ($value===false)	{
							$value = intval($crit[$this->config->piComparePrefix.'int']);
						}
						switch ($crit['field_compare_number'])	{
							case 'equals':
								$compareStr = '###FIELDNAME### = \'###VALUE###\'';
							break;
							case 'bigger':
								$compareStr = '###FIELDNAME### > \'###VALUE###\'';
							break;
							case 'in':
								$value = $crit[$this->config->piComparePrefix.'string'];
								$compareStr = '###FIELDNAME### IN (###VALUE###)';
							break;
							case 'smaller':
							default:
								$compareStr = '###FIELDNAME### < \'###VALUE###\'';
							break;
						}
					break;
					case 7:		// Date
					case 12:		// Time
					case 13:		// Timesec
					case 14:		// DateTime
					case 15:		// Year
						$value = intval($crit[$this->config->piComparePrefix.'date']);
						$allowUsersel = true;
						switch ($crit['field_compare_date'])	{
							case 'equals':
								$compareStr = '###FIELDNAME### = \'###VALUE###\'';
							break;
							case 'bigger':
								$compareStr = '###FIELDNAME### > \'###VALUE###\'';
							break;
							case 'biggerNow':
								$compareStr = '###FIELDNAME### > (unix_timestamp()+'.(intval($crit['field_compare_value_dateoffset'])*3600).')';
							break;
							case 'smallerNow':
								$compareStr = '###FIELDNAME### < (unix_timestamp()-'.(intval($crit['field_compare_value_dateoffset'])*3600).')';
							break;
							case 'smaller':
							default:
								$compareStr = '###FIELDNAME### < \'###VALUE###\'';
							break;
						}
					break;
					case 6:		// Check
						$allowUsersel = true;
						$disableNegate = true;
						$value = intval($crit[$this->config->piComparePrefix.'bool']);
						if ($value)	{
							$compareStr = '###FIELDNAME### != 0';
						} else	{
							$compareStr = '###FIELDNAME### = 0';
						}
					break;
					case 10:		// Multi Check
						$allowUsersel = true;
						$value = $crit['field_compare_value_'.$f];
						$compareStr = '###FIELDNAME### = ###VALUE###';
						$renderCheck = intval($crit['field_compare_rendercheck_multibool'])?true:false;
					break;
					case 200:		// container
						return $this->pi_getLL('error_invalid_criteria_field_container');
					break;
						/*
						 * TYPEADD
						 */
					default:
							return str_replace('###TYPE###', 'X'.$fArr['type'], $this->pi_getLL('error_invalid_criteria_field_unknown'));
					break;
				}
				if (strlen($crit['field_compare_custom']))	{
					$cs = trim($crit['field_compare_custom']);
					if (substr($cs , 0, 1)==='<')	{
						$key = trim(substr($cs ,1));
						$cF = t3lib_div::makeInstance('t3lib_TSparser');
							// $name and $conf is loaded with the referenced values.
						list($name, $conf) = $cF->getVal($key, $GLOBALS['TSFE']->tmpl->setup);
							// Getting the cObject
						$GLOBALS['TT']->incStackPointer();
						$cs = $this->localcObj->cObjGetSingle($name,$conf,$key);
						$GLOBALS['TT']->decStackPointer();
					}
					if (strlen($cs))	{
						$compareStr = $cs;
					}
				}
				if ($allowUsersel)	{
					if (intval($crit['field_compare_usersel']))	{
						$multi = intval($this->pi_getFFvalue($fArr['flexform'], 'multiple', 'sDEF', $this->config->lDEF, $this->config->vDEF));
							// We always want a exact value when having a user selection:
						if ($fArr['type']==8)	{
								// We do a string match and we are having md5 sums as values in the JSMENUs. This way we avoid many problems with special characters as the value in JSMENUs (which normally simple page links)
							$compareStr = 'SUBSTRING(md5(###FIELDNAME###), 1, '.$this->config->pi_md5StringOptionValues.') = \'###VALUE###\'';
						} elseif ($fArr['type']==10)	{
								// When we do a user-selection match on multi-check boxes do them via & operator - so we select each items having the selected flags set (not caring about if also other flags are set)
							$compareStr = '(###FIELDNAME### & ###VALUE###) = ###VALUE###';
							if (is_array($this->piVars['c'][$thash][$fhash]))	{
								$mask = 0;
								foreach ($this->piVars['c'][$thash][$fhash] as $v=> $set)	{
									if (intval($set))	{
										$mask |= intval($v);
									}
								}
								$this->piVars['c'][$thash][$fhash] = $mask;
							}
						} elseif (($fArr['type']==9)&&$multi)	{
							$compareStr = '(###FIELDNAME### = \'###VALUE###\'';
							if (is_array($multiSortFields[$thash][$fhash]))	{
								foreach ($multiSortFields[$thash][$fhash] as $fidx => $val)	{
									$compareStr .= ' AND ###SORTFIELD_'.$idx.'### != ###SORTFIELD_'.$fidx.'###';
								}
							}
							$compareStr .= ')';
							$multiSortFields[$thash][$fhash][$idx] = true;
						} else	{
							$compareStr = '###FIELDNAME### = \'###VALUE###\'';
						}
						
						if (($fArr['type']==9)&&intval($this->pi_getFFvalue($fArr['flexform'], 'multiple', 'sDEF', $this->config->lDEF, $this->config->vDEF)))	{
							$pival = $this->piVars['c'][$thash][$fhash][$idx];
						} else	{
							$pival = $this->piVars['c'][$thash][$fhash];
						}

							// Set return array:
						$c = array(
							'table' => $t,
							'field' => $f,
							'compareStr' => $compareStr,
							'row' => $fArr,
							'value' => (isset($pival)&&strcmp($pival, '__ALL__'))?($value = $pival):false,
							'thash' => $thash,
							'fhash' => $fhash,
							'MM' => $mmt,
							'defaultNone' => intval($crit['field_compare_defaultNone']),
						);
						if ($renderCheck)	{
							$c['renderCheck'] = true;
						}
						$this->criteriaItems[] = $c;
							// Only continue when no criteria value was set for this user selecatable criteria.
						if ($c['defaultNone']&&($c['value']===false))	{
							$compareStr = '0';
							$c['compareStr'] = '0';
						}
						if (($c['value']===false)&&!$c['defaultNone'])	{
							continue;
						}
						$userValue = true;
							// There was a value set for this criteria. Set also the WHERE array so the WHERE string get's created properly.
					}
				}
				$value = $this->cObj->insertData($value);
				$compareStr = str_replace('###VALUE###', $GLOBALS['TYPO3_DB']->quoteStr($value, $t), $compareStr);
				$compareStr = str_replace('###RAWVALUE###', str_replace('\\%', '%', $GLOBALS['TYPO3_DB']->quoteStr($value, $t)), $compareStr);
				if ((!$disableNegate)&&(!$userValue)&&intval($crit['field_compare_negate']))	{
					$compareStr = 'NOT ('.$compareStr.')';
				}
				$this->whereParts[$idx] = array(
					'table' => $t,
					'field' => $f,
					'compareStr' => $compareStr,
					'row' => $fArr,
					'value' => $value,
					'MM' => $mmt,
					'userValue' => $userValue,
				);
			}
		}
		foreach ($this->criteriaItems as $idx => $critItem)	{
			if ($critItem['row']['type']=='10')	{
				$items = $this->getMultiCheckOptions($critItem);
			} else	{
				$tmpCrit = $critItem;
				$items = $this->queryUidList($tmpCrit, 0, 1);
				if (is_array($conf = $this->conf['criteriaSelector.']['valid.'][$critItem['table'].'.'][$critItem['field'].'.']))	{
					$new_items = array();
					foreach ($items as $key => $value)	{
						$rec = tx_kbshop_abstract::getRecord($critItem['table'], $value);
						if (is_array($rec))	{
							$this->localcObj->start($rec, $critItem['table']);
							if ($this->checkIf($conf))	{
								$new_items[$key] = $value;
							}
						}
					}
					$items = $new_items;
				}
			}
			$this->criteriaItems[$idx]['values'] = $items;
		}
		return '';
	}

	function getMultiCheckOptions($critItem)	{
		$ret = array();
		if (is_array($i = $critItem['row']['flexform']['data']['sDEF'][$this->config->lDEF]['list_value_section']['el']))	{
			$cnt = 0;
			foreach ($i as $idx => $iArr)	{
				$ret[] = 1<<$cnt;
				$cnt++;
			}
		}
		return $ret;
	}

	function getDefaultType($f)	{
		$type = 0;
		switch ($f)	{
			case 'uid':
				$type = 5;
			break;
			case 'crdate':
			case 'tstamp':
			case 'starttime':
			case 'endtime':
				$type = 7;
			break;
			case 'deleted':
			case 'hidden':
				$type = 6;
			break;
			case 'title':
				$title = $GLOBALS['TSFE']->sL($GLOBALS['TCA'][$this->config->entriesTable]['columns']['title']['label']);
				$type = 8;
			break;
			case 'category':
				$flex = array(
					'data' => array(
						'sDEF' => array(
							$this->config->lDEF => array(
								'field_table' => array(
									$this->config->vDEF => $this->config->categoriesTable,
								),
							),
						),
					),
				);
				$title = $GLOBALS['TSFE']->sL($GLOBALS['TCA'][$this->config->entriesTable]['columns']['category']['label']);
			case 'cruser_id':
				$type = 9;
			break;
		}
		$ret = array(
			'type' => $type,
		);
		if ($flex)	{
			$ret['flexform'] = $flex;
		}
		if ($title)	{
			$ret['title'] = $title;
		}
		return $ret;
	}

/*
	function getDistinctValues($table, $field, $prop)	{
		global $TCA;
		if (($prop['type']=='9')&&(intval($prop['flexform']['data']['sDEF'][$this->config->lDEF]['field_maxitems'][$this->config->vDEF])>1))	{
			$f = substr($field, strlen($this->config->fieldPrefix));
			$mmtable = $this->config->mmRelationTablePrefix.$f.$this->config->mmRelationTablePostfix;
			$ftable = $prop['flexform']['data']['sDEF'][$this->config->lDEF]['field_table'][$this->config->vDEF];
			$sorting = $ftable.'.'.$TCA[$table]['ctrl']['sortby'];
			if (!$sorting)	{
				$sorting = $TCA[$table]['ctrl']['default_sortby'];
			}
			if (!$sorting)	{
				$lf = $TCA[$table]['ctrl']['label'];
				if ($lf)	{
					$sorting = $lf;
				} else	{
					$sorting = 'uid';
				}
			}
			$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('DISTINCT '.$ftable.'.uid AS '.$field, $table, $mmtable, $ftable, ' AND '.tx_kbshop_abstract::enableFields($table).' AND '.tx_kbshop_abstract::enableFields($ftable), '', $sorting);
		} else	{
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('DISTINCT '.$field, $table, tx_kbshop_abstract::enableFields($table), '', $field);
		}
		$ret = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			if ($prop['type']=='9')	{
				if ($row[$field])	{
					$ret[md5($row[$field])] = $row[$field];
				}
			} else	{
				$ret[md5($row[$field])] = $row[$field];
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $ret;
	}
*/

	function getField($field, $section = '', $sub = false, $parent = false, $pkey = false)	{
		if (!$sub)	{
			$sub = $this->propertiesMerged;
		}
		if ($sub[$field])	{
			if ((!strlen($section))||(strlen($section)&&($pkey==$section)&&($parent['type']==200)))	{
				$a = $sub[$field]['alias']?strtolower($sub[$field]['alias']):$sub[$field]['uid'];
				$sub[$field]['__key'] = preg_replace('/[^a-z0-9_]/', '', $a);
				return $sub[$field];
			} 
		}
		if (is_array($sub)&&count($sub))	{
			foreach ($sub as $f => $fArr)	{
				if (is_array($fArr['_SUBPROPS'])&&count($fArr['_SUBPROPS']))	{
					$r = $this->getField($field, $section, $fArr['_SUBPROPS'], $fArr, $f);
					if ($r)	{
						return $r;
					}
				}
			}
		}
		return false;
	}

	function checkPiVars()	{
		if (isset($this->piVars['p']))	{
			$this->piVars['p'] = t3lib_div::intInRange($this->piVars['p'], 0);
		}
	}

	/**
	 * Detects, if a save command has been triggered.
	 *
	 * @return	boolean		True, then save the document (data submitted)
	 */
	function doProcessData()	{
		$this->sessionData = $GLOBALS['TSFE']->fe_user->getKey('ses', $this->prefixId);
		$this->data = $this->sessionData['basket'];
		$this->doSave = t3lib_div::_GP('doSave');
		$out = $this->doSave || isset($_POST['_savedok_x']) || isset($_POST['_saveandclosedok_x']) || isset($_POST['_savedokview_x']) || isset($_POST['_savedoknew_x']);
		$traverse = array();
		$after = array();
		if (is_array($this->data))	{
			foreach ($this->data as $table => $tableVals)	{
				if (t3lib_div::inList($this->conf['forms.']['respectRequired'], $table))	{	
					$after[] = $table;
				} else	{
					$traverse[] = $table;
				}
			}
			$traverse = array_merge($traverse, $after);
			$rt = t3lib_div::trimExplode(',', $this->conf['forms.']['respectRequired'], 1);
			foreach ($traverse as $table)	{
				if (is_array($this->data[$table]))	{
					$this->checkTableValues($table, $this->data[$table], false);
				}
				foreach ($rt as $rtk)	{
					list($rtt, $ct) = t3lib_div::trimExplode('=', $rtk, 1);
					if ($ct)	{
						$cta = t3lib_div::trimExplode('/', $ct, 1);
						foreach ($cta as $ctt)	{
							if (!(is_array($this->data[$ctt])&&count($this->data[$ctt])))	{
								unset($this->data[$rtt]);
							}
						}
					}
				}
			}
		}
		return $out;
	}
	
	
	/**
	 * Do processing of data, submitting it to TCEmain.
	 *
	 * @return	void
	 */
	function processData()	{
			// GPvars specifically for processing:
		$this->postedData = $data = t3lib_div::_GP('data');
		if (!is_array($this->data))	{
			$this->data = array();
		}
		if (!is_array($data))	{
			$data = array();
		}
		$this->data = tx_kbshop_misc::array_merge_recursive_overrule($this->data, $data);

		$tce = t3lib_div::makeInstance('tx_kbshop_t3lib_TCEmain');
		$tce->stripslashes_values=0;

		$tce->debug = 0;
		$tce->disableRTE = 0;
		$this->loadFormTables();
		$this->formError = 0;
		$traverse = array();
		$after = array();
		foreach ($this->data as $table => $tableVals)	{
			if (t3lib_div::inList($this->conf['forms.']['respectRequired'], $table))	{	
				$after[] = $table;
			} else	{
				$traverse[] = $table;
			}
		}
		$traverse = array_merge($traverse, $after);
		$localCObj = clone($this->cObj);
		foreach ($traverse as $table)	{
				// Check if a virtual table got submitted.
			if (!$this->formTables[$table]) continue;
			$tableVals = $this->data[$table];
				// Fake access to virtual tables.
			$nTableVals = array();
			$tmpUser = $this->formTableUsers[$table];
			$sBEU = $GLOBALS['BE_USER'];
			$GLOBALS['BE_USER'] = $tmpUser;
			foreach ($tableVals as $id => $row)	{
				$tce->recInsertAccessCache[$table][$row['pid']] = 1;
				$tce->recUpdateAccessCache[$table][$id] = 1;
				if (!$row['pid'])	{
					$newRow = $this->initTableRow($table, $id, $this->conf[$this->tskey.'.']['forms.']);
					$row = t3lib_div::array_merge_recursive_overrule($newRow, $row);
				}
				$nTableVals['NEW'.$id] = $row;
			}
			$tmpData = array();
			$tmpData[$table] = $nTableVals;
			$tce->BE_USER = $tmpUser;
			$tce->start($tmpData, array(), $tmpUser);
			$tce->process_datamap();
			$formError = 0;
			foreach ($tmpData[$table] as $tmpId => $tmpArr)	{
				$id = intval(substr($tmpId, 3));
				unset($tmpArr['hidden']);
				unset($tmpArr['deleted']);
				unset($tmpArr['starttime']);
				unset($tmpArr['endtime']);
				unset($tmpArr['fe_group']);
				unset($tmpArr['category']);
				unset($tmpArr['crdate']);
				unset($tmpArr['cruser_id']);
				unset($tmpArr['tstamp']);
				unset($tmpArr['sorting']);
				unset($tmpArr['__type']);
				$tmp2Arr = $tce->returnTables[$table][$id];
				if (!is_array($tmp2Arr))	{
					$tmp2Arr = array();
				}
				unset($tmp2Arr['hidden']);
				unset($tmp2Arr['deleted']);
				unset($tmp2Arr['starttime']);
				unset($tmp2Arr['endtime']);
				unset($tmp2Arr['fe_group']);
				unset($tmp2Arr['category']);
				unset($tmp2Arr['crdate']);
				unset($tmp2Arr['cruser_id']);
				unset($tmp2Arr['tstamp']);
				unset($tmp2Arr['sorting']);
				unset($tmp2Arr['__type']);
				$keys1 = array_keys($tmpArr);
				$keys2 = array_keys($tmp2Arr);
				$kdiff = array_diff($keys1, $keys2);
				$formError = $formError||count($kdiff);
				if (count($kdiff)&&t3lib_div::inList($this->conf['forms.']['respectRequired'], $table))	{
					unset($tce->returnTables[$table][$id]);
					unset($this->data[$table][$id]);
				}
			}
			if (!count($tce->returnTables[$table]))	{
				unset($tce->returnTables[$table]);
				unset($this->data[$table]);
				if ($pT = $GLOBALS['TCA'][$table]['ctrl']['parentTable'])	{
					unset($tce->returnTables[$pT]);
					unset($this->data[$pT]);
				}
			}
			if (t3lib_div::inList($this->conf['forms.']['respectRequired'], $table))	{
				if (!$formError)	{
					$this->data[$table] = $tce->returnTables[$table];
					if (($pT = $GLOBALS['TCA'][$table]['ctrl']['parentTable']) && !is_array($this->data[$pT]))	{
						if ($this->data[$pT][1]['__type']=='listing')	{
							$conf = $this->conf[$this->tskey.'.']['forms.'];
						} else	{
							$conf = $this->conf[$this->tskey.'.']['itemList.']['forms.'];
						}
						$this->data[$pT][1] = $this->initTableRow($pT, 1, $conf);
					}
				}
			} else	{
				if ($tce->returnTables[$table])	{
					$this->data[$table] = $tce->returnTables[$table];
				}
			}
			$formError |= $this->checkTableValues($table, $dArr);
			$this->sessionData['basket'] = $this->data;
			$GLOBALS['TSFE']->fe_user->setKey('ses', $this->prefixId, $this->sessionData);
			$GLOBALS['TSFE']->fe_user->storeSessionData();
			$this->formError = $this->formError||$formError;
			$GLOBALS['BE_USER'] = $sBEU;
		}
	}
			
				
	function checkTableValues($table, $dArr, $setErrors = true)	{
		$formError = false;
		$localCObj = clone($this->cObj);
		if (is_array($this->conf['forms.']['valid.'])||is_array($this->conf['forms.']['valid.']))	{
			$dArr = $this->data[$table];
			if (is_array($dArr))	{
				foreach ($dArr as $uid => $row)	{
					$localCObj->start($row);
					if ($this->conf['forms.']['validRow.'][$table] || $this->conf['forms.']['validRow.'][$table.'.'])	{
						if (!$localCObj->stdWrap($this->conf['forms.']['validRow.'][$table], $this->conf['forms.']['validRow.'][$table.'.']))	{
							unset($this->data[$table][$uid]);
							continue;
						}
					}
					foreach ($row as $field => $value)	{
						if ($this->conf['forms.']['eval.'][$table.'.'][$field] || $this->conf['forms.']['eval.'][$table.'.'][$field.'.'])	{
							$this->data[$table][$uid][$field] = $localCObj->data[$field] = $localCObj->stdWrap($this->conf['forms.']['eval.'][$table.'.'][$field], $this->conf['forms.']['eval.'][$table.'.'][$field.'.']);
						}
						if ($this->conf['forms.']['valid.'][$table.'.'][$field] || $this->conf['forms.']['valid.'][$table.'.'][$field.'.'])	{
							if (!$localCObj->stdWrap($this->conf['forms.']['valid.'][$table.'.'][$field], $this->conf['forms.']['valid.'][$table.'.'][$field.'.']))	{
								if ($setErrors)	{
									$formError |= true;
									if ($m = $localCObj->stdWrap($this->conf['forms.']['errorMsg.'][$table.'.'][$field], $this->conf['forms.']['errorMsg.'][$table.'.'][$field.'.']))	{
										$this->errorFields[$table][$field] = $m;
									} else	{
										$this->errorFields[$table][$field] = true;
									}
								}
							}
						}
					}
				}
				$cur = $this->data[$table];
				$this->data[$table] = array();
				foreach ($cur as $row)	{
					$this->data[$table][$row['uid']] = $row;
				}
			}
		}
		return $formError;
	}


	function checkIf($conf)	{
		$ret = true;
		if (is_array($conf))	{
			$ret = $this->localcObj->checkIf($conf);
			if ((!$ret)&&is_array($conf['orsubif.']))	{
				$ret |= $this->checkIf($conf['orsubif.']);
			}
			if ($ret&&is_array($conf['andsubif.']))	{
				$ret &= $this->checkIf($conf['andsubif.']);
			}
		}
		return $ret;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/'.$_EXTKEY.'/pi1/class.tx_'.$_EXTKEY_.'_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/'.$_EXTKEY.'/pi1/class.tx_'.$_EXTKEY_.'_pi1.php']);
}


?>
