<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Andreas Cord-Landwehr <fsmi@uni-paderborn.de>
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
require_once(t3lib_extMgm::extPath('fsmi_exams').'api/class.tx_fsmiexams_div.php');

/**
 * Admin Menu for Exams Extension
 *
 * @author	Andreas Cord-Landwehr <fsmi@uni-paderborn.de>
 * @package	TYPO3
 * @subpackage	tx_fsmiexams
 */
class tx_fsmiexams_admin_menu extends tslib_pibase {
	var $prefixId      = 'tx_fsmiexams_controller_admin';		// Same as class name
	var $scriptRelPath = 'controller/class.tx_fsmiexams_controller_admin.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'fsmi_exams';	// The extension key.
	var $pi_checkCHash = true;

	var $LANG;						// language object
	var $cObj;

	function __construct () {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->LANG = t3lib_div::makeInstance('language');
		// initialization procedure for language
		$this->LLkey = $GLOBALS['TSFE']->lang;
		$this->LOCAL_LANG_loaded = 0;
		$this->pi_loadLL();
	}


	function createAdminMenu ($type) {
		// type selection head
		$content = $this->printEditTypeOptions();
		$content .= $this->printCreateAction($type);
		return $content;
	}


	private function printEditTypeOptions () {
		$content = '<div>';
		$content .= $this->pi_linkTP($this->pi_getLL("option_lecture"),
								array (
									$this->extKey.'[view]' => tx_fsmiexams_controller_admin::kVIEW_LIST,
									$this->extKey.'[type]' => tx_fsmiexams_controller_admin::kLIST_TYPE_LECTURE
								));
		$content .= ' | ';
		$content .= $this->pi_linkTP($this->pi_getLL("option_exam"),
								array (
									$this->extKey.'[view]' => tx_fsmiexams_controller_admin::kVIEW_LIST,
									$this->extKey.'[type]' => tx_fsmiexams_controller_admin::kLIST_TYPE_EXAM
								));
		$content .= ' | ';
		$content .= $this->pi_linkTP($this->pi_getLL("option_lecturer"),
								array (
									$this->extKey.'[view]' => tx_fsmiexams_controller_admin::kVIEW_LIST,
									$this->extKey.'[type]' => tx_fsmiexams_controller_admin::kLIST_TYPE_LECTURER,
								));
		$content .= ' | ';
		$content .= $this->pi_linkTP($this->pi_getLL("option_folder"),
								array (
									$this->extKey.'[view]' => tx_fsmiexams_controller_admin::kVIEW_LIST,
									$this->extKey.'[type]' => tx_fsmiexams_controller_admin::kLIST_TYPE_FOLDER
								));
		$content .= '</div>';

		return $content;
	}


	private function printCreateAction($type) {
		if (!$type) {
			return;
		}
		$content = '<div style="font-weight:bold;margin:10px;"><h3>';
		
		if ($type==tx_fsmiexams_controller_admin::kEDIT_TYPE_LECTURE || $type==tx_fsmiexams_controller_admin::kLIST_TYPE_LECTURE) {
			$content .= $this->pi_linkTP($this->pi_getLL("option_new-lecture"),
									array (
										$this->extKey.'[view]' => tx_fsmiexams_controller_admin::kVIEW_CREATE,
										$this->extKey.'[type]' => tx_fsmiexams_controller_admin::kEDIT_TYPE_LECTURE
									));
		}
		if ($type==tx_fsmiexams_controller_admin::kEDIT_TYPE_EXAM || $type==tx_fsmiexams_controller_admin::kLIST_TYPE_EXAM) {
			$content .= $this->pi_linkTP($this->pi_getLL("option_new-exam"),
								array (
									$this->extKey.'[view]' => tx_fsmiexams_controller_admin::kVIEW_CREATE,
									$this->extKey.'[type]' => tx_fsmiexams_controller_admin::kEDIT_TYPE_EXAM
								));
		}
		if ($type==tx_fsmiexams_controller_admin::kEDIT_TYPE_LECTURER || $type==tx_fsmiexams_controller_admin::kLIST_TYPE_LECTURER) {
			$content .= $this->pi_linkTP($this->pi_getLL("option_new-lecturer"),
								array (
									$this->extKey.'[view]' => tx_fsmiexams_controller_admin::kVIEW_CREATE,
									$this->extKey.'[type]' => tx_fsmiexams_controller_admin::kEDIT_TYPE_LECTURER,
								));
		}
		if ($type==tx_fsmiexams_controller_admin::kEDIT_TYPE_FOLDER || $type==tx_fsmiexams_controller_admin::kLIST_TYPE_FOLDER) {
			$content .= $this->pi_linkTP($this->pi_getLL("option_new-folder"),
								array (
									$this->extKey.'[view]' => tx_fsmiexams_controller_admin::kVIEW_CREATE,
									$this->extKey.'[type]' => tx_fsmiexams_controller_admin::kEDIT_TYPE_FOLDER_PRESELECT
								));
		}
		$content .= '</h3></div>';

		return $content;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/view/admin/class.tx_fsmiexams_admin_menu.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/view/admin/class.tx_fsmiexams_admin_menu.php']);
}

?>