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
 * Implementing section for normal TCA tables via external tables referencing to the record they are contained in.
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

require_once(t3lib_extMgm::extPath('kb_shop').'class.tx_kbshop_abstract.php');
require_once(t3lib_extMgm::extPath('kb_shop').'class.tx_kbshop_misc.php');
require_once(PATH_t3lib.'class.t3lib_transferdata.php');
class tx_kbshop_tcasection	{

	function getSingleField_typeSection($PA, &$pObj)	{
		$this->confObj = t3lib_div::getUserObj('EXT:kb_shop/class.tx_kbshop_config.php:&tx_kbshop_config');
		$this->confObj->init($this);	
		$this->tceforms = &$PA['pObj'];
		$this->config = $PA['fieldConf']['config'];
		$tRows = array();
		$setAllDisabled = '';
		if (is_array($this->config['el'])&&count($this->config['el']))	{
			list($elements, $sort) = $this->getElements(array_keys($this->config['el']), $PA['row']['uid']);
			$opt=array();
			$opt[]='<option value=""></option>';
			$last = end($elements);
			foreach ($this->config['el'] as $table => $tca)	{
				$hash = substr(md5(rand(1,0x7fffffff).getmypid().time()), 0, 10);
				$hid[] = '<input type="hidden" name="data['.$table.'][NEW'.$hash.'][pid]" value="'.(is_array($last)?-$last['uid']:$PA['row']['pid']).'" disabled="disabled" /><input type="hidden" name="data['.$table.'][NEW'.$hash.'][parent]" value="'.$PA['row']['uid'].'" disabled="disabled" />'.chr(10);
				$setAllDisabled .= 'if (parts[2]=="'.$PA['field'].'")	{
			document.forms[0]["data['.$table.'][NEW'.$hash.'][pid]"].disabled = 1;
			document.forms[0]["data['.$table.'][NEW'.$hash.'][parent]"].disabled = 1;
//			document.forms[0]["data['.$table.'][NEW'.$hash.'][sorting]"].disabled = 1;
		}
';
				$opt[] = '<option value="'.$hash.'|'.$table.'|'.$PA['field'].'">'.htmlspecialchars($this->tceforms->getLL('l_new').' "'.$this->tceforms->sL($tca['ctrl']['title'])).'"</option>';
						
			}
			$newEl = implode($hid).'<select name="newTCASection_'.$PA['field'].'" onchange="TBE_EDITOR_setNewTCASection(this.options[this.selectedIndex].value);" >'.implode('',$opt).'</select>';

			$tRows[]='<tr>
				<td colspan="3" width="100%" class="bgColor2">'.$newEl.'</td>
			</tr>';

			$keys = array_keys($elements);
			$cnt = 0;
			$elcount = count($elements);
			$showOrderLinks = $GLOBALS['TCA'][$table]['ctrl']['default_sortby']?false:true;
			foreach ($elements as $key => $element)	{
				$firstItem = ($cnt==0)?true:false;
				$lastItem = (($cnt+1)==$elcount)?true:false;
				$nextElement = $elements[$keys[$cnt+1]];
				$row = tx_kbshop_abstract::getRecord($element['tablename'], $element['uid']);
				if (is_array($row)&&count($row))	{
					// ???
					if (!is_array($GLOBALS['TCA'][$element['tablename']]))	{
						$GLOBALS['TCA'][$element['tablename']] = $this->config['el'][$element['tablename']];
					}
					// ---
					$prevPageID = is_object($trData)?$trData->prevPageID:'';
					$trData = t3lib_div::makeInstance('t3lib_transferData');
					$trData->addRawData = TRUE;
					$trData->lockRecords =1;
					$trData->disableRTE = $GLOBALS['SOBE']->MOD_SETTINGS['disableRTE'];
					$trData->prevPageID = $prevPageID;
					$trData->fetchRecord($element['tablename'], $row['uid'], '');	// 'new'
					reset($trData->regTableItems_data);
					$rec = current($trData->regTableItems_data);

					$item = $this->tceforms->getMainFields($element['tablename'], $rec);
					$hash = md5(rand(0, 0x7fffffff).time().getmypid());

					$tRows[] = '<tr>
						<td nowrap="nowrap" width="15" valign="top"><img src="clear.gif" width="15" height="1" alt="Clear" /></td>
						<td nowrap="nowrap" valign="top" class="bgColor5">'.
						'<input name="__SECTION_delete_'.$element['tablename'].'_'.$element['uid'].'" type="checkbox" onclick="if (confirm('.tx_kbshop_abstract::quoteJSvalue($this->tceforms->sL('LLL:EXT:kb_shop/locallang.php:label.delete')).')) {jumpToUrl('.tx_kbshop_abstract::quoteJSvalue($this->tceforms->backPath.'tce_db.php?cmd['.$element['tablename'].']['.$element['uid'].'][delete]=1&redirect=').'+T3_THIS_LOCATION+'.tx_kbshop_abstract::quoteJSvalue('&prErr=1&uPT=1').');} return false;" value="1" /><label for="'.$idTagPrefix.'-del"><img src="'.$this->backPath.'gfx/garbage.gif" border="0" alt="" /></label>'.
						((!$showOrderLinks || $firstItem)?'':('<input type="hidden" id="'.$hash.'up" name="data['.$previousElement['tablename'].']['.$previousElement['uid'].'][sorting]" value="'.$element['sorting'].'" disabled="disabled" /><input name="data['.$element['tablename'].']['.$element['uid'].'][sorting]" type="checkbox" onclick="if (confirm('.$GLOBALS['LANG']->JScharCode($this->tceforms->getLL('m_onChangeAlert')).') && TBE_EDITOR_checkSubmit(-1)){ enableMove(\''.$hash.'\', \'up\', this); TBE_EDITOR_submitForm(); return true; } else { return false; }" value="'.$previousElement['sorting'].'" /><label for="'.$idTagPrefix.'-mvup"><img src="'.$this->backPath.'gfx/button_up.gif" border="0" alt="" /></label>')).
						((!$showOrderLinks || $lastItem)?'':('<input type="hidden" id="'.$hash.'down" name="data['.$nextElement['tablename'].']['.$nextElement['uid'].'][sorting]" value="'.$element['sorting'].'" disabled="disabled" /><input name="data['.$element['tablename'].']['.$element['uid'].'][sorting]" type="checkbox" onclick="if (confirm('.$GLOBALS['LANG']->JScharCode($this->tceforms->getLL('m_onChangeAlert')).') && TBE_EDITOR_checkSubmit(-1)){ enableMove(\''.$hash.'\', \'up\', this); TBE_EDITOR_submitForm(); return true; } else { return false; }" value="'.$nextElement['sorting'].'" /><label for="'.$idTagPrefix.'-mvdown"><img src="'.$this->backPath.'gfx/button_down.gif" border="0" alt="" /></label>')).
						'</td>
						<td width="100%" class="bgColor5" style="text-align: left; font-weight: bold;">'.$this->tceforms->sL($this->config['el'][$element['tablename']]['ctrl']['title']).'</td>
					</tr>
					<tr>
						<td nowrap="nowrap" valign="top"><img src="clear.gif" width="15" height="1" alt="Clear" /></td>
						<td colspan="2"><table cellspacing="0" cellpadding="0" border="0">'.$item.'</table></td>
					</tr>';
					$previousElement = $element;
					$cnt++;
				}
			}
		}
		$PA['pObj']->additionalJS_post['tx_kbshop_tcasection'] = '

	'.$GLOBALS['SOBE']->doc->redirectUrls().'

	function enableMove(hash, dir, checkbox)	{
		var obj = document.getElementById(hash+dir);
		obj.disabled = checkbox.checked?false:true;
	}

	function TBE_EDITOR_setNewTCASection(val)	{
		var parts = val.split("|");
		'.$setAllDisabled.'
		if (!val.length) return false;
		document.forms[0]["data["+parts[1]+"][NEW"+parts[0]+"][pid]"].disabled = 0;
		document.forms[0]["data["+parts[1]+"][NEW"+parts[0]+"][parent]"].disabled = 0;
//		document.forms[0]["data["+parts[1]+"][NEW"+parts[0]+"][sorting]"].disabled = 0;
		return true;
	}

		';
		return '<table width="100%" cellspacing="0" cellpadding="3" border="0">'.chr(10).implode(chr(10), $tRows).chr(10).'</table>'.chr(10);
	}


	function getElements($tables, $parent)	{
		$this->sanitizeSorting($tables, $parent);
		$query = $this->sectionElementsQuery($tables, $parent);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		$ret = array();
		$sort = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$ret[] = $row;
			if ($sort[$row['tablename']]<$row['sorting'])	{
				$sort[$row['tablename']] = $row['sorting'];
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return array($ret, $sort);
	}

	function sanitizeSorting($tables, $parent)	{
		$saneCnt = 200;
		do	{
			$updated = false;
			$query = $this->sectionElementsQuery($tables, $parent, true);
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			$oldSorting = -1;
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				if ($row['sorting']==$oldSorting)	{
					$fields = array(
						'sorting' => $row['sorting']+1,
					);
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery($row['tablename'], 'uid='.$row['uid'], $fields);
					$oldSorting = $row['sorting']+1;
					$updated = true;
				} else	{
					$oldSorting = $row['sorting'];
				}
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		} while ($updated && ($saneCnt--));
	}
		
	function sectionElementsQuery($tables, $parent, $overrideSorting = false)	{
		$queries = array();
		$internalSort = false;
		foreach ($tables as $table)	{
			if (!$overrideSorting)	{
				list($sf) = t3lib_div::trimExplode(',', tx_kbshop_abstract::getSortFields($table), 1);
				list($o_join, $o_where, $o_table) = tx_kbshop_misc::getSectionOrder($table, $sf, true);
				list($sf) = t3lib_div::trimExplode(',', tx_kbshop_abstract::getSortFields($o_table), 1);
			}
			if (!$overrideSorting && (($o_table!==$table)||($sf!=='sorting')))	{
				$internalSort = true;
			} else	{
				$o_table = $table;
				$sf = 'sorting';
				$sortField .= $table.'.sorting AS sorting, ';
			}
			$queries[] = 'SELECT '.$table.'.uid as uid,'.$sortField.' \''.$table.'\' as tablename FROM '.$table.$o_join.' WHERE '.$table.'.parent=\''.$parent.'\''.($o_where?(' AND '.$o_where):'').t3lib_BEfunc::deleteClause($table).($internalSort?(' ORDER BY '.$o_table.'.'.$sf):'');
		}
		if (count($queries)>1)	{
			$query = '('.implode(') UNION ALL (', $queries).')';
		} else	{
			$query = array_pop($queries);
		}
		if (!$internalSort)	{
			$query .= ' ORDER BY sorting;';
		}
		return $query;
	}
	
}


?>
