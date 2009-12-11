<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_ptgsadunning_dunning"] = array (
	"ctrl" => $TCA["tx_ptgsadunning_dunning"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,related_doc_no,dunning_level,dunning_charge,last_dunning_date"
	),
	"feInterface" => $TCA["tx_ptgsadunning_dunning"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"related_doc_no" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pt_gsadunning/locallang_db.xml:tx_ptgsadunning_dunning.related_doc_no",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "255",	
				"eval" => "required,trim",
			)
		),
		"dunning_level" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pt_gsadunning/locallang_db.xml:tx_ptgsadunning_dunning.dunning_level",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "int,nospace",
			)
		),
		"dunning_charge" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pt_gsadunning/locallang_db.xml:tx_ptgsadunning_dunning.dunning_charge",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"last_dunning_date" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pt_gsadunning/locallang_db.xml:tx_ptgsadunning_dunning.last_dunning_date",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, related_doc_no, dunning_level, dunning_charge, last_dunning_date")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);
?>