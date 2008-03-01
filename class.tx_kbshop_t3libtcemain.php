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
 * BE/FE Abstraction class for static calling via scope (::) operator
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

class tx_kbshop_t3libtcemain	{

	function tx_kbshop_t3libtcemain()	{
		$this->saveSQLdebug = $GLOBALS['TYPO3_DB']->debugOutput;
		$GLOBALS['TYPO3_DB']->debugOutput = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['SQLdebug'];
	}

/*
	function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, &$parent)	{
		if ($status=='new')	{
			$config = t3lib_div::getUserObj('EXT:kb_shop/class.tx_kbshop_config.php:&tx_kbshop_config');
			$config->init($this);	
			if ((substr($table, 0, strlen($config->sectionTablePrefix))==$config->sectionTablePrefix)&&(substr($table, -strlen($config->sectionTablePostfix))==$config->sectionTablePostfix))	{
					// Don't display newly created section entries alone (They will get rendered by the userFunc)
				unset($parent->substNEWwithIDs_table[$id]);
				unset($parent->substNEWwithIDs[$id]);
			}
		}
	}
*/

	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$parent)	{
		if (intval($GLOBALS['TCA'][$table]['ctrl']['virtual']) && ($status=='new'))	{
			$uid = intval(substr($id, 3));
			$parent->returnTables[$table][$uid] = $fieldArray;
			$parent->returnTables[$table][$uid]['uid'] = $uid;
			$fieldArray = array();
		}
	}

}


?>
