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
	var $LANG;

	function __construct() {
		// select selectors
		$GETcommands = t3lib_div::_GET(self::extKey);	// can be both: POST or GET

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

		$this->LANG = t3lib_div::makeInstance('language');
		$this->LANG->init($GLOBALS['TSFE']->tmpl->setup['config.']['language']);
		$this->LANG->includeLLFile('typo3conf/ext/fsmi_exams/locallang_db.xml');
	}

	/**
	 * List function for selector menu and display
	 */
	function listAllExams() {
		$content = '';

		/* Idea: go as far as selections are given, then return */
		if (!$this->degreeprogram) {
			$resProgram = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
												FROM tx_fsmiexams_degreeprogram
												WHERE deleted=0 AND hidden=0');
			$content .= '<ul>';
			while ($resProgram && $rowProgram = mysql_fetch_assoc($resProgram)) {
				$content .= '<li>'.$this->pi_linkTP(
								$rowProgram['name'],
								array (
									self::extKey.'[type]' => self::kVIEW_TYPE_AGGREGATION,
									self::extKey.'[degreeprogram]' => $rowProgram['uid']
								))
							  .'</li>';
			}
			$content .= '</ul>';
			return $content;
		}

		// thus a  degreepgrogram was chosen
		$degreeprogramDB = t3lib_BEfunc::getRecord('tx_fsmiexams_degreeprogram', $this->degreeprogram);
		$content .= '<div>'.$this->pi_linkTP(
								$degreeprogramDB['name'],
								array (
									self::extKey.'[type]' => self::kVIEW_TYPE_AGGREGATION,
								)).'</div>';

		/* next choice are the the field */
		if (!$this->field) {
			$resField = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
												FROM tx_fsmiexams_field
												WHERE FIND_IN_SET('.$this->degreeprogram.',degreeprogram)
													AND deleted=0 AND hidden=0');
			$content .= '<ul>';
			while ($resField && $rowField = mysql_fetch_assoc($resField)) {
				$content .= '<li>'.$this->pi_linkTP(
								$rowField['name'],
								array (
									self::extKey.'[type]' => self::kVIEW_TYPE_AGGREGATION,
									self::extKey.'[degreeprogram]' => $this->degreeprogram,
									self::extKey.'[field]' => $rowField['uid'],
								))
							  .'</li>';
			}
			$content .= '</ul>';
			return $content;
		}
		// thus a  degreepgrogram was chosen
		$fieldDB = t3lib_BEfunc::getRecord('tx_fsmiexams_field', $this->field);
		$content .= '<div>'.$this->pi_linkTP(
								$fieldDB['name'],
								array (
									self::extKey.'[type]' => self::kVIEW_TYPE_AGGREGATION,
									self::extKey.'[degreeprogram]' => $this->degreeprogram,
								)).'</div>';

		/* next choice are the modules */
		if (!$this->module) {
			$resModule = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
												FROM tx_fsmiexams_module
												WHERE FIND_IN_SET('.$this->field.',field)
													AND deleted=0 AND hidden=0');
			$content .= '<ul>';
			while ($resModule && $rowModule = mysql_fetch_assoc($resModule)) {
				$content .= '<li>'.$this->pi_linkTP(
								$rowModule['name'],
								array (
									self::extKey.'[type]' => self::kVIEW_TYPE_AGGREGATION,
									self::extKey.'[degreeprogram]' => $this->degreeprogram,
									self::extKey.'[field]' => $this->field,
									self::extKey.'[module]' => $rowModule['uid'],
								))
							  .'</li>';
			}
			$content .= '</ul>';
			return $content;
		}

		// thus a  degreepgrogram was chosen
		$moduleDB = t3lib_BEfunc::getRecord('tx_fsmiexams_module', $this->module);
		$content .= '<div>'.$this->pi_linkTP(
								$moduleDB['name'],
								array (
									self::extKey.'[type]' => self::kVIEW_TYPE_AGGREGATION,
									self::extKey.'[degreeprogram]' => $this->degreeprogram,
									self::extKey.'[field]' => $this->field,
								)).'</div>';

		/* next choice are lectures */
		$resLecture = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
												FROM tx_fsmiexams_lecture
												WHERE FIND_IN_SET('.$this->module.',module)
													AND deleted=0 AND hidden=0');
		$content .= '<div>';
		while ($resLecture && $rowLecture = mysql_fetch_assoc($resLecture)) {
			$exams = tx_fsmiexams_div::getExamUIDs ($this->degreeprogram, $this->field, $this->module, $rowLecture['uid']);
			$content .= '<div> -> '.$this->pi_linkTP(
							$rowLecture['name'],
							array (
								self::extKey.'[type]' => self::kVIEW_TYPE_AGGREGATION,
								self::extKey.'[degreeprogram]' => $this->degreeprogram,
								self::extKey.'[field]' => $this->field,
								self::extKey.'[module]' => $this->module,
								self::extKey.'[lecture]' => $rowLecture['uid']
							))
						  .' <strong>('.count($exams).')</strong>
						  </div>';
			if ($rowLecture['uid'] == $this->lecture) {
				if (count($exams)==0)
					continue;
				$content .= '<table>';
				$content .= '<tr>';
					$content .= '<th width="300px">'.$this->LANG->getLL("tx_fsmiexams_exam.lecture").'</th>';
					$content .= '<th width="140px">'.$this->LANG->getLL("tx_fsmiexams_exam.lecturer").'</th>';
					$content .= '<th width="60px">'.$this->LANG->getLL("tx_fsmiexams_exam.term").'</th>';
					$content .= '<th>Nr.</th>';
					$content .= '<th>'.$this->LANG->getLL("tx_fsmiexams_exam.exactdate").'</th>';
				$content .= '</tr>';
				foreach ($exams as $exam)  {
					$examDB = t3lib_BEfunc::getRecord('tx_fsmiexams_exam', $exam);
					$content .= '<tr>';
					$content .= '<td>'.tx_fsmiexams_div::examToText($exam).'</td>';
					$content .= '<td>'.tx_fsmiexams_div::lecturerToText($examDB['lecturer']).'</td>';
					$content .= '<td>'.tx_fsmiexams_div::examToTermdate($exam).'</td>';
					if ($exam['number']!=0)
						$content .= '<td>'.$examDB['number'].'</td>';
					else
						$content .= '<td>-</td>';
					if ($exam['exactdate']!=0)
						$content .= '<td>'.date('d.m.y',$examDB['exactdate']).'</td>';
					else
						$content .= '<td>-</td>';
					$content .= '</tr>'."\n";
				}
				$content .= '</table>';
			}
		}
		$content .= '</div>';

		return $content;
	}

	/**
	 * This function outputs a list with anchors to all degree programs.
	 */
	function listMenuBreadcrumb() {
		$content = '';

		$content .= '<div>Lecturer / Modules </div>';

		return $content;
	}


}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/view/class.tx_fsmiexams_module_aggregation.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/view/class.tx_fsmiexams_module_aggregation.php']);
}
?>