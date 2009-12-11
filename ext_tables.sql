#
# Table structure for table 'tx_ptgsadunning_dunning'
#
CREATE TABLE tx_ptgsadunning_dunning (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	related_doc_no varchar(255) DEFAULT '' NOT NULL,
	dunning_level int(11) DEFAULT '0' NOT NULL,
	dunning_charge varchar(255) DEFAULT '' NOT NULL,
	last_dunning_date varchar(255) DEFAULT '' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);