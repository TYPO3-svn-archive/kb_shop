<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');



$TCA['tx_kbshop_category'] = Array (
	'ctrl' => $TCA['tx_kbshop_category']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,starttime,endtime,fe_group,title,alias,virtual,allowOnPages,labelProperty,sortingProperty,sortingDirection,parent,image,icon,description,properties'
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
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.title',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
			)
		),
		'alias' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.alias',		
			'displayCond' => 'FIELD:parent:REQ:false',
			'config' => Array (
				'type' => 'input',	
				'size' => '30',	
			)
		),
		'virtual' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.virtual',		
			'displayCond' => 'FIELD:parent:REQ:false',
			'config' => Array (
				'type' => 'check',	
			)
		),
		'allowOnPages' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.allowOnPages',		
			'displayCond' => 'FIELD:parent:REQ:false',
			'config' => Array (
				'type' => 'check',	
			)
		),
		'labelProperty' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.labelProperty',		
			'displayCond' => 'FIELD:parent:REQ:false',
			'exclude' => 1,		
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'tx_kbshop_property',
				'foreign_table_where' => ' AND tx_kbshop_property.pid IN (0###PAGE_TSCONFIG_STR###)',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
		'sortingProperty' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.sortingProperty',		
			'displayCond' => 'FIELD:parent:REQ:false',
			'exclude' => 1,		
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'tx_kbshop_property',
				'foreign_table_where' => ' AND tx_kbshop_property.pid IN (0###PAGE_TSCONFIG_STR###)',
				'size' => 1,
				'items' => array(
					array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.sortingProperty.manual', 0),
					array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.sortingProperty.tstamp', -1),
					array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.sortingProperty.crdate', -2),
				),
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
		'sortingDirection' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.sortingDirection',		
			'displayCond' => 'FIELD:parent:REQ:false',
			'exclude' => 1,		
			'config' => Array (
				'type' => 'select',
				'size' => 1,
				'items' => array(
					array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.sortingDirection.ascending', 0),
					array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.sortingDirection.descending', 1),
				),
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
		'parent' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.parent',		
			'config' => Array (
				'type' => 'select',
				'form_type' => 'user',
				'userFunc' => 'tx_kbshop_treeview->displayCategoryTree',
				'treeView' => 1,
				'foreign_table' => 'tx_kbshop_category',
				'foreign_table_where' => ' AND tx_kbshop_category.pid IN (0###PAGE_TSCONFIG_IDLIST###)',
				'size' => 1,
				'autoSizeMax' => 25,
				'minitems' => 0,
				'maxitems' => 2,
			),
		),
		'image' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.image',		
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],	
				'max_size' => 1000,	
				'uploadfolder' => 'uploads/tx_kbshop',
				'show_thumbs' => 1,	
				'size' => 4,	
				'minitems' => 0,
				'maxitems' => 10,
			)
		),
		'icon' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.icon',		
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
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.description',		
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
		'properties' => Array (		
			'exclude' => 1,		
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.properties',		
			'config' => Array (
				'type' => 'select',
				'form_type' => 'user',
				'userFunc' => 'tx_kbshop_treeview->displayCategoryTree',
				'treeView' => 1,
				'foreign_table' => 'tx_kbshop_property',
				'foreign_table_where' => ' AND tx_kbshop_property.pid IN (0###PAGE_TSCONFIG_STR###) AND tx_kbshop_property.sys_language_uid=0',
				'size' => 3,
				'autoSizeMax' => 25,
				'minitems' => 0,
				'maxitems' => 500,
//				'MM' => 'tx_kbshop_category_properties_mm',
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
		'0' => Array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, alias;;2, sys_language_uid;;3;;3-3-3, t3ver_label, parent;;;;4-4-4, image;;;;5-5-, icon, description;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kbshop/rte/], properties'),
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'starttime, endtime, fe_group'),
		'2' => Array('showitem' => 'virtual, allowOnPages, labelProperty, sortingProperty, sortingDirection'),
		'3' => Array('showitem' => 'l18n_parent, l18n_diffsource'),
	),
);

?>
