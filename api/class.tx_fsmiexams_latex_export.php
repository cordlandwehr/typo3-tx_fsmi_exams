<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Andreas Cord-Landwehr <cola@uni-paderborn.de>
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
 * Everything to export data as LaTeX files
 *
 * @author Andreas Cord-Landwehr <cola@uni-paderborn.de>
 */

require_once (PATH_t3lib.'class.t3lib_befunc.php');
require_once (PATH_t3lib.'class.t3lib_tcemain.php');
require_once (PATH_t3lib.'class.t3lib_iconworks.php');


/**
 * Script Class to download files as defined in reports
 *
 */
class tx_fsmiexams_latex_export {

//     static private $pi_base;
// 
//     static function init () {
//         if (!self::$pi_base) {
//             self::$pi_base = t3lib_div::makeInstance('tslib_pibase');
//             self::$pi_base->cObj = t3lib_div::makeInstance('tslib_cObj');
//         }
//     }

    /**
     * Translates given UID of lecture to name
     *
     * @param   integer $uid    UID of folder
     * @return  string  link to file
     */
    static function storeExamsListForFolder($folderUID) {
		$output = '';
$output .='\documentclass[a4paper,12pt]{article}

\usepackage[utf8x]{inputenc}
\usepackage{ngerman}
\usepackage{graphicx}
\usepackage{fancyhdr}


\setlength{\oddsidemargin}{0mm}
\setlength{\oddsidemargin}{0mm}
\setlength{\textwidth}{17cm}
\setlength{\headsep}{2cm}
\setlength{\topmargin}{10mm}
\setlength{\voffset}{0mm}
\addtolength{\voffset}{-1in}

\renewcommand{\headrulewidth}{0pt}

\pagestyle{fancy}
\fancyhead[OL,EL]{\Huge{INHALTSVERZEICHNIS}}
\fancyfoot{}

\begin{document}
\vspace*{2cm}
';
$output .= '\begin{tabular}{|p{2.5cm}|p{6cm}|p{6cm}|} \hline \textbf{Datum}&\textbf{Prüfung}&\textbf{Dozent} \\\\ \hline\hline'."\n";
		// associated lectures
		$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $folderUID);

		$exams = explode( ',', $folderDATA['content'] );
		foreach ($exams as $exam) {
			$examDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_exam', $exam);

			$output .= tx_fsmiexams_div::examToTermdate($exam).'& '.tx_fsmiexams_div::examToText($exam).'& '.
			tx_fsmiexams_div::lecturerToText($examDATA['lecturer'])."\\\\ \n";
		}
$output .= '\end{tabular}';
$output .= '\end{document}';
		t3lib_div::writeFileToTypo3tempDir (
										PATH_site.'typo3temp/content.'.$folderDATA['folder_id'].'.tex',
										$output
										);
		
        return 'typo3temp/content.'.$folderDATA['folder_id'].'.tex';
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/api/class.tx_fsmiexams_latex_export.php'])
{
    required_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/api/class.tx_fsmiexams_latex_export.php']);
}
?>