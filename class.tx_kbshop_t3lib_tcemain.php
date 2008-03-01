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
 * Extended version for t3lib_tcemain for FE forms
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */


require_once(PATH_t3lib.'class.t3lib_tcemain.php');
class tx_kbshop_t3lib_TCEmain extends t3lib_TCEmain	{


/**
	 * Evaluates a value according to $table/$field settings.
	 * This function is for real database fields - NOT FlexForm "pseudo" fields.
	 * NOTICE: Calling this function expects this: 1) That the data is saved! (files are copied and so on) 2) That files registered for deletion IS deleted at the end (with ->removeRegisteredFiles() )
	 *
	 * @param	string		Table name
	 * @param	string		Field name
	 * @param	string		Value to be evaluated. Notice, this is the INPUT value from the form. The original value (from any existing record) must be manually looked up inside the function if needed - or taken from $currentRecord array.
	 * @param	string		The record-uid, mainly - but not exclusively - used for logging
	 * @param	string		'update' or 'new' flag
	 * @param	integer		The real PID value of the record. For updates, this is just the pid of the record. For new records this is the PID of the page where it is inserted. If $realPid is -1 it means that a new version of the record is being inserted.
	 * @param	integer		$tscPID
	 * @return	array		Returns the evaluated $value as key "value" in this array. Can be checked with isset($res['value']) ...
	 */
	function checkValue($table,$field,$value,$id,$status,$realPid,$tscPID)	{
		global $TCA, $PAGES_TYPES;
		t3lib_div::loadTCA($table);

		$res = Array();	// result array
		$recFID = $table.':'.$id.':'.$field;

			// Processing special case of field pages.doktype
		if ($table=='pages' && $field=='doktype')	{
				// If the user may not use this specific doktype, we issue a warning
			if (! ($this->admin || t3lib_div::inList($this->BE_USER->groupData['pagetypes_select'],$value)))	{
				$propArr = $this->getRecordProperties($table,$id);
				$this->log($table,$id,5,0,1,"You cannot change the 'doktype' of page '%s' to the desired value.",1,array($propArr['header']),$propArr['event_pid']);
				return $res;
			};
			if ($status=='update')	{
					// This checks 1) if we should check for disallowed tables and 2) if there are records from disallowed tables on the current page
				$onlyAllowedTables = isset($PAGES_TYPES[$value]['onlyAllowedTables']) ? $PAGES_TYPES[$value]['onlyAllowedTables'] : $PAGES_TYPES['default']['onlyAllowedTables'];
				if ($onlyAllowedTables)	{
					$theWrongTables = $this->doesPageHaveUnallowedTables($id,$value);
					if ($theWrongTables)	{
						$propArr = $this->getRecordProperties($table,$id);
						$this->log($table,$id,5,0,1,"'doktype' of page '%s' could not be changed because the page contains records from disallowed tables; %s",2,array($propArr['header'],$theWrongTables),$propArr['event_pid']);
						return $res;
					}
				}
			}
		}

			// Get current value:
		if (strpos($id, 'NEW')===0)	{
			$curValueRec = array();
		} else	{
			$curValueRec = $this->recordInfo($table,$id,$field);
		}
		$curValue = $curValueRec[$field];

			// Getting config for the field
		$tcaFieldConf = $TCA[$table]['columns'][$field]['config'];

			// Preform processing:
		$res = $this->checkValue_SW($res,$value,$tcaFieldConf,$table,$id,$curValue,$status,$realPid,$recFID,$field,$this->uploadedFileArray[$table][$id][$field],$tscPID);

		return $res;
	}


	/**
	 * Filling in the field array
	 * $this->exclude_array is used to filter fields if needed.
	 *
	 * @param	string		Table name
	 * @param	[type]		$id: ...
	 * @param	array		Default values, Preset $fieldArray with 'pid' maybe (pid and uid will be not be overridden anyway)
	 * @param	array		$incomingFieldArray is which fields/values you want to set. There are processed and put into $fieldArray if OK
	 * @param	integer		The real PID value of the record. For updates, this is just the pid of the record. For new records this is the PID of the page where it is inserted.
	 * @param	string		$status = 'new' or 'update'
	 * @param	[type]		$tscPID: ...
	 * @return	[type]		...
	 */
	function fillInFieldArray($table,$id,$fieldArray,$incomingFieldArray,$realPid,$status,$tscPID)	{
		global $TCA;

			// Initialize:
		t3lib_div::loadTCA($table);
		$originalLanguageRecord = NULL;
		$originalLanguage_diffStorage = NULL;
		$diffStorageFlag = FALSE;

			// Setting 'currentRecord' and 'checkValueRecord':
		if (strstr($id,'NEW'))	{
			$currentRecord = $checkValueRecord = $fieldArray;	// must have the 'current' array - not the values after processing below...

				// IF $incomingFieldArray is an array, overlay it.
				// The point is that when new records are created as copies with flex type fields there might be a field containing information about which DataStructure to use and without that information the flexforms cannot be correctly processed.... This should be OK since the $checkValueRecord is used by the flexform evaluation only anyways...
			if (is_array($incomingFieldArray) && is_array($checkValueRecord))	{
				$checkValueRecord = t3lib_div::array_merge_recursive_overrule($checkValueRecord, $incomingFieldArray);
			}
		} else {
			$currentRecord = $checkValueRecord = $this->recordInfo($table,$id,'*');	// We must use the current values as basis for this!

				// Get original language record if available:
			if (is_array($currentRecord)
					&& $TCA[$table]['ctrl']['transOrigDiffSourceField']
					&& $TCA[$table]['ctrl']['languageField']
					&& $currentRecord[$TCA[$table]['ctrl']['languageField']] > 0
					&& $TCA[$table]['ctrl']['transOrigPointerField']
					&& intval($currentRecord[$TCA[$table]['ctrl']['transOrigPointerField']]) > 0)	{

				$lookUpTable = $TCA[$table]['ctrl']['transOrigPointerTable'] ? $TCA[$table]['ctrl']['transOrigPointerTable'] : $table;
				$originalLanguageRecord = $this->recordInfo($lookUpTable,$currentRecord[$TCA[$table]['ctrl']['transOrigPointerField']],'*');
				t3lib_BEfunc::workspaceOL($lookUpTable,$originalLanguageRecord);
				$originalLanguage_diffStorage = unserialize($currentRecord[$TCA[$table]['ctrl']['transOrigDiffSourceField']]);
			}
		}
		$this->checkValue_currentRecord = $checkValueRecord;

			/*
				In the following all incoming value-fields are tested:
				- Are the user allowed to change the field?
				- Is the field uid/pid (which are already set)
				- perms-fields for pages-table, then do special things...
				- If the field is nothing of the above and the field is configured in TCA, the fieldvalues are evaluated by ->checkValue

				If everything is OK, the field is entered into $fieldArray[]
			*/
		foreach($incomingFieldArray as $field => $fieldValue)	{
			if (!in_array($table.'-'.$field, $this->exclude_array) && !$this->data_disableFields[$table][$id][$field])	{	// The field must be editable.

					// Checking if a value for language can be changed:
				$languageDeny = $TCA[$table]['ctrl']['languageField'] && !strcmp($TCA[$table]['ctrl']['languageField'], $field) && !$this->BE_USER->checkLanguageAccess($fieldValue);

				if (!$languageDeny)	{
						// Stripping slashes - will probably be removed the day $this->stripslashes_values is removed as an option...
					if ($this->stripslashes_values)	{
						if (is_array($fieldValue))	{
							t3lib_div::stripSlashesOnArray($fieldValue);
						} else $fieldValue = stripslashes($fieldValue);
					}

					switch ($field)	{
						case 'uid':
						case 'pid':
							// Nothing happens, already set
						break;
						case 'perms_userid':
						case 'perms_groupid':
						case 'perms_user':
						case 'perms_group':
						case 'perms_everybody':
								// Permissions can be edited by the owner or the administrator
							if ($table=='pages' && ($this->admin || $status=='new' || $this->pageInfo($id,'perms_userid')==$this->userid) )	{
								$value=intval($fieldValue);
								switch($field)	{
									case 'perms_userid':
										$fieldArray[$field]=$value;
									break;
									case 'perms_groupid':
										$fieldArray[$field]=$value;
									break;
									default:
										if ($value>=0 && $value<pow(2,5))	{
											$fieldArray[$field]=$value;
										}
									break;
								}
							}
						break;
						case 't3ver_oid':
						case 't3ver_id':
						case 't3ver_wsid':
						case 't3ver_state':
						case 't3ver_swapmode':
						case 't3ver_count':
						case 't3ver_stage':
						case 't3ver_tstamp':
							// t3ver_label is not here because it CAN be edited as a regular field!
						break;
						default:
							if (isset($TCA[$table]['columns'][$field]))	{
									// Evaluating the value.
								$res = $this->checkValue($table,$field,$fieldValue,$id,$status,$realPid,$tscPID);
								if (isset($res['value']))	{
									$fieldArray[$field]=$res['value'];

										// Add the value of the original record to the diff-storage content:
									if ($TCA[$table]['ctrl']['transOrigDiffSourceField'])	{
										$originalLanguage_diffStorage[$field] = $originalLanguageRecord[$field];
										$diffStorageFlag = TRUE;
									}
								} else	{
									unset($fieldArray[$field]);
								}
								/*
								else	{
									// DEBUG
									unset($fieldArray[$field]);
								}
								*/
							} elseif ($TCA[$table]['ctrl']['origUid']===$field) {	// Allow value for original UID to pass by...
								$fieldArray[$field] = $fieldValue;
							}
						break;
					}
				}	// Checking language.
			}	// Check exclude fields / disabled fields...
		}

			// Add diff-storage information:
		if ($diffStorageFlag && !isset($fieldArray[$TCA[$table]['ctrl']['transOrigDiffSourceField']]))	{	// If the field is set it would probably be because of an undo-operation - in which case we should not update the field of course...
			 $fieldArray[$TCA[$table]['ctrl']['transOrigDiffSourceField']] = serialize($originalLanguage_diffStorage);
		}

			// Checking for RTE-transformations of fields:
		$types_fieldConfig = t3lib_BEfunc::getTCAtypes($table,$currentRecord);
		$theTypeString = t3lib_BEfunc::getTCAtypeValue($table,$currentRecord);
		if (is_array($types_fieldConfig))	{
			reset($types_fieldConfig);
			while(list(,$vconf) = each($types_fieldConfig))	{
					// Write file configuration:
				$eFile = t3lib_parsehtml_proc::evalWriteFile($vconf['spec']['static_write'],array_merge($currentRecord,$fieldArray));	// inserted array_merge($currentRecord,$fieldArray) 170502

					// RTE transformations:
				if (!$this->dontProcessTransformations)	{
					if (isset($fieldArray[$vconf['field']]))	{
							// Look for transformation flag:
						switch((string)$incomingFieldArray['_TRANSFORM_'.$vconf['field']])	{
							case 'RTE':
								$RTEsetup = $this->BE_USER->getTSConfig('RTE',t3lib_BEfunc::getPagesTSconfig($tscPID));
								$thisConfig = t3lib_BEfunc::RTEsetup($RTEsetup['properties'],$table,$vconf['field'],$theTypeString);

									// Set alternative relative path for RTE images/links:
								$RTErelPath = is_array($eFile) ? dirname($eFile['relEditFile']) : '';

									// Get RTE object, draw form and set flag:
								$RTEobj = &t3lib_BEfunc::RTEgetObj();
								if (is_object($RTEobj))	{
									$fieldArray[$vconf['field']] = $RTEobj->transformContent('db',$fieldArray[$vconf['field']],$table,$vconf['field'],$currentRecord,$vconf['spec'],$thisConfig,$RTErelPath,$currentRecord['pid']);
								} else {
									debug('NO RTE OBJECT FOUND!');
								}
							break;
						}
					}
				}

					// Write file configuration:
				if (is_array($eFile))	{
					$mixedRec = array_merge($currentRecord,$fieldArray);
					$SW_fileContent = t3lib_div::getUrl($eFile['editFile']);
					$parseHTML = t3lib_div::makeInstance('t3lib_parsehtml_proc');
					$parseHTML->init('','');

					$eFileMarker = $eFile['markerField']&&trim($mixedRec[$eFile['markerField']]) ? trim($mixedRec[$eFile['markerField']]) : '###TYPO3_STATICFILE_EDIT###';
					$insertContent = str_replace($eFileMarker,'',$mixedRec[$eFile['contentField']]);	// must replace the marker if present in content!

					$SW_fileNewContent = $parseHTML->substituteSubpart($SW_fileContent, $eFileMarker, chr(10).$insertContent.chr(10), 1, 1);
					t3lib_div::writeFile($eFile['editFile'],$SW_fileNewContent);

						// Write status:
					if (!strstr($id,'NEW') && $eFile['statusField'])	{
						$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
							$table,
							'uid='.intval($id),
							array(
								$eFile['statusField'] => $eFile['relEditFile'].' updated '.date('d-m-Y H:i:s').', bytes '.strlen($mixedRec[$eFile['contentField']])
							)
						);
					}
				} elseif ($eFile && is_string($eFile))	{
					$this->log($table,$id,2,0,1,"Write-file error: '%s'",13,array($eFile),$realPid);
				}
			}
		}
			// Return fieldArray
		return $fieldArray;
	}

	/**
	 * Handling files for group/select function
	 *
	 * @param	array		Array of incoming file references. Keys are numeric, values are files (basically, this is the exploded list of incoming files)
	 * @param	array		Configuration array from TCA of the field
	 * @param	string		Current value of the field
	 * @param	array		Array of uploaded files, if any
	 * @param	string		Status ("update" or ?)
	 * @param	string		tablename of record
	 * @param	integer		UID of record
	 * @param	string		Field identifier ([table:uid:field:....more for flexforms?]
	 * @return	array		Modified value array
	 * @see checkValue_group_select()
	 */
	function checkValue_group_select_file($valueArray,$tcaFieldConf,$curValue,$uploadedFileArray,$status,$table,$id,$recFID)	{

		if (!$this->bypassFileHandling)	{	// If filehandling should NOT be bypassed, do processing:

				// If any files are uploaded, add them to value array
			if (is_array($uploadedFileArray) &&
				$uploadedFileArray['name'] &&
				strcmp($uploadedFileArray['tmp_name'],'none'))	{
					$valueArray[]=$uploadedFileArray['tmp_name'];
					$this->alternativeFileName[$uploadedFileArray['tmp_name']] = $uploadedFileArray['name'];
			}

				// Creating fileFunc object.
			if (!$this->fileFunc)	{
				$this->fileFunc = t3lib_div::makeInstance('t3lib_basicFileFunctions');
				$this->include_filefunctions=1;
			}
				// Setting permitted extensions.
			$all_files = Array();
			$all_files['webspace']['allow'] = $tcaFieldConf['allowed'];
			$all_files['webspace']['deny'] = $tcaFieldConf['disallowed'] ? $tcaFieldConf['disallowed'] : '*';
			$all_files['ftpspace'] = $all_files['webspace'];
			$this->fileFunc->init('', $all_files);
		}

			// If there is an upload folder defined:
		if ($tcaFieldConf['uploadfolder'])	{
			if (!$this->bypassFileHandling)	{	// If filehandling should NOT be bypassed, do processing:
					// For logging..
				if ($GLOBALS['TCA'][$table]['ctrl']['virtual'])	{
					$propArr = array();
				} else	{
					$propArr = $this->getRecordProperties($table,$id);
				}

					// Get destrination path:
				$dest = $this->destPathFromUploadFolder($tcaFieldConf['uploadfolder']);

					// If we are updating:
				if ($status=='update')	{

						// Traverse the input values and convert to absolute filenames in case the update happens to an autoVersionized record.
						// Background: This is a horrible workaround! The problem is that when a record is auto-versionized the files of the record get copied and therefore get new names which is overridden with the names from the original record in the incoming data meaning both lost files and double-references!
						// The only solution I could come up with (except removing support for managing files when autoversioning) was to convert all relative files to absolute names so they are copied again (and existing files deleted). This should keep references intact but means that some files are copied, then deleted after being copied _again_.
						// Actually, the same problem applies to database references in case auto-versioning would include sub-records since in such a case references are remapped - and they would be overridden due to the same principle then.
						// Illustration of the problem comes here:
						// We have a record 123 with a file logo.gif. We open and edit the files header in a workspace. So a new version is automatically made.
						// The versions uid is 456 and the file is copied to "logo_01.gif". But the form data that we sents was based on uid 123 and hence contains the filename "logo.gif" from the original.
						// The file management code below will do two things: First it will blindly accept "logo.gif" as a file attached to the record (thus creating a double reference) and secondly it will find that "logo_01.gif" was not in the incoming filelist and therefore should be deleted.
						// If we prefix the incoming file "logo.gif" with its absolute path it will be seen as a new file added. Thus it will be copied to "logo_02.gif". "logo_01.gif" will still be deleted but since the files are the same the difference is zero - only more processing and file copying for no reason. But it will work.
					if ($this->autoVersioningUpdate===TRUE)	{
						foreach($valueArray as $key => $theFile)	{
							if ($theFile===basename($theFile))	{	// If it is an already attached file...
								$valueArray[$key] = PATH_site.$tcaFieldConf['uploadfolder'].'/'.$theFile;
							}
						}
					}

						// Finding the CURRENT files listed, either from MM or from the current record.
					$theFileValues=array();
					if ($tcaFieldConf['MM'])	{	// If MM relations for the files also!
						$dbAnalysis = t3lib_div::makeInstance('t3lib_loadDBGroup');
						$dbAnalysis->start('','files',$tcaFieldConf['MM'],$id);
						reset($dbAnalysis->itemArray);
						while (list($somekey,$someval)=each($dbAnalysis->itemArray))	{
							if ($someval['id'])	{
								$theFileValues[]=$someval['id'];
							}
						}
					} else {
						$theFileValues=t3lib_div::trimExplode(',',$curValue,1);
					}

						// DELETE files: If existing files were found, traverse those and register files for deletion which has been removed:
					if (count($theFileValues))	{
							// Traverse the input values and for all input values which match an EXISTING value, remove the existing from $theFileValues array (this will result in an array of all the existing files which should be deleted!)
						foreach($valueArray as $key => $theFile)	{
							if ($theFile && !strstr(t3lib_div::fixWindowsFilePath($theFile),'/'))	{
								$theFileValues = t3lib_div::removeArrayEntryByValue($theFileValues,$theFile);
							}
						}

							// This array contains the filenames in the uploadfolder that should be deleted:
						foreach($theFileValues as $key => $theFile)	{
							$theFile = trim($theFile);
							if (@is_file($dest.'/'.$theFile))	{
								$this->removeFilesStore[]=$dest.'/'.$theFile;
							} elseif ($theFile) {
								$this->log($table,$id,5,0,1,"Could not delete file '%s' (does not exist). (%s)",10,array($dest.'/'.$theFile, $recFID),$propArr['event_pid']);
							}
						}
					}
				}

					// Traverse the submitted values:
				foreach($valueArray as $key => $theFile)	{
						// NEW FILES? If the value contains '/' it indicates, that the file is new and should be added to the uploadsdir (whether its absolute or relative does not matter here)
					if (strstr(t3lib_div::fixWindowsFilePath($theFile),'/'))	{
							// Init:
						$maxSize = intval($tcaFieldConf['max_size']);
						$cmd='';
						$theDestFile='';		// Must be cleared. Else a faulty fileref may be inserted if the below code returns an error!

							// Check various things before copying file:
						if (@is_dir($dest) && (@is_file($theFile) || @is_uploaded_file($theFile)))	{		// File and destination must exist

								// Finding size. For safe_mode we have to rely on the size in the upload array if the file is uploaded.
							if (is_uploaded_file($theFile) && $theFile==$uploadedFileArray['tmp_name'])	{
								$fileSize = $uploadedFileArray['size'];
							} else {
								$fileSize = filesize($theFile);
							}

							if (!$maxSize || $fileSize<=($maxSize*1024))	{	// Check file size:
									// Prepare filename:
								$theEndFileName = isset($this->alternativeFileName[$theFile]) ? $this->alternativeFileName[$theFile] : $theFile;
								$fI = t3lib_div::split_fileref($theEndFileName);

									// Check for allowed extension:
								if ($this->fileFunc->checkIfAllowed($fI['fileext'], $dest, $theEndFileName)) {
									$theDestFile = $this->fileFunc->getUniqueName($this->fileFunc->cleanFileName($fI['file']), $dest);

										// If we have a unique destination filename, then write the file:
									if ($theDestFile)	{
										t3lib_div::upload_copy_move($theFile,$theDestFile);
										$this->copiedFileMap[$theFile] = $theDestFile;
										clearstatcache();
										if (!@is_file($theDestFile))	$this->log($table,$id,5,0,1,"Copying file '%s' failed!: The destination path (%s) may be write protected. Please make it write enabled!. (%s)",16,array($theFile, dirname($theDestFile), $recFID),$propArr['event_pid']);
									} else $this->log($table,$id,5,0,1,"Copying file '%s' failed!: No destination file (%s) possible!. (%s)",11,array($theFile, $theDestFile, $recFID),$propArr['event_pid']);
								} else $this->log($table,$id,5,0,1,"Fileextension '%s' not allowed. (%s)",12,array($fI['fileext'], $recFID),$propArr['event_pid']);
							} else $this->log($table,$id,5,0,1,"Filesize (%s) of file '%s' exceeds limit (%s). (%s)",13,array(t3lib_div::formatSize($fileSize),$theFile,t3lib_div::formatSize($maxSize*1024),$recFID),$propArr['event_pid']);
						} else $this->log($table,$id,5,0,1,'The destination (%s) or the source file (%s) does not exist. (%s)',14,array($dest, $theFile, $recFID),$propArr['event_pid']);

							// If the destination file was created, we will set the new filename in the value array, otherwise unset the entry in the value array!
						if (@is_file($theDestFile))	{
							$info = t3lib_div::split_fileref($theDestFile);
							$valueArray[$key]=$info['file']; // The value is set to the new filename
						} else {
							unset($valueArray[$key]);	// The value is set to the new filename
						}
					}
				}
			}

				// If MM relations for the files, we will set the relations as MM records and change the valuearray to contain a single entry with a count of the number of files!
			if ($tcaFieldConf['MM'])	{
				$dbAnalysis = t3lib_div::makeInstance('t3lib_loadDBGroup');
				$dbAnalysis->tableArray['files']=array();	// dummy

				reset($valueArray);
				while (list($key,$theFile)=each($valueArray))	{
						// explode files
						$dbAnalysis->itemArray[]['id']=$theFile;
				}
				if ($status=='update')	{
					$dbAnalysis->writeMM($tcaFieldConf['MM'],$id,0);
				} else {
					$this->dbAnalysisStore[] = array($dbAnalysis, $tcaFieldConf['MM'], $id, 0);	// This will be traversed later to execute the actions
				}
				$valueArray = $dbAnalysis->countItems();
			}
		}

		return $valueArray;
	}


}


?>
