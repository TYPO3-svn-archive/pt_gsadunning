<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addStaticFile($_EXTKEY,'static/', 'GSA Dunning');

$TCA["tx_ptgsadunning_dunning"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:pt_gsadunning/locallang_db.xml:tx_ptgsadunning_dunning',		
		'label'     => 'related_doc_no',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_ptgsadunning_dunning.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, related_doc_no, dunning_level, dunning_charge, last_dunning_date",
	)
);
?>