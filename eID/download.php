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
 * Download handler for eID calls
 * 
 * @version     $Id: download.php,v 1.1 2008/10/20 15:56:34 ry37 Exp $
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-10-20
 */

// Exit, if script is called directly (must be included via eID in index_ts.php)
if (!defined ('PATH_typo3conf')) {
    die ('Could not access this script directly!');
}

require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'controller/class.tx_ptgsapdfdocs_controller_download.php';

$controller = new tx_ptgsapdfdocs_controller_download();

echo $controller->main();

?>