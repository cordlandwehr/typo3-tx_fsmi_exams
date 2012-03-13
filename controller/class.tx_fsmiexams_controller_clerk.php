<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2012  Andreas Cord-Landwehr <cola@uni-paderborn.de>
*  (c) 2010-2011  Alexander Wiens <awiens@uni-paderborn.de>
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
 * Plugin 'Lend it' for the 'fsmi_exams' extension.
 *
 * @author	Andreas Cord-Landwehr <fsmi@uni-paderborn.de>
 * @package	TYPO3
 * @subpackage	tx_fsmiexams
 */
class tx_fsmiexams_controller_clerk extends tslib_pibase {
	var $prefixId      = 'tx_fsmiexams_clerk';		// Same as class name
	var $scriptRelPath = 'controller/class.tx_fsmiexams_controller_clerk.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'fsmi_exams';	// The extension key.

	var $loanStoragePID		= 0;

	//Constant Values
	const kMODE_UNKNOWN = 0;
	const kMODE_LEND = 1;
	const kMODE_WITHDRAWAL = 2;

	const MAGIC = 'magic';
	const kGFX_PATH = 'typo3conf/ext/fsmi_exams/images/';
	const PREFIX = 'tx_fsmiexams_loan';

	const kSTEP_START = 1;
	const kSTEP_SECOND_PAGE = 2;
	const kSTEP_FINALIZE = 3;
	const kSTEP_SHOW_LENT_FOLDERS = 4;
	const kSTEP_SHOW_SEARCH_FORM = 5;

	const kCTRL_NEXT   = 1;		// next button
	const kCTRL_RELOAD = 2;		// next button
	const kCTRL_CANCEL = 5;		// cancel button

	var $LANG;						// language object

	function __construct () {
		$this->LANG = t3lib_div::makeInstance('language');
		$this->LANG->init($GLOBALS['TSFE']->tmpl->setup['config.']['language']);
		$this->LANG->includeLLFile('typo3conf/ext/fsmi_exams/locallang_db.xml');
	}

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

		$this->loanStoragePID =intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pidTransactions'));
		$GETcommands = t3lib_div::_GP($this->extKey);
		$this->piVars = array();

        //Important variables //TODO: Escape and set variables
		$this->piVars['step'] = intval($GETcommands['step']);
		$this->piVars['mode'] = intval($GETcommands['mode']);

		$this->piVars['lender_name'] = $this->escape($GETcommands['lender_name']);
		$this->piVars['lender_imt'] = $this->escape($GETcommands['lender_login']);
		$this->piVars['deposit'] = $this->escape($GETcommands['deposit']);
		$this->piVars['dispenser'] = $this->escape($GETcommands['dispenser']);

		// convert all possible linebreaks to delimiter
		$folderIDset = $GETcommands['folder_id'];
		$folderIDset = str_replace("\r\n", ",", $folderIDset);
		$folderIDset = str_replace("\n\r", ",", $folderIDset);
		$folderIDset = str_replace("\n", ",", $folderIDset);
		$folderIDset = str_replace("\r", ",", $folderIDset);
		$this->piVars['folder_ids'] = explode(',',$this->escape($folderIDset));
		$this->piVars['folder_weight'] = intval($GETcommands['folder_weight']);
		$this->piVars['folder_list'] = $GETcommands['folder_list']; // TODO: escaping destroys serializing
		$this->piVars['folder_list_hash'] = $this->escape($GETcommands['folder_list_hash']);

		$this->piVars['folder_list_array'] = null;

		//Deserialize folder list
		if (isset($this->piVars['folder_list']) && isset($this->piVars['folder_list_hash']) && (md5($this->piVars['folder_list'] . self::MAGIC) == $this->piVars['folder_list_hash']))
		{
		    $this->piVars['folder_list_array'] = unserialize($this->piVars['folder_list']);
		}
	    //TODO: serialized arrays as strings contain " - xml attributes use "


		//Style
		$content .= '<style type="text/css"> .tx-fsmiexams-pi3 form{clear:both;} .tx-fsmiexams-pi3 img{margin-bottom:5px;} .tx-fsmiexams-pi3 .step{text-align:center; width:80px; display:inline-block; margin:10px 15px;} .tx-fsmiexams-pi3 a{text-decoration:none;} .tx-fsmiexams-pi3 table th{background-color:#B5CDE1;}</style>' . "\n";
		//main_container

		$content .= '<div style="text-align:left; font-weight:bold; float:left;">';
		$content .= $this->pi_linkTP('<i>Suche</i>',array($this->extKey.'[step]' => self::kSTEP_SHOW_SEARCH_FORM)).'';
		$content .= '</div>';
		$content .= '<div style="text-align:right; font-weight:bold; float:right;">';
// 		$content .= $this->pi_linkTP('<i>Suche</i>',array($this->extKey.'[step]' => self::kSTEP_SHOW_LENT_FOLDERS)).'';
		$content .= '<i>Übersichten<br />Ordner<br />Prüfungen<br />Module</i>';
		$content .= '</div>';
		$content .= '<div style="text-align:center; font-weight:bold;">';
		$content .= $this->pi_linkTP('<i>zeige Ausgeliehene</i>',array($this->extKey.'[step]' => self::kSTEP_SHOW_LENT_FOLDERS)).'';
		$content .= '</div>';

		$content .= '<div style="margin:0px 15px; padding-top:15px; clear:both;">' . "\n";

		// on cancel go to start
		if (isset($GETcommands['control'.self::kCTRL_CANCEL])) {
			$this->piVars['step'] = self::kSTEP_START;
		}

		switch ($this->piVars['step']) {
			case self::kSTEP_SHOW_LENT_FOLDERS: {
				$content .= $this->listAllLentFolders();
				$content .= '<div style="text-align:center;">'.$this->pi_linkTP('<h3>Zurück zur Ausleihe</h3>',array()).'</div>';
				break;
			}

			case self::kSTEP_SHOW_SEARCH_FORM: {
				$content .= $this->searchForm();
				$content .= '<div style="text-align:center;">'.$this->pi_linkTP('<h3>Zurück zur Ausleihe</h3>',array()).'</div>';
				break;
			}

			case self::kSTEP_START: {
				// if next-button, need to change mode:
				if(!(is_array($this->piVars['folder_ids']) || count($this->piVars['folder_ids']=0)) && isset($GETcommands['control'.self::kCTRL_NEXT])) {
					$content .= tx_fsmiexams_div::printSystemMessage(
										tx_fsmiexams_div::kSTATUS_ERROR,
										"<b>Fehler:</b><br />Um hier weiter zu kommen musst du schon einen Ordner-Barcode eingeben."
										);
					$content .= $this->formStartpage();
					break;
				}
				if (isset($GETcommands['control'.self::kCTRL_NEXT])) {
					$content .= $this->formSecondPage();
				} else {
					$content .= $this->formStartpage();
				}
				break;
			}

		    default: {
				$content .= $this->formStartpage();
				break;
			}

			case self::kSTEP_SECOND_PAGE: {
				// if next-button, need to change mode:
				if (isset($GETcommands['control'.self::kCTRL_NEXT])) {
					if (count($this->piVars['folder_ids'])>0 && !$this->piVars['weight']) {
						$content .= tx_fsmiexams_div::printSystemMessage(
										tx_fsmiexams_div::kSTATUS_ERROR,
										"<b>Fehler:</b><br />Rückgabe ist nur möglich mit Angabe eines Gewichtes.."
										);
						$content .= $this->formSecondPage();
						break;
					}
					$content .= $this->formFinalizeLendOrWithdrawal();
				} else {
					// first: check if folder even exists
					if (!$this->folderExists($this->piVars['folder_id']))
						$content .= tx_fsmiexams_div::printSystemMessage(
										tx_fsmiexams_div::kSTATUS_ERROR,
										"<b>Fehler:</b><br />Den eingegebenen Ordner-Barcode haben wir leider nicht im Archiv."
										);
					$content .= $this->formSecondPage();
				}
				break;
			}

			case self::kSTEP_FINALIZE: {
				$content .= $this->performTransactions();
				break;
			}
		}


	    $content .= '</form>';

		$content .= '</div>';


//static t3lib_div::cmpIP


		return $this->pi_wrapInBaseClass($content);
	}


	private function formStartpage() {
		$content = '';
		//Steps
		$content .= '<h1>FSMI-Ausleihtool</h1>';
		$content .= $this->renderSteps(1, array());


		$content .= '<form method="GET" action="index.php">' . "\n";
		$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id . '"/>';
		$content .= '<input type="hidden" name="' . $this->extKey . '[step]" value="'.self::kSTEP_START.'"/>';
		$content .= '<div style="text-align:center"><h3>Ordnereingabe</h3>';
			$content .= '<table cellpadding="5" cellspacing="0" style="width:60%; text-align:left;"><tbody>';
			$content .= '<tr><td>Alle Ordner-Barcodes:</td><td><textarea row="5" col="6" name="' . $this->extKey . '[folder_id]"></textarea></td></tr></tbody></table>';
		$content .= '</div></br>';

		$content .= '<p style="margin-left:auto; margin-right:auto; width:90%;">So, das Ganze funktioniert wie folgt. Du gibst hier den ersten Ordner Barcode an von dem Ordner mit dem du etwas machen m&ouml;chtest. Danach machst du mit dem n&auml;chsten Ordner weiter etc. Wenn du alle Ordner durch hast (also Ausleihen oder Zur&uuml;cknehmen), dann gibst du die Daten vom dem Ausleiher an und schon bist du fertig.</p>';

		//Buttons
		$content .= $this->renderButtons(array("Weiter" => self::kCTRL_NEXT));

		return $content;
	}

	private function formSecondPage() {
		$this->addFoldersToFolderArray($this->piVars['folder_ids']);
		if (count($this->piVars['folder_ids'])==0) {
			return $this->formStartpage();
		}

		// this page gets the initial folder IDs and estimates what to do with them.
		$content = '';

		//Steps
		$content .= '<h1>Buchung vorbereiten</h1>';

		$content .= $this->renderSteps(	2,
										array(
											0 => array ('title' => 'Buchung'),
											1 => array ('title' => 'Ergebnis'),
										));

		$content .= $this->transactionForm();

		return $content;
	}


	/**
	 * This function gives you the transaction interface for the pre-set folders.
	 * The function assumes plausibility checks of folder existance beforehand
	 * as well as a call of addFoldersToFolderArray().
	 */
	private function transactionForm() {
		if (!is_array($this->piVars['folder_list_array']) ) {
			return "Houston, we have a problem!";
		}

		$folderIDs = array();
		foreach ($this->piVars['folder_list_array'] as $key => $value) {
			$folderIDs[] = $key;
		}
		$folderDATA = null;

		$content = '<form method="GET" action="index.php">' . "\n";
		$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id . '"/>' . "\n";
		$content .= '<input type="hidden" name="' . $this->extKey . '[step]" value="'.self::kSTEP_FINALIZE.'"/>' . "\n";
		$content .= (isset($this->piVars['folder_list']) ? '<input type="hidden" name="' . $this->extKey . '[folder_list]" value=\'' . $this->piVars['folder_list'] . '\'/>'."\n":'');
		$content .= (isset($this->piVars['folder_list_hash']) ? '<input type="hidden" name="' . $this->extKey . '[folder_list_hash]" value="' . $this->piVars['folder_list_hash'] . '"/>'."\n":'');

		// return table
		if(is_array($this->piVars['folder_list_array']) && $this->isLent($folderIDs))
		{
			$content .= '<h4 style="text-align:left">Ordner Zurücknehmen</h4>';
			$content .= '<table><tr><th width="100">RÜCK-Gewicht</th><th width="100">SOLL-Gewicht</th><th width="40">ID</th><th width="250">Ordnername</th></tr>';
			$tabindex=1;
			foreach($this->piVars['folder_list_array'] as $key => $value) {
				if (!($value['mode']==self::kMODE_WITHDRAWAL))
					continue;
				$content .= '<tr>';
				$content .= '<td><input tabindex="'.($tabindex++).'" size="8" name="'.$this->extKey.'[folder]['.$key.'][weight]" value="'.$value['weight'].'" /> g</td>';
				$content .= '<td>'.$value['weight'].' g</td>';
				$content .= '<td><input type="hidden" name="' . $this->extKey . '[folder][uid]" value=\''.$key.'\'/>'. $value['barcode'].'</td>';
				$content .= '<td>'.$value['name'].'</td>';
				$content .= '</tr>';
				//TODO: add javascript to indicate if weight is ok
			}
			$content .= '</tr></table>';
		}

		// lend table
		if(is_array($this->piVars['folder_list_array']) && $this->isAvailable($folderIDs))
		{
			$content .= '<h4 style="text-align:left">Ordner Verleihen</h4>';
			$content .= '<table><tr><th width="100">Gewicht</th><th width="40">ID</th><th width="250">Ordnername</th></tr>';
			foreach($this->piVars['folder_list_array'] as $key => $value) {
				if (!($value['mode']==self::kMODE_LEND))
					continue;
				$content .= '<tr>';
				$content .= '<td><input size="8" name="'.$this->extKey.'[folder]['.$key.'][weight]" value="'.$value['weight'].'" /> g</td>';
				$content .= '<td><input type="hidden" name="' . $this->extKey . '[folder]['.$key.'][barcode]" value=\''.$key.'\'/>'. $value['barcode'].'</td>';
				$content .= '<td>'.$value['name'].'</td>';
				$content .= '</tr>';
			}
			$content .= '</tr></table>';
		}

		/* combination of different possible lending/return scenarios */

		$pendingFolders = $this->pendingFoldersAfterTransaction($folderIDs);
		$affectedLoans = $this->affectedLoansByTransaction($folderIDs);
		$pendingFolderList = '';
		foreach ($pendingFolders as $folder) {
			$folderInstanceDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder_instance', $folder);
			$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $folderInstanceDATA['folder']);
			$pendingFolderList .= '<li>'.$folderDATA['name'].' ('.$folderInstanceDATA['offset'].')</li>';
		}
		// print folders that will be left open after this operation
		if (count($pendingFolders)>0) {
			$content .= tx_fsmiexams_div::printSystemMessage(
						tx_fsmiexams_div::kSTATUS_WARNING,
						"<b>Offene Transaktionen</b><br /> Nicht alle ausgeliehenen Ordner werden zurück gegeben. Folgende Ordner fehlen:<ul>".$pendingFolderList.'</ul>'.
						'Es muss ein Pfand zurückbehalten werden!'
						);
		}


		if (!$this->isLent($folderIDs)) {	// if ONLY lending (no associated transaction in the system)
			$content .= '<h4>Transaktionsdaten</h4>';
			$content .= '<table>';
			$content .= '<tr><td style="color:#aaa;font-weight:bold">Name (Student)</td>
				<td><input name="' . $this->extKey.'[transaction][lender_name]" value=\'' . $this->piVars['transaction']['lender_name'] . '\'/></td></tr>';
			$content .= '<tr><td style="color:#aaa;font-weight:bold">Login (Student)</td>
				<td><input name="' . $this->extKey.'[transaction][lender_login]" value=\'' . $this->piVars['transaction']['lender_login'] . '\'/></td></tr>';
			$content .= '<tr><td style="color:#aaa;font-weight:bold">Pfand</td>
				<td><input name="' . $this->extKey.'[transaction][deposit]" value=\'' . $this->piVars['transaction']['deposit'] . '\'/></td></tr>';
			$content .= '<tr><td style="color:#aaa;font-weight:bold">Ausgeliehen von</td>
				<td><input name="' . $this->extKey.'[transaction][dispenser]" value=\'' . $this->piVars['transaction']['dispenser'] . '\'/></td></tr>';
			$content .= '</table>';
		}
		else {	// withdrawal mode or mixed mode
			$loanUIDs = $this->affectedLoansByTransaction($folderIDs);
			$deposit = array();
			$name = array();
			$login = array();
			foreach ($loanUIDs as $loan) {
				$loanDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_loan', $loan);
				$deposit[] = $loanDATA['deposit'];
				$name[] = $loanDATA['lender'];
				$login[] = $loanDATA['lenderlogin'];
			}
			// allow changing of values IFF  not all transactions are close/new ar opened
			if ($this->isAvailable($folderIDs) || count($pendingFolders)>0) {
				$content .= '<h4>Transaktionsdaten</h4>';
				$content .= '<table>';
				$content .= '<tr><td style="color:#aaa;font-weight:bold">Name (Student)</td>
					<td><input name="' . $this->extKey.'[transaction][lender_name]" value=\'' .implode(', ',$name) . '\'/></td></tr>';
				$content .= '<tr><td style="color:#aaa;font-weight:bold">Login (Student)</td>
					<td><input name="' . $this->extKey.'[transaction][lender_login]" value=\''.implode(', ',$login).'\'/></td></tr>';
				$content .= '<tr><td style="color:#aaa;font-weight:bold">Pfand</td>
					<td><input name="' . $this->extKey.'[transaction][deposit]" value="'.implode(' + ',$deposit).'" /></td></tr>';
				$content .= '<tr><td style="color:#aaa;font-weight:bold">Wird bearbeitet von</td>
					<td><input name="' . $this->extKey.'[transaction][dispenser]" value=\'' . $this->piVars['transaction']['dispenser'] . '\'/></td></tr>';
				$content .= '</table>';
			}
			else { // otherwise print only information that cannot be changed
				$content .= '<h4>Transaktionsdaten</h4>';
				$content .= '<table>';
				$content .= '<tr><td style="color:#aaa;font-weight:bold">Verliehen an</td>
					<td>';
				foreach ($loanUIDs as $loan) {
					$loanDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_loan', $loan);
					$content .= $loanDATA['lender'].' ('.$loanDATA['lenderlogin'].')<br />';
				}
				$content .= '</td></tr>';
				$content .= '<tr><td style="color:#aaa;font-weight:bold">Pfand</td>
					<td>';
				foreach ($loanUIDs as $loan) {
					$loanDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_loan', $loan);
					$content .= $loanDATA['deposit'].'<br />';
				}
				$content .= '</td></tr>';
				$content .= '<tr><td style="color:#aaa;font-weight:bold">Zurückgenommen von</td>
					<td><input name="' . $this->extKey.'[transaction][withdrawal]" value=\'' . $this->piVars['transaction']['withdrawal'] . '\'/></td></tr>';
				$content .= '</table>';
			}
		}

		//Buttons
		$content .= $this->renderButtons(array("Abbruch" => self::kCTRL_CANCEL, "Weiter" => self::kCTRL_NEXT));

		return $content;
	}


	/**
	 * returns false if the folders could not be added
	 * deletes also all temp. vars
	 */
	private function addFoldersToFolderArray($folder_ids) {
		$content = '';

		if (!is_array($this->piVars['folder_list_array'])) {
			$this->piVars['folder_list_array'] = array();
		}

		// delete empty lines
		foreach ($this->piVars['folder_ids'] as $key => $value) {
			if ($value=='' || !$this->folderExists($value)) {
				unset ($this->piVars['folder_ids'][$key]);
			}
		}

		if (count($this->piVars['folder_ids'])==0) {
			$this->piVars['folder_list'] = serialize($this->piVars['folder_list_array']); //TODO we do not need all information
			$this->piVars['folder_list_hash'] = md5($this->piVars['folder_list'] . self::MAGIC);
			return '';
		}

		$resFolder = $GLOBALS['TYPO3_DB']->sql_query(
			'SELECT * FROM tx_fsmiexams_folder_instance
			WHERE folder_id IN ('.implode(',',$this->piVars['folder_ids']).')
			AND hidden=0 AND deleted=0');
		while ($resFolder && $res = mysql_fetch_assoc($resFolder)) {

			if ($res['state'] == tx_fsmiexams_div::kFOLDER_STATE_LOST) {
				$content .= tx_fsmiexams_div::printSystemMessage(
												tx_fsmiexams_div::kSTATUS_INFO,
												'<b>Für Dich zur Info</b><br /> Dieser Ordner ist LOST... aber anscheinend wiedergefunden worden. Lassen wir es mal dabei und machen den Ausleihvorang weiter.'
												);
				continue;
			}
			if ($res['state'] == tx_fsmiexams_div::kFOLDER_STATE_MAINTENANCE) {
				$content .= tx_fsmiexams_div::printSystemMessage(
												tx_fsmiexams_div::kSTATUS_INFO,
												'<b>Für Dich zur Info</b><br /> Für das System ist der Ordner gerade in der händischen Überarbeitung/Wartung und sein Status wurde  nicht auf &quot;verfügbar&quot; zurück gesetzt. Mit dem Abschluss der Ausleihe nehmen wir ihn nun wieder in das System.'
												);
				continue;
			}
			if ($res['state']==tx_fsmiexams_div::kFOLDER_STATE_LEND) {
				$this->piVars['folder_list_array'][$res['uid']]['weight'] = $this->getLoanWeightForFolder($res['uid']);
			}
			//TODO add weight in case of already entered...
			if ($res['state']==tx_fsmiexams_div::kFOLDER_STATE_LEND) {
				$this->piVars['folder_list_array'][$res['uid']]['mode'] = self::kMODE_WITHDRAWAL;
			}
			if ($res['state']==tx_fsmiexams_div::kFOLDER_STATE_PRESENT) {
				$this->piVars['folder_list_array'][$res['uid']]['mode'] = self::kMODE_LEND;
			}
			$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $res['folder']);
			$this->piVars['folder_list_array'][$res['uid']]['name'] = $folderDATA['name'].' ('.$res['offset'].')';
			$this->piVars['folder_list_array'][$res['uid']]['barcode'] = tx_fsmiexams_div::numberFixedDigits($res['folder_id'],4).'';
		}
		$this->piVars['folder_list'] = serialize($this->piVars['folder_list_array']); //TODO we do not need all information
		$this->piVars['folder_list_hash'] = md5($this->piVars['folder_list'] . self::MAGIC);

		return $content;
	}

	/**
	 * renders given array of buttons. Each button-value is expected to be $label => $value.
	 * name then is '.$this->extKey.'[control'.$value.']
	 */
	private function renderButtons($buttons) {
		if (!is_array($buttons))
			return '';

		$content = '<div style="margin-top:15px;">';
		$counter = 0;
		foreach ($buttons as $label => $value) {
			$counter++;
			$content .= '<input type="submit" name="'.$this->extKey.'[control'.$value.']" value="' . $label . '"';
			if ($counter == count($buttons))
				$content .= ' style="float:right;"/>';
			else {
			    $content .= ' style="float:left;" tabindex="1" />';
			}

		}
    $content .= '</div>';
		return $content;
	}

	private function renderSteps($currentStep, $titles) {
		$steps = '<table style="margin-left: auto; margin-right: auto; text-align: center;">';
		$steps .= '<tr><td width="100"><a href="index.php?id='.$GLOBALS['TSFE']->id.'">';
		$steps .= '<img src="typo3conf/ext/fsmi_exams/images/one_' .
	         ($currentStep==1 ? 'active' : 'inactive') .
	         '.png"/><br /><b>Startseite</b></a></td>'."\n";

		$steps .= isset($titles[0]) ? '<td width="100"><img src="' . self::kGFX_PATH . 'two_' .
	                            ($currentStep==2 ? 'active' : 'inactive') .
								'.png"/><br /><b>' . $titles[0]['title'] . '</b></td>' . "\n" : '';
		$steps .= isset($titles[1]) ? '<td width="100"><img src="' . self::kGFX_PATH . 'three_'.
	                            ($currentStep==3 ? 'active':'inactive').
								'.png"/><br /><b>' . $titles[1]['title'] . '</b></td>' . "\n" : '';
		$steps .= isset($titles[2]) ? '<td width="100"><img src="' . self::kGFX_PATH . 'four_' .
	                            ($currentStep==4 ? 'active' : 'inactive') .
								'.png"/><br /><b>' . $titles[2]['title'] . '</b></td>'."\n":'';
		$steps .= isset($titles[3]) ? '<td width="100"><img src="' . self::kGFX_PATH . 'five_' .
	                            ($currentStep==5 ? 'active' : 'inactive') .
								'.png"/><br /><b>' . $titles[3]['title'] . '</b></td>' . "\n" : '';
		return $steps . "</tr></table>";
	}

	private function renderLentFolderInfo($folderArray, $tableStructure, $relativeSizes=null) {
		$infoTable = '';
		$infoTable .= '<table cellpadding="8" cellspacing="2" style="width: 90%; text-align:center;"><tr>';

		if(!isset($relativeSizes) || !is_array($relativeSizes) || count($relativeSizes)!=count($tableStructure)) {
			for($i=0; $i<count($tableStructure); $i++)
				$relativeSizes[$i] = 100/count($tableStructure);
		}

		$counter = 0;
		foreach( $tableStructure as $value) {
			$infoTable .= '<th style="width:'.$relativeSizes[$counter++].'%">' . $this->pi_getLL($value) . '</th>';
		}

		$infoTable .= '</tr>' . "\n";

		foreach($folderArray as $folderRow){
			if(isset($folderRow))
			{
				$infoTable .= '<tr>';
				foreach($tableStructure as $value)
					$infoTable .= '<td>' . $folderRow[$value] . '</td>';
				$infoTable .= '</tr>';
			}
		}
		$infoTable .= '</table>';

		return $infoTable;
	}

	/**
	 * \param state indicates if state information shall be printed
	 */
	function printLoanInfo($loanUID, $state=true) {
		$loanDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_loan', $loanUID);
		$content = '<tt>';
		$content .= '<strong>Leihvorgang ID '.$loanUID.'</strong> erstellt am '.date('d.m.Y h:i',$loanDATA['lendingdate']).', Pfand: '.$loanDATA['deposit'].'</tt><br/>';
		$folders = explode(",", $loanDATA['folder']);
		$weights = explode(",", $loanDATA['weight']);

		$content .= '<table cellpadding="8" cellspacing="2" style="width: 100%; text-align:center;"><tbody><tr><th>Ordner-ID</th><th>Ordner-Name</th><th>Ausleihgewicht</th><th>Rückgabegewicht</th><th>Status</th></tr>';
		foreach ($folders as $id => $folderInstanceUID) {
			$folderInstanceDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder_instance', $folderInstanceUID);
			$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $folderInstanceDATA['folder']);
			$content .= '<tr><td>'.tx_fsmiexams_div::numberFixedDigits($folderInstanceDATA['folder_id'],4).
				'</td><td>'.$folderDATA['name'].' ['.$folderInstanceDATA['offset'].']</td><td>'.$weights[$id].'g</td>';
			if (array_key_exists($folderInstanceDATA['uid'], $this->piVars['folder_list_array'])) {
				$returnWeight = $this->piVars['folder_list_array'][$folderInstanceDATA['uid']]['weight'];
				$content .= '<td>'.$returnWeight.'g</td>';
				if ($weights[$id]*1.0/$returnWeight > 1.05) {
					if ($state) $content .= '<td><strong style="color:red"> Achtung: &gt;5% Abweichung</strong></td>';
					else $content .= '<td></td>';
				}
				else {
					if ($state) $content .= '<td><strong style="color:green"> alles gut</strong></td>';
					else $content .= '<td></td>';
				}
			}
			else {
			    $content .= '<td></td><td><strong style="color:orange">keine Rückgabe</strong></td>';
			}
			$content .= '</tr>';
		}
		$content .= '</tbody></table>';
		return $content;
	}


	private function performTransactions() {
		$content = '';

		$folderIDs = array();
		foreach ($this->piVars['folder_list_array'] as $key => $value) {
			$folderIDs[] = $key;
		}
		if ($this->isAvailable($folderIDs) && !$this->isLent()) {
			$content .= $this->transactionCreate();
		}
		else {
			$content .= $this->transactionModify();
		}
		return $content;
	}

	/**
	 * Create a new transaction BASED ON an or several existing ones.
	 * Explicitely this is the case if folders are given back (withdrawal).
	 */
	private function transactionModify() {
		$content = '';

		$content .= '<h1>Transaktionsende</h1>';

		$content .= $this->renderSteps(	3,
										array(
											0 => array ('title' => 'Buchung'),
											1 => array ('title' => 'Ergebnis'),
										));

		// transaction information
		$formValues = t3lib_div::_GP($this->extKey);
		$withdrawal = $this->escape($formValues['transaction']['withdrawal']);
		$new_deposit = $this->escape($formValues['transaction']['deposit']);
		$lender_name = $this->escape($formValues['transaction']['lender_name']);
		$lender_imt = $this->escape($formValues['transaction']['lender_login']);
		$deposit = $this->escape($formValues['transaction']['deposit']);
		$folders = $this->piVars['folder_list_array'];
		$dispenser = $this->escape($formValues['transaction']['dispenser']);

		// get for all folders that shall be lent the according UID
		foreach ($folders as $key => $value) {
			$folders[$key]['weight'] = intval($formValues['folder'][$key]['weight']);
		}

		// sometimes dispenser takes also folder back
		if ($withdrawal=='')
			$withdrawal = $dispenser;


		$foldersToLoans = array();
		foreach($this->piVars['folder_list_array'] as $key => $value) {
			// now find corresponding loan
			$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT uid FROM tx_fsmiexams_loan
										WHERE deleted=0 AND hidden=0 AND withdrawaldate=0 AND FIND_IN_SET('.$key.',folder)');
			if($res && $loan = mysql_fetch_array($res)) {
				$foldersToLoans[$key] = $loan['uid'];
			}
			else {
				$content .= tx_fsmiexams_div::printSystemMessage(
										tx_fsmiexams_div::kSTATUS_ERROR,
										'<b>Fehler</b><br /> Der Ordner ist in keinem offenen Ausleihvorgang gebucht.'
										);
			}
		}

		// calculate which loans can be closed
		$affectedLoans = array();			// array of loans that shall be closed
		$pendingFolders = array();			// array of folders that must be put into new loan
		$pledges = array();					// pledges that could be given back

		foreach ($foldersToLoans as $folderUID => $loanUID) {
			$loanDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_loan', $loanUID);
			$loanFolders = explode(',', $loanDATA['folder']);
			$loanFolderWeights = explode(',', $loanDATA['weight']);
			$clean = true;
			foreach ($loanFolders as $key => $folder) {
				if (!array_key_exists($folder, $foldersToLoans) ) { // case there is a folder that is not taken back
					$pendingFolders[$folder] = array( 'uid' => $folder, 'weight' => $loanFolderWeights[$key]);
				}
			}
			$pledges[] = array ( 'deposit' => $loanDATA['deposit'], 'lender' => $loanDATA['lender']);
			if (!array_key_exists($loanUID, $affectedLoans))
				$affectedLoans[] = $loanUID;
		}

		if (count($foldersToLoans)==0) {
			$content .= tx_fsmiexams_div::printSystemMessage(
									tx_fsmiexams_div::kSTATUS_ERROR,
									'<b>Fehler</b><br /> beim Mapping von Ordnern zu Ausleihvorgängen.'
									);
		}
		else {
			// first we close all loans
			foreach ($affectedLoans as $loan) {
				$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
							'tx_fsmiexams_loan',
							'uid = '.intval($loan),
							array ( 'withdrawal' => $withdrawal, 'withdrawaldate' => time(), 'tstamp' => time() )
							);

				if (!$res)
					$content .= tx_fsmiexams_div::printSystemMessage(
											tx_fsmiexams_div::kSTATUS_ERROR,
											'<b>Fehler</b><br /> beim Schließen von Leihvorgang '.$loan.'. Bitte diese Seite ausdrucken und dem Admin überreichen.'
											);
			}
			// then we close all folders that ar taken back
			foreach ($foldersToLoans as $folder) {
				$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
							'tx_fsmiexams_folder_instance',
							'uid = '.intval($folder),
							array ( 'state' => tx_fsmiexams_div::kFOLDER_STATE_PRESENT )
							);
				if (!res)
					$content .= tx_fsmiexams_div::printSystemMessage(
											tx_fsmiexams_div::kSTATUS_ERROR,
											'<b>Fehler</b><br /> Konnte Zustand von Ordner UID='.$folder.' nicht freigeben.'
											);
			}

			// print out what is closed
			$content .= '<div style="margin-left:auto; margin-right:auto; width:90%;">';
			$content .= '<h3 style="text-align:center;">Abgeschlossene Ausleihvorgänge</h3>';
			foreach ($affectedLoans as $loanUID) {
				$content .= $this->printLoanInfo($loanUID, true);
			}

			$content .= '</div>';

			// and create a new loan with everything that is left
			if (count($pendingFolders)>0) {
				$lendFolders = array();
				$lendWeights = array();
				foreach ($pendingFolders as $folder) {
					$lendFolders[] = $folder['uid'];
					$lendWeights[] = $folder['weight'];
				}
				$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
								'tx_fsmiexams_loan',
								array (	'pid' => $this->loanStoragePID,
										'crdate' => time(),
										'tstamp' => time(),
										'deleted' => 0,
										'hidden' => 0,
										'dispenser' => $GLOBALS['TYPO3_DB']->quoteStr($dispenser, 'tx_fsmiexams_loan'),
										'lenderlogin' => $GLOBALS['TYPO3_DB']->quoteStr($lender_imt, 'tx_fsmiexams_loan'),
										'lender' => $GLOBALS['TYPO3_DB']->quoteStr($lender_name, 'tx_fsmiexams_loan'),
										'deposit' => $GLOBALS['TYPO3_DB']->quoteStr($deposit, 'tx_fsmiexams_loan'),
										'folder' => implode(',',$lendFolders),
										'weight' => implode(',',$lendWeights),
										'lendingdate' => time(),
										'withdrawaldate' => 0
								));

				if (!$res) {
					$content .= tx_fsmiexams_div::printSystemMessage(
											tx_fsmiexams_div::kSTATUS_ERROR,
											'<b>Fehler</b><br /> Neuer Ausleihvorgang konnte nicht angelegt werden.'
											);
				}
			}

			// now update the folders
			foreach ($foldersToLoans as $folderInstanceUID => $loan) {
				$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
							'tx_fsmiexams_folder_instance',
							'uid = '.intval($folderInstanceUID),
							array ( 'state' => tx_fsmiexams_div::kFOLDER_STATE_PRESENT )
							);

				if ($res) {
					$lendFolders[] = $folderInstanceUID;
					$lendWeights[] = $info['weight'];
				} else {
					$content .= 'ERROR: Mutex on folder UID '.$folderInstanceUID.' could not be set, aboarding this folder.';
				}
			}
		}

		$content .= '<div style="margin-left:auto; margin-right:auto; width:90%; text-align:center;">';
		$content .= '<h4>Pfandbearbeitung</h4>';
		$content .= '<table cellpadding="8" cellspacing="2" style="width:100%; text-align:center;"><tbody><tr><th>Aktion</th><th>Pfand</th></tr>';
		foreach ($pledges as $pledge) {
			if (strcmp($pledge['deposit'],$new_deposit)==0)
				continue;
			$content .= '<tr><td>Zurück geben</td><td>'.$pledge['deposit'].'</td></tr>';
		}
		if (count($pendingFolders)>0 && $new_deposit) {
			$content .= '<tr><td><b>Einbehalten</b></td><td>'.$new_deposit.'</td></tr>';
		}
		$content .= '</tbody></table>';
		$content .= '</div>';

		$content .= '<div style="text-align:center; font-size: 20px; padding-top: 30px">'.$this->pi_linkToPage('Zurück zur Startseite',$GLOBALS['TSFE']->id).'</div>';
		return $content;
	}

	/**
	 * This is the case if an user only wants to LEND folders. Hence there is no
	 * existing loan associated to this user that need to be modified.
	 */
	private function transactionCreate() {
		$content = '';	// the transaction log

		// first: get all interesting values
		$formValues = t3lib_div::_GP($this->extKey);
		$lender_name = $this->escape($formValues['transaction']['lender_name']);
		$lender_imt = $this->escape($formValues['transaction']['lender_login']);
		$deposit = $this->escape($formValues['transaction']['deposit']);
		$dispenser = $this->escape($formValues['transaction']['dispenser']);
		$folders = $this->piVars['folder_list_array'];

		// get for all folders that shall be lent the according UID
		foreach ($folders as $key => $value) {
			$folders[$key]['weight'] = intval($formValues['folder'][$key]['weight']);
		}

		if ($formValues=='' || $lender_name=='' || $lender_imt=='' || $deposit=='' || $dispenser=='' || count($folders)<1 ) {
			// TODO give more feedback
			$content .= tx_fsmiexams_div::printSystemMessage(
										tx_fsmiexams_div::kSTATUS_WARNING,
										"<b>Achtung:</b><br />Weiter geht es erst, wenn du alle Felder ausgefüllt hast."
										);
			// TODO at this point return button!
			return $content;
		}

		// second: database transformations, and there first secure all folders
		$lendFolders = array ();
		$lendWeights = array ();
		foreach ($folders as $folderUID => $info) {
			$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
						'tx_fsmiexams_folder_instance',
						'uid = '.intval($folderUID),
						array ( 'state' => tx_fsmiexams_div::kFOLDER_STATE_LEND )
						);

			if ($res) {
				$lendFolders[] = $folderUID;
				$lendWeights[] = $info['weight'];
			} else {
				$content .= 'ERROR: Mutex on folder UID '.$folderUID.' could not be set, aboarding this folder.';
			}
		}

		// third: store actual transaction into database
		$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
						'tx_fsmiexams_loan',
						array (	'pid' => $this->loanStoragePID,
								'crdate' => time(),
								'tstamp' => time(),
								'deleted' => 0,
								'hidden' => 0,
								'dispenser' => $GLOBALS['TYPO3_DB']->quoteStr($dispenser, 'tx_fsmiexams_loan'),
								'lenderlogin' => $GLOBALS['TYPO3_DB']->quoteStr($lender_imt, 'tx_fsmiexams_loan'),
								'lender' => $GLOBALS['TYPO3_DB']->quoteStr($lender_name, 'tx_fsmiexams_loan'),
								'deposit' => $GLOBALS['TYPO3_DB']->quoteStr($deposit, 'tx_fsmiexams_loan'),
								'folder' => implode(',',$lendFolders),
								'weight' => implode(',',$lendWeights),
								'lendingdate' => time(),
								'withdrawaldate' => 0
						));

		$content .= '<h1>Ausgabe</h1>';

		$content .= $this->renderSteps(	3,
										array(
											0 => array ('title' => 'Buchung'),
											1 => array ('title' => 'Ergebnis'),
										));

		if ($res) {
			$content .= '<h2 style="text-align:center;">Ausleihe erfolgreich!</h2>';
			$content .= '<div style="margin-left:auto; margin-right:auto; width:90%;">';
			$content .= '<p>Folgende Ordner wurden gebucht:</p>';
			$content .= '<table cellpadding="8" cellspacing="2" style="width:100%; text-align:center;">';
			$content .= '<tbody><tr><th>Gebuchte Ordner</th></tr>';
			foreach ($lendFolders as $folder) {
				$folderInstanceDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder_instance', $folder);
				$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $folderInstanceDATA['folder']);
				$content .= '<tr><td>'.$folderDATA['name'].'</td></tr>';
			}
			$content .= '</tbody></table>';
			$content .= '<p>Ausgeliehen an Nutzer <b>'.$lender_name.'</b>
				mit Mailadresse <a href="mailto:'.$lender_imt.'@campus.upb.de">'.$lender_imt.'@campus.upb.de</a>.</p></div>';
		} else {
			$content .= 'ERROR: Could not enter database information!';
		}
		$content .= '<div style="text-align:center; font-size: 20px; padding-top: 30px">'.$this->pi_linkToPage('Zurück zur Startseite',$GLOBALS['TSFE']->id).'</div>';

		return $content;
	}

	private function listAllLentFolders() {
		$content = '<div style="margin-left:auto; margin-right:auto; width:90%; text-align:center;">';
		$content .= '<h3>Ausgeliehene Ordner</h3>';
		$content .= '<table cellpadding="8" cellspacing="2" style="width:100%; text-align:center;"><tbody>';
		$content .= '<tr><th>Ordnername</th><th>Ausleihdatum</th><th>Ausleiher</th><th>Verleiher</th></tr>';
		$resLent = $GLOBALS['TYPO3_DB']->sql_query('SELECT * ' . $query . '
													FROM tx_fsmiexams_folder_instance
													WHERE state='.tx_fsmiexams_div::kFOLDER_STATE_LEND.' AND deleted=0 AND hidden=0
													ORDER BY folder_id');
		while ($resLent && $folderInstanceDATA = mysql_fetch_assoc($resLent)){
			$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $folderInstanceDATA['folder']);
			$content .= '<tr>';
			$content .= '<td><strong>'.$folderDATA['name'].'('.$folderInstanceDATA['offset'].') ['.$folderInstanceDATA['folder_id'].']</strong></td>';
			$content .= $this->printActiveLentForFolder($folderInstanceDATA['uid']);
			$content .= '</tr>';
		}
		$content .= '</tbody></table></div>';

		return $content;
	}

	private function searchForm() {
		// transaction information
		$formValues = t3lib_div::_GP($this->extKey);
		$exam = $this->escape($formValues['search']['exam']);
		$lecturer = $this->escape($formValues['search']['lecturer']);

		$types = array();
		if ($formValues['search']['examtype'] && is_array($formValues['search']['examtype'])) {
			foreach ($formValues['search']['examtype'] as $type => $state) {
				if (intval($type)==0) continue;
				$types[] = intval($type);
			}
		}

		$content .= '<h1>Suche</h1>';
		$content .= '<form method="GET" action="index.php">' . "\n";
		$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id . '"/>';
		$content .= '<input type="hidden" name="' . $this->extKey . '[step]" value="'.self::kSTEP_SHOW_SEARCH_FORM.'"/>';
		$content .= '<table>';
		$content .= '<tr><td><b>Prüfung</b></td><td><input size="16" name="'.$this->extKey . '[search][exam]" value="'.$exam.'" /></td></tr>';
		$content .= '<tr><td><b>Dozent</b></td><td><input size="16" name="'.$this->extKey . '[search][lecturer]" value="'.$lecturer.'" /></td></tr>';
		$content .= '</table>';

		// examtypes
		$content .= '<table>';
		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT *
													FROM tx_fsmiexams_examtype
													WHERE deleted=0 AND hidden=0
													ORDER BY description');
		while ($res && $typeDATA = mysql_fetch_assoc($res)) {
			$content .= '<tr><td>'.$typeDATA['description'].'</td>';
			$content .= '<td><input type="checkbox" ';
			if (count($types)==0 || in_array($typeDATA['uid'],$types)) {
				$content .= ' checked="checked" ';
			}
			$content .= ' size="16" name="'.$this->extKey . '[search][examtype]['.$typeDATA['uid'].']" /></td></tr>';
		}
		$content .= '</table>';

		// submit
		$content .= '<input type="submit" name="'.$this->extKey.'[control][search]" value="Suche" />';
		$content .= '</form>';

		$content .= $this->searchResults($lecturer, $exam, $types);

		return $content;
	}

	private function searchResults($lecturerString, $examString, $examtypes) {
		$examtypeNames = tx_fsmiexams_div::listExamTypes();

		$content = '';

		// abort if no strings given
		if ($lecturerString=='' && $examString=='')
			return '';

		$lecturers = $this->searchLecturers($lecturerString);
		$exams = $this->searchExams($examString, $lecturers, $examtypes);

		// print exams
		$content .= '<table><tr><th>Name</th><th>Dozent</th><th>Datum</th><th>Art</th><th>Ordner</th>';
		foreach ($exams as $exam) {
			$examDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_exam', $exam);
			$content .= '<tr><td>'.$examDATA['name'].' / '.tx_fsmiexams_div::examToTermdate($exam).'</td>';
			$content .= '<td>'.tx_fsmiexams_div::lecturerToText($examDATA['lecturer']).'</td>';
			$content .= '<td>'.date('d.m.Y',$examDATA['exactdate']).'</td>';
			$content .= '<td>'.$examtypeNames[$examDATA['examtype']].'</td>';
			$folders = tx_fsmiexams_div::folderInstancesForExam($exam);
			if (count($folders)>0) {
				$content .= '<td>'.implode('<br />',tx_fsmiexams_div::folderInstancesForExam($exam)).'</td>';
			}
			else {
				$content .= '<td>kein Ordner vorhanden</td>';
			}
			$content .= '</tr>';
		}
		return $content;
	}

	/** \brief returns array of lecturers
	 * the function can handle strings of kind "name,forname", "forname name", with arbitrary blanks
	 * returns empty array if empty string is given
	 */
	private function searchLecturers($lecturerSearchString) {
		if ($lecturerSearchString=='') return array();

		$searchStrings = array ();
		// case that we have firstname and lastname in commen comma-separated format
		if (count(explode(',',$lecturerSearchString))==2) {
			$strings = explode(',',$lecturerSearchString);
			$searchStrings[] = trim($strings[0]);
			$searchStrings[] = trim($strings[1]);
		}
		else { // now try to find string separation
			$searchStrings = preg_split("/[ ]+/", $lecturerSearchString);
			if (count($searchStrings)>2) {
				debug('could not identify search strings');
				return array();
			}
		}

		if (count($searchStrings)==1) {
			$searchQuery = ' lastname LIKE \'%'.$searchStrings[0].'%\' OR firstname LIKE \'%'.$searchStrings[0].'%\' ';
		}
		else {
			$searchQuery = ' (lastname LIKE \'%'.$searchStrings[0].'%\' AND firstname LIKE \'%'.$searchStrings[1].'%\') ';
			$searchQuery .= ' OR (lastname LIKE \'%'.$searchStrings[1].'%\' AND firstname LIKE \'%'.$searchStrings[0].'%\') ';
		}
		// search lecturer
		$res = $GLOBALS['TYPO3_DB']->sql_query(
			'SELECT uid FROM tx_fsmiexams_lecturer
			WHERE ('.$searchQuery.')
			AND deleted=0 AND hidden=0');

		$results = array ();
		while ($res && $lecturer = mysql_fetch_assoc($res)){
			$results[] = $lecturer['uid'];
		}
		return $results;
	}

	/** \brief returns array of exams
	 * the function can handle strings of kind "name,forname", "forname name", with arbitrary blanks
	 */
	private function searchExams($examSearchString, $lecturers, $examtypes) {
		// efficiency can be improved
		// but we expect that table to be small (hopefully)
		$optionalLecturer='';
		if (is_array($lecturers) && count($lecturers)>0) {
			$tmpLecturer = array ();
			foreach($lecturers as $uid) {
				$tmpLecturer[] = ' FIND_IN_SET('.$uid.',lecturer) ';
			}
			$optionalLecturer = ' AND ('.implode(' OR ',$tmpLecturer).') ';
		}

		// efficiency can be improved
		// but we expect that table to be small (hopefully)
		$optionalExamtypes='';
		if (is_array($examtypes) && count($examtypes)>0) {
			$tmpSearch = array ();
			foreach($examtypes as $uid) {
				$tmpSearch[] = ' FIND_IN_SET('.$uid.',examtype) ';
			}
			$optionalExamtypes = ' AND ('.implode(' OR ', $tmpSearch).') ';
		}

		if ($examSearchString=="") {
			// no exam search string
			$res = $GLOBALS['TYPO3_DB']->sql_query(
				'SELECT uid FROM tx_fsmiexams_exam
				WHERE TRUE '.$optionalLecturer.' '.$optionalExamtypes.'
				AND deleted=0 AND hidden=0');
		} else {
			$examSearchString = preg_replace('/ /', '%', $examSearchString);
			// combined search
			$res = $GLOBALS['TYPO3_DB']->sql_query(
				'SELECT uid FROM tx_fsmiexams_exam
				WHERE (name LIKE \'%'.trim($examSearchString).'%\' ) '.
				$optionalLecturer.' '.$optionalExamtypes.'
				AND deleted=0 AND hidden=0');
		}
		$results = array ();
		while ($res && $exam = mysql_fetch_assoc($res)){
			$results[] = $exam['uid'];
		}
		return $results;
	}

	/**
	 * This function checks if ANY folder in a specific set is lent
	 */
	private function isLent($folderIDs) {
		if (!is_array($folderIDs))
			return false;

		$res = $GLOBALS['TYPO3_DB']->sql_query(
			'SELECT * FROM tx_fsmiexams_folder_instance
			WHERE uid in ('.implode(',',$folderIDs).')
			AND state='.tx_fsmiexams_div::kFOLDER_STATE_LEND.' AND deleted=0 AND hidden=0');
		if ($res && mysql_num_rows($res)>0){
			return true;
		}
		return false;
	}

	/**
	 * This function checks if ANY folder in a specific set is available for lending
	 */
	private function isAvailable($folderIDs) {
		if (!is_array($folderIDs))
			return false;

		$res = $GLOBALS['TYPO3_DB']->sql_query(
			'SELECT * FROM tx_fsmiexams_folder_instance
			WHERE uid in ('.implode(',',$folderIDs).')
			AND state='.tx_fsmiexams_div::kFOLDER_STATE_PRESENT.' AND deleted=0 AND hidden=0');
		if ($res && mysql_num_rows($res)>0) {
			return true;
		}
		return false;
	}

	/**
	 * This function checks whether a folder with specified $folder_id exists or not.
	 */
	private function folderExists($folder_id) {
		$resource = $GLOBALS['TYPO3_DB']->sql_query('SELECT * FROM tx_fsmiexams_folder_instance WHERE folder_id = ' . intval($folder_id) . ' AND hidden=0');
		if ($resource && mysql_num_rows($resource)>0) {
			return true;
		} else {
			return false;
		}
	}


	private function printActiveLentForFolder($folderUID) {
	debug($folderUID);
		$content = '';
		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT * FROM tx_fsmiexams_loan
												WHERE deleted=0 AND hidden=0 AND withdrawaldate=0 AND FIND_IN_SET('.$folderUID.',folder)');
		if ($res && $loanDATA = mysql_fetch_assoc($res)){
			$content .= '<td>'.date('m.d.Y h:i',$loanDATA['lendingdate']).'</td>';
			$content .= '<td><a href="mailto:'.$loanDATA['lenderlogin'].'@campus.uni-paderborn.de">'.$loanDATA['lender'].'</a></td>';
			$content .= '<td>'.$loanDATA['dispenser'].'</td>';
		}
		return $content;
	}

	/**
	 * Returns array with the corresponding active Loans for the given set of UIDs.
	 */
	private function affectedLoansByTransaction($folderUIDs)
	{
		$loans = array();
		foreach($folderUIDs as $key) {
			$folderInstanceDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder_instance', $key);

			// now find corresponding loan
			$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT uid FROM tx_fsmiexams_loan
										WHERE deleted=0 AND hidden=0 AND withdrawaldate=0 AND FIND_IN_SET('.$key.',folder)');
			if($res && $loan = mysql_fetch_array($res)) {
				$loans[$key] = $loan['uid'];
			}
			else {
				debug("folder not contained in any loan");
			}
		}
		return $loans;
	}

	/** \brief list of folders that will still be pending after transaction
	 * functions expects array of folders that shall be withdrawaled
	 * and return array of folders that will be pending after this operation
	 */
	private function pendingFoldersAfterTransaction($folderUIDs)
	{
		$foldersToLoans = $this->affectedLoansByTransaction($folderUIDs);

		// calculate which loans can be closed
		$affectedLoans = array();			// array of loans that shall be closed
		$pendingFolders = array();			// array of folders that must be put into new loan
		$pledges = array();					// pledges that could be given back

		foreach ($foldersToLoans as $folderUID => $loanUID) {
			$loanDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_loan', $loanUID);
			$loanFolders = explode(',', $loanDATA['folder']);
			$loanFolderWeights = explode(',', $loanDATA['weight']);
			$clean = true;
			foreach ($loanFolders as $key => $folder) {
				if (!array_key_exists($folder, $foldersToLoans) ) { // case there is a folder that is not taken back
					$pendingFolders[$folder] = array( 'uid' => $folder, 'weight' => $loanFolderWeights[$key]);
				}
			}
			$pledges[] = array ( 'deposit' => $loanDATA['deposit'], 'lender' => $loanDATA['lender']);
			if (!array_key_exists($loanUID, $affectedLoans))
				$affectedLoans[] = $loanUID;
		}
		return $pendingFolders;
	}

	private function getLoanWeightForFolder($folderUID) {
		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT folder,weight FROM tx_fsmiexams_loan
									WHERE deleted=0 AND hidden=0 AND withdrawaldate=0 AND FIND_IN_SET('.$folderUID.',folder)');
		if($res && $loan = mysql_fetch_array($res)) {
			$folders = explode(',',$loan['folder']);
			$weights = explode(',',$loan['weight']);
			$offset = array_search($folderUID, $folders);
			return intval($weights[$offset]);
		}
		return 0;
	}

	private function isEasterEgg($folder_id)
	{
		// TODO:
	}

	private function escape($string)
	{
		if (isset($string))
			return $GLOBALS['TYPO3_DB']->quoteStr($string, null);
		else
			return null;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/controller/class.tx_fsmiexams_controller_clerk.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/controller/class.tx_fsmiexams_controller_clerk.php']);
}

?>