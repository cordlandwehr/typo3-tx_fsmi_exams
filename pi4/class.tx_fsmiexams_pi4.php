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
	var $kNONE			= 0;
	var $kMODULE		= 1;
	var $kEXAM			= 2;
	var $kLECTURE		= 3;
	var $kLECTURER		= 4;
	
	
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
		

		// save POST data if received
		if (t3lib_div::_POST($this->extKey))
			$this->saveFormData();
		
		// type selection head
		$content .= $this->createTypeSelector();
					
		// select input type
		$GETcommands = t3lib_div::_GP($this->extKey);	// can be both: POST or GET
		switch (intval($GETcommands['type'])) {
			case $this->kLECTURE:
				$content .= $this->createLectureInputForm(); break;
			case $this->kEXAM;
				$content .= $this->createExamInputForm(); break;
			case $this->kLECTURER;
				$content .= $this->createLecturerInputForm(); break;
			default:
				break;
		}
				
		return $this->pi_wrapInBaseClass($content);
	}
	
	function createTypeSelector () {
		$content = '<div>';
		$content .= $this->pi_linkTP('New Lecture', 
								array (	$this->extKey.'[type]' => $this->kLECTURE));
		$content .= ' | ';
		$content .= $this->pi_linkTP('New Exam', 
								array (	$this->extKey.'[type]' => $this->kEXAM));
		$content .= ' | ';
		$content .= $this->pi_linkTP('New Lecturer', 
								array (	$this->extKey.'[type]' => $this->kLECTURER));
		$content .= '</div>';
		
		return $content;
								
	}
	
	function createLectureInputForm () {

		// create JSON files
		$this->createModuleListJSON();
		$this->createFieldListJSON();
		$this->createDegreeprogramListJSON();
		
		$GLOBALS['TSFE']->additionalHeaderData['fsmi_exam_pi4_widget'] = 
			'<script type="text/javascript">
				dojo.require("dojo.parser");
				dojo.require("dijit.form.FilteringSelect");
				dojo.require("dojo.data.ItemFileReadStore");
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
			<h2>Lecture Input</h2> 
			<form action="'.$this->pi_getPageLink($GLOBALS["TSFE"]->id).'" method="POST" name="'.$this->extKey.'">
			<input type="hidden" name="no_cache" value="1" />
			<input type="hidden" name="'.$this->extKey.'[type]" value="'.$this->kLECTURE.'" />
			<table>';
			
		// Degree Program
		$content .= '
			<tr>	
				<td><label for="'.$this->extKey.'_degreeprogram">Degree Program:</label></td>
				<td>
				<input 
					dojoType="dijit.form.FilteringSelect"
					store="fsmiexamsDegreeprogram" 
					searchAttr="name"
					autocomplete="true"
					name="'.$this->extKey.'[degreeprogram]" 
					id="'.$this->extKey.'_degreeprogram"
				/>
				</td>
			</tr>';
		
		// Field
		$content .= '
			<tr>	
				<td><label for="'.$this->extKey.'_field">Field:</label></td>
				<td>
				<input dojoType="dijit.form.FilteringSelect" 
					store="fsmiexamsField"
					searchAttr="name"
					autocomplete="true"
					name="'.$this->extKey.'[field]" 
					id="'.$this->extKey.'_field"
				/>
				</td>
			</tr>';

		// Modules
		$content .= 
			'<tr>	
				<td><label for="'.$this->extKey.'_module">Module:</label></td>
				<td>
					<input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsModule"						searchAttr="name"
						query="{uid:\'*\'}"
						name="'.$this->extKey.'[module]" 
						id="'.$this->extKey.'_module"
						autocomplete="true"
					/>
				</td>
			</tr>
			<tr>	
				<td><label for="'.$this->extKey.'[name]">Lecture Name:</label></td>
				<td><input 
					type="text" 
					name="'.$this->extKey.'[name]" 
					id="'.$this->extKey.'_name"  	
					value="'.htmlspecialchars($this->piVars["name"]).'"></td>
			</tr>
			</table>
			<input type="submit" name="'.$this->prefixId.'[submit_button]" 
				value="'.htmlspecialchars($this->pi_getLL("submit_button_label")).'">
			</form>';
		
		return $content;
	}
	
	function createLecturerInputForm () {
	
		$content .= '
			<h2>Lecturer Input</h2> 
			<form action="'.$this->pi_getPageLink($GLOBALS["TSFE"]->id).'" method="POST" name="'.$this->extKey.'">
			<input type="hidden" name="no_cache" value="1" />
			<input type="hidden" name="'.$this->extKey.'[type]" value="'.$this->kLECTURER.'" />
			<table>';
			
		// Degree Program
		$content .= '
			<tr>	
				<td><label for="'.$this->extKey.'[firstname]">Forename:</label></td>
				<td><input 
					type="text" 
					name="'.$this->extKey.'[firstname]" 
					id="'.$this->extKey.'_firstname"  	
					value="'.htmlspecialchars($this->piVars["firstname"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->extKey.'[lastname]">Lastname:</label></td>
				<td><input 
					type="text" 
					name="'.$this->extKey.'[lastname]" 
					id="'.$this->extKey.'_lastname"  	
					value="'.htmlspecialchars($this->piVars["lastname"]).'"></td>
			</tr>
			</table>
			<input type="submit" name="'.$this->prefixId.'[submit_button]" 
				value="'.htmlspecialchars($this->pi_getLL("submit_button_label")).'">
			</form>';
		
		return $content;
	}
	
	function createExamInputForm () {
 
		// create JSON files
		$this->createLectureListJSON();
		$this->createModuleListJSON();
		$this->createFieldListJSON();
		$this->createDegreeprogramListJSON();
		$this->createLecturerListJSON();
		
		$GLOBALS['TSFE']->additionalHeaderData['fsmi_exam_pi4_widget'] = 
			'<script type="text/javascript">
				dojo.require("dojo.parser");
				dojo.require("dijit.form.FilteringSelect");
				dojo.require("dojo.data.ItemFileReadStore");
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
			.'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsLecturer" url="typo3temp/fsmiexams_lecturer.json"></div>';
		
		$content .= '
			<h2>Exam Input</h2> 
			<form action="'.$this->pi_getPageLink($GLOBALS["TSFE"]->id).'" method="POST" name="'.$this->extKey.'">
			<input type="hidden" name="no_cache" value="1" />
			<input type="hidden" name="'.$this->extKey.'[type]" value="'.$this->kEXAM.'" />
			<table>
			';

		// Degree Program
		$content .= '
			<tr>	
				<td><label for="'.$this->extKey.'_degreeprogram">Degree Program:</label></td>
				<td>
				<input 
					dojoType="dijit.form.FilteringSelect"
					store="fsmiexamsDegreeprogram" 
					searchAttr="name"
					autocomplete="true"
					name="'.$this->extKey.'[degreeprogram]" 
					id="'.$this->extKey.'_degreeprogram"
				/>
				</td>
			</tr>';
		
		// Field
		$content .= '
			<tr>	
				<td><label for="'.$this->extKey.'_field">Field:</label></td>
				<td>
				<input dojoType="dijit.form.FilteringSelect" 
					store="fsmiexamsField"
					searchAttr="name"
					autocomplete="true"
					name="'.$this->extKey.'[field]" 
					id="'.$this->extKey.'_field"
				/>
				</td>
			</tr>';
		
		// Modules
		$content .= 
			'<tr>	
				<td><label for="'.$this->extKey.'_module">Module:</label></td>
				<td>
					<input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsModule"
						searchAttr="name"
						query="{uid:\'*\'}"
						name="'.$this->extKey.'[module]" 
						id="'.$this->extKey.'_module"
						autocomplete="true"
					/>
				</td>
			</tr>';
		
		// Lecture
		$content .= 
			'<tr>	
				<td><label for="'.$this->extKey.'_module">Lecture:</label></td>
				<td>
					<input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsLecture"
						searchAttr="name"
						query="{uid:\'*\'}"
						name="'.$this->extKey.'[lecture]" 
						id="'.$this->extKey.'_lecture"
						autocomplete="true"
					/>
				</td>
			</tr>';
		
		$content .= '
			<tr>	
				<td><label for="'.$this->extKey.'_name">Exam Name:</label></td>
				<td>
					<input dojoType="dijit.form.ValidationTextBox" 
						name="'.$this->extKey.'[name]" 
						id="'.$this->extKey.'_name" 
						
					/>
				</td>
			</tr>
			<tr>	
				<td><label for="'.$this->extKey.'[number]">Number:</label></td>
				<td><input type="text" name="'.$this->extKey.'[number]" id="'.$this->extKey.'_number"  	
					value="'.htmlspecialchars($this->piVars["number"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->extKey.'[term]">Term:</label></td>
				<td><input type="text" name="'.$this->extKey.'[term]" id="'.$this->extKey.'_term"  	
					value="'.htmlspecialchars($this->piVars["term"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->extKey.'[year]">Year:</label></td>
				<td><input type="text" name="'.$this->extKey.'[year]" id="'.$this->extKey.'_year"  	
					value="'.htmlspecialchars($this->piVars["year"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->extKey.'[exactdate]">Day of Exam:</label></td>
				<td><input type="text" name="'.$this->extKey.'[exactdate]" id="'.$this->extKey.'_exactdate"  	
					value="'.htmlspecialchars($this->piVars["exactdate"]).'"></td>
			</tr>';
		
		// Lecturer
		$content .= 
			'<tr>	
				<td><label for="'.$this->extKey.'_module">Lecturer:</label></td>
				<td>
					<input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsLecturer"
						searchAttr="name"
						query="{uid:\'*\'}"
						name="'.$this->extKey.'[lecturer]" 
						id="'.$this->extKey.'_lecturer"
						autocomplete="true"
					/>
				</td>
			</tr>

			<tr>	
				<td><label for="'.$this->extKey.'[approved]">Approved:</label></td>
				<td><input type="text" name="'.$this->extKey.'[approved]" id="'.$this->extKey.'_approved"  	
					value="'.htmlspecialchars($this->piVars["approved"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->extKey.'[file]">Exam File:</label></td>
				<td><input type="text" name="'.$this->extKey.'[file]" id="'.$this->extKey.'_file"
					value="'.htmlspecialchars($this->piVars["file"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->extKey.'[material]">Add. Material:</label></td>
				<td><input type="text" name="'.$this->extKey.'[material]" id="'.$this->extKey.'_material"  	
					value="'.htmlspecialchars($this->piVars["material"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->extKey.'[quality]">Quality:</label></td>
				<td><input type="text" name="'.$this->extKey.'[quality]" id="'.$this->extKey.'_quality"  	
					value="'.htmlspecialchars($this->piVars["quality"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->extKey.'[examtype]">Exam Type:</label></td>
				<td><input type="text" name="'.$this->extKey.'[examtype]" id="'.$this->extKey.'_examtype"  	
					value="'.htmlspecialchars($this->piVars["examtype"]).'"></td>
			</tr>
			</table>
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
				'identifier: "uid",'."\n".
				'items: ['."\n";
			
		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT * 
												FROM tx_fsmiexams_lecture 
												WHERE deleted=0 AND hidden=0');
		
		while ($res && $row = mysql_fetch_assoc($res))
			$fileContent .= '{name:"'.$row['name'].'", uid:"'.$row['uid'].'", module:"'.$row['module'].'"},'."\n";
		
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
												WHERE deleted=0 AND hidden=0');
		
		while ($res && $row = mysql_fetch_assoc($res))
			$fileContent .= '{name:"'.$row['lastname'].', '.$row['firstname'].'", uid:"'.$row['uid'].'"},'."\n";
		
		// file ending
		$fileContent .= '] }';
		
		// writing file
		t3lib_div::writeFileToTypo3tempDir (	
										PATH_site."typo3temp/".'fsmiexams_lecturer.json',
										$fileContent 
										);
		
		
	}
	
	function saveFormData () {
		// get form data
		$formData = t3lib_div::_POST($this->extKey);

		// switch by hidden type field
		switch (intval($formData['type'])) {
			case $this->kLECTURE: {
			;//	debug('adsf');
			} break;
				
		}
		

		
//		$protocolPost = t3lib_div::_POST($this->extKey);
//		// test if there are post variables for new protocol
//		if (t3lib_div::_POST('protocol_new')) {
//			$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(	'tx_fsmiprotokolle_list',
//													array (	'pid' => $this->storePidNew,
//															'crdate' => time(),
//															'tstamp' => time(),
//															'meeting_date' => strtotime($protocolPost['meeting_date']),
//															'protocol' => $protocolPost['protocol'],
//															'reviewer_a' => $protocolPost['reviewer_a'],
//															'reviewer_b' => $protocolPost['reviewer_b'],
//													));
		
		
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/pi4/class.tx_fsmiexams_pi4.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/pi4/class.tx_fsmiexams_pi4.php']);
}

?>