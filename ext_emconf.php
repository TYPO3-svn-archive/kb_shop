<?php

########################################################################
# Extension Manager/Repository config file for ext: "kb_shop"
#
# Auto generated 01-03-2008 22:15
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'KB Shop',
	'description' => 'A web-shop application. Adaption to specific needs (fields) by changing flex-form definition (wizard planned). All fields passed through TS configurable stdWrap/cObjects before output in FE',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '0.1.1',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1',
	'state' => 'alpha',
	'uploadfolder' => 1,
	'createDirs' => 'uploads/tx_kbshop/rte/,uploads/tx_kbshop/selecticons/,uploads/tx_kbshop/wizardicons',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Bernhard Kraft',
	'author_email' => 'kraftb@kraftb.at',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:83:{s:10:"EXTEND.txt";s:4:"9ba7";s:28:"class.tx_kbshop_abstract.php";s:4:"72b2";s:28:"class.tx_kbshop_category.php";s:4:"ccbd";s:26:"class.tx_kbshop_config.php";s:4:"af4b";s:31:"class.tx_kbshop_geolocation.php";s:4:"010f";s:27:"class.tx_kbshop_isEmail.php";s:4:"7397";s:28:"class.tx_kbshop_itemproc.php";s:4:"0d75";s:24:"class.tx_kbshop_misc.php";s:4:"6603";s:33:"class.tx_kbshop_popupLinkWrap.php";s:4:"166f";s:29:"class.tx_kbshop_sqlengine.php";s:4:"4122";s:33:"class.tx_kbshop_t3lib_tcemain.php";s:4:"790f";s:31:"class.tx_kbshop_t3libbefunc.php";s:4:"bdd6";s:32:"class.tx_kbshop_t3libtcemain.php";s:4:"2d50";s:26:"class.tx_kbshop_tcagen.php";s:4:"99e1";s:31:"class.tx_kbshop_tcagen_flex.php";s:4:"8a85";s:30:"class.tx_kbshop_tcagen_pi1.php";s:4:"a9f9";s:30:"class.tx_kbshop_tcagen_pi2.php";s:4:"f9fa";s:30:"class.tx_kbshop_tcagen_tca.php";s:4:"4afb";s:26:"class.tx_kbshop_tcamgm.php";s:4:"92c4";s:28:"class.tx_kbshop_treeview.php";s:4:"54f3";s:27:"class.tx_kbshop_various.php";s:4:"040f";s:27:"class.ux_t3lib_tceforms.php";s:4:"8582";s:26:"class.ux_t3lib_tcemain.php";s:4:"1bb4";s:26:"class.ux_tslib_content.php";s:4:"051d";s:21:"ext_conf_template.txt";s:4:"4cae";s:12:"ext_icon.gif";s:4:"3e4d";s:17:"ext_localconf.php";s:4:"fd65";s:22:"ext_section_tables.php";s:4:"0d8d";s:14:"ext_tables.php";s:4:"8fb5";s:14:"ext_tables.sql";s:4:"bdef";s:24:"ext_typoscript_setup.txt";s:4:"1a9d";s:27:"icon_tx_kbshop_category.gif";s:4:"401f";s:26:"icon_tx_kbshop_product.gif";s:4:"dc05";s:27:"icon_tx_kbshop_property.gif";s:4:"78eb";s:26:"icon_tx_kbshop_section.gif";s:4:"dc05";s:13:"locallang.php";s:4:"ae3f";s:16:"locallang_db.php";s:4:"e4c6";s:16:"svn-commit.2.tmp";s:4:"7945";s:16:"svn-commit.3.tmp";s:4:"7945";s:14:"svn-commit.tmp";s:4:"7945";s:7:"tca.php";s:4:"2c67";s:14:"tca_belist.php";s:4:"1639";s:16:"tca_category.php";s:4:"2509";s:16:"tca_property.php";s:4:"3b72";s:14:"doc/manual.sxw";s:4:"dda2";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"6140";s:14:"mod1/index.php";s:4:"7c4d";s:18:"mod1/locallang.php";s:4:"9178";s:22:"mod1/locallang_mod.php";s:4:"7f6a";s:19:"mod1/moduleicon.gif";s:4:"3e4d";s:14:"pi1/ce_wiz.gif";s:4:"02b6";s:27:"pi1/class.tx_kbshop_pi1.php";s:4:"3c0d";s:35:"pi1/class.tx_kbshop_pi1_wizicon.php";s:4:"4146";s:27:"pi1/class.tx_kbshop_pi2.php";s:4:"8399";s:35:"pi1/class.tx_kbshop_pi2_wizicon.php";s:4:"7836";s:28:"pi1/class.tx_kbshop_t3tt.php";s:4:"67a4";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.php";s:4:"a989";s:24:"pi1/static/editorcfg.txt";s:4:"898d";s:25:"res/default_template.html";s:4:"36d7";s:18:"res/ds_product.xml";s:4:"9252";s:28:"res/ds_property_checkbox.xml";s:4:"fd97";s:29:"res/ds_property_container.xml";s:4:"1ce6";s:24:"res/ds_property_date.xml";s:4:"4fd9";s:28:"res/ds_property_datetime.xml";s:4:"3e90";s:25:"res/ds_property_dbrel.xml";s:4:"385f";s:27:"res/ds_property_decimal.xml";s:4:"2328";s:24:"res/ds_property_file.xml";s:4:"768b";s:28:"res/ds_property_freetext.xml";s:4:"df2a";s:31:"res/ds_property_freetextrte.xml";s:4:"3491";s:31:"res/ds_property_geolocation.xml";s:4:"56c9";s:27:"res/ds_property_integer.xml";s:4:"5eba";s:24:"res/ds_property_link.xml";s:4:"7874";s:30:"res/ds_property_multicheck.xml";s:4:"e5bf";s:26:"res/ds_property_select.xml";s:4:"0b2a";s:26:"res/ds_property_string.xml";s:4:"9ff0";s:24:"res/ds_property_time.xml";s:4:"72d5";s:27:"res/ds_property_timesec.xml";s:4:"eea0";s:24:"res/ds_property_user.xml";s:4:"9726";s:24:"res/ds_property_year.xml";s:4:"ea80";s:23:"res/flexform_ds_pi1.xml";s:4:"08b5";s:23:"res/flexform_ds_pi2.xml";s:4:"a1e1";}',
	'suggests' => array(
	),
);

?>