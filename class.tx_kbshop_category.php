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
 * Category methods
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */



class tx_kbshop_category	{
	var $categoryRootline = false;
	var $config = false;

	function init(&$config)	{
		$this->config = &$config;
	}


	function collectPropertiesPage($pagePid, $haveParent = false, $callback = false)	{
		/*
		$cats = array();
		foreach ($this->categoryRootline as $catRow)	{
			$cats[] = $catRow['uid'];
		}
		$cats = implode(',', $cats);
		$properties = $this->collectPropertiesCatPage($cats, $pagePid, $haveParent, $callback);
		*/

		$props = array();
		foreach ($this->categoryRootline as $catRow)	{
			if ($catRow['properties'])	{
				$props[] = $catRow['properties'];
			}
		}
		$props = implode(',', $props);
		$properties = $this->collectPropertiesCatPage($props, $pagePid, $haveParent, $callback);

		foreach ($properties as $idx => $prop)	{
			$subprops = $this->collectPropertiesPage(0, $prop['uid'], $callback);
			if (is_array($subprops)&&count($subprops))	{
				$properties[$idx]['_SUBPROPS'] = $subprops;
			}
		}
		return $properties;
	}



	function collectPropertiesCatPage($propUids, $pagePid, $haveParent = false, $callback = false)	{
//	function collectPropertiesCatPage($catUid, $pagePid, $haveParent = false, $callback = false)	{
//		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($this->config->propertiesTable.'.*', $this->config->categoriesTable, $this->config->categoriesPropertiesMMTable, $this->config->propertiesTable, 'AND '.$this->config->categoriesTable.'.uid IN ('.$catUid.') '.($pagePid?' AND '.$this->config->propertiesTable.'.pid='.$pagePid:'').' '.($haveParent?' AND '.$this->config->propertiesTable.'.parent='.$haveParent:' AND '.$this->config->propertiesTable.'.parent=0').' AND '.$this->config->propertiesTable.'.sys_language_uid=0 AND '.tx_kbshop_abstract::enableFields($this->config->categoriesTable).' AND '.tx_kbshop_abstract::enableFields($this->config->propertiesTable), '', $this->config->categoriesPropertiesMMTable.'.sorting');
//		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($this->config->propertiesTable.'.*', $this->config->categoriesTable, $this->config->categoriesPropertiesMMTable, $this->config->propertiesTable, 'AND '.$this->config->categoriesTable.'.uid IN ('.$catUid.') '.($pagePid?' AND '.$this->config->propertiesTable.'.pid='.$pagePid:'').' '.($haveParent?' AND '.$this->config->propertiesTable.'.parent='.$haveParent:' AND '.$this->config->propertiesTable.'.parent=0').' AND '.$this->config->propertiesTable.'.sys_language_uid=0 AND '.tx_kbshop_abstract::enableFields($this->config->categoriesTable).' AND '.tx_kbshop_abstract::enableFields($this->config->propertiesTable), '', $this->config->categoriesPropertiesMMTable.'.sorting');
		if (!$propUids)	{
			return array();
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->config->propertiesTable, $this->config->propertiesTable.'.uid IN ('.$propUids.') '.($pagePid?' AND '.$this->config->propertiesTable.'.pid='.$pagePid:'').' '.($haveParent?' AND '.$this->config->propertiesTable.'.parent='.$haveParent:' AND '.$this->config->propertiesTable.'.parent=0').' AND '.$this->config->propertiesTable.'.sys_language_uid=0 AND '.tx_kbshop_abstract::enableFields($this->config->propertiesTable), '', 'FIND_IN_SET(uid, \''.$propUids.'\')');
		$rows = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$localProps = tx_kbshop_abstract::getRecordsByField($this->config->propertiesTable, 'l18n_parent', $row['uid']);
			if (is_array($localProps))	{
				foreach ($localProps as $localProp)	{
					$row['_LANG_ROWS'][$localProp['sys_language_uid']] = $localProp;
				}
			}
			$alias = $row['alias']?$row['alias']:$row['uid'];
			if (is_object($callback['object'])&&method_exists($callback['object'], $callback['method']))	{
				$callback['object']->$callback['method']($row);
			}
			$rows[$this->config->fieldPrefix.$alias] = $row;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $rows;
	}


	function getCategoryRootline($uid)	{
		$rl = array();
		$parent = intval($uid);
		$visited = array();
		while ($parent&&!$visited[$parent])	{
			$visited[$parent]  = true;
			$row = t3lib_BEfunc::getRecord($this->config->categoriesTable, $parent);
			if (is_array($row))	{
				array_unshift($rl, $row);
				$parent = intval($row['parent']);
			} else	{
				$parent = 0;
			}
		}
		$this->categoryRootline = $rl;
	}


	function getPropertyTree($baseId, $callback = false)	{
		$allProps = array();
		$allProps[-1] = array(
			'title' => 'Basic',
			'props' => $this->collectPropertiesPage($baseId, false, $callback),
		);
		$subpages = tx_kbshop_abstract::getRecordsOnPid('pages', $baseId, 'sorting');
		if (is_array($subpages)&&count($subpages))	{
			foreach ($subpages as $subpage)	{
				$props = $this->collectPropertiesPage($subpage['uid'], false, $callback);
				if (is_array($props)&&count($props))	{
					$allProps[$subpage['uid']] = array(
					'title' => $subpage['title'],
					'props' => $props,
					);
				}
			}
		}
		return $allProps;
	}

	function getCategoriesRec($cuid)	{
		$ret = array();
		$ret[] = $cuid;
		$cats = tx_kbshop_abstract::getRecordsByField($this->config->categoriesTable, 'parent', $cuid);
		if (is_array($cats))	{
			foreach ($cats as $cat)	{
				$ret = array_merge($ret, $this->getCategoriesRec($cat['uid']));
			}
		}
		return $ret;
	}

}

?>
