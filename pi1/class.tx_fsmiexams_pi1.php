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
 * Plugin 'Exam Input' for the 'fsmi_exams' extension.
 *
 * @author	Andreas Cord-Landwehr <fsmi@uni-paderborn.de>
 * @package	TYPO3
 * @subpackage	tx_fsmiexams
 */
class tx_fsmiexams_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_fsmiexams_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_fsmiexams_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'fsmi_exams';	// The extension key.
	
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
		
		$this->LANG = t3lib_div::makeInstance('language');
		$this->LANG->init($GLOBALS['TSFE']->tmpl->setup['config.']['language']);
		$this->LANG->includeLLFile('typo3conf/ext/fsmi_exams/locallang_db.xml'); 
		
		$content = $this->listAllExams();
	
		return $this->pi_wrapInBaseClass($content);
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
			$content .= '<h2>'.$rowProgram['name'].'</h2>';
			
			$resField = $GLOBALS['TYPO3_DB']->sql_query('SELECT * 
												FROM tx_fsmiexams_field
												WHERE '.$rowProgram['uid'].' = tx_fsmiexams_field.degreeprogram
												AND deleted=0 AND hidden=0');
		
			while ($resField && $rowField = mysql_fetch_assoc($resField)) {
				$content .= '<h3>'.$rowField['name'].'</h3>';

				$examUIDs = tx_fsmiexams_div::getExamUIDs($rowProgram['uid'],$rowField['uid'],0,0,0,0,0);
				if (count($examUIDs)==0)
					continue;
					
				$content .= '<table>';
				$content .= '<tr>';
					$content .= '<th>'.$this->LANG->getLL("tx_fsmiexams_exam.lecture").'</th>';	
					//$content .= '<th>'.$this->LANG->getLL("tx_fsmiexams_exam.name").'</th>';
					$content .= '<th>'.$this->LANG->getLL("tx_fsmiexams_exam.lecturer").'</th>';
					$content .= '<th>'.$this->LANG->getLL("tx_fsmiexams_exam.term").'</th>';
					$content .= '<th>Nr.</th>';
					$content .= '<th>'.$this->LANG->getLL("tx_fsmiexams_exam.file").'</th>';
				$content .= '</tr>';
				
				foreach ($examUIDs as $uid) {
        			$exam = t3lib_BEfunc::getRecord('tx_fsmiexams_exam', $uid);
					$content .= '<tr>';
					$content .= '<td>'.tx_fsmiexams_div::lectureToText($exam['lecture']).'</td>';
					//$content .= '<td>'.$exam['name'].'</td>';
					$content .= '<td>'.tx_fsmiexams_div::lecturerToText($exam['lecturer']).'</td>';
					$content .= '<td>'.tx_fsmiexams_div::examToTermdate($uid).'</td>';
					$content .= '<td>'.$exam['number'].'</td>';
					$content .= '<td><a href="uploads/tx_fsmiexams/'.$exam['file'].'">Download</a></td>';
					$content .= '</tr>';

				}				
				$content .= '</table>';
			}
		}
		return $content;
		
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/pi1/class.tx_fsmiexams_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/pi1/class.tx_fsmiexams_pi1.php']);
}

?>