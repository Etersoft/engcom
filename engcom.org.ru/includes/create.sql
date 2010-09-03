DROP TABLE IF EXISTS `mos_dictionary`;
CREATE TABLE `mos_dictionary` (
  `word` varchar(50) NOT NULL default '',
  `article` text NOT NULL,
  PRIMARY KEY  (`word`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `mos_dictionary_additions`;
CREATE TABLE `mos_dictionary_additions` (
  `id` int(11) NOT NULL auto_increment,
  `word` varchar(50) NOT NULL default '',
  `text` text NOT NULL,
  `author` varchar(100) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `admin_check` char(1) NOT NULL default '',
  PRIMARY KEY (`id`),
  KEY `word`(`word`)
) TYPE=MyISAM;
