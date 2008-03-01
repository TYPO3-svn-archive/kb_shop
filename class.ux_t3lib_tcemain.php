<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Kraft Bernhard (kraftb@kraftb.at)
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

class ux_t3lib_TCEmain extends t3lib_TCEmain	{

	
	/**
	 * Processing of the sheet/language data array
	 * When it finds a field with a value the processing is done by ->checkValue_SW() by default but if a call back function name is given that method in this class will be called for the processing instead.
	 *
	 * @param	array		New values (those being processed): Multidimensional Data array for sheet/language, passed by reference!
	 * @param	array		Current values: Multidimensional Data array. May be empty array() if not needed (for callBackFunctions)
	 * @param	array		Uploaded files array for sheet/language. May be empty array() if not needed (for callBackFunctions)
	 * @param	array		Data structure which fits the data array
	 * @param	array		A set of parameters to pass through for the calling of the evaluation functions / call back function
	 * @param	string		Call back function, default is checkValue_SW(). If $this->callBackObj is set to an object, the callback function in that object is called instead.
	 * @param	[type]		$structurePath: ...
	 * @return	void
	 * @see checkValue_flex_procInData()
	 */
	function checkValue_flex_procInData_travDS(&$dataValues,$dataValues_current,$uploadedFiles,$DSelements,$pParams,$callBackFunc,$structurePath)	{
		if (is_array($DSelements))	{

				// For each DS element:
			foreach($DSelements as $key => $dsConf)	{

						// Array/Section:
				if ($DSelements[$key]['type']=='array')	{
					if (is_array($dataValues[$key]['el']))	{
						if ($DSelements[$key]['section'])	{
							foreach($dataValues[$key]['el'] as $ik => $el)	{
								$theKey = key($el);
								$cur = is_array($dataValues_current[$key]['el'][$ik][$theKey]['el'])?$dataValues_current[$key]['el'][$ik][$theKey]['el']:array();
								if (is_array($dataValues[$key]['el'][$ik][$theKey]['el']))	{
									$this->checkValue_flex_procInData_travDS(
											$dataValues[$key]['el'][$ik][$theKey]['el'],
											$cur,
											$uploadedFiles[$key]['el'][$ik][$theKey]['el'],
											$DSelements[$key]['el'][$theKey]['el'],
											$pParams,
											$callBackFunc,
											$structurePath.$key.'/el/'.$ik.'/'.$theKey.'/el/'
										);
								}
							}
						} else {
							if (!isset($dataValues[$key]['el']))	$dataValues[$key]['el']=array();
							$this->checkValue_flex_procInData_travDS(
									$dataValues[$key]['el'],
									$dataValues_current[$key]['el'],
									$uploadedFiles[$key]['el'],
									$DSelements[$key]['el'],
									$pParams,
									$callBackFunc,
									$structurePath.$key.'/el/'
								);
						}
					}
				} else {
					if (is_array($dsConf['TCEforms']['config']) && is_array($dataValues[$key]))	{
						foreach($dataValues[$key] as $vKey => $data)	{

							if ($callBackFunc)	{
								if (is_object($this->callBackObj))	{
									$res = $this->callBackObj->$callBackFunc(
												$pParams,
												$dsConf['TCEforms']['config'],
												$dataValues[$key][$vKey],
												$dataValues_current[$key][$vKey],
												$uploadedFiles[$key][$vKey],
												$structurePath.$key.'/'.$vKey.'/'
											);
								} else {
									$res = $this->$callBackFunc(
												$pParams,
												$dsConf['TCEforms']['config'],
												$dataValues[$key][$vKey],
												$dataValues_current[$key][$vKey],
												$uploadedFiles[$key][$vKey]
											);
								}
							} else {	// Default
								list($CVtable,$CVid,$CVcurValue,$CVstatus,$CVrealPid,$CVrecFID,$CVtscPID) = $pParams;

								$res = $this->checkValue_SW(
											array(),
											$dataValues[$key][$vKey],
											$dsConf['TCEforms']['config'],
											$CVtable,
											$CVid,
											$dataValues_current[$key][$vKey],
											$CVstatus,
											$CVrealPid,
											$CVrecFID,
											'',
											$uploadedFiles[$key][$vKey],
											array(),
											$CVtscPID
										);

									// Look for RTE transformation of field:
								if ($dataValues[$key]['_TRANSFORM_'.$vKey] == 'RTE' && !$this->dontProcessTransformations)	{

										// Unsetting trigger field - we absolutely don't want that into the data storage!
									unset($dataValues[$key]['_TRANSFORM_'.$vKey]);

									if (isset($res['value']))	{

											// Calculating/Retrieving some values here:
										list(,,$recFieldName) = explode(':', $CVrecFID);
										$theTypeString = t3lib_BEfunc::getTCAtypeValue($CVtable,$this->checkValue_currentRecord);
										$specConf = t3lib_BEfunc::getSpecConfParts('',$dsConf['TCEforms']['defaultExtras']);

											// Find, thisConfig:
										$RTEsetup = $this->BE_USER->getTSConfig('RTE',t3lib_BEfunc::getPagesTSconfig($CVtscPID));
										$thisConfig = t3lib_BEfunc::RTEsetup($RTEsetup['properties'],$CVtable,$recFieldName,$theTypeString);

											// Get RTE object, draw form and set flag:
										$RTEobj = &t3lib_BEfunc::RTEgetObj();
										if (is_object($RTEobj))	{
											$res['value'] = $RTEobj->transformContent('db',$res['value'],$CVtable,$recFieldName,$this->checkValue_currentRecord,$specConf,$thisConfig,'',$CVrealPid);
										} else {
											debug('NO RTE OBJECT FOUND!');
										}
									}
								}
							}

								// Adding the value:
							if (isset($res['value']))	{
								$dataValues[$key][$vKey] = $res['value'];
							}
						}
					}
				}
			}
		}
	}


}

?>
