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
		
		if (isset($this->piVars['submit_button'])) {
			//TODO do something 
		}
	
		
		// first attempt do make form
		$content = $this->createLectureInputForm();
				
		return $this->pi_wrapInBaseClass($content);
	}
	
	function createLectureInputForm () {

		// create Lecture TXT file
		$this->createModuleListTXT();
		$this->createFieldListTXT();
		$this->createDegreeprogramListTXT();
		
		$GLOBALS['TSFE']->additionalHeaderData['fsmi_exam_pi4_widget'] = 
			'<script type="text/javascript">
				dojo.require("dojo.parser");
				dojo.require("dijit.form.FilteringSelect");
				dojo.require("dojo.data.ItemFileReadStore");
			</script>'
				.'<script type="text/javascript" src="typo3conf/ext/fsmi_exams/js/update_select.js" ></script>'
				.'<script type="text/javascript">
					init_update_select();
				</script>'
		;
		
		// file storages
		$content .= 
			'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsModule" url="typo3temp/fsmiexams_module.json"></div>'
			.'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsField" url="typo3temp/fsmiexams_field.json"></div>'
			.'<div dojoType="dojo.data.ItemFileReadStore" jsId="fsmiexamsDegreeprogram" url="typo3temp/fsmiexams_degreeprogram.json"></div>';
		
		
		$content .= '
			<h2>Lecture Input</h2> 
			<form action="'.$this->pi_getPageLink($GLOBALS["TSFE"]->id).'" method="POST">
			<input type="hidden" name="no_cache" value="1" />
			<input type="hidden" name="'.$this->prefixId.'[type]" value="'.$this->kLECTURE.'" />
			<table>';
			
		// Degree Program
		$content .= '
			<tr>	
				<td><label for="'.$this->prefixId.'[degreeprogram]">Degree Program:</label></td>
				<td>
				<input 
					dojoType="dijit.form.FilteringSelect"
					store="fsmiexamsDegreeprogram" 
					searchAttr="name"
					autocomplete="true"
					name="'.$this->prefixId.'[degreeprogram]" 
					id="'.$this->prefixId.'_degreeprogram"
				/>
				</td>
			</tr>';
		
		// Field
		$content .= '
			<tr>	
				<td><label for="'.$this->prefixId.'_field">Field:</label></td>
				<td>
				<input dojoType="dijit.form.FilteringSelect" 
					store="fsmiexamsField"
					searchAttr="name"
					autocomplete="true"
					name="'.$this->prefixId.'[field]" 
					id="'.$this->prefixId.'_field"
				/>
				</td>
			</tr>';

		// Modules
		$content .= 
			'<tr>	
				<td><label for="'.$this->prefixId.'_module">Module:</label></td>
				<td>
					<input dojoType="dijit.form.FilteringSelect"
						store="fsmiexamsModule"
						searchAttr="name"
						query="{uid:\'*\'}"
						name="'.$this->prefixId.'[module]" 
						id="'.$this->prefixId.'_module"
						autocomplete="true"
					/>
				</td>
			</tr>
			<tr>	
				<td><label for="'.$this->prefixId.'[name]">Lecture Name:</label></td>
				<td><input type="text" name="'.$this->prefixId.'[name]" id="'.$this->prefixId.'[name]"  	
					value="'.htmlspecialchars($this->piVars["name"]).'"></td>
			</tr>
			</table>
			<input type="submit" name="'.$this->prefixId.'[submit_button]" 
				value="'.htmlspecialchars($this->pi_getLL("submit_button_label")).'">
			</form>';
		
		return $content;
	}
	
	function createExamInputForm () {
		$content = '
			<h2>Exam Input</h2> 
			<form action="'.$this->pi_getPageLink($GLOBALS["TSFE"]->id).'" method="POST">
			<input type="hidden" name="no_cache" value="1" />
			<input type="hidden" name="'.$this->prefixId.'[type]" value="'.$this->kEXAM.'" />
			<table>
			<tr>	
				<td><label for="'.$this->prefixId.'[name]">Exam Name:</label></td>
				<td><input type="text" name="'.$this->prefixId.'[name]" id="'.$this->prefixId.'[name]"  	
					value="'.htmlspecialchars($this->piVars["name"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->prefixId.'[number]">Number:</label></td>
				<td><input type="text" name="'.$this->prefixId.'[number]" id="'.$this->prefixId.'[number]"  	
					value="'.htmlspecialchars($this->piVars["number"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->prefixId.'[term]">Term:</label></td>
				<td><input type="text" name="'.$this->prefixId.'[term]" id="'.$this->prefixId.'[term]"  	
					value="'.htmlspecialchars($this->piVars["term"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->prefixId.'[lecture]">Lecture:</label></td>
				<td><input type="text" name="'.$this->prefixId.'[lecture]" id="'.$this->prefixId.'[lecture]"  	
					value="'.htmlspecialchars($this->piVars["lecture"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->prefixId.'[year]">Year:</label></td>
				<td><input type="text" name="'.$this->prefixId.'[year]" id="'.$this->prefixId.'[year]"  	
					value="'.htmlspecialchars($this->piVars["year"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->prefixId.'[exactdate]">Day of Exam:</label></td>
				<td><input type="text" name="'.$this->prefixId.'[exactdate]" id="'.$this->prefixId.'[exactdate]"  	
					value="'.htmlspecialchars($this->piVars["exactdate"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->prefixId.'[lecturer]">Lecturer:</label></td>
				<td><input type="text" name="'.$this->prefixId.'[lecturer]" id="'.$this->prefixId.'[lecturer]"  	
					value="'.htmlspecialchars($this->piVars["lecturer"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->prefixId.'[approved]">Approved:</label></td>
				<td><input type="text" name="'.$this->prefixId.'[approved]" id="'.$this->prefixId.'[approved]"  	
					value="'.htmlspecialchars($this->piVars["approved"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->prefixId.'[file]">Exam File:</label></td>
				<td><input type="text" name="'.$this->prefixId.'[file]" id="'.$this->prefixId.'[file]"  	
					value="'.htmlspecialchars($this->piVars["file"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->prefixId.'[material]">Add. Material:</label></td>
				<td><input type="text" name="'.$this->prefixId.'[material]" id="'.$this->prefixId.'[material]"  	
					value="'.htmlspecialchars($this->piVars["material"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->prefixId.'[quality]">Quality:</label></td>
				<td><input type="text" name="'.$this->prefixId.'[quality]" id="'.$this->prefixId.'[quality]"  	
					value="'.htmlspecialchars($this->piVars["quality"]).'"></td>
			</tr>
			<tr>	
				<td><label for="'.$this->prefixId.'[examtype]">Exam Type:</label></td>
				<td><input type="text" name="'.$this->prefixId.'[examtype]" id="'.$this->prefixId.'[examtype]"  	
					value="'.htmlspecialchars($this->piVars["examtype"]).'"></td>
			</tr>
			</table>
			<input type="submit" name="'.$this->prefixId.'[submit_button]" 
				value="'.htmlspecialchars($this->pi_getLL("submit_button_label")).'">
			</form>';
		
		return $content;
	}
	
	function createModuleListTXT () {
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
	
	function createFieldListTXT () {
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
	
	function createDegreeprogramListTXT () {
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
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/pi4/class.tx_fsmiexams_pi4.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/pi4/class.tx_fsmiexams_pi4.php']);
}

?>