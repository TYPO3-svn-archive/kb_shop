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
 * Miscelaneous methods
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

class tx_kbshop_misc	{


	function getKey($row, $prefix = '')	{
		return preg_replace('/[^a-z0-9_]/', '', strtolower($row[$prefix.'alias']?$row[$prefix.'alias']:$row[$prefix.'uid']));
	}

	
	function getSectionOrder($table, $field, $leftJoin = false)	{
		$join = '';
		$where = '';
		if (is_array($config = $GLOBALS['TCA'][$table]['columns'][$field]['config']))	{
			if (($config['type']=='select')&&strlen($fTable = $config['foreign_table']))	{
				if ($config['MM'])	{
					if ($leftJoin)	{
						$join .= ' LEFT JOIN '.$config['MM'];
						$join .= ' ON '.$config['MM'].'.uid_local='.$table.'.uid';
					} else	{
						$join .= ', '.$config['MM'];
						$where .= $config['MM'].'.uid_local='.$table.'.uid AND '.$config['MM'].'.uid_foreign='.$fTable.'.uid';
					}
				} else	{
					if (!$leftJoin)	{
						$where .= $table.'.'.$field.'='.$fTable.'.uid';
					}
				}
				if ($leftJoin)	{
					$join .= ' LEFT JOIN '.$fTable;
					if ($config['MM'])	{
						$join .= ' ON '.$config['MM'].'.uid_foreign='.$fTable.'.uid';
					} else	{
						$join .= ' ON '.$table.'.'.$field.'='.$fTable.'.uid';
					}
				} else	{
					$join .= ', '.$fTable;
				}
				$table = $fTable;
			}
		}
		return array($join, $where, $table);
	}

	
	function createDirs($uploadFolders)	{
		foreach ($uploadFolders as $dir)	{
			if (!file_exists(PATH_site.$dir))	{
				t3lib_div::mkdir_deep(PATH_site, $dir);
			}
		}
	}

	/**
	 * Merges two arrays recursively and "binary safe" (integer keys are overridden as well), overruling similar values in the first array ($arr0) with the values of the second array ($arr1)
	 * In case of identical keys, ie. keeping the values of the second.
	 * Usage: 0
	 *
	 * @param	array		First array
	 * @param	array		Second array, overruling the first array
	 * @param	boolean		If set, keys that are NOT found in $arr0 (first array) will not be set. Thus only existing value can/will be overruled from second array.
	 * @param	boolean		If set, values from $arr1 will overrule if they are empty. Default: true
	 * @return	array		Resulting array where $arr1 values has overruled $arr0 values
	 */
	function array_merge_recursive_overrule($arr0,$arr1,$notAddKeys=0,$includeEmtpyValues=true) {
		reset($arr1);
		while(list($key,$val) = each($arr1)) {
			if(is_array($arr0[$key])) {
				if (is_array($arr1[$key]))	{
					$arr0[$key] = tx_kbshop_misc::array_merge_recursive_overrule($arr0[$key],$arr1[$key],$notAddKeys);
				} else	{
					if ($notAddKeys) {
						if (isset($arr0[$key])) {
							if ($includeEmtpyValues OR $val) {
								$arr0[$key] = $val;
							}
						}
					} else {
						if ($includeEmtpyValues OR $val) {
							$arr0[$key] = $val;
						}
					}
				}
			} else {
				if ($notAddKeys) {
					if (isset($arr0[$key])) {
						if ($includeEmtpyValues OR $val) {
							$arr0[$key] = $val;
						}
					}
				} else {
					if ($includeEmtpyValues OR $val) {
						$arr0[$key] = $val;
					}
				}
			}
		}
		reset($arr0);
		return $arr0;
	}


}


?>
