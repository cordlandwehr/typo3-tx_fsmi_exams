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

require_once(t3lib_extMgm::extPath('fsmi_exams').'view/class.tx_fsmiexams_base_view_user.php');

/**
 * Script Class to download files as defined in reports
 *
 */
class tx_fsmiexams_listview extends tx_fsmiexams_base_view_user {
	const kSTATUS_INFO 		= 0;
	const kSTATUS_WARNING 	= 1;
	const kSTATUS_ERROR 	= 2;
	const kSTATUS_OK 		= 3;
	const imgPath			= 'typo3conf/ext/fsmi_exams/images/'; // absolute path to images

	var $pidEditPage 		= 0;	// PID for edit functions
	var $LANG;						// language object
	var $cObj;

	function __construct () {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->LANG = t3lib_div::makeInstance('language');
		$this->LANG->init($GLOBALS['TSFE']->tmpl->setup['config.']['language']);
		$this->LANG->includeLLFile('typo3conf/ext/fsmi_exams/locallang_db.xml');
	}

	function init($cObj, $pidEditPage) {
		$this->pidEditPage = $pidEditPage;
		$this->cObj = $cObj;
	}

	/**
	 * This function outputs a list with anchors to all degree programs.
	 */
	function listMenuBreadcrumb() {
		$content = '';
		$content .= '<h3>'.$this->LANG->getLL("tx_fsmiexams_degreeprograms").'</h3>';

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
	 * This function lists all exams ordered by degree program, part etc.
	 * @return HTML table
	 *
	 */
	function listAllExams () {
		$content = '';
		$examTypes = $this->listExamTypes();

		$resProgram = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
												FROM tx_fsmiexams_degreeprogram
												WHERE deleted=0 AND hidden=0');

		while ($resProgram && $rowProgram = mysql_fetch_assoc($resProgram)) {
			$content .= '<hr />';
			$content .= '<a name="fsmiexams_degreeprogram_'.$rowProgram['uid'].'"><h2>'.$rowProgram['name'].'</h2></a>';

			$resField = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
												FROM tx_fsmiexams_field
												WHERE '.$rowProgram['uid'].' = tx_fsmiexams_field.degreeprogram
												AND deleted=0 AND hidden=0');

			while ($resField && $rowField = mysql_fetch_assoc($resField)) {
				$content .= '<h3>'.$rowField['name'].'</h3>';

				$resModule = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
												FROM tx_fsmiexams_module
												WHERE '.$rowField['uid'].' in (tx_fsmiexams_module.field)
												AND deleted=0 AND hidden=0');

				while ($resModule && $rowModule = mysql_fetch_assoc($resModule)) {
					$content .= '<div name="fsmiexams_module_'.$rowModule['uid'].'" class="fsmiexams_module"><h4>'.$rowModule['name'].'</h4></div>';

					$content .= '<table>';
					$content .= '<tr>';
						$content .= '<th width="300px">'.$this->LANG->getLL("tx_fsmiexams_exam.lecture").'</th>';
						$content .= '<th width="140px">'.$this->LANG->getLL("tx_fsmiexams_exam.lecturer").'</th>';
						$content .= '<th width="60px">'.$this->LANG->getLL("tx_fsmiexams_exam.term").'</th>';
						$content .= '<th>Nr.</th>';
						$content .= '<th>'.$this->LANG->getLL("tx_fsmiexams_exam.exactdate").'</th>';
						$content .= '<th>'.$this->LANG->getLL("tx_fsmiexams_exam.file").'</th>';
					$content .= '</tr>';

					$lineCounter = 1;

					$resLecture = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
													FROM tx_fsmiexams_lecture
													WHERE '.$rowModule['uid'].' in (tx_fsmiexams_lecture.module)
													AND deleted=0 AND hidden=0');

					while ($resLecture && $rowLecture = mysql_fetch_assoc($resLecture)) {

						$examUIDs = tx_fsmiexams_div::getExamUIDs($rowProgram['uid'],$rowField['uid'],$rowModule['uid'],$rowLecture['uid'],0,0,0);
						if (count($examUIDs)==0)
							continue;

						// lecture
						$content .= '<tr class="sepline">
										<td colspan="6"><strong>'.tx_fsmiexams_div::lectureToText($rowLecture['uid'],$this->pidEditPage).
										' ('.count($examUIDs).')</strong></td></tr>';

						// exams
						foreach ($examUIDs as $uid) {
							$exam = t3lib_BEfunc::getRecord('tx_fsmiexams_exam', $uid);

							// colorize odd lines
							($lineCounter++ % 2) == 0 ? $content .= '<tr>': $content .= '<tr class="oddline">';

							$content .= '<td><img style="float:left;" src="typo3conf/ext/fsmi_exams/images/arrow_r.png" alt="->" title="Gleicher Vorlesungsname" /> '; //TODO change to symbol
							$content .= '<div style="font-style:italic; margin-left:20px;">'.tx_fsmiexams_div::examToText($exam['uid'],$this->pidEditPage).'</div>';
							$content .= '</td>';

							$content .= '<td>'.tx_fsmiexams_div::lecturerToText($exam['lecturer'],$this->pidEditPage).'</td>';
							$content .= '<td>'.tx_fsmiexams_div::examToTermdate($uid).'</td>';
							if ($exam['number']!=0)
								$content .= '<td>'.$exam['number'].'</td>';
							else
								$content .= '<td>-</td>';
							if ($exam['exactdate']!=0)
								$content .= '<td>'.date('d.m.y',$exam['exactdate']).'</td>';
							else
								$content .= '<td>-</td>';



							$content .= '<td><a href="uploads/tx_fsmiexams/'.$exam['file'].'">'.$examTypes[$exam['examtype']].'</a>';
							if ($exam['material']!='')
								$content .= '<br /><a href="uploads/tx_fsmiexams/'.$exam['material'].'">Zusatzmateriall</a>';
							$content .= '</td>';

							$content .= '</tr>';
						}
					}
					$content .= '</table>';
				}
			}
		}
		return $content;

	}


}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/view/class.tx_fsmiexams_listview.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/view/class.tx_fsmiexams_listview.php']);
}
?>