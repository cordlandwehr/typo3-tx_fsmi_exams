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

/**
 * Plugin 'Exam Input' for the 'fsmi_exams' extension.
 *
 * @author	Andreas Cord-Landwehr <fsmi@uni-paderborn.de>
 * @package	TYPO3
 * @subpackage	tx_fsmiexams
 */
class tx_fsmiexams_pi4 extends tslib_pibase {
	var $prefixId      = 'tx_fsmiexams_pi4';		// Same as class name
	var $scriptRelPath = 'pi4/class.tx_fsmiexams_pi4.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'fsmi_exams';	// The extension key.
	var $pi_checkCHash = true;

	// constants
//	const kEDIT_TYPE_NONE		= 0;
//	const kEDIT_TYPE_MODULE		= 1;
	const kEDIT_TYPE_EXAM		= 2;
	const kEDIT_TYPE_LECTURE	= 3;
	const kEDIT_TYPE_LECTURER	= 4;

	// storages
	var $storageLecturer;
	var $storageLecture;
	var $storageExam;
	var $LANG;

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
		$this->pi_initPIflexForm(); // Init and get the flexform data of the plugin
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

		$this->LANG = t3lib_div::makeInstance('language');
		$this->LANG->init($GLOBALS['TSFE']->tmpl->setup['config.']['language']);
		$this->LANG->includeLLFile('typo3conf/ext/fsmi_exams/locallang_db.xml');

		// set storages
		$this->storageLecturer = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pidStoreLecturer'));
		$this->storageLecture = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pidStoreLecture'));
		$this->storageExam = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pidStoreExam'));

		// type selection head
		$content .= $this->createTypeSelector();

		// select input type
		$GETcommands = t3lib_div::_GP($this->extKey);	// can be both: POST or GET
		switch (intval($GETcommands['type'])) {
			case self::kEDIT_TYPE_LECTURE: {
				// save POST data if received
				if (t3lib_div::_POST($this->extKey))
					$content .= $this->saveFormData();
				if (intval($GETcommands['uid']))
					$this->setPiVarsFromDB(self::kEDIT_TYPE_LECTURE, intval($GETcommands['uid']));
				$content .= $this->createLectureInputForm(intval($GETcommands['uid']));
				break;
			}
			case self::kEDIT_TYPE_EXAM: {
				// save POST data if received
				if (t3lib_div::_POST($this->extKey)) {
					$this->setPiVarsFromPOST(self::kEDIT_TYPE_EXAM);	// further operations are down only with piVars

					if ($this->validateFormData(self::kEDIT_TYPE_EXAM)==false) {	// check content
						$content .= tx_fsmiexams_div::printSystemMessage(
							tx_fsmiexams_div::kSTATUS_ERROR,
							'Error in input form! Nothing saved yet. Please modify and try again.'
							);
						$content .= $this->createExamInputForm();
					}
					else	// no input errors
						$content .= $this->saveFormData();
				}
				// output the input form
				else {
					if (intval($GETcommands['uid']))
						$this->setPiVarsFromDB(self::kEDIT_TYPE_EXAM, intval($GETcommands['uid']));
					$content .= $this->createExamInputForm(intval($GETcommands['uid']));
				}

				break;
			}
			case self::kEDIT_TYPE_LECTURER: {
				// save POST data if received
				if (t3lib_div::_POST($this->extKey))
					$content .= $this->saveFormData();
				if (intval($GETcommands['uid']))
					$this->setPiVarsFromDB(self::kEDIT_TYPE_LECTURER, intval($GETcommands['uid']));
				$content .= $this->createLecturerInputForm(intval($GETcommands['uid']));
				break;
			}
			default:
				break;
		}

		return $this->pi_wrapInBaseClass($content);
	}

	function createTypeSelector () {
		$content = '<div>';
		$content .= $this->pi_linkTP($this->pi_getLL("option_new-lecture"),
								array (	$this->extKey.'[type]' => self::kEDIT_TYPE_LECTURE));
		$content .= ' | ';
		$content .= $this->pi_linkTP($this->pi_getLL("option_new-exam"),
								array (	$this->extKey.'[type]' => self::kEDIT_TYPE_EXAM));
		$content .= ' | ';
		$content .= $this->pi_linkTP($this->pi_getLL("option_new-lecturer"),
								array (	$this->extKey.'[type]' => self::kEDIT_TYPE_LECTURER));
		$content .= '</div>';

		return $content;
	}

	function setPiVarsFromDB($type, $uid) {
		switch($type) {
			case self::kEDIT_TYPE_LECTURE: {
				$lectureDB = t3lib_BEfunc::getRecord('tx_fsmiexams_lecture', $uid);
				$this->piVars["name"] = $lectureDB['name'];
				$this->piVars["field"] = $lectureDB['field']; //TODO not in this DB, search by DB select
				$modules = explode(',',$lectureDB['module']);
				for ($i=0; $i<count($modules); $i++)
					$this->piVars["module".$i] = $modules[$i];
				break;
			}
			case self::kEDIT_TYPE_EXAM: {
				$examDB = t3lib_BEfunc::getRecord('tx_fsmiexams_exam', $uid);
				$this->piVars["name"] = $examDB['name'];
				$this->piVars["number"] = $examDB['number'];
				$this->piVars["term"] = $examDB['term'];
				$lectures = explode(',',$examDB['lecture']);
				for ($i=0; $i<count($lectures); $i++)
					$this->piVars["lecture".$i] = $lectures[$i];
				$this->piVars["year"] = $examDB['year'];
				if ($examDB['exactdate']!=0)
					$this->piVars["exactdate"] = $examDB['exactdate'];
				$this->piVars["name"] = $examDB['name'];
				$lecturers = explode(',',$examDB['lecturer']);
				for ($i=0; $i<count($lecturers); $i++)
					$this->piVars["lecturer".$i] = $lecturers[$i];
				$this->piVars["approved"] = $examDB['approved'];
				$this->piVars["file"] = $examDB['file'];
				$this->piVars["material"] = $examDB['material'];
				$this->piVars["quality"] = $examDB['quality'];
				$this->piVars["examtype"] = $examDB['examtype'];
				break;
			}
			case self::kEDIT_TYPE_LECTURER: {
				$lecturerDB = t3lib_BEfunc::getRecord('tx_fsmiexams_lecturer', $uid);
				$this->piVars["firstname"] = $lecturerDB['firstname'];
				$this->piVars['lastname'] = $lecturerDB['lastname'];
				break;
			}
		}
	}

	function setPiVarsFromPOST($type) {
		// get form data
		$formData = t3lib_div::_POST($this->extKey);

		switch($type) {
// 			case self::kEDIT_TYPE_LECTURE: {
// 				$lectureDB = t3lib_BEfunc::getRecord('tx_fsmiexams_lecture', $uid);
// 				$this->piVars["name"] = $lectureDB['name'];
// 				$this->piVars["field"] = $lectureDB['field']; //TODO not in this DB, search by DB select
// 				$modules = explode(',',$lectureDB['module']);
// 				for ($i=0; $i<count($modules); $i++)
// 					$this->piVars["module".$i] = $modules[$i];
// 				break;
// 			}
			case self::kEDIT_TYPE_EXAM: {
				// get approved button
				if (isset($formData['approved']))
					$approved = 1;

				$this->piVars["name"] = $formData['name'];
				$this->piVars["number"] = $formData['number'];
				$this->piVars["term"] = $formData['term'];
				for ($i=0; $i<3; $i++)
					$this->piVars["lecture".$i] = substr($formData['lecture'.$i],0,strrchr($formData['lecture'.$i],'-'))  ;
				$this->piVars["year"] = $formData['year'];
				if ($formData['exactdate']!='')
					$this->piVars["exactdate"] = strtotime($formData['exactdate']);
				$this->piVars["name"] = $formData['name'];
				for ($i=0; $i<3; $i++)
					$this->piVars["lecturer".$i] = $formData['lecturer'.$i];
				$this->piVars["approved"] = $approved;
				$this->piVars["file"] = $formData['file'];
				$this->piVars["material"] = $formData['material'];
				$this->piVars["quality"] = $formData['quality'];
				$this->piVars["examtype"] = $formData['examtype'];
				break;
			}
// 			case self::kEDIT_TYPE_LECTURER: {
// 				$lecturerDB = t3lib_BEfunc::getRecord('tx_fsmiexams_lecturer', $uid);
// 				$this->piVars["firstname"] = $lecturerDB['firstname'];
// 				$this->piVars['lastname'] = $lecturerDB['lastname'];
// 				break;
// 			}
		}
	}

	function createLectureInputForm ($editUID) {

		if ($editUID)
			$content .= '<h2>'.$this->pi_getLL("edit_form_lecture").'</h2>';
		else
			$content .= '<h2>'.$this->pi_getLL("input_form_lecture").'</h2>';


		// create JSON files
		$this->createModuleListJSON();
		$this->createFieldListJSON();
		$this->createDegreeprogramListJSON();

		$GLOBALS['TSFE']->additionalHeaderData['fsmi_exam_pi4_widget'] =
			'<script type="text/javascript">
				dojo.require("dojo.parser");
				dojo.require("dijit.form.FilteringSelect");
				dojo.require("dojo.data.ItemFileReadStore");
				dojo.require("dijit.form.DateTextBox");
			</script>'
				.'<script type="text/javascript" src="typo3conf/ext/fsmi_exams/js/update_select.js" ></script>'
				.'<script type="text/javascript">
					init_update_select_lecture();
				</script>'
		;

		// file storages
		$content .=
			'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsModule" url="typo3temp/fsmiexams_module.json"></div>'
			.'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsField" url="typo3temp/fsmiexams_field.json"></div>'
			.'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsDegreeprogram" url="typo3temp/fsmiexams_degreeprogram.json"></div>';


		$content .= '
			<form action="'.$this->pi_getPageLink($GLOBALS["TSFE"]->id).'" method="POST" name="'.$this->extKey.'">
			<input type="hidden" name="no_cache" value="1" />
			<input type="hidden" name="'.$this->extKey.'[type]" value="'.self::kEDIT_TYPE_LECTURE.'" />';

		// hidden field for UID if editing lecturer
		if ($editUID)
			$content .= '<input type="hidden" name="'.$this->extKey.'[uid]" value="'.$editUID.'" />';

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
					value="'.$this->piVars['field'].'"
					style="width:300px;"
					name="'.$this->extKey.'[field]"
					id="'.$this->extKey.'_field"
				/>
				</td>
			</tr>';
		$content .= '</table></fieldset>';

		$content .= '<fieldset><legend>Datensatz für Vorlesung</legend><table>';
		// Modules
		$content .=
			'<tr>
				<td><label for="'.$this->extKey.'_module0">'.
					$this->LANG->getLL("tx_fsmiexams_lecture.module").
				':</label></td>
				<td>
					<input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsModule"
						earchAttr="name"
						query="{uid:\'*\'}"
						style="width:300px;"
						value="'.$this->piVars['module0'].'"
						name="'.$this->extKey.'[module0]"
						id="'.$this->extKey.'_module0"
						autocomplete="true"
					/>
				</td>
			</tr>';

		$content .=
			'<tr>
				<td><label for="'.$this->extKey.'_module1">'.
					$this->LANG->getLL("tx_fsmiexams_lecture.module").
				' + 1:</label></td>
				<td>
					<input dojoType="dijit.form.FilteringSelect" ';
		if (!$this->piVars['module0']) $content .= 'disabled="disabled"';
		$content .= '
						store="fsmiexamsModule"
						earchAttr="name"
						query="{uid:\'*\'}"
						style="width:300px;"
						value="'.$this->piVars['module1'].'"
						name="'.$this->extKey.'[module1]"
						id="'.$this->extKey.'_module1"
						autocomplete="true"
					/>
				</td>
			</tr>';

		$content .=
			'<tr>
				<td><label for="'.$thedit_form_lectureris->extKey.'_module2">'.
					$this->LANG->getLL("tx_fsmiexams_lecture.module").
				' + 2:</label></td>
				<td>
					<input dojoType="dijit.form.FilteringSelect" ';
		if (!$this->piVars['module1']) $content .= 'disabled="disabled"';
		$content .= '
						store="fsmiexamsModule"
						earchAttr="name"
						query="{uid:\'*\'}"
						style="width:300px;"
						value="'.$this->piVars['module2'].'"
						name="'.$this->extKey.'[module2]"
						id="'.$this->extKey.'_module2"
						autocomplete="true"
					/>
				</td>
			</tr>';

		$content .=
			'<tr>
				<td><label for="'.$this->extKey.'_module3">'.
					$this->LANG->getLL("tx_fsmiexams_lecture.module").
				' + 3:</label></td>
				<td>
					<input dojoType="dijit.form.FilteringSelect" ';
		if (!$this->piVars['module2']) $content .= 'disabled="disabled"';
		$content .= '
						store="fsmiexamsModule"
						earchAttr="name"
						query="{uid:\'*\'}"
						style="width:300px;"
						value="'.$this->piVars['module3'].'"
						name="'.$this->extKey.'[module3]"
						id="'.$this->extKey.'_module3"
						autocomplete="true"
					/>
				</td>
			</tr>';

		$content .=
			'<tr>
				<td><label for="'.$this->extKey.'[name]">'.
					$this->LANG->getLL("tx_fsmiexams_lecture.name").
				':</label></td>
				<td><input
					type="text"
					style="width:300px;"
					name="'.$this->extKey.'[name]"
					id="'.$this->extKey.'_name"
					value="'.htmlspecialchars($this->piVars["name"]).'"></td>
			</tr>
			</table></fieldset>
			<input type="submit" name="'.$this->prefixId.'[submit_button]"
				value="'.htmlspecialchars($this->pi_getLL("submit_button_label")).'">
			</form>';

		return $content;
	}

	/**
	 * This function provides a form to enter new lecturers or (if an editUID is given) to
	 * change an existing one.
	 */
	function createLecturerInputForm ($editUID) {

		if ($editUID)
			$content .= '<h2>'.$this->pi_getLL("edit_form_lecturer").'</h2>';
		else
			$content .= '<h2>'.$this->pi_getLL("input_form_lecturer").'</h2>';

		$content .= '
			<form action="'.$this->pi_getPageLink($GLOBALS["TSFE"]->id).'" method="POST" name="'.$this->extKey.'">';

		// hidden field for UID if editing lecturer
		if ($editUID)
			$content .= '<input type="hidden" name="'.$this->extKey.'[uid]" value="'.$editUID.'" />';

		//TODO switch to USER_INT object
		$content .= '
			<input type="hidden" name="no_cache" value="1" />
			<input type="hidden" name="'.$this->extKey.'[type]" value="'.self::kEDIT_TYPE_LECTURER.'" />
			<fieldset><legend>Datensatz für Dozent</legend><table>';

		// lecturer
		$content .= '
			<tr>
				<td><label for="'.$this->extKey.'[firstname]">'.
					$this->LANG->getLL("tx_fsmiexams_lecturer.firstname").
				':</label></td>
				<td><input
					type="text"
					name="'.$this->extKey.'[firstname]"
					id="'.$this->extKey.'_firstname"
					value="'.htmlspecialchars($this->piVars["firstname"]).'"></td>
			</tr>
			<tr>
				<td><label for="'.$this->extKey.'[lastname]">'.
					$this->LANG->getLL("tx_fsmiexams_lecturer.lastname").
				':</label></td>
				<td><input
					type="text"
					name="'.$this->extKey.'[lastname]"
					id="'.$this->extKey.'_lastname"
					value="'.htmlspecialchars($this->piVars["lastname"]).'"></td>
			</tr>
			</table></fieldset>
			<input type="submit" name="'.$this->prefixId.'[submit_button]"
				value="'.htmlspecialchars($this->pi_getLL("submit_button_label")).'">
			</form>';

		return $content;
	}

	function createExamInputForm ($editUID) {

		if ($editUID)
			$content .= '<h2>'.$this->pi_getLL("edit_form_exam").'</h2>';
		else
			$content .= '<h2>'.$this->pi_getLL("input_form_exam").'</h2>';

		// create JSON files
		$this->createLectureListJSON();
		$this->createModuleListJSON();
		$this->createFieldListJSON();
		$this->createDegreeprogramListJSON();
		$this->createLecturerListJSON();
		$this->createExamtypeListJSON();
		$this->createQualityListJSON();
		$this->createYearListJSON();
		$this->createTermListJSON();
		$this->createNumberListJSON();

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
					init_update_select_exam();
				</script>'
		;

		// file storages
		$content .=
			'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsModule" url="typo3temp/fsmiexams_module.json"></div>'
			.'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsField" url="typo3temp/fsmiexams_field.json"></div>'
			.'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsLecture" url="typo3temp/fsmiexams_lecture.json"></div>'
			.'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsDegreeprogram" url="typo3temp/fsmiexams_degreeprogram.json"></div>'
			.'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsLecturer" url="typo3temp/fsmiexams_lecturer.json"></div>'
			.'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsExamtype" url="typo3temp/fsmiexams_examtype.json"></div>'
			.'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsQuality" url="typo3temp/fsmiexams_quality.json"></div>'
			.'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsYear" url="typo3temp/fsmiexams_year.json"></div>'
			.'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsTerm" url="typo3temp/fsmiexams_term.json"></div>'
			.'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsNumber" url="typo3temp/fsmiexams_number.json"></div>'
			;

		// start of form
		$content .= '
			<form action="'.$this->pi_getPageLink($GLOBALS["TSFE"]->id).'" method="POST" name="'.$this->extKey.'" enctype="multipart/form-data">';

		if ($editUID)
			$content .= '<input type="hidden" name="'.$this->extKey.'[uid]" value="'.$editUID.'" />';

		$content .= '
			<input type="hidden" name="no_cache" value="1" />
			<input type="hidden" name="'.$this->extKey.'[type]" value="'.self::kEDIT_TYPE_EXAM.'" />
			';

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
		$content .= '<fieldset><legend>Datensatz für Prüfung</legend><table>';

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
			$content .= ' value="'.$this->piVars['lecture0'].'-0" ';
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
			$content .= ' value="'.$this->piVars['lecture1'].'-0" ';
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
			$content .= ' value="'.$this->piVars['lecture2'].'-0" ';
		$content .= '	name="'.$this->extKey.'[lecture2]"
						id="'.$this->extKey.'_lecture2"
						autocomplete="true"
					/>
				</td>
			</tr>';

		$content .= '
			<tr>
				<td><label for="'.$this->extKey.'_name">'.
					$this->LANG->getLL("tx_fsmiexams_exam.name").
				':</label></td>
				<td>
					<input dojoType="dijit.form.ValidationTextBox"
						value="'.$this->piVars['name'].'"
						name="'.$this->extKey.'[name]"
						id="'.$this->extKey.'_name"
					/>
				</td>
			</tr>
			<tr>
				<td><label for="'.$this->extKey.'_number">'.
					$this->LANG->getLL("tx_fsmiexams_exam.number").
				':</label></td>
				<td><input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsNumber"
						searchAttr="number"
						value="'.$this->piVars['number'].'"
						name="'.$this->extKey.'[number]"
						id="'.$this->extKey.'_number"
						value="'.htmlspecialchars($this->piVars["number"]).'"
						autocomplete="true" />
				</td>
			</tr>
			<tr>
				<td><label for="'.$this->extKey.'_term">'.
					$this->LANG->getLL("tx_fsmiexams_exam.term").
				':</label></td>
				<td><input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsTerm"
						searchAttr="name"
						value="'.$this->piVars['term'].'"
						name="'.$this->extKey.'[term]"
						id="'.$this->extKey.'_term"
						value="'.htmlspecialchars($this->piVars["term"]).'"
						autocomplete="true" />
				</td>
			</tr>
			<tr>
				<td><label for="'.$this->extKey.'_year">'.
					$this->LANG->getLL("tx_fsmiexams_exam.year").
				':</label></td>
				<td><input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsYear"
						searchAttr="year"
						value="'.$this->piVars['year'].'"
						name="'.$this->extKey.'[year]"
						id="'.$this->extKey.'_year"
						value="'.htmlspecialchars($this->piVars["year"]).'"
						autocomplete="true" />
				</td>
			</tr>
			<tr>
				<td><label for="'.$this->extKey.'_exactdate">'.
					$this->LANG->getLL("tx_fsmiexams_exam.exactdate").
				':</label></td>
				<td><input
						type="text"
						name="'.$this->extKey.'[exactdate]"
						id="'.$this->extKey.'_exactdate"
						dojoType="dijit.form.DateTextBox" ';
		if ($this->piVars['exactdate']!=0)
			$content .=' value="'.date('Y-m-d',intval($this->piVars["exactdate"])).'"';
		$content .= '	/></td>
			</tr>';

		// Lecturer
		$content .=
			'<tr>
				<td><label for="'.$this->extKey.'_lecturer0">'.
					$this->LANG->getLL("tx_fsmiexams_exam.lecturer").
				':</label></td>
				<td>
					<input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsLecturer"
						searchAttr="name"
						query="{uid:\'*\'}"
						value="'.$this->piVars['lecturer0'].'"
						name="'.$this->extKey.'[lecturer0]"
						id="'.$this->extKey.'_lecturer0"
						autocomplete="true"
					/>
				</td>
			</tr>';

		// Lecturer 2
		$content .=
			'<tr>
				<td><label for="'.$this->extKey.'_lecturer1">'.
					$this->LANG->getLL("tx_fsmiexams_exam.lecturer").
				' + 1:</label></td>
				<td>
					<input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsLecturer"
						searchAttr="name" ';
		if (!$this->piVars['lecturer0']) $content .= 'disabled="disabled"';
		$content .= '	query="{uid:\'*\'}"
						value="'.$this->piVars['lecturer1'].'"
						name="'.$this->extKey.'[lecturer1]"
						id="'.$this->extKey.'_lecturer1"
						autocomplete="true"
					/>
				</td>
			</tr>';

		// Lecturer 3
		$content .=
			'<tr>
				<td><label for="'.$this->extKey.'_lecturer2">'.
					$this->LANG->getLL("tx_fsmiexams_exam.lecturer").
				' + 2:</label></td>
				<td>
					<input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsLecturer"
						searchAttr="name" ';
		if (!$this->piVars['lecturer1']) $content .= 'disabled="disabled"';
		$content .= '	query="{uid:\'*\'}"
						value="'.$this->piVars['lecturer2'].'"
						name="'.$this->extKey.'[lecturer2]"
						id="'.$this->extKey.'_lecturer2"
						autocomplete="true"
					/>
				</td>
			</tr>';

		$content .= '
			<tr>
				<td><label for="'.$this->extKey.'_approved">'.
					$this->LANG->getLL("tx_fsmiexams_exam.approved").
				':</label></td>
				<td><input dojoType="dijit.form.CheckBox" ';
		if ($this->piVars['approved']==1) $content .= ' checked="checked" ';
		$content .= '	name="'.$this->extKey.'[approved]"
						id="'.$this->extKey.'_approved"
						value="'.htmlspecialchars($this->piVars["approved"]).'" />
				</td>
			</tr>
			<tr>
				<td><label for="'.$this->extKey.'_file">'.
					$this->LANG->getLL("tx_fsmiexams_exam.file").
				':</label></td>
				<td><input ';
		if ($editUID) $content .= 'disabled="disabled"';
		$content .= '	type="file"
					value="'.$this->piVars['file'].'"
					name="'.$this->extKey.'[file]"
					id="'.$this->extKey.'_file"
					value="'.htmlspecialchars($this->piVars["file"]).'"></td>
			</tr>
			<tr>
				<td><label for="'.$this->extKey.'_material">'.
					$this->LANG->getLL("tx_fsmiexams_exam.material").
				':</label></td>
				<td><input ';
		if ($editUID) $content .= 'disabled="disabled"';
		$content .= '	type="file"
					value="'.$this->piVars['material'].'"
					name="'.$this->extKey.'[material]"
					id="'.$this->extKey.'_material"
					value="'.htmlspecialchars($this->piVars["material"]).'"></td>
			</tr>
			<tr>
				<td><label for="'.$this->extKey.'_quality">'.
					$this->LANG->getLL("tx_fsmiexams_exam.quality").
				':</label></td>
				<td><input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsQuality"
						searchAttr="name"
						value="'.$this->piVars['quality'].'"
						name="'.$this->extKey.'[quality]"
						id="'.$this->extKey.'_quality"
						autocomplete="true"
						value="'.htmlspecialchars($this->piVars["quality"]).'" />
				</td>
			</tr>
			<tr>
				<td><label for="'.$this->extKey.'_examtype">'.
					$this->LANG->getLL("tx_fsmiexams_exam.examtype").
				':</label></td>
				<td><input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsExamtype"
						searchAttr="name"
						value="'.$this->piVars['examtype'].'"
						name="'.$this->extKey.'[examtype]"
						id="'.$this->extKey.'_examtype"
						autocomplete="true"
						value="'.htmlspecialchars($this->piVars["examtype"]).'" />
				</td>
			</tr>
			</table></fieldset>
			<input type="submit" name="'.$this->extKey.'[submit_button]"
				value="'.htmlspecialchars($this->pi_getLL("submit_button_label")).'">
			</form>';

		return $content;
	}

	function createModuleListJSON () {
		$fileContent = '';

		// file opening
		$fileContent  =
			'{'."\n".
				'identifier: "uid",'."\n".
				'items: ['."\n";

		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
												FROM tx_fsmiexams_module
												WHERE deleted=0 AND hidden=0');

		while ($res && $row = mysql_fetch_assoc($res))
			$fileContent .= '{name:"'.$row['name'].'", uid:"'.$row['uid'].'", field:"'.$row['field'].'"},'."\n";

		// empty entry for each field
		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
												FROM tx_fsmiexams_field
												WHERE deleted=0 AND hidden=0');

		$negativeCntr = -1;
		while ($res && $row = mysql_fetch_assoc($res))
			$fileContent .= '{name:"---", uid:"'.$negativeCntr--.'", field:"'.$row['uid'].'"},'."\n";

		// file ending
		$fileContent .= '] }';

		// writing file
		t3lib_div::writeFileToTypo3tempDir (
										PATH_site."typo3temp/".'fsmiexams_module.json',
										$fileContent
										);


	}

	function createFieldListJSON () {
		$fileContent = '';

		// file opening
		$fileContent  =
			'{'."\n".
				'identifier: "uid",'."\n".
				'items: ['."\n";

		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
												FROM tx_fsmiexams_field
												WHERE deleted=0 AND hidden=0');

		while ($res && $row = mysql_fetch_assoc($res))
			$fileContent .= '{name:"'.$row['name'].'", uid:"'.$row['uid'].'", degreeprogram:"'.$row['degreeprogram'].'"},'."\n";

		// file ending
		$fileContent .= '] }';

		// writing file
		t3lib_div::writeFileToTypo3tempDir (
										PATH_site."typo3temp/".'fsmiexams_field.json',
										$fileContent
										);


	}

	function createDegreeprogramListJSON () {
		$fileContent = '';

		// file opening
		$fileContent  =
			'{'."\n".
				'identifier: "uid",'."\n".
				'items: ['."\n";

		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
												FROM tx_fsmiexams_degreeprogram
												WHERE deleted=0 AND hidden=0');

		while ($res && $row = mysql_fetch_assoc($res))
			$fileContent .= '{name:"'.$row['name'].'", uid:"'.$row['uid'].'"},'."\n";

		// file ending
		$fileContent .= '] }';

		// writing file
		t3lib_div::writeFileToTypo3tempDir (
										PATH_site."typo3temp/".'fsmiexams_degreeprogram.json',
										$fileContent
										);

	}

	function createLectureListJSON () {
		$fileContent = '';

		// file opening
		$fileContent  =
			'{'."\n".
				'identifier: "line",'."\n".
				'items: ['."\n";

		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
												FROM tx_fsmiexams_lecture
												WHERE deleted=0 AND hidden=0
												ORDER BY name');

		while ($res && $row = mysql_fetch_assoc($res)) {
			$counter = 0;
			$modules = explode(',',$row['module']);
			$master = 1;
			foreach ($modules as $module) {
				$fileContent .= '{name:"'.$row['name'].'", uid:"'.$row['uid'].'", line:"'.$row['uid'].'-'.$counter++.'", module:"'.$module.'", master:"'.$master.'"},'."\n";
				$master = 0;
			}
		}

		// empty entry for each module
		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
												FROM tx_fsmiexams_module
												WHERE deleted=0 AND hidden=0
												ORDER BY name');

		$negativeCntr = -1;
		while ($res && $row = mysql_fetch_assoc($res)) {
			// use master/client variable to determine that only this entry should be presented when no selection is done
			if ($negativeCntr==-1)
				$master=1;
			else
				$master=0;

			$fileContent .= '{name:"---", uid:"0", line:"'.$negativeCntr--.'", module:"'.$row['uid'].'", master:"'.$master.'"},'."\n";
		}


		// file ending
		$fileContent .= '] }';

		// writing file
		t3lib_div::writeFileToTypo3tempDir (
										PATH_site."typo3temp/".'fsmiexams_lecture.json',
										$fileContent
										);


	}

	function createLecturerListJSON () {
		$fileContent = '';

		// file opening
		$fileContent  =
			'{'."\n".
				'identifier: "uid",'."\n".
				'items: ['."\n";

		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
												FROM tx_fsmiexams_lecturer
												WHERE deleted=0 AND hidden=0
												ORDER BY lastname');

		while ($res && $row = mysql_fetch_assoc($res))
			$fileContent .= '{name:"'.$row['lastname'].', '.$row['firstname'].'", uid:"'.$row['uid'].'"},'."\n";

		// empty one
		$fileContent .= '{name:"---", uid:"-1"},'."\n";

		// file ending
		$fileContent .= '] }';

		// writing file
		t3lib_div::writeFileToTypo3tempDir (
										PATH_site."typo3temp/".'fsmiexams_lecturer.json',
										$fileContent
										);


	}

	function createExamtypeListJSON () {
		$fileContent = '';

		// file opening
		$fileContent  =
			'{'."\n".
				'identifier: "uid",'."\n".
				'items: ['."\n";

		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
												FROM tx_fsmiexams_examtype
												WHERE deleted=0 AND hidden=0');

		while ($res && $row = mysql_fetch_assoc($res))
			$fileContent .= '{name:"'.$row['description'].'", uid:"'.$row['uid'].'"},'."\n";

		// file ending
		$fileContent .= '] }';

		// writing file
		t3lib_div::writeFileToTypo3tempDir (
										PATH_site."typo3temp/".'fsmiexams_examtype.json',
										$fileContent
										);


	}

	function createYearListJSON () {
		$fileContent = '';

		// file opening
		$fileContent  =
			'{'."\n".
				'identifier: "year",'."\n".
				'items: ['."\n";

		for ($i=intval(date('Y')); $i>=1976; $i--)
			$fileContent .= '{year:"'.$i.'"},'."\n";

		// file ending
		$fileContent .= '] }';

		// writing file
		t3lib_div::writeFileToTypo3tempDir (
										PATH_site."typo3temp/".'fsmiexams_year.json',
										$fileContent
										);


	}

	function createTermListJSON () {
		$fileContent = '';

		// file opening
		$fileContent  =
			'{'."\n".
				'identifier: "uid",'."\n".
				'items: ['."\n";

		for ($i=0; $i<2; $i++)
			$fileContent .= '{name:"'.$GLOBALS['TSFE']->sL('LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.term.I.'.$i).'", uid:"'.$i.'"},'."\n";

		// file ending
		$fileContent .= '] }';

		// writing file
		t3lib_div::writeFileToTypo3tempDir (
										PATH_site."typo3temp/".'fsmiexams_term.json',
										$fileContent
										);


	}

	function createNumberListJSON () {
		$fileContent = '';

		// file opening
		$fileContent  =
			'{'."\n".
				'identifier: "number",'."\n".
				'items: ['."\n";

		for ($i=1; $i<10; $i++)
			$fileContent .= '{number:"'.$i.'"},'."\n";

		$fileContent .= '{number:""},'."\n";

		// file ending
		$fileContent .= '] }';

		// writing file
		t3lib_div::writeFileToTypo3tempDir (
										PATH_site."typo3temp/".'fsmiexams_number.json',
										$fileContent
										);


	}

	function createQualityListJSON () {
		$fileContent = '';

		// file opening
		$fileContent  =
			'{'."\n".
				'identifier: "uid",'."\n".
				'items: ['."\n";

		for ($i=0; $i<3; $i++)
			$fileContent .= '{name:"'.$GLOBALS['TSFE']->sL('LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.quality.I.'.$i).'", uid:"'.$i.'"},'."\n";

		// file ending
		$fileContent .= '] }';

		// writing file
		t3lib_div::writeFileToTypo3tempDir (
										PATH_site."typo3temp/".'fsmiexams_quality.json',
										$fileContent
										);


	}

	function saveFormData () {
		// get form data
		$formData = t3lib_div::_POST($this->extKey);

		// switch by hidden type field
		switch (intval($formData['type'])) {
			case self::kEDIT_TYPE_LECTURE: {

				if (intval($formData['uid']!=0)) { // update data
					// get module list
					$modules = array();
					for ($i=0; $i<4; $i++) {
						if (intval($formData['module'.$i])<=0)
							continue;
						array_push($modules,intval($formData['module'.$i]));
					}
					$modules = array_unique($modules); // delete duplicate values
					$moduleTXT = implode(',',$modules);


					$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
									'tx_fsmiexams_lecture',
									'uid = '.intval($formData['uid']),
									array (
										'tstamp' => time(),
										'name' => $GLOBALS['TYPO3_DB']->quoteStr($formData['name'], 'tx_fsmiexams_lecture'),
										'module' => $GLOBALS['TYPO3_DB']->quoteStr($moduleTXT, 'tx_fsmiexams_lecture')
										)
									);
					// output info, if ok
					if ($res)
						return tx_fsmiexams_div::printSystemMessage(
							tx_fsmiexams_div::kSTATUS_INFO,
							'Lecture &quot;'.htmlentities($formData['name']).'&quot; updated (UID:'.intval($formData['uid']).')');
					else
						return tx_fsmiexams_div::printSystemMessage(
							tx_fsmiexams_div::kSTATUS_ERROR,
							$this->LANG->getLL("tx_fsmiexams_general.message.sql_error"));
				}

				else { // enter new entry
					// get module list
					$modules = array();
					for ($i=0; $i<4; $i++) {
						if (intval($formData['module'.$i])<=0)
							continue;
						array_push($modules,intval($formData['module'.$i]));
					}
					$modules = array_unique($modules); // delete duplicate values
					$moduleTXT = implode(',',$modules);

					// save everything
					$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
										'tx_fsmiexams_lecture',
										array (	'pid' => $this->storageLecture,
												'crdate' => time(),
												'tstamp' => time(),
												'l10n_diffsource' => 'a:4:{s:16:"sys_language_uid";N;s:6:"hidden";N;s:4:"name";N;s:6:"module";N;}',
												'name' => $GLOBALS['TYPO3_DB']->quoteStr($formData['name'], 'tx_fsmiexams_lecture'),
												'module' => $GLOBALS['TYPO3_DB']->quoteStr($moduleTXT, 'tx_fsmiexams_lecture'),
										));

					// output info, if okmierung I
					if ($res)
						return tx_fsmiexams_div::printSystemMessage(
							tx_fsmiexams_div::kSTATUS_INFO,
							'Lecture saved: '.htmlentities(utf8_decode($formData['name'])));
					else
						return tx_fsmiexams_div::printSystemMessage(
							tx_fsmiexams_div::kSTATUS_ERROR,
							$this->LANG->getLL("tx_fsmiexams_general.message.sql_error"));
				}
			} break;

			case self::kEDIT_TYPE_EXAM: {
				/* Prework for all saving mechanisms */

				// get lecture list
				$lectures = array();
				for ($i=0; $i<4; $i++) {
					if (intval($formData['lecture'.$i])<=0)
						continue;
					array_push($lectures,intval($formData['lecture'.$i]));
				}
				$lectures = array_unique($lectures); // delete duplicate values
				$lectureTXT = implode(',',$lectures);

				// get lecturer list
				$lecturers = array();
				for ($i=0; $i<4; $i++) {
					if (intval($formData['lecturer'.$i])<=0)
						continue;
					array_push($lecturers,intval($formData['lecturer'.$i]));
				}
				$lecturers = array_unique($lecturers); // delete duplicate values
				$lecturerTXT = implode(',',$lecturers);

				// get approved button
				if (isset($formData['approved']))
					$approved = 1;

				// start saving
				if (intval($formData['uid']!=0)) { // update data
					// save everything
					$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
										'tx_fsmiexams_exam',
										'uid = '.intval($formData['uid']),
										array (	'tstamp' => time(),
												'name' => $GLOBALS['TYPO3_DB']->quoteStr($formData['name'], 'tx_fsmiexams_exam'),
												'number' => intval($formData['number']),
												'term' => intval($formData['term']),
												'lecture' => $GLOBALS['TYPO3_DB']->quoteStr($lectureTXT, 'tx_fsmiexams_exam'),
												'year' => intval($formData['year']),
												'exactdate' => strtotime(htmlspecialchars($formData['exactdate'])),
												'lecturer' => $GLOBALS['TYPO3_DB']->quoteStr($lecturerTXT, 'tx_fsmiexams_exam'),
												'approved' => $approved,
												'quality' => intval($formData['quality']),
												'examtype' => intval($formData['examtype']),
										));
					// output info, if ok
					if ($res)
						return tx_fsmiexams_div::printSystemMessage(
								tx_fsmiexams_div::kSTATUS_OK,
								'Update Query ok.');
				}
				else { // insert new db entry

					// save/move files
					$formDataFiles = $_FILES[$this->extKey];
					$examFileName = $this->saveExamFiles($formDataFiles['tmp_name']['file'], $formDataFiles['name']['file']);
					$materialFileName = $this->saveExamFiles($formDataFiles['tmp_name']['material'], $formDataFiles['name']['material']);

					// save everything
					$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
										'tx_fsmiexams_exam',
										array (	'pid' => $this->storageExam,
												'crdate' => time(),
												'tstamp' => time(),
												'l10n_diffsource' => 'a:12:{s:16:"sys_language_uid";N;s:6:"hidden";N;s:4:"name";N;s:6:"number";N;s:7:"lecture";N;s:4:"term";N;s:4:"year";N;s:8:"examtype";N;s:9:"exactdate";N;s:8:"lecturer";N;s:8:"approved";N;s:4:"file";N;}',
												'name' => $GLOBALS['TYPO3_DB']->quoteStr($formData['name'], 'tx_fsmiexams_exam'),
												'number' => intval($formData['number']),
												'term' => intval($formData['term']),
												'lecture' => $GLOBALS['TYPO3_DB']->quoteStr($lectureTXT, 'tx_fsmiexams_exam'),
												'year' => intval($formData['year']),
												'exactdate' => strtotime(htmlspecialchars($formData['exactdate'])),
												'lecturer' => $GLOBALS['TYPO3_DB']->quoteStr($lecturerTXT, 'tx_fsmiexams_exam'),
												'approved' => $approved,
												'file' => $examFileName,
												'material' => $materialFileName,
												'quality' => intval($formData['quality']),
												'examtype' => intval($formData['examtype']),
										));

					// output info, if ok
					if ($res)
						return tx_fsmiexams_div::printSystemMessage(
								tx_fsmiexams_div::kSTATUS_OK,
								'<div>'.
									'<h4>Exam data was saved</h4>
									<ul>'.
										'<li><strong>Name:</strong> '.htmlspecialchars(utf8_decode($formData['name'])).'</li>'.
										'<li><strong>Lecture(s):</strong> '.tx_fsmiexams_div::lectureToText($lectureTXT,0).'</li>'.
										'<li><strong>Lecturer(s):</strong> '.tx_fsmiexams_div::lecturerToText($lecturerTXT,0).'</li>'.
										'<li><strong>Year/Term/No.:</strong> '.intval($formData['term']).' '.intval($formData['year']).' Nr. '.intval($formData['number']).'</li>'.
										'<li><strong>Date:</strong> '.date('d.m.Y',strtotime(htmlspecialchars($formData['exactdate']))).'</li>'.
									'</ul>'.
								'</div>');
					else
						return tx_fsmiexams_div::printSystemMessage(
							tx_fsmiexams_div::kSTATUS_ERROR,
							$this->LANG->getLL("tx_fsmiexams_general.message.sql_error"));
				}
			} break;

			case self::kEDIT_TYPE_LECTURER: {

				if (intval($formData['uid'])!=0) { // update existing one
					$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
									'tx_fsmiexams_lecturer',
									'uid = '.intval($formData['uid']),
									array (
										'firstname' => $GLOBALS['TYPO3_DB']->quoteStr($formData['firstname']),
										'lastname' => $GLOBALS['TYPO3_DB']->quoteStr($formData['lastname'])
										)
									);
					if ($res)
						return tx_fsmiexams_div::printSystemMessage(
							tx_fsmiexams_div::kSTATUS_INFO,
							'Lecturer successfully updated (UID '.intval($formData['uid']).'): '.$formData['lastname'].', '.$formData['firstname']);
				}
				else { // create new one
					$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
									'tx_fsmiexams_lecturer',
									array (	'pid' => $this->storageLecturer,
											'crdate' => time(),
											'tstamp' => time(),
											'firstname' => $GLOBALS['TYPO3_DB']->quoteStr($formData['firstname'], 'tx_fsmiexams_lecturer'),
											'lastname' => $GLOBALS['TYPO3_DB']->quoteStr($formData['lastname'], 'tx_fsmiexams_lecturer'),
									));
					if ($res)
						return tx_fsmiexams_div::printSystemMessage(
							tx_fsmiexams_div::kSTATUS_INFO,
							'Lecturer successfully created: '.$formData['lastname'].', '.$formData['firstname']);
				}

				// this point should never be reached
				return tx_fsmiexams_div::printSystemMessage(
					tx_fsmiexams_div::kSTATUS_ERROR,
					$this->LANG->getLL("tx_fsmiexams_general.message.sql_error"));
			} break;
		}

	}

	/**
	 * This function saves files (from POST fileupload) into the 'uploads/fx_fsmiexams/' directory
	 * At the moment no validation is done if this file really exists, please take care!
	 * @param $tmpName the temporary name for the file uploaded by webserver
	 * @param $fileName the name of the file it should be saved as
	 * @return $fileName of the save file
	 */

	function saveExamFiles($tmpName, $fileName) {
		// TODO generate some error codes
		// TODO make shure that we have "*.pdf"

		// move file
		$file = t3lib_div::upload_to_tempfile($tmpName);

		// make filename valid
		$fileName = preg_replace(
			array("/\s+/", "/[^-\.\w]+/"),
			array("_", ""),
			trim($fileName));

		$usedFilenames = t3lib_div::getFilesInDir(	'uploads/tx_fsmiexams/',
																$extensionList = 'pdf');
		// save file
		if ($file) {
			$cnt=0;
			$baseFilename = basename($fileName, ".pdf");
			// make filename unique
			while (array_search($fileName,$usedFilenames)==true)
				$fileName = $baseFilename.'_'.$cntr++.'.pdf';
				t3lib_div::upload_copy_move($file, 'uploads/tx_fsmiexams/'.$fileName);
			}
		t3lib_div::unlink_tempfile($file);

		return $fileName;
	}

	/**
	 * This function validates input strings
	 * TODO compare with ER diagram -> is everything asked?
	 * TODO give hints were error could be ;-)
	 * @param $type is constant for edit-type
	 * @return boolean true iff everything is fine
	 */
	function validateFormData($type) {

		$formData = t3lib_div::_POST($this->extKey);
		$formDataFiles = $_FILES[$this->extKey];

		switch($type) {
			case self::kEDIT_TYPE_EXAM: {

				if (htmlspecialchars($formData['lecture0'])=='')
					return false;
				if ($formData['name']=='')
					return false;
				if (htmlspecialchars($formData['lecturer0'])=='')
					return false;
			  // TODO to test this we have problems with updates where this is not mandatory, need complete new file framework
// 				if ($formDataFiles['tmp_name']['file']=='')
// 					return false;

				break;
			}
			default:
				return true;
		}
		return true;

	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/pi4/class.tx_fsmiexams_pi4.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/pi4/class.tx_fsmiexams_pi4.php']);
}

?>