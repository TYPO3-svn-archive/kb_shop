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
 * TCA Generator for KB-Shop
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

class tx_kbshop_tcagen	{
	var $extraProps = array();
	var $config = false;
	var $MMtables = array();
	var $uploadFolders = array();

	/*
	 * TYPEADD: Add methods here if you introduce a new property-type
	 */
	var $renderFunc = array(
		'',
		/* 1 */'renderTCA__Select',
		/* 2 */'renderTCA__Text',
		/* 3 */'renderTCA__RTE',
		/* 4 */'renderTCA__Decimal',
		/* 5 */'renderTCA__Integer',
		/* 6 */'renderTCA__Check',
		/* 7 */'renderTCA__Date',
		/* 8 */'renderTCA__String',
		/* 9 */'renderTCA__dbrel',
		/* 10 */'renderTCA__multiCheck',
		/* 11 */'renderTCA__File',
		/* 12 */'renderTCA__Time',
		/* 13 */'renderTCA__Timesec',
		/* 14 */'renderTCA__DateTime',
		/* 15 */'renderTCA__Year',
		/* 16 */'renderTCA__Link',
		/* 17 */'renderTCA__User',
		/* 18 */'',
		/* 19 */'',
		/* 20 */'',
		/* 21 */'',
		/* 22 */'',
		/* 23 */'',
		/* 24 */'',
		/* 25 */'',
		/* 26 */'',
		/* 27 */'',
		/* 28 */'',
		/* 29 */'',
		/* 30 */'',
		/* 31 */'',
		/* 32 */'',
		/* 33 */'',
		/* 34 */'',
		/* 35 */'',
		/* 36 */'',
		/* 37 */'',
		/* 38 */'',
		/* 39 */'',
		/* 40 */'',
		/* 41 */'',
		/* 42 */'',
		/* 43 */'',
		/* 44 */'',
		/* 45 */'',
		/* 46 */'',
		/* 47 */'',
		/* 48 */'',
		/* 49 */'',
		/* 50 */'',
		/* 51 */'',
		/* 52 */'',
		/* 53 */'',
		/* 54 */'',
		/* 55 */'',
		/* 56 */'',
		/* 57 */'',
		/* 58 */'',
		/* 59 */'',
		/* 60 */'',
		/* 61 */'',
		/* 62 */'',
		/* 63 */'',
		/* 64 */'',
		/* 65 */'',
		/* 66 */'',
		/* 67 */'',
		/* 68 */'',
		/* 69 */'',
		/* 70 */'',
		/* 71 */'',
		/* 72 */'',
		/* 73 */'',
		/* 74 */'',
		/* 75 */'',
		/* 76 */'',
		/* 77 */'',
		/* 78 */'',
		/* 79 */'',
		/* 80 */'',
		/* 81 */'',
		/* 82 */'',
		/* 83 */'',
		/* 84 */'',
		/* 85 */'',
		/* 86 */'',
		/* 87 */'',
		/* 88 */'',
		/* 89 */'',
		/* 90 */'',
		/* 91 */'',
		/* 92 */'',
		/* 93 */'',
		/* 94 */'',
		/* 95 */'',
		/* 96 */'',
		/* 97 */'',
		/* 98 */'',
		/* 99 */'',
		/* 100 */'renderTCA__geolocation',
		/* 101 */'',
		/* 102 */'',
		/* 103 */'',
		/* 104 */'',
		/* 105 */'',
		/* 106 */'',
		/* 107 */'',
		/* 108 */'',
		/* 109 */'',
		/* 110 */'',
		/* 111 */'',
		/* 112 */'',
		/* 113 */'',
		/* 114 */'',
		/* 115 */'',
		/* 116 */'',
		/* 117 */'',
		/* 118 */'',
		/* 119 */'',
		/* 120 */'',
		/* 121 */'',
		/* 122 */'',
		/* 123 */'',
		/* 124 */'',
		/* 125 */'',
		/* 126 */'',
		/* 127 */'',
		/* 128 */'',
		/* 129 */'',
		/* 130 */'',
		/* 131 */'',
		/* 132 */'',
		/* 133 */'',
		/* 134 */'',
		/* 135 */'',
		/* 136 */'',
		/* 137 */'',
		/* 138 */'',
		/* 139 */'',
		/* 140 */'',
		/* 141 */'',
		/* 142 */'',
		/* 143 */'',
		/* 144 */'',
		/* 145 */'',
		/* 146 */'',
		/* 147 */'',
		/* 148 */'',
		/* 149 */'',
		/* 150 */'',
		/* 151 */'',
		/* 152 */'',
		/* 153 */'',
		/* 154 */'',
		/* 155 */'',
		/* 156 */'',
		/* 157 */'',
		/* 158 */'',
		/* 159 */'',
		/* 160 */'',
		/* 161 */'',
		/* 162 */'',
		/* 163 */'',
		/* 164 */'',
		/* 165 */'',
		/* 166 */'',
		/* 167 */'',
		/* 168 */'',
		/* 169 */'',
		/* 170 */'',
		/* 171 */'',
		/* 172 */'',
		/* 173 */'',
		/* 174 */'',
		/* 175 */'',
		/* 176 */'',
		/* 177 */'',
		/* 178 */'',
		/* 179 */'',
		/* 180 */'',
		/* 181 */'',
		/* 182 */'',
		/* 183 */'',
		/* 184 */'',
		/* 185 */'',
		/* 186 */'',
		/* 187 */'',
		/* 188 */'',
		/* 189 */'',
		/* 190 */'',
		/* 191 */'',
		/* 192 */'',
		/* 193 */'',
		/* 194 */'',
		/* 195 */'',
		/* 196 */'',
		/* 197 */'',
		/* 198 */'',
		/* 199 */'',
		/* 200 */'renderTCA__container',
		/* 201 */'',
		/* 202 */'',
		/* 203 */'',
		/* 204 */'',
		/* 205 */'',
		/* 206 */'',
		/* 207 */'',
		/* 208 */'',
		/* 209 */'',
		);

	function init(&$config)	{
		$this->config = &$config;
		$this->vDEF = $this->config->vDEF;
		$this->lDEF = $this->config->lDEF;
	}


	function renderTCA($allProps)	{
		$ret = array();
		$this->__initTCA($ret);
		foreach ($allProps as $idx => $propSheet)	{
			$key = $this->__getPropCatKey($idx, $propSheet);
			$h = $this->renderTCA_Cat($idx, $propSheet);
			$this->__addTCA_Cat($ret, $key, $idx, $h);
		}
		if (count($this->requestUpdate))	{
			$this->requestUpdate = array_unique($this->requestUpdate);
			$this->__store_RequestUpdate($ret, $this->requestUpdate);
		}
		$this->__postProcess($ret, $allProps);
		return $ret;
	}

	function renderTCA_Cat($idx, $sheetArr)	{
		$propArr = array();
		if (is_array($sheetArr['props']))	{
			foreach ($sheetArr['props'] as $prop)	{
				$this->extraProps = array();
				$el = $this->renderTCA_Element($prop);
				if ($el)	{
					if ($el['__containsFields'])	{
						unset($el['__containsFields']);
						foreach ($el as $key => $value)	{
							$propArr[$this->config->fieldPrefix.$key] = $value;
						}
					} else	{
						list($key, $value) = $el;
						$propArr[$this->config->fieldPrefix.$key] = $value;
					}
					if ($this->config->reverseExtraProps)	{
						$this->extraProps = array_reverse($this->extraProps);
					}
					$propArr = array_merge($propArr, $this->extraProps);
				}
			}
		}
		return $this->__renderTCA_Cat($idx, $sheetArr, $propArr);
	}

	function renderTCA_Element($prop, $displayCond = '')	{
		$xmldata = t3lib_div::xml2array($prop['flexform']);
		$ret = array();
		$alias = preg_replace('/[^a-z0-9_]/', '', strtolower($prop['alias']));
		if ($displayCond)	{
			$prop['__displayCond'] = $displayCond;
		}
		$prop['__alias'] = $alias;
		$prop['__key'] = $alias?$alias:$prop['uid'];
		$t = intval($prop['type']);
	/*
	 * TYPEADD: Increase upper bound here if you introduce a new property-type
	 */
		$method = $this->renderFunc[$t];
		if (strlen($method))	{
			$config = $this->$method($xmldata['data']['sDEF'][$this->lDEF], $prop);
		} else	{
				// TODO: LOG
			die('Invalid TCA render-func !');
			return false;
		}
		if (is_array($config)&&!$config['type'])	{
			foreach ($config as $cArr)	{
				$ret[$cArr['prop']['__key']] = $this->__renderTCA_Element($cArr['config'], $cArr['prop'], $xmldata['data']['sDEF'][$this->lDEF], $displayCond);
			}
			$ret['__containsFields'] = true;
		} else	{
			$ret = $this->__renderTCA_Element($config, $prop, $xmldata['data']['sDEF'][$this->lDEF], $displayCond);
		}
		if (is_array($ret)&&$ret['__containsFields'])	{
			return $ret;
		} elseif (is_array($ret))	{
			return array($prop['__key'], $ret);
		} else	{
			return false;
		}
	}



	function renderTCA__Select($xmlArr, $prop, $preConfig = array())	{
		$items = array();
		foreach ($xmlArr['list_value_section']['el'] as $xmlSub)	{
			$items[] = array(
					// TODO: Add multi language support - currently only ISO-8859-1
//				$xmlSub['list_value_field']['el']['field_value'][$this->vDEF],
				tx_kbshop_abstract::csConv($xmlSub['list_value_field']['el']['field_value'][$this->vDEF], $this->config->currentCharset, 'iso-8859-1'),
				$xmlSub['list_value_field']['el']['field_index'][$this->vDEF],
				trim($xmlSub['list_value_field']['el']['field_icon'][$this->vDEF])?('../uploads/tx_kbshop/selecticons/'.trim($xmlSub['list_value_field']['el']['field_icon'][$this->vDEF])):'',
			);
		}
		$config = array(
			'type' => 'select',
			'items' => $items,
			'size' => intval($preConfig['size'])?intval($preConfig['size']):1,
			'maxitems' => intval($preConfig['maxitems'])?intval($preConfig['maxitems']):1,
		);
		if (intval($preConfig['autoSizeMax']))	{
			$config['autoSizeMax'] = intval($preConfig['autoSizeMax']);
		}
		if (is_array($prop['_SUBPROPS'])&&count($prop['_SUBPROPS']))	{
			$this->requestUpdate[] = $this->config->fieldPrefix.$prop['__key'];
			foreach ($prop['_SUBPROPS'] as $subprop)	{
				list($key, $value) = $this->renderTCA_Element($subprop, $prop['__displayCond'].($prop['__displayCond']?' && ':'').'FIELD:'.$this->config->fieldPrefix.$prop['__key'].':'.(($config['size']>1)?'CONTAINS':'=').':'.$subprop['parent_value']);
				$this->extraProps = array_merge(array($this->config->fieldPrefix.$key => $value), $this->extraProps);
			}
		}
		return $config;
	}

	function renderTCA__Text($xmlArr, $prop)	{
		$config = array(
			'type' => 'text',
		);
		if (strlen($xmlArr['field_cols'][$this->vDEF]))	{
			$config['cols'] = intval($xmlArr['field_cols'][$this->vDEF]);
		}
		if (strlen($xmlArr['field_rows'][$this->vDEF]))	{
			$config['rows'] = intval($xmlArr['field_rows'][$this->vDEF]);
		}
		return $config;
	}

	function renderTCA__RTE($xmlArr, $prop)	{
		$config = array(
			'type' => 'text',
			'wizards' => Array(
				'_PADDING' => 4,
				'RTE' => Array(
					'notNewRecords' => 1,
					'RTEonly' => 1,
					'type' => 'script',
					'title' => 'LLL:EXT:cms/locallang_ttc.php:bodytext.W.RTE',
					'icon' => 'wizard_rte2.gif',
					'script' => 'wizard_rte.php',
				),
			),
		);
		if (strlen($xmlArr['field_cols'][$this->vDEF]))	{
			$config['cols'] = intval($xmlArr['field_cols'][$this->vDEF]);
		}
		if (strlen($xmlArr['field_rows'][$this->vDEF]))	{
			$config['rows'] = intval($xmlArr['field_rows'][$this->vDEF]);
		}
		return $config;
	}

	function renderTCA__Decimal($xmlArr, $prop)	{
		$config = array(
			'type' => 'input',
			'eval' => 'double2',
			'size' => '15',
		);
		if (strlen($xmlArr['field_min'][$this->vDEF])||strlen($xmlArr['field_max'][$this->vDEF]))	{
			if (strlen($xmlArr['field_min'][$this->vDEF]))	{
				$config['range']['lower'] = intval($xmlArr['field_min'][$this->vDEF]);
			}
			if (strlen($xmlArr['field_max'][$this->vDEF]))	{
				$config['range']['upper'] = intval($xmlArr['field_max'][$this->vDEF]);
			}
		}
		if (strlen($xmlArr['field_default'][$this->vDEF]))	{
			$config['default'] = sprintf('%.2f', doubleval($xmlArr['field_default'][$this->vDEF]));
		} else	{
			$config['default'] = sprintf('%.2f', 0);
		}
		if (intval($xmlArr['field_size'][$this->vDEF])>0)	{
			$config['size'] = intval($xmlArr['field_size'][$this->vDEF]);
		}
		if (intval($xmlArr['field_required'][$this->vDEF]))	{
			$config['eval'] .= ',required';
		}
		return $config;
	}


	function renderTCA__Integer($xmlArr, $prop)	{
		$config = array(
			'type' => 'input',
			'eval' => 'int',
			'size' => '10',
		);
		if (strlen($xmlArr['field_min'][$this->vDEF])||strlen($xmlArr['field_max'][$this->vDEF]))	{
			if (strlen($xmlArr['field_min'][$this->vDEF]))	{
				$config['range']['lower'] = intval($xmlArr['field_min'][$this->vDEF]);
			}
			if (strlen($xmlArr['field_max'][$this->vDEF]))	{
				$config['range']['upper'] = intval($xmlArr['field_max'][$this->vDEF]);
			}
		}
		if (strlen($xmlArr['field_default'][$this->vDEF]))	{
			$config['default'] = intval($xmlArr['field_default'][$this->vDEF]);
		}
		if (intval($xmlArr['field_size'][$this->vDEF])>0)	{
			$config['size'] = intval($xmlArr['field_size'][$this->vDEF]);
		}
		if (intval($xmlArr['field_required'][$this->vDEF]))	{
			$config['eval'] .= ',required';
		}
		if (intval($xmlArr['field_readonly'][$this->vDEF]))	{
			$config['readOnly'] = '1';
		}
		if ($wt = ($xmlArr['field_wizard_type'][$this->vDEF]))	{
			$wn = $xmlArr['field_wizard_title'][$this->vDEF];
			$wns = preg_replace('/[^a-z0-9A-Z]/', '_', $wn);
			$config['wizards'] = array(
				'_PADDING' => 1,
				'_VERTICAL' => 1,
				$wns => array(
					'type' => $wt,
					'title' => $wn,
				),
			);
			if ($wi = ($xmlArr['field_wizard_icon'][$this->vDEF]))	{
				$config['wizards'][$wns]['icon'] = '../uploads/tx_kbshop/wizardicons/'.$wi;
			}
			$config['wizards'][$wns][$wt] = $xmlArr['field_wizard_script'][$this->vDEF];
		}
		return $config;
	}

	function renderTCA__Check($xmlArr, $prop)	{
		$config = array(
			'type' => 'check',
		);
		if (intval($xmlArr['field_default'][$this->vDEF]))	{
			$config ['default'] = 1;
		}
		return $config;
	}

	function renderTCA__Date($xmlArr, $prop)	{
		$config = array(
			'type' => 'input',
			'eval' => 'date',
			'size' => '10',
			'checkbox' => 0,
			'default' => '0',
		);
		if (intval($xmlArr['field_default'][$this->vDEF]))	{
			$config ['default'] = intval($xmlArr['field_default'][$this->vDEF]);
		}
		return $config;
	}
	
	function renderTCA__Time($xmlArr, $prop)	{
		$config = array(
			'type' => 'input',
			'eval' => 'time',
			'size' => '10',
			'checkbox' => 0,
			'default' => '0',
		);
		if (intval($xmlArr['field_default'][$this->vDEF]))	{
			$config ['default'] = intval($xmlArr['field_default'][$this->vDEF]);
		}
		return $config;
	}

	function renderTCA__Timesec($xmlArr, $prop)	{
		$config = array(
			'type' => 'input',
			'eval' => 'timesec',
			'size' => '10',
			'checkbox' => 0,
			'default' => '0',
		);
		if (intval($xmlArr['field_default'][$this->vDEF]))	{
			$config ['default'] = intval($xmlArr['field_default'][$this->vDEF]);
		}
		return $config;
	}
	
	function renderTCA__DateTime($xmlArr, $prop)	{
		$config = array(
			'type' => 'input',
			'eval' => 'datetime',
			'size' => '10',
			'checkbox' => 0,
			'default' => '0',
		);
		if (intval($xmlArr['field_default'][$this->vDEF]))	{
			$config ['default'] = intval($xmlArr['field_default'][$this->vDEF]);
		}
		if ($wt = ($xmlArr['field_wizard_type'][$this->vDEF]))	{
			$wn = $xmlArr['field_wizard_title'][$this->vDEF];
			$wns = preg_replace('/[^a-z0-9A-Z]/', '_', $wn);
			$config['wizards'] = array(
				'_PADDING' => 1,
				'_VERTICAL' => 1,
				$wns => array(
					'type' => $wt,
					'title' => $wn,
				),
			);
			if ($wi = ($xmlArr['field_wizard_icon'][$this->vDEF]))	{
				$config['wizards'][$wns]['icon'] = '../uploads/tx_kbshop/wizardicons/'.$wi;
			}
			$config['wizards'][$wns][$wt] = $xmlArr['field_wizard_script'][$this->vDEF];
		}
		return $config;
	}

	function renderTCA__Year($xmlArr, $prop)	{
		$config = array(
			'type' => 'input',
			'eval' => 'year',
			'size' => '10',
			'checkbox' => 0,
			'default' => '0',
		);
		if (intval($xmlArr['field_default'][$this->vDEF]))	{
			$config ['default'] = intval($xmlArr['field_default'][$this->vDEF]);
		}
		return $config;
	}

	function renderTCA__Link($xmlArr, $prop)	{
		$config = array(
			'type' => 'input',
			'size' => 15,
			'max' => 256,
			'checkbox' => '',
			'eval' => 'trim',
			'wizards' => Array(
				'_PADDING' => 2,
				'link' => Array(
					'type' => 'popup',
					'title' => 'Link',
					'icon' => 'link_popup.gif',
					'script' => 'browse_links.php?mode=wizard',
					'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
				)
			),
			'softref' => 'typolink'
		);
		if (intval($xmlArr['field_required'][$this->vDEF]))	{
			$config['eval'] .= ($config['eval']?',':'').'required';
		}
		return $config;
	}

	function renderTCA__String($xmlArr, $prop)	{
		$config = array(
			'type' => 'input',
			'size' => t3lib_div::intInRange($xmlArr['field_size'][$this->vDEF], 1, 200, 30),
			'max' => t3lib_div::intInRange($xmlArr['field_max'][$this->vDEF], 1, 200, 200),
		);
		if (strlen($xmlArr['field_default'][$this->vDEF]))	{
			$config ['default'] = $xmlArr['field_default'][$this->vDEF];
		}
		if (intval($xmlArr['field_required'][$this->vDEF]))	{
			$config['eval'] .= ($config['eval']?',':'').'required';
		}
		if (intval($xmlArr['field_pass'][$this->vDEF]))	{
			$config['eval'] .= ($config['eval']?',':'').'password';
		}
		if (intval($xmlArr['field_md5'][$this->vDEF]))	{
			$config['eval'] .= ($config['eval']?',':'').'md5';
		}
		if (intval($xmlArr['field_unique'][$this->vDEF]))	{
			$config['eval'] .= ($config['eval']?',':'').'unique';
		}
		if (intval($xmlArr['field_email'][$this->vDEF]))	{
			$config['eval'] .= ',tx_kbshop_isEmail';
		}
		return $config;
	}


	function renderTCA__dbrel($xmlArr, $prop, $preConfig = array())	{
		if ($xmlArr['groupfield'][$this->vDEF])	{
			$config = array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => ($table = $xmlArr['field_table'][$this->vDEF]),
				'size' => $preConfig['size']?$preConfig['size']:t3lib_div::intInRange($xmlArr['field_size'][$this->vDEF], 1, 200, 1),
				'maxitems' => $preConfig['maxitems']?$preConfig['maxitems']:t3lib_div::intInRange($xmlArr['field_maxitems'][$this->vDEF], 1, 200, 1),
				'multiple' => intval($xmlArr['multiple']['vDEF'])?1:0,
			);
		} else	{
			$config = array(
				'type' => 'select',
				'foreign_table' => ($table = $xmlArr['field_table'][$this->vDEF]),
				'size' => $preConfig['size']?$preConfig['size']:t3lib_div::intInRange($xmlArr['field_size'][$this->vDEF], 1, 200, 1),
				'maxitems' => $preConfig['maxitems']?$preConfig['maxitems']:t3lib_div::intInRange($xmlArr['field_maxitems'][$this->vDEF], 1, 200, 1),
				'multiple' => intval($xmlArr['multiple']['vDEF'])?1:0,
			);
			$config['foreign_table_where'] .= tx_kbshop_abstract::deleteClause($table);
			if (strlen($xmlArr['field_pages'][$this->vDEF]))	{
				$config ['foreign_table_where'] .= ' AND '.$table.'.pid IN ('.$xmlArr['field_pages'][$this->vDEF].')';
			}
			if (intval($xmlArr['only_current_page'][$this->vDEF]))	{
				$config ['foreign_table_where'] .= ' AND '.$table.'.pid=###CURRENT_PID###';
			}
			if (strlen($c = $xmlArr['andwhere_clause'][$this->vDEF]))	{
				$config ['foreign_table_where'] .= ' AND '.$c;
			}
			if ($sf = $GLOBALS['TCA'][$table]['ctrl']['sortby'])	{
				$config['foreign_table_where'] .= ' ORDER BY '.$table.'.'.$sf;
			} elseif ($sf = $GLOBALS['TCA'][$table]['ctrl']['default_sortby'])	{
				$sf = preg_replace('/\s*order\s+by\s+/i', '', $sf);
				$config['foreign_table_where'] .= ' ORDER BY '.$table.'.'.$sf;
			}
			if (($sortf = $GLOBALS['TCA'][$table]['ctrl']['sortby'])||($sortf = $GLOBALS['TCA'][$table]['ctrl']['default_sortby']))	{
//			$config ['foreign_table_where'] .= ' ORDER BY '.$table.'.'.$sortf;
			}
			if (strlen($l = $xmlArr['emptyLabel'][$this->vDEF]))	{
				$config['items'] = array(
					array($l, 0),		// TODO: Add multilanguage support
				);
			}
			if (strlen($xmlArr['field_elements'][$this->vDEF]))	{
				$el = t3lib_div::trimExplode(',', $xmlArr['field_elements'][$this->vDEF], 1);
				$ua = array();
				foreach ($el as $e)	{
					$p = explode('_', $e);
					$u = intval(array_pop($p));
					$t = implode('_', $p);
					if ($t==$table)	{
						$ua[] = $u;
					}
				}
				$qs = intval($xmlArr['field_quickselect'][$this->vDEF]);
				if (count($ua))	{
					$not = $qs?'NOT':'';
					$config ['foreign_table_where'] .= ' AND '.$table.'.uid '.$not.' IN ('.implode(',', $ua).')';
				}
				if ($qs&&count($ua))	{
					if (!is_array($config['items']))	{
						$config['items'] = array();
					}
					foreach ($ua as $elUid)	{
						$elRec = tx_kbshop_abstract::getRecord($table, $elUid);
						$elTitle = tx_kbshop_abstract::getRecordTitle($table, $elRec);
						$this->config->baseClass->LLBuffer[0]['select_'.$prop['__key'].'_'.$elUid] = $elTitle;
						$config['items'][] = array('LLL:EXT:'.$this->config->configExt.'/locallang_dyn.xml:select_'.$prop['__key'].'_'.$elUid, $elUid);
					}
					$config['items'][] = array('----------------------', '--div--');
				}
			}
		}
		if (intval($xmlArr['field_autoSizeMax'][$this->vDEF]))	{
			$config ['autoSizeMax'] = t3lib_div::intInRange($xmlArr['field_autoSizeMax'][$this->vDEF], 1, 200, 20);
		}
		if ($preConfig['autoSizeMax'])	{
			$config ['autoSizeMax'] = $preConfig['autoSizeMax'];
		}
		if (($config['maxitems']>1)&&(!intval($preConfig['noMM']))&&(!intval($xmlArr['noMM'][$this->vDEF]))&&(!$this->virtual))	{
			$mkey = $this->entriesTable.'_'.$prop['__key'];
			$config['MM'] = $this->config->mmRelationTablePrefix.$mkey.$this->config->mmRelationTablePostfix;
			$this->MMtables[$mkey] = true;
		}
		return $config;
	}
	
	function renderTCA__container($xmlArr, $prop)	{
		echo 'Method renderTCA__container must get overloaded by expressing rendering class !<br>'.chr(10);
		exit(1);
	}
	
	function renderTCA__multiCheck($xmlArr, $prop)	{
		$cnt = 0;
		$default = 0;
		foreach ($xmlArr['list_value_section']['el'] as $xmlSub)	{
			$items[] = array(
				$xmlSub['list_value_label']['el']['field_label'][$this->vDEF],
				'',
			);
			if (intval($xmlSub['list_value_label']['el']['field_default'][$this->vDEF]))	{
				$default |= (1<<$cnt);
			}
			$cnt++;
			if ($cnt>=10)	{
				break;
			}
		}
		$config = array(
			'type' => 'check',
			'items' => $items,
			'default' => $default,
			'cols' => t3lib_div::intInRange($xmlArr['field_cols'][$this->vDEF], 1, 10, 2),
		);
		return $config;
	}

	function renderTCA__File($xmlArr, $prop)	{
		$uploadfolder = trim($xmlArr['field_uploaddir'][$this->vDEF]);
		if (substr($uploadfolder, 0, 1)=='/')	{
			$uploadfolder = substr($uploadfolder, 1);
		}
		if (substr($uploadfolder, -1)!='/')	{
			$uploadfolder .= '/';
		}
		$config = array(
			'type' => 'group',
			'internal_type' => 'file',
			'allowed' => $xmlArr['field_allowed'][$this->vDEF],
			'disallowed' => $xmlArr['field_disallowed'][$this->vDEF],
			'size' => t3lib_div::intInRange($xmlArr['field_size'][$this->vDEF], 1, 200000, 10),
			'maxitems' => t3lib_div::intInRange($xmlArr['field_maxitems'][$this->vDEF], 1, 200000, 10),
			'max_size' => t3lib_div::intInRange($xmlArr['field_maxfilesize'][$this->vDEF], 1, 200000, 10),
			'uploadfolder' => $uploadfolder,
			'show_thumbs' => intval($xmlArr['field_showthumbs'][$this->vDEF])?1:0,
		);
		if (intval($xmlArr['field_size'][$this->vDEF]))	{
			$config['autoSizeMax'] = t3lib_div::intInRange($xmlArr['field_autoSizeMax'][$this->vDEF], 1, 200000, 10);
		}
		$this->uploadFolders[] = $uploadfolder;
		return $config;
	}


	function renderTCA__geolocation($xmlArr, $prop)	{
		$configlat = array(
			'type' => 'input',
			'size' => '20',
			'max' => '20',
		);
		$configlng = $configlat;
		if (strlen($xmlArr['field_default_lat'][$this->vDEF]))	{
			$configlat['default'] = $xmlArr['field_default_lat'][$this->vDEF];
		}
		if (strlen($xmlArr['field_default_lng'][$this->vDEF]))	{
			$configlng['default'] = $xmlArr['field_default_lng'][$this->vDEF];
		}
		$configlat['wizards'] = array(
			'_POSITION' => 'right',
			'googlemap' => array(
				'title' => 'LLL:EXT:rggooglemap/locallang_db.xml:wizard.title',
				'icon' => $BACK_PATH.t3lib_extMgm::extRelPath('rggooglemap').'mod1/moduleicon.gif',
				'type' => 'popup',
				'script' => 'EXT:rggooglemap/class.tx_rggooglemap_wizard.php',
				'JSopenParams' => 'height=630,width=800,status=0,menubar=0,scrollbars=0',
//				'userFunc' => 'EXT:rggooglemap/class.tx_rggooglemap_wizard.php:tx_rggooglemap_wizard->renderWizard',
				'lat_field' => $this->config->fieldPrefix.$prop['__key'].'_lat',
				'lng_field' => $this->config->fieldPrefix.$prop['__key'].'_lng',
			),
		);
		$configlng['wizards'] = $configlat['wizards'];
		$proplat = $prop;
		$proplng = $prop;
		$proplat['__key'] .= '_lat';
		$proplng['__key'] .= '_lng';
		$proplat['title'] .= ' ('.tx_kbshop_abstract::sL('LLL:EXT:rggooglemap/locallang_db.xml:tt_address.tx_rggooglemap_lat').')';
		$proplng['title'] .= ' ('.tx_kbshop_abstract::sL('LLL:EXT:rggooglemap/locallang_db.xml:tt_address.tx_rggooglemap_lng').')';
		if (is_array($proplat['_LANG_ROWS']))	{
			foreach ($proplat['_LANG_ROWS'] as $idx => $lrow)	{
				$proplat['_LANG_ROWS'][$idx]['title'] .= ' ('.tx_kbshop_abstract::sL('LLL:EXT:rggooglemap/locallang_db.xml:tt_address.tx_rggooglemap_lat').')';
			}
		}
		if (is_array($proplng['_LANG_ROWS']))	{
			foreach ($proplng['_LANG_ROWS'] as $idx => $lrow)	{
				$proplng['_LANG_ROWS'][$idx]['title'] .= ' ('.tx_kbshop_abstract::sL('LLL:EXT:rggooglemap/locallang_db.xml:tt_address.tx_rggooglemap_lng').')';
			}
		}
		$retArr = array(
			array(
				'config' => $configlat,
				'prop' => $proplat,
			),
			array(
				'config' => $configlng,
				'prop' => $proplng,
			),
		);
		return $retArr;
	}

	function renderTCA__User($xmlArr, $prop)	{
		$config = array(
			'type' => 'user',
			'userFunc' => $xmlArr['field_userfunc'][$this->vDEF],
		);
		if ($wt = ($xmlArr['field_wizard_type'][$this->vDEF]))	{
			$wn = $xmlArr['field_wizard_title'][$this->vDEF];
			$wns = preg_replace('/[^a-z0-9A-Z]/', '_', $wn);
			$config['wizards'] = array(
				'_PADDING' => 1,
				'_VERTICAL' => 1,
				$wns => array(
					'type' => $wt,
					'title' => $wn,
				),
			);
			if ($wi = ($xmlArr['field_wizard_icon'][$this->vDEF]))	{
				$config['wizards'][$wns]['icon'] = '../uploads/tx_kbshop/wizardicons/'.$wi;
			}
			$config['wizards'][$wns][$wt] = $xmlArr['field_wizard_script'][$this->vDEF];
		}
		return $config;
	}

	/*
	 * TYPEADD: Add methods here if you introduce a new property-type
	 */

	function getSpecialConf($prop, $xmlArr = false)	{
		if ($prop['type']==3)	{		// RTE
			if ($xmlArr)	{
				return $xmlArr['field_editorConfig'][$this->vDEF];
			} else	{
				$xmldata = t3lib_div::xml2array($prop['flexform']);
				return $xmldata['data']['sDEF'][$this->lDEF]['field_editorConfig'][$this->vDEF];
			}
		}
	}


	/*
		Methods to overwrite in the different expressing classes
	*/
	function __addTCA_Cat(&$xml, $key, $idx, $new)	{
	}
	function __getPropCatKey($idx, $propArr)	{
	}
	function __renderTCA_Cat($idx, $sheetArr, $propArr)	{
		return $propArr;
	}
	function __postProcess($ret, $allProps)	{
	}


}


?>
