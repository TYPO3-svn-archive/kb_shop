<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-2006 Kraft Bernhard (kraftb@kraftb.at)
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
 * BE/FE Abstraction class for static calling via scope (::) operator
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

require_once(PATH_t3lib.'class.t3lib_befunc.php');
class tx_kbshop_abstract	{

	function getRecordsOnPid($table, $pid, $sorting = '', $where = '')	{
		return tx_kbshop_abstract::getRecordsByField($table, 'pid', $pid, $sorting, $where);
	}

	function deleteClause($table)	{
		if (TYPO3_MODE=='BE')	{
			return t3lib_BEfunc::deleteClause($table);
		} elseif (TYPO3_MODE=='FE')	{
			return $GLOBALS['TSFE']->sys_page->deleteClause($table);
		} else	{
			return '';
		}
	}


	function getRecordsByField($table, $field, $value, $sorting = '', $where = '')	{
		if (TYPO3_MODE=='BE')	{
			return t3lib_BEfunc::getRecordsByField($table, $field, $value, $where, '', $sorting);
		} elseif (TYPO3_MODE=='FE')	{
			return $GLOBALS['TSFE']->sys_page->getRecordsByField($table, $field, $value, $where, '', $sorting);
		} else	{
			return false;
		}
	}

	function getPage($pageId)	{
		if (TYPO3_MODE=='BE')	{
			if (method_exists('t3lib_BEfunc', 'getRecordWSOL'))	{
				return t3lib_BEfunc::getRecordWSOL('pages', $pageId);
			} else	{
				return t3lib_BEfunc::getRecord('pages', $pageId);
			}
		} elseif (TYPO3_MODE=='FE')	{
			return $GLOBALS['TSFE']->sys_page->getPage($pageId);
		} else	{
			return false;
		}
	}

	function getRecord($table, $uid)	{
		if ($uid<=0) return false;
		if (TYPO3_MODE=='BE')	{
			if (method_exists('t3lib_BEfunc', 'getRecordWSOL'))	{
				$rec = t3lib_BEfunc::getRecordWSOL($table, $uid);
				if (!is_array($rec))	{
					$rec = t3lib_BEfunc::getRecord($table, $uid);
				}
				return $rec;
			} else	{
				return t3lib_BEfunc::getRecord($table, $uid);
			}
		} elseif (TYPO3_MODE=='FE')	{
			return $GLOBALS['TSFE']->sys_page->getRawRecord($table, $uid);
		} else	{
			return false;
		}
	}

	function getPagesRecursive($pageId)	{
		$pages = tx_kbshop_abstract::getRecordsOnPid('pages', $pageId);
		$res = array($pageId);
		if (is_array($pages) && count($pages))	{
			foreach ($pages as $page)	{
				$res = array_merge($res, tx_kbshop_abstract::getPagesRecursive($page['uid']));
			}
		}
		return $res;
	}


	function cacheOK(&$parent, $cacheFile, $storagePid, $catPid){
		$fmtime = filemtime($cacheFile);
		$rmtime = -1;	// The cache file will surely not have been created on New years eve 1969

		$pidList = tx_kbshop_abstract::getPagesRecursive($storagePid);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tstamp', $parent->config->propertiesTable, 'pid IN ('.implode(',', $pidList).')', '', 'tstamp DESC', 1);
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			if ($row['tstamp']>$rmtime)	{
				$rmtime = $row['tstamp'];
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tstamp', $parent->config->categoriesTable, 'pid='.$catPid, '', 'tstamp DESC', 1);
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			if ($row['tstamp']>$rmtime)	{
				$rmtime = $row['tstamp'];
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		if ($rmtime<$fmtime)	{
			return true;
		}
		return false;
	}

	function getRootLine($uid)	{
		if (TYPO3_MODE=='BE')	{
			return t3lib_BEfunc::BEgetRootLine($uid);
		} elseif (TYPO3_MODE=='FE')	{
			return $GLOBALS['TSFE']->sys_page->getRootLine($uid);
		} else	{
			return false;
		}
	}
	
	function getFlexformChilds($flexform, $section, $types, $fields, $sheet = 'sDEF', $LKey = 'lDEF', $VKey = 'vDEF', $isSubArr = false)	{
		$childs = array();
		if ($isSubArr)	{
			$flexform = array(
				'data' => array(
					$sheet => array(
						$LKey => $flexform,
					),
				),
			);
		}
		if (is_array($flexform['data'][$sheet][$LKey][$section]['el']))	{
			foreach ($flexform['data'][$sheet][$LKey][$section]['el'] as $element)	{
				if (is_array($element))	{
					reset($element);
					$k = key($element);
					if ((is_string($types)&&($k==$types))||(is_array($types)&&in_array($k, $types)))	{
						if (is_string($fields))	{
							if ($fields=='*')	{
								$tmp = array();
								foreach ($element[$k]['el'] as $field => $fieldArr)	{
									if (is_array($fieldArr['el']))	{
										$tmp[$field] = $fieldArr;
									} else	{
										$tmp[$field] = $fieldArr[$VKey];
									}
								}
								$childs[] = $tmp;
							} else	{
								if (is_array($element[$k]['el'][$fields]['el']))	{
									$childs[] = $element[$k]['el'][$fields];
								} else	{
									$childs[] = $element[$k]['el'][$fields][$VKey];
								}
							}
						} elseif (is_array($fields))	{
							$r = array();
							foreach ($fields as $field)	{
								$r[$field] = $element[$k]['el'][$field][$VKey];
							}
							$childs[] = $r;
						}
					}
				}
			}
		}
		return $childs;
	}


	function enableFields($table, $show_hidden=0, $showOwn = 0)	{
		$enable = '';
		if (TYPO3_MODE=='FE')	{
			if (!is_array($GLOBALS['TCA'][$table]))	{
				echo "enableFields called with invalid argument '$table' !<br>\n";
				echo t3lib_div::debug_trail();
				exit();
			}
			$enable = $GLOBALS['TSFE']->sys_page->enableFields($table, $show_hidden);
			if ($enable)	{
				$enable = '1=1 '.$enable;
			}
		} else	{
			$ctrl = $GLOBALS['TCA'][$table]['ctrl'];
			$enable = t3lib_BEfunc::deleteClause($table).t3lib_BEfunc::BEenableFields($table);
			if ($enable)	{
				$enable = '1=1 '.$enable;
			}
			if ($showOwn)	{
				if ($field = $GLOBALS['TCA'][$table]['ctrl']['cruser_id'])	{
					$field = $table.'.'.$field;
					$enable .= (strlen($enable)?' AND':'').' '.$field.'='.$GLOBALS['BE_USER']->user['uid'];
				}
			}
		}
		if (!strlen($enable))	{
			$enable = '1=1';
		}
		return '('.$enable.')';
	}

	function getSortingField($table)	{
		$sorting = $TCA[$table]['ctrl']['sortby'];
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
		return $sorting;
	}

	/**
	 * Quotes a string for usage as JS parameter. Depends wheter the value is used in script tags (it doesn't need/must not get htmlspecialchared in this case)
	 *
	 * @param	string		The string to encode.
	 * @param	boolean		If the values get's used in <script> tags.
	 * @return	string	The encoded value already quoted
	 */
	function quoteJSvalue($value, $inScriptTags = false)	{
		if (method_exists('t3lib_div', 'quoteJSvalue'))	{
			return t3lib_div::quoteJSvalue($value, $inScriptTags);
		}
		$value = addcslashes($value, '\''.chr(10).chr(13));
		if (!$inScriptTags)	{
			$value = htmlspecialchars($value);
		}
		return '\''.$value.'\'';
	}

	function sL($label)	{
		global $BE_USER;
		if (TYPO3_MODE=='FE')	{
			return $GLOBALS['TSFE']->sL($label);
		} else	{
			if (!is_object($GLOBALS['LANG']))	{
				require_once(t3lib_extMgm::extPath('lang').'lang.php');
				$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
				$GLOBALS['LANG']->init($BE_USER->uc['lang']);
			}
			return $GLOBALS['LANG']->sL($label);
		}
	}

	function csConv($str, $from, $to)	{
		global $BE_USER;
		if (TYPO3_MODE=='FE')	{
			return $GLOBALS['TSFE']->csConv($str, $from, $to);
		} else	{
			if (!is_object($GLOBALS['LANG']))	{
				require_once(t3lib_extMgm::extPath('lang').'lang.php');
				$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
				$GLOBALS['LANG']->init($BE_USER->uc['lang']);
			}
			return $GLOBALS['LANG']->csConvObj->conv($str, $from, $to);
		}
	}
	
	function csConvArray($arr, $from, $to)	{
		global $BE_USER;
		if (TYPO3_MODE=='FE')	{
			$GLOBALS['TSFE']->csConvObj->convArray($arr, $from, $to);
			return $arr;
		} else	{
			if (!is_object($GLOBALS['LANG']))	{
				require_once(t3lib_extMgm::extPath('lang').'lang.php');
				$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
				$GLOBALS['LANG']->init($BE_USER->uc['lang']);
			}
			$GLOBALS['LANG']->csConvObj->convArray($arr, $from, $to);
			return $arr;
		}
	}

	function getSortFields($table)	{
		global $TCA;
		return ($TCA[$table]['ctrl']['sortby']) ? $TCA[$table]['ctrl']['sortby'] : ($TCA[$table]['ctrl']['default_sortby'] ? preg_replace('/\s*ORDER\s+BY\s+/i', '', $TCA[$table]['ctrl']['default_sortby']) : 'uid');
	}

	function getRecordTitle($table, $row)	{
		global $TCA;
		if (is_array($TCA[$table]))	{
			$lf = $TCA[$table]['ctrl']['label'];
			$alf = $TCA[$table]['ctrl']['label_alt'];
			$alff = $TCA[$table]['ctrl']['label_alt_force'];

			$recTitle = $row['uid'];
			if ($alf && ($alff || !strcmp($row[$lf], '') ) )	{
				$altFields = t3lib_div::trimExplode(',', $alf, 1);
				$tA = array();
				if ( $row[$fCol] )	{
					$tA[] = $row[$fCol];
				}
				foreach ($altFields as $fN)	{
					$t = t3lib_BEfunc::getProcessedValueExtra($table, $fN, $row[$fN], 1000, $row['uid']);
					if ( $t )	{
						$tA[] = $t;
					}
				}
				if ($laff)	{
					$t = implode(', ', $tA);
				}
				if ( $t )	{
					$recTitle = $t;
				}
			} else {
				$recTitle = t3lib_BEfunc::getProcessedValueExtra($table, $lf, $row[$lf], 1000, $row['uid']);
			}
			return $recTitle;
		}
		return $row['uid'];
	}

}


?>
