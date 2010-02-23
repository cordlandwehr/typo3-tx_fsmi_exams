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
* This class provides a huge amount on utility functions, e.g. for database access...
*
* @author Andreas Cord-Landwehr <cola@uni-paderborn.de>
*/



require_once(PATH_t3lib.'class.t3lib_befunc.php');
require_once(PATH_t3lib.'class.t3lib_tcemain.php');
require_once(PATH_t3lib.'class.t3lib_iconworks.php');
require_once(t3lib_extMgm::extPath('fsmi_exams').'pi4/class.tx_fsmiexams_pi4.php');


/**
 * Script Class to download files as defined in reports
 *
 */
class tx_fsmiexams_div {
	const kSTATUS_INFO 		= 0;
	const kSTATUS_WARNING 	= 1;
	const kSTATUS_ERROR 	= 2;
	const kSTATUS_OK 		= 3;
	const imgPath			= 'typo3conf/ext/fsmi_exams/images/'; // absolute path to images

	var $cObj;

	static $kSTATUS_INFO = 0;		// deprecated, need to change!
	static $kSTATUS_ERROR = 2;		// deprecated, need to change!

	/**
	 * Translates given UID of lecture to name
	 *
	 * @param UID $uid
	 * @return text
	 */
	function lectureToText ($uid, $editPage) {
 		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$lectureList = explode(',',$uid);
		$text = '';
		foreach ($lectureList as $uid) {
			$lecture = t3lib_BEfunc::getRecord('tx_fsmiexams_lecture', $uid);
			if ($editPage)
				$text .= $this->pi_linkTP(
								$lecture['name'],
								array (
									$this->extKey.'[type]' => tx_fsmiexams_pi4::kEDIT_TYPE_LECTURE,
									$this->extKey.'[uid]' => $lecture['uid']
								),
								0,
								$editPage
							  ). ' ';
			else
				$text .= $lecture['name'];
		}
		return $text;
	}

	/**
	 * Translates given UID of exams to name
	 *
	 * @param UID $uid
	 * @return text
	 */
	function examToText ($uid, $editPage) {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$examDB = t3lib_BEfunc::getRecord('tx_fsmiexams_exam', $uid);

		if ($editPage)
			$text = $this->pi_linkTP(
							$examDB['name'],
							array (
								$this->extKey.'[type]' => tx_fsmiexams_pi4::kEDIT_TYPE_EXAM,
								$this->extKey.'[uid]' => $examDB['uid']
							),
							0,
							$editPage
						  ). ' ';
		else
			$text .= $examDB['name'];
		return $text;
	}

	/**
	 * Prints name of the lecturer in order "lastname, firstname", but also links to edit page if any is given.
	 *
	 * @param UID $uid
	 * @param INTEGER edit page id
	 * @return text
	 */
	function lecturerToText ($uid, $editPage) {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$lecturerList = explode(',',$uid);
		$text = '';
		foreach ($lecturerList as $uid) {
			$lecturer = t3lib_BEfunc::getRecord('tx_fsmiexams_lecturer', $uid);
			if ($editPage)
				$text .= $this->pi_linkTP(
								$lecturer['lastname'].', '.$lecturer['firstname'],
								array (
									$this->extKey.'[type]' => tx_fsmiexams_pi4::kEDIT_TYPE_LECTURER,
									$this->extKey.'[uid]' => $lecturer['uid']
								),
								0,
								$editPage
							  ). ' ';
			else
				$text .= $lecturer['lastname'].', '.$lecturer['firstname'].' ';
		}
		return $text;
	}

	/**
	 * Translates given UID of exam to readable term date
	 *
	 * @param UID $uid
	 * @return text
	 */
	function examToTermdate ($uid) {//TODO no locallang yet
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$exam = t3lib_BEfunc::getRecord('tx_fsmiexams_exam', $uid);

		$text = '';
		if ($exam['term'] == 1)
			$text .= 'SS ';
		else
			$text .= 'WS ';

		$text .= $exam['year'];

		return $text;
	}

	/**
	 * This function provides a database access ability for exams. Use this function
	 * to get the UIDs for specific exams as requested.
	 * Each parameter of optional -- If you do not set it (or set it to 0) a wildcard is used for database.
	 *
	 * @param UID $degreeprogram
	 * @param UID $part
	 * @param UID $module
	 * @param UID $lecture
	 * @param UID $lecturer
	 * @param UID $folder
	 */
	function getExamUIDs ($degreeprogram, $field, $module, $lecture, $lecturer, $folder, $examtype) {
		/*
		 * For the logic behind the following questions please confer the handbook, especially the
		 * database scheme. E.g. if a field is given, the degreeprogram alreade is uniquely defined
		 *
		 * TODO NOTICE: $folder not implemented, yet!
		 *
		 * The way is the following:
		 * CASE $folder  -> only the the content
		 * CASE $lecture -> only select further by $examtype and $lecturer
		 * CASE $module  -> select further as at $lecture
		 *
		 * Prework
		 *   1. construct list of modules
		 *   2. construct by this list of lectures
		 */
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');

		// no field given, check degree program
		$fieldUIDs = array ();
		if ($field == 0) {
			// no $degreeprogram given
			if ($degreeprogram == 0) {
				// get all fields
				$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT tx_fsmiexams_field.uid as uid
												FROM tx_fsmiexams_field
												WHERE deleted=0 AND hidden=0
												ORDER BY name');
				while ($res && $row = mysql_fetch_assoc($res))
					array_push($fieldUIDs, $row['uid']);
			}
			else {
				// get fields
				$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT tx_fsmiexams_field.uid as uid
												FROM tx_fsmiexams_field
												WHERE degreeprogram = '.intval($degreeprogram).'
													AND deleted=0 AND hidden=0
												ORDER BY degreeprogram, name');
				while ($res && $row = mysql_fetch_assoc($res))
					array_push($fieldUIDs, $row['uid']);
			}
		}
		else
			array_push($fieldUIDs, intval($field));

		// no module given, check field
		$moduleUIDs = array ();
		if ($module == 0) {

			foreach ($fieldUIDs as $fieldUID) {
				// get modules
				$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT tx_fsmiexams_module.uid as uid, field
													FROM tx_fsmiexams_module
													WHERE deleted=0 AND hidden=0
													ORDER BY field, name');


				while ($res && $row = mysql_fetch_assoc($res)) {
					// TODO a little bit inefficient
					$rowHaystack = explode(',',$row['field']);
					if (in_array($fieldUID, $rowHaystack))
						array_push($moduleUIDs, $row['uid']);
				}
			}
		}
		else
			array_push($moduleUIDs, intval($module));
		if (count($moduleUIDs)==0)
			return array ();

		// no lecture given, check modules
		$lectureUIDs = array ();
		if ($lecture == 0) {

			foreach ($moduleUIDs as $moduleUID) {
				// get lectures
				$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT tx_fsmiexams_lecture.uid as uid, module
													FROM tx_fsmiexams_lecture
													WHERE deleted=0 AND hidden=0
													ORDER BY module, name');

				while ($res && $row = mysql_fetch_assoc($res)) {
					// TODO a little bit inefficient
					$rowHaystack = explode(',',$row['module']);
					if (in_array($moduleUID, $rowHaystack))
						array_push($lectureUIDs, $row['uid']);
				}
			}
		}
		else
			array_push($lectureUIDs, intval($lecture));
		if (count($lectureUIDs)==0)
			return array ();

		// finally get exams
		$examWhere = '';
		$examUIDs = array ();
		if ($lecturer != 0)
			$examWhere .= 'lecturer = '.intval($lecturer).' AND ';
		if ($examtype != 0)
			$examWhere .=  'examtype = '.intval($examtype).' AND ';

		foreach ($lectureUIDs as $lecture) {
			$lectureWhere = ' FIND_IN_SET('.$lecture.',lecture) ';
			// get exams
			$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT tx_fsmiexams_exam.uid as uid, year, term, exactdate
												FROM tx_fsmiexams_exam
												WHERE '.$examWhere.
													$lectureWhere.'
													AND deleted=0 AND hidden=0
												ORDER BY year DESC, term ASC, number DESC, exactdate DESC, name');
			while ($res && $row = mysql_fetch_assoc($res))
				array_push($examUIDs, $row['uid']);
		}


		return $examUIDs;
	}

	/**
	 *
	 * @param integer $status from constants
	 * @param string $text information text
	 * @return string of HTML div box
	 */
	function printSystemMessage($status, $text) {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		// TODO it would be nice if the info boxes may be hidden on click

		$content = '';
		$content .= '<div style="min-height:30px; " ';
		switch ($status) {
			case self::kSTATUS_INFO: {
				$content .= 'class="fsmivkrit_notify_info">';
				$content .=  '<img src="'.self::imgPath.'info.png" width="30" style="float:left; margin-right:10px;" />';
				break;
			}
			case self::kSTATUS_WARNING: {
				$content .= 'class="fsmivkrit_notify_warning">';
				$content .=  '<img src="'.self::imgPath.'warning.png" width="30" style="float:left; margin-right:10px;" />';
				break;
			}
			case self::kSTATUS_ERROR: {
				$content .= 'class="fsmivkrit_notify_error">';
				$content .=  '<img src="'.self::imgPath.'error.png" width="30" style="float:left; margin-right:10px;" />';
				break;
			}
			case self::kSTATUS_OK: {
				$content .= 'class="fsmivkrit_notify_ok">';
				$content .=  '<img src="'.self::imgPath.'ok.png" width="30" style="float:left; margin-right:10px;" />';
				break;
			}
		}
		// TODO switch $status
		$content .= $text;
		$content .= '</div>';

		return $content;
	}


}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/api/class.tx_fsmiexams_div.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/api/class.tx_fsmiexams_div.php']);
}
?>