<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2007 Kraft Bernhard (kraftb@kraftb.at)
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
 * Extending t3lib_tceforms
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

class ux_t3lib_TCEforms extends t3lib_TCEforms	{
	var $minInputWidth = 1;
	var $FE_RTE = 1;


	/**
	 * Returns true, if the evaluation of the required-field code is OK.
	 *
	 * @param	string		The required-field code
	 * @param	array		The record to evaluate
	 * @return	boolean
	 */
	function isDisplayCondition($displayCond,$row,$ffValueKey='')	{
		$output = FALSE;

		$orparts = t3lib_div::trimExplode('||', $displayCond, 1);
		foreach ($orparts as $orpart)	{
			$andparts = t3lib_div::trimExplode('&&', trim($orpart), 1);
			$andout = true;
			foreach ($andparts as $part)	{
				$part = trim($part);
				if (strpos(strtoupper($part), ':CONTAINS:')!==false)	{
					$parts = explode(':',$part);
					switch(strtoupper($parts[0]))	{	// Type of condition:
						case 'FIELD':
							$theFieldValue = $ffValueKey ? $row[$parts[1]][$ffValueKey] : $row[$parts[1]];
							switch((string)$parts[2])	{
								case 'CONTAINS':
									if (!preg_match('/(^|,)'.preg_quote($parts[3], '/').'($|,|\|)/', $theFieldValue))	{
										$andout = 0;
									}
								break;
								default:
									$andout = 0;
								break;
							}
						break;
						default:
							$andout = 0;
						break;
					}
				} else	{
					$andout &= parent::isDisplayCondition($part, $row, $ffValueKey);
				}
			}
			$output |= $andout;
		}
		return $output;
	}


	/**
	 * Generation of TCEform elements of the type "input"
	 * This will render a single-line input form field, possibly with various control/validation features
	 *
	 * @param	string		The table name of the record
	 * @param	string		The field name which this element is supposed to edit
	 * @param	array		The record data array where the value(s) for the field can be found
	 * @param	array		An array with additional configuration options.
	 * @return	string		The HTML code for the TCEform field
	 */
	function getSingleField_typeInput($table,$field,$row,&$PA)	{
		// typo3FormFieldSet(theField, evallist, is_in, checkbox, checkboxValue)
		// typo3FormFieldGet(theField, evallist, is_in, checkbox, checkboxValue, checkbox_off)

		$config = $PA['fieldConf']['config'];

#		$specConf = $this->getSpecConfForField($table,$row,$field);
		$specConf = $this->getSpecConfFromString($PA['extra'], $PA['fieldConf']['defaultExtras']);
		$size = t3lib_div::intInRange($config['size']?$config['size']:30,$this->minInputWidth, $this->maxInputWidth);
		$evalList = t3lib_div::trimExplode(',',$config['eval'],1);


		if($this->renderReadonly || $config['readOnly'])  {
			$itemFormElValue = $PA['itemFormElValue'];
			if (in_array('date',$evalList))	{
				$config['format'] = 'date';
			} elseif (in_array('date',$evalList))	{
				$config['format'] = 'date';
			} elseif (in_array('datetime',$evalList))	{
				$config['format'] = 'datetime';
			} elseif (in_array('time',$evalList))	{
				$config['format'] = 'time';
			}
			if (in_array('password',$evalList))	{
				$itemFormElValue = $itemFormElValue ? '*********' : '';
			}
			return $this->getSingleField_typeNone_render($config, $itemFormElValue,$table,$field,$row,$PA);
		}

		if (in_array('required',$evalList))	{
			$this->requiredFields[$table.'_'.$row['uid'].'_'.$field]=$PA['itemFormElName'];
		}

		$paramsList = "'".$PA['itemFormElName']."','".implode(',',$evalList)."','".trim($config['is_in'])."',".(isset($config['checkbox'])?1:0).",'".$config['checkbox']."'";
		if (isset($config['checkbox']))	{
				// Setting default "click-checkbox" values for eval types "date" and "datetime":
			$thisMidnight = mktime(0,0,0);
			$checkSetValue = in_array('date',$evalList) ? $thisMidnight : '';
			$checkSetValue = in_array('datetime',$evalList) ? time() : $checkSetValue;

			$cOnClick = 'typo3FormFieldGet('.$paramsList.',1,\''.$checkSetValue.'\');'.implode('',$PA['fieldChangeFunc']);
			$item.='<input type="checkbox"'.$this->insertDefStyle('check').' name="'.$PA['itemFormElName'].'_cb" onclick="'.htmlspecialchars($cOnClick).'" />';
		}

		$PA['fieldChangeFunc'] = array_merge(array('typo3FormFieldGet'=>'typo3FormFieldGet('.$paramsList.');'), $PA['fieldChangeFunc']);
		$mLgd = ($config['max']?$config['max']:256);
		//$iOnChange = implode('',$PA['fieldChangeFunc']);
		$item.='<input type="text" name="'.$PA['itemFormElName'].'" value="'.htmlspecialchars($PA['itemFormElValue']).'"'.$this->formWidth($size).' maxlength="'.$mLgd.'" onchange="'.htmlspecialchars($iOnChange).'"'.$PA['onFocus'].' class="'.preg_replace('/[\[\]]/', '_', $PA['itemFormElName']).'" />';	// This is the EDITABLE form field.
		//$item.='<input type="hidden" name="'.$PA['itemFormElName'].'" value="'.htmlspecialchars($PA['itemFormElValue']).'" />';			// This is the ACTUAL form field - values from the EDITABLE field must be transferred to this field which is the one that is written to the database.
		//$this->extJSCODE.='typo3FormFieldSet('.$paramsList.');';

			// going through all custom evaluations configured for this field
		foreach ($evalList as $evalData) {
			if (substr($evalData, 0, 3) == 'tx_')	{
				$evalObj = t3lib_div::getUserObj($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][$evalData].':&'.$evalData);
				if(is_object($evalObj) && method_exists($evalObj, 'returnFieldJS'))	{
					$this->extJSCODE .= "\n\nfunction ".$evalData."(value) {\n".$evalObj->returnFieldJS()."\n}\n";
				}
			}
		}

			// Creating an alternative item without the JavaScript handlers.
		$altItem = '<input type="hidden" name="'.$PA['itemFormElName'].'_hr" value="" />';
		$altItem.= '<input type="hidden" name="'.$PA['itemFormElName'].'" value="'.htmlspecialchars($PA['itemFormElValue']).'" />';

			// Wrap a wizard around the item?
		$item= $this->renderWizards(array($item,$altItem),$config['wizards'],$table,$row,$field,$PA,$PA['itemFormElName'].'_hr',$specConf);

		return $item;
	}


	/**
	 * Constructor function, setting internal variables, loading the styles used.
	 *
	 * @return	void
	 */
	function ux_t3lib_TCEforms()	{
		global $CLIENT, $TYPO3_CONF_VARS;

		$this->clientInfo = t3lib_div::clientInfo();

		if ($GLOBALS['BE_USER'])	{
			$this->RTEenabled = $GLOBALS['BE_USER']->isRTE();
		} else	{
			$this->RTEenabled = $this->FE_RTE;
		}
		if (!$this->RTEenabled)	{
			$this->RTEenabled_notReasons = implode(chr(10),$GLOBALS['BE_USER']->RTE_errors);
			$this->commentMessages[] = 'RTE NOT ENABLED IN SYSTEM due to:'.chr(10).$this->RTEenabled_notReasons;
		}

			// Default color+class scheme
		$this->defColorScheme = array(
			$GLOBALS['SOBE']->doc->bgColor,	// Background for the field AND palette
			t3lib_div::modifyHTMLColorAll($GLOBALS['SOBE']->doc->bgColor,-20),	// Background for the field header
			t3lib_div::modifyHTMLColorAll($GLOBALS['SOBE']->doc->bgColor,-10),	// Background for the palette field header
			'black',	// Field header font color
			'#666666'	// Palette field header font color
		);
		$this->defColorScheme = array();

			// Override / Setting defaults from TBE_STYLES array
		$this->resetSchemes();

			// Setting the current colorScheme to default.
		$this->defColorScheme = $this->colorScheme;
		$this->defClassScheme = $this->classScheme;

 			// Prepare user defined objects (if any) for hooks which extend this function:
 		$this->hookObjectsMainFields = array();
 		if (is_array ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass']))	{
 			foreach ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'] as $classRef)	{
 				$this->hookObjectsMainFields[] = &t3lib_div::getUserObj($classRef);
 			}
 		}
 		$this->hookObjectsSingleField = array();
 		if (is_array ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getSingleFieldClass']))	{
 			foreach ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getSingleFieldClass'] as $classRef)	{
 				$this->hookObjectsSingleField[] = &t3lib_div::getUserObj($classRef);
 			}
 		}
		$this->inline = t3lib_div::makeInstance('t3lib_TCEforms_inline');
	}
	
	/**
	 * Adds records from a foreign table (for selector boxes)
	 *
	 * @param	array		The array of items (label,value,icon)
	 * @param	array		The 'columns' array for the field (from TCA)
	 * @param	array		TSconfig for the table/row
	 * @param	string		The fieldname
	 * @param	boolean		If set, then we are fetching the 'neg_' foreign tables.
	 * @return	array		The $items array modified.
	 * @see addSelectOptionsToItemArray(), t3lib_BEfunc::exec_foreign_table_where_query()
	 */
	function foreignTable($items,$fieldValue,$TSconfig,$field,$pFFlag=0)	{
		global $TCA;

			// Init:
		$pF=$pFFlag?'neg_':'';
		$f_table = $fieldValue['config'][$pF.'foreign_table'];
		$uidPre = $pFFlag?'-':'';

			// Get query:
		$res = t3lib_BEfunc::exec_foreign_table_where_query($fieldValue,$field,$TSconfig,$pF);

			// Perform lookup
		if ($GLOBALS['TYPO3_DB']->sql_error())	{
			echo($GLOBALS['TYPO3_DB']->sql_error()."\n\nThis may indicate a table defined in tables.php is not existing in the database!");
			return array();
		}

			// Get label prefix.
		$lPrefix = $this->sL($fieldValue['config'][$pF.'foreign_table_prefix']);

			// Get icon field + path if any:
		$iField = $TCA[$f_table]['ctrl']['selicon_field'];
		$iPath = trim($TCA[$f_table]['ctrl']['selicon_field_path']);

			// Traverse the selected rows to add them:
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			t3lib_BEfunc::workspaceOL($f_table, $row);
				// Prepare the icon if available:
			if ($iField && $iPath && $row[$iField])	{
				$iParts = t3lib_div::trimExplode(',',$row[$iField],1);
				$icon = '../'.$iPath.'/'.trim($iParts[0]);
			} elseif (t3lib_div::inList('singlebox,checkbox',$fieldValue['config']['renderMode'])) {
				$icon = '../'.TYPO3_mainDir.t3lib_iconWorks::skinImg($this->backPath,t3lib_iconWorks::getIcon($f_table, $row),'',1);
			} else $icon = '';

				// Add the item:
//			$title = t3lib_BEfunc::getRecordTitle($f_table, $row);
//			$tCol = $GLOBALS['TCA'][$f_table]['ctrl']['label'];
//			$title = t3lib_BEfunc::getProcessedValueExtra($f_table, $tCol, $row[$tCol], $this->titleLen, $row['uid']);
			$title = t3lib_div::fixed_lgd_cs($lPrefix.strip_tags(t3lib_BEfunc::getRecordTitle($f_table,$row)),$this->titleLen);
			$items[] = array(
				$title,
				$uidPre.$row['uid'],
				$icon
			);
		}
		return $items;
	}




}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kb_shop/class.ux_t3lib_tceforms.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kb_shop/class.ux_t3lib_tceforms.php']);
}

?>
