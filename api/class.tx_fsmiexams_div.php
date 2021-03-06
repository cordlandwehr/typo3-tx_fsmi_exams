<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2011  Andreas Cord-Landwehr <cola@uni-paderborn.de>
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
 * This class provides a huge amount on utility functions, e.g. for database
 * access...
 *
 * @author Andreas Cord-Landwehr <cola@uni-paderborn.de>
 */



require_once (PATH_t3lib.'class.t3lib_befunc.php');
require_once (PATH_t3lib.'class.t3lib_tcemain.php');
require_once (PATH_t3lib.'class.t3lib_iconworks.php');
require_once (t3lib_extMgm::extPath('fsmi_exams').'controller/class.tx_fsmiexams_controller_admin.php'); //FIXME: refactor!


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
	const extKey			= 'fsmi_exams';

	const kFOLDER_STATE_PRESENT			= 0;
	const kFOLDER_STATE_LEND			= 1;
	const kFOLDER_STATE_MAINTENANCE		= 2;
	const kFOLDER_STATE_LOST			= 3;

	static private $pi_base;

	static function init() {
		if (!self::$pi_base) {
			self::$pi_base = t3lib_div::makeInstance('tslib_pibase');
			self::$pi_base->cObj = t3lib_div::makeInstance('tslib_cObj');
		}
	}

	/**
	 * Translates given UID of lecture to name
	 *
	 * @param	integer	$uid	UID of lecture
	 * @param	integer	$editPage	is optional page for FE-editing
	 * @return	string	text
	 */
	static function lectureToText($uid, $editPage = 0) {
		self::init();

		$lectureList = explode(',',$uid);
		$lectureArray = array();
		foreach ($lectureList as $uid) {
			$lecture = t3lib_BEfunc::getRecord('tx_fsmiexams_lecture', $uid);
			if ($editPage)
				$lectureArray[] = self::$pi_base->pi_linkTP(
								$lecture['name'],
								array (
									self::extKey.'[view]' => tx_fsmiexams_controller_admin::kVIEW_CREATE,
									self::extKey.'[type]' => tx_fsmiexams_controller_admin::kEDIT_TYPE_LECTURE,
									self::extKey.'[uid]' => $lecture['uid']
								),
								0,
								$editPage
							  );
			else
				$lectureArray[] = $lecture['name'];
		}
		return implode('; ',$lectureArray);
	}

	/**
	 * Translates given UID of exams to name
	 *
	 * \param $uid UID of exam
	 * \param $editPage is optional page for FE-editing
	 * \return text
	 */
	static function examToText($uid, $editPage = 0) {
		self::init();
		$examDB = t3lib_BEfunc::getRecord('tx_fsmiexams_exam', $uid);

		if ($editPage)
			$text = self::$pi_base->pi_linkTP(
							$examDB['name'],
							array (
								self::extKey.'[view]' => tx_fsmiexams_controller_admin::kVIEW_CREATE,
								self::extKey.'[type]' => tx_fsmiexams_controller_admin::kEDIT_TYPE_EXAM,
								self::extKey.'[uid]' => $examDB['uid']
							),
							0,
							$editPage
						  ). ' ';
		else
			$text = $examDB['name'];
		return $text;
	}

	/**
	 * Prints name of the lecturer in order "lastname, firstname", but also links to edit page if any is given.
	 *
	 * \param $uid UID of lecturer OR array of lecturers
	 * \param $editPage is optional page for FE-editing
	 * \return text
	 */
	static function lecturerToText($uid, $editPage = 0) {
		self::init();
		if (is_array($uid)) {
			$lecturerList = $uid;
		}
		else {
			$lecturerList = explode(',',$uid);
		}
		$lecturerArray = array();
		foreach ($lecturerList as $uid) {
			$lecturer = t3lib_BEfunc::getRecord('tx_fsmiexams_lecturer', $uid);
			if ($editPage)
				$lecturerArray[] = self::$pi_base->pi_linkTP(
								$lecturer['lastname'].', '.$lecturer['firstname'],
								array (
									self::extKey.'[view]' => tx_fsmiexams_controller_admin::kVIEW_CREATE,
									self::extKey.'[type]' => tx_fsmiexams_controller_admin::kEDIT_TYPE_LECTURER,
									self::extKey.'[uid]' => $lecturer['uid']
								),
								0,
								$editPage
							  ). ' ';
			else
				$lecturerArray[] = $lecturer['lastname'].', '.$lecturer['firstname'].' ';
		}
		return implode('; ', $lecturerArray);
	}

	/**
	 * Creates an array with key UID and value description of exam type.
	 * /TODO use this instead of version in base view class for all views
	 * \return array of names
	 */
	static function listExamTypes() {
		$types = array ();

		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
													FROM tx_fsmiexams_examtype
													WHERE deleted=0 AND hidden=0');

		while ($res && $row = mysql_fetch_assoc($res)) {
			$types[$row['uid']] = $row['description'];
		}
		return $types;
	}

	/**
	 * Prints name of the folder in order, but also links to edit page if any is given.
	 *
	 * \param $uid UID of folder
	 * \param $editPage is optional page for FE-editing
	 * \return text
	 */
	static function folderToText($uid, $editPage=0) {
		self::init();
		$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $uid);
		if ($editPage)
			return self::$pi_base->pi_linkTP(
							$folderDATA['name'],
							array (
								self::extKey.'[view]' => tx_fsmiexams_controller_admin::kVIEW_CREATE,
								self::extKey.'[type]' => tx_fsmiexams_controller_admin::kEDIT_TYPE_FOLDER,
								self::extKey.'[uid]' => $folderDATA['uid']
							),
							0,
							$editPage
						). ' ';
		else
			return $folderDATA['name'];
	}

	static function printTCALabelFolderInstance(&$params, &$pObj) {
		$instanceDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder_instance', $params['row']['uid']);
		$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $instanceDATA['folder']);
		$params['title'] = $params['title'] = "[" . tx_fsmiexams_div::numberFixedDigits($instanceDATA["folder_id"],4) . "] "
			. $folderDATA['name'];
	}

	/**
	 * returns array of assembled folder names for given exam where this exam is included
	 */
	static function folderInstancesForExam($exam) {
		$folderInstance = array ();
		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
													FROM tx_fsmiexams_folder
													WHERE deleted=0 AND hidden=0
													AND FIND_IN_SET('.$exam.',content)');

		while ($res && $folderDATA = mysql_fetch_assoc($res)) {
			$name = $folderDATA['name'];

			$resInstance = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
													FROM tx_fsmiexams_folder_instance
													WHERE deleted=0 AND hidden=0
													AND folder='.$folderDATA['uid'].'
													ORDER BY offset');
			$instances = array ();
			while ($resInstance && $folderInstanceDATA = mysql_fetch_assoc($resInstance)) {
				$color = '#000';
				if ($folderInstanceDATA['state']==self::kFOLDER_STATE_LEND) {
					$color='red';
				}
				if ($folderInstanceDATA['state']==self::kFOLDER_STATE_MAINTENANCE) {
					$color='blue';
				}
				if ($folderInstanceDATA['state']==self::kFOLDER_STATE_PRESENT) {
					$color='green';
				}
				$instances[] = '<strong style="color:'.$color.'">('.$folderInstanceDATA['offset'].')</strong>';
			}
			$folderInstance[] = $name.' '.implode(' ',$instances);
		}
		return $folderInstance;
	}


	/**
	 * Translates given UID of exam to readable term date
	 *
	 * \param UID $uid
	 * \return text
	 */
	static function examToTermdate($uid) {
			//TODO no locallang yet
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
	 * \param UID $degreeprogram
	 * \param UID $part
	 * \param UID $module
	 * \param UID $lecture
	 * \param UID $lecturer
	 * \param UID $folder
	 * \return array of uids
	 */
	function getExamUIDs($degreeprogram, $field, $module, $lecture, $lecturer, $folder, $examtype) {
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
	self::init();
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

			// if no module given, check field
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
		if (count($moduleUIDs) == 0)
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
		if (count($lectureUIDs) == 0)
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
	 * Get all exam UIDs that are present for specific lecture
	 **/
	function get_exam_uids($lecture) {
		$exam_uids = array();
		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT tx_fsmiexams_exam.uid as uid, year, term, exactdate
												FROM tx_fsmiexams_exam
												WHERE FIND_IN_SET('.$lecture.',lecture)
													AND deleted=0 AND hidden=0
												ORDER BY year DESC, term ASC, number DESC, exactdate DESC, name');

		while ($res && $row = mysql_fetch_assoc($res))
			array_push($exam_uids, $row['uid']);

		return $exam_uids;
	}

	/**
	 * Get all exam UIDs that are present for specific lecture
	 * grouped by exam types.
	 * \param $lecture UID of lecture
	 **/
	function get_exam_uids_grouped($lecture) {
		$exam_uids = array();
		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT examtype, tx_fsmiexams_exam.uid as uid
												FROM tx_fsmiexams_exam
												WHERE FIND_IN_SET('.$lecture.',lecture)
													AND deleted=0 AND hidden=0
												ORDER BY year DESC, term ASC, number DESC, exactdate DESC, name');

		while ($res && $row = mysql_fetch_assoc($res)) {
			if (!is_array($exam_uids[$row['examtype']]))
				$exam_uids[$row['examtype']] = array ();
			$exam_uids[$row['examtype']][] = $row['uid'];
		}

		return $exam_uids;
	}

	/**
	 *
	 * @param integer $status from constants
	 * @param string $text information text
	 * @return string of HTML div box
	 */
	function printSystemMessage($status, $text) {
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

	static function printColorHEXcode($color) {
		// set color information
		$colors[0]['name'] = "keine";
		$colors[0]['rgb'] = "#cccccc";
		$colors[1]['name'] = "rot";
		$colors[1]['rgb'] = "#f00";
		$colors[2]['name'] = "blau";
		$colors[2]['rgb'] = "#00f";
		$colors[3]['name'] = "gelb";
		$colors[3]['rgb'] = "#ff0";
		$colors[4]['name'] = "grün";
		$colors[4]['rgb'] = "#0f0";
		$colors[5]['name'] = "schwarz";
		$colors[5]['rgb'] = "#000";

		return $colors[$color]['rgb'];
	}

	/**
	 * Converts given number into string with exactly $digits man digits,
	 * if $number is not larger than 10 times $digits.
	 * \param $number is to be displayed number
	 * \param $digits is number of digits
	 */
	static function numberFixedDigits( $number, $digits ) {
		if (intval($number)>=pow(10,$digits)) {
			return $number;
		}
		$neededZeros = $digits - strlen($number.'');

			//TODO pretty inefficient
		for ($i=0; $i<$neededZeros; $i++)
			$number = '0'.$number;
		return $number;
	}

	/**
	 * Returns array with all group UIDs whose members are allowed to edit entries
	 * @return	array of UIDs
	 */
	static function getGroupUIDsRightsEdit() {
		$uids = array();
		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT uid
												FROM fe_groups
												WHERE tx_fsmiexams_fsmiexams_rights_edit=1');
		while ($res && $row = mysql_fetch_assoc($res)) {
			$uids[] = $row['uid'];
		}
		return $uids;
	}

	/**
	 * Returns array with all group UIDs whose members are allowed to download exams
	 * @return	array of UIDs
	 */
	static function getGroupUIDsRightsDownload() {
		$uids = array();
		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT uid
												FROM fe_groups
												WHERE tx_fsmiexams_fsmiexams_rights_download=1');
		while ($res && $row = mysql_fetch_assoc($res)) {
			$uids[] = $row['uid'];
		}
		return $uids;
	}

	/**
	 * Returns array with all group UIDs whose members are allowed to download exams
	 * @return	array of UIDs
	 */
	static function getGroupUIDsRightsPrint() {
		$uids = array();
		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT uid
												FROM fe_groups
												WHERE tx_fsmiexams_fsmiexams_rights_print=1');
		while ($res && $row = mysql_fetch_assoc($res)) {
			$uids[] = $row['uid'];
		}
		return $uids;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/api/class.tx_fsmiexams_div.php'])
{
	required_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/api/class.tx_fsmiexams_div.php']);
}
?>