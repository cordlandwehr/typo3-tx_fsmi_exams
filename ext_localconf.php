<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

t3lib_extMgm::addPItoST43($_EXTKEY, 'controller/class.tx_fsmiexams_controller_browse.php', '_browse', 'list_type', 0);


t3lib_extMgm::addPItoST43($_EXTKEY, 'controller/class.tx_fsmiexams_controller_clerk.php', '_clerk', 'list_type', 0);


t3lib_extMgm::addPItoST43($_EXTKEY, 'controller/class.tx_fsmiexams_controller_admin.php', '_admin', 'list_type', 0);
?>