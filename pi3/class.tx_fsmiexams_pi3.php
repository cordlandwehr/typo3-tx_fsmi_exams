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
	
	//Constant Values
	const MODE_LEND = 1;
	const MODE_WITHDRAW = 2;	
	const MAGIC = 'magic';
	const kGFX_PATH = 'typo3conf/ext/fsmi_exams/images/';
	const PREFIX = 'tx_fsmiexams_loan';
	
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

		$GETcommands = t3lib_div::_GP($this->extKey);
		$this->piVars = array();
		

		//Table-Structure
		$lentInfoTable = array(0 => 'folder', 1 => 'lender', 2 => 'dispenser', 3 => 'weight', 4 => 'deposit', 5 => 'lendingdate');
		$withdrawalInfoTable = array(0 => 'folder', 1 => 'lender', 2 => 'dispenser', 3 => 'weight', 4 => 'deposit', 5 => 'lendingdate', 6 => 'withdrawal', 7 => 'withdrawaldate');

        //Important variables //TODO: Escape and set variables
		$this->piVars['type'] = intval($GETcommands['type']);
		$this->piVars['mode'] = intval($GETcommands['mode']);
		$this->piVars['buttonLeft'] = $this->escape($GETcommands['buttonLeft']);
		$this->piVars['buttonCenter'] = $this->escape($GETcommands['buttonCenter']);
		$this->piVars['buttonRight'] = $this->escape($GETcommands['buttonRight']);


		$this->piVars['lender_name'] = $this->escape($GETcommands['lender_name']);
		$this->piVars['lender_imt'] = $this->escape($GETcommands['lender_imt']);
		$this->piVars['deposit'] = $this->escape($GETcommands['deposit']);
		$this->piVars['dispenser'] = $this->escape($GETcommands['dispenser']);

		$this->piVars['folder_id'] = intval($this->escape($GETcommands['folder_id']));
		$this->piVars['folder_weight'] = $this->escape($GETcommands['folder_weight']);
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
		$content .= '<style type="text/css">.tx-fsmiexams-pi3 text{font-size:x-large;} .tx-fsmiexams-pi3 form{clear:both; padding-top:50px;} .tx-fsmiexams-pi3 textarea{font-size:x-large; margin-bottom:50px;} .tx-fsmiexams-pi3 input{font-size:large;} .tx-fsmiexams-pi3 img{margin-bottom:5px;} .tx-fsmiexams-pi3 .step{text-align:center; width:80px; display:inline-block; margin:10px 15px;} .tx-fsmiexams-pi3 #title{ color:Gainsboro; text-align:center; font-size:xx-large;} .tx-fsmiexams-pi3 a{text-decoration:none;} .tx-fsmiexams-pi3 table{margin-left:auto; margin-right:auto; font-size:large;} .tx-fsmiexams-pi3 table td{background-color:AliceBlue;} .tx-fsmiexams-pi3 table th{background-color:#B5CDE1;}</style>' . "\n";
		//main_container
		$content .= '<div style="margin:0px 15px; padding-top:15px; width:700px; border:solid 1px #f00;">' . "\n";

		switch ($this->piVars['type']) {
			case 4: {
				if (isset($this->piVars['buttonLeft']))
					$type=2;
				else
				if ($this->piVars['mode'] == self::MODE_WITHDRAW) {
					//Withdrawal Mode

					//Steps
					$content .= $this->renderTitle($this->pi_getLL("withdrawal"));
					$content .= $this->renderSteps(	4, 
													array(0 => $this->pi_getLL("folder"), 
													1 => $this->pi_getLL("withdrawal"), 
													2 => $this->pi_getLL("overview"))
												  );


					//TODO: Add Last Folder to folder_list
					if (!is_array($this->piVars['folder_list_array']))
						$this->piVars['folder_list_array'] = array();

					//TODO: Withdrawal DB update

					/*** >> Anzeige der ausgeliehenen Ordner + weitere Informationen <<***/

					$this->piVars['folderInfoArray'] = array();
					foreach ($this->piVars['folder_list_array'] as $folderInfo) {
						array_push($this->piVars['folderInfoArray'], $this->getLentFolderInfo($folderInfo, $withdrawalInfoTable));
					}
					$content .= $this->renderLentFolderInfo($this->piVars['folderInfoArray'], $withdrawalInfoTable);

					//Buttons
					$content .= $this->renderButtons(null, null, null, $this->extKey);

					break;
				} else {
					//Lending Mode
					//Steps
					$content .= $this->renderTitle($this->pi_getLL("lend_it"));
					$content .= $this->renderSteps(4, array(0 => 'Ordner', 1 => 'Ausleihe', 2 => 'Ausgabe'));


					//TODO: Finally write entrys to DB
					//TODO: Add Last Folder to folder_list
					if (!isset($this->piVars['folder_list_array']))
						$this->piVars['folder_list_array'] = array();

					$this->piVars['folder_list'] = serialize($this->piVars['folder_list_array']);
					$this->piVars['folder_list_hash'] = md5($this->piVars['folder_list'] . self::MAGIC);


					/*** >> Liste der Entliehenen Ordner+Gewichte << ***/
					if (isset($this->piVars['folder_list'])) {
						//TODO: Render Already Chosen Folders
						$this->piVars['renderArray'] = array();
						foreach ($this->piVars['folder_list_array'] as $key => $value)
							array_push($this->piVars['renderArray'], array('folder' => $key, 'weight' => $value));
						$content .= $this->renderLentFolderInfo($this->piVars['renderArray'], array(0 => 'folder', 1 => 'weight'));
					}


					//Buttons
					$content .= $this->renderButtons(null, null, null, $this->extKey);

					break;
				}
			}
			case 3: {
				$content .= $this->controllerAddLenderInformation();
				break;
			}
			case 2: {
				$content .= $this->controllerStartLendOrWithdrawal();
				break;
			};
			
		    default: {

				$content .= $this->formStartEverything();
			};
		}


	    $content .= '</form>';

		$content .= '</div>';

		/*
			<label index="tx_fsmiexams_loan">Ausleihe</label>
			<label index="tx_fsmiexams_loan.folder">Ordner</label>
			<label index="tx_fsmiexams_loan.lender">Ausleiher</label>
			<label index="tx_fsmiexams_loan.dispenser">Ausgeber</label>
			<label index="tx_fsmiexams_loan.lenderlogin">Ausleher IMT Login</label>
			<label index="tx_fsmiexams_loan.weight">Gewicht (g)</label>
			<label index="tx_fsmiexams_loan.withdrawal">Rückname von</label>
			<label index="tx_fsmiexams_loan.withdrawaldate">Rücknahme Datum</label>
			<label index="tx_fsmiexams_loan.deposit">Pfand</label>
			<label index="tx_fsmiexams_loan.lendingdate">Ausleihe Datum</label>
		*/


//static t3lib_div::cmpIP


		return $this->pi_wrapInBaseClass($content);
	}

	private function controllerStartLendOrWithdrawal() {
		if (!$this->folderExists($this->piVars['folder_id'])) {
			return "<h3>Fehler: Kein Order mit dieser ID bekannt</h3>".
					$this->formStartEverything();
		}
			
		if ($this->isLent($this->piVars['folder_id'])) {
			//Withdrawal Mode
			
			//TODO: Liste von zurückgenommenen Ordnern erstellen (wie folder_list)


			//Steps
			$content .= $this->renderTitle('R&uuml;cknehmen');
			$content .= $this->renderSteps(2, array(0 => 'Ordner', 1 => 'R&uuml;cknahme' ,2 => '&Uuml;bersicht'));


			$content .= 'blaaa' . serialize($this->piVars['folder_list_array']);
			//List
			//TODO: Add Last Folder to folder_list
			if (!isset($this->piVars['folder_list_array']))
				$this->piVars['folder_list_array'] = array();
				
			if ($this->piVars['folder_id'] != "") {
				array_push($this->piVars['folder_list_array'], $this->piVars['folder_id']); //TODO: does folder exist?
				$this->piVars['folder_id'] = '';
			}
			
			$this->piVars['folder_list'] = serialize($this->piVars['folder_list_array']);
			$this->piVars['folder_list_hash'] = md5($this->piVars['folder_list'] . self::MAGIC);

			$content .= serialize($this->piVars['folder_list_array']);
			/*** >> Anzeige der ausgeliehenen Ordner + weitere Informationen <<***/
			$this->piVars['folderInfoArray'] = array();
			foreach($this->piVars['folder_list_array'] as $folderInfo)
				array_push($this->piVars['folderInfoArray'], $this->getLentFolderInfo($folderInfo, $lentInfoTable));

			$content .= $this->renderLentFolderInfo($this->piVars['folderInfoArray'], $lentInfoTable);

			//Form
			$content .= '<form method="GET" action="index.php">' . "\n";
			$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id . '"/>' . "\n";
			$content .= '<input type="hidden" name="' . $this->extKey . '[type]" value="3"/>' . "\n";
			$content .= '<input type="hidden" name="' . $this->extKey . '[mode]" value="2"/>' . "\n";


			$content .= (isset($this->piVars['folder_list']) ? '<input type="hidden" name="' . $this->extKey . '[folder_list]" value=\'' . $this->piVars['folder_list'] . '\'/>' . "\n" : '');
			$content .= (isset($this->piVars['folder_list_hash']) ? '<input type="hidden" name="' . $this->extKey . '[folder_list_hash]" value="' . $this->piVars['folder_list_hash'] . '"/>' . "\n" : '');

			$content .= '<text><b>Ordner:</b></text><br/><textarea name="' . $this->extKey . '[folder_id]">' . $this->piVars['folder_id'] . '</textarea><br/>' . "\n";

			//Buttons
			$content .= $this->renderButtons(null, 'Wiederholen', 'Weiter', $this->extKey);

			return $content;
		} else { 
			//Lending Mode
			$resFolder = $GLOBALS['TYPO3_DB']->sql_query('SELECT * ' . $query . ' FROM tx_fsmiexams_folder WHERE folder_id = ' . $this->piVars['folder_id'] . ' AND hidden=0 AND deleted=0');
			if ($resFolder && $res = mysql_fetch_assoc($resFolder)) {
				$content = $this->lendFolderForm($res['uid']);
			}
			else {
				return "FEHLER: konnte Ordner nicht finden.";
			}
			
			return $content;
		}

	}

	private function controllerAddLenderInformation() 
	{
		if (isset($this->piVars['buttonCenter']))
			$type=2;
		else
		if (isset($this->piVars['buttonLeft']))
			$type=1;
		else
		if ($this->piVars['mode'] == self::MODE_WITHDRAW) {
			//Withdrawal Mode

			//Steps
			$content .= $this->renderTitle('R&uuml;cknehmen');
			$content .= $this->renderSteps(3, array(0 => 'Ordner', 1 => 'R&uuml;cknahme', 2 => '&Uuml;bersicht'));

			// put information to folder list
			$this->addFolderToFolderArray($this->piVars['folder_id'], $this->piVars['folder_weight']);

			$content .= serialize($this->piVars['folder_list_array']);
			/*** >> Anzeige der ausgeliehenen Ordner + weitere Informationen <<***/
			$this->piVars['folderInfoArray'] = array();
			foreach ($this->piVars['folder_list_array'] as $folderInfo) {
				array_push($this->piVars['folderInfoArray'], $this->getLentFolderInfo($folderInfo, $lentInfoTable));
			}
			$content .= $this->renderLentFolderInfo($this->piVars['folderInfoArray'], $lentInfoTable);

			//Form
			$content .= '<form method="GET" action="index.php">' . "\n";
			$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id . '"/>' . "\n";
			$content .= '<input type="hidden" name="' . $this->extKey . '[type]" value="4"/>' . "\n";
			$content .= '<input type="hidden" name="' . $this->extKey . '[mode]" value="2"/>' . "\n";


			$content .= (isset($this->piVars['folder_list']) ? '<input type="hidden" name="' . $this->extKey . '[folder_list]" value=\'' . $this->piVars['folder_list'] . '\'/>' . "\n" : '');
			$content .= (isset($this->piVars['folder_list_hash']) ? '<input type="hidden" name="' . $this->extKey . '[folder_list_hash]" value="' . $this->piVars['folder_list_hash'] . '"/>' . "\n":'');

			$content .= '<text><b>R&uuml;cknahme von:</b></text><br/><textarea name="' . $this->extKey . '[withdrawal]"></textarea><br/>' . "\n";

			//Buttons
			$content .= $this->renderButtons(null, null, 'Fertig', $this->extKey);

			return $content;
		}
		else{
			//Lending Mode

			//Steps
			$content .= $this->renderTitle('Ausleihen');
			$content .= $this->renderSteps(3, array(0 => 'Ordner', 1 => 'Ausleihe', 2 => 'Ausgabe'));

			// put information to folder list
			$this->addFolderToFolderArray($this->piVars['folder_id'], $this->piVars['folder_weight']);

			// list of scheduled folders
			if(is_array($this->piVars['folder_list_array']) && count($this->piVars['folder_list_array'])>0) 
			{
				$content .= '<h3>Folgende Ordner werden ausgeliehen</h3>';
				$this->piVars['renderArray'] = array();
				foreach($this->piVars['folder_list_array'] as $key => $value) {
					$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $key);
					array_push($this->piVars['renderArray'], array('folder_id' => $folderDATA['folder_id'], 'name' => $folderDATA['name'], 'weight' => $value));
				}
				$content .= $this->renderLentFolderInfo($this->piVars['renderArray'], array(0 => 'folder_id', 1=> 'name', 2 => 'weight'));
			}

			$content .= '<form method="GET" action="index.php">' . "\n";
			$content .= '<label><b>Name des Ausleihers:</b> </label><input type="text" name="'.$this->extKey.'[lender_name]" size="30" value="'.
				(isset($this->piVars['lender_name']) ? $this->piVars['lender_name'] : '') . '" /><br/>' . "\n";
			$content .= '<label><b>IMT-Login des Ausleihers:</b> </label><input type="text" name="' . $this->extKey . '[lender_imt]" size="30" value="'.
				(isset($this->piVars['lender_imt']) ? $this->piVars['lender_imt'] : '') .'" /><br/>' . "\n";
			$content .= '<label><b>Pfand: </b></label><input type="text" name="'.$this->extKey .'[deposit]" size="30" value="' .
				(isset($this->piVars['deposit']) ? $this->piVars['deposit'] : '') . '" /><br/>' . "\n";
			$content .= '<hr/><br/>' . "\n";
			$content .= '<label><b>Name des Ausgebers: </b></label><input type="text" name="' . $this->extKey.'[dispenser]" size="30" value="' .
			(isset($this->piVars['dispenser']) ? $this->piVars['dispenser'] : '') . '" />' . "\n";
			$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id.'"/>' . "\n";
			$content .= '<input type="hidden" name="' . $this->extKey . '[type]" value="4"/>' . "\n";
			$content .= '<input type="hidden" name="' . $this->extKey . '[folder_list]" value=\'' . $this->piVars['folder_list'] . '\'/>' . "\n";
			$content .= '<input type="hidden" name="' . $this->extKey . '[folder_list_hash]" value="' . $this->piVars['folder_list_hash'] . '"/>' . "\n";
			//Buttons
			$content .= $this->renderButtons('Zur&uuml;ck', null, 'Ausleihen!', $this->extKey);

			return $content;
		}
	}

	private function formStartEverything() {
		$content = '';
		//Steps
		$content .= $this->renderTitle('FSMI-Ausleihtool');
		$content .= $this->renderSteps(1, array());


		$content .= '<form method="GET" action="index.php">' . "\n";
		$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id . '"/>';
		$content .= '<input type="hidden" name="' . $this->extKey . '[type]" value="2"/>';
		$content .= '<div style="text-align:center"><h3>Ordnereingabe</h3><b>Der erste Ordner:</b> <input type="text" name="' . $this->extKey . '[folder_id]" size="8" maxlength="8" /></div></br>';
		
		$content .= '<p>So, das Ganze funktioniert wie folgt. Du gibst hier den ersten Ordner Barcode an von dem Ordner mit dem du etwas machen m&ouml;chtest. Danach machst du mit dem n&auml;chsten Ordner weiter etc. Wenn du alle Ordner durch hast (also Ausleihen oder Zur&uuml;cknehmen, dann gibst du die Daten vom dem Ausleiher an und schon bist du fertig.</p>';

		//Buttons
		$content .= $this->renderButtons(null, null, 'Weiter', $this->extKey);
		
		return $content; 
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
		$content .= $this->renderSteps(2, array(0 => 'Ordner', 1 => 'Ausleihe', 2 => 'Ausgabe'));

		if (isset($this->piVars['folder_id']) && isset($this->piVars['folder_weight'])) {
			$this->addFolderToFolderArray($this->piVars['folder_id'], $this->piVars['folder_weight']);
		}

		if(is_array($this->piVars['folder_list_array']) && count($this->piVars['folder_list_array'])>0) 
		{
			$content .= '<h3>Vorgemerkte Ordner f&uuml;r diesen Ausleihvorgang</h3>';
			$this->piVars['renderArray'] = array();
			foreach($this->piVars['folder_list_array'] as $key => $value) {
				$folderDATA = t3lib_BEfunc::getRecord('tx_fsmiexams_folder', $key);
				array_push($this->piVars['renderArray'], array('folder_id' => $folderDATA['folder_id'], 'name' => $folderDATA['name'], 'weight' => $value));
			}
			$content .= $this->renderLentFolderInfo($this->piVars['renderArray'], array(0 => 'folder_id', 1=> 'name', 2 => 'weight'));
		}



		$content .= '<form method="GET" action="index.php">' . "\n";
		$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id . '"/>' . "\n";
		$content .= '<input type="hidden" name="' . $this->extKey . '[type]" value="3"/>' . "\n";
		$content .= '<input type="hidden" name="' . $this->extKey . '[mode]" value="1"/>' . "\n";

		$content .= (isset($this->piVars['folder_list']) ? '<input type="hidden" name="' . $this->extKey . '[folder_list]" value=\'' . $this->piVars['folder_list'] . '\'/>'."\n":'');
		$content .= (isset($this->piVars['folder_list_hash']) ? '<input type="hidden" name="' . $this->extKey . '[folder_list_hash]" value="' . $this->piVars['folder_list_hash'] . '"/>'."\n":'');
		$content .= (isset($this->piVars['lender_name']) ? '<input type="hidden" name="' . $this->extKey . '[lender_name]" value="' . $this->piVars['lender_name'] . '"/>' . "\n" : '');
		$content .= (isset($this->piVars['lender_imt']) ? '<input type="hidden" name="' . $this->extKey . '[lender_imt]" value="' . $this->piVars['lender_imt'] . '"/>' . "\n" : '');
		$content .= (isset($this->piVars['deposit']) ? '<input type="hidden" name="' . $this->extKey . '[deposit]" value="' . $this->piVars['deposit'] . '"/>' . "\n" : '');
		$content .= (isset($this->piVars['dispenser']) ? '<input type="hidden" name="' . $this->extKey . '[dispenser]" value="' . $this->piVars['dispenser'] . '"/>' . "\n" : '');

		$content .= '<h3>Weitere Ordner ausleihen</h3>';
		$content .= '<table>';
		$content .= '<tr><td><label for="text_folder_id">Code:</label></td>';
		$content .= '<td><input type="text" name="' . $this->extKey . '[folder_id]" size="8" value="'.$this->piVars['folder_id'].'" id="text_folder_id" /></td></tr>' . "\n";
		$content .= '<tr><td><label>Einzelgewicht:</label></td>';
		$content .= '<td><input type="text" name="'.$this->extKey.'[folder_weight]" size="8" /></td></tr>' . "\n";
		$content .= '</table>';
		$content .= $this->renderButtons(null, 'Ordner hinzufügen', 'Nutzerdaten eintragen/weiter', $this->extKey);

		//Buttons
		
			
		return $content;
	}

	/**
	 * returns false if folder could not be added
	 * deletes also all temp. vars
	 */
	private function addFolderToFolderArray($folder_id, $weight) {
		if ($weight<=0)
			return false;

		if(!is_array($this->piVars['folder_list_array']))
			$this->piVars['folder_list_array'] = array();
			
		$resFolder = $GLOBALS['TYPO3_DB']->sql_query('SELECT * ' . $query . ' FROM tx_fsmiexams_folder WHERE folder_id='.$folder_id.' AND hidden=0 AND deleted=0');
		if ($resFolder && $res = mysql_fetch_assoc($resFolder)) {
			$this->piVars['folder_list_array'][$res['uid']] = $weight;
			$this->piVars['folder_id'] = '';
			$this->piVars['folder_weight'] = '';
			$this->piVars['folder_list'] = serialize($this->piVars['folder_list_array']);
			$this->piVars['folder_list_hash'] = md5($this->piVars['folder_list'] . self::MAGIC);
			return true;
		}

		return false;
	}

	private function renderButtons($buttonLeft, $buttonCenter, $buttonRight, $ext_key) {
		$buttons = '<br/>';
		$buttons .= (!isset($buttonLeft)? '':
	            '<input type="submit" name="' . $ext_key . '[buttonLeft]" value="' . $buttonLeft . '" style="float:left;"/>');
		$buttons .= (!isset($buttonCenter)?'':
				'<input type="submit" name="' . $ext_key . '[buttonCenter]" value="' . $buttonCenter . '" style=""/>');
		$buttons .= (!isset($buttonRight)?'':
				'<input type="submit" name="' . $ext_key . '[buttonRight]" value="' . $buttonRight . '"/ style="float:right;">');
		return $buttons;
	}

	private function renderSteps($step, $titles) {
		$steps = '<div style="text-align:center;>';
		$steps .= '<a href="index.php?id='.$GLOBALS['TSFE']->id.'">';
		$steps .= '<div class="step"><img src="typo3conf/ext/fsmi_exams/images/one_' .
	         ($step==1 ? 'active' : 'inactive') .
	         '.png"/><b>Startseite</b></div></a>'."\n";

		$steps .= isset($titles[0]) ? '<div class="step"><img src="' . self::kGFX_PATH . 'two_' .
	                            ($step==2 ? 'active' : 'inactive') .
								'.png"/><b>' . $titles[0] . '</b></div>' . "\n" : '';
		$steps .= isset($titles[1]) ? '<div class="step"><img src="' . self::kGFX_PATH . 'three_'.
	                            ($step==3 ? 'active':'inactive').
								'.png"/><b>' . $titles[1] . '</b></div>' . "\n" : '';
		$steps .= isset($titles[2]) ? '<div class="step"><img src="' . self::kGFX_PATH . 'four_' .
	                            ($step==4 ? 'active' : 'inactive') .
								'.png"/><b>' . $titles[2] . '</b></div>'."\n":'';
		$steps .= isset($titles[3]) ? '<div class="step"><img src="' . self::kGFX_PATH . 'five_' .
	                            ($step==5 ? 'active' : 'inactive') .
								'.png"/><b>' . $titles[3] . '</b></div>' . "\n" : '';
		return $steps . "</div>";
	}

	private function renderTitle($title) {
		return '<div id="title">' . $title . '</div>';
	}

	private function renderLentFolderInfo($folderArray, $tableStructure) {
		$infoTable = '';
		$infoTable .= '<table cellpadding="8" cellspacing="2"><tr>';

		foreach( $tableStructure as $value)
			$infoTable .= '<th>' . $this->pi_getLL($value) . '</th>';

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
		$infoTable .= '</table>' . "\n";

		return $infoTable;
	}

	//DB_functions
	private function getLentFolderInfo($folder_id, $tableStructure) {
		//Example array
// 		return array('folder' => '123', 'lender' => 'Studi', 'dispenser' => 'Fachschaftler', 'weight' => '200', 'deposit' => 'Rolex', 'lendingdate' => 'Gestern', 'withdrawal' => 'anderer Fachschaftler', 'withdrawaldate' => 'Heute');
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
		$resLent = $GLOBALS['TYPO3_DB']->sql_query('SELECT * FROM tx_fsmiexams_folder WHERE folder_id = ' . intval($folder_id) . ' AND hidden=0');
		if ($resLent && $res = mysql_fetch_assoc($resLent) && $resLent['state']==self::MODE_LEND) {
			return true;
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