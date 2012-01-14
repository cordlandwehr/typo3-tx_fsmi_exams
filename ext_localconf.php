<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_fsmiexams_pi1.php', '_pi1', 'list_type', 0);


t3lib_extMgm::addPItoST43($_EXTKEY, 'controller/class.tx_fsmiexams_controller_clerk.php', '_clerk', 'list_type', 0);


t3lib_extMgm::addPItoST43($_EXTKEY, 'pi4/class.tx_fsmiexams_pi4.php', '_pi4', 'list_type', 0);
?>