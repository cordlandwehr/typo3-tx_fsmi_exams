<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Andreas Cord-Landwehr (cola@uni-paderborn.de)
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
* This class provides the list view for browsing exams
*
* @author Andreas Cord-Landwehr <cola@uni-paderborn.de>
*/



require_once(PATH_t3lib.'class.t3lib_befunc.php');
require_once(PATH_t3lib.'class.t3lib_tcemain.php');
require_once(PATH_t3lib.'class.t3lib_iconworks.php');

require_once(t3lib_extMgm::extPath('fsmi_exams').'api/class.tx_fsmiexams_div.php');

/**
 * Base view for all users. This shall be extended by specific view (list, aggregated...)
 *
 */
class tx_fsmiexams_base_view_user extends tslib_pibase {
	const kVIEW_TYPE_LIST			= 1;
	const kVIEW_TYPE_AGGREGATION	= 2;
	const kVIEW_TYPE_FOLDERVIEW		= 3;
	const kVIEW_TYPE_LECTURERVIEW	= 4;
	const imgPath			= 'typo3conf/ext/fsmi_exams/images/'; // absolute path to images
	const extKey			= 'fsmi_exams';

	var $LANG;						// language object
	var $cObj;
	var $examDiv;

	protected $pidEditPage 		= 0;	// PID for edit functions
	protected $rightsEdit		= false;
	protected $rightsDownload	= false;
	protected $rightsPrint		= false;

	function init($cObj, $pidEditPage, $allowedGroupsEdit, $allowedGroupsDownload, $allowedGroupsPrint) {
		// edit rights
		$this->rightsEdit = $this->isUserAllowedToEdit($allowedGroupsEdit);
		if ($this->rightsEdit)
			$this->pidEditPage = $pidEditPage;
		else
			$this->pidEditPage = 0;
		// Download rights
		$this->rightsDownload = $this->isUserAllowedToDownload($allowedGroupsDownload);

		// Printing rights
		$this->rightsPrint = $this->isUserAllowedToPrint($allowedGroupsPrint);

		// crucial point: set up cObj
		$this->cObj = $cObj;
	}

	/**
	 * This function provides a selector for the different views.
	 * at the moment it does not preserve previous selections
	 */
	function switchViewMenu() {
 		$this->cObj = t3lib_div::makeInstance('tslib_cObj');	// TODO need to check!
		$content = '';

		$content .= '<div style="text-align:right; font-weight:bold;">';
		$content .= $this->pi_linkTP($this->LANG->getLL("tt_content.list_type_browse.list"),
						array (	self::extKey.'[type]' => self::kVIEW_TYPE_LIST));
		$content .= ' / ';
		$content .= $this->pi_linkTP($this->LANG->getLL("tt_content.list_type_browse.aggregated"),
				array (	self::extKey.'[type]' => self::kVIEW_TYPE_AGGREGATION));
		$content .= ' / ';
		$content .= $this->pi_linkTP($this->LANG->getLL("tt_content.list_type_browse.folderview"),
				array (	self::extKey.'[type]' => self::kVIEW_TYPE_FOLDERVIEW));
		$content .= '</div>';

		return $content;
	}

	function tx_fsmiexams_listview () {
		$this->LANG = t3lib_div::makeInstance('language');
		$this->LANG->init($GLOBALS['TSFE']->tmpl->setup['config.']['language']);
		$this->LANG->includeLLFile('typo3conf/ext/fsmi_exams/locallang_db.xml');
	}

	/**
	 * This function outputs a list with anchors to all degree programs.
	 */
	function listMenuBreadcrumb() {
		$content = '';

		$resProgram = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
												FROM tx_fsmiexams_degreeprogram
												WHERE deleted=0 AND hidden=0');
		while ($resProgram && $rowProgram = mysql_fetch_assoc($resProgram)) {
			$content .= '<a href="index.php?id='.$GLOBALS['TSFE']->id.'#fsmiexams_degreeprogram_'.$rowProgram['uid'].'">'.$rowProgram['name'].'</a>';
			$content .= ' / ';
		}

		return $content;
	}

	/**
	 * Creates an array with key UID and value description of exam type.
	 * FIXME: use version in div-class
	 * \return array
	 */
	function listExamTypes () {
		$types = array ();

		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
													FROM tx_fsmiexams_examtype
													WHERE deleted=0 AND hidden=0');

		while ($res && $row = mysql_fetch_assoc($res))
			$types[$row['uid']] = $row['description'];

		return $types;
	}

	/**
	 * This function lists all exams ordered by degree program, part etc.
	 * \return HTML table
	 */
	function listAllExams () {
		$content = '';
		return $content;
	}

// TODO next three functions can be merged

	/**
	 * This function confirms if a user is really allowed to edit files.
	 * @param	array	$allowedGroups
	 * @return	boolean	indicates of user has specific right
	 */
	function isUserAllowedToEdit ($allowedGroups) {
		$userGroups = $GLOBALS['TSFE']->fe_user->groupData['uid'];

		foreach ($userGroups as $group) {
			if (in_array($group, $allowedGroups))
				return true;
		}
		return false;
	}

	/**
	 * This function confirms if a user is really allowed to download files.
	 * @param	array	$allowedGroups
	 * @return	boolean	indicates of user has specific right
	 */
	function isUserAllowedToDownload ($allowedGroups) {
		$userGroups = $GLOBALS['TSFE']->fe_user->groupData['uid'];

		foreach ($userGroups as $group) {
			if (in_array($group, $allowedGroups))
				return true;
		}
		return false;
	}

	/**
	 * This function confirms if a user is really allowed to print files.
	 * @param	array	$allowedGroups
	 * @return	boolean	indicates of user has specific right
	 */
	function isUserAllowedToPrint ($allowedGroups) {
		$userGroups = $GLOBALS['TSFE']->fe_user->groupData['uid'];

		foreach ($userGroups as $group) {
			if (in_array($group, $allowedGroups))
				return true;
		}
		return false;
	}


}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/view/class.tx_fsmiexams_base_view_user.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/view/class.tx_fsmiexams_base_view_user.php']);
}
?>
