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
 * Extending tslib_content
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */


class ux_tslib_cObj extends tslib_cObj	{

	/**
	 * Calling a user function/class-method
	 * Notice: For classes the instantiated object will have the internal variable, $cObj, set to be a *reference* to $this (the parent/calling object).
	 *
	 * @param	string		The functionname, eg "user_myfunction" or "user_myclass->main". Notice that there are rules for the names of functions/classes you can instantiate. If a function cannot be called for some reason it will be seen in the TypoScript log in the AdminPanel.
	 * @param	array		The TypoScript configuration to pass the function
	 * @param	string		The content string to pass the function
	 * @return	string		The return content from the function call. Should probably be a string.
	 * @see USER(), stdWrap(), typoLink(), _parseFunc()
	 */
	function callUserFunction($funcName,$conf,$content)	{
		$conf['_pObj'] = &$this;
		return parent::callUserFunction($funcName, $conf, $content);
	}

	/**
	 * Performs basic mathematical evaluation of the input string. Does NOT take parathesis and operator precedence into account! (for that, see t3lib_div::calcPriority())
	 *
	 * @param	string		The string to evaluate. Example: "3+4*10/5" will generate "35". Only integer numbers can be used.
	 * @return	integer		The result (might be a float if you did a division of the numbers).
	 * @see t3lib_div::calcPriority()
	 */
	function calc($val)	{
		$parts= t3lib_div::splitCalc($val,'+-*/');
		$value=0;
		reset($parts);
		while(list(,$part)=each($parts))	{
			$theVal = $part[1];
			$sign =  $part[0];
			if ((string)intval($theVal)==(string)$theVal)	{
				$theVal = intval($theVal);
			} else {
				$theVal =0;
			}
			if ($sign=='-')	{$value-=$theVal;}
			if ($sign=='+')	{$value+=$theVal;}
			if ($sign=='/')	{if (intval($theVal)) $value/=intval($theVal);}
			if ($sign=='*')	{$value*=$theVal;}
			if ($sign=='%')	{if (intval($theVal)) $value= $value%$theVal;}
		}
		return $value;
	}


	/**
	 * The "stdWrap" function. This is the implementation of what is known as "stdWrap properties" in TypoScript.
	 * Basically "stdWrap" performs some processing of a value based on properties in the input $conf array (holding the TypoScript "stdWrap properties")
	 * See the link below for a complete list of properties and what they do. The order of the table with properties found in TSref (the link) follows the actual order of implementation in this function.
	 *
	 * If $this->alternativeData is an array it's used instead of the $this->data array in ->getData
	 *
	 * @param	string		Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
	 * @param	array		TypoScript "stdWrap properties".
	 * @return	string		The processed input value
	 * @link http://typo3.org/doc.0.html?&tx_extrepmgm_pi1[extUid]=270&tx_extrepmgm_pi1[tocEl]=314&cHash=02ab044c7b
	 */
	function stdWrap($content,$conf)	{
		if (is_array($conf))	{

				// Setting current value, if so
			if ($conf['setContentToCurrent']){$this->data[$this->currentValKey]=$content;}
			if ($conf['setCurrent'] || $conf['setCurrent.']){$this->data[$this->currentValKey] = $this->stdWrap($conf['setCurrent'], $conf['setCurrent.']);}

				// Getting data:
			if (isset($conf['lang.']) && $GLOBALS['TSFE']->config['config']['language'] && isset($conf['lang.'][$GLOBALS['TSFE']->config['config']['language']]))	{
				$content = $conf['lang.'][$GLOBALS['TSFE']->config['config']['language']];
			}
			if ($conf['parentFields.']) {
					// Save contents
				$d = $this->data;
				$pR = $this->parentRecord;
					// Set new contents
				$this->data = $this->parentRecord['data']; 
				$this->parentRecord = $this->parentRecord['parentRecord'];
					// Call stdWrap recursively.
				$content = $this->stdWrap($content, $conf['parentFields.']);
					// Restore previous contents
				$this->parentRecord = $pR;
				$this->data = $d;
			}
			if ($conf['data']){$content=$this->getData($conf['data'], is_array($this->alternativeData)?$this->alternativeData:$this->data);}
				$this->alternativeData='';		// This must be unset directly after
			if ($conf['field']) {$content=$this->getFieldVal($conf['field']);}
			if ($conf['current']) {$content=$this->data[$this->currentValKey];}
			if ($conf['cObject']) {$content=$this->cObjGetSingle($conf['cObject'],$conf['cObject.'],'/stdWrap/.cObject');}
			if ($conf['numRows.']) {$content=$this->numRows($conf['numRows.']);}
			if ($conf['filelist'] || $conf['filelist.'])	{$content=$this->filelist($this->stdWrap($conf['filelist'], $conf['filelist.']));}
			if ($conf['preUserFunc'])	{$content = $this->callUserFunction($conf['preUserFunc'], $conf['preUserFunc.'], $content);}

				// Overriding values, evaluating conditions
			if ($conf['override'] || $conf['override.']){
				$override = $this->stdWrap($conf['override'], $conf['override.']);
				if (trim($override)) {$content=$override;}
			}
			if (isset($conf['preIfEmptyListNum']) || isset($conf['preIfEmptyListNum.']['stdWrap.'])) {
				$preIfEmptyListNumber = isset($conf['preIfEmptyListNum.']['stdWrap.'])  ?  $this->stdWrap($conf['preIfEmptyListNum'], $conf['preIfEmptyListNum.']['stdWrap.'])  :  $conf['preIfEmptyListNum'];
				$content=$this->listNum($content,$preIfEmptyListNumber,$conf['preIfEmptyListNum.']['splitChar']);
			}
			if (!trim($content) && ($conf['ifEmpty'] || $conf['ifEmpty.']))	{
				$content = $this->stdWrap($conf['ifEmpty'], $conf['ifEmpty.']);
			}
			if (!strlen(trim($content)) && ($conf['ifBlank'] || $conf['ifBlank.']))	{
				$content = $this->stdWrap($conf['ifBlank'], $conf['ifBlank.']);
			}

				// values...
			if (isset($conf['listNum']) || isset($conf['listNum.']['stdWrap.'])) {
				$listNumber = isset($conf['listNum.']['stdWrap.'])  ?  $this->stdWrap($conf['listNum'], $conf['listNum.']['stdWrap.'])  :  $conf['listNum'];
				$content=$this->listNum($content,$listNumber,$conf['listNum.']['splitChar']);
			}

			if ($conf['trim'])	{ $content=trim($content); }

				// Call stdWrap recursively
			if ($conf['stdWrap'])	{ $content=$this->stdWrap($content,$conf['stdWrap.']); }
			if (   ($conf['required'] && (string)$content=='') || ($conf['if.'] && !$this->checkIf($conf['if.'])) || ($conf['fieldRequired'] && !trim($this->data[$conf['fieldRequired']]) || ($conf['validEmail']) && !t3lib_div::validEmail($content) )    ){
				$content = '';
			} else	{
					// Perform data processing:
				if ($conf['csConv'])	{ $content=$GLOBALS['TSFE']->csConv($content,$conf['csConv']); }
				if ($conf['parseFunc.'] || $conf['parseFunc']) {$content=$this->parseFunc($content,$conf['parseFunc.'],$conf['parseFunc']);}
				if ($conf['HTMLparser'] && is_array($conf['HTMLparser.'])) {$content=$this->HTMLparser_TSbridge($content,$conf['HTMLparser.']);}
				if ($conf['split.']){$content=$this->splitObj($content,$conf['split.']);}
				if ($conf['prioriCalc']){$content=t3lib_div::calcParenthesis($content); if ($conf['prioriCalc']=='intval') $content=intval($content);}
				if ((string)$conf['char']!=''){$content=chr(intval($conf['char']));}
				if ($conf['intval']){$content=intval($content);}
				if ($conf['date']){$content=date($conf['date'], $content);}
				if ($conf['strftime']){
					$content = strftime($conf['strftime'], $content);
					$tmp_charset = $conf['strftime.']['charset'] ? $conf['strftime.']['charset'] : $GLOBALS['TSFE']->localeCharset;
					if ($tmp_charset)	{
						$content = $GLOBALS['TSFE']->csConv($content,$tmp_charset);
					}
				}
				if ($conf['age']){$content=$this->calcAge(time()-$content,$conf['age']);}

				if ($conf['case']){$content=$this->HTMLcaseshift($content, $conf['case']);}
				if ($conf['bytes']){$content=$this->bytes($content,$conf['bytes.']['labels']);}
				if ($conf['substring']){$content=$this->substring($content,$conf['substring']);}
				if ($conf['removeBadHTML'])	{$content = $this->removeBadHTML($content, $conf['removeBadHTML.']);}
				if ($conf['stripHtml']){$content = strip_tags($content);}
				if ($conf['crop']){$content=$this->crop($content, $conf['crop']);}
				if ($conf['sprintf']||$conf['sprintf.']){$content=sprintf($this->stdWrap($conf['sprintf'], $conf['sprintf.']), $content);}
				if ($conf['rawUrlEncode']){$content = rawurlencode($content);}
				if ($conf['htmlSpecialChars']){
					$content=htmlSpecialChars($content);
					if ($conf['htmlSpecialChars.']['preserveEntities'])	$content = t3lib_div::deHSCentities($content);
				}

				if ($conf['doubleBrTag']) {
					$content=ereg_replace("\r?\n[\t ]*\r?\n",$conf['doubleBrTag'],$content);
				}
				if ($conf['br']) {$content=nl2br($content);}
				if ($conf['brTag']) {$content= ereg_replace(chr(10),$conf['brTag'],$content);}
				if ($conf['pregReplace.'])	{
					$pattern = $this->stdWrap($conf['pregReplace.']['pattern'], $conf['pregReplace.']['pattern.']);
					$replacement = $this->stdWrap($conf['pregReplace.']['replacement'], $conf['pregReplace.']['replacement.']);
					$content = preg_replace($pattern, $replacement, $content);
				}
				if ($conf['encapsLines.']) {$content=$this->encaps_lineSplit($content,$conf['encapsLines.']);}
				if ($conf['keywords']) {$content= $this->keywords($content);}
				if ($conf['innerWrap'] || $conf['innerWrap.']){$content=$this->wrap($content, $this->stdWrap($conf['innerWrap'], $conf['innerWrap.']));}
				if ($conf['innerWrap2'] || $conf['innerWrap2.']){$content=$this->wrap($content, $this->stdWrap($conf['innerWrap2'], $conf['innerWrap2.']));}
				if ($conf['fontTag']){$content=$this->wrap($content, $conf['fontTag']);}
				if ($conf['addParams.']) {$content=$this->addParams($content,$conf['addParams.']);}
				if ($conf['textStyle.']) {$content=$this->textStyle($content,$conf['textStyle.']);}
				if ($conf['tableStyle.']) {$content=$this->tableStyle($content,$conf['tableStyle.']);}
				if ($conf['filelink.']) {$content=$this->filelink($content,$conf['filelink.']);}
				if ($conf['preCObject']) {$content=$this->cObjGetSingle($conf['preCObject'],$conf['preCObject.'],'/stdWrap/.preCObject').$content;}
				if ($conf['postCObject']) {$content.=$this->cObjGetSingle($conf['postCObject'],$conf['postCObject.'],'/stdWrap/.postCObject');}

				if ($conf['wrapAlign'] || $conf['wrapAlign.']){
					$wrapAlign = trim($this->stdWrap($conf['wrapAlign'], $conf['wrapAlign.']));
					if ($wrapAlign)	{$content=$this->wrap($content, '<div style="text-align:'.$wrapAlign.';">|</div>');}
				}
				if ($conf['typolink.']){$content=$this->typolink($content, $conf['typolink.']);}
				if (is_array($conf['TCAselectItem.'])) {$content=$this->TCAlookup($content,$conf['TCAselectItem.']);}

					// Spacing
				if ($conf['space']){$content=$this->wrapSpace($content, $conf['space']);}
				$spaceBefore = '';
				if ($conf['spaceBefore'] || $conf['spaceBefore.'])	{$spaceBefore = trim($this->stdWrap($conf['spaceBefore'], $conf['spaceBefore.']));}
				$spaceAfter = '';
				if ($conf['spaceAfter'] || $conf['spaceAfter.'])	{$spaceAfter = trim($this->stdWrap($conf['spaceAfter'], $conf['spaceAfter.']));}
				if ($spaceBefore || $spaceAfter)	{$content=$this->wrapSpace($content, $spaceBefore.'|'.$spaceAfter);}

					// Wraps
				if ($conf['wrap']){$content=$this->wrap($content, $conf['wrap'], ($conf['wrap.']['splitChar']?$conf['wrap.']['splitChar']:'|'));}
				if ($conf['noTrimWrap']){$content=$this->noTrimWrap($content, $conf['noTrimWrap']);}
				if ($conf['wrap2']){$content=$this->wrap($content, $conf['wrap2'], ($conf['wrap2.']['splitChar']?$conf['wrap2.']['splitChar']:'|'));}
				if ($conf['dataWrap']){$content=$this->dataWrap($content, $conf['dataWrap']);}
				if ($conf['prepend']){$content=$this->cObjGetSingle($conf['prepend'],$conf['prepend.'],'/stdWrap/.prepend').$content;}
				if ($conf['append']){$content.=$this->cObjGetSingle($conf['append'],$conf['append.'],'/stdWrap/.append');}
				if ($conf['wrap3']){$content=$this->wrap($content, $conf['wrap3'], ($conf['wrap3.']['splitChar']?$conf['wrap3.']['splitChar']:'|'));}
				if ($conf['outerWrap'] || $conf['outerWrap.']){$content=$this->wrap($content, $this->stdWrap($conf['outerWrap'], $conf['outerWrap.']));}
				if ($conf['insertData'])	{$content = $this->insertData($content);}
				if ($conf['offsetWrap']){
					$controlTable = t3lib_div::makeInstance('tslib_tableOffset');
					if ($conf['offsetWrap.']['tableParams'] || $conf['offsetWrap.']['tableParams.'])	{$controlTable->tableParams = $this->stdWrap($conf['offsetWrap.']['tableParams'], $conf['offsetWrap.']['tableParams.']);}
					if ($conf['offsetWrap.']['tdParams'] || $conf['offsetWrap.']['tdParams.'])	{$controlTable->tdParams = ' '.$this->stdWrap($conf['offsetWrap.']['tdParams'], $conf['offsetWrap.']['tdParams.']);}
					$content=$controlTable->start($content,$conf['offsetWrap']);
					if ($conf['offsetWrap.']['stdWrap.'])	{	$content=$this->stdWrap($content,$conf['offsetWrap.']['stdWrap.']);	}
				}
				if ($conf['postUserFunc'])	{$content = $this->callUserFunction($conf['postUserFunc'], $conf['postUserFunc.'], $content);}
				if ($conf['postUserFuncInt'])	{
					$substKey = 'INT_SCRIPT.'.$GLOBALS['TSFE']->uniqueHash();
					$GLOBALS['TSFE']->config['INTincScript'][$substKey] = array(
						'content' => $content,
						'postUserFunc' => $conf['postUserFuncInt'],
						'conf' => $conf['postUserFuncInt.'],
						'type' => 'POSTUSERFUNC',
						'cObj' => serialize($this),
					);
					$content ='<!--'.$substKey.'-->';
				}
					// Various:
				if ($conf['prefixComment'] && !$GLOBALS['TSFE']->config['config']['disablePrefixComment'])	{$content = $this->prefixComment($conf['prefixComment'], $conf['prefixComment.'], $content);}

				if ($conf['editIcons'] && $GLOBALS['TSFE']->beUserLogin){$content=$this->editIcons($content,$conf['editIcons'],$conf['editIcons.']);}
				if ($conf['editPanel'] && $GLOBALS['TSFE']->beUserLogin){$content=$this->editPanel($content, $conf['editPanel.']);}
			}

				//Debug:
			if ($conf['debug'])	{$content = '<pre>'.htmlspecialchars($content).'</pre>';}
			if ($conf['debugFunc'])	{debug($conf['debugFunc']==2?array($content):$content);}
			if ($conf['debugData'])	{
				echo '<b>$cObj->data:</b>';
				t3lib_div::debug($this->data,'$cObj->data:');
				if (is_array($this->alternativeData))	{
					echo '<b>$cObj->alternativeData:</b>';
					debug($this->alternativeData,'$this->alternativeData');
				}
			}
		}
		return $content;
	}



	/**
	 * Rendering the cObject, CONTENT
	 *
	 * @param	array		Array of TypoScript properties
	 * @return	string		Output
	 * @link http://typo3.org/doc.0.html?&tx_extrepmgm_pi1[extUid]=270&tx_extrepmgm_pi1[tocEl]=356&cHash=9f3b5c6ba2
	 */
	function CONTENT($conf)	{
		$theValue='';

		$originalRec = $GLOBALS['TSFE']->currentRecord;
		if ($originalRec)	{		// If the currentRecord is set, we register, that this record has invoked this function. It's should not be allowed to do this again then!!
			$GLOBALS['TSFE']->recordRegister[$originalRec]++;
		}

		if ($conf['table']=='pages' || substr($conf['table'],0,3)=='tt_' || substr($conf['table'],0,3)=='fe_' || substr($conf['table'],0,3)=='tx_' || substr($conf['table'],0,4)=='ttx_' || substr($conf['table'],0,5)=='user_')	{

			$renderObjName = $conf['renderObj'] ? $conf['renderObj'] : '<'.$conf['table'];
			$renderObjKey = $conf['renderObj'] ? 'renderObj' : '';
			$renderObjConf = $conf['renderObj.'];

			$slide = intval($conf['slide'])?intval($conf['slide']):0;
			$slideCollect = intval($conf['slide.']['collect'])?intval($conf['slide.']['collect']):0;
			$slideCollectReverse = intval($conf['slide.']['collectReverse'])?true:false;
			$slideCollectFuzzy = $slideCollect?(intval($conf['slide.']['collectFuzzy'])?true:false):true;
			$again = false;

			$confmd5 = md5($renderObjName.':'.serialize($renderObjConf));

			do {
				$res = $this->exec_getQuery($conf['table'],$conf['select.']);
				if ($error = $GLOBALS['TYPO3_DB']->sql_error()) {
					$GLOBALS['TT']->setTSlogMessage($error,3);
				} else {
					$this->currentRecordTotal = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
					$GLOBALS['TT']->setTSlogMessage('NUMROWS: '.$GLOBALS['TYPO3_DB']->sql_num_rows($res));
					$cObj =t3lib_div::makeInstance('tslib_cObj');
					$cObj->setParent($this->data,$this->currentRecord);
					$this->currentRecordNumber=0;
					$cobjValue = '';
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

							// Versioning preview:
						$GLOBALS['TSFE']->sys_page->versionOL($conf['table'],$row);

							// Language Overlay:
						if (is_array($row) && $GLOBALS['TSFE']->sys_language_contentOL) {
							$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay($conf['table'],$row,$GLOBALS['TSFE']->sys_language_content,$GLOBALS['TSFE']->sys_language_contentOL);
						}

						if (is_array($row)) { // Might be unset in the sys_language_contentOL
							if (!$GLOBALS['TSFE']->recordRegister[$conf['table'].':'.$row['uid']].':'.$confmd5) {
								$this->currentRecordNumber++;
								$cObj->parentRecordNumber = $this->currentRecordNumber;
								$GLOBALS['TSFE']->currentRecord = $conf['table'].':'.$row['uid'].':'.$confmd5;
								$this->lastChanged($row['tstamp']);
								$cObj->start($row,$conf['table']);
								$tmpValue = $cObj->cObjGetSingle($renderObjName, $renderObjConf, $renderObjKey);
								$cobjValue .= $tmpValue;
							}# else debug($GLOBALS['TSFE']->recordRegister,'CONTENT');
						}
					}
				}
				if ($slideCollectReverse) {
					$theValue = $cobjValue.$theValue;
				} else {
					$theValue .= $cobjValue;
				}
				if ($slideCollect>0) {
					$slideCollect--;
				}
				if ($slide) {
					if ($slide>0) {
						$slide--;
					}
					$conf['select.']['pidInList'] = $this->getSlidePids($conf['select.']['pidInList'], $conf['select.']['pidInList.']);
					$again = strlen($conf['select.']['pidInList'])?true:false;
				}
			} while ($again&&(($slide&&!strlen($tmpValue)&&$slideCollectFuzzy)||($slide&&$slideCollect)));
		}

		$theValue = $this->wrap($theValue,$conf['wrap']);
		if ($conf['stdWrap.']) $theValue = $this->stdWrap($theValue,$conf['stdWrap.']);

		$GLOBALS['TSFE']->currentRecord = $originalRec;	// Restore
		return $theValue;
	}

	/**
	 * Rendering the cObject, RECORDS
	 *
	 * @param	array		Array of TypoScript properties
	 * @return	string		Output
	 * @link http://typo3.org/doc.0.html?&tx_extrepmgm_pi1[extUid]=270&tx_extrepmgm_pi1[tocEl]=357&cHash=303e959472
	 */
	function RECORDS($conf)	{
		$theValue='';

		$originalRec = $GLOBALS['TSFE']->currentRecord;
		if ($originalRec)	{		// If the currentRecord is set, we register, that this record has invoked this function. It's should not be allowed to do this again then!!
			$GLOBALS['TSFE']->recordRegister[$originalRec]++;
		}

		$conf['source'] = $this->stdWrap($conf['source'],$conf['source.']);
		if ($conf['tables'] && $conf['source']) {
			$allowedTables = $conf['tables'];
			if (is_array($conf['conf.']))	{
				reset($conf['conf.']);
				while(list($k)=each($conf['conf.']))	{
					if (substr($k,-1)!='.')		$allowedTables.=','.$k;
				}
			}

			$loadDB = t3lib_div::makeInstance('FE_loadDBGroup');
			$loadDB->start($conf['source'], $allowedTables);
			reset($loadDB->tableArray);
			while(list($table,)=each($loadDB->tableArray))	{
				if (is_array($GLOBALS['TCA'][$table]))	{
					$loadDB->additionalWhere[$table]=$this->enableFields($table);
				}
			}
			$loadDB->getFromDB();

			reset($loadDB->itemArray);
			$data = $loadDB->results;

			$cObj =t3lib_div::makeInstance('tslib_cObj');
			$cObj->setParent($this->data,$this->currentRecord);
			$this->currentRecordNumber=0;
			$this->currentRecordTotal = count($loadDB->itemArray);
			reset($loadDB->itemArray);
			$confmd5 = md5($renderObjName.':'.serialize($renderObjConf));
			while(list(,$val)=each($loadDB->itemArray))	{
				$row = $data[$val['table']][$val['id']];

					// Versioning preview:
				$GLOBALS['TSFE']->sys_page->versionOL($val['table'],$row);

					// Language Overlay:
				if (is_array($row) && $GLOBALS['TSFE']->sys_language_contentOL)	{
					$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay($val['table'],$row,$GLOBALS['TSFE']->sys_language_content,$GLOBALS['TSFE']->sys_language_contentOL);
				}

				if (is_array($row))	{	// Might be unset in the content overlay things...
					if (!$conf['dontCheckPid'])	{
						$row = $this->checkPid($row['pid']) ? $row : '';
					}
					if ($row && !$GLOBALS['TSFE']->recordRegister[$val['table'].':'.$val['id']].':'.$confmd5)	{
						$renderObjName = $conf['conf.'][$val['table']] ? $conf['conf.'][$val['table']] : '<'.$val['table'];
						$renderObjKey = $conf['conf.'][$val['table']] ? 'conf.'.$val['table'] : '';
						$renderObjConf = $conf['conf.'][$val['table'].'.'];
						$this->currentRecordNumber++;
						$cObj->parentRecordNumber=$this->currentRecordNumber;
						$GLOBALS['TSFE']->currentRecord = $val['table'].':'.$val['id'].':'.$confmd5;
						$this->lastChanged($row['tstamp']);
						$cObj->start($row,$val['table']);
						$tmpValue = $cObj->cObjGetSingle($renderObjName, $renderObjConf, $renderObjKey);
						$theValue .= $tmpValue;
					}# else debug($GLOBALS['TSFE']->recordRegister,'RECORDS');
				}
			}
		}
		if ($conf['wrap'])	$theValue = $this->wrap($theValue,$conf['wrap']);
		if ($conf['stdWrap.'])	$theValue = $this->stdWrap($theValue,$conf['stdWrap.']);

		$GLOBALS['TSFE']->currentRecord = $originalRec;	// Restore
		return $theValue;
	}

}

?>
