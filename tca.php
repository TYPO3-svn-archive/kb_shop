<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['TCAmode'])	{
		/* For Flex-Mode (not implemented fully actually) */
	$TCA['tx_kbshop_product'] = Array (
		'ctrl' => $TCA['tx_kbshop_product']['ctrl'],
		'interface' => Array (
			'showRecordFieldList' => 'hidden,starttime,endtime,fe_group,title,category,flexform'
		),
		'feInterface' => $TCA['tx_kbshop_product']['feInterface'],
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
				'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_product.title',		
				'config' => Array (
					'type' => 'input',	
					'size' => '30',
				)
			),
			'category' => Array (		
				'exclude' => 1,		
				'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_product.category',		
				'config' => Array (
					'type' => 'select',
					'form_type' => 'user',
					'userFunc' => 'tx_kbshop_treeview->displayCategoryTree',
					'treeView' => 1,
					'foreign_table' => 'tx_kbshop_category',
					'foreign_table_where' => ' AND tx_kbshop_category.pid=###KBSHOP_PID_CATEGORIES###',
					'size' => 3,
					'autoSizeMax' => 25,
					'minitems' => 0,
					'maxitems' => 2,
				),
			),
			'flexform' => Array (		
				'exclude' => 1,		
				'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_product.flexform',		
				'displayCond' => 'FIELD:category:REQ:true',
				'config' => Array (
					'type' => 'flex',
					'ds' => Array(
						'default' => 'FILE:EXT:kb_shop/res/ds_product.xml',
					),
				)
			),
		),
		'types' => Array (
			'0' => Array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, category, flexform;;;;3-3-3')
		),
		'palettes' => Array (
			'1' => Array('showitem' => 'starttime, endtime, fe_group')
		)
	);
}



$TCA['tx_kbshop_category'] = Array (
	'ctrl' => $TCA['tx_kbshop_category']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,starttime,endtime,fe_group,title,parent,image,description,properties'
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
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.title',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
			)
		),
		'alias' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.alias',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',	
			)
		),
		'parent' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.parent',		
			'config' => Array (
				'type' => 'select',
				'form_type' => 'user',
				'userFunc' => 'tx_kbshop_treeview->displayCategoryTree',
				'treeView' => 1,
				'foreign_table' => 'tx_kbshop_category',
				'foreign_table_where' => ' AND tx_kbshop_category.pid=###KBSHOP_PID_CATEGORIES###',
				'size' => 1,
				'autoSizeMax' => 25,
				'minitems' => 0,
				'maxitems' => 2,
			),
		),
		'image' => Array (		
			'exclude' => 1,		
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
		'description' => Array (		
			'exclude' => 1,		
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
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category.properties',		
			'config' => Array (
				'type' => 'select',
				'form_type' => 'user',
				'userFunc' => 'tx_kbshop_treeview->displayCategoryTree',
				'treeView' => 1,
				'foreign_table' => 'tx_kbshop_property',
				'foreign_table_where' => ' AND tx_kbshop_property.pid IN (###KBSHOP_PID_PROPERTIES###)',
				'size' => 3,
				'autoSizeMax' => 25,
				'minitems' => 0,
				'maxitems' => 500,
				'MM' => 'tx_kbshop_category_properties_mm',
			),
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, parent;;;;3-3-3, image, description;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kbshop/rte/], properties')
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'starttime, endtime, fe_group')
	)
);



$TCA['tx_kbshop_property'] = Array (
	'ctrl' => $TCA['tx_kbshop_property']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,starttime,endtime,fe_group,title,alias,parent,parent_value,type,flexform'
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
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.title',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
			)
		),
		'alias' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.alias',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',
			)
		),
		'parent' => Array (		
			'exclude' => 1,		
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
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.8', '8'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.11', '11'),
					Array('LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property.type.I.200', '200'),
				),
				'size' => 1,	
				'maxitems' => 1,
			)
		),
		'flexform' => Array (		
			'exclude' => 1,		
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
					'200' => 'FILE:EXT:kb_shop/res/ds_property_container.xml',
				),
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, alias, parent, parent_value, type;;;;3-3-3, flexform'),
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'starttime, endtime, fe_group')
	)
);

?>