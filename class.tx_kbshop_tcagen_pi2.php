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
 * FE-Plugin flexform DS generator (for uncached version)
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

class ux_tx_kbshop_tcagen_pi extends tx_kbshop_tcagen_pi	{
	var $defaultDSFile = 'EXT:kb_shop/res/flexform_ds_pi2.xml';
	
	function __renderTCA_Element($config, $prop, $xmldata, $displayCond)	{
			$this->renderTCA__ItemSetSearch($prop);
			return parent::__renderTCA_Element($config, $prop, $xmldata, $displayCond);
	}

	function renderTCA__ItemSetSearch($prop)	{
		$title = $this->labelPrefix[0].$prop['title'];
		$lll = ($this->lllPrefix?($this->lllPrefix.'__'):'').$prop['__key'];
		$this->LLBuffer[0]['pi_'.$lll] = $title;
		$langProps = tx_kbshop_abstract::getRecordsByField($this->config->propertiesTable, 'l18n_parent', $prop['uid'], 'sys_language_uid!=0');
		if (is_array($langProps))	{
			foreach ($langProps as $langProp)	{
//				print_r($langProp);
//				echo "TODO";
//				exit();
				$title = $this->labelPrefix[$langProp['sys_language_uid']].$langProp['title'];
				$this->LLBuffer[$langProp['sys_language_uid']]['pi_'.$lll] = $title;
			}
		}

		$this->xml['sheets']['search']['ROOT']['el']['list_searchfield_section']['el']['list_searchfield_item']['el']['field_search_field']['TCEforms']['config']['items'][] = array('LLL:EXT:'.$this->config->configExt.'/locallang_dyn.xml:pi_'.$lll, $this->config->fieldPrefix.$prop['__key']);
	}



}


?>
