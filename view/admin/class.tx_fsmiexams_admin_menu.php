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

	// switch between views
	const kVIEW_CREATE						= 1;
	const kVIEW_LIST						= 2;

	// constants
//	const kEDIT_TYPE_NONE						= 0;
//	const kEDIT_TYPE_MODULE						= 1;
	const kEDIT_TYPE_EXAM						= 4;
	const kEDIT_TYPE_EXAM_CREATION_TRIGGERS		= 5;
	const kEDIT_TYPE_LECTURE					= 6;
	const kEDIT_TYPE_LECTURER					= 7;
	const kEDIT_TYPE_FOLDER_PRESELECT			= 8;
	const kEDIT_TYPE_FOLDER						= 9;
	const kCREATE_TYPE_FOLDER					= 10;
	const kEDIT_TYPE_FOLDER_SAVE				= 11;

	const kLIST_TYPE_FOLDER						= 1;
	const kLIST_TYPE_LECTURE					= 2;
	const kLIST_TYPE_LECTURER					= 3;
	//TODO some constants are called contrary to their meanings

	function __construct () {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->LANG = t3lib_div::makeInstance('language');
		// initialization procedure for language
		$this->LLkey = $GLOBALS['TSFE']->lang;
		$this->LOCAL_LANG_loaded = 0;
		$this->pi_loadLL();
	}


	function createAdminMenu ($view) {
		// type selection head
		$content = $this->menuViewModes($view);
		switch ($view) {
			case self::kVIEW_CREATE:
				$content .= $this->menuCreateTypes();
				break;
			case self::kVIEW_LIST:
				$content .= $this->menuListTypes();
				break;
		}

		return $content;
	}
		
	/**
	 * Creates menu to switch between
	 * - create
	 * - list
	 * @param	integer	$view	preselected view mode
	 * @return	string as HTML div
	 */
	function menuViewModes ($view = 0) {
		$content = '<div>';
		$content .= ($view==self::kVIEW_LIST
			? '<span style="font-weight: bold">': '');
		$content .= $this->pi_linkTP($this->pi_getLL("view_list"),
								array (	$this->extKey.'[view]' => self::kVIEW_LIST));
		$content .= '</span> | ';
		$content .= ($view==self::kVIEW_CREATE
			? '<span style="font-weight: bold">': '');
		$content .= $this->pi_linkTP($this->pi_getLL("view_create"),
								array (	$this->extKey.'[view]' => self::kVIEW_CREATE));
		$content .= '</span>';
		$content .= '</div>';

		return $content;
	}


	function menuCreateTypes () {
		$content = '<div>';
		$content .= $this->pi_linkTP($this->pi_getLL("option_new-lecture"),
								array (
									$this->extKey.'[view]' => self::kVIEW_CREATE,
									$this->extKey.'[type]' => self::kEDIT_TYPE_LECTURE
								));
		$content .= ' | ';
		$content .= $this->pi_linkTP($this->pi_getLL("option_new-exam"),
								array (
									$this->extKey.'[view]' => self::kVIEW_CREATE,
									$this->extKey.'[type]' => self::kEDIT_TYPE_EXAM
								));
		$content .= ' | ';
		$content .= $this->pi_linkTP($this->pi_getLL("option_new-lecturer"),
								array (
									$this->extKey.'[view]' => self::kVIEW_CREATE,
									$this->extKey.'[type]' => self::kEDIT_TYPE_LECTURER,
								));
		$content .= ' | ';
		$content .= $this->pi_linkTP($this->pi_getLL("option_new-folder"),
								array (
									$this->extKey.'[view]' => self::kVIEW_CREATE,
									$this->extKey.'[type]' => self::kEDIT_TYPE_FOLDER_PRESELECT
								));
		$content .= '</div>';

		return $content;
	}


	function menuListTypes () {
		$content = '<div>';
		$content .= $this->pi_linkTP($this->pi_getLL("option_edit-folder"),
								array (
									$this->extKey.'[view]' => self::kVIEW_LIST,
									$this->extKey.'[type]' => self::kLIST_TYPE_FOLDER
								));
		$content .= ' | ';
		$content .= $this->pi_linkTP($this->pi_getLL("option_edit-lecture"),
								array (
									$this->extKey.'[view]' => self::kVIEW_LIST,
									$this->extKey.'[type]' => self::kLIST_TYPE_LECTURE
								));
		$content .= ' | ';
		$content .= $this->pi_linkTP($this->pi_getLL("option_edit-lecturer"),
								array (
									$this->extKey.'[view]' => self::kVIEW_LIST,
									$this->extKey.'[type]' => self::kLIST_TYPE_LECTURER
								));
		$content .= '</div>';

		return $content;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/view/admin/class.tx_fsmiexams_admin_menu.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/view/admin/class.tx_fsmiexams_admin_menu.php']);
}

?>