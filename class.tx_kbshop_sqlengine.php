<?PHP
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
 * TCA Management class.
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */


require_once(t3lib_extMgm::extPath('kb_shop').'class.tx_kbshop_misc.php');
class tx_kbshop_sqlengine	{
	var $config = false;
	var $properties = array();
	var $tableUids = array();
	var $tables = array();
	var $basicFields = array(
		"	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,\n",
		"	pid int(11) unsigned DEFAULT '0' NOT NULL,\n",
		"	tstamp int(11) unsigned DEFAULT '0' NOT NULL,\n",
		"	crdate int(11) unsigned DEFAULT '0' NOT NULL,\n",
		"	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,\n",
		"	sorting int(10) unsigned DEFAULT '0' NOT NULL,\n",
		"	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,\n",
		"	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,\n",
		"	starttime int(11) unsigned DEFAULT '0' NOT NULL,\n",
		"	endtime int(11) unsigned DEFAULT '0' NOT NULL,\n",
		"	fe_group int(11) DEFAULT '0' NOT NULL,\n",
		"	category int(11) DEFAULT '0' NOT NULL,\n",
	);
	var $basicKeys = array(
		"	PRIMARY KEY (uid),\n",
		"	KEY parent (pid)\n",
	);

	function init(&$config)	{
		$this->config = &$config;
	}

	function writeExtTablesSQL($mmtables, $tkey = '')	{
		$sqlConfExtension = strlen($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['configExtension'])?$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['configExtension']:'kb_shop';
		$data = t3lib_div::getURL(t3lib_extMgm::extPath($sqlConfExtension).'ext_tables.sql');
		if (!strlen($data))	{
			$modified = false;
			$origDbTables = $dbTables = $this->getDBTables();
			$this->relTables = $this->getRelTables($dbTables, $tkey);
			foreach ($dbTables as $tkey => $trow)	{
				$tuid = $this->tableUids[$tkey];
				if ($this->tables[$tuid]['tcaTable'])	{
					$this->config->fieldPrefix_backup = $this->config->fieldPrefix;
					$this->config->entriesTablePrefix_backup = $this->config->entriesTablePrefix;
					$this->config->fieldPrefix = '';
					$this->config->entriesTablePrefix = '';
				}
				list($data, $mod) = $this->updateSQLTable($data, $tkey, $trow);
				$modified |= $mod;
				if ($this->tables[$tuid]['tcaTable'])	{
					$this->config->fieldPrefix = $this->config->fieldPrefix_backup;
					$this->config->entriesTablePrefix = $this->config->entriesTablePrefix_backup;
				}
			}
			$relTables = $this->getRelTables($origDbTables);
			$data = $this->setSectionTableCode($data, $relTables);
			$data = $this->setMMtableCode($data, $mmtables);
			t3lib_div::writeFile(t3lib_extMgm::extPath($sqlConfExtension).'ext_tables.sql', $data);
			$this->performDBupdates($sqlConfExtension);
			return true;
		} else	{
				// Already written. Just update MM tables
			if ($tkey)	{
				$dbTables = $this->getDBTables();
				$this->relTables = $this->getRelTables($dbTables, $tkey);
			}
			$data = $this->setMMtableCode($data, $mmtables);
			t3lib_div::writeFile(t3lib_extMgm::extPath($sqlConfExtension).'ext_tables.sql', $data);
			$this->performDBupdates($sqlConfExtension);
		}
		return false;
	}

	function updateSQLTable($data, $tkey, $trow)	{
		$modified = false;
		$origFields = array();
		$keys = array();
		if (preg_match('/['.preg_quote(chr(10).chr(13)).']CREATE\s+TABLE\s+'.preg_quote($this->config->entriesTablePrefix.$tkey).'\s+\((.*)['.preg_quote(chr(10).chr(13)).']\);/sU', $data, $tableMatches)>0)	{
			if (preg_match_all('/^\s*([a-zA-Z0-9_]+)\s+.*$/m', $tableMatches[1], $lineMatches, PREG_SET_ORDER)>0)	{
				foreach ($lineMatches as $idx => $match)	{
					if (substr($match[1], 0, strlen($this->config->fieldPrefix))!=$this->config->fieldPrefix)	{
						if (strtolower($match[1])=='fulltext')	{
							// Discard - get regenerated
						} elseif ((strtolower($match[1])=='primary')||(strtolower($match[1])=='key')||(strtolower($match[1])=='fulltext'))	{
							$keys[] = chr(9).trim($match[0]).chr(10);
						} else	{	
							$origFields[] = chr(9).trim($match[0]).chr(10);
						}
						continue;
					};
				}
				list($nlines, $fulltext) = $this->getSQLFieldLines($trow);

				$lrecs = tx_kbshop_abstract::getRecordsByField('tx_kbshop_category', 'l18n_parent', $trow['uid']);
				if (count($lrecs))	{
					$nlines[] = '	sys_language_uid int(11) DEFAULT \'0\' NOT NULL,'.chr(10);
					$nlines[] = '	l18n_parent int(11) DEFAULT \'0\' NOT NULL,'.chr(10);
					$nlines[] = '	l18n_diffsource mediumblob NOT NULL,'.chr(10);
				}
				if (0 && is_array($fulltext)&&count($fulltext))	{		// FULLTEXT does not work with TYPO3's sqlparser.
					$fulltext = array(chr(9).'FULLTEXT ('.implode(', ', array_unique($fulltext)).'),'.chr(10));
				} else	{
					$fulltext = array();
				}
				$lines = array_merge($origFields, $nlines, $fulltext, $keys);
				$new = '
CREATE TABLE '.$this->config->entriesTablePrefix.$tkey.' (
'.implode('', $lines).');';
				$data = str_replace($tableMatches[0], $new, $data);
				$modified = true;
			}
		} else	{
			list($nlines, $fulltext) = $this->getSQLFieldLines($trow);
			$tuid = $this->tableUids[$tkey];
			$lrecs = tx_kbshop_abstract::getRecordsByField('tx_kbshop_category', 'l18n_parent', $tuid);
			if (count($lrecs))	{
				$nlines[] = '	sys_language_uid int(11) DEFAULT \'0\' NOT NULL,'.chr(10);
				$nlines[] = '	l18n_parent int(11) DEFAULT \'0\' NOT NULL,'.chr(10);
				$nlines[] = '	l18n_diffsource mediumblob NOT NULL,'.chr(10);
			}
			if (0 && is_array($fulltext)&&count($fulltext))	{		// FULLTEXT does not work with TYPO3's sqlparser.	
				$fulltext = array(chr(9).'FULLTEXT ('.implode('),'.chr(10).chr(9).'FULLTEXT (', array_unique($fulltext)).'),'.chr(10));
			} else	{
				$fulltext = array();
			}
			$lines = array_merge($this->basicFields, $nlines, $fulltext, $this->basicKeys);
			$new = '
CREATE TABLE '.$this->config->entriesTablePrefix.$tkey.' (
'.implode('', $lines).');';
			$data .= $new;
			$modified = true;
		}
		return array($data, $modified);
	}

	function getDBTables($virtual = 0)	{
		$dbTables = array();
		$cats = tx_kbshop_abstract::getRecordsByField($this->config->categoriesTable, 'parent', 0, 'sorting', ' AND virtual'.($virtual?'>0':'=0').' AND sys_language_uid=0 AND parent=0'.tx_kbshop_abstract::deleteClause($this->config->categoriesTable));
		foreach ($cats as $row)	{
			$tkey = tx_kbshop_misc::getKey($row);
			$tuid = $row['uid'];
			if (!is_array($this->tables[$tuid]))	{
				$this->tables[$tuid] = array(
					'uid' => $tuid,
					'title' => $row['title'],
					'alias' => $row['alias'],
					'description' => $row['description'],
					'image' => $row['image'],
					'tcaTable' => $row['tcaTable'],
				);
			}
			$this->tableUids[$tkey] = $tuid;

			$check = array($row['uid']);
			$propUids = t3lib_div::intExplode(',', $row['properties']);
			while (count($check))	{
				$subcats = tx_kbshop_abstract::getRecordsByField($this->config->categoriesTable, 'sys_language_uid', 0, 'sorting', ' AND parent IN ('.implode(',', $check).')'.tx_kbshop_abstract::deleteClause($this->config->categoriesTable));
				$check = array();
				if (is_array($subcats))	{
					foreach ($subcats as $subcat)	{
						$propUids = array_merge($propUids, t3lib_div::intExplode(',', $subcat['properties']));
						$check[] = $subcat['uid'];
					}
				}
			}
			$propUids = array_unique($propUids);
			if (count($propUids))	{
				$props = tx_kbshop_abstract::getRecordsByField($this->config->propertiesTable, 'sys_language_uid', 0, 'sorting', ' AND '.$this->config->propertiesTable.'.uid IN ('.implode(',', $propUids).') '.tx_kbshop_abstract::deleteClause($this->config->propertiesTable));
				if (is_array($props))	{
					foreach ($props as $prop)	{
						$pkey = tx_kbshop_misc::getKey($prop);
						$this->tables[$tuid]['properties'][$prop['uid']] = $prop;
						$dbTables[$tkey][$pkey] = $prop;
					}
				}
			} else	{
					$this->tables[$tuid]['properties'][$prop['uid']] = array();
					$dbTables[$tkey][$pkey] = array();
			}
		}
		return $dbTables;
	}

	function getRelTables(&$dbTables, $ptkey = '')	{
		$relTables = array();
		foreach ($dbTables as $tkey => $dbFields)	{
			if ($ptkey && ($ptkey!=$tkey))	continue;	
			$tmpDbFields = $dbFields;
			$this->properties = $this->tables[$this->tableUids[$tkey]]['properties'];
			if (is_array($tmpDbFields))	{
				foreach ($tmpDbFields as $field => $fArr)	{
					if (intval($fArr['parent']))	{
						if ($cont = $this->inContainer($fArr))	{
							$relTables[$this->config->sectionTablePrefix.$tkey.$this->config->sectionTableCenter.$cont.$this->config->sectionTablePostfix][$field] = $fArr;
							unset($dbTables[$tkey][$field]);
						}
					}
				}
			}
		}
		return $relTables;
	}

	function setMMtableCode($data, $mmtables)	{
		$createTables = $mmtables;
		if (preg_match_all('/['.preg_quote(chr(10).chr(13)).']CREATE\s+TABLE\s+'.preg_quote($this->config->mmRelationTablePrefix).'([a-zA-Z0-9_]+)'.preg_quote($this->config->mmRelationTablePostfix).'\s+\(.*['.preg_quote(chr(10).chr(13)).']\);['.preg_quote(chr(10).chr(13)).']/sU', $data, $tableMatches, PREG_SET_ORDER)>0)	{
			foreach ($tableMatches as $idx => $match)	{
				if ($mmtables[$match[1]])	{
						// Doesn't need to get created
					unset($createTables[$match[1]]);
				} else	{
					// Just keep it.
				}
			}
		}
		$data .= $this->genMMtables(array_keys($createTables));
		return $data;
	}
	
	function setSectionTableCode($data, $relTables)	{
		$createTables = $relTables;
		if (preg_match_all('/['.preg_quote(chr(10).chr(13)).']CREATE\s+TABLE\s+'.preg_quote($this->config->config->sectionTablePrefix).'([a-zA-Z0-9_]+)'.preg_quote($this->config->sectionTablePostfix).'\s+\(.*['.preg_quote(chr(10).chr(13)).']\);['.preg_quote(chr(10).chr(13)).']/sU', $data, $tableMatches, PREG_SET_ORDER)>0)	{
			foreach ($tableMatches as $match)	{
				if ($relTab = $relTables[$this->config->sectionTablePrefix.$match[1].$this->config->sectionTablePostfix])	{
					list($tkey) = explode($this->config->sectionTableCenter, $match[1], 2);
					$new = $this->getSectionTable($this->config->sectionTablePrefix.$match[1].$this->config->sectionTablePostfix, $relTab);
					$data = str_replace($match[0], $new, $data);
					unset($createTables[$this->config->sectionTablePrefix.$match[1].$this->config->sectionTablePostfix]);
				} else	{
					$data = str_replace($match[0], '', $data);
				}
			}
		}
		if (is_array($createTables))	{
			foreach ($createTables as $key => $crFields)	{
				$data .= $this->getSectionTable($key, $crFields, $this->section);
			}
		}
		return $data;
	}

	function getSectionTable($name, $relTab)	{
		$new = '
CREATE TABLE '.$name.' (
	uid int(11) unsigned DEFAULT \'0\' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT \'0\' NOT NULL,
	tstamp int(11) unsigned DEFAULT \'0\' NOT NULL,
	crdate int(11) unsigned DEFAULT \'0\' NOT NULL,
	cruser_id int(11) unsigned DEFAULT \'0\' NOT NULL,
	sorting int(11) unsigned DEFAULT \'0\' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT \'0\' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT \'0\' NOT NULL,
	starttime int(11) unsigned DEFAULT \'0\' NOT NULL,
	endtime int(11) unsigned DEFAULT \'0\' NOT NULL,
	fe_group int(11) DEFAULT \'0\' NOT NULL,
	parent int(11) DEFAULT \'0\' NOT NULL,
';
		list($fields, $fulltext) = $this->getSQLFieldLines($relTab, $this->config->sectionFieldPrefix);
		list($nlines, $fulltext) = $this->getSQLFieldLines($trow);
		if (is_array($fulltext)&&count($fulltext))	{
			$fulltext = array(chr(9).'FULLTEXT ('.implode(', ', array_unique($fulltext)).'),'.chr(10));
		} else	{
			$fulltext = array();
		}
		$keys = array(
			chr(9).'PRIMARY KEY (uid),'.chr(10),
			chr(9).'KEY parent (pid),'.chr(10),
			chr(9).'KEY parentobj (parent),'.chr(10),
		);
		$fields = array_merge($fields, $keys, $fulltext);
		$new .= implode('', $fields);
		$new .= ');';
		return $new;
	}

	function genMMtables($createTables)	{
		if (is_array($createTables))	{
			$str = '';
			foreach ($createTables as $table)	{
				$str .= $this->genMMtables($table).chr(10);
			}
			return $str;
		} elseif (is_string($createTables))	{
			return '
CREATE TABLE '.$this->config->mmRelationTablePrefix.$createTables.$this->config->mmRelationTablePostfix.' (
	uid_local int(11) unsigned DEFAULT \'0\' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT \'0\' NOT NULL,
	tablenames varchar(30) DEFAULT \'\' NOT NULL,
	sorting int(11) unsigned DEFAULT \'0\' NOT NULL,
	crdate int(11) DEFAULT \'0\' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);
';
		}
	}


	function inContainer($row)	{
		if ($row['parent']&&is_array($this->properties[$row['parent']]))	{
			if ($this->properties[$row['parent']]['type']==200)	{
				$row = $this->properties[$row['parent']];
				$key = strtolower($row['alias']?$row['alias']:$row['uid']);
				return $key;
			} else	{
				return $this->inContainer($this->properties[$row['parent']]);
			}
		} else	{
			return false;
		}
	}

	function getSQLFieldLines($dbFields, $fieldPrefix = '')	{
		$res = array();
		$fulltext = array();
		if (!strlen($fieldPrefix))	{
			$fieldPrefix = $this->config->fieldPrefix;
		}
		if (is_array($dbFields))	{
			ksort($dbFields);
			foreach ($dbFields as $field => $fArr)	{
				$str = chr(9).$fieldPrefix.$field.' ';
				switch ($fArr['type'])	{
					case 2:		// renderTCA__Text
					case 3:		// renderTCA__RTE
						$str .= 'text NOT NULL';
						$fulltext[] = $fieldPrefix.$field;
					break;
					case 8:		// renderTCA__String
						$str .= 'varchar(220) DEFAULT \'\' NOT NULL';
					break;
					case 1:		// renderTCA__Select
						$str .= 'int(11) DEFAULT \'0\' NOT NULL';		// We either store the uid of a record or the number of relations.
					break;
					case 4:		// renderTCA__Decimal
						$str .= 'varchar(20) DEFAULT \'\' NOT NULL';
					break;
					case 9:		// renderTCA__dbrel
						$str .= 'varchar(100) DEFAULT \'\' NOT NULL';
					break;
					case 5:		// renderTCA__Integer
					case 7:		// renderTCA__Date
					case 12:		// renderTCA__Time
					case 13:		// renderTCA__Timesec
					case 14:		// renderTCA__DateTime
					case 15:		// renderTCA__Year
					case 10:		// renderTCA__multiCheck
						$str .= 'int(11) DEFAULT \'0\' NOT NULL';
					break;
					case 11:		// renderTCA__File
						$str .= 'blob NOT NULL';
					break;
					case 100:		// renderTCA__geolocation
						$str = chr(9).$fieldPrefix.$field.'_lat ';
						$str .= 'varchar(30) DEFAULT \'\' NOT NULL,'.chr(10);
						$str .= chr(9).$fieldPrefix.$field.'_lng ';
						$str .= 'varchar(30) DEFAULT \'\' NOT NULL';
					break;
					case 200:		// renderTCA__container
						$str .= 'int(11) DEFAULT \'0\' NOT NULL';
					break;
					case 16:		// renderTCA__Check
						$str .= 'tinytext NOT NULL';
					break;
					case 17:		// renderTCA__Check
						$xmldata = t3lib_div::xml2array($fArr['flexform']);
						$xmlArr = $xmldata['data']['sDEF'][$this->config->lDEF];
						$field = $xmlArr['field_sqlfield'][$this->config->vDEF];
						switch ($field)	{
							case 'int':
							case 'tinyint':
								$def = '0';
							case 'varchar':
								$len = $xmlArr['field_sqlsize'][$this->config->vDEF];
								$sig  = intval($xmlArr['field_sqlsigned'][$this->config->vDEF])?'signed':'unsigned';
								$str .= $field.'('.$len.') '.$sig.' DEFAULT \''.$def.'\' NOT NULL';
							break;
							case 'text':
							case 'mediumtext':
							case 'tinytext':
							case 'blob':
								$str .= $field.' NOT NULL';
							break;
							default:
								echo 'Invalid SQL field type \''.$field.'\' !';
								exit();
							break;
						}
					break;
					case 6:		// renderTCA__Check
						$str .= 'tinyint(4) unsigned DEFAULT \'0\' NOT NULL';
					break;
		/*
		 * TYPEADD: Add cases here if you introduce a new property-type
		 */
				}
				$str .= ','.chr(10);
				$res[] = $str;
			}
		}
		return array($res, $fulltext);
	}

	/**
	 * Updates the database according to extension requirements
	 */
	function performDBupdates($extKey = 'kb_shop')	{
		
		require_once(PATH_t3lib.'class.t3lib_install.php');

		$extInfo = $this->getInstExt(PATH_typo3conf.'ext/', $extKey);

			// Initializing Install Tool object:
		$instObj = t3lib_div::makeInstance('t3lib_install');
		$instObj->INSTALL = t3lib_div::_GP('TYPO3_INSTALL');
		$dbStatus = array();

			// Updating tables and fields?
		if (is_array($extInfo['files']) && in_array('ext_tables.sql',$extInfo['files']))	{
			$fileContent = t3lib_div::getUrl(t3lib_extMgm::extPath($extKey).'ext_tables.sql');

			$FDfile = $instObj->getFieldDefinitions_sqlContent($fileContent);
			if (count($FDfile))	{
				$FDdb = $instObj->getFieldDefinitions_database(TYPO3_db);
				$diff = $instObj->getDatabaseExtra($FDfile, $FDdb);
				$update_statements = $instObj->getUpdateSuggestions($diff);

				if (is_array($update_statements['add']))	{
					foreach ($update_statements['add'] as $hash => $updateSt)	{
						if (preg_match('/ALTER TABLE ([a-zA-Z0-9_]+) ADD FULLTEXT \(([a-zA-Z0-9_\,]+)\);/', $updateSt, $matches)>0)	{
							foreach ($FDdb[$matches[1]]['keys'] as $idx => $key)	{
								if (preg_match('/KEY [a-zA-Z0-9_]+ \('.$matches[2].'\)/', $key, $smatches)>0)	{
									unset($update_statements['add'][$hash]);
								}
							}
						}
					}
				}

				$dbStatus['structure']['tables_fields'] = $FDfile;
				$dbStatus['structure']['diff'] = $diff;

					// We REALLY want to modify the DB. On the fly.
				$okArr = array();
				foreach ($update_statements as $mode => $updateArr)	{
					foreach ($updateArr as $key => $statement)	{
						$okArr[$mode][$key] = true;
					}
				}

				// Updating database...
				$instObj->performUpdateQueries($update_statements['add'], $okArr['add']);
				$instObj->performUpdateQueries($update_statements['change'], $okArr['change']);
				$instObj->performUpdateQueries($update_statements['create_table'], $okArr['create_table']);
			}
		}
	}

	/**
	 * Gathers all extensions in $path
	 *
	 * @param	string		Absolute path to local, global or system extensions
	 * @param	array		Array with information for each extension key found. Notice: passed by reference
	 * @param	array		Categories index: Contains extension titles grouped by various criteria.
	 * @param	string		Path-type: L, G or S
	 * @return	void		"Returns" content by reference
	 * @access private
	 * @see getInstalledExtensions()
	 */
	function getInstExt($path,$extKey)	{

		if (@is_dir($path))	{
			if (@is_file($path.$extKey.'/ext_emconf.php'))	{
				$emConf = $this->includeEMCONF($path.$extKey.'/ext_emconf.php', $extKey);
				if (is_array($emConf))	{
					$list = array();
					$list['doubleInstall'].= $type;
					$list['type'] = $type;
					$list['EM_CONF'] = $emConf;
					$list['files'] = t3lib_div::getFilesInDir($path.$extKey);
					return $list;
				}
			}
		}
		return false;
	}

	/**
	 * Returns the $EM_CONF array from an extensions ext_emconf.php file
	 *
	 * @param	string		Absolute path to EMCONF file.
	 * @param	string		Extension key.
	 * @return	array		EMconf array values.
	 */
	function includeEMCONF($path,$_EXTKEY)	{
		@include($path);
		if(is_array($EM_CONF[$_EXTKEY])) {
			return $this->fixEMCONF($EM_CONF[$_EXTKEY]);
		}
		return false;
	}

	/**
	 * Fixes an old styke ext_emconf.php array by adding constraints if needed and removing deprecated keys
	 *
	 * @param unknown_type $emConf
	 * @return unknown
	 */
	function fixEMCONF($emConf) {
		if(!isset($emConf['constraints'])) {
			$emConf['constraints']['depends'] = $this->stringToDep($emConf['dependencies']);
			if(strlen($emConf['PHP_version'])) {
				$versionRange = $this->splitVersionRange($emConf['PHP_version']);
				if(version_compare($versionRange[0],'3.0.0','<')) $versionRange[0] = '3.0.0';
				if(version_compare($versionRange[1],'3.0.0','<')) $versionRange[1] = '';
				$emConf['constraints']['depends']['php'] = implode('-',$versionRange);
			}
			if(strlen($emConf['TYPO3_version'])) {
				$versionRange = $this->splitVersionRange($emConf['TYPO3_version']);
				if(version_compare($versionRange[0],'3.5.0','<')) $versionRange[0] = '3.5.0';
				if(version_compare($versionRange[1],'3.5.0','<')) $versionRange[1] = '';
				$emConf['constraints']['depends']['typo3'] = implode('-',$versionRange);
			}
			$emConf['constraints']['conflicts'] = $this->stringToDep($emConf['conflicts']);
			$emConf['constraints']['suggests'] = array();
		} elseif (isset($emConf['constraints']) && isset($emConf['dependencies'])) {
			$constraints = $emConf['constraints'];
			$emConf['dependencies'] = $this->depToString($constraints);
			$emConf['conflicts'] = $this->depToString($constraints, 'conflicts');
		}
		unset($emConf['private']);
		unset($emConf['download_password']);
		unset($emConf['TYPO3_version']);
		unset($emConf['PHP_version']);

		return $emConf;
	}

	/**
	 * Splits a version range into an array.
	 *
	 * If a single version number is given, it is considered a minimum value.
	 * If a dash is found, the numbers left and right are considered as minimum and maximum. Empty values are allowed.
	 *
	 * @param string $ver A string with a version range.
	 * @return array
	 */
	function splitVersionRange($ver) {
		$versionRange = array();
		if(strstr($ver, '-')) $versionRange = explode('-', $ver, 2);
		else {
			$versionRange[0] = $ver;
			$versionRange[1] = '';
		}

		return $versionRange;
	}

	/**
	 * Checks whether the passed dependency is TER2-style (array) and returns a single string for displaying the dependencies.
	 *
	 * It leaves out all version numbers and the "php" and "typo3" dependencies, as they are implicit and of no interest without the version number.
	 *
	 * @param mixed $dep Either a string or an array listing dependencies.
	 * @param string $type The dependency type to list if $dep is an array
	 * @return string	A simple dependency list for display
	 */
	function depToString($dep,$type='depends') {
		if(is_array($dep)) {
			unset($dep[$type]['php']);
			unset($dep[$type]['typo3']);
			$s = (count($dep[$type])) ? implode(',', array_keys($dep[$type])) : '';
			return $s;
		}
		return '';
	}

	/**
	 * Checks whether the passed dependency is TER-style (string) or TER2-style (array) and returns a single string for displaying the dependencies.
	 *
	 * It leaves out all version numbers and the "php" and "typo3" dependencies, as they are implicit and of no interest without the version number.
	 *
	 * @param mixed $dep Either a string or an array listing dependencies.
	 * @param string $type The dependency type to list if $dep is an array
	 * @return string	A simple dependency list for display
	 */
	function stringToDep($dep,$type='depends') {
		$constraint = array();
		if(is_string($dep) && strlen($dep)) {
			$dep = explode(',',$dep);
			foreach($dep as $v) {
				$constraint[$v] = '';
			}
		}
		return $constraint;
	}



}



?>
