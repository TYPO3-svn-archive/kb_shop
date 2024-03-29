<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');



$TCA['tx_kbshop_belist'] = Array (
	'ctrl' => $TCA['tx_kbshop_belist']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,starttime,endtime,fe_group,title,alias,tablenr,icon,description,listprops,permaprops,defsortprop'
	),
	'feInterface' => $TCA['tx_kbshop_category']['feInterface'],
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
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_belist.title',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
			)
		),
		'alias' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_belist.alias',		
			'displayCond' => 'FIELD:parent:REQ:false',
			'config' => Array (
				'type' => 'input',	
				'size' => '30',	
			)
		),
		'tablenr' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_belist.table',		
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'tx_kbshop_category',
				'foreign_table_where' => 'AND parent=0',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'icon' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_belist.icon',		
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],	
				'max_size' => 100,	
				'uploadfolder' => 'uploads/tx_kbshop',
				'show_thumbs' => 1,	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'description' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'prefixLangTitle',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_belist.description',		
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
				'wizards' => Array(
					'_PADDING' => 2,
					'RTE' => Array(
						'notNewRecords' => 1,
						'RTEonly' => 1,
						'type' => 'script',
						'title' => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
						'icon' => 'wizard_rte2.gif',
						'script' => 'wizard_rte.php',
					),
				),
			)
		),
		'listprops' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_belist.listprops',		
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'tx_kbshop_property',
				'foreign_table_where' => ' AND tx_kbshop_property.pid IN (0###PAGE_TSCONFIG_STR###)',
				'size' => 8,
				'minitems' => 0,
				'autoSizeMax' => 25,
				'maxitems' => 100,
			),
		),
		'permaprops' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_belist.permaprops',		
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'tx_kbshop_property',
				'foreign_table_where' => ' AND tx_kbshop_property.pid IN (0###PAGE_TSCONFIG_STR###)',
				'size' => 8,
				'minitems' => 0,
				'autoSizeMax' => 25,
				'maxitems' => 100,
			),
		),
		'defsortprop' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_belist.defsortprop',		
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'tx_kbshop_property',
				'foreign_table_where' => ' AND tx_kbshop_property.pid IN (0###PAGE_TSCONFIG_STR###)',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			),
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
				'foreign_table' => 'tx_kbshop_category',
				'foreign_table_where' => 'AND tx_kbshop_category.uid=###REC_FIELD_l18n_parent### AND tx_kbshop_category.sys_language_uid IN (-1,0)',
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
		'0' => Array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, alias;;2, tablenr;;;;3-3-3, sys_language_uid;;3, t3ver_label;;;;4-4-4, icon, description;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kbshop/rte/], listprops, permaprops, defsortprop'),
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'starttime, endtime, fe_group'),
		'3' => Array('showitem' => 'l18n_parent, l18n_diffsource'),
	),
);

?>
