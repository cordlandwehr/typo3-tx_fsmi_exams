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
		
		//Translation
		$trans = array("folder" => "Ordner","lender" => "Ausleiher","dispenser" => "Ausgeber","lenderlogin" => "Ausleiher Login","weight" => "Gewicht (g)","withdrawal" => "Rücknahme von","withdrawaldate" => "Rücknahme Datum","deposit" => "Pfand","lendingdate" => "Ausleih-Datum");
		//Table-Structure
		$lentInfoTable = array(0 => "folder",1 => "lender",2 => "dispenser",3 => "weight",4 => "deposit",5 => "lendingdate");
		$withdrawalInfoTable = array(0 => "folder",1 => "lender",2 => "dispenser",3 => "weight",4 => "deposit",5 => "lendingdate",6 => "withdrawal",7 => "withdrawaldate");
		
        //Important variables //TODO: Escape and set variables
		$type = intval($GETcommands['type']);
		$mode = intval($GETcommands['mode']);
		$button = $GETcommands['button'];
		
		
		$lender_name = $GETcommands['lender_name'];
		$lender_imt = $GETcommands['lender_imt'];
		$deposit = $GETcommands['deposit'];
		$dispenser = $GETcommands['dispenser'];

		$folder_id = $GETcommands['folder_id'];
		$folder_weight = $GETcommands['folder_weight'];
		$folder_list = $GETcommands['folder_list'];
		$folder_list_hash = $GETcommands['folder_list_hash'];
		
		$folder_list_array = null;
		//Deserialize folder list
		if(isset($folder_list) && isset($folder_list_hash) && (md5($folder_list.'magic') == $folder_list_hash))
		    $folder_list_array = unserialize($folder_list);
	    //TODO: serialized arrays as strings contain " - xml attributes use "

		
		//Listen : <input type="hidden" value="ordner1;ordner2"
		
		
		//Style
		$content .= '<style type="text/css">.tx-fsmiexams-pi3 text{font-size:x-large;} .tx-fsmiexams-pi3 form{clear:both; padding-top:50px;} .tx-fsmiexams-pi3 textarea{font-size:x-large; margin-bottom:50px;} .tx-fsmiexams-pi3 input{font-size:large;} .tx-fsmiexams-pi3 img{margin-bottom:5px;} .tx-fsmiexams-pi3 .step{text-align:center; width:80px; display:inline-block; margin:10px 15px;} .tx-fsmiexams-pi3 #title{ color:Gainsboro; text-align:center; font-size:xx-large;} .tx-fsmiexams-pi3 a{text-decoration:none;} .tx-fsmiexams-pi3 table{margin-left:auto; margin-right:auto; font-size:large;} .tx-fsmiexams-pi3 table td{background-color:AliceBlue;} .tx-fsmiexams-pi3 table th{background-color:#B5CDE1;}</style>'."\n";
		//main_container
		$content .= '<div style="margin:0px 15px; padding-top:15px; text-align:center; width:700px; border:solid 1px #f00;">'."\n";
		
		switch($type)
		{
		  case 4:{
		  
		    if($button == "Zurück")
			  $type=2;
			else
			if($mode == '2')
			{
              //Withdrawal Mode
			  
			  //Steps
			  $content .= renderTitle("Rücknehmen");
			  $content .= renderSteps(4, array(0 => "Ordner",1 => "Rücknahme" ,2 => "Übersicht"));
			  
			  
				  //TODO: Add Last Folder to folder_list
				  if(!isset($folder_list_array))
				    $folder_list_array = array();
					
                  //TODO: Withdrawal DB update
				  
			  /*** >> Anzeige der ausgeliehenen Ordner + weitere Informationen <<***/
			  
			  $folderInfoArray = array();
			  foreach($folder_list_array as $folderInfo)
			  {
		        array_push($folderInfoArray, getLentFolderInfo($folderInfo, $withdrawalInfoTable));
		      }
		      $content .= renderLentFolderInfo($folderInfoArray, $withdrawalInfoTable, $trans);
			  
		      //Buttons
			  $content .= renderButtons(null, null, null, $this->extKey);
		  
		      break;				
			}
			else
			{
			  //Lending Mode
		      //Steps
			  $content .= renderTitle("Ausleihen");
              $content .= renderSteps(4, array(0 => "Ordner", 1 => "Ausleihe", 2 => "Ausgabe"));
		      
			  
		      //TODO: Finally write entrys to DB
				  //TODO: Add Last Folder to folder_list
				  if(!isset($folder_list_array))
				    $folder_list_array = array();

				  $folder_list = serialize($folder_list_array);
				  $folder_list_hash = md5($folder_list.'magic');

				  
              /*** >> Liste der Entliehenen Ordner+Gewichte << ***/
		          if(isset($folder_list))
			      {
  			        //TODO: Render Already Chosen Folders
				    $renderArray = array();
				    foreach($folder_list_array as $key => $value)
				      array_push($renderArray, array("folder"=>$key,"weight"=>$value));
				    $content .= renderLentFolderInfo($renderArray,array(0=>"folder",1=>"weight"),array("folder"=>"Ordner","weight"=>"Gewicht (g)"));
			      }
              

		        //Buttons
			    $content .= renderButtons(null, null, null, $this->extKey);
		  
		      break;
			}
		  }
		  case 3:{
		  
            if($button == "Wiederholen")
			  $type=2;
			else
			if($button == "Zurück")
			  $type=1;
			else
			if($mode == '2')
			{ 
			  //Withdrawal Mode
			  
		      //Steps
			  $content .= renderTitle("Rücknehmen");
              $content .= renderSteps(3, array(0 => "Ordner",1 => "Rücknahme" ,2 => "Übersicht"));
			  

			  //List
		               //TODO: Add Last Folder to folder_list
			  if(!isset($folder_list_array))
				$folder_list_array = array();
			  if($folder_id != "")
			  {
			    array_push($folder_list_array, $folder_id); //TODO: does folder exist?
				$folder_id = '';
			  }
			  
			  $folder_list = serialize($folder_list_array);
			  $folder_list_hash = md5($folder_list.'magic');
			  
		      $content .= serialize($folder_list_array);
			  /*** >> Anzeige der ausgeliehenen Ordner + weitere Informationen <<***/
			  $folderInfoArray = array();
			  foreach($folder_list_array as $folderInfo)
			  {
		        array_push($folderInfoArray, getLentFolderInfo($folderInfo, $lentInfoTable));
		      }
		      $content .= renderLentFolderInfo($folderInfoArray, $lentInfoTable, $trans);

			  //Form
			  $content .= '<form method="GET" action="index.php">'."\n";
			  $content .= '<input type="hidden" name="id" value="'.$GLOBALS['TSFE']->id.'"/>'."\n";
			  $content .= '<input type="hidden" name="'.$this->extKey.'[type]" value="4"/>'."\n";			  
			  $content .= '<input type="hidden" name="'.$this->extKey.'[mode]" value="2"/>'."\n";
			  
			  
			  $content .= (isset($folder_list)?'<input type="hidden" name="'.$this->extKey.'[folder_list]" value=\''.$folder_list.'\'/>'."\n":'');
			  $content .= (isset($folder_list_hash)?'<input type="hidden" name="'.$this->extKey.'[folder_list_hash]" value="'.$folder_list_hash.'"/>'."\n":'');
			  
              $content .= '<text><b>Rücknahme von:</b></text><br/><textarea name="'.$this->extKey.'[withdrawal]"></textarea><br/>'."\n";			  
			  
              //Buttons
			  $content .= renderButtons(null, "Wiederholen", "Fertig", $this->extKey);			  
			  
			  break;			  

  		    }
			else
			{
			  //Lending Mode
			  
		      //Steps
			  $content .= renderTitle("Ausleihen");
              $content .= renderSteps(3, array(0 => "Ordner", 1 => "Ausleihe", 2 => "Ausgabe"));
		      
			  

			    if(isset($folder_id) && isset($folder_weight))
				{
				  //TODO: Add Last Folder to folder_list
				  if(!isset($folder_list_array))
				    $folder_list_array = array();
				  $folder_list_array[$folder_id] = $folder_weight;
				  $folder_id = '';
				  $folder_weight = '';
				  $folder_list = serialize($folder_list_array);
				  $folder_list_hash = md5($folder_list.'magic');
				}				
			
			  /*** >> Liste der "bereits" Entliehenen Ordner+Gewichte ?!?! << ***/
			
			    if(isset($folder_list))
			    {
  			      //TODO: Render Already Chosen Folders
				  $content .= '<text><b>Bereits eingegeben: </b></text><br/>';
				  $renderArray = array();
				  foreach($folder_list_array as $key => $value)
				    array_push($renderArray, array("folder"=>$key,"weight"=>$value));
				  $content .= renderLentFolderInfo($renderArray,array(0=>"folder",1=>"weight"),array("folder"=>"Ordner","weight"=>"Gewicht (g)"));
			    }


		      $content .= '<form method="GET" action="index.php">'."\n";
			  $content .= '<text><b>Name des Ausleihers: </b></text><br/><textarea name="'.$this->extKey.'[lender_name]" cols="30" rows="1">'.(isset($lender_name)?$lender_name:'').'</textarea><br/>'."\n";
			  $content .= '<text><b>IMT-Login des Ausleihers: </b></text><br/><textarea name="'.$this->extKey.'[lender_imt]" cols="30" rows="1">'.(isset($lender_imt)?$lender_imt:'').'</textarea><br/>'."\n";
			  $content .= '<text><b>Pfand: </b></text><br/><textarea name="'.$this->extKey.'[deposit]" cols="30" rows="1">'.(isset($deposit)?$deposit:'').'</textarea><br/>'."\n";
			  $content .= '<hr/><br/>'."\n";
			  $content .= '<text><b>Name des Ausgebers: </b></text><br/><textarea name="'.$this->extKey.'[dispenser]" cols="30" rows="1">'.(isset($dispenser)?$dispenser:'').'</textarea>'."\n";
			  $content .= '<input type="hidden" name="id" value="'.$GLOBALS['TSFE']->id.'"/>'."\n";
			  $content .= '<input type="hidden" name="'.$this->extKey.'[type]" value="4"/>'."\n";
			  $content .= '<input type="hidden" name="'.$this->extKey.'[folder_list]" value=\''.$folder_list.'\'/>'."\n";
              $content .= '<input type="hidden" name="'.$this->extKey.'[folder_list_hash]" value="'.$folder_list_hash.'"/>'."\n";
		      //Buttons
			  $content .= renderButtons("Zurück", null, "Weiter", $this->extKey);
		  
		      break;
			}
			
			
		  }
		  case 2: {
		  
		    if(isLent($folder_id))
			{ 
			  //Withdrawal Mode
			  
			  //TODO: Liste von zurückgenommenen Ordnern erstellen (wie folder_list)
			  
			  
		      //Steps
			  $content .= renderTitle("Rücknehmen");
              $content .= renderSteps(2, array(0 => "Ordner",1 => "Rücknahme" ,2 => "Übersicht"));
			  
			  
			  //List
		               //TODO: Add Last Folder to folder_list
			  if(!isset($folder_list_array))
				$folder_list_array = array();
			  if($folder_id != "")
			  {
			    array_push($folder_list_array, $folder_id); //TODO: does folder exist?
			    $folder_id = '';
			  }
			  $folder_list = serialize($folder_list_array);
			  $folder_list_hash = md5($folder_list.'magic');
			  
		      $content .= serialize($folder_list_array);
			  /*** >> Anzeige der ausgeliehenen Ordner + weitere Informationen <<***/
			  $folderInfoArray = array();
			  foreach($folder_list_array as $folderInfo)
			  {
		        array_push($folderInfoArray, getLentFolderInfo($folderInfo, $lentInfoTable));
		      }
			  
		      $content .= renderLentFolderInfo($folderInfoArray, $lentInfoTable, $trans);
			  
			  //Form
			  $content .= '<form method="GET" action="index.php">'."\n";
			  $content .= '<input type="hidden" name="id" value="'.$GLOBALS['TSFE']->id.'"/>'."\n";
			  $content .= '<input type="hidden" name="'.$this->extKey.'[type]" value="3"/>'."\n";			  
			  $content .= '<input type="hidden" name="'.$this->extKey.'[mode]" value="2"/>'."\n";
			  
			  
			  $content .= (isset($folder_list)?'<input type="hidden" name="'.$this->extKey.'[folder_list]" value=\''.$folder_list.'\'/>'."\n":'');
			  $content .= (isset($folder_list_hash)?'<input type="hidden" name="'.$this->extKey.'[folder_list_hash]" value="'.$folder_list_hash.'"/>'."\n":'');
			  
              $content .= '<text><b>Ordner:</b></text><br/><textarea name="'.$this->extKey.'[folder_id]">'.$folder_id.'</textarea><br/>'."\n";			  
			  
              //Buttons
			  $content .= renderButtons(null, "Wiederholen", "Weiter", $this->extKey);			  
			  
			  break;
			}
            else
			{ //Lending Mode

		      //Steps
			  $content .= renderTitle("Ausleihen");
              $content .= renderSteps(2, array(0 => "Ordner", 1 => "Ausleihe", 2 => "Ausgabe"));
			  
			  
			
			    if(isset($folder_id) && isset($folder_weight))
				{
				  //TODO: Add Last Folder to folder_list
				  if(!isset($folder_list_array))
				    $folder_list_array = array();
				  $folder_list_array[$folder_id] = $folder_weight;
				  $folder_id = '';
				  $folder_weight = '';
				  $folder_list = serialize($folder_list_array);
				  $folder_list_hash = md5($folder_list.'magic');
				}
			
			    if(isset($folder_list_array))
			    {
  			      //TODO: Render Already Chosen Folders
				  $content .= '<text><b>Bereits eingegeben: </b></text><br/>';
				  $renderArray = array();
				  foreach($folder_list_array as $key => $value)
				    array_push($renderArray, array("folder"=>$key,"weight"=>$value));
				  $content .= renderLentFolderInfo($renderArray,array(0=>"folder",1=>"weight"),array("folder"=>"Ordner","weight"=>"Gewicht (g)"));
			    }
				
				
			  
		      $content .= '<form method="GET" action="index.php">'."\n";
			  $content .= '<input type="hidden" name="id" value="'.$GLOBALS['TSFE']->id.'"/>'."\n";
			  $content .= '<input type="hidden" name="'.$this->extKey.'[type]" value="3"/>'."\n";
              $content .= '<input type="hidden" name="'.$this->extKey.'[mode]" value="1"/>'."\n";
			  
			  $content .= (isset($folder_list)?'<input type="hidden" name="'.$this->extKey.'[folder_list]" value=\''.$folder_list.'\'/>'."\n":'');
			  $content .= (isset($folder_list_hash)?'<input type="hidden" name="'.$this->extKey.'[folder_list_hash]" value="'.$folder_list_hash.'"/>'."\n":'');
			  $content .= (isset($lender_name)?'<input type="hidden" name="'.$this->extKey.'[lender_name]" value="'.$lender_name.'"/>'."\n":'');
			  $content .= (isset($lender_imt)?'<input type="hidden" name="'.$this->extKey.'[lender_imt]" value="'.$lender_imt.'"/>'."\n":'');
			  $content .= (isset($deposit)?'<input type="hidden" name="'.$this->extKey.'[deposit]" value="'.$deposit.'"/>'."\n":'');
			  $content .= (isset($dispenser)?'<input type="hidden" name="'.$this->extKey.'[dispenser]" value="'.$dispenser.'"/>'."\n":'');
			  
			  $content .= '<text><b>Ordner:</b></text><br/><textarea name="'.$this->extKey.'[folder_id]">'.$folder_id.'</textarea><br/>'."\n";
			  $content .= '<text><b>Gewicht des Ordners:</b></text><br/><textarea name="'.$this->extKey.'[folder_weight]" cols="30" rows="3"></textarea>'."\n";

			
              //Buttons
			  $content .= renderButtons(null, "Wiederholen", "Weiter", $this->extKey);
			  
			}
  
		    break;
		  };
		  default: {
		  
		    //Steps
			$content .= renderTitle("FSMI-Ausleihtool");
            $content .= renderSteps(1, array());
			
		    
		    $content .= '<form method="GET" action="index.php">'."\n";
			$content .= '<input type="hidden" name="id" value="'.$GLOBALS['TSFE']->id.'"/>';
			$content .= '<input type="hidden" name="'.$this->extKey.'[type]" value="2"/>';
			$content .= '<text><b>Ordnereingabe</b></text><br/><textarea name="'.$this->extKey.'[folder_id]" cols="30" rows="3"></textarea>';
			
			//Buttons
			$content .= renderButtons(null, null, "Weiter", $this->extKey);
			
			
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
			<label index="tx_fsmiexams_loan.withdrawal">R�ckname von</label>
			<label index="tx_fsmiexams_loan.withdrawaldate">R�cknahme Datum</label>
			<label index="tx_fsmiexams_loan.deposit">Pfand</label>
			<label index="tx_fsmiexams_loan.lendingdate">Ausleihe Datum</label>
		*/
		
		
//static t3lib_div::cmpIP


		return $this->pi_wrapInBaseClass($content);
	}
}

function renderButtons($buttonLeft, $buttonCenter, $buttonRight, $ext_key)
{
    $buttons = '<br/>';
	$buttons .= (!isset($buttonLeft)? '': 
	            '<input type="submit" name="'.$ext_key.'[button]" value="'.$buttonLeft.'" style="float:left;"/>');
	$buttons .= (!isset($buttonCenter)?'': 
	            '<input type="submit" name="'.$ext_key.'[button]" value="'.$buttonCenter.'" style=""/>');
	$buttons .= (!isset($buttonRight)?'':
	            '<input type="submit" name="'.$ext_key.'[button]" value="'.$buttonRight.'"/ style="float:right;">');
	return $buttons;
}

function renderSteps($step, $titles)
{
    $steps = '<a href="index.php?id='.$GLOBALS['TSFE']->id.'">';
    $steps .= '<div class="step"><img src="typo3conf/ext/fsmi_exams/images/one_'.
	         ($step==1?'active':'inactive').
	         '.png"/><b>Startseite</b></div></a>'."\n";
			 
    $steps .= isset($titles[0])?'<div class="step"><img src="typo3conf/ext/fsmi_exams/images/two_'.
	                            ($step==2?'active':'inactive').
								'.png"/><b>'.$titles[0].'</b></div>'."\n":'';
    $steps .= isset($titles[1])?'<div class="step"><img src="typo3conf/ext/fsmi_exams/images/three_'.
	                            ($step==3?'active':'inactive').
								'.png"/><b>'.$titles[1].'</b></div>'."\n":'';
    $steps .= isset($titles[2])?'<div class="step"><img src="typo3conf/ext/fsmi_exams/images/four_'.
	                            ($step==4?'active':'inactive').
								'.png"/><b>'.$titles[2].'</b></div>'."\n":'';
	$steps .= isset($titles[3])?'<div class="step"><img src="typo3conf/ext/fsmi_exams/images/five_'.
	                            ($step==5?'active':'inactive').
								'.png"/><b>'.$titles[3].'</b></div>'."\n":'';
  return $steps."<br/>";
}

function renderTitle($title)
{
  return '<div id="title">'.$title.'</div>';
}

function renderLentFolderInfo($folderArray, $tableStructure, $trans)
{
  $infoTable = '';
  $infoTable .= '<table cellpadding="8" cellspacing="2"><tr>';
  
  for($i=0;$i<count($tableStructure);$i++)
    $infoTable .= '<th>'.$trans[$tableStructure[$i]].'</th>';
  
  $infoTable .= '</tr>'."\n";
  
  foreach($folderArray as $folderRow)
  {
    if(isset($folderRow))
	{
	  $infoTable .= '<tr>';
	  for($i=0;$i<count($tableStructure);$i++)
	    $infoTable .= '<td>'.$folderRow[$tableStructure[$i]].'</td>';
	  $infoTable .= '</tr>';
	}
  }
  $infoTable .= '</table>'."\n";
  
  return $infoTable;
}

//DB_functions
function getLentFolderInfo($folder_id, $tableStructure)
{
  //Example array
  return array("folder" => "123","lender" => "Studi","dispenser" => "Fachschaftler","weight" => "200","deposit" => "Rolex","lendingdate" => "Gestern","withdrawal" => "anderer Fachschaftler","withdrawaldate" => "Heute");
  $retArray = array();
  $query = '';
  foreach($tableStructure as $field)
    $query .= ($query==''?'':',').$field;
	
  $resLent = $GLOBALS['TYPO3_DB']->sql_query('SELECT '.$query.' FROM tx_fsmiexams_loan WHERE folder = '.$folder_id.' AND hidden=0');
  while($resLent && $res = mysql_fetch_assoc($resLent))
  {
    foreach($tableStructure as $field)
	  $retArray[$field] = $res[$field];
	return $retArray;
  }
  return $retArray;
}

function isLent($folder_id)
{
  //TODO:
  //return true;
  $resLent = $GLOBALS['TYPO3_DB']->sql_query('SELECT COUNT(folder) FROM tx_fsmiexams_loan WHERE folder = '.$folder_id.' AND hidden=0');
  while($resLent && $res = mysql_fetch_assoc($resLent))
  {
    if($res['COUNT(folder)'] != "0")
	  return true;
  }
  return false;
}

function isEasterEgg($folder_id)
{
  //TODO:
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/pi3/class.tx_fsmiexams_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fsmi_exams/pi3/class.tx_fsmiexams_pi3.php']);
}

?>