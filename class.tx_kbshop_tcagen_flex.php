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
 * TCA Flexform Generator for KB-Shop
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

class tx_kbshop_tcagen_flex extends tx_kbshop_tcagen	{

	function __initTCA(&$xml)	{
		$xml['sheets'] = array();
	}
	
	function __getPropCatKey($idx, $propArr)	{
		return ($idx==-1)?'sDEF':'sheet_'.$idx;
	}

	function __addTCA_Cat(&$xml, $key, $idx, $new)	{
		$xml['sheets'][$key] = $new;
	}

	function __renderTCA_Cat($idx, $sheetArr, $propArr)	{
		$sheet = array(
			'ROOT' => array(
				'TCEforms' => array(
					'sheetTitle' => $sheetArr['title'],
				),
				'type' => 'array',
				'el' => $propArr,
			),
		);
		return $sheet;
	}

	function __store_RequestUpdate(&$xml, $rQU)	{
		$xml['requestUpdate'] = implode(',', $rQU);
		$GLOBALS['TCA'][$this->config->entriesTable]['ctrl']['requestUpdate'] .= ($GLOBALS['TCA'][$this->config->entriesTable]['ctrl']['requestUpdate']?',':'').$xml['requestUpdate'];
	}

	function __renderTCA_Element($config, $prop, $xmldata, $displayCond)	{
		if ($config['__directReturn'])	{
			unset($config['__directReturn']);
			return $config;
		}
		$ret = array(
			'TCEforms' => array(
				'label' => $prop['title'],
				'exclude' => 1,
				'config' => $config,
			),
		);
		if ($prop['type']==3)	{
			$ret['TCEforms']['defaultExtras'] = $this->getSpecialConf($prop, $xmldata);
		}
		if (strlen($displayCond))	{
			$ret['TCEforms']['displayCond'] = $displayCond;
		}
		return $ret;
	}


	function renderTCA__container($xmlArr, $prop)	{
		$sub = array();
		if (is_array($prop['_SUBPROPS'])&&count($prop['_SUBPROPS']))	{
			foreach ($prop['_SUBPROPS'] as $subprop)	{
				list($key, $value) = $this->renderTCA_Element($subprop, $prop['__displayCond']);
				$sub[$key] = $value;
			}
		}
		$ret = array(
			'type' => 'array',
			'label' => $prop['title'],
			'section' => 1,
			'tx_templavoila' => array(
				'title' => $prop['title'],
			),
			'__directReturn' => true,
			'el' => array(
				$this->config->fieldPrefix.$prop['__key'] => array(
					'type' => 'array',
					'label' => $prop['title'],
					'tx_templavoila' => array(
						'title' => $prop['title'],
					),
					'el' => $sub;
				),
			),
		);
		return $ret;
	}


}


?>
