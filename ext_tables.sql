#
# Table structure for table 'tx_fsmiexams_degreeprogram'
#
CREATE TABLE tx_fsmiexams_degreeprogram (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	name varchar(64) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_fsmiexams_field'
#
CREATE TABLE tx_fsmiexams_field (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	name varchar(64) DEFAULT '' NOT NULL,
	degreeprogram text,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_fsmiexams_module'
#
CREATE TABLE tx_fsmiexams_module (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	name varchar(128) DEFAULT '' NOT NULL,
	field text,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_fsmiexams_lecture'
#
CREATE TABLE tx_fsmiexams_lecture (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	name varchar(128) DEFAULT '' NOT NULL,
	module text,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_fsmiexams_exam'
#
CREATE TABLE tx_fsmiexams_exam (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	name varchar(255) DEFAULT '' NOT NULL,
	number int(11) DEFAULT '0' NOT NULL,
	term int(11) DEFAULT '0' NOT NULL,
	lecture text,
	year int(11) DEFAULT '0' NOT NULL,
	exactdate int(11) DEFAULT '0' NOT NULL,
	lecturer text,
	approved tinyint(3) DEFAULT '0' NOT NULL,
	file text,
	material text,
	quality int(11) DEFAULT '0' NOT NULL,
	examtype text,
	material_description varchar(255) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_fsmiexams_lecturer'
#
CREATE TABLE tx_fsmiexams_lecturer (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	firstname varchar(255) DEFAULT '' NOT NULL,
	lastname varchar(255) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_fsmiexams_examtype'
#
CREATE TABLE tx_fsmiexams_examtype (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	description tinytext,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_fsmiexams_folder'
#
CREATE TABLE tx_fsmiexams_folder (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	name tinytext,
	folder_id int(11) DEFAULT '0' NOT NULL,
	color int(11) DEFAULT '0' NOT NULL,
	state text,
	content text,
	associated_lectures text,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_fsmiexams_loan'
#
CREATE TABLE tx_fsmiexams_loan (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	folder text,
	lender tinytext,
	dispenser tinytext,
	lenderlogin tinytext,
	weight int(11) DEFAULT '0' NOT NULL,
	withdrawal varchar(255) DEFAULT '' NOT NULL,
	withdrawaldate int(11) DEFAULT '0' NOT NULL,
	deposit varchar(255) DEFAULT '' NOT NULL,
	lendingdate int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for extension to table 'fe_groups'
#
CREATE TABLE fe_groups (
	tx_fsmiexams_fsmiexams_rights_edit tinyint(4) DEFAULT '0' NOT NULL,
	tx_fsmiexams_fsmiexams_rights_download tinyint(4) DEFAULT '0' NOT NULL,
	tx_fsmiexams_fsmiexams_rights_print tinyint(4) DEFAULT '0' NOT NULL
);
