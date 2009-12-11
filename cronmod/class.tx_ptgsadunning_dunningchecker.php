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
 * Command Line Interface dunning checker for GSA transactions
 *
 * $Id: class.tx_ptgsadunning_dunningchecker.php,v 1.21 2009/12/11 11:10:24 ry37 Exp $
 *
 * @author	Rainer Kuhn <kuhn@punkt.de>
 * @since   2008-09-22
 */ 



/**
 * Inclusion of TYPO3 resources
 */
require_once t3lib_extMgm::extPath('lang').'lang.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_cliHandler.php'; // CLI handler class with general CLI methods
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_assert.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_smartyAdapter.php';
require_once t3lib_extMgm::extPath('pt_mail').'res/class.tx_ptmail_mail.php';
require_once t3lib_extMgm::extPath('pt_mail').'res/class.tx_ptmail_address.php';
require_once t3lib_extMgm::extPath('pt_mail').'res/class.tx_ptmail_addressCollection.php';
require_once t3lib_extMgm::extPath('pt_mail').'res/class.tx_ptmail_attachment.php';
require_once t3lib_extMgm::extPath('pt_mail').'res/class.tx_ptmail_attachmentCollection.php';
require_once t3lib_extMgm::extPath('pt_gsauserreg').'res/class.tx_ptgsauserreg_customer.php';
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_lib.php';  // GSA Shop library with static methods
require_once t3lib_extMgm::extPath('pt_gsaaccounting').'res/class.tx_ptgsaaccounting_dueCustomerCollection.php';
require_once t3lib_extMgm::extPath('pt_gsaaccounting').'res/class.tx_ptgsaaccounting_erpDocument.php';
require_once t3lib_extMgm::extPath('pt_gsaaccounting').'res/class.tx_ptgsaaccounting_erpDocumentCollection.php';
require_once t3lib_extMgm::extPath('pt_gsadunning').'res/class.tx_ptgsadunning_pdfdoc.php';



/**
 * Command Line Interface Class for processing a dunning check of GSA transactions
 *
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-09-22
 * @package     TYPO3
 * @subpackage  tx_ptgsadunning
 */
class tx_ptgsadunning_dunningchecker {
    
    
    /***************************************************************************
    *   CLASS PROPERTIES
    ***************************************************************************/

    /**
     * @var string  the extension key
     */
    protected $extKey = 'pt_gsadunning';
    
    /**
     * @var string  this script's name
     */
    protected $scriptName = 'tx_ptgsadunning_dunningchecker'; 
    
    /**
     * @var array   this extension's configuration data
     */
    protected $conf = array();
    
    /**
     * @var array   basic GSA shop configuration data
     */
    protected $shopConfigArr = array();
    
    /**
     * @var tx_pttools_cliHandler  CLI handler class with general CLI methods
     */
    protected $cliHandler;
    
    /**
     * @var language    TYPO3 language object (definded in sysext/lang/lang.php)
     */
    protected $lang;
    
    /**
     * @var string  language key ('default' or two character string, e.g. 'de') to use for localization of this scripts actions (e.g. dunning emails)
     */
    protected $languageKey = '';
    
    /**
     * @var tx_pttools_smartyAdapter    smarty object
     */
    protected $smarty;
    
    /**
     * @var array   smarty configuration data
     */
     protected $smartyConf = array();
    
    
    
    /***************************************************************************
    *   CONSTRUCTOR & RUN METHOD
    ***************************************************************************/
    
    /**
     * Class constructor: define CLI options, set class properties 
     *
     * @param   void
     * @return  void
     * @throws  tx_pttools_exception    if basic GSA shop config not found
     * @throws  tx_pttools_exception    if language file not found
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-09-22
     */
    public function __construct() {
            
        // for TYPO3 3.8.0+: enable storage of last built SQL query in $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery for all query building functions of class t3lib_DB
        $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
        
        // start script output
        echo "\n".
             "---------------------------------------------------------------------\n".
             "CLI Dunning Checker initialized...\n".
             "---------------------------------------------------------------------\n";
            
        // get extension configuration configured in Extension Manager (from localconf.php) - NOTICE: this has to be placed *before* the first call of $this->cliHandler->cliMessage()!!
        $this->conf = tx_pttools_div::returnExtConfArray($this->extKey);
        if (!is_array($this->conf)) {
            fwrite(STDERR, "[ERROR] No extension configuration found!\nScript terminated.\n\n");
            die();
        }
        
        // retrieve basic GSA shop config
        $this->shopConfigArr = tx_ptgsashop_lib::getGsaShopConfig();
        tx_pttools_assert::isArray($this->shopConfigArr, array('message'=>'Basic GSA shop config not found'));
        
        // intialize locallang features for CLI mode (requires faketsfe!)
        tx_pttools_assert::isFilePath($this->conf['languageFile'], array('message' => 'languageFile not found in "'.$this->conf['languageFile'].'"'));
        $this->languageKey = 'de';   // TODO: set language key (two character string or 'default') depending on GSA customer's language
        $this->lang = t3lib_div::makeInstance('language');
        $this->lang->init($this->languageKey); 
        $this->lang->charSet = tx_pttools_div::getSiteCharsetEncoding();
        
        // intialize smarty
        $this->smartyConf = array(
            'compile_dir'  => PATH_site.'typo3temp/smarty_compile',
            'cache_dir'    => PATH_site.'typo3temp/smarty_cache',
            't3_languageFile' => $this->conf['languageFile'],
            't3_languageKey'  => $this->languageKey
        );
        $this->smarty = new tx_pttools_smartyAdapter($this, $this->smartyConf);
        
        // invoke CLI handler with extension configuration
        $this->cliHandler = new tx_pttools_cliHandler($this->scriptName, 
                                                      $this->conf['cliAdminEmailRecipient'],
                                                      $this->conf['cliHostName'],
                                                      $this->conf['cliQuietMode'],
                                                      $this->conf['cliEnableLogging'],
                                                      $this->conf['cliLogDir']
                                                     );
        $this->cliHandler->cliMessage('Script initialized', false, 2, true); // start new audit log entry
        $this->cliHandler->cliMessage('$this->conf = '.print_r($this->conf, 1));
        
        // dev only
        #fwrite(STDERR, "[TRACE] died: STOP \n\n"); die();
            
    }
    
    /**
     * Run method of the CLI class: executes the business logic
     *
     * @param   void
     * @return  void
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-09-22
     */
    public function run() {
        
        try {
            
            $this->processDunningRun();
            
            
        } catch (tx_pttools_exception $excObj) {
            
            // if an exception has been catched, handle it and display error message
            $this->cliHandler->cliMessage($excObj->__toString(), true, 1);
            
        }
        
    }
    
    
    
    /***************************************************************************
    *   BUSINESS LOGIC METHODS
    ***************************************************************************/
    
    /** 
     * Processes a dunning run for all due customers
     *
     * @param   void    
     * @return  void
     * @throws  tx_pttools_exception    if no outstanding items found for a processed customer
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-09-23
     */
    protected function processDunningRun() {
        
        $this->cliHandler->cliMessage('Processing dunning run...', 0, 1);
        
        // retrieve all customers with 1) minimum one due outstanding item 2) of payment type invoice (=bank transfer/on account) 3) is "dunnable" (adds GSA-DB DEBITOR.MAHNTAGE to document's duePeriod or checks dunning level) 
        $dueCustomerCollectionObj = new tx_ptgsaaccounting_dueCustomerCollection('bt', true);
        $this->cliHandler->cliMessage('count($dueCustomerCollectionObj) = '.count($dueCustomerCollectionObj));
        
        // for each due customer: retrieve all outstanding items, update dunning parameters for customer and due invoices, generate dunning PDF, send dunning mail and store updated data to GSA DB
        foreach ($dueCustomerCollectionObj as $customerObj) {
            /* @var $customerObj    tx_ptgsauserreg_customer() */
            
            // process dunning for customer if the default dunning method is set for this customer
            if ($customerObj->get_gsa_dunningMethod() == tx_ptgsauserreg_customer::DM_NORMAL) {
                
                $this->cliHandler->cliMessage("\n".'Processing customer "'.$customerObj->getFullName().'":', 0, 1);
            
              #if ($customerObj->get_lastname() == "Mahn") {  // ### TODO: Dev only ###
                
                // retrieve all outstanding items (type bank transfer only) for the customer
                $outstandingItemsCollectionObj = new tx_ptgsaaccounting_erpDocumentCollection(true, $customerObj->get_gsauid(), 'bt');
                tx_pttools_assert::isNotEmptyObjectCollection($outstandingItemsCollectionObj, array('message'=>'No outstanding items found for customer "'.$customerObj->getFullName().'"'));
                $this->cliHandler->cliMessage('count($outstandingItemsCollectionObj) = '.count($outstandingItemsCollectionObj));
                $this->cliHandler->cliMessage('$outstandingItemsCollectionObj = '.print_r($outstandingItemsCollectionObj, 1));
                    
                // update dunning parameters in objects (updated params are required for generating the correct dunning PDF file!)
                $this->updateDunningParameters($customerObj, $outstandingItemsCollectionObj);
                
                // generate dunning PDF file
                $absPdfPath = $this->generateDunningDocument($customerObj, $outstandingItemsCollectionObj);
                
                // send dunning mail including PDF file attachment
                $this->sendDunningMail($customerObj, $outstandingItemsCollectionObj->getMaxDunningLevel(), $absPdfPath);
                
                // store updated objects (with changed dunning parameters) into database
                $this->storeUpdatedDunningData($customerObj, $outstandingItemsCollectionObj); ### TODO: outcomment this line for dev only purposes ### 
                
              #} // ### TODO: Dev only ###
              
            }
        }
        
        $this->cliHandler->cliMessage("\n".'Dunning run completed!', 0, 1);
    }
    
    /** 
     * Updates the dunning parameters for a given customer and this customer's due invoices (with dunning level lower than 3)
     *
     * @param   tx_ptgsauserreg_customer                    customer to handle
     * @param   tx_ptgsaaccounting_erpDocumentCollection    the customer's documents with outstanding items to set the updated dunning parameters to
     * @return  void
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-09-23
     */
    protected function updateDunningParameters(tx_ptgsauserreg_customer $customerObj, tx_ptgsaaccounting_erpDocumentCollection $outstandingItemsCollectionObj) {
        
        $this->cliHandler->cliMessage('Updating dunning parameters for customer and invoices in session objects...', 0, 1);
                
        $newDunningDate = tx_pttools_div::dateToday();
        
        // set new dunning parameters for customer
        $customerObj->set_gsa_dunningLastDate($newDunningDate);
        
        // set new dunning parameters for the customer's due invoices (due check including dunning due date) with dunning level lower than 3 only
        foreach ($outstandingItemsCollectionObj as $erpDocumentObj) {
            
            $this->cliHandler->cliMessage('  Processing document "'.$erpDocumentObj->get_relatedDocNo().'"...');
            
            if ($erpDocumentObj->isDue(true) && $erpDocumentObj->get_dunningLevel() < 3) {   // here we check the "dunning including" due date (consider dunning due dates, too)
                
                /* @var $erpDocumentObj  tx_ptgsaaccounting_erpDocument(); */
                $newDunningLevel = $erpDocumentObj->get_dunningLevel() + 1;
                $customerDunningChargeGetter = 'get_gsa_dunningCharge'.(string)$newDunningLevel;
                $newDunningCharge = $customerObj->$customerDunningChargeGetter(); // method call results e.g. in $customerObj->get_gsa_dunningCharge1()
                $dunningDueDateTs = time() + (($customerObj->get_gsa_dunningDays() + 1) * 24 * 60 * 60);  // $customerObj->get_gsa_dunningDays() relates to GSA-DB DEBITOR.MAHNTAGE; ERP adds one day to this value and todays date
                $newDunningDueDate = date('Y-m-d', $dunningDueDateTs);
                
                $erpDocumentObj->set_dunningLevel($newDunningLevel);
                $this->cliHandler->cliMessage('    Dunning level set to: '.$newDunningLevel);
                $erpDocumentObj->set_dunningCharge($newDunningCharge); 
                $this->cliHandler->cliMessage('    Dunning charge set to: '.$newDunningCharge);
                $erpDocumentObj->set_dunningDate($newDunningDate);
                $this->cliHandler->cliMessage('    Last dunning date set to: '.$newDunningDate);
                $erpDocumentObj->set_dunningDueDate($newDunningDueDate);
                $this->cliHandler->cliMessage('    Dunning due date set to: '.$newDunningDueDate);
                
            }
        }
    }
    
    /** 
     * Generates a PDF reminder document with all outstanding items of a given customer
     *
     * @param   tx_ptgsauserreg_customer                    customer to handle
     * @param   tx_ptgsaaccounting_erpDocumentCollection    the customer's documents with outstanding items containing the updated dunning information
     * @return  string      absolute file path to the newly generated dunning document PDF file
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-09-23
     */
    protected function generateDunningDocument(tx_ptgsauserreg_customer $customerObj, tx_ptgsaaccounting_erpDocumentCollection $outstandingItemsCollectionObj) { 
        
        $this->cliHandler->cliMessage('Generating reminder PDF document...', 0, 1);
        
        // set document storage path
        $replace = array(
            '###GSAUID###' => $customerObj->get_gsauid(),
            '###GSAUIDMOD10###' => $customerObj->get_gsauid() % 10, 
            '###GSAUIDMOD100###' => $customerObj->get_gsauid() % 100,
            '###GSAUIDMOD1000###' => $customerObj->get_gsauid() % 1000,
            '###DAY###' => strftime('%d'),
            '###MONTH###' => strftime('%m'),
            '###YEAR###' => strftime('%Y'),
        );
        $path = str_replace(array_keys($replace), array_values($replace), $this->conf['reminderPdfStoragePath']);
        $absolutePath = PATH_site.$path;
        $this->cliHandler->cliMessage('PDF Storage Path: '.$absolutePath);
        t3lib_div::mkdir_deep(PATH_site, dirname($path));
        
        // process additional markers // TODO: add additional markers??
        $additionalMarkers = array();
        
        // generate PDF reminder document
        $pdfDoc = new tx_ptgsadunning_pdfdoc();
        $pdfDoc
            ->set_customerObj($customerObj)
            ->set_outstandingItemsCollectionObj($outstandingItemsCollectionObj)
            ->set_languageFile($this->conf['languageFile'])
            ->set_languageKey($this->languageKey)
            ->fillMarkerArray($this->lang)
            ->set_xmlSmartyTemplate($this->conf['reminderPdfTemplate'])
            ->addMarkers($additionalMarkers)   
            ->createXml($this->smartyConf)
            ->renderPdf($absolutePath);  // absolute paths are necessary for CLI mode!
                        
        // save document info to database
        $pdfDoc->set_gsaCustomerId($customerObj->get_gsauid());
        $pdfDoc->storeSelf();
        
        return $absolutePath;
        
    }
    
    /** 
     * Sends a dunning mail (containing a link to the detailled PDF document on the server) to the given customer
     *
     * @param   tx_ptgsauserreg_customer    customer to handle
     * @param   integer     maximal dunnuing level of the due documents of the customer
     * @param   string      absolute file path to the reminder document PDF file to attach to the mail
     * @return  void
     * @throws  tx_pttools_exception    if no email recipient given
     * @throws  tx_pttools_exception    if a mail body template error occurs
     * @throws  tx_pttools_exception    if mail sending error occurs
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-09-23
     */
    protected function sendDunningMail(tx_ptgsauserreg_customer $customerObj, $maxDunningLevel, $absPdfPath) {
        
        $mailRecipient = $customerObj->get_email1();
        tx_pttools_assert::isNotEmptyString($mailRecipient, array('message'=>'No email recipient found in customer data.'));
        $this->cliHandler->cliMessage('Sending reminder email to "'.$mailRecipient.'"...', 0, 1);
        
        // initiate email and set extension specific mail data
        $mailObj = new tx_ptmail_mail();
        $mailObj->set_templateCharset($this->conf['dunningEmailTemplateCharset']);
        $mailObj->set_to(new tx_ptmail_addressCollection(new tx_ptmail_address('', '', $customerObj)));  // this uses internally $customerObj->get_email1();
        $mailObj->set_subject($this->lang->sL('LLL:'.$this->conf['languageFile'].':subject_level'.(string)$maxDunningLevel));
        $mailObj->set_body($this->returnReminderMailBody($customerObj, $maxDunningLevel));
                                                                                           
        // overwrite basic pt_mail config if individual dunning config is set in EM
        if (!empty($this->conf['dunningEmailSender'])) {
            $mailObj->set_from(new tx_ptmail_address($this->conf['dunningEmailSender']));
        }
        if (!empty($this->conf['dunningEmailReplyTo'])) {
            $mailObj->set_reply(new tx_ptmail_address($this->conf['dunningEmailReplyTo']));
        }
        
        // add PDF attachment
        $attachmentsCollObj = new tx_ptmail_attachmentCollection();
        $attachmentsCollObj->addItem(new tx_ptmail_attachment($absPdfPath));
        $mailObj->set_attachments($attachmentsCollObj);  
        
        // send email
        $mailObj->sendMail();
        
    }
    
    /** 
     * Generates the dunning mail body from the template and returns it as plain text
     *
     * @param   tx_ptgsauserreg_customer    customer to handle
     * @param   integer     maximal dunnuing level of the due documents of the customer
     * @return  string  the dunning mail body as plain text
     * @throws  tx_pttools_exception    if template file for dunning mail body not found
     * @throws  tx_pttools_exception    if mail body could not be generated
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-09-24
     */
    protected function returnReminderMailBody(tx_ptgsauserreg_customer $customerObj, $maxDunningLevel) {
        
        $markerArray = array();
        
        // retrieve additional config
        $pdfdocsConfigArr = tx_pttools_div::typoscriptRegistry('config.pt_gsapdfdocs.', NULL, 'pt_gsapdfdocs', 'tsConfigurationPid');
        tx_pttools_assert::isArray($pdfdocsConfigArr, array('message'=>'Basic GSA pdfdoc config not found'));
        
        // set markers
        $markerArray['customerName'] = $customerObj->getFullName();
        $markerArray['customerId'] = $customerObj->get_gsauid(); // TODO: do we need this? use $customerObj->get_gsa_kundnr() here?
        $markerArray['introText'] = $this->lang->sL('LLL:'.$this->conf['languageFile'].':introText_level'.(string)$maxDunningLevel);
        $markerArray['shopName'] = $this->shopConfigArr['shopName'];
        $markerArray['contactEmail'] = $this->shopConfigArr['shopOperatorEmail'];
        $markerArray['contactPhoneNumber'] = $pdfdocsConfigArr['additionalMarkers.']['contactPhoneNumber'];
        
        foreach ($markerArray as $markerKey=>$markerValue) {
            $this->smarty->assign($markerKey, $markerValue);
        }
        
        $templateFile = substr(PATH_thisScript, 0, (strrpos(PATH_thisScript, "/")+1)).$this->conf['dunningEmailBodyTemplate'];
        if (!@is_file($templateFile) || !@is_readable($templateFile)) {
            throw new tx_pttools_exception('Smarty template not found in "'.$templateFile.'"'); // tx_pttools_assert::isFilePath fails with URLs containing '..'
        }
        $this->cliHandler->cliMessage('Template File Email: '.$templateFile);
        $mailBody = $this->smarty->fetch('file:'.$templateFile);
        tx_pttools_assert::isNotEmptyString($mailBody, array('message' => 'Could not generate mail body.'));
        
        return $mailBody;
        
    }
    
    /** 
     * Stores the updated objects (with changed dunning parameters) for a given customer and this customer's due invoices into the GSA database
     *
     * @param   tx_ptgsauserreg_customer                    customer to handle
     * @param   tx_ptgsaaccounting_erpDocumentCollection    the customer's documents with outstanding items
     * @return  void
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-10-02
     */
    protected function storeUpdatedDunningData(tx_ptgsauserreg_customer $customerObj, tx_ptgsaaccounting_erpDocumentCollection $outstandingItemsCollectionObj) {
    
        $this->cliHandler->cliMessage('Storing updated session objects (customer and invoices) to GSA DB...', 0, 1);
        
        $customerObj->storeSelf();
        foreach ($outstandingItemsCollectionObj as $erpDocumentObj) {
                $erpDocumentObj->storeSelf();
        }
        
    }
    
    
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsadunning/cronmod/class.tx_ptgsadunning_dunningchecker.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsadunning/cronmod/class.tx_ptgsadunning_dunningchecker.php']);
}

?>