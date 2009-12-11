<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2008 Rainer Kuhn (kuhn@punkt.de)
*  All rights reserved
*  
*  This script is part of the TYPO3 project. The TYPO3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*  
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*  
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * @version     $Id: class.tx_ptgsadunning_controller_download_hook.php,v 1.2 2008/10/20 15:57:51 ry37 Exp $
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-10-20
 */



require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/exceptions/class.tx_pttools_exceptionAuthentication.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_assert.php';
require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'controller/class.tx_ptgsapdfdocs_controller_download.php';



/**
 * Controller hook class for "tx_ptgsapdfdocs_controller_download"
 * 
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-10-20
 */
class tx_ptgsadunning_controller_download_hook extends tx_ptgsapdfdocs_controller_download {
    
    /**
     * Action method for downloading dunning documents ('downloadDunningDocument' controller action)
     *
     * @param   array     $params
     * @param   tx_ptgsapdfdocs_controller_download     calling parent object
     * @return  string    HTML Error message (should be returned in case of download error only)
     * @throws  tx_pttools_exceptionAuthentication  if currently logged-in user has no right to access the requested document
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-10-20
     */
    public function downloadDunningDocumentAction(array $params, tx_ptgsapdfdocs_controller_download $pObj) {
        
        // checking and preparing dunning doc download parameter
        trace($pObj->piVars, 0, '$pObj->piVars'); 
        tx_pttools_assert::isNotEmptyString($pObj->piVars['ddid'], array('message' => 'No valid "ddid" set!'));
        $dunningDocumentUid = urldecode($pObj->piVars['ddid']);
        
        // get document record data
        $documentRecordArray = tx_ptgsapdfdocs_documentAccessor::getInstance()->selectDocumentByUid($dunningDocumentUid);
        trace($documentRecordArray, 0, '$documentRecordArray');
        tx_pttools_assert::isArray($documentRecordArray, array('message' => 'No document found in database for this "ddid"!'));
        
        // check if currently logged-in user is allowed to download the requested file
        $feCustomer = new tx_ptgsauserreg_feCustomer($pObj->feUserObj->user['uid']);
        if ($documentRecordArray['gsa_customer_id'] != $feCustomer->get_gsaMasterAddressId()) {
            throw new tx_pttools_exceptionAuthentication('No access right for the requested document', 
                                                         'Currently logged-in user (uid "'.$pObj->feUserObj->user['uid'].'") is not allowed to access requested document '.
                                                         '(uid "'.$dunningDocumentUid.'", gsa_customer_id "'.$documentRecordArray['gsa_customer_id'].'")'
                                                        );
        } else {
            $pObj->_downloadFile(PATH_site . $documentRecordArray['file']);
        }
        
        return 'ERROR while downloading requested document!';
        
    }
    
    
    
}



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsadunning/res/class.tx_ptgsadunning_controller_download_hook.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsadunning/res/class.tx_ptgsadunning_controller_download_hook.php']);
}

?>