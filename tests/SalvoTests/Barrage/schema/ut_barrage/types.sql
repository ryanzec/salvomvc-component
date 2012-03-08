CREATE TABLE `types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) COLLATE utf8_bin NOT NULL,
  `global` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `enum` enum('one','two','no_value') COLLATE utf8_bin DEFAULT NULL,
  `set` set('some','value_here','hello') COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
