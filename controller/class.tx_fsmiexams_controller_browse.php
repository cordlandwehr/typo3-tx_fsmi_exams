<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2012  Andreas Cord-Landwehr <cola@uni-paderborn.de>
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
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('lang').'lang.php');

require_once(t3lib_extMgm::extPath('fsmi_exams').'api/class.tx_fsmiexams_div.php');
require_once(t3lib_extMgm::extPath('fsmi_exams').'view/class.tx_fsmiexams_listview.php');
require_once(t3lib_extMgm::extPath('fsmi_exams').'view/class.tx_fsmiexams_base_view_user.php');
require_once(t3lib_extMgm::extPath('fsmi_exams').'view/class.tx_fsmiexams_module_aggregation.php');
require_once(t3lib_extMgm::extPath('fsmi_exams').'view/class.tx_fsmiexams_folderview.php');

/**
 * Plugin 'Exam List' for the 'fsmi_exams' extension.
 *
 * @author	Andreas Cord-Landwehr <cola@uni-paderborn.de>
 * @package	TYPO3
 * @subpackage	tx_fsmiexams
 */
class tx_fsmiexams_controller_browse extends tslib_pibase {
	var $prefixId      = 'tx_fsmiexams_browse';		// Same as class name
	var $scriptRelPath = 'controller/class.tx_fsmiexams_controller_browse.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'fsmi_exams';	// The extension key.
	var $viewObj;

	// flexform values
	var $pidEditPage   = 0;
	var $allowedGroupsEdit 		= 0;
	var $allowedGroupsDownload	= 0;
	var $allowedGroupsPrint		= 0;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		$this->pi_initPIflexForm(); // Init and get the flexform data of the plugin

		// select input type
		$GETcommands = t3lib_div::_GP($this->extKey);	// can be both: POST or GET

		switch (intval($GETcommands['type'])) {
			case tx_fsmiexams_base_view_user::kVIEW_TYPE_LIST: {
				$this->viewObj = t3lib_div::makeInstance(tx_fsmiexams_listview);
				break;
			}
			case tx_fsmiexams_base_view_user::kVIEW_TYPE_AGGREGATION: {
				$this->viewObj = t3lib_div::makeInstance(tx_fsmiexams_module_aggregation);
				break;
			}
			case tx_fsmiexams_base_view_user::kVIEW_TYPE_FOLDERVIEW: {
				$this->viewObj = t3lib_div::makeInstance(tx_fsmiexams_folderview);
				break;
			}
			default:
				$this->viewObj = t3lib_div::makeInstance(tx_fsmiexams_module_aggregation);
		}

		// get Edit information
		$this->pidEditPage = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pidEdit'));
		$this->allowedGroupsEdit = tx_fsmiexams_div::getGroupUIDsRightsEdit();
		$this->allowedGroupsDownload = tx_fsmiexams_div::getGroupUIDsRightsDownload();
		$this->allowedGroupsPrint = tx_fsmiexams_div::getGroupUIDsRightsPrint();

		// case of listview
		$this->viewObj->init($this, $this->pidEditPage, $this->allowedGroupsEdit, $this->allowedGroupsDownload, $this->allowedGroupsPrint);

		$content .= $this->viewObj->switchViewMenu();
		$content .= $this->viewObj->listMenuBreadcrumb();
		$content .= $this->viewObj->listAllExams();

		return $this->pi_wrapInBaseClass($content);
	}


}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/controller/class.tx_fsmiexams_controller_browse.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/controller/class.tx_fsmiexams_controller_browse.php']);
}

?>