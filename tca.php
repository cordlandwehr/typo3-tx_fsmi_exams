<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_fsmiexams_degreeprogram'] = array (
	'ctrl' => $TCA['tx_fsmiexams_degreeprogram']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l10n_parent,l10n_diffsource,hidden,name'
	),
	'feInterface' => $TCA['tx_fsmiexams_degreeprogram']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l10n_parent' => array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_fsmiexams_degreeprogram',
				'foreign_table_where' => 'AND tx_fsmiexams_degreeprogram.pid=###CURRENT_PID### AND tx_fsmiexams_degreeprogram.sys_language_uid IN (-1,0)',
			)
		),
		'l10n_diffsource' => array (
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'name' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_degreeprogram.name',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max' => '64',
				'eval' => 'trim',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, name')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);



$TCA['tx_fsmiexams_field'] = array (
	'ctrl' => $TCA['tx_fsmiexams_field']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l10n_parent,l10n_diffsource,hidden,name,degreeprogram'
	),
	'feInterface' => $TCA['tx_fsmiexams_field']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l10n_parent' => array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_fsmiexams_field',
				'foreign_table_where' => 'AND tx_fsmiexams_field.pid=###CURRENT_PID### AND tx_fsmiexams_field.sys_language_uid IN (-1,0)',
			)
		),
		'l10n_diffsource' => array (
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'name' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_field.name',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max' => '64',
				'eval' => 'required,trim',
			)
		),
		'degreeprogram' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_field.degreeprogram',
			'config' => array (
				'type' => 'select',
				'foreign_table' => 'tx_fsmiexams_degreeprogram',
				'foreign_table_where' => 'ORDER BY name',
				'rootLevel' => 1,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, name, degreeprogram')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);


$TCA['tx_fsmiexams_module'] = array (
	'ctrl' => $TCA['tx_fsmiexams_module']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l10n_parent,l10n_diffsource,hidden,name,field'
	),
	'feInterface' => $TCA['tx_fsmiexams_module']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l10n_parent' => array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_fsmiexams_module',
				'foreign_table_where' => 'AND tx_fsmiexams_module.pid=###CURRENT_PID### AND tx_fsmiexams_module.sys_language_uid IN (-1,0)',
			)
		),
		'l10n_diffsource' => array (
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'name' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_module.name',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max' => '128',
				'eval' => 'required,trim',
			)
		),
		'field' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_module.field',
			'config' => array (
				'type' => 'select',
				'foreign_table' => 'tx_fsmiexams_field',
				'foreign_table_where' => 'ORDER BY name',
				'rootLevel' => 1,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, name, field')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);



$TCA['tx_fsmiexams_lecture'] = array (
	'ctrl' => $TCA['tx_fsmiexams_lecture']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l10n_parent,l10n_diffsource,hidden,name,module'
	),
	'feInterface' => $TCA['tx_fsmiexams_lecture']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l10n_parent' => array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_fsmiexams_lecture',
				'foreign_table_where' => 'AND tx_fsmiexams_lecture.pid=###CURRENT_PID### AND tx_fsmiexams_lecture.sys_language_uid IN (-1,0)',
			)
		),
		'l10n_diffsource' => array (
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'name' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_lecture.name',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max' => '128',
				'eval' => 'trim',
			)
		),
		'module' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_lecture.module',
			'config' => array (
				'type' => 'select',
				'foreign_table' => 'tx_fsmiexams_module',
				'foreign_table_where' => 'ORDER BY tx_fsmiexams_module.field, tx_fsmiexams_module.name',
				'rootLevel' => 1,
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 10,
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, name, module')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);



$TCA['tx_fsmiexams_exam'] = array (
	'ctrl' => $TCA['tx_fsmiexams_exam']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l10n_parent,l10n_diffsource,hidden,name,number,term,lecture,year,lecturer,approved,file,examtype'
	),
	'feInterface' => $TCA['tx_fsmiexams_exam']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l10n_parent' => array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_fsmiexams_exam',
				'foreign_table_where' => 'AND tx_fsmiexams_exam.pid=###CURRENT_PID### AND tx_fsmiexams_exam.sys_language_uid IN (-1,0)',
			)
		),
		'l10n_diffsource' => array (
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'name' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.name',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'checkbox' => '',
				'eval' => 'trim',
			)
		),
		'number' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.number',
			'config' => array (
				'type'     => 'input',
				'size'     => '4',
				'max'      => '4',
				'eval'     => 'int',
				'checkbox' => '0',
				'range'    => array (
					'upper' => '10',
					'lower' => '1'
				),
				'default' => 0
			)
		),
		'term' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.term',
			'config' => array (
				'type' => 'radio',
				'items' => array (
					array('LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.term.I.0', '0'),
					array('LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.term.I.1', '1'),
				),
			)
		),
		'lecture' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.lecture',
			'config' => array (
				'type' => 'select',
				'foreign_table' => 'tx_fsmiexams_lecture',
				'foreign_table_where' => 'ORDER BY tx_fsmiexams_lecture.name',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 10,
			)
		),
		'year' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.year',
			'config' => array (
				'type' => 'input',
				'size' => '5',
				'max' => '4',
				'eval' => 'year,nospace',
			)
		),
		'exactdate' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.exactdate',
			'config' => array (
				'type'     => 'input',
				'size'     => '12',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0'
			)
		),
		'lecturer' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.lecturer',
			'config' => array (
				'type' => 'select',
				'foreign_table' => 'tx_fsmiexams_lecturer',
				'foreign_table_where' => 'ORDER BY tx_fsmiexams_lecturer.lastname',
				'rootLevel' => 1,
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 2,
			)
		),
		'approved' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.approved',
			'config' => array (
				'type' => 'check',
			)
		),
		'file' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.file',
			'config' => array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],
				'uploadfolder' => 'uploads/tx_fsmiexams',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'material' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.material',
			'config' => array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],
				'uploadfolder' => 'uploads/tx_fsmiexams',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'quality' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.quality',
			'config' => array (
				'type' => 'radio',
				'items' => array (
					array('LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.quality.I.0', '0'),
					array('LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.quality.I.1', '1'),
					array('LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.quality.I.2', '2'),
				),
			)
		),
		'examtype' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_exam.examtype',
			'config' => array (
				'type' => 'select',
				'foreign_table' => 'tx_fsmiexams_examtype',
				'rootLevel' => 1,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, name, number, lecture, term, year, examtype, exactdate, lecturer, approved, file, material, quality')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);



$TCA['tx_fsmiexams_lecturer'] = array (
	'ctrl' => $TCA['tx_fsmiexams_lecturer']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,firstname,lastname'
	),
	'feInterface' => $TCA['tx_fsmiexams_lecturer']['feInterface'],
	'columns' => array (
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'firstname' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_lecturer.firstname',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'lastname' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_lecturer.lastname',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, firstname, lastname')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);



$TCA['tx_fsmiexams_examtype'] = array (
	'ctrl' => $TCA['tx_fsmiexams_examtype']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,description'
	),
	'feInterface' => $TCA['tx_fsmiexams_examtype']['feInterface'],
	'columns' => array (
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'description' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_examtype.description',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, description')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);



$TCA['tx_fsmiexams_folder'] = array (
	'ctrl' => $TCA['tx_fsmiexams_folder']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l10n_parent,l10n_diffsource,hidden,name,folder_id,color,state,content'
	),
	'feInterface' => $TCA['tx_fsmiexams_folder']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l10n_parent' => array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_fsmiexams_folder',
				'foreign_table_where' => 'AND tx_fsmiexams_folder.pid=###CURRENT_PID### AND tx_fsmiexams_folder.sys_language_uid IN (-1,0)',
			)
		),
		'l10n_diffsource' => array (
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'name' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_folder.name',
			'config' => array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'folder_id' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_folder.folder_id',
			'config' => array (
				'type'     => 'input',
				'size'     => '5',
				'max'      => '5',
				'eval'     => 'int,unique,required',
				'default' => 0
			)
		),
		'color' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_folder.barcode',
			'config' => array (
				'type' => 'radio',
				'items' => array (
					array('LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_folder.color.I.0', '0'),
					array('LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_folder.color.I.1', '1'),
					array('LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_folder.color.I.2', '2'),
					array('LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_folder.color.I.3', '3'),
					array('LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_folder.color.I.4', '4'),
					array('LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_folder.color.I.5', '5'),
				),
			)
		),
		'state' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_folder.state',
			'config' => array (
				'type' => 'radio',
				'items' => array (
					array('LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_folder.state.I.0', '0'),
					array('LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_folder.state.I.1', '1'),
					array('LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_folder.state.I.2', '2'),
					array('LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_folder.state.I.3', '3'),
				),
			)
		),
		'content' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_folder.content',
			'config' => array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_fsmiexams_exam',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 1000,
			)
		),
		'associated_lectures' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_folder.associated_lectures',
			'config' => array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_fsmiexams_lecture',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 100,
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, name, folder_id, color, state, content, associated_lectures')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);




$TCA['tx_fsmiexams_loan'] = array (
	'ctrl' => $TCA['tx_fsmiexams_loan']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l10n_parent,l10n_diffsource,hidden,folder,lender,dispenser,lenderlogin,weight,withdrawal,withdrawaldate,deposit,lendingdate'
	),
	'feInterface' => $TCA['tx_fsmiexams_loan']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l10n_parent' => array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_fsmiexams_loan',
				'foreign_table_where' => 'AND tx_fsmiexams_loan.pid=###CURRENT_PID### AND tx_fsmiexams_loan.sys_language_uid IN (-1,0)',
			)
		),
		'l10n_diffsource' => array (
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'folder' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_loan.folder',
			'config' => array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_fsmiexams_folder',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'lender' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_loan.lender',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			)
		),
		'dispenser' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_loan.dispenser',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			)
		),
		'lenderlogin' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_loan.lenderlogin',
			'config' => array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'weight' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_loan.weight',
			'config' => array (
				'type'     => 'input',
				'size'     => '4',
				'max'      => '4',
				'eval'     => 'int',
				'checkbox' => '0',
				'range'    => array (
					'upper' => '1000',
					'lower' => '10'
				),
				'default' => 0
			)
		),
		'withdrawal' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_loan.withdrawal',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'withdrawaldate' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_loan.withdrawaldate',
			'config' => array (
				'type'     => 'input',
				'size'     => '12',
				'max'      => '20',
				'eval'     => 'datetime',
				'checkbox' => '0',
				'default'  => '0'
			)
		),
		'deposit' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_loan.deposit',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
		'lendingdate' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:fsmi_exams/locallang_db.xml:tx_fsmiexams_loan.lendingdate',
			'config' => array (
				'type'     => 'input',
				'size'     => '12',
				'max'      => '20',
				'eval'     => 'datetime',
				'checkbox' => '0',
				'default'  => '0'
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, folder, lender, dispenser, lenderlogin, weight, withdrawal, withdrawaldate, deposit, lendingdate')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
?>