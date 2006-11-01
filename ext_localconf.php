<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_kbshop_product=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_kbshop_category=1
');
t3lib_extMgm::addPageTSConfig('

	# ***************************************************************************************
	# CONFIGURATION of RTE in table "tx_kbshop_category", field "description"
	# ***************************************************************************************
RTE.config.tx_kbshop_category.description { hidePStyleItems = H1, H4, H5, H6
  proc.exitHTMLparser_db=1
  proc.exitHTMLparser_db {
    keepNonMatchedTags=1
    tags.font.allowedAttribs= color
    tags.font.rmTagIfNoAttrib = 1
    tags.font.nesting = global
  }
}
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_kbshop_property=1
');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,"editorcfg","
	tt_content.CSS_editor.ch.tx_kbshop_pi1 = < plugin.tx_kbshop_pi1.CSS_editor
	tt_content.CSS_editor.ch.tx_kbshop_pi2 = < plugin.tx_kbshop_pi1.CSS_editor
",43);


t3lib_extMgm::addPItoST43($_EXTKEY,"pi1/class.tx_kbshop_pi1.php", "_pi1", "list_type", 1);	// Cached version
t3lib_extMgm::addPItoST43($_EXTKEY,"pi1/class.tx_kbshop_pi1.php", "_pi2", "list_type", 0);	// Uncached version


$_EXTCONF = unserialize($_EXTCONF);    // unserializing the configuration so we can use it here
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['TCAmode'] = intval($_EXTCONF['TCAmode']) ? true : false;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['SQLdebug'] = intval($_EXTCONF['SQLdebug']) ? true : false;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['dontCache'] = intval($_EXTCONF['dontCache']) ? true : false;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['categoryFolders'] = trim($_EXTCONF['categoryFolders']);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['propertyFolders'] = trim($_EXTCONF['propertyFolders']);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['fieldPrefix'] = trim($_EXTCONF['fieldPrefix']);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['configExtension'] = trim($_EXTCONF['configExtension']);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['listItems'] = intval($_EXTCONF['listItems']);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['singleListItems'] = intval($_EXTCONF['singleListItems']);

if (!t3lib_extMgm::isLoaded($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['configExtension']))	{
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['configExtension'] = '';
}


	// Hooks
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass'][] = 'EXT:kb_shop/class.tx_kbshop_t3libbefunc.php:&tx_kbshop_t3libbefunc';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'] = array_merge(array('EXT:kb_shop/class.tx_kbshop_t3libtcemain.php:&tx_kbshop_t3libtcemain'), $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']?$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']:array());

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['tx_kbshop_isEmail'] = 'EXT:kb_shop/class.tx_kbshop_isEmail.php';

	// XCLASSES :(
$GLOBALS['TYPO3_CONF_VARS']['BE']['XCLASS']['t3lib/class.t3lib_tceforms.php'] = t3lib_extMgm::extPath($_EXTKEY).'class.ux_t3lib_tceforms.php';
$GLOBALS['TYPO3_CONF_VARS']['FE']['XCLASS']['t3lib/class.t3lib_tceforms.php'] = t3lib_extMgm::extPath($_EXTKEY).'class.ux_t3lib_tceforms.php';
$GLOBALS['TYPO3_CONF_VARS']['FE']['XCLASS']['tslib/class.tslib_content.php'] = t3lib_extMgm::extPath($_EXTKEY).'class.ux_tslib_content.php';


// $GLOBALS['TYPO3_CONF_VARS']['FE']['XCLASS']['t3lib/class.t3lib_tcemain.php'] = t3lib_extMgm::extPath($_EXTKEY).'class.ux_t3lib_tcemain.php';
// $GLOBALS['TYPO3_CONF_VARS']['BE']['XCLASS']['t3lib/class.t3lib_transferdata.php'] = t3lib_extMgm::extPath($_EXTKEY).'class.ux_t3lib_transferdata.php';



?>
