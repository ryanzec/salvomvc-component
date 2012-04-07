CREATE TABLE `UsersTwo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `firstName` varchar(64) COLLATE utf8_bin NOT NULL,
  `lastName` varchar(64) COLLATE utf8_bin NOT NULL,
  `email` varchar(256) COLLATE utf8_bin NOT NULL,
  `username` varchar(64) COLLATE utf8_bin NOT NULL,
  `password` varchar(32) COLLATE utf8_bin NOT NULL,
  `typeId` int(10) unsigned DEFAULT NULL,
  `statusId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `typeId` (`typeId`),
  KEY `statusId` (`statusId`),
  CONSTRAINT `userstwo_ibfk_4` FOREIGN KEY (`typeId`) REFERENCES `types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `userstwo_ibfk_3` FOREIGN KEY (`statusId`) REFERENCES `sTaT_useS` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
