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


		
		
		
		//Variables Array
		$piVars = array();
		

		//Table-Structure
		$lentInfoTable = array(0 => 'folder', 1 => 'lender', 2 => 'dispenser', 3 => 'weight', 4 => 'deposit', 5 => 'lendingdate');
		$withdrawalInfoTable = array(0 => 'folder', 1 => 'lender', 2 => 'dispenser', 3 => 'weight', 4 => 'deposit', 5 => 'lendingdate', 6 => 'withdrawal', 7 => 'withdrawaldate');

        //Important variables //TODO: Escape and set variables
		$piVars['type'] = intval($GETcommands['type']);
		$piVars['mode'] = intval($GETcommands['mode']);
		$piVars['buttonLeft'] = $this->escape($GETcommands['buttonLeft']);
		$piVars['buttonCenter'] = $this->escape($GETcommands['buttonCenter']);
		$piVars['buttonRight'] = $this->escape($GETcommands['buttonRight']);


		$piVars['lender_name'] = $this->escape($GETcommands['lender_name']);
		$piVars['lender_imt'] = $this->escape($GETcommands['lender_imt']);
		$piVars['deposit'] = $this->escape($GETcommands['deposit']);
		$piVars['dispenser'] = $this->escape($GETcommands['dispenser']);

		$piVars['folder_id'] = $this->escape($GETcommands['folder_id']);
		$piVars['folder_weight'] = $this->escape($GETcommands['folder_weight']);
		$piVars['folder_list'] = $GETcommands['folder_list']; // TODO: escaping destroys serializing
		$piVars['folder_list_hash'] = $this->escape($GETcommands['folder_list_hash']);

		$piVars['folder_list_array'] = null;
		
		
		//Deserialize folder list
		if (isset($piVars['folder_list']) && isset($piVars['folder_list_hash']) && (md5($piVars['folder_list'] . self::MAGIC) == $piVars['folder_list_hash']))
		{
		    $piVars['folder_list_array'] = unserialize($piVars['folder_list']);
		}
	    //TODO: serialized arrays as strings contain " - xml attributes use "


		//Listen : <input type="hidden" value="ordner1;ordner2"


		//Style
		$content .= '<style type="text/css">.tx-fsmiexams-pi3 text{font-size:x-large;} .tx-fsmiexams-pi3 form{clear:both; padding-top:50px;} .tx-fsmiexams-pi3 textarea{font-size:x-large; margin-bottom:50px;} .tx-fsmiexams-pi3 input{font-size:large;} .tx-fsmiexams-pi3 img{margin-bottom:5px;} .tx-fsmiexams-pi3 .step{text-align:center; width:80px; display:inline-block; margin:10px 15px;} .tx-fsmiexams-pi3 #title{ color:Gainsboro; text-align:center; font-size:xx-large;} .tx-fsmiexams-pi3 a{text-decoration:none;} .tx-fsmiexams-pi3 table{margin-left:auto; margin-right:auto; font-size:large;} .tx-fsmiexams-pi3 table td{background-color:AliceBlue;} .tx-fsmiexams-pi3 table th{background-color:#B5CDE1;}</style>' . "\n";
		//main_container
		$content .= '<div style="margin:0px 15px; padding-top:15px; text-align:center; width:700px; border:solid 1px #f00;">' . "\n";

		switch ($piVars['type']) {
			case 4: {
				if (isset($piVars['buttonLeft']))
					$type=2;
				else
				if ($piVars['mode'] == self::MODE_WITHDRAW) {
					//Withdrawal Mode

					//Steps
					$content .= $this->renderTitle($this->pi_getLL("withdrawal"));
					$content .= $this->renderSteps(	4, 
													array(0 => $this->pi_getLL("folder"), 
													1 => $this->pi_getLL("withdrawal"), 
													2 => $this->pi_getLL("overview"))
												  );


					//TODO: Add Last Folder to folder_list
					if (!isset($piVars['folder_list_array']))
						$piVars['folder_list_array'] = array();

					//TODO: Withdrawal DB update

					/*** >> Anzeige der ausgeliehenen Ordner + weitere Informationen <<***/

					$piVars['folderInfoArray'] = array();
					foreach ($piVars['folder_list_array'] as $folderInfo) {
						array_push($piVars['folderInfoArray'], $this->getLentFolderInfo($folderInfo, $withdrawalInfoTable));
					}
					$content .= $this->renderLentFolderInfo($piVars['folderInfoArray'], $withdrawalInfoTable);

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
					if (!isset($piVars['folder_list_array']))
						$piVars['folder_list_array'] = array();

					$piVars['folder_list'] = serialize($piVars['folder_list_array']);
					$piVars['folder_list_hash'] = md5($piVars['folder_list'] . self::MAGIC);


					/*** >> Liste der Entliehenen Ordner+Gewichte << ***/
					if (isset($piVars['folder_list'])) {
						//TODO: Render Already Chosen Folders
						$piVars['renderArray'] = array();
						foreach ($piVars['folder_list_array'] as $key => $value)
							array_push($piVars['renderArray'], array('folder' => $key, 'weight' => $value));
						$content .= $this->renderLentFolderInfo($piVars['renderArray'], array(0 => 'folder', 1 => 'weight'));
					}


					//Buttons
					$content .= $this->renderButtons(null, null, null, $this->extKey);

					break;
				}
			}
			case 3: {

				if (isset($piVars['buttonCenter']))
					$type=2;
				else
				if (isset($piVars['buttonLeft']))
					$type=1;
				else
				if ($piVars['mode'] == self::MODE_WITHDRAW) {
					//Withdrawal Mode

					//Steps
					$content .= $this->renderTitle('R&uuml;cknehmen');
					$content .= $this->renderSteps(3, array(0 => 'Ordner', 1 => 'R&uuml;cknahme', 2 => '&Uuml;bersicht'));


					//List
		            //TODO: Add Last Folder to folder_list
					if (!isset($piVars['folder_list_array']))
						$piVars['folder_list_array'] = array();
					if ($piVars['folder_id'] != "") {
						array_push($piVars['folder_list_array'], $piVars['folder_id']); //TODO: does folder exist?
						$piVars['folder_id'] = '';
					}

					$piVars['folder_list'] = serialize($piVars['folder_list_array']);
					$piVars['folder_list_hash'] = md5($piVars['folder_list'] . self::MAGIC);

					$content .= serialize($piVars['folder_list_array']);
					/*** >> Anzeige der ausgeliehenen Ordner + weitere Informationen <<***/
					$piVars['folderInfoArray'] = array();
					foreach ($piVars['folder_list_array'] as $folderInfo) {
						array_push($piVars['folderInfoArray'], $this->getLentFolderInfo($folderInfo, $lentInfoTable));
					}
					$content .= $this->renderLentFolderInfo($piVars['folderInfoArray'], $lentInfoTable);

					//Form
					$content .= '<form method="GET" action="index.php">' . "\n";
					$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id . '"/>' . "\n";
					$content .= '<input type="hidden" name="' . $this->extKey . '[type]" value="4"/>' . "\n";
					$content .= '<input type="hidden" name="' . $this->extKey . '[mode]" value="2"/>' . "\n";


					$content .= (isset($piVars['folder_list']) ? '<input type="hidden" name="' . $this->extKey . '[folder_list]" value=\'' . $piVars['folder_list'] . '\'/>' . "\n" : '');
					$content .= (isset($piVars['folder_list_hash']) ? '<input type="hidden" name="' . $this->extKey . '[folder_list_hash]" value="' . $piVars['folder_list_hash'] . '"/>' . "\n":'');
		
					$content .= '<text><b>R&uuml;cknahme von:</b></text><br/><textarea name="' . $this->extKey . '[withdrawal]"></textarea><br/>' . "\n";

					//Buttons
					$content .= $this->renderButtons(null, null, 'Fertig', $this->extKey);

					break;
				}
				else{
					//Lending Mode

					//Steps
					$content .= $this->renderTitle('Ausleihen');
					$content .= $this->renderSteps(3, array(0 => 'Ordner', 1 => 'Ausleihe', 2 => 'Ausgabe'));



					if (isset($piVars['folder_id']) && isset($piVars['folder_weight'])) {
						//TODO: Add Last Folder to folder_list
						if (!isset($piVars['folder_list_array']))
							$piVars['folder_list_array'] = array();
						$piVars['folder_list_array'][$piVars['folder_id']] = $piVars['folder_weight'];
						$piVars['folder_id'] = '';
						$piVars['folder_weight'] = '';
						$piVars['folder_list'] = serialize($piVars['folder_list_array']);
						$piVars['folder_list_hash'] = md5($piVars['folder_list'] . self::MAGIC);
					}

					/*** >> Liste der "bereits" Entliehenen Ordner+Gewichte ?!?! << ***/

					if (isset($piVars['folder_list'])) {
						//TODO: Render Already Chosen Folders
						$content .= '<text><b>Bereits eingegeben: </b></text><br/>';
						$piVars['renderArray'] = array();
						foreach($piVars['folder_list_array'] as $key => $value)
							array_push($piVars['renderArray'], array('folder' => $key, 'weight' => $value));
						$content .= $this->renderLentFolderInfo($piVars['renderArray'], array(0 => 'folder', 1 => 'weight'));
					}


					$content .= '<form method="GET" action="index.php">' . "\n";
					$content .= '<text><b>Name des Ausleihers: </b></text><br/><textarea name="'.$this->extKey.'[lender_name]" cols="30" rows="1">' .
						(isset($piVars['lender_name']) ? $piVars['lender_name'] : '') . '</textarea><br/>' . "\n";
					$content .= '<text><b>IMT-Login des Ausleihers: </b></text><br/><textarea name="' . $this->extKey . '[lender_imt]" cols="30" rows="1">' .
						(isset($piVars['lender_imt']) ? $piVars['lender_imt'] : '') . '</textarea><br/>' . "\n";
					$content .= '<text><b>Pfand: </b></text><br/><textarea name="' . $this->extKey . '[deposit]" cols="30" rows="1">' .
						(isset($piVars['deposit']) ? $piVars['deposit'] : '') . '</textarea><br/>' . "\n";
					$content .= '<hr/><br/>' . "\n";
					$content .= '<text><b>Name des Ausgebers: </b></text><br/><textarea name="' . $this->extKey . '[dispenser]" cols="30" rows="1">' .
					(isset($piVars['dispenser']) ? $piVars['dispenser'] : '') . '</textarea>' . "\n";
					$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id.'"/>' . "\n";
					$content .= '<input type="hidden" name="' . $this->extKey . '[type]" value="4"/>' . "\n";
					$content .= '<input type="hidden" name="' . $this->extKey . '[folder_list]" value=\'' . $piVars['folder_list'] . '\'/>' . "\n";
					$content .= '<input type="hidden" name="' . $this->extKey . '[folder_list_hash]" value="' . $piVars['folder_list_hash'] . '"/>' . "\n";
					//Buttons
					$content .= $this->renderButtons('Zur&uuml;ck', null, 'Weiter', $this->extKey);

					break;
				}


			}
			case 2: {

				if ($this->isLent($piVars['folder_id'])) {
					//Withdrawal Mode

					//TODO: Liste von zurÃ¼ckgenommenen Ordnern erstellen (wie folder_list)


					//Steps
					$content .= $this->renderTitle('R&uuml;cknehmen');
					$content .= $this->renderSteps(2, array(0 => 'Ordner', 1 => 'RÃ¼cknahme' ,2 => '&Uuml;bersicht'));


					$content .= 'blaaa' . serialize($piVars['folder_list_array']);
					//List
		            //TODO: Add Last Folder to folder_list
					if (!isset($piVars['folder_list_array']))
						$piVars['folder_list_array'] = array();
						
					if ($piVars['folder_id'] != "") {
						array_push($piVars['folder_list_array'], $piVars['folder_id']); //TODO: does folder exist?
						$piVars['folder_id'] = '';
					}
					
					$piVars['folder_list'] = serialize($piVars['folder_list_array']);
					$piVars['folder_list_hash'] = md5($piVars['folder_list'] . self::MAGIC);
	
					$content .= serialize($piVars['folder_list_array']);
					/*** >> Anzeige der ausgeliehenen Ordner + weitere Informationen <<***/
					$piVars['folderInfoArray'] = array();
					foreach($piVars['folder_list_array'] as $folderInfo)
						array_push($piVars['folderInfoArray'], $this->getLentFolderInfo($folderInfo, $lentInfoTable));

					$content .= $this->renderLentFolderInfo($piVars['folderInfoArray'], $lentInfoTable);

					//Form
					$content .= '<form method="GET" action="index.php">' . "\n";
					$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id . '"/>' . "\n";
					$content .= '<input type="hidden" name="' . $this->extKey . '[type]" value="3"/>' . "\n";
					$content .= '<input type="hidden" name="' . $this->extKey . '[mode]" value="2"/>' . "\n";


					$content .= (isset($piVars['folder_list']) ? '<input type="hidden" name="' . $this->extKey . '[folder_list]" value=\'' . $piVars['folder_list'] . '\'/>' . "\n" : '');
					$content .= (isset($piVars['folder_list_hash']) ? '<input type="hidden" name="' . $this->extKey . '[folder_list_hash]" value="' . $piVars['folder_list_hash'] . '"/>' . "\n" : '');
	
					$content .= '<text><b>Ordner:</b></text><br/><textarea name="' . $this->extKey . '[folder_id]">' . $piVars['folder_id'] . '</textarea><br/>' . "\n";

					//Buttons
					$content .= $this->renderButtons(null, 'Wiederholen', 'Weiter', $this->extKey);

					break;
				} else { 
					//Lending Mode

					//Steps
					$content .= $this->renderTitle('Ausleihen');
					$content .= $this->renderSteps(2, array(0 => 'Ordner', 1 => 'Ausleihe', 2 => 'Ausgabe'));



					if (isset($piVars['folder_id']) && isset($piVars['folder_weight'])) {
						//TODO: Add Last Folder to folder_list
						if(!isset($piVars['folder_list_array']))
							$piVars['folder_list_array'] = array();
						$piVars['folder_list_array'][$piVars['folder_id']] = $piVars['folder_weight'];
						$piVars['folder_id'] = '';
						$piVars['folder_weight'] = '';
						$piVars['folder_list'] = serialize($piVars['folder_list_array']);
						$piVars['folder_list_hash'] = md5($piVars['folder_list'] . self::MAGIC);
					}

					if(isset($piVars['folder_list_array']))
					{
						//TODO: Render Already Chosen Folders
						$content .= '<text><b>Bereits eingegeben: </b></text><br/>';
						$piVars['renderArray'] = array();
						foreach($piVars['folder_list_array'] as $key => $value)
							array_push($piVars['renderArray'], array('folder' => $key, 'weight' => $value));
						$content .= $this->renderLentFolderInfo($piVars['renderArray'], array(0 => 'folder', 1 => 'weight'));
					}



					$content .= '<form method="GET" action="index.php">' . "\n";
					$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id . '"/>' . "\n";
					$content .= '<input type="hidden" name="' . $this->extKey . '[type]" value="3"/>' . "\n";
					$content .= '<input type="hidden" name="' . $this->extKey . '[mode]" value="1"/>' . "\n";

					$content .= (isset($piVars['folder_list']) ? '<input type="hidden" name="' . $this->extKey . '[folder_list]" value=\'' . $piVars['folder_list'] . '\'/>'."\n":'');
					$content .= (isset($piVars['folder_list_hash']) ? '<input type="hidden" name="' . $this->extKey . '[folder_list_hash]" value="' . $piVars['folder_list_hash'] . '"/>'."\n":'');
					$content .= (isset($piVars['lender_name']) ? '<input type="hidden" name="' . $this->extKey . '[lender_name]" value="' . $piVars['lender_name'] . '"/>' . "\n" : '');
					$content .= (isset($piVars['lender_imt']) ? '<input type="hidden" name="' . $this->extKey . '[lender_imt]" value="' . $piVars['lender_imt'] . '"/>' . "\n" : '');
					$content .= (isset($piVars['deposit']) ? '<input type="hidden" name="' . $this->extKey . '[deposit]" value="' . $piVars['deposit'] . '"/>' . "\n" : '');
					$content .= (isset($piVars['dispenser']) ? '<input type="hidden" name="' . $this->extKey . '[dispenser]" value="' . $piVars['dispenser'] . '"/>' . "\n" : '');

					$content .= '<text><b>Ordner:</b></text><br/><textarea name="' . $this->extKey . '[folder_id]">' . $piVars['folder_id'] . '</textarea><br/>' . "\n";
					$content .= '<text><b>Gewicht des Ordners:</b></text><br/><textarea name="' . $this->extKey . '[folder_weight]" cols="30" rows="3"></textarea>' . "\n";


					//Buttons
					$content .= $this->renderButtons(null, 'Wiederholen', 'Weiter', $this->extKey);
				}

				break;
			};
			
		    default: {

				//Steps
				$content .= $this->renderTitle('FSMI-Ausleihtool');
				$content .= $this->renderSteps(1, array());


				$content .= '<form method="GET" action="index.php">' . "\n";
				$content .= '<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id . '"/>';
				$content .= '<input type="hidden" name="' . $this->extKey . '[type]" value="2"/>';
				$content .= '<text><b>Ordnereingabe</b></text><br/><textarea name="' . $this->extKey . '[folder_id]" cols="30" rows="3"></textarea>';

				//Buttons
				$content .= $this->renderButtons(null, null, 'Weiter', $this->extKey);
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
		$steps = '<a href="index.php?id='.$GLOBALS['TSFE']->id.'">';
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
		return $steps . "<br/>";
	}

	private function renderTitle($title) {
		return '<div id="title">' . $title . '</div>';
	}

	private function renderLentFolderInfo($folderArray, $tableStructure) {
		$infoTable = '';
		$infoTable .= '<table cellpadding="8" cellspacing="2"><tr>';

		foreach( $tableStructure as $value)
			$infoTable .= '<th>' . $this->LANG->getLL(self::PREFIX . '.' . $value) . '</th>';

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

		$resLent = $GLOBALS['TYPO3_DB']->sql_query('SELECT ' . $query . ' FROM tx_fsmiexams_loan WHERE folder = ' . $folder_id . ' AND hidden=0');
		while ($resLent && $res = mysql_fetch_assoc($resLent)) {
			foreach ($tableStructure as $field)
				$retArray[$field] = $res[$field];
			return $retArray;
		}
		return $retArray;
	}

	private function isLent($folder_id) {
		//TODO:
		return true;
		$resLent = $GLOBALS['TYPO3_DB']->sql_query('SELECT COUNT(folder) FROM tx_fsmiexams_loan WHERE folder = ' . $folder_id . ' AND hidden=0');
		while ($resLent && $res = mysql_fetch_assoc($resLent)) {
			if ($res['COUNT(folder)'] != "0")
				return true;
		}
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