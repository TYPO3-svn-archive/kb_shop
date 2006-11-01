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
		$this->xml['sheets']['search']['ROOT']['el']['list_searchfield_section']['el']['list_searchfield_item']['el']['field_search_field']['TCEforms']['config']['items'][] = array($prop['title'], $this->config->origFieldPrefix.$prop['__key']);
	}



}


?>
