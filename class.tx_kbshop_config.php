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
 * Configuration class
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

require_once($_EXTPATH.'class.tx_kbshop_abstract.php');
class tx_kbshop_config	{
	var $lDEF = 'lDEF';
	var $vDEF = 'vDEF';
	var $baseClass = false;
	var $hookMethodPrefix = 'kbshop_hook_';

		// Never set this to "" (empty string) this will result in the application being unable to differ between fields required by TYPO3 (uid, pid, etc.) and it's own field and it would simply delete all orignal fields. (This is avoided)
	var $origFieldPrefix = 'kbs_';
	var $fieldPrefix = '';			// Get's set in the init routine
	var $piComparePrefix = 'field_compare_value_';
	var $reverseExtraProps = false;


	var $mmRelationTablePrefix = 'tx_kbshop_product_';
	var $mmRelationTablePostfix = '_KBSMM';
	var $sectionFieldPrefix = 'kbs_';

	var $sectionTablePrefix = 'tx_kbshop_stbl_';
	var $sectionTablePostTable = '__';
	var $sectionTableCenter = '___';
	var $sectionTablePostfix = '_SECTION';

	var $entriesTable = 'tx_kbshop_product';
	var $entriesTablePrefix = 'tx_kbshop_tbl_';
	var $propertiesTable = 'tx_kbshop_property';
	var $categoriesTable = 'tx_kbshop_category';
	var $categoriesPropertiesMMTable = 'tx_kbshop_category_properties_mm';
	var $entriesCategoriesMMTable = 'tx_kbshop_product_category_mm';

	var $typo3tempPath = 'typo3temp/';


	var $pi_md5StringOptionValues = 8;
	var $pi_md5TableAndFieldNames = 3;
	/*
	 * INTERNAL
	 */

	var $propPage = 0;
	var $catPage = 0;
	var $prodPage = 0;

	var $rootUids = array();

	function tx_kbshop_config()	{
		global $BE_USER;
		if (strlen($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['fieldPrefix']))	{
			$this->origFieldPrefix = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['fieldPrefix'];
			$this->sectionFieldPrefix = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['fieldPrefix'];
		}
		if (!strlen($this->origFieldPrefix))	{
			echo 'tx_kbshop_config::origFieldPrefix must be set !<br>'.chr(10);
			exit(1);
		}
		if (TYPO3_MODE=='FE')	{
			$this->currentCharset = $this->TYPO3_CONF_VARS['BE']['forceCharset']?$this->TYPO3_CONF_VARS['BE']['forceCharset']:$GLOBALS['TSFE']->labelsCharset;
		} else	{
			if (!is_object($GLOBALS['LANG']))	{
				require_once(t3lib_extMgm::extPath('lang').'lang.php');
				$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
				$GLOBALS['LANG']->init($BE_USER->uc['lang']);
			}
			$this->currentCharset = $GLOBALS['LANG']->charSet;
		}
		$this->fieldPrefix = $this->origFieldPrefix;
		if (strlen($configExt = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['configExtension']))	{
			$basePath = t3lib_extMgm::extPath($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['configExtension']);
			if (!$basePath)	{
				$basePath = t3lib_extMgm::extPath('kb_shop');
				$configExt = 'kb_shop';
			}
		} else	{
			$configExt = 'kb_shop';
			$basePath = t3lib_extMgm::extPath('kb_shop');
		}
		$this->configExtBasePath = $basePath;
		$this->configExt = $configExt;
	}

	function init(&$baseClass)	{
		$this->baseClass = &$baseClass;
	}

	function getRootLineListTarget($pageId, $list, $allowTargetList = false)	{
		if (!count($this->rootUids))	{
			$rootline = tx_kbshop_abstract::getRootLine($pageId);
			foreach ($rootline as $row)	{
				$this->rootUids[] = intval($row['uid']);
			}
		}
		$parts = t3lib_div::trimExplode(';', $list, 1);
		foreach ($parts as $part)	{
			list($pages, $target) = preg_split('/\s*[|\\/]\s*/', $part);
			$pageUids = t3lib_div::intExplode(',', $pages);
			if (count(array_intersect($this->rootUids, $pageUids)))	{
				$targets = t3lib_div::intExplode(',', $target);
				if ($allowTargetList)	{
					if (is_array($targets)&&count($targets))	{
						$target = $targets;
					}
				} else	{
					if (!($target = intval(array_shift($targets))))	{
						continue;
					}
				}
				return $target;
			}
		}
		return false;
	}

	function getPropertiesPage()	{
		$folders = t3lib_div::intExplode(',', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['propertyFolders']);
		return intval($folders[0]);
	}

	function getCategoryPages($pageId)	{
		return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['categoryFolders'];
	}

	function getFlexformLKey($langDisable = 0, $langChildren = 0)	{
		if (TYPO3_MODE=='FE')	{
			$lKey = ($GLOBALS['TSFE']->sys_language_isocode && !$langDisabled && !$langChildren) ? 'l'.$GLOBALS['TSFE']->sys_language_isocode : 'lDEF';
		} else	{
			if (!$this->allAvailableLanguages)	{
				$this->allAvailableLanguages = $this->getAvailableLanguages(0, true, true, true);
			}
			$this->currentLanguageKey = $this->allAvailableLanguages[$this->pObj->MOD_SETTINGS['language']]['ISOcode'];
			$lKey = $langDisable ? 'lDEF' : ($langChildren ? 'lDEF' : 'l'.$this->currentLanguageKey);
		}
		return $lKey;
	}

	function getFlexformVKey($langDisable = 0, $langChildren = 0)	{
		if (TYPO3_MODE=='FE')	{
			$vKey = ($GLOBALS['TSFE']->sys_language_isocode && !$langDisabled && $langChildren) ? 'v'.$GLOBALS['TSFE']->sys_language_isocode : 'vDEF';
		} else	{
			if (!$this->allAvailableLanguages)	{
				$this->allAvailableLanguages = $this->getAvailableLanguages(0, true, true, true);
			}
			$this->currentLanguageKey = $this->allAvailableLanguages[$this->pObj->MOD_SETTINGS['language']]['ISOcode'];
			$vKey = $langDisable ? 'vDEF' : ($langChildren ? 'v'.$this->currentLanguageKey : 'vDEF');
		}
		return $vKey;
	}
	

	/**
	 * Returns an array of available languages (to use for FlexForms)
	 *
	 * @param	integer		Page id: If zero, the query will select all sys_language records from root level. If set to another value, the query will select all sys_language records that has a pages_language_overlay record on that page (and is not hidden, unless you are admin user)
	 * @param	boolean		If set, only languages which are paired with a static_info_table / static_language record will be returned.
	 * @param	boolean		If set, an array entry for a default language is set.
	 * @param	boolean		If set, an array entry for "multiple languages" is added (uid -1)
	 * @return	array
	 */
	function getAvailableLanguages($id=0, $onlyIsoCoded=true, $setDefault=true, $setMulti=false)	{
		global $LANG, $TYPO3_DB, $BE_USER, $TCA, $BACK_PATH;

		t3lib_div::loadTCA ('sys_language');
		$flagAbsPath = t3lib_div::getFileAbsFileName($TCA['sys_language']['columns']['flag']['config']['fileFolder']);
		$flagIconPath = $BACK_PATH.'../'.substr($flagAbsPath, strlen(PATH_site));

		$output = array();
		$excludeHidden = $BE_USER->isAdmin() ? '1=1' : 'sys_language.hidden=0';

		if ($id)	{
			$res = $TYPO3_DB->exec_SELECTquery(
							'sys_language.*',
							'pages_language_overlay,sys_language',
							'pages_language_overlay.sys_language_uid=sys_language.uid AND pages_language_overlay.pid='.intval($id).' AND '.$excludeHidden,
							'pages_language_overlay.sys_language_uid',
							'sys_language.title'
			);
		} else {
			$res = $TYPO3_DB->exec_SELECTquery(
							'sys_language.*',
							'sys_language',
							$excludeHidden,
							'',
							'sys_language.title'
			);
		}

		if ($setDefault) {
			$output[0]=array(
				'uid' => 0,
				'title' => strlen ($this->pObj->modSharedTSconfig['properties']['defaultLanguageFlag']) ? $this->pObj->modSharedTSconfig['properties']['defaultLanguageLabel'].' ('.$LANG->getLL ('defaultLanguage').')' : $LANG->getLL ('defaultLanguage'),
				'ISOcode' => 'DEF',
				'flagIcon' => strlen($this->pObj->modSharedTSconfig['properties']['defaultLanguageFlag']) && @is_file($flagAbsPath.$this->pObj->modSharedTSconfig['properties']['defaultLanguageFlag']) ? $flagIconPath.$this->pObj->modSharedTSconfig['properties']['defaultLanguageFlag'] : null,
			);
		}

		if ($setMulti) {
			$output[-1]=array(
				'uid' => -1,
				'title' => $LANG->getLL ('multipleLanguages'),
				'ISOcode' => 'DEF',
				'flagIcon' => $flagIconPath.'multi-language.gif',
			);
		}

		while($row = $TYPO3_DB->sql_fetch_assoc($res))	{
			$output[$row['uid']]=$row;

			if ($row['static_lang_isocode'])	{
				$staticLangRow = t3lib_BEfunc::getRecord('static_languages',$row['static_lang_isocode'],'lg_iso_2');
				if ($staticLangRow['lg_iso_2']) {
					$output[$row['uid']]['ISOcode'] = $staticLangRow['lg_iso_2'];
				}
			}
			if (strlen ($row['flag'])) {
				$output[$row['uid']]['flagIcon'] = @is_file($flagAbsPath.$row['flag']) ? $flagIconPath.$row['flag'] : '';
			}

			if ($onlyIsoCoded && !$output[$row['uid']]['ISOcode']) unset($output[$row['uid']]);
		}

		return $output;
	}

	function updateLLLfile($LLBuffer)	{
		if (!is_array($LLBuffer)) return;
		foreach ($LLBuffer as $lang_id => $LLlabels)	{
			if ($lang_id)	{
				$langRec = tx_kbshop_abstract::getRecord('sys_language', $lang_id);
				if (is_array($langRec))	{
					$langRec = tx_kbshop_abstract::getRecord('static_languages', $langRec['static_lang_isocode']);
					if (is_array($langRec))	{
						$lk = $langRec['lg_typo3'];
						if ($langRec['lg_iso_2']=='EN')	{
							$lk = 'en';
						}
					} else	{
						echo 'kb_shop: Fatal Error: Language does not exist ! ('.__FILE__.':'.__CLASS__.'->'.__FUNCTION__.' @ '.__LINE__.')';
						return;
					}
				} else	{
					echo 'kb_shop: Fatal Error: Language does not exist ! ('.__FILE__.':'.__CLASS__.'->'.__FUNCTION__.' @ '.__LINE__.')';
					return;
				}
			} else	{
				$lk = $GLOBALS['TYPO3_CONF_VARS']['SYS']['defaultLanguage']?$GLOBALS['TYPO3_CONF_VARS']['SYS']['defaultLanguage']:'en';
			}
			if ($lk=='en')	{
				$LLkey = 'default';
			} elseif($lk)	{
				$LLkey = $lk;
			} else	{
				echo 'kb_shop: Fatal Error: Language "'.$lk.'" not supported by TYPO3 ! ('.__FILE__.':'.__CLASS__.'->'.__FUNCTION__.' @ '.__LINE__.')';
				return;
			}
			$LLLArray[$LLkey] = $LLlabels;
		}
		foreach ($LLBuffer[0] as $llkey => $llval)	{
			if (!$LLLArray['default'][$llkey])	{
				$LLLArray['default'][$llkey] = $llval;
			}
		}
		if (($cs = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'])!='utf-8')	{
			if (!$cs)	{
				$cs = 'iso-8859-1';
			}
			$LLLArray = tx_kbshop_abstract::csConvArray($LLLArray, $cs, 'utf-8');
		}
		$this->writeLLLfile($LLLArray);
	}

	function writeLLLfile($LLA)	{
		$file = $this->configExtBasePath.'locallang_dyn.xml';
		if (@file_exists($file)&&($data = t3lib_div::getURL($file)))	{
			$xml = t3lib_div::xml2array($data);
		} else	{
			$xml = array(
				'meta' => array(
					'type' => 'module',
					'description' => 'Dynamic local-lang labels for the kb-shop extension',
				),
				'data' => array(
				),
			);
		}
		$xml['data'] = t3lib_div::array_merge_recursive_overrule(is_array($xml['data'])?$xml['data']:array(), $LLA);
		$xmlcode = $this->createXML($xml);
		t3lib_div::writeFile($file, $xmlcode);
	}
	
	/**
	 * Creates llXML string from input array
	 *
	 * @param	array		locallang-XML array
	 * @return	string		XML content
	 */
	function createXML($outputArray)	{

			// Options:
		$options = array(
			#'useIndexTagForAssoc'=>'key',
			'parentTagMap' => array(
				'data' => 'languageKey',
				'orig_hash' => 'languageKey',
				'orig_text' => 'languageKey',
				'labelContext' => 'label',
				'languageKey' => 'label'
			)
		);

			// Creating XML file from $outputArray:
		$XML = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>'.chr(10);
		$XML.= t3lib_div::array2xml($outputArray,'',0,'T3locallang',0,$options);

		return $XML;
	}	

}



?>
