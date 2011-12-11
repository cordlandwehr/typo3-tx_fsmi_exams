<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Andreas Cord-Landwehr (cola@uni-paderborn.de)
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

require_once(t3lib_extMgm::extPath('fsmi_exams').'view/class.tx_fsmiexams_base_view_user.php');

/**
 * Script Class to download files as defined in reports
 *
 */
class tx_fsmiexams_folderview extends tx_fsmiexams_base_view_user {
	const kSTATUS_INFO 		= 0;
	const kSTATUS_WARNING 	= 1;
	const kSTATUS_ERROR 	= 2;
	const kSTATUS_OK 		= 3;
	const imgPath			= 'typo3conf/ext/fsmi_exams/images/'; // absolute path to images

	var $LANG;						// language object
	var $cObj;

	function __construct () {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->LANG = t3lib_div::makeInstance('language');
		$this->LANG->init($GLOBALS['TSFE']->tmpl->setup['config.']['language']);
		$this->LANG->includeLLFile('typo3conf/ext/fsmi_exams/locallang_db.xml');
	}

	/**
	 * This function outputs a list with anchors to all degree programs.
	 */
	function listMenuBreadcrumb($type) {
		$content = '';
		$content .= '<h3>'.$this->LANG->getLL("tx_fsmiexams_folder").'</h3>';

// 		$resProgram = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
// 												FROM tx_fsmiexams_degreeprogram
// 												WHERE deleted=0 AND hidden=0');
// 		while ($resProgram && $rowProgram = mysql_fetch_assoc($resProgram)) {
// 			$content .= '<a href="index.php?id='.$GLOBALS['TSFE']->id.'&'.parent::extKey.'[type]='.parent::kVIEW_TYPE_LIST.'#fsmiexams_degreeprogram_'.$rowProgram['uid'].'">'.$rowProgram['name'].'</a>';
// 			$content .= ' / ';
// 		}

		return $content;
	}

	/**
	 * This function lists all exams ordered by degree program, part etc.
	 * @return HTML table
	 *
	 */
	function listAllExams () {
		$content = '';

		$resFolder = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
												FROM tx_fsmiexams_folder
												WHERE deleted=0 AND hidden=0');

		$content .= '<table>';
		$content .= '<tr>
			<th></th>
			<th>Name</th>
			<th>'.$this->LANG->getLL("tx_fsmiexams_folder.associated_lectures").'</th>
			<th>Ordner</th>
			</tr>';
		while ($resFolder && $rowFolder = mysql_fetch_assoc($resFolder)) {
			$content .= '<tr>';
			$content .= '<td><span style="background-color:'.tx_fsmiexams_div::printColorHEXcode($rowFolder['color']).'">&nbsp;&nbsp;&nbsp;</span></td>';
			$content .= '<td><b>'.tx_fsmiexams_div::folderToText($rowFolder['uid'],$this->pidEditPage).'</b></td>';

			// associated lectures
			$lectures = explode( ',', $rowFolder['associated_lectures'] );
			$content .= '<td><div>';
			foreach ($lectures as $lecture) {
				$lectureDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_lecture', $lecture);
				$content .= $lectureDATA['name'].'<br />';
			}
			$content .= '</div></td>';
			
			// associziated physical folders
			$resInstance = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
													FROM tx_fsmiexams_folder_instance
													WHERE folder='.$rowFolder['uid'].' AND deleted=0 AND hidden=0
													ORDER BY offset');
			$content .= '<td>';
			while ($resInstance && $rowInstance = mysql_fetch_assoc($resInstance)) {
				$instanceDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder_instance', $rowInstance['uid']);
				$content .= '#'.tx_fsmiexams_div::numberFixedDigits($instanceDATA['folder_id'], 4).' ('.$instanceDATA['offset'].') ';
				$content .= '<br />';
			}
			$content .= '</td>';
			$content .= '</tr>';
		}
		$content .= '</table>';
		return $content;

	}


}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/view/class.tx_fsmiexams_folderview.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/view/class.tx_fsmiexams_folderview.php']);
}
?>