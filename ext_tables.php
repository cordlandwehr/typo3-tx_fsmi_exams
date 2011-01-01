<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
$TCA['tx_fsmiexams_degreeprogram'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_degreeprogram',
		'label'     => 'name',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'default_sortby' => 'ORDER BY name',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_fsmiexams_degreeprogram.gif',
	),
);

$TCA['tx_fsmiexams_field'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_field',
		'label'     => 'name',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'default_sortby' => 'ORDER BY crdate',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_fsmiexams_field.gif',
	),
);

$TCA['tx_fsmiexams_module'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_module',
		'label'     => 'name',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'default_sortby' => 'ORDER BY crdate',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_fsmiexams_module.gif',
	),
);

$TCA['tx_fsmiexams_lecture'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_lecture',
		'label'     => 'name',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'default_sortby' => 'ORDER BY crdate',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_fsmiexams_lecture.gif',
	),
);

$TCA['tx_fsmiexams_exam'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam',
		'label'     => 'name',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'default_sortby' => 'ORDER BY crdate',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_fsmiexams_exam.gif',
	),
);

$TCA['tx_fsmiexams_lecturer'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_lecturer',
		'label'     => 'lastname',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_fsmiexams_lecturer.gif',
	),
);

$TCA['tx_fsmiexams_examtype'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_examtype',
		'label'     => 'description',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_fsmiexams_examtype.gif',
	),
);

$TCA['tx_fsmiexams_folder'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_folder',
		'label'     => 'name',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'default_sortby' => 'ORDER BY crdate',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_fsmiexams_folder.gif',
	),
);


$TCA['tx_fsmiexams_loan'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_loan',
		'label'     => 'uid',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'default_sortby' => 'ORDER BY crdate',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_fsmiexams_loan.gif',
	),
);


// add extension fields to frontend user groups
$columnRightsEdit = array (
    'tx_fsmiexams_fsmiexams_rights_edit' => array (
		'exclude' => 0,
		'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams.fsmiexams_rights_edit',
		'config'  => array (
			'type'    => 'check',
			'default' => '0'
		)
    ),
);
$columnRightsDownload = array (
    'tx_fsmiexams_fsmiexams_rights_download' => array (
		'exclude' => 0,
		'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams.fsmiexams_rights_download',
		'config'  => array (
			'type'    => 'check',
			'default' => '0'
		)
    ),
);
$columnRightsPrint = array (
    'tx_fsmiexams_fsmiexams_rights_print' => array (
		'exclude' => 0,
		'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams.fsmiexams_rights_print',
		'config'  => array (
			'type'    => 'check',
			'default' => '0'
		)
    ),
);
t3lib_div::loadTCA('fe_groups');
t3lib_extMgm::addTCAcolumns('fe_groups',$columnRightsEdit,1);
t3lib_extMgm::addTCAcolumns('fe_groups',$columnRightsDownload,1);
t3lib_extMgm::addTCAcolumns('fe_groups',$columnRightsPrint,1);
t3lib_extMgm::addToAllTCAtypes('fe_groups','tx_fsmiexams_fsmiexams_rights_edit;;;;1-1-1');
t3lib_extMgm::addToAllTCAtypes('fe_groups','tx_fsmiexams_fsmiexams_rights_download;;;;1-1-1');
t3lib_extMgm::addToAllTCAtypes('fe_groups','tx_fsmiexams_fsmiexams_rights_print;;;;1-1-1');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(array(
	'LLL:EXT:fsmi_exams/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:fsmi_exams/flexform/flexform_pi1.xml');


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key';


t3lib_extMgm::addPlugin(array(
	'LLL:EXT:fsmi_exams/locallang_db.xml:tt_content.list_type_pi2',
	$_EXTKEY . '_pi2',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi3']='layout,select_key';


t3lib_extMgm::addPlugin(array(
	'LLL:EXT:fsmi_exams/locallang_db.xml:tt_content.list_type_pi3',
	$_EXTKEY . '_pi3',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

/* Here starts pi4 */
t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi4']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi4']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi4', 'FILE:EXT:fsmi_exams/flexform/flexform_pi4.xml');
t3lib_extMgm::addPlugin(array(
	'LLL:EXT:fsmi_exams/locallang_db.xml:tt_content.list_type_pi4',
	$_EXTKEY . '_pi4',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

t3lib_extMgm::addStaticFile($_EXTKEY,"static/css/","CSS Style");

?>