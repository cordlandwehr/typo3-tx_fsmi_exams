<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Andreas Cord-Landwehr (cola@uni-paderborn.de)
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
require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Creates all needed database dumps as JSON files
 *
 */
class tx_fsmiexams_json {

	static function createModuleListJSON () {
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

	static function createFieldListJSON () {
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

	static function createDegreeprogramListJSON () {
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

	static function createLectureListJSON () {
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

	static function createLecturerListJSON () {
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

	static function createExamtypeListJSON () {
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

	static function createYearListJSON () {
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

	static function createTermListJSON () {
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

	static function createNumberListJSON () {
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

	static function createQualityListJSON () {
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
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/api/class.tx_fsmiexams_json.php'])
{
	required_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/api/class.tx_fsmiexams_json.php']);
}
?>