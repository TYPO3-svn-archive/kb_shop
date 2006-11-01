<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


$TCA['tx_kbshop_property'] = Array (
	'ctrl' => $TCA['tx_kbshop_property']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,starttime,endtime,fe_group,title,alias,parent,parent_value,type,sys_language_mode,flexform'
	),
	'feInterface' => $TCA['tx_kbshop_property']['feInterface'],
	'columns' => Array (
		'hidden' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'starttime' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => Array (
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
				)
			)
		),
		'fe_group' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.fe_group',
			'config' => Array (
				'type' => 'select',	
				'items' => Array (
					Array('', 0),
					Array('LLL:EXT:lang/locallang_general.php:LGL.hide_at_login', -1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.any_login', -2),
					Array('LLL:EXT:lang/locallang_general.php:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		'title' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'prefixLangTitle',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.title',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
			)
		),
		'alias' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.alias',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',
			)
		),
		'parent' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.parent',		
			'config' => Array (
				'type' => 'select',
				'form_type' => 'user',
				'userFunc' => 'tx_kbshop_treeview->displayCategoryTree',
				'treeView' => 1,
				'foreign_table' => 'tx_kbshop_property',
				'foreign_table_where' => ' AND (tx_kbshop_property.type=1 OR tx_kbshop_property.type=200)',
				'size' => 1,
				'autoSizeMax' => 25,
				'minitems' => 0,
				'maxitems' => 2,
			),
		),
		'parent_value' => Array(
			'exclude' => 1,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.parent_value',		
			'displayCond' => 'FIELD:parent:REQ:true',
			'config' => Array(
				'type' => 'select',
				'itemsProcFunc' => 'tx_kbshop_itemproc->user_TCAitemsProcFunc_propertyParentValue',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
		'type' => Array (		
			'exclude' => 0,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type',		
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.0', '0'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.1', '1'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.9', '9'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.2', '2'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.3', '3'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.4', '4'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.5', '5'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.6', '6'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.10', '10'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.7', '7'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.12', '12'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.13', '13'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.14', '14'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.15', '15'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.8', '8'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.11', '11'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.200', '200'),
				),
				'size' => 1,	
				'maxitems' => 1,
			)
		),
		'sys_language_mode' => Array (		
			'exclude' => 0,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.sys_language_mode',		
			'displayCond' => 'FIELD:type:<:200 && FIELD:sys_language_uid:REQ:false',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.sys_language_mode.I.default', ''),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.sys_language_mode.I.exclude', 'exclude'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.sys_language_mode.I.mergeIfNotBlank', 'mergeIfNotBlank'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.sys_language_mode.I.noCopy', 'noCopy'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.sys_language_mode.I.prefixLangTitle', 'prefixLangTitle'),
				),
				'itemsProcFunc' => 'tx_kbshop_itemproc->user_TCAitemsProcFunc_propertySysLanguageModeValue',
				'size' => 1,	
				'maxitems' => 1,
			)
		),
		'flexform' => Array (
			'exclude' => 1,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.flexform',		
//			'displayCond' => 'FIELD:type:REQ:true && FIELD:type:!IN:200',
			'displayCond' => 'FIELD:type:REQ:true',
			'config' => Array (
				'type' => 'flex',
				'ds_pointerField' => 'type',
				'ds' => Array(
					'default' => 'FILE:EXT:kb_shop/res/ds_property_decimal.xml',
					'1' => 'FILE:EXT:kb_shop/res/ds_property_select.xml',
					'2' => 'FILE:EXT:kb_shop/res/ds_property_freetext.xml',
					'3' => 'FILE:EXT:kb_shop/res/ds_property_freetextrte.xml',
					'4' => 'FILE:EXT:kb_shop/res/ds_property_decimal.xml',
					'5' => 'FILE:EXT:kb_shop/res/ds_property_integer.xml',
					'6' => 'FILE:EXT:kb_shop/res/ds_property_checkbox.xml',
					'7' => 'FILE:EXT:kb_shop/res/ds_property_date.xml',
					'8' => 'FILE:EXT:kb_shop/res/ds_property_string.xml',
					'9' => 'FILE:EXT:kb_shop/res/ds_property_dbrel.xml',
					'10' => 'FILE:EXT:kb_shop/res/ds_property_multicheck.xml',
					'11' => 'FILE:EXT:kb_shop/res/ds_property_file.xml',
					'12' => 'FILE:EXT:kb_shop/res/ds_property_time.xml',
					'13' => 'FILE:EXT:kb_shop/res/ds_property_timesec.xml',
					'14' => 'FILE:EXT:kb_shop/res/ds_property_datetime.xml',
					'15' => 'FILE:EXT:kb_shop/res/ds_property_year.xml',
					'200' => 'FILE:EXT:kb_shop/res/ds_property_container.xml',
				),
			)
		),
		'sys_language_uid' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => Array(
					Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				),
			),
		),
		'l18n_parent' => Array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
				),
				'foreign_table' => 'tx_kbshop_property',
				'foreign_table_where' => 'AND tx_kbshop_property.uid=###REC_FIELD_l18n_parent### AND tx_kbshop_property.sys_language_uid IN (-1,0)',
			),
		),
		'l18n_diffsource' => Array(
			'config'=>array(
				'type'=>'passthrough'
			),
		),
		't3ver_label' => Array (
			'displayCond' => 'FIELD:t3ver_label:REQ:true',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
			'config' => Array (
							'type'=>'none',
							'cols' => 27,
			),
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, alias, sys_language_uid;;2;;3-3-3 t3ver_label, parent;;;;4-4-4, parent_value, type;;;;5-5-5, sys_language_mode, flexform'),
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'starttime, endtime, fe_group'),
		'2' => Array('showitem' => 'l18n_parent, l18n_diffsource'),
	)
);

?>
