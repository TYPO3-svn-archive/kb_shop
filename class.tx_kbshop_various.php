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

}



?>
