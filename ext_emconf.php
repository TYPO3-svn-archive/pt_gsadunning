<?php

########################################################################
# Extension Manager/Repository config file for ext: "pt_gsadunning"
#
# Auto generated 13-11-2008 17:38
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'GSA Dunning',
	'description' => 'CLI based dunning addition for the General Shop Applications (GSA) extensions',
	'category' => 'General Shop Applications',
	'author' => 'Rainer Kuhn',
	'author_email' => 't3extensions@punkt.de',
	'shy' => '',
	'dependencies' => 'smarty,pt_tools,pt_mail,pt_gsauserreg,pt_gsashop,pt_gsaaccounting,pt_gsapdfdocs',
	'conflicts' => '',
	'priority' => '',
	'module' => 'cronmod',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.0.1dev',
	'constraints' => array(
		'depends' => array(
			'smarty' => '1.0.2-',
			'pt_tools' => '0.3.1-',
			'pt_mail' => '0.0.1',
			'pt_gsauserreg' => '0.0.13',
			'pt_gsashop' => '0.14.0',
			'pt_gsaaccounting' => '0.0.2',
			'pt_gsapdfdocs' => '0.0.1',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'PHP with --enable-bcmath (THIS IS JUST A HINT, please ignore if your server is correctly configured)' => '',
			'PEAR Console_Getopt (THIS IS JUST A HINT, please ignore if your server is correctly configured)' => '',
		),
	),
	'_md5_values_when_last_written' => 'a:24:{s:9:"ChangeLog";s:4:"9a56";s:10:"README.txt";s:4:"ee2d";s:21:"ext_conf_template.txt";s:4:"e876";s:12:"ext_icon.gif";s:4:"4546";s:17:"ext_localconf.php";s:4:"6946";s:14:"ext_tables.php";s:4:"885d";s:14:"ext_tables.sql";s:4:"6c80";s:32:"icon_tx_ptgsadunning_dunning.gif";s:4:"dc05";s:16:"locallang_db.xml";s:4:"18c5";s:7:"tca.php";s:4:"ece5";s:14:"doc/DevDoc.txt";s:4:"0954";s:19:"doc/wizard_form.dat";s:4:"2761";s:20:"doc/wizard_form.html";s:4:"1eb4";s:48:"cronmod/class.tx_ptgsadunning_dunningchecker.php";s:4:"d2c0";s:30:"cronmod/cli_dunningchecker.php";s:4:"3e3d";s:16:"cronmod/conf.php";s:4:"4b00";s:21:"cronmod/locallang.xml";s:4:"fdad";s:54:"res/class.tx_ptgsadunning_controller_download_hook.php";s:4:"526f";s:36:"res/class.tx_ptgsadunning_pdfdoc.php";s:4:"a032";s:40:"res/smarty_tpl/reminder_mailbody.tpl.txt";s:4:"3a08";s:35:"res/smarty_tpl/reminder_pdf.tpl.xml";s:4:"2fa4";s:20:"static/constants.txt";s:4:"71dd";s:16:"static/setup.txt";s:4:"45a7";s:16:"eID/download.php";s:4:"eb7f";}',
	'suggests' => array(
	),
);

?>