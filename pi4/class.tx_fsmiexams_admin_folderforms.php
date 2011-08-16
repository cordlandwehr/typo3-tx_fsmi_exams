<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Andreas Cord-Landwehr <fsmi@uni-paderborn.de>
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
require_once(t3lib_extMgm::extPath('fsmi_exams').'pi4/class.tx_fsmiexams_admin_menu.php');
require_once(t3lib_extMgm::extPath('fsmi_exams').'api/class.tx_fsmiexams_json.php');

/**
 * Admin Menu for Exams Extension
 *
 * @author	Andreas Cord-Landwehr <fsmi@uni-paderborn.de>
 * @package	TYPO3
 * @subpackage	tx_fsmiexams
 */
class tx_fsmiexams_admin_folderforms extends tslib_pibase {
	var $prefixId      = 'tx_fsmiexams_pi4';		// Same as class name
	var $scriptRelPath = 'pi4/class.tx_fsmiexams_pi4.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'fsmi_exams';	// The extension key.
	var $pi_checkCHash = true;

	var $LANG;						// language object
	var $cObj;

	function __construct () {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->LANG = t3lib_div::makeInstance('language');
		// initialization procedure for language
		$this->LLkey = $GLOBALS['TSFE']->lang;
		$this->LOCAL_LANG_loaded = 0;
		$this->pi_loadLL();
	}

	function setPiVarsFromDB($type, $uid) {
		switch($type) {
			case tx_fsmiexams_admin_menu::kCREATE_TYPE_FOLDER: {
				$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $uid);
				$this->piVars["name"] = $folderDATA['name'];
				$this->piVars['folder_id'] = $folderDATA['folder_id'];
				$this->piVars['color'] = $folderDATA['color'];
				$this->piVars['state'] = $folderDATA['state'];

					// get exams, also delete duplicates
				$exams = explode(',',$folderDATA['content']);
				$this->piVars['content'] = array();
				foreach ($exams as $exam)
					$this->piVars['content'][$exam] = true;
				$lectures = explode(',',$folderDATA['associated_lectures']);
				for($i=0;$i<count($lectures);$i++)
					$this->piVars['lecture'.$i] = $lectures[$i];
				break;
			}
			case tx_fsmiexams_admin_menu::kEDIT_TYPE_FOLDER: {
				$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $uid);
				$this->piVars["name"] = $folderDATA['name'];
				$this->piVars['folder_id'] = $folderDATA['folder_id'];
				$this->piVars['color'] = $folderDATA['color'];
				$this->piVars['state'] = $folderDATA['state'];

					// get exams, also delete duplicates
				$exams = explode(',',$folderDATA['content']);
				$this->piVars['content'] = array();
				foreach ($exams as $exam)
					$this->piVars['content'][$exam] = true;
				$lectures = explode(',',$folderDATA['associated_lectures']);
				for($i=0;$i<count($lectures);$i++)
					$this->piVars['lecture'.$i] = $lectures[$i];
				break;
			}
		}
	}
	
	function setPiVarsFromPOST($type) {
		// get form data
		$formData = t3lib_div::_POST($this->extKey);

		// only set if view is create
		if ($formData['view']!=tx_fsmiexams_admin_menu::kVIEW_CREATE)
			return;

		switch($type) {
			case tx_fsmiexams_admin_menu::kCREATE_TYPE_FOLDER: {
				$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $uid);
				$this->piVars["name"] = $formData['name'];
				$this->piVars['folder_id'] = intval($formData['folder_id']);
				$this->piVars['color'] = intval($formDATA['color']);
				for ($i=0; $i<4; $i++)
					$this->piVars['lecture'.$i] = intval($formData['lecture'.$i]);
// 				$this->piVars['state'] = $folderDATA['state'];
// 				$this->piVars['content'] = $folderDATA['content'];
// 				$this->piVars['associated_lectures'] = $folderDATA['associated_lectures'];
				break;
			}
			case tx_fsmiexams_admin_menu::kEDIT_TYPE_FOLDER: {
				$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $uid);
				$this->piVars["name"] = $formData['name'];
				$this->piVars['folder_id'] = intval($formData['folder_id']);
				$this->piVars['color'] = intval($formDATA['color']);
				for ($i=0; $i<4; $i++)
					$this->piVars['lecture'.$i] = intval($formData['lecture'.$i]);
// 				$this->piVars['state'] = $folderDATA['state'];
// 				$this->piVars['content'] = $folderDATA['content'];
// 				$this->piVars['associated_lectures'] = $folderDATA['associated_lectures'];
				break;
			}
		}
	}

	/**
	 * This function provides a form to enter new folders or (if an editUID is given) to
	 * change an existing one.
	 * \param $editUID optional UID for folder
	 */
	function createFolderInputFormPreselect ($editUID=0) {
		$content = '';
		if ($editUID)
			$content .= '<h2>'.$this->pi_getLL("edit_form_folder").'</h2>';
		else
			$content .= '<h2>'.$this->pi_getLL("input_form_folder").'</h2>';

		// create JSON files
		tx_fsmiexams_json::createLectureListJSON();
		tx_fsmiexams_json::createModuleListJSON();
		tx_fsmiexams_json::createFieldListJSON();
		tx_fsmiexams_json::createDegreeprogramListJSON();

		$GLOBALS['TSFE']->additionalHeaderData['fsmi_exam_pi4_widget'] =
			'<script type="text/javascript">
				dojo.require("dojo.parser");
				dojo.require("dijit.form.FilteringSelect");
				dojo.require("dojo.data.ItemFileReadStore");
				dojo.require("dijit.form.DateTextBox");
				dojo.require("dijit.form.CheckBox");
			</script>'
				.'<script type="text/javascript" src="typo3conf/ext/fsmi_exams/js/update_select.js" ></script>'
				.'<script type="text/javascript">
					init_update_select_folder();
				</script>'
		;

		// file storages
		$content .=
			'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsModule" url="typo3temp/fsmiexams_module.json"></div>'
			.'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsField" url="typo3temp/fsmiexams_field.json"></div>'
			.'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsLecture" url="typo3temp/fsmiexams_lecture.json"></div>'
			.'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsDegreeprogram" url="typo3temp/fsmiexams_degreeprogram.json"></div>'
		;

		$content .= '
			<form action="'.$this->pi_getPageLink($GLOBALS["TSFE"]->id).'" method="POST" name="'.$this->extKey.'">
			<input type="hidden" name="no_cache" value="1" />
			<input type="hidden" name="'.$this->extKey.'[view]" value="'.tx_fsmiexams_admin_menu::kVIEW_CREATE.'" />
			<input type="hidden" name="'.$this->extKey.'[type]" value="'.tx_fsmiexams_admin_menu::kCREATE_TYPE_FOLDER.'" />';

		// hidden field for UID if editing existing folder
		if ($editUID)
			$content .= '<input type="hidden" name="'.$this->extKey.'[uid]" value="'.$editUID.'" />';

		// Main Information
		$content .= '
			<fieldset><legend>Ordner-Details</legend><table>';
		$content .= '<tr><td>Name:</td><td>
			<input
					type="text"
					name="'.$this->extKey.'[name]"
					id="'.$this->extKey.'_name"
					value="'.htmlspecialchars($this->piVars["name"]).'" /></td></tr>';

		// search for next free folder ID and set it if not editing existing folder
		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT MAX(tx_fsmiexams_folder.folder_id) as minimal_folder_id
						FROM tx_fsmiexams_folder
						WHERE deleted=0 AND hidden=0');

		$new_folder_id = $this->minimal_folder_id_;
		if ($res && $row = mysql_fetch_assoc($res))
			if (intval($row['minimal_folder_id'])>=intval($new_folder_id))
				$new_folder_id = $row['minimal_folder_id']+1;
		if (!$this->piVars["folder_id"])
			$this->piVars["folder_id"] = $new_folder_id;

		$content .= '<tr><td>Ordner ID:</td><td>
			<input
					type="text"
					name="'.$this->extKey.'[folder_id]"
					id="'.$this->extKey.'_folder_id"
					readonly="readonly"
					style="color: black;"
					value="'.htmlspecialchars($this->piVars["folder_id"]).'"
			/></td></tr>';
		$content .= '<tr><td>Ordnerfarbe:</td><td>
			<select
					type="text"
					name="'.$this->extKey.'[color]"
					id="'.$this->extKey.'_color"
					value="'.htmlspecialchars($this->piVars["color"]).'">';
		foreach ($this->colors as $id => $info) {
			$content .= '<option value="'.$id.'" style="background:'.$info['rgb'].'">'.$info['name'].'</option>';
		}
		$content .= '</select>';
		$content .= '</td></tr>';

		$content .= '</table></fieldset>';

		$content .= '
			<fieldset><legend>Vorauswahl, nicht Bestandteil des Datensatzes</legend><table>';

		// Degree Program
		$content .= '
			<tr>
				<td><label for="'.$this->extKey.'_degreeprogram">'.
					$this->LANG->getLL("tx_fsmiexams_field.degreeprogram").
				':</label></td>
				<td>
				<input
					dojoType="dijit.form.FilteringSelect"
					store="fsmiexamsDegreeprogram"
					searchAttr="name"
					autocomplete="true"
					style="width:300px;"
					name="'.$this->extKey.'[degreeprogram]"
					id="'.$this->extKey.'_degreeprogram"
				/>
				</td>
			</tr>';

		// Field
		$content .= '
			<tr>
				<td><label for="'.$this->extKey.'_field">'.
					$this->LANG->getLL("tx_fsmiexams_module.field").
				':</label></td>
				<td>
				<input dojoType="dijit.form.FilteringSelect"
					store="fsmiexamsField"
					searchAttr="name"
					autocomplete="true"
					style="width:300px;"
					name="'.$this->extKey.'[field]"
					id="'.$this->extKey.'_field"
				/>
				</td>
			</tr>';

		// Modules
		$content .=
			'<tr>
				<td><label for="'.$this->extKey.'_module">'.
					$this->LANG->getLL("tx_fsmiexams_lecture.module").
				':</label></td>
				<td>
					<input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsModule"
						searchAttr="name"
						query="{uid:\'*\', master:1}"
						style="width:300px;"
						name="'.$this->extKey.'[module]"
						id="'.$this->extKey.'_module"
						autocomplete="true"
					/>
				</td>
			</tr>';

		$content .= '</table></fieldset>';

		// database entry
		$content .= '<fieldset><legend>Assoziierte Veranstaltungen</legend><table>';
		// Lecture 0
		$content .=
			'<tr>
				<td><label for="'.$this->extKey.'_lecture0">'.
					$this->LANG->getLL("tx_fsmiexams_exam.lecture").
				':</label></td>
				<td>
					<input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsLecture"
						searchAttr="name"
						query="{uid:\'*\', master:\'1\'}"
						style="width:300px;" ';
		if ($this->piVars['lecture0'])
			$content .= ' value="'.$this->piVars['lecture0'].'" ';
		$content .= '	name="'.$this->extKey.'[lecture0]"
						id="'.$this->extKey.'_lecture0"
						autocomplete="true"
					/>
				</td>
			</tr>';

		// Lecture 1
		$content .=
			'<tr>
				<td><label for="'.$this->extKey.'_lecture1">'.
					$this->LANG->getLL("tx_fsmiexams_exam.lecture").
				' + 1:</label></td>
				<td>
					<input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsLecture"
						searchAttr="name" ';
		if (!$this->piVars['lecture0']) $content .= 'disabled="disabled"';
		$content .= '	query="{uid:\'*\', master:\'1\'}"
						style="width:300px;" ';
		if ($this->piVars['lecture1'])
			$content .= ' value="'.$this->piVars['lecture1'].'" ';
		$content .= '	name="'.$this->extKey.'[lecture1]"
						name="'.$this->extKey.'[lecture1]"
						id="'.$this->extKey.'_lecture1"
						autocomplete="true"
					/>
				</td>
			</tr>';

		// Lecture 2
		$content .=
			'<tr>
				<td><label for="'.$this->extKey.'_lecture2">'.
					$this->LANG->getLL("tx_fsmiexams_exam.lecture").
				' + 2:</label></td>
				<td>
					<input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsLecture"
						searchAttr="name" ';
		if (!$this->piVars['lecture1']) $content .= 'disabled="disabled"';
		$content .= '	query="{uid:\'*\', master:\'1\'}"
						style="width:300px;" ';
		if ($this->piVars['lecture2'])
			$content .= ' value="'.$this->piVars['lecture2'].'" ';
		$content .= '	name="'.$this->extKey.'[lecture2]"
						id="'.$this->extKey.'_lecture2"
						autocomplete="true"
					/>
				</td>
			</tr>';

		// Lecture 3
		$content .=
			'<tr>
				<td><label for="'.$this->extKey.'_lecture3">'.
					$this->LANG->getLL("tx_fsmiexams_exam.lecture").
				' + 3:</label></td>
				<td>
					<input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsLecture"
						searchAttr="name" ';
		if (!$this->piVars['lecture1']) $content .= 'disabled="disabled"';
		$content .= '	query="{uid:\'*\', master:\'1\'}"
						style="width:300px;" ';
		if ($this->piVars['lecture3'])
			$content .= ' value="'.$this->piVars['lecture3'].'" ';
		$content .= '	name="'.$this->extKey.'[lecture3]"
						id="'.$this->extKey.'_lecture3"
						autocomplete="true"
					/>
				</td>
			</tr>';


		$content .= '
			</table></fieldset>
			<input type="submit" name="'.$this->prefixId.'[submit_button]"
				value="'.htmlspecialchars($this->pi_getLL("submit_button_label_folder_detail")).'">
			</form>';

		return $content;
	}

	/**
	 * This function provides a form to enter new folders or (if an editUID is given) to
	 * change an existing one.
	 * Only use after preselection form.
	 * If no UID is given, function assumes initial input for folder and presets some values
	 *
	 * @param	integer	$editUID	optional UID for folder
	 */
	function createFolderInputForm ($editUID = 0) {
// 		$preselection_data = t3lib_div::_POST($this->extKey);

		$GLOBALS['TSFE']->additionalHeaderData['fsmi_exam_pi4_widget'] =
			'<script type="text/javascript" src="typo3conf/ext/fsmi_exams/js/update_select.js" ></script>';

		$content = '';
		if ($editUID)
			$content .= '<h2>'.$this->pi_getLL("edit_form_folder").'</h2>';
		else
			$content .= '<h2>'.$this->pi_getLL("input_form_folder").'</h2>';

		$content .= '
			<form action="'.$this->pi_getPageLink($GLOBALS["TSFE"]->id).'" method="POST" name="'.$this->extKey.'">
			<input type="hidden" name="no_cache" value="1" />
			<input type="hidden" name="'.$this->extKey.'[view]" value="'.tx_fsmiexams_admin_menu::kVIEW_CREATE.'" />
			<input type="hidden" name="'.$this->extKey.'[type]" value="'.tx_fsmiexams_admin_menu::kEDIT_TYPE_FOLDER_SAVE.'" />
			<input type="hidden" name="'.$this->extKey.'[name]" value="'.$this->piVars['name'].'" />
			<input type="hidden" name="'.$this->extKey.'[folder_id]" value="'.$this->piVars['folder_id'].'" />
			<input type="hidden" name="'.$this->extKey.'[color]" value="'.$this->piVars['color'].'" />';

		// hidden field for UID if editing existing folder
		if ($editUID)
			$content .= '<input type="hidden" name="'.$this->extKey.'[uid]" value="'.$editUID.'" />';

		$GETcommands = t3lib_div::_GP($this->extKey);	// can be both: POST or GET
		$folderUID = intval($GETcommands['uid']);

		// generate checkboxes for all containing exams
		$content .= '<h3>Ordner: '.$this->piVars['name'].
			' ['.tx_fsmiexams_div::numberFixedDigits($this->piVars['folder_id'],4).']</h3>';

		for ($i=0; $i<4; $i++) {
			$lectureUID = $this->piVars['lecture'.$i];
			if ($lectureUID<=0)
				continue;

			$lectureDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_lecture', $lectureUID);
			$content .= '<fieldset><legend>Assoziierte Vorlesung: '.$lectureDATA['name'].'</legend><table>';
			$content .= '<tr><td style="border-width: 0px 0px 1px 0px; border-style: solid">abonnieren</td>
				<td><input type="checkbox" checked="checked" name="'.$this->extKey."[lectures][$lectureUID]".'[subscribe]" /></td></tr>';
			$content .= '<tr><td valign="top"><strong>Prüfungen</strong></td>';
			$exam_uids_aggregated = tx_fsmiexams_div::get_exam_uids_grouped($lectureUID);
			$content .= '<td>';
			foreach ($exam_uids_aggregated as $examtype => $examUIDs) {
				$examtypeDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_examtype', $examtype);

					// create list of all exam checkbox ids for select-all/unselect-all
				$listOfAllExamIds = array();
				foreach ($examUIDs as $exam_uid)
					$listOfAllExamIds[] = '\''.$this->extKey."_$lectureUID".'_exam'."_$exam_uid".'\'';
				$listOfAllExamIds = implode(',',$listOfAllExamIds);

					// print all checkboxes and descriptions
				$content .= '<p><strong>'.$examtypeDATA['description'].' ';
				$content .= '[<a onClick="check_all(new Array('.$listOfAllExamIds.'))">alle auswählen</a> /
					<a onClick="uncheck_all(new Array('.$listOfAllExamIds.'))">keine auswählen</a>]</strong><br />';
				foreach ($examUIDs as $exam_uid) {
					$examDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_exam', $exam_uid);
					$content .= '<input
							type="checkbox"
							'.
							(array_key_exists($exam_uid, $this->piVars['content']) == true ?
								'checked="checked"' : ' '
							).
							'id="'.$this->extKey."_$lectureUID".'_exam'."_$exam_uid".'"
							name="'.$this->extKey."[lectures][$lectureUID]".'[exam]'."[$exam_uid]".'" />'.
						tx_fsmiexams_div::examToTermdate($exam_uid).' '.tx_fsmiexams_div::examToText($exam_uid).', '.
						tx_fsmiexams_div::lecturerToText($examDATA['lecturer']).' '.
						($examDATA['exactdate']!=0 ? '['.date('d.m.Y',$examDATA['exactdate']).']' : '').'<br />';
				}
				$content .= '</p>';
			}
			if (count($examUIDs)==0)
				$content .= '<i>keine Prüfungen vorhanden</i>';

			$content .= '</td></tr>';

			$content .= '</table></fieldset>';
		}


		$content .= '
			</table></fieldset>
			<input type="submit" name="'.$this->prefixId.'[submit_button]"
				value="'.htmlspecialchars($this->pi_getLL("submit_button_label")).'">
			</form>';

		return $content;
	}

	/**
	 * This function provides a form to enter new folders or (if an editUID is given) to
	 * change an existing one.
	 * Only use after preselection form.
	 * If no UID is given, function assumes initial input for folder and presets some values
	 *
	 * @param	integer	$editUID	optional UID for folder
	 */
	function editFolderInputForm ($editUID = 0) {
// 		$preselection_data = t3lib_div::_POST($this->extKey);

		$GLOBALS['TSFE']->additionalHeaderData['fsmi_exam_pi4_widget'] =
			'<script type="text/javascript" src="typo3conf/ext/fsmi_exams/js/update_select.js" ></script>';

		$content = '';
		if ($editUID)
			$content .= '<h2>'.$this->pi_getLL("edit_form_folder").'</h2>';
		else
			$content .= '<h2>'.$this->pi_getLL("input_form_folder").'</h2>';

		$content .= '
			<form action="'.$this->pi_getPageLink($GLOBALS["TSFE"]->id).'" method="POST" name="'.$this->extKey.'">
			<input type="hidden" name="no_cache" value="1" />
			<input type="hidden" name="'.$this->extKey.'[view]" value="'.tx_fsmiexams_admin_menu::kVIEW_CREATE.'" />
			<input type="hidden" name="'.$this->extKey.'[type]" value="'.tx_fsmiexams_admin_menu::kEDIT_TYPE_FOLDER_SAVE.'" />
			<input type="hidden" name="'.$this->extKey.'[color]" value="'.$this->piVars['color'].'" />';

		// hidden field for UID if editing existing folder
		if ($editUID)
			$content .= '<input type="hidden" name="'.$this->extKey.'[uid]" value="'.$editUID.'" />';

		$GETcommands = t3lib_div::_GP($this->extKey);	// can be both: POST or GET
		$folderUID = intval($GETcommands['uid']);

		// generate checkboxes for all containing exams
		$content .= '<h3>Ordner: '.$this->piVars['name'].
			' ['.tx_fsmiexams_div::numberFixedDigits($this->piVars['folder_id'],4).']</h3>';

        $content .= '<fieldset><legend>Ordnerdaten editieren</legend><table>';
        $content .= '<tr>
            <td>Name:</td>
            <td><input name="'.$this->extKey.'[name]" value="'.$this->piVars['name'].'" /></td></tr>';
        $content .= '<tr>
            <td>ID (nicht editierbar)</td>
            <td><input disabled="disabled" name="'.$this->extKey.'[folder_id]" value="'.$this->piVars['folder_id'].'" /></td></tr>';
        $content .= '</table></fieldset>';

        $content .= '<fieldset><legend>Download Links</legend><ul>';
		$content .= '<li><a href="'.tx_fsmiexams_latex_export::storeExamsListForFolder($folderUID).'">Inhaltsverzeichnis</a></li>';
		$content .= '<li><a href="'.tx_fsmiexams_latex_export::storeExamsListForFolder($folderUID).'">Deckblatt</a></li>';
		$content .= '</ul></fieldset>';

		for ($i=0; $i<4; $i++) {
			$lectureUID = $this->piVars['lecture'.$i];
			if ($lectureUID<=0)
				continue;

			$lectureDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_lecture', $lectureUID);
			$content .= '<fieldset><legend>Assoziierte Vorlesung: '.$lectureDATA['name'].'</legend><table>';
			$content .= '<tr><td style="border-width: 0px 0px 1px 0px; border-style: solid">abonnieren</td>
				<td><input type="checkbox" checked="checked" name="'.$this->extKey."[lectures][$lectureUID]".'[subscribe]" /></td></tr>';
			$content .= '<tr><td valign="top"><strong>Prüfungen</strong></td>';
			$exam_uids_aggregated = tx_fsmiexams_div::get_exam_uids_grouped($lectureUID);
			$content .= '<td>';
			foreach ($exam_uids_aggregated as $examtype => $examUIDs) {
				$examtypeDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_examtype', $examtype);

					// create list of all exam checkbox ids for select-all/unselect-all
				$listOfAllExamIds = array();
				foreach ($examUIDs as $exam_uid)
					$listOfAllExamIds[] = '\''.$this->extKey."_$lectureUID".'_exam'."_$exam_uid".'\'';
				$listOfAllExamIds = implode(',',$listOfAllExamIds);

					// print all checkboxes and descriptions
				$content .= '<p><strong>'.$examtypeDATA['description'].' ';
				$content .= '[<a onClick="check_all(new Array('.$listOfAllExamIds.'))">alle auswählen</a> /
					<a onClick="uncheck_all(new Array('.$listOfAllExamIds.'))">keine auswählen</a>]</strong><br />';
				foreach ($examUIDs as $exam_uid) {
					$examDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_exam', $exam_uid);
					$content .= '<input
							type="checkbox"
							'.
							(array_key_exists($exam_uid, $this->piVars['content']) == true ?
								'checked="checked"' : ' '
							).
							'id="'.$this->extKey."_$lectureUID".'_exam'."_$exam_uid".'"
							name="'.$this->extKey."[lectures][$lectureUID]".'[exam]'."[$exam_uid]".'" />'.
						tx_fsmiexams_div::examToTermdate($exam_uid).' '.tx_fsmiexams_div::examToText($exam_uid).', '.
						tx_fsmiexams_div::lecturerToText($examDATA['lecturer']).' '.
						($examDATA['exactdate']!=0 ? '['.date('d.m.Y',$examDATA['exactdate']).']' : '').'<br />';
				}
				$content .= '</p>';
			}
			if (count($examUIDs)==0)
				$content .= '<i>keine Prüfungen vorhanden</i>';

			$content .= '</td></tr>';

			$content .= '</table></fieldset>';
		}


		$content .= '
			</table></fieldset>
			<input type="submit" name="'.$this->prefixId.'[submit_button]"
				value="'.htmlspecialchars($this->pi_getLL("submit_button_label")).'">
			</form>';

		return $content;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/pi4/class.tx_fsmiexams_admin_folderforms.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/pi4/class.tx_fsmiexams_admin_folderforms.php']);
}

?>