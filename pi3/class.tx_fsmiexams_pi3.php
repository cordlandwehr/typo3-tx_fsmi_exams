<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2011  Andreas Cord-Landwehr <cola@uni-paderborn.de>
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
class tx_fsmiexams_pi3 extends tslib_pibase {
	var $prefixId      = 'tx_fsmiexams_pi3';		// Same as class name
	var $scriptRelPath = 'pi3/class.tx_fsmiexams_pi3.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'fsmi_exams';	// The extension key.
	
	var $loanStoragePID		= 0;
	
	//Constant Values
	const kMODE_LEND = 1;
	const kMODE_WITHDRAWAL = 2;	
	
	const MAGIC = 'magic';
	const kGFX_PATH = 'typo3conf/ext/fsmi_exams/images/';
	const PREFIX = 'tx_fsmiexams_loan';
	
	const kSTEP_START = 1;
	const kSTEP_SECOND_PAGE = 2;
	const kSTEP_FINALIZE = 3;
	const kSTEP_SHOW_LENT_FOLDERS = 4;
	
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

		$this->$loanStoragePID =intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pidTransactions'));
		$GETcommands = t3lib_div::_GP($this->extKey);
		$this->piVars = array();
		

		//Table-Structure
		$lentInfoTable = array(0 => 'folder', 1 => 'lender', 2 => 'dispenser', 3 => 'weight', 4 => 'deposit', 5 => 'lendingdate');
		$withdrawalInfoTable = array(0 => 'folder', 1 => 'lender', 2 => 'dispenser', 3 => 'weight', 4 => 'deposit', 5 => 'lendingdate', 6 => 'withdrawal', 7 => 'withdrawaldate');

        //Important variables //TODO: Escape and set variables
		$this->piVars['step'] = intval($GETcommands['step']);
		$this->piVars['mode'] = intval($GETcommands['mode']);

		$this->piVars['lender_name'] = $this->escape($GETcommands['lender_name']);
		$this->piVars['lender_imt'] = $this->escape($GETcommands['lender_imt']);
		$this->piVars['deposit'] = $this->escape($GETcommands['deposit']);
		$this->piVars['dispenser'] = $this->escape($GETcommands['dispenser']);

		$this->piVars['folder_id'] = intval($this->escape($GETcommands['folder_id']));
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

		//Listen : <input type="hidden" value="ordner1;ordner2"


		//Style
		$content .= '<style type="text/css"> .tx-fsmiexams-pi3 form{clear:both;} .tx-fsmiexams-pi3 img{margin-bottom:5px;} .tx-fsmiexams-pi3 .step{text-align:center; width:80px; display:inline-block; margin:10px 15px;} .tx-fsmiexams-pi3 a{text-decoration:none;} .tx-fsmiexams-pi3 table{margin-left:auto; margin-right:auto; } .tx-fsmiexams-pi3 table td{background-color:AliceBlue;} .tx-fsmiexams-pi3 table th{background-color:#B5CDE1;}</style>' . "\n";
		//main_container
		$content .= '<div style="margin:0px 15px; padding-top:15px; width:700px; border:solid 1px #f00;">' . "\n";

		$content .= $this->pi_linkTP('<i>zeige Ausgeliehene</i>',array($this->extKey.'[step]' => self::kSTEP_SHOW_LENT_FOLDERS)).'';

		// on cancel go to start
		if (isset($GETcommands['control'.self::kCTRL_CANCEL])) {
			$this->piVars['step'] = self::kSTEP_START;
		}

		switch ($this->piVars['step']) {
			case self::kSTEP_SHOW_LENT_FOLDERS: {
				$content .= $this->pi_linkTP('<h3>Zurück zur Ausleihe</h3>',array()).'';
				$content .= $this->listAllLentFolders();
				break;
			}
		    case self::kSTEP_START: {
				// if next-button, need to change mode:
				if(!$this->piVars['folder_id'] && isset($GETcommands['control'.self::kCTRL_NEXT])) {
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
					if ($this->piVars['folder_id']!=0 && !$this->folderExists($this->piVars['folder_id'])) {
						$content .= tx_fsmiexams_div::printSystemMessage(
										tx_fsmiexams_div::kSTATUS_ERROR,
										"<b>Fehler:</b><br />Den eingegebenen Ordner-Barcode haben wir leider nicht im Archiv."
										);
						$content .= $this->formSecondPage();
						break;
					}
					if ($this->piVars['folder_id'] && !$this->piVars['weight']) {
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
				if ($this->piVars['mode'] == self::kMODE_WITHDRAWAL) {
					$content .= $this->transactionWithdrawFolders();
				}
				else {
					$content .= $this->transactionLendFolders();
				}
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
		$content .= $this->renderTitle('FSMI-Ausleihtool');
		$content .= $this->renderSteps(1, array());


		$content .= '<form method="GET" action="index.php">' . "\n";
		$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id . '"/>';
		$content .= '<input type="hidden" name="' . $this->extKey . '[step]" value="'.self::kSTEP_START.'"/>';
		$content .= '<div style="text-align:center"><h3>Ordnereingabe</h3><b>Der erste Ordner:</b> <input type="text" name="' . $this->extKey . '[folder_id]" size="8" maxlength="8" /></div></br>';
		
		$content .= '<p>So, das Ganze funktioniert wie folgt. Du gibst hier den ersten Ordner Barcode an von dem Ordner mit dem du etwas machen m&ouml;chtest. Danach machst du mit dem n&auml;chsten Ordner weiter etc. Wenn du alle Ordner durch hast (also Ausleihen oder Zur&uuml;cknehmen, dann gibst du die Daten vom dem Ausleiher an und schon bist du fertig.</p>';

		//Buttons
		$content .= $this->renderButtons(array("Weiter" => self::kCTRL_NEXT));
		
		return $content; 
	}

	private function formSecondPage() {
		// this page gets the initial folder ID and estimates what to do with it.
		if ($this->isLent($this->piVars['folder_id'])) {
			//Withdrawal Mode
			$content .= $this->addFolderToFolderArray($this->piVars['folder_id'],0,self::kMODE_WITHDRAWAL);
			$content .= $this->withdrawFolderForm($this->piVars['folder_id']);

			return $content;
		} else { 
			$content .= $this->addFolderToFolderArray($this->piVars['folder_id'],0,self::kMODE_LEND);
			$content .= $this->lendFolderForm();
			
			return $content;
		}

	}

	private function formFinalizeLendOrWithdrawal() {
		if ($this->piVars['mode'] == self::kMODE_WITHDRAWAL) {
			//Withdrawal Mode
			
			//Steps
			$content .= $this->renderTitle('Rücknahme');
			$content .= $this->renderSteps(	2, 
											array(
												0 => array ('title' => 'Ordner'), 
												1 => array ('title' => 'Rücknahme'), 
												2 => array ('title' => 'Pfandrückgabe')
											));

			if (isset($this->piVars['folder_id']) && isset($this->piVars['folder_weight'])) {
				$content .= $this->addFolderToFolderArray($this->piVars['folder_id'], $this->piVars['folder_weight'],self::kMODE_WITHDRAWAL);
			}

			$foldersToLoans = array();

			if(is_array($this->piVars['folder_list_array']) && count($this->piVars['folder_list_array'])>0) 
			{
				$content .= '<h3 style="text-align:center">Vorgemerkte Ordner f&uuml;r diesen Rücknahmevorgang</h3>';
				$this->piVars['renderArray'] = array();
				foreach($this->piVars['folder_list_array'] as $key => $value) {
					$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $key);
					array_push($this->piVars['renderArray'], array('folder_id' => $folderDATA['folder_id'], 'name' => $folderDATA['name'], 'weight' => $value['weight']));
					
					// now find corresponding loan
					$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT uid FROM tx_fsmiexams_loan 
												WHERE deleted=0 AND hidden=0 AND withdrawaldate=0 AND FIND_IN_SET('.$key.',folder)');
					if($res && $loan = mysql_fetch_array($res)) {
						$foldersToLoans[$key] = $loan['uid'];
					}
					else {
					    tx_fsmiexams_div::printSystemMessage(
												tx_fsmiexams_div::kSTATUS_ERROR,
												'<b>Fehler</b><br /> Der Ordner ist in keinem offenen Ausleihvorgang gebucht.'
												);
					}
					
				}
				$content .= $this->renderLentFolderInfo($this->piVars['renderArray'], array(0 => 'folder_id', 1=> 'name', 2 => 'weight'));
			}

			if (count($foldersToLoans)==0) {
				tx_fsmiexams_div::printSystemMessage(
										tx_fsmiexams_div::kSTATUS_ERROR,
										'<b>Fehler</b><br /> Der Ordner ist in keinem offenen Ausleihvorgang gebucht.'
										);
			}
			else {
				$content .= '<div><h3>Rücknahmeplan</h3>';
				foreach ($foldersToLoans as $folderUID => $loanUID) {
					$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $folderUID);
					$loanDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_loan', $loanUID);
					$content .= '<div>';
					$content .= $this->printLoanInfo($loanUID);
					$content .= '</div>';
				}
				$content .= '</div>';
			}
			
			// calculate which loans can be closed
			$closeLoans = array();				// array of loans that shall be closed
			$pendingFolders = array();			// array of folders that must be put into new loan
			$pendingFoldersLoan = array (); 	// information for new loan in case this loan is needed
			$pledges = array();					// pledges that could be given back
			
			foreach ($foldersToLoans as $folderUID => $loanUID) {
				$loanDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_loan', $loanUID);
				$loanFolders = explode(',', $loanDATA['folder']);
				$clean = true;
				foreach ($loanFolders as $folder) {
					if (!array_key_exists($folder, $foldersToLoans) ) { // case there is a folder that is not taken back
						$pendingFolders[] = $folder;
						if (!array_key_exists($loanDATA['uid'],$pendingFoldersLoan)) 
							$pendingFoldersLoan[] = $loanDATA['uid'];
						$clean = false;
					}
				}
				if ($clean) {
					$closeLoans[] = $loanDATA['uid'];
				}
				$pledges[] = array ( 'deposit' => $loanDATA['deposit'], 'lender' => $loanDATA['lender']);
			}
			
			// give form depending on which folders we get back
			if ( count($pendingFolders)==0 ) {
				
				$content .= '<form method="GET" action="index.php">' . "\n";
				$content .= '<h3 style="text-align:center">Rücknahme</h3>';
				$content .= '<table cellpadding="5">';
				$content .= '<tr><td><label><b>Zurückgenommen von:</b></label></td>
						<td><input type="text" name="'.$this->extKey.'[withdrawal]" size="30" value="'.
					(isset($this->piVars['withdrawal']) ? $this->piVars['withdrawal'] : '') . '" /></td></tr>' . "\n";
				$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id.'"/>' . "\n";
				$content .= '<input type="hidden" name="' . $this->extKey . '[step]" value="'.self::kSTEP_FINALIZE.'"/>' . "\n";
				$content .= '<input type="hidden" name="' . $this->extKey . '[mode]" value="'.self::kMODE_WITHDRAWAL.'"/>' . "\n";
				$content .= '<input type="hidden" name="' . $this->extKey . '[folder_list]" value=\'' . $this->piVars['folder_list'] . '\'/>' . "\n";
				$content .= '<input type="hidden" name="' . $this->extKey . '[folder_list_hash]" value="' . $this->piVars['folder_list_hash'] . '"/>' . "\n";
				$content .= '</table>';
				$content .= $this->renderButtons(array("Abbruch" => self::kCTRL_CANCEL, "Rücknahme abschließen" => self::kCTRL_NEXT));
				// JETZT zur DB Aktualisierung
			}
			else {
				$content .= '<h3>Folgende Ordner wurden nicht zurück gebracht</h3>';
				foreach ($pendingFolders as $folderUID) {
					$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $folderUID);
					$content .= '<div>['.$folderDATA['folder_id'].'] '.$folderDATA['name'].'</div>';
				}
				
				$loanDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_loan', end($pendingFoldersLoan));

				$content .= '<form method="GET" action="index.php">' . "\n";
				$content .= '<h3 style="text-align:center">Aktualisierte Ausleihdaten</h3>';
				$content .= '<table cellpadding="5">';
				$content .= '<tr><td><label><b>Name des Ausleihers:</b></label></td>
						<td><input type="text" name="'.$this->extKey.'[lender_name]" size="30" value="'.
						$loanDATA['lender_name']. '" /></td></tr>' . "\n";
				$content .= '<tr><td><label><b>IMT-Login des Ausleihers:</b></label></td>
						<td><input type="text" name="' . $this->extKey . '[lender_imt]" size="30" value="'.
						$loanDATA['lender_imt'] .'" /></td></tr>' . "\n";
				$content .= '<tr><td><label><b>Neues Pfand: </b></label></td>
						<td><input type="text" name="'.$this->extKey .'[deposit]" size="30" value="' .
					(isset($this->piVars['deposit']) ? $this->piVars['deposit'] : '') . '" /><br/>' . "\n";
				$content .= '<tr><td><label><b>Name des Ausgebers: </b></label></td>
						<td><input type="text" name="' . $this->extKey.'[dispenser]" size="30" value="' .
				(isset($this->piVars['dispenser']) ? $this->piVars['dispenser'] : '') . '" /></td></tr>' . "\n";
				$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id.'"/>' . "\n";
				$content .= '<input type="hidden" name="' . $this->extKey . '[step]" value="'.self::kSTEP_FINALIZE.'"/>' . "\n";
				$content .= '<input type="hidden" name="' . $this->extKey . '[mode]" value="'.self::kMODE_WITHDRAWAL.'"/>' . "\n";
				$content .= '<input type="hidden" name="' . $this->extKey . '[folder_list]" value=\'' . $this->piVars['folder_list'] . '\'/>' . "\n";
				$content .= '<input type="hidden" name="' . $this->extKey . '[folder_list_hash]" value="' . $this->piVars['folder_list_hash'] . '"/>' . "\n";
				$content .= '</table>';
				//Buttons
				$content .= $this->renderButtons(array("Abbruch" => self::kCTRL_CANCEL, "Weiter" => self::kCTRL_NEXT));
			}
			return $content;
		}
		else{
			//Lending Mode

			//Steps
			$content .= $this->renderTitle('Ausleihen');
			$content .= $this->renderSteps(	3, 
											array(
												0 => array ('title' => 'Ordner'), 
												1 => array ('title' => 'Ausleihe'), 
												2 => array ('title' => 'Ausgabe')
											));

			// put information to folder list
			$content .= $this->addFolderToFolderArray($this->piVars['folder_id'], $this->piVars['folder_weight'],self::kMODE_LEND);

			// list of scheduled folders
			if(is_array($this->piVars['folder_list_array']) && count($this->piVars['folder_list_array'])>0) 
			{
				$content .= '<h3 style="text-align:center">Folgende Ordner werden ausgeliehen</h3>';
				$this->piVars['renderArray'] = array();
				foreach($this->piVars['folder_list_array'] as $key => $value) {
					$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $key);
					array_push($this->piVars['renderArray'], array('folder_id' => $folderDATA['folder_id'], 'name' => $folderDATA['name'], 'weight' => $value['weight']));
				}
				$content .= $this->renderLentFolderInfo(
															$this->piVars['renderArray'], 
															array(0 => 'folder_id', 1=> 'name', 2 => 'weight'),
															array(0 => 20, 1=> 50, 2 => 30)
														);
			}

			$content .= '<form method="GET" action="index.php">' . "\n";
			$content .= '<h3 style="text-align:center">Ausleihdaten</h3>';
			$content .= '<table cellpadding="5">';
			$content .= '<tr><td><label><b>Name des Ausleihers:</b></label></td>
					<td><input type="text" name="'.$this->extKey.'[lender_name]" size="30" value="'.
				(isset($this->piVars['lender_name']) ? $this->piVars['lender_name'] : '') . '" /></td></tr>' . "\n";
			$content .= '<tr><td><label><b>IMT-Login des Ausleihers:</b></label></td>
					<td><input type="text" name="' . $this->extKey . '[lender_imt]" size="30" value="'.
				(isset($this->piVars['lender_imt']) ? $this->piVars['lender_imt'] : '') .'" /></td></tr>' . "\n";
			$content .= '<tr><td><label><b>Pfand: </b></label></td>
					<td><input type="text" name="'.$this->extKey .'[deposit]" size="30" value="' .
				(isset($this->piVars['deposit']) ? $this->piVars['deposit'] : '') . '" /><br/>' . "\n";
			$content .= '<tr><td><label><b>Name des Ausgebers: </b></label></td>
					<td><input type="text" name="' . $this->extKey.'[dispenser]" size="30" value="' .
			(isset($this->piVars['dispenser']) ? $this->piVars['dispenser'] : '') . '" /></td></tr>' . "\n";
			$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id.'"/>' . "\n";
			$content .= '<input type="hidden" name="' . $this->extKey . '[step]" value="'.self::kSTEP_FINALIZE.'"/>' . "\n";
			$content .= '<input type="hidden" name="' . $this->extKey . '[folder_list]" value=\'' . $this->piVars['folder_list'] . '\'/>' . "\n";
			$content .= '<input type="hidden" name="' . $this->extKey . '[folder_list_hash]" value="' . $this->piVars['folder_list_hash'] . '"/>' . "\n";
			$content .= '</table>';
			//Buttons
			$content .= $this->renderButtons(array("Abbruch" => self::kCTRL_CANCEL, "Weiter" => self::kCTRL_NEXT));

			return $content;
		}
	}


	/**
	 * This function gives you the lend-it interface for a specified and existing folder.
	 * The function assumes plausibility checks of folder existance beforehand.
	 * @param	$folderUID	UID of database entry
	 */
	private function lendFolderForm($folderUID) {
		$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $folderUID);

		//Steps
		$content .= $this->renderTitle('Ausleihen');
		
		$content .= $this->renderSteps(	2, 
										array(
											0 => array ('title' => 'Ordner'), 
											1 => array ('title' => 'Ausleihe'), 
											2 => array ('title' => 'Ausgabe')
										));

		if (isset($this->piVars['folder_id']) && isset($this->piVars['folder_weight'])) {
			$content .= $this->addFolderToFolderArray($this->piVars['folder_id'], $this->piVars['folder_weight'],self::kMODE_LEND);
		}

		if(is_array($this->piVars['folder_list_array']) && count($this->piVars['folder_list_array'])>0) 
		{
			$content .= '<h3 style="text-align:center">Vorgemerkte Ordner f&uuml;r diesen Ausleihvorgang</h3>';
			$this->piVars['renderArray'] = array();
			foreach($this->piVars['folder_list_array'] as $key => $value) {
				$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $key);
				array_push($this->piVars['renderArray'], array('folder_id' => $folderDATA['folder_id'], 'name' => $folderDATA['name'], 'weight' => $value['weight']));
			}
			$content .= $this->renderLentFolderInfo($this->piVars['renderArray'], array(0 => 'folder_id', 1=> 'name', 2 => 'weight'));
		}

		$content .= '<form method="GET" action="index.php">' . "\n";
		$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id . '"/>' . "\n";
		$content .= '<input type="hidden" name="' . $this->extKey . '[step]" value="'.self::kSTEP_SECOND_PAGE.'"/>' . "\n";
		$content .= '<input type="hidden" name="' . $this->extKey . '[mode]" value="'.self::kMODE_LEND.'"/>' . "\n";

		$content .= (isset($this->piVars['folder_list']) ? '<input type="hidden" name="' . $this->extKey . '[folder_list]" value=\'' . $this->piVars['folder_list'] . '\'/>'."\n":'');
		$content .= (isset($this->piVars['folder_list_hash']) ? '<input type="hidden" name="' . $this->extKey . '[folder_list_hash]" value="' . $this->piVars['folder_list_hash'] . '"/>'."\n":'');
		$content .= (isset($this->piVars['lender_name']) ? '<input type="hidden" name="' . $this->extKey . '[lender_name]" value="' . $this->piVars['lender_name'] . '"/>' . "\n" : '');
		$content .= (isset($this->piVars['lender_imt']) ? '<input type="hidden" name="' . $this->extKey . '[lender_imt]" value="' . $this->piVars['lender_imt'] . '"/>' . "\n" : '');
		$content .= (isset($this->piVars['deposit']) ? '<input type="hidden" name="' . $this->extKey . '[deposit]" value="' . $this->piVars['deposit'] . '"/>' . "\n" : '');
		$content .= (isset($this->piVars['dispenser']) ? '<input type="hidden" name="' . $this->extKey . '[dispenser]" value="' . $this->piVars['dispenser'] . '"/>' . "\n" : '');

		$content .= '<h3 style="text-align:center">Ordner zu Ausleihvorgang hinzufügen</h3>';
		$content .= '<table cellpadding="5" cellspacing="0" width="60%">';
		$content .= '<tr><td><label for="text_folder_id">Ordner-Code:</label></td>';
		$content .= '<td><input type="text" name="' . $this->extKey . '[folder_id]" size="8" value="'.
			($this->piVars['folder_id']==0 ? '' : $this->piVars['folder_id']).
			'" id="text_folder_id" /></td></tr>' . "\n";
		$content .= '<tr><td><label>Einzelgewicht (g):</label></td>';
		$content .= '<td><input type="text" name="'.$this->extKey.'[folder_weight]" size="8" /></td></tr>' . "\n";
		$content .= '<tr><td> </td><td><input type="submit" name="'.$this->extKey.'[control'.self::kCTRL_RELOAD.']" value="Hinzufügen" "/></td></tr>';
		$content .= '</table>';

		//Buttons
		$content .= $this->renderButtons(array("Abbruch" => self::kCTRL_CANCEL, "Weiter" => self::kCTRL_NEXT));
			
		return $content;
	}
	
	private function withdrawFolderForm() {
		//Steps
		$content .= $this->renderTitle('Rücknehmen');
		
		$content .= $this->renderSteps(	2, 
										array(
											0 => array ('title' => 'Ordner'), 
											1 => array ('title' => 'Rücknahme'), 
											2 => array ('title' => 'Pfandrückgabe')
										));

		if (isset($this->piVars['folder_id']) && isset($this->piVars['folder_weight'])) {
			$content .= $this->addFolderToFolderArray($this->piVars['folder_id'], $this->piVars['folder_weight'],self::kMODE_WITHDRAWAL);
		}

		if(is_array($this->piVars['folder_list_array']) && count($this->piVars['folder_list_array'])>0) 
		{
			$content .= '<h3 style="text-align:center">Vorgemerkte Ordner f&uuml;r diesen Rücknahmevorgang</h3>';
			$this->piVars['renderArray'] = array();
			foreach($this->piVars['folder_list_array'] as $key => $value) {
				$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $key);
				array_push($this->piVars['renderArray'], array('folder_id' => $folderDATA['folder_id'], 'name' => $folderDATA['name'], 'weight' => $value['weight']));
			}
			$content .= $this->renderLentFolderInfo($this->piVars['renderArray'], array(0 => 'folder_id', 1=> 'name', 2 => 'weight'));
		}
	
		$content .= '<form method="GET" action="index.php">' . "\n";
		$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id . '"/>' . "\n";
		$content .= '<input type="hidden" name="' . $this->extKey . '[step]" value="'.self::kSTEP_SECOND_PAGE.'"/>' . "\n";
		$content .= '<input type="hidden" name="' . $this->extKey . '[mode]" value="'.self::kMODE_WITHDRAWAL.'"/>' . "\n";

		$content .= (isset($this->piVars['folder_list']) ? '<input type="hidden" name="' . $this->extKey . '[folder_list]" value=\'' . $this->piVars['folder_list'] . '\'/>'."\n":'');
		$content .= (isset($this->piVars['folder_list_hash']) ? '<input type="hidden" name="' . $this->extKey . '[folder_list_hash]" value="' . $this->piVars['folder_list_hash'] . '"/>'."\n":'');
		$content .= (isset($this->piVars['lender_name']) ? '<input type="hidden" name="' . $this->extKey . '[lender_name]" value="' . $this->piVars['lender_name'] . '"/>' . "\n" : '');
		$content .= (isset($this->piVars['lender_imt']) ? '<input type="hidden" name="' . $this->extKey . '[lender_imt]" value="' . $this->piVars['lender_imt'] . '"/>' . "\n" : '');
		$content .= (isset($this->piVars['deposit']) ? '<input type="hidden" name="' . $this->extKey . '[deposit]" value="' . $this->piVars['deposit'] . '"/>' . "\n" : '');
		$content .= (isset($this->piVars['dispenser']) ? '<input type="hidden" name="' . $this->extKey . '[dispenser]" value="' . $this->piVars['dispenser'] . '"/>' . "\n" : '');

		$content .= '<h3 style="text-align:center">Ordner zurück geben</h3>';
		$content .= '<table cellpadding="5" cellspacing="0" width="60%">';
		$content .= '<tr><td><label for="text_folder_id">Ordner-Code:</label></td>';
		$content .= '<td><input type="text" name="' . $this->extKey . '[folder_id]" size="8" value="'.
			($this->piVars['folder_id']==0 ? '' : $this->piVars['folder_id']).
			'" id="text_folder_id" /></td></tr>' . "\n";
		$content .= '<tr><td><label>Einzelgewicht (g):</label></td>';
		$content .= '<td><input type="text" name="'.$this->extKey.'[folder_weight]" size="8" /></td></tr>' . "\n";
		$content .= '<tr><td> </td><td><input type="submit" name="'.$this->extKey.'[control'.self::kCTRL_RELOAD.']" value="einbuchen" "/></td></tr>';
		$content .= '</table>';

		//Buttons
		$content .= $this->renderButtons(array("Abbruch" => self::kCTRL_CANCEL, "Weiter zur Pfandrückgabe" => self::kCTRL_NEXT));

		return $content;

	}
	/**
	 * returns false if folder could not be added
	 * deletes also all temp. vars
	 */
	private function addFolderToFolderArray($folder_id, $weight, $mode) {
		$content = '';

		if(!is_array($this->piVars['folder_list_array']))
			$this->piVars['folder_list_array'] = array();
			
		$resFolder = $GLOBALS['TYPO3_DB']->sql_query('SELECT * ' . $query . ' FROM tx_fsmiexams_folder WHERE folder_id='.$folder_id.' AND hidden=0 AND deleted=0');
		if ($resFolder && $res = mysql_fetch_assoc($resFolder)) {

			if ($res['state'] == tx_fsmiexams_div::kFOLDER_STATE_LEND && $mode==self::kMODE_LEND) {
				$this->piVars['folder_id']='0';
				return tx_fsmiexams_div::printSystemMessage(
												tx_fsmiexams_div::kSTATUS_ERROR,
												'<b>Fehler</b><br /> Der Ordner &quot;'.$res['name'].'&quot; ist bereits verliehen. Ausleihen geht also nicht.'
												);
			}
			
			if ($weight<=0)
				return '';

			if ($res['state'] == tx_fsmiexams_div::kFOLDER_STATE_LOST) {
				$content .= tx_fsmiexams_div::printSystemMessage(
												tx_fsmiexams_div::kSTATUS_INFO,
												'<b>Für Dich zur Info</b><br /> Dieser Ordner ist LOST... aber anscheinend wiedergefunden worden. Lassen wir es mal dabei und machen den Ausleihvorang weiter.'
												);
			}
			if ($res['state'] == tx_fsmiexams_div::kFOLDER_STATE_MAINTENANCE) {
				$content .= tx_fsmiexams_div::printSystemMessage(
												tx_fsmiexams_div::kSTATUS_INFO,
												'<b>Für Dich zur Info</b><br /> Für das System ist der Ordner gerade in der händischen Überarbeitung/Wartung und sein Status wurde  nicht auf &quot;verfügbar&quot; zurück gesetzt. Mit dem Abschluss der Ausleihe nehmen wir ihn nun wieder in das System.'
												);
			}
			$this->piVars['folder_list_array'][$res['uid']]['weight'] = $weight;
			$this->piVars['folder_list_array'][$res['uid']]['mode'] = $mode;
			$this->piVars['folder_id'] = '';
			$this->piVars['folder_weight'] = '';
			$this->piVars['folder_list'] = serialize($this->piVars['folder_list_array']);
			$this->piVars['folder_list_hash'] = md5($this->piVars['folder_list'] . self::MAGIC);
			return $content;
		}

		return $content;
	}

	/**
	 * renders given array of buttons. Each button-value is expected to be $label => $value.
	 * name then is '.$this->extKey.'[control'.$value.']
	 */
	private function renderButtons($buttons) {
		if (!is_array($buttons)) 
			return '';
			
		$content = '<div>';
		foreach ($buttons as $label => $value) {
			$content .= '<input type="submit" name="'.$this->extKey.'[control'.$value.']" value="' . $label . '" style="float:left;"/>';
		}
		return $content;
	}

	private function renderSteps($currentStep, $titles) {
		$steps = '<div style="text-align:center;>';
		$steps .= '<a href="index.php?id='.$GLOBALS['TSFE']->id.'">';
		$steps .= '<div class="step"><img src="typo3conf/ext/fsmi_exams/images/one_' .
	         ($currentStep==1 ? 'active' : 'inactive') .
	         '.png"/><b>Startseite</b></div></a>'."\n";

		$steps .= isset($titles[0]) ? '<div class="step"><img src="' . self::kGFX_PATH . 'two_' .
	                            ($currentStep==2 ? 'active' : 'inactive') .
								'.png"/><b>' . $titles[0]['title'] . '</b></div>' . "\n" : '';
		$steps .= isset($titles[1]) ? '<div class="step"><img src="' . self::kGFX_PATH . 'three_'.
	                            ($currentStep==3 ? 'active':'inactive').
								'.png"/><b>' . $titles[1]['title'] . '</b></div>' . "\n" : '';
		$steps .= isset($titles[2]) ? '<div class="step"><img src="' . self::kGFX_PATH . 'four_' .
	                            ($currentStep==4 ? 'active' : 'inactive') .
								'.png"/><b>' . $titles[2]['title'] . '</b></div>'."\n":'';
		$steps .= isset($titles[3]) ? '<div class="step"><img src="' . self::kGFX_PATH . 'five_' .
	                            ($currentStep==5 ? 'active' : 'inactive') .
								'.png"/><b>' . $titles[3]['title'] . '</b></div>' . "\n" : '';
		return $steps . "</div>";
	}

	private function renderTitle($title) {
		return '<h1 style="text-align:center; color:Gainsboro;">' . $title . '</h2>';
	}

	private function renderLentFolderInfo($folderArray, $tableStructure, $relativeSizes=null) {
		$infoTable = '';
		$infoTable .= '<table cellpadding="8" cellspacing="2" style="width: 90%; text-align:center"><tr>';

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
	
	function printLoanInfo($loanUID, $state=true) {
		$loanDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_loan', $loanUID);
		debug($loanDATA);
		$content = '<div><tt>';
		$content .= "<strong>Leihvorgang ID $loanUID</strong> erstellt am ".date('d.m.Y h:i',$loanDATA['lendingdate']).", Pfand: ".$loanDATA['deposit'].'<br />';
		$folders = explode(",", $loanDATA['folder']);
		$weights = explode(",", $loanDATA['weight']);
		debug($weights);
		foreach ($folders as $id => $folderUID) {
			$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $folderUID);
			$content .= ' - ['.$folderDATA['folder_id'].'] '.$folderDATA['name'].', Gewicht: '.$weights[$id].'g';
			if (array_key_exists($folderDATA['uid'], $this->piVars['folder_list_array'])) {
				$returnWeight = $this->piVars['folder_list_array'][$folderDATA['uid']]['weight'];
				$content .= ' ----> Rückgabe mit '.$returnWeight.'g';
				if ($weights[$id]*1.0/$returnWeight > 1.05) {
					if ($state) $content .= ' <strong style="color:red"> (Achtung: &gt;5% Abweichung)</strong>';
				}
				else {
					if ($state) $content .= ' <strong style="color:green"> (alles gut)</strong>';
				}
			}
			else {
			    $content .= ' ----> <strong style="color:orange">keine Rückgabe</strong>';
			}
			$content .= '<br />';
		}
		$content .= '</tt></div>';
		return $content;
	}
	
	
	private function transactionWithdrawFolders() {
		$content = '';
		
		// transaction information
		$formValues = t3lib_div::_GP($this->extKey);
		$withdrawal = $this->escape($formValues['withdrawal']);
		$new_deposit = $this->escape($formValues['deposit']);
		$lender_name = $this->escape($formValues['lender_name']);
		$lender_imt = $this->escape($formValues['lender_imt']);
		$deposit = $this->escape($formValues['deposit']);
		$dispenser = $this->escape($formValues['dispenser']);
		$folders = $this->piVars['folder_list_array'];
	
		$foldersToLoans = array();
		foreach($this->piVars['folder_list_array'] as $key => $value) {
			$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $key);

			// now find corresponding loan
			$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT uid FROM tx_fsmiexams_loan 
										WHERE deleted=0 AND hidden=0 AND withdrawaldate=0 AND FIND_IN_SET('.$key.',folder)');
			if($res && $loan = mysql_fetch_array($res)) {
				$foldersToLoans[$key] = $loan['uid'];
			}
			else {
				tx_fsmiexams_div::printSystemMessage(
										tx_fsmiexams_div::kSTATUS_ERROR,
										'<b>Fehler</b><br /> Der Ordner ist in keinem offenen Ausleihvorgang gebucht.'
										);
			}
		}
	
		// calculate which loans can be closed
		$affectedLoans = array();				// array of loans that shall be closed
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
		}
		
		if (count($foldersToLoans)==0) {
			tx_fsmiexams_div::printSystemMessage(
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
					$content .= 'ERROR: Fehler beim Schließen von Leihvorgang '.$loan.'. Bitte diese Seite ausdrucken und dem Admin überreichen.';
			}
			// then we close all folders that ar taken back
			foreach ($foldersToLoans as $folder) {
				$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
							'tx_fsmiexams_folder',
							'uid = '.intval($folder),
							array ( 'state' => tx_fsmiexams_div::kFOLDER_STATE_PRESENT )
							);
				if (!res)
					$content .= 'ERROR: Konnte Zustand von Ordner UID='.$folder.' nicht freigeben.';
			}
			// print out what is closed
			$content .= '<div><h3>Abgeschlossene Ausleihvorgänge</h3>';
			foreach ($affectedLoans as $loanUID) {
				$content .= '<div>';
				$content .= $this->printLoanInfo($loanUID, false);
				$content .= '</div>';
			}
			$content .= '</div>';
			// and create an new loan with everything that is left
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
			$content .= '<h4>Folgender Neuer Ausleihvorgang wurde angelegt</h4>';
			if ($res && $newLoan = mysql_fetch_array($res))
				$content .= $this->printLoanInfo($newLoan['uid'], false);
			else
				$content .= 'ERROR: neuer Ausleihvorgang konnte nicht angelegt werden.';

		}
		$content .= '<h4>Folgende Pfandstücke können zurückgegeben werden:</h4>';
		$content .= '<ul>';
		foreach ($pledges as $pledge)
			$content .= '<li>'.$pledge['lender'].': '.$pledge['deposit'].'</li>';
		$content .= '</ul>';
		
		if ($new_deposit) {
			$content .= '<h4>Neues Pfand das einbehalten werden muss</h4>';
			$content .= $new_deposit;
		}

		$content .= '<div style="text-align:center; font-size: 20px; padding-top: 30px">'.$this->pi_linkToPage('Zurück zur Startseite',$GLOBALS['TSFE']->id).'</div>';
		return $content;
	}
	
	/**
	 *
	 */
	private function transactionLendFolders() {
		$content = '';	// the transaction log
	
		// first: get all interesting values
		$formValues = t3lib_div::_GP($this->extKey);
		$lender_name = $this->escape($formValues['lender_name']);
		$lender_imt = $this->escape($formValues['lender_imt']);
		$deposit = $this->escape($formValues['deposit']);
		$dispenser = $this->escape($formValues['dispenser']);
		$folders = $this->piVars['folder_list_array'];

		if ($formValues=='' || $lender_name=='' || $lender_imt=='' || $deposit=='' || $dispenser=='' || count($folders)<1 ) {
			// TODO give more feedback
			$content .= tx_fsmiexams_div::printSystemMessage(
										tx_fsmiexams_div::kSTATUS_WARNING,
										"<b>Achtung:</b><br />Weiter geht es erst, wenn du alle Felder ausgefüllt hast."
										);
			$content .= $this->formFinalizeLendOrWithdrawal();
			return $content;
		}
		
		// second: database transformations, and there first secure all folders
		$lendFolders = array ();
		$lendWeights = array ();
		foreach ($folders as $folderUID => $info) {
			$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
						'tx_fsmiexams_folder',
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

		if ($res) {
			$content .= '<h3>Ausleihe erfolgreich!</h3>';
			$content .= '<p>Folgende Ordner wurden gebucht:</p><ul>';
			foreach ($lendFolders as $folder) {
				$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $folder);
				$content .= '<li>'.$folderDATA['name'].'</li>';
			}
			$content .= '</ul>';
			$content .= '<p>Ausgeliehen auf Nutzer <b>'.$lender_name.'</b> 
				mit Mailadresse <a href="mailto:'.$lender_imt.'@campus.upb.de">'.$lender_imt.'@campus.upb.de</a>.</p>';
		} else {
			$content .= 'ERROR: Could not enter database information!';
		}
		$content .= '<div style="text-align:center; font-size: 20px; padding-top: 30px">'.$this->pi_linkToPage('Zurück zur Startseite',$GLOBALS['TSFE']->id).'</div>';
		
		return $content;
	}

	private function listAllLentFolders() {
		$content = '';
		$resLent = $GLOBALS['TYPO3_DB']->sql_query('SELECT * ' . $query . ' 
													FROM tx_fsmiexams_folder 
													WHERE state='.tx_fsmiexams_div::kFOLDER_STATE_LEND.' AND deleted=0 AND hidden=0
													ORDER BY folder_id');
		while ($resLent && $folderDATA = mysql_fetch_assoc($resLent)){
			$content .= '<div><strong>'.$folderDATA['name'].' ['.$folderDATA['folder_id'].']</strong>';
			$content .= $this->printActiveLentForFolder($folderDATA['uid']);
			$content .= '</div>';
		}
		return $content;
	}

	//DB_functions
	private function getLentFolderInfo($folder_id, $tableStructure) {

		$retArray = array();
		$query = '';
		foreach ($tableStructure as $field)
			$query .= ($query=='' ? '' : ',') . $field;

		$resLent = $GLOBALS['TYPO3_DB']->sql_query('SELECT * ' . $query . ' FROM tx_fsmiexams_loan WHERE folder = ' . $folder_id . ' AND hidden=0');
		while ($resLent && $res = mysql_fetch_assoc($resLent)) {
			foreach ($tableStructure as $field)
				$retArray[$field] = $res[$field];
			return $retArray;
		}
		return $retArray;
	}

	/**
	 * This function checks if a specific folder is lend or not.
	 */
	private function isLent($folder_id) {
		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT * FROM tx_fsmiexams_folder WHERE folder_id = ' . intval($folder_id) . ' AND hidden=0');
		if ($res && $folderDATA = mysql_fetch_assoc($res)){
			if ($folderDATA['state']==tx_fsmiexams_div::kFOLDER_STATE_LEND) {
				return true;
			}
		}
		return false;
	}

	/**
	 * This function checks whether a folder with specified $folder_id exists or not.
	 */
	private function folderExists($folder_id) {
		$resLent = $GLOBALS['TYPO3_DB']->sql_query('SELECT * FROM tx_fsmiexams_folder WHERE folder_id = ' . intval($folder_id) . ' AND hidden=0');
		if ($resLent && $res = mysql_fetch_assoc($resLent))
			return true;
		else 
			return false;	
	}

	private function printActiveLentForFolder($folderUID) {
		$content = '';
		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT * FROM tx_fsmiexams_loan 
												WHERE deleted=0 AND hidden=0 AND withdrawaldate=0 AND FIND_IN_SET('.$folderUID.',folder)');
		if ($res && $loanDATA = mysql_fetch_assoc($res)){
			$content .= '<ul>';
			$content .= '<li>verliehen am: '.date('m.d.Y h:i',$loanDATA['lendingdate']).'</li>';
			$content .= '<li>geliehen von: <a href="mailto:'.$loanDATA['lenderlogin'].'@campus.uni-paderborn.de">'.$loanDATA['lender'].'</a></li>';
			$content .= '<li>herausgegeben von: '.$loanDATA['dispenser'].'</li>';
			$content .= '</ul>';
		}
		return $content;
	}

	private function isEasterEgg($folder_id)
	{
		// TODO:
	}
	
	private function escape($string)
	{
		if (isset($string))
			return $GLOBALS['TYPO3_DB']->quoteStr($string);
		else
			return null;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/pi3/class.tx_fsmiexams_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/pi3/class.tx_fsmiexams_pi3.php']);
}

?>