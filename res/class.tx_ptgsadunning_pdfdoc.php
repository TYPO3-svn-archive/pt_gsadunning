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
 * Reminder PDF Generator class for GSA transactions
 *
 * $Id: class.tx_ptgsadunning_pdfdoc.php,v 1.12 2008/11/07 12:52:53 ry37 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>
 * @since   2008-09-25
 */ 



/**
 * Inclusion of TYPO3 resources
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_assert.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_cliHandler.php'; // CLI handler class with general CLI methods
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_finance.php';
require_once t3lib_extMgm::extPath('pt_gsauserreg').'res/class.tx_ptgsauserreg_customer.php';
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_lib.php';  // GSA Shop library with static methods
require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_document.php';
require_once t3lib_extMgm::extPath('pt_gsaaccounting').'res/class.tx_ptgsaaccounting_erpDocument.php';
require_once t3lib_extMgm::extPath('pt_gsaaccounting').'res/class.tx_ptgsaaccounting_erpDocumentCollection.php';


/**
 * Reminder PDF Generator class
 *  
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-09-25
 * @package     TYPO3
 * @subpackage  tx_ptgsadunning
 */
class tx_ptgsadunning_pdfdoc extends tx_ptgsapdfdocs_document {
    
    /**
     * @var string  document type
     */
    protected $documenttype = 'reminder';
    
    /**
     * @var tx_ptgsauserreg_customer
     */
    protected $customerObj;
    
    /**
     * @var tx_ptgsaaccounting_erpDocumentCollection    the customer's documents with outstanding items (type bank transfer only) containing the updated dunning information
     */
    protected $outstandingItemsCollectionObj;
    
    
    
    /***************************************************************************
     *   BUSINESS LOGIC METHODS
     **************************************************************************/
    
    /**
     * Fill the marker array
     *
     * @param   language    TYPO3 language object (definded in sysext/lang/lang.php) to use
     * @return  tx_ptgsadunning_pdfdoc  $this
     * @throws  tx_pttools_exception    if no valid GSA customer set
     * @throws  tx_pttools_exception    if no outstanding items set for customer
     * @throws  tx_pttools_exception    if basic GSA shop config not found
     * @throws  tx_pttools_exception    if basic GSA pdfdoc config not found
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-09-25
     */
    public function fillMarkerArray(language $langObj) {
        
        tx_pttools_assert::isInstanceOf($this->customerObj, 'tx_ptgsauserreg_customer', array('message'=>'No valid GSA customer set.'));
        tx_pttools_assert::isNotEmptyObjectCollection($this->outstandingItemsCollectionObj, array('message'=>'No outstanding items set for customer'));
        
        // retrieve basic GSA shop config
        $shopConfigArr = tx_ptgsashop_lib::getGsaShopConfig();
        $pdfdocsConfigArr = tx_pttools_div::typoscriptRegistry('config.pt_gsapdfdocs.', NULL, 'pt_gsapdfdocs', 'tsConfigurationPid');
        tx_pttools_assert::isArray($shopConfigArr, array('message'=>'Basic GSA shop config not found'));
        tx_pttools_assert::isArray($pdfdocsConfigArr, array('message'=>'Basic GSA pdfdoc config not found'));
        
        $maxDunningLevel = $this->outstandingItemsCollectionObj->getMaxDunningLevel();
        
        // general markers
        $this->markerArray['shopOperatorContact'] = array(
            'name'        => $shopConfigArr['shopOperatorName'],
            'streetNo'    => $shopConfigArr['shopOperatorStreetNo'],
            'zip'         => $shopConfigArr['shopOperatorZip'],
            'city'        => $shopConfigArr['shopOperatorCity'],
            'countryCode' => $shopConfigArr['shopOperatorCountryCode'],
            'email'       => $shopConfigArr['shopOperatorEmail'],
        );
        $this->markerArray['backgroundPdf'] = $pdfdocsConfigArr['additionalMarkers.']['backgroundPdf'];
        $this->markerArray['creator'] = $pdfdocsConfigArr['additionalMarkers.']['creator'];
        $this->markerArray['date'] = date('d.m.Y');
        $this->markerArray['contactPhoneNumber'] = $pdfdocsConfigArr['additionalMarkers.']['contactPhoneNumber'];
        
        // customer specific markers, dunning markers
        $this->markerArray['subject'] = $langObj->sL('LLL:'.$this->get_languageFile().':subject_level'.(string)$maxDunningLevel);
        $this->markerArray['introText'] = $langObj->sL('LLL:'.$this->get_languageFile().':introText_level'.(string)$maxDunningLevel);
        $this->markerArray['customerAddress'] = $this->customerObj->getAddressLabel("\n", 0);
        $this->markerArray['outstandingItems'] = array();
        
        foreach ($this->outstandingItemsCollectionObj as $docId=>$erpDocumentObj) {
            /* @var $erpDocumentObj    tx_ptgsaaccounting_erpDocument() */
            $this->markerArray['outstandingItems'][$docId]['relatedDocNo'] = $erpDocumentObj->get_relatedDocNo();
            $this->markerArray['outstandingItems'][$docId]['date'] = tx_pttools_div::convertDate($erpDocumentObj->get_date(), 1);
            $this->markerArray['outstandingItems'][$docId]['amountGross'] = tx_pttools_finance::getFormattedPriceString($erpDocumentObj->get_amountGross(), $shopConfigArr['currencyCode']);
            $this->markerArray['outstandingItems'][$docId]['oustandingAmount'] = tx_pttools_finance::getFormattedPriceString($erpDocumentObj->getOutstandingAmount(), $shopConfigArr['currencyCode']);
            $this->markerArray['outstandingItems'][$docId]['isDue'] = $erpDocumentObj->isDue();  // here we check the original due date (and do not consider any dunning due dates)
            $this->markerArray['outstandingItems'][$docId]['dueDate'] = tx_pttools_div::convertDate($erpDocumentObj->getDueDate(), 1);
            $this->markerArray['outstandingItems'][$docId]['dunningLevel'] = $erpDocumentObj->get_dunningLevel();
            $this->markerArray['outstandingItems'][$docId]['dunningDueDate'] = tx_pttools_div::convertDate($erpDocumentObj->get_dunningDueDate(), 1);
            $this->markerArray['outstandingItems'][$docId]['dunningCharge'] = tx_pttools_finance::getFormattedPriceString($erpDocumentObj->get_dunningCharge(), $shopConfigArr['currencyCode']);
        }
                
        return $this;
        
    }

    /**
     * Returns a download URL for the dunning document (passed the eID script)
     *
     * @param   integer     dunning document id (UID of DB table tx_ptgsapdfdocs_documents) of the document to get its URL
     * @return  string      URL for the download of the dunning document via eID script
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-10-20
     */
    public static function getDownloadUrl($documentId) {
    
        return 'index.php?eID=tx_ptgsapdfdocs_download&download[action]=downloadDunningDocument&download[ddid]='.urlencode($documentId);
        
    }
    
    
    
    /***************************************************************************
     *   PROPERTY GETTER/SETTER METHODS
     **************************************************************************/
    
    /**
     * Sets the property value
     * 
     * @param   tx_ptgsauserreg_customer    customer object to handle
     * @return  tx_ptgsadunning_pdfdoc      $this
     * @since   2008-09-25
     */
    public function set_customerObj(tx_ptgsauserreg_customer $customerObj) {

        $this->customerObj = $customerObj;
        
        return $this;
        
    }
    
    /**
     * Sets the property value
     * 
     * @param   tx_ptgsaaccounting_erpDocumentCollection    the customer's documents with outstanding items (type bank transfer only) containing the updated dunning information (thus it should not be read from database)
     * @return  tx_ptgsadunning_pdfdoc      $this
     * @since   2008-10-01
     */
    public function set_outstandingItemsCollectionObj(tx_ptgsaaccounting_erpDocumentCollection $outstandingItemsCollectionObj) {

        $this->outstandingItemsCollectionObj = $outstandingItemsCollectionObj;
        
        return $this;
        
    }
    
    
    
}



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsadunning/res/class.tx_ptgsadunning_pdfdoc.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsadunning/res/class.tx_ptgsadunning_pdfdoc.php']);
}

?>