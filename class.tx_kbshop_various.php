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
 * Various FE-rendering helpers
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */


class tx_kbshop_various	{
	var $defaultFields = array('uid', 'pid', 'deleted', 'crdate', 'tstamp');


	function sprintf($content, $conf)	{
		$this->cObj = &$conf['_pObj'];
		$vals = array();
		foreach ($conf as $key => $type)	{
			if (t3lib_div::testInt($key))	{
				$value = $this->cObj->cObjGetSingle($type, $conf[$key.'.']);
				$vals[$key] = $value;
			}
		}
		if (count($vals))	{
			ksort($vals);
			$def = array_shift($vals);
			return vsprintf($def, array_values($vals));
		}
		return '';
	}


	function dbTool($content, $conf)	{
		$this->cObj = &$conf['_pObj'];
		$this->conf = $conf;
		if ((!$this->conf['if.'])||$this->cObj->checkIf($this->conf['if.']))	{
			$type = $this->cObj->stdWrap($this->conf['type'], $this->conf['type.']);
			switch (strtoupper($type))	{
				case 'UPDATE':
					$table = $this->cObj->stdWrap($this->conf['table'], $this->conf['table.']);
					t3lib_div::loadTCA($table);
					if (is_array($GLOBALS['TCA'][$table]))	{
						$where = $this->cObj->stdWrap($this->conf['where'], $this->conf['where.']);
						$values = array();
						$checked = false;
						if (is_array($this->conf['values.']))	{
							foreach ($this->conf['values.'] as $k => $v)	{
								if (substr($k, -1)=='.')	{
									$k = substr($k, 0, -1);
								}
								if ((!$checked[$k])&&(is_array($GLOBALS['TCA'][$table]['columns'][$k])||in_array($k, $this->defaultFields)))	{
									$checked[$k] = true;
									$values[$k] = $this->cObj->stdWrap($this->conf['values.'][$k], $this->conf['values.'][$k.'.']);
								}
							}
						}
						if (count($values))	{
							$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $values);
						}
					break;
				}
			}
		}
	}




}



?>
