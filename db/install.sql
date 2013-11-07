CREATE TABLE `dozentenrechte` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_id` varchar(32) COLLATE latin1_german1_ci NOT NULL,
  `for_id` varchar(32) COLLATE latin1_german1_ci NOT NULL,
  `institute_id` varchar(32) COLLATE latin1_german1_ci NOT NULL,
  `begin` int(11) NOT NULL,
  `end` int(11) NOT NULL,
  `verify` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `mkdate` int(11) NOT NULL,
  `chdate` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);