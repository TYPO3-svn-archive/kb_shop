<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::addModule('tools','txkbshopM1','',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
}


$TCA['tx_kbshop_category'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_category',		
		'label' => 'title',	
		'description_field' => 'description',	
		'tstamp' => 'tstamp',
		'treeParentField' => 'parent',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'requestUpdate' => 'parent',
			// Versioning - begin
		'versioningWS' => true,
		'versioning_followPages' => true,
		'origUid' => 't3_origuid',
			// Versioning - end
			// Localization - begin
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
			// Localization - end
		'enablecolumns' => Array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca_category.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_kbshop_category.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'hidden, starttime, endtime, fe_group, title, alias, virtual, allowOnPages, labelProperty, sortingProperty, sortingDirection, parent, image, icon, description, properties',
	)
);

$TCA['tx_kbshop_property'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_property',		
		'label' => 'title',	
		'tstamp' => 'tstamp',
		'treeParentField' => 'parent',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'requestUpdate' => 'parent,type',
			// Versioning - begin
		'versioningWS' => true,
		'versioning_followPages' => true,
		'origUid' => 't3_origuid',
			// Versioning - end
			// Localization - begin
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
			// Localization - end
		'enablecolumns' => Array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca_property.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_kbshop_property.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'hidden, starttime, endtime, fe_group, title, alias, type, sys_language_mode, flexform',
	)
);


if (t3lib_extMgm::isLoaded('static_info_tables')&&!is_array($GLOBALS['TCA']['static_languages']))	{
	require_once(t3lib_extMgm::extPath('static_info_tables').'ext_tables.php');
}


/* The dynamic tables */
if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['TCAmode'])	{
	$saveSQLdebug = $GLOBALS['TYPO3_DB']->debugOutput;
	$GLOBALS['TYPO3_DB']->debugOutput = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_shop']['SQLdebug'];
	$tcaObj = t3lib_div::getUserObj('EXT:kb_shop/class.tx_kbshop_tcamgm.php:&tx_kbshop_tcamgm');
	$GLOBALS['TYPO3_DB']->debugOutput = $saveSQLdebug;
	list($tca, $allowOnPages) = $tcaObj->getExtTablesCacheFile();
	if (is_array($tca))	{
		$GLOBALS['TCA'] = array_merge($GLOBALS['TCA'], $tca);
	}
	foreach ($allowOnPages as $table)	{
		t3lib_extMgm::allowTableOnStandardPages($table);
	}
	unset($tca);
} else	{
	$TCA['tx_kbshop_product'] = Array (
		'ctrl' => Array (
			'title' => 'LLL:EXT:kb_shop/locallang_db.php:tx_kbshop_product',		
			'label' => 'title',	
			'tstamp' => 'tstamp',
			'treeParentField' => 'parent',
			'crdate' => 'crdate',
			'cruser_id' => 'cruser_id',
			'sortby' => 'sorting',	
			'delete' => 'deleted',	
			'requestUpdate' => 'parent,type',
			'enablecolumns' => Array (		
				'disabled' => 'hidden',	
				'starttime' => 'starttime',	
				'endtime' => 'endtime',	
				'fe_group' => 'fe_group',
			),
			'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
			'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_kbshop_product.gif',
		),
		'feInterface' => Array (
			'fe_admin_fieldList' => 'hidden, starttime, endtime, fe_group, title, category, flexform',
		)
	);
}

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages,sys_language_uid';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key,pages,sys_language_uid';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';
$TCA['tt_content']['ctrl']['requestUpdate'] .=  ($TCA['tt_content']['ctrl']['requestUpdate']?',':'').'field_compare_field,field_compare_usersel,field_search_page,list_criteria_section';

t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:kb_shop/res/flexform_ds_pi1.xml');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi2', 'FILE:EXT:kb_shop/res/flexform_ds_pi2.xml');


t3lib_extMgm::addPlugin(Array('LLL:EXT:kb_shop/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'), 'list_type');	// Cached version
t3lib_extMgm::addPlugin(Array('LLL:EXT:kb_shop/locallang_db.php:tt_content.list_type_pi2', $_EXTKEY.'_pi2'), 'list_type');	// Uncached version


t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/','Shop');						


if (TYPO3_MODE=='BE')	{
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_kbshop_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_kbshop_pi1_wizicon.php';
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_kbshop_pi2_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_kbshop_pi2_wizicon.php';
	include_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_kbshop_treeview.php');
	include_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_kbshop_itemproc.php');
}

if (!strlen($cExt = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['configExtension']))	{
	@include(t3lib_extMgm::extPath($_EXTKEY).'ext_section_tables.php');
} else	{
	@include(t3lib_extMgm::extPath($cExt).'ext_section_tables.php');
}

$tempColumns = Array (
	'storage_pid' => Array (
		'exclude' => 1,
		'label' => 'LLL:EXT:lang/locallang_tca.php:storage_pid',
		'config' => Array (
			'type' => 'group',
			'internal_type' => 'db',
			'allowed' => 'pages',
			'size' => '5',
			'maxitems' => '10',
			'minitems' => '0',
			'show_thumbs' => '1'
		)
	),
);

t3lib_div::loadTCA('pages');
t3lib_extMgm::addTCAcolumns('pages',$tempColumns,1);


t3lib_extMgm::addPageTSConfig('

TCEFORM.tx_kbshop_category	{
	labelProperty {
		PAGE_TSCONFIG_IDLIST = '.$GLOBALS['TYPO3_DB']->cleanIntList($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['categoryFolders']).'
		PAGE_TSCONFIG_STR = '.$GLOBALS['TYPO3_DB']->cleanIntList($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['propertyFolders']).'
	}
	sortingProperty {
		PAGE_TSCONFIG_IDLIST = '.$GLOBALS['TYPO3_DB']->cleanIntList($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['categoryFolders']).'
		PAGE_TSCONFIG_STR = '.$GLOBALS['TYPO3_DB']->cleanIntList($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['propertyFolders']).'
	}
	parent {
		PAGE_TSCONFIG_IDLIST = '.$GLOBALS['TYPO3_DB']->cleanIntList($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['categoryFolders']).'
		PAGE_TSCONFIG_STR = '.$GLOBALS['TYPO3_DB']->cleanIntList($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['propertyFolders']).'
	}
	properties	{
		PAGE_TSCONFIG_IDLIST = '.$GLOBALS['TYPO3_DB']->cleanIntList($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['categoryFolders']).'
		PAGE_TSCONFIG_STR = '.$GLOBALS['TYPO3_DB']->cleanIntList($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['propertyFolders']).'
	}
}

');


?>
