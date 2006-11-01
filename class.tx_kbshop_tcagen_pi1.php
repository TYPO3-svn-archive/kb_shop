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
 * FE-Plugin flexform DS generator (for cached version)
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

class tx_kbshop_tcagen_pi extends tx_kbshop_tcagen	{
	var $defaultDSFile = 'EXT:kb_shop/res/flexform_ds_pi1.xml';
	var $labelPrefix = '';
	var $fieldPostfix = '';
	var $LLBuffer = array();

	function __initTCA(&$xml)	{
		$xml = t3lib_div::xml2array(t3lib_div::getURL(t3lib_div::getFileAbsFileName($this->defaultDSFile)));
		$this->xml = &$xml;
	}

		/*
		 * Overwrite base renderTCA cause when we have to render a TCA we get fed with an array containig all types first.
		 */
	function renderTCA($typeArr)	{
		$allProps = array();
		$cnt = 4;
		foreach ($typeArr as $catIdx => $catProps)	{
			$allProps = t3lib_div::array_merge_recursive_overrule($allProps, $catProps);
		}
		$fieldPrefix = $this->config->fieldPrefix;
		$this->config->fieldPrefix = $this->config->piComparePrefix.$this->config->fieldPrefix;
		parent::renderTCA($allProps);
		$this->config->fieldPrefix = $fieldPrefix;
			// Bring negate field to the end.
		$negate = $this->xml['sheets']['criteria']['ROOT']['el']['list_criteria_section']['el']['list_criteria_item']['el']['field_compare_negate'];
		unset($this->xml['sheets']['criteria']['ROOT']['el']['list_criteria_section']['el']['list_criteria_item']['el']['field_compare_negate']);
		$this->xml['sheets']['criteria']['ROOT']['el']['list_criteria_section']['el']['list_criteria_item']['el']['field_compare_negate'] = $negate;
		return $this->xml;
	}
	
	function __getPropCatKey($idx, $propArr)	{
		return 'criteria';		// TODO: Change here for search plugin.
	}

	function __addTCA_Cat(&$xml, $key, $idx, $new)	{
		$xml['sheets'][$key] = array_merge_recursive($xml['sheets'][$key], $new);
	}

	function __renderTCA_Cat($idx, $sheetArr, $propArr)	{
		$sheet = array(
			'ROOT' => array(
				'el' => array(
					'list_criteria_section' => array(
						'el' => array(
							'list_criteria_item' => array(
								'el' => $propArr,
							),
						)
					),
				),
			),
		);
		return $sheet;
	}

	function __store_RequestUpdate(&$xml, $rQU)	{
		$xml['requestUpdate'] = implode(',', $rQU);
		$GLOBALS['TCA']['tt_content']['ctrl']['requestUpdate'] .= ($GLOBALS['TCA']['tt_content']['ctrl']['requestUpdate']?',':'').implode(',', $this->requestUpdate);
	}

	function __renderTCA_Element($config, $prop, $xmldata, $displayCond)	{
		if ($config['__containsFields'])	{
			return $config;
		}
		if (!$displayCond)	{
			$displayCond = 'FIELD:field_compare_usersel:REQ:false && FIELD:field_compare_field:=:'.$this->config->fieldPrefix.$prop['__key'].$this->fieldPostfix;
		}
		if ($config)	{
			$ret = array(
				'TCEforms' => array(
					'label' => $prop['title'],
					'exclude' => 1,
					'config' => $config,
				),
			);
			if ($displayCond)	{
				$ret['TCEforms']['displayCond'] = $displayCond;
			}
			return $ret;
		}
		return false;
	}




		/*
		 *  Overloaded rendering methods
		 */

	function renderTCA__Select($xmlArr, $prop)	{
		if (!$prop['__displayCond'])	{
			$dc = 'FIELD:field_compare_usersel:REQ:false && FIELD:field_compare_field:=:'.$this->config->fieldPrefix.$prop['__key'].$this->fieldPostfix;
			$prop['__displayCond'] = $dc;
		}
		$this->renderTCA__ItemSet($xmlArr, $prop);
		$this->renderTCA__ItemDisplayCond('field_compare_usersel', $prop['__key']);
		$config = array(		// TODO: Make adjustable via some configuration.
			'size' => 5,
			'autoSizeMax' => 20,
			'maxitems' => 50,
		);
		$config = parent::renderTCA__Select($xmlArr, $prop, $config);
		return $config;
	}

	function renderTCA__Text($xmlArr, $prop)	{
		$this->renderTCA__ItemSet($xmlArr, $prop);
		$this->renderTCA__ItemDisplayCond('field_compare_string', $prop['__key']);
		$this->renderTCA__ItemDisplayCond($this->config->piComparePrefix.'string', $prop['__key']);
		return false;
	}

	function renderTCA__String($xmlArr, $prop)	{
		$this->renderTCA__ItemSet($xmlArr, $prop);
		$this->renderTCA__ItemDisplayCond('field_compare_usersel', $prop['__key']);
		$this->renderTCA__ItemDisplayCond('field_compare_string', $prop['__key']);
		$this->renderTCA__ItemDisplayCond($this->config->piComparePrefix.'string', $prop['__key']);
		return false;
	}

	function renderTCA__RTE($xmlArr, $prop)	{
		$this->renderTCA__ItemSet($xmlArr, $prop);
		$this->renderTCA__ItemDisplayCond('field_compare_string', $prop['__key']);
		$this->renderTCA__ItemDisplayCond($this->config->piComparePrefix.'string', $prop['__key']);
		return false;
	}

	function renderTCA__Decimal($xmlArr, $prop)	{
		$this->renderTCA__ItemSet($xmlArr, $prop);
		$this->renderTCA__ItemDisplayCond('field_compare_usersel', $prop['__key']);
		$this->renderTCA__ItemDisplayCond('field_compare_number', $prop['__key']);
		$this->renderTCA__ItemDisplayCond($this->config->piComparePrefix.'int', $prop['__key']);
		return false;
	}

	function renderTCA__Integer($xmlArr, $prop)	{
		$this->renderTCA__ItemSet($xmlArr, $prop);
		$this->renderTCA__ItemDisplayCond('field_compare_usersel', $prop['__key']);
		$this->renderTCA__ItemDisplayCond('field_compare_number', $prop['__key']);
		$this->renderTCA__ItemDisplayCond($this->config->piComparePrefix.'double', $prop['__key']);
		return false;
	}

	function renderTCA__Check($xmlArr, $prop)	{
		$this->renderTCA__ItemSet($xmlArr, $prop);
		$this->renderTCA__ItemDisplayCond('field_compare_usersel', $prop['__key']);
		$this->renderTCA__ItemDisplayCond('field_compare_value_bool', $prop['__key']);
		$this->renderTCA__ItemDisplayCond('field_compare_negate', $prop['__key']);
		return false;
	}

	function renderTCA__Date($xmlArr, $prop)	{
		$this->renderTCA__ItemSet($xmlArr, $prop);
		$this->renderTCA__ItemDisplayCond('field_compare_usersel', $prop['__key']);
		$this->renderTCA__ItemDisplayCond('field_compare_date', $prop['__key']);
		$this->renderTCA__ItemDisplayCond('field_compare_value_dateoffset', $prop['__key']);
		$this->renderTCA__ItemDisplayCond($this->config->piComparePrefix.'date', $prop['__key']);
		return false;
	}

	function renderTCA__Time($xmlArr, $prop)	{
		$this->renderTCA__ItemSet($xmlArr, $prop);
		$this->renderTCA__ItemDisplayCond('field_compare_usersel', $prop['__key']);
		$this->renderTCA__ItemDisplayCond('field_compare_date', $prop['__key']);
		$this->renderTCA__ItemDisplayCond('field_compare_value_dateoffset', $prop['__key']);
		$this->renderTCA__ItemDisplayCond($this->config->piComparePrefix.'time', $prop['__key']);
		return false;
	}

	function renderTCA__Timesec($xmlArr, $prop)	{
		$this->renderTCA__ItemSet($xmlArr, $prop);
		$this->renderTCA__ItemDisplayCond('field_compare_usersel', $prop['__key']);
		$this->renderTCA__ItemDisplayCond('field_compare_date', $prop['__key']);
		$this->renderTCA__ItemDisplayCond('field_compare_value_dateoffset', $prop['__key']);
		$this->renderTCA__ItemDisplayCond($this->config->piComparePrefix.'timesec', $prop['__key']);
		return false;
	}

	function renderTCA__DateTime($xmlArr, $prop)	{
		$this->renderTCA__ItemSet($xmlArr, $prop);
		$this->renderTCA__ItemDisplayCond('field_compare_usersel', $prop['__key']);
		$this->renderTCA__ItemDisplayCond('field_compare_date', $prop['__key']);
		$this->renderTCA__ItemDisplayCond('field_compare_value_dateoffset', $prop['__key']);
		$this->renderTCA__ItemDisplayCond($this->config->piComparePrefix.'datetime', $prop['__key']);
		return false;
	}

	function renderTCA__Year($xmlArr, $prop)	{
		$this->renderTCA__ItemSet($xmlArr, $prop);
		$this->renderTCA__ItemDisplayCond('field_compare_usersel', $prop['__key']);
		$this->renderTCA__ItemDisplayCond('field_compare_date', $prop['__key']);
		$this->renderTCA__ItemDisplayCond('field_compare_value_dateoffset', $prop['__key']);
		$this->renderTCA__ItemDisplayCond($this->config->piComparePrefix.'year', $prop['__key']);
		return false;
	}

	/*
	 * TYPEADD
	 *
	 * Add pi plugin render-methods for each property type. Those will create the flexform
	 * for the pi plugin
	 *
	 */

	function renderTCA__dbrel($xmlArr, $prop)	{
		if (!$prop['__displayCond'])	{
			$dc = 'FIELD:field_compare_usersel:REQ:false;';
			$prop['__displayCond'] = $dc;
		}
		$this->renderTCA__ItemSet($xmlArr, $prop);
		$this->renderTCA__ItemDisplayCond('field_compare_usersel', $prop['__key']);
		$prop['__displayCond'] .= ' && FIELD:field_compare_field:=:'.$this->config->fieldPrefix.$prop['__key'].$this->fieldPostfix;
		$config = array(		// TODO: Make adjustable via some configuration.
			'size' => 5,
			'autoSizeMax' => 20,
			'maxitems' => 50,
			'noMM' => true,
		);
		$config = parent::renderTCA__dbrel($xmlArr, $prop, $config);
		return $config;
	}

	function renderTCA__container($xmlArr, $prop)	{
		$tempLabelPrefix = $this->labelPrefix;
		$tempLLLPrefix = $this->lllPrefix;
		$this->labelPrefix[0] = $prop['title'].': ';
		$langProps = tx_kbshop_abstract::getRecordsByField($this->config->propertiesTable, 'l18n_parent', $prop['uid'], 'sys_language_uid!=0');
		if (is_array($langProps))	{
			foreach ($langProps as $langProp)	{
				$this->labelPrefix[$langProp['sys_language_uid']] = $langProp['title'].': ';
			}
		}
		$this->lllPrefix .= $prop['__key'];

		$tempFieldPostfix = $this->fieldPostfix;
		$this->fieldPostfix = '___'.$this->config->sectionTablePrefix.$this->tableKey.'___'.$prop['__key'].$this->config->sectionTablePostfix;
		$ret = array();
		if (is_array($prop['_SUBPROPS'])&&count($prop['_SUBPROPS']))	{
			foreach ($prop['_SUBPROPS'] as $subprop)	{
				$el = $this->renderTCA_Element($subprop);
				if ($el)	{
					list($key, $value) = $el;
					$ret[$key.$this->fieldPostfix] = $value;
				}
			}
		}
		$this->labelPrefix = $tempLabelPrefix;
		$this->lllPrefix = $tempLLLPrefix;
		$this->fieldPostfix = $tempFieldPostfix;
		if (count($ret))	{
			$ret['__containsFields'] = true;
			return $ret;
		}
		return false;
	}

	function renderTCA__multiCheck($xmlArr, $prop)	{
		$this->renderTCA__ItemSet($xmlArr, $prop);
		$this->renderTCA__ItemDisplayCond('field_compare_usersel', $prop['__key']);
		$this->renderTCA__ItemDisplayCond('field_compare_rendercheck_multibool', $prop['__key']);
		$config = parent::renderTCA__multiCheck($xmlArr, $prop);
		return $config;
	}

	function renderTCA__File($xmlArr, $prop)	{
		return false;
	}

	/*
	 * TYPEADD
	 */


	function renderTCA__ItemDisplayCond($field, $key)	{
			// Main criterias
		if (is_string($this->xml['sheets']['criteria']['ROOT']['el']['list_criteria_section']['el']['list_criteria_item']['el'][$field]['TCEforms']['displayCond']))	{
			$this->xml['sheets']['criteria']['ROOT']['el']['list_criteria_section']['el']['list_criteria_item']['el'][$field]['TCEforms']['displayCond'] .= ((substr($this->xml['sheets']['criteria']['ROOT']['el']['list_criteria_section']['el']['list_criteria_item']['el'][$field]['TCEforms']['displayCond'], -1)==':')?'':',').$this->config->fieldPrefix.$key.$this->fieldPostfix;
		} else	{
			$this->xml['sheets']['criteria']['ROOT']['el']['list_criteria_section']['el']['list_criteria_item']['el'][$field]['TCEforms']['displayCond'] = 'FIELD:field_compare_field:IN:'.$this->config->fieldPrefix.$key.$this->fieldPostfix;
		}
			// Sub criterias
		if (is_string($this->xml['sheets']['criteria']['ROOT']['el']['list_criteria_section']['el']['list_subcriteria']['el']['list_criteria_section']['el']['list_criteria_item']['el'][$field]['TCEforms']['displayCond']))	{
			$this->xml['sheets']['criteria']['ROOT']['el']['list_criteria_section']['el']['list_subcriteria']['el']['list_criteria_section']['el']['list_criteria_item']['el'][$field]['TCEforms']['displayCond'] .= ((substr($this->xml['sheets']['criteria']['ROOT']['el']['list_criteria_section']['el']['list_subcriteria']['el']['list_criteria_section']['el']['list_criteria_item']['el'][$field]['TCEforms']['displayCond'], -1)==':')?'':',').$this->config->fieldPrefix.$key.$this->fieldPostfix;
		} else	{
			$this->xml['sheets']['criteria']['ROOT']['el']['list_criteria_section']['el']['list_subcriteria']['el']['list_criteria_section']['el']['list_criteria_item']['el'][$field]['TCEforms']['displayCond'] = 'FIELD:field_compare_field:IN:'.$this->config->fieldPrefix.$key.$this->fieldPostfix;
		}
	}

	function renderTCA__ItemSet($xmlArr, $prop)	{
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
			// Main criterias
		$this->xml['sheets']['criteria']['ROOT']['el']['list_criteria_section']['el']['list_criteria_item']['el']['field_compare_field']['TCEforms']['config']['items'][] = array('LLL:EXT:'.$this->config->configExt.'/locallang_dyn.xml:pi_'.$lll, $this->config->fieldPrefix.$prop['__key'].$this->fieldPostfix);
			// Sub criterias
		$this->xml['sheets']['criteria']['ROOT']['el']['list_criteria_section']['el']['list_subcriteria']['el']['list_criteria_section']['el']['list_criteria_item']['el']['field_compare_field']['TCEforms']['config']['items'][] = array('LLL:EXT:'.$this->config->configExt.'/locallang_dyn.xml:pi_'.$lll, $this->config->fieldPrefix.$prop['__key'].$this->fieldPostfix);

		$this->xml['sheets']['listView']['ROOT']['el']['list_order_section']['el']['list_order_item']['el']['field_list_order']['TCEforms']['config']['items'][] = array('LLL:EXT:'.$this->config->configExt.'/locallang_dyn.xml:pi_'.$lll, $this->config->fieldPrefix.$prop['__key'].$this->fieldPostfix);
	}



}


?>
