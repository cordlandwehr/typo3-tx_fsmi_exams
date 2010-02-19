<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Andreas Cord-Landwehr <fsmi@uni-paderborn.de>
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
require_once(t3lib_extMgm::extPath('lang').'lang.php');

/**
 * Plugin 'Exam List' for the 'fsmi_exams' extension.
 *
 * @author	Andreas Cord-Landwehr <fsmi@uni-paderborn.de>
 * @package	TYPO3
 * @subpackage	tx_fsmiexams
 */
class tx_fsmiexams_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_fsmiexams_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_fsmiexams_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'fsmi_exams';	// The extension key.
	var $pidEditPage   = 0;

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

		$this->LANG = t3lib_div::makeInstance('language');
		$this->LANG->init($GLOBALS['TSFE']->tmpl->setup['config.']['language']);
		$this->LANG->includeLLFile('typo3conf/ext/fsmi_exams/locallang_db.xml');
		$this->pidEditPage = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pidEdit'));

		$content = $this->listDegreeprogramAnchors();

		$content .= $this->listAllExams();

		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * This function outputs a list with anchors to all degree programs.
	 */
	function listDegreeprogramAnchors() {
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
	 * This function lists all exams ordered by degree program, part etc.
	 * @return HTML table
	 *
	 */
	function listAllExams () {
		$content = '';

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

					//TODO only print module if there is something in
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

						$firstEntry = true;
						foreach ($examUIDs as $uid) {
							$exam = t3lib_BEfunc::getRecord('tx_fsmiexams_exam', $uid);

							// colorize odd lines
							($lineCounter++ % 2) == 0 ? $content .= '<tr>': $content .= '<tr class="oddline">';

							// to improve readability only write lecture name once
							if ($firstEntry) {	// start of next lecture
								$content .= '<td><strong>'.tx_fsmiexams_div::lectureToText($rowLecture['uid']).'</strong>';
								if (tx_fsmiexams_div::lectureToText($rowLecture['uid'])!=tx_fsmiexams_div::examToText($exam['uid']))
									$content .= '<span style="font-style:italic;">'.tx_fsmiexams_div::examToText($exam['uid']).'</span>';
								$firstEntry = false;
								$content .= '</td>';
							}
							else {	// no new name
								$content .= '<td><img src="typo3conf/ext/fsmi_exams/images/arrow_r.png" alt="->" title="Gleicher Vorlesungsname" /> '; //TODO change to symbol
								if (tx_fsmiexams_div::lectureToText($rowLecture['uid'])!=tx_fsmiexams_div::examToText($exam['uid']))
									$content .= '<span style="font-style:italic;">'.tx_fsmiexams_div::examToText($exam['uid']).'</span>';
								$content .= '</td>';
							}

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
							$content .= '<td><a href="uploads/tx_fsmiexams/'.$exam['file'].'">Exam Download</a>';
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



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/pi1/class.tx_fsmiexams_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/pi1/class.tx_fsmiexams_pi1.php']);
}

?>