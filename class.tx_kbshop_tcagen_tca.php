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
 * TCA Generator for KB-Shop
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

class tx_kbshop_tcagen_tca extends tx_kbshop_tcagen	{
	var $entriesTable = '';
	var $virtual = 0;

		/*
		 * Overwrite base renderTCA cause when we have to render a TCA we get fed with an array containig all types first.
		*/
	function renderTCA($typeArr, $tkey = '', $baseCat = array())	{
		$this->entriesTable = $tkey;
		$allProps = array();
		$allfields = array('hidden', 'starttime', 'endtime', 'fe_group', 'category');
		$types = array(
			'0' => array(
				'showitem' => 'hidden;;1;;1-1-1, category;;;;2-2-2',
			)
		);
		foreach ($typeArr as $catIdx => $catProps)	{
			foreach ($catProps as $tabIdx => $tabArr)	{
				$this->getAllFieldsRec($allfields, $typeArr[$catIdx][$tabIdx]['props']);
				if (!isset($types[$catIdx]))	{
					$types[$catIdx]['showitem'] = 'hidden;;1;;1-1-1, category;;;;2-2-2';
					if ($GLOBALS['TCA'][$this->config->entriesTablePrefix.$tkey]['ctrl']['languageField'])	{
						$types[$catIdx]['showitem'] .= ', sys_language_uid, l18n_parent, l18n_diffsource';
					}
				}
				if (count($typeArr[$catIdx][$tabIdx]['props']))	{
					$types[$catIdx]['showitem'] .= ', '.$this->getTabFieldsRec($typeArr[$catIdx][$tabIdx]['props'], $tabArr['title'], 0, $baseCat['noTabs']?true:false);
				}
			}
			$allProps = t3lib_div::array_merge_recursive_overrule($allProps, $typeArr[$catIdx]);
		}
		$this->allfields = $allfields;
		$this->types = $types;
		return parent::renderTCA($allProps);
	}
	
	function getAllFieldsRec(&$allfields, &$fieldArr)	{
		if (is_array($fieldArr)&&count($fieldArr))	{
			$allfields = array_unique(array_merge($allfields, array_keys($fieldArr)));
			foreach ($fieldArr as $key => $field)	{
				if (($field['type']!=200) && is_array($field['_SUBPROPS']) && count($field['_SUBPROPS']))	{
					$this->getAllFieldsRec($allfields, $fieldArr[$key]['_SUBPROPS']);
				} elseif (($field['type']==200) && is_array($field['_SUBPROPS']) && count($field['_SUBPROPS']))	{
					$suballfields = array('hidden', 'starttime', 'endtime', 'fe_group', 'parent', 'sorting');
					$this->getAllFieldsRec($suballfields, $fieldArr[$key]['_SUBPROPS']);
					$fieldArr[$key]['__showRecordFieldList'] = implode(',', $suballfields);
				}
			}
		}
	}
	
	function getTabFieldsRec(&$fieldArr, $title = false, $newSection = 0, $noTabs = false)	{
		$str = '';
		if ($title&&!$noTabs)	{
			$str .= '--div--;'.preg_replace('/[;,]/', '_', tx_kbshop_abstract::csConv($title, $this->config->currentCharset, 'iso-8859-1'));
		}
		foreach ($fieldArr as $propIdx => $prop)	{
			$specialConf = false;
			if ($prop['type']==3)	{		// RTE
				$specialConf = $this->getSpecialConf($prop);
			}
			if ($prop['type']==100)	{
				$str .= ', '.strtolower($propIdx).'_lng, '.strtolower($propIdx).'_lat';
			} else	{
				$str .= ', '.strtolower($propIdx);
			}
			if ($specialConf)	{
				$str .= ';;;'.$specialConf;
				if ($newSection)	{
					$str .= ';'.$newSection.'-'.$newSection.'-'.$newSection;
					$newSection = 0;
				}
			} else	{
				if ($newSection)	{
					$str .= ';;;;'.$newSection.'-'.$newSection.'-'.$newSection;
					$newSection = 0;
				}
			}
			if (($prop['type']!=200)&&is_array($prop['_SUBPROPS'])&&count($prop['_SUBPROPS']))	{			// Don't get tab properties for a containers subelements
				$str .= $this->getTabFieldsRec($fieldArr[$propIdx]['_SUBPROPS'], false, 0, $noTabs);
			} elseif (($prop['type']==200)&&is_array($prop['_SUBPROPS'])&&count($prop['_SUBPROPS']))	{
				$newstr = 'hidden;;1;;1-1-1';
				$newstr .= $this->getTabFieldsRec($fieldArr[$propIdx]['_SUBPROPS'], false, 2, $noTabs);
				$fieldArr[$propIdx]['__showitems'] = $newstr;
			}
		}
		return $str;
	}


	function __initTCA(&$tca)	{
		if (!count($tca))	{
			$tca = array(
				'ctrl' => $GLOBALS['TCA'][$this->config->entriesTablePrefix.$this->entriesTable]['ctrl'],
				'interface' => array(
					'showRecordFieldList' => strtolower(implode(',', $this->allfields)),
					'maxDBListItems' => t3lib_div::intInRange($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['listItems'], 20, 100000, 20),
					'maxSingleDBListItems' => t3lib_div::intInRange($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['singleListItems'], 20, 100000, 100),
				),
				// $GLOBALS['TCA'][$this->config->entriesTablePrefix.$this->entriesTable]['feInterface'],
				'feInterface' => array(
					'fe_admin_fieldList' => 'pid,deleted,'.strtolower(implode(',', $this->allfields)),
				),
				'columns' => array(
					'hidden' => Array (		
						'exclude' => 1,	
						'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
						'config' => Array (
							'type' => 'check',
							'default' => '0'
						)
					),
					'starttime' => Array (		
						'exclude' => 1,	
						'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
						'config' => Array (
							'type' => 'input',
							'size' => '8',
							'max' => '20',
							'eval' => 'date',
							'default' => '0',
							'checkbox' => '0'
						)
					),
					'endtime' => Array (		
						'exclude' => 1,	
						'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
						'config' => Array (
							'type' => 'input',
							'size' => '8',
							'max' => '20',
							'eval' => 'date',
							'checkbox' => '0',
							'default' => '0',
							'range' => Array (
								'upper' => mktime(0,0,0,12,31,2020),
								'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
							)
						)
					),
					'fe_group' => Array (		
						'exclude' => 1,	
						'label' => 'LLL:EXT:lang/locallang_general.php:LGL.fe_group',
						'config' => Array (
							'type' => 'select',	
							'items' => Array (
								Array('', 0),
								Array('LLL:EXT:lang/locallang_general.php:LGL.hide_at_login', -1),
								Array('LLL:EXT:lang/locallang_general.php:LGL.any_login', -2),
								Array('LLL:EXT:lang/locallang_general.php:LGL.usergroups', '--div--')
							),
							'foreign_table' => 'fe_groups'
						)
					),
					/*
					'title' => Array (		
						'exclude' => 1,		
						'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_product.title',		
						'config' => Array (
							'type' => 'input',	
							'size' => '30',
						)
					),
					*/
					'category' => Array (		
						'exclude' => 1,		
						'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_product.category',		
						'config' => Array (
							'type' => 'select',
							'form_type' => 'user',
							'userFunc' => 'tx_kbshop_treeview->displayCategoryTree',
							'treeView' => 1,
							'foreign_table' => 'tx_kbshop_category',
							'size' => 1,
							'autoSizeMax' => 25,
							'minitems' => 1,
							'maxitems' => 1,
						),
					),
				),
				'types' => $this->types,
				'palettes' => array(
					'1' => array(
						'showitem' => 'starttime, endtime, fe_group',
					)
				),
			);
			if ($GLOBALS['TCA'][$this->config->entriesTablePrefix.$this->entriesTable]['ctrl']['languageField'])	{
				$tca['columns']['sys_language_uid'] = Array (
					'exclude' => 1,
					'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
					'config' => Array (
						'type' => 'select',
						'foreign_table' => 'sys_language',
						'foreign_table_where' => 'ORDER BY sys_language.title',
						'items' => Array(
							Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
							Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
						),
					),
				);
				$tca['columns']['l18n_parent'] = Array (
					'displayCond' => 'FIELD:sys_language_uid:>:0',
					'exclude' => 1,
					'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
					'config' => Array (
						'type' => 'select',
						'items' => Array (
							Array('', 0),
						),
						'foreign_table' => $this->config->entriesTablePrefix.$this->entriesTable,
						'foreign_table_where' => 'AND '.$this->config->entriesTablePrefix.$this->entriesTable.'.pid=###CURRENT_PID### AND '.$this->config->entriesTablePrefix.$this->entriesTable.'.sys_language_uid IN (-1,0)',
					),
				);
				$tca['columns']['l18n_diffsource'] = Array (
					'config'=>array(
						'type'=>'passthrough'
					),
				);
			}
			$tca['ctrl']['dividers2tabs'] = true;
			$tca['ctrl']['type'] = 'category';
			if ($v = intval($tca['ctrl']['virtual']))	{
				$this->virtual = $v;
			}
		}
	}
	
	function __addTCA_Cat(&$tca, $key, $idx, $new)	{
		$tca['columns'] = array_merge($tca['columns'], $new);
	}

	function __store_RequestUpdate(&$tca, $rQU)	{
		$tca['ctrl']['requestUpdate'] .= ($tca['ctrl']['requestUpdate']?',':'').implode(',', $rQU);
	}

	function __renderTCA_Element($config, $prop, $xmldata, $displayCond)	{
		$this->config->baseClass->LLBuffer[0]['prop_'.$this->entriesTable.'__'.$prop['__key']] = $prop['title'];
		if (is_array($prop['_LANG_ROWS']))	{
			foreach ($prop['_LANG_ROWS'] as $lrow)	{
				$this->config->baseClass->LLBuffer[$lrow['sys_language_uid']]['prop_'.$this->entriesTable.'__'.$prop['__key']] = $lrow['title'];
			}
		}
		$ret = array(
				// TODO: Add multi language support - currently only ISO-8859-1
//			'label' => $prop['title'],
//			'label' => tx_kbshop_abstract::csConv($prop['title'], $this->config->currentCharset, 'iso-8859-1'),
			'label' => 'LLL:EXT:'.$this->config->configExt.'/locallang_dyn.xml:prop_'.$this->entriesTable.'__'.$prop['__key'],
			'exclude' => 1,
			'config' => $config,
		);
		if ($GLOBALS['TCA'][$this->config->entriesTablePrefix.$this->entriesTable]['ctrl']['languageField'] && $prop['sys_language_mode'])	{
			$ret['l10n_mode'] = $prop['sys_language_mode'];
		}
		if (strlen($displayCond))	{
			$ret['displayCond'] = $displayCond;
		}
		return $ret;
	}


	function renderTCA__container($xmlArr, $prop)	{
		$sub = array(
			'hidden' => Array (		
				'exclude' => 1,	
				'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
				'config' => Array (
					'type' => 'check',
					'default' => '0'
				)
			),
			'starttime' => Array (		
				'exclude' => 1,	
				'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
				'config' => Array (
					'type' => 'input',
					'size' => '8',
					'max' => '20',
					'eval' => 'date',
					'default' => '0',
					'checkbox' => '0'
				)
			),
			'endtime' => Array (		
				'exclude' => 1,	
				'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
				'config' => Array (
					'type' => 'input',
					'size' => '8',
					'max' => '20',
					'eval' => 'date',
					'checkbox' => '0',
					'default' => '0',
					'range' => Array (
						'upper' => mktime(0,0,0,12,31,2020),
						'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
					)
				)
			),
			'fe_group' => Array (		
				'exclude' => 1,	
				'label' => 'LLL:EXT:lang/locallang_general.php:LGL.fe_group',
				'config' => Array (
					'type' => 'select',	
					'items' => Array (
						Array('', 0),
						Array('LLL:EXT:lang/locallang_general.php:LGL.hide_at_login', -1),
						Array('LLL:EXT:lang/locallang_general.php:LGL.any_login', -2),
						Array('LLL:EXT:lang/locallang_general.php:LGL.usergroups', '--div--')
					),
					'foreign_table' => 'fe_groups'
				)
			),
			'parent' => Array (		
				'exclude' => 1,
				'label' => 'LLL:EXT:kb_shop/locallang_db.php:section_table.parent',		
				'config' => Array (
					'type' => 'select',
					'foreign_table' => $this->config->entriesTablePrefix.$this->entriesTable,
					'size' => 1,
					'minitems' => 0,
					'maxitems' => 1,
				),
			),
			'sorting' => Array (		
				'exclude' => 1,
				'label' => 'LLL:EXT:kb_shop/locallang_db.php:section_table.sorting',		
				'config' => Array (
					'type' => 'input',
					'eval' => 'int',
				),
			),
		);
		if (is_array($prop['_SUBPROPS'])&&count($prop['_SUBPROPS']))	{
			foreach ($prop['_SUBPROPS'] as $subprop)	{
				$el = $this->renderTCA_Element($subprop, $prop['__displayCond']);
				if ($el['__containsFields'])	{
					unset($el['__containsFields']);
					foreach ($el as $key => $value)	{
						$sub[$this->config->sectionFieldPrefix.$key] = $value;
					}
				} else	{
					list($key, $value) = $el;
					$sub[$this->config->sectionFieldPrefix.$key] = $value;
				}
			}
		}
		$xml = t3lib_div::xml2array($prop['flexform']);
		$altlf = '';
		if ($u = $xml['data']['sDEF'][$this->config->lDEF]['field_label'][$this->config->vDEF])	{
			$lfa = t3lib_div::intExplode(',', $u);
			$lfres = array();
			foreach ($lfa as $u)	{
				if (!$u)	continue;
				$p = tx_kbshop_abstract::getRecord($this->config->propertiesTable, $u);
				if (!is_array($p))	continue;
				$a = preg_replace('/[^a-z0-9_]/', '', strtolower($p['alias']));
				$lfres[] = $this->config->fieldPrefix.($a?$a:$p['uid']);
			}
			$lf = array_shift($lfres);
			$altlf = implode(',', $lfres);
		} else	{
			$lf = 'title';
		}
		$sb = false;
		$dsb = false;
		if ($s = intval($xml['data']['sDEF'][$this->config->lDEF]['field_sort'][$this->config->vDEF]))	{
			list($s) = t3lib_div::intExplode(', ', $s);
			$p = tx_kbshop_abstract::getRecord($this->config->propertiesTable, $s);
			$a = preg_replace('/[^a-z0-9_]/', '', strtolower($p['alias']));
			$dsb = $this->config->fieldPrefix.($a?$a:$p['uid']);
		} else	{
			if (!$this->virtual)	{
				$sb = 'sorting';
			}
		}
		$subtca = array(
			'ctrl' => array(
				'title' => tx_kbshop_abstract::csConv($prop['title'], $this->config->currentCharset, 'iso-8859-1'),
				'label' => $lf,
				'tstamp' => 'tstamp',
				'crdate' => 'crdate',
				'cruser_id' => 'cruser_id',
				'default_sortby' => $dsb?$dsb:false,
				'sortby' => $sb?$sb:false,
				'delete' => 'deleted',
				'hideTable' => true,
				'virtual' => $this->virtual,
				'parentTable' => $this->config->entriesTablePrefix.$this->entriesTable,
				'hideTableList' => true,
				'enablecolumns' => Array (		
					'disabled' => 'hidden',	
					'starttime' => 'starttime',	
					'endtime' => 'endtime',	
					'fe_group' => 'fe_group',
				),
				'iconfile' => t3lib_extMgm::extRelPath('kb_shop').'icon_tx_kbshop_section.gif',
			),
			'interface' => array(
				'showRecordFieldList' => $prop['__showRecordFieldList'],
			),
			'feInterface' => array(
				'fe_admin_fieldList' => $prop['__showRecordFieldList'],
			),
			'label' => $prop['title'],
			'columns' => $sub,
			'types' => array(
				'0' => array(
					'showitem' => $prop['__showitems'],
				),
			),
			'palettes' => array(
				'1' => array(
					'showitem' => 'starttime, endtime, fe_group',
				),
			),
		);
		if (strlen($altlf))	{
			$subtca['ctrl']['label_alt_force'] = 1;
			$subtca['ctrl']['label_alt'] = $altlf;
		}
		$ftable = $this->config->sectionTablePrefix.$this->entriesTable.$this->config->sectionTableCenter.$prop['__key'].$this->config->sectionTablePostfix;
		$config = array(
			'type' => 'inline',
			'foreign_table' => $ftable,
			'foreign_field' => 'parent',
			'appearance' => array(
				'collapseAll' => true,
				'expandSingle' => true,
				'newRecordLinkAddTitle' => true,
			),
		);
		$GLOBALS['TCA'][$ftable] = $subtca;
		return $config;
	}




}


?>
