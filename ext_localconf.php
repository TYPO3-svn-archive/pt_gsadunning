<?php
/**
 * @version     $Id: ext_localconf.php,v 1.1 2008/10/20 15:56:34 ry37 Exp $
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-10-20
 */

if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

/*******************************************************************************
 * eID
 ******************************************************************************/

// eID-Skript for downloading the files
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['tx_ptgsadunning_download'] = 'EXT:pt_gsadunning/eID/download.php';



/*******************************************************************************
 * HOOKS   - !!IMPORTANT: clear conf cache to activate changes!!
 ******************************************************************************/

// download controller hook
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_mvc']['controller_actions']['tx_ptgsapdfdocs_controller_download']['downloadDunningDocumentAction'] 
    = 'EXT:pt_gsadunning/res/class.tx_ptgsadunning_controller_download_hook.php:tx_ptgsadunning_controller_download_hook->downloadDunningDocumentAction';

?>