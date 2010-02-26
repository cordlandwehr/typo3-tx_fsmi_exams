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
* This class provides an aggregated list view.
*
* @author Andreas Cord-Landwehr <cola@uni-paderborn.de>
*/

require_once(PATH_t3lib.'class.t3lib_befunc.php');
require_once(PATH_t3lib.'class.t3lib_tcemain.php');
require_once(PATH_t3lib.'class.t3lib_iconworks.php');

require_once(t3lib_extMgm::extPath('fsmi_exams').'view/class.tx_fsmiexams_base_view_user.php');

/**
 * Script Class to download files as defined in reports
 *
 */
class tx_fsmiexams_module_aggregation extends tx_fsmiexams_base_view_user {

	// selectors for single sub views
	var $degreeprogram;
	var $field;
	var $module;
	var $lecture;
	var $lecturer;
	var $exam;

	function __construct() {
		// select selectors
		$GETcommands = t3lib_div::_GET($this->extKey);	// can be both: POST or GET

		if (intval($GETcommands['degreeprogram']))
			$this->degreeprogram = intval($GETcommands['degreeprogram']);
		if (intval($GETcommands['field']))
			$this->field = intval($GETcommands['field']);
		if (intval($GETcommands['module']))
			$this->module = intval($GETcommands['module']);
		if (intval($GETcommands['lecture']))
			$this->lecture = intval($GETcommands['lecture']);
		if (intval($GETcommands['lecturer']))
			$this->lecturer = intval($GETcommands['lecturer']);
		if (intval($GETcommands['exam']))
			$this->exam = intval($GETcommands['exam']);
	}

	function tx_fsmiexams_module_aggregation() {

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


}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/view/class.tx_fsmiexams_module_aggregation.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/view/class.tx_fsmiexams_module_aggregation.php']);
}
?>