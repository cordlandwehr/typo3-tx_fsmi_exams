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
	const imgPath			= 'typo3conf/ext/fsmi_exams/images/'; // absolute path to images
	const extKey			= 'fsmiexams';

	var $pidEditPage 		= 0;	// PID for edit functions
	var $LANG;						// language object
	var $cObj;
	var $examDiv;


	/**
	 * This function provides a selector for the different views.
	 * at the moment it does not preserve previous selections
	 */
	function switchViewMenu() {
 		$this->cObj = t3lib_div::makeInstance('tslib_cObj');	// TODO need to check!
		$content = '';

		$content .= '<div style="text-align:right;">';
		$content .= $this->pi_linkTP('List-View',
						array (	self::extKey.'[type]' => self::kVIEW_TYPE_LIST));
		$content .= ' / ';
		$content .= $this->pi_linkTP('Menue-View',
				array (	self::extKey.'[type]' => self::kVIEW_TYPE_AGGREGATION));
		$content .= '</div>';

		return $content;
	}

	function tx_fsmiexams_listview () {
		$this->LANG = t3lib_div::makeInstance('language');
		$this->LANG->init($GLOBALS['TSFE']->tmpl->setup['config.']['language']);
		$this->LANG->includeLLFile('typo3conf/ext/fsmi_exams/locallang_db.xml');
	}

	function init() {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
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
	 * @return array
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
	 * @return HTML table
	 *
	 */
	function listAllExams () {
		$content = '';
		return $content;
	}


}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/view/class.tx_fsmiexams_base_view_user.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/view/class.tx_fsmiexams_base_view_user.php']);
}
?>
