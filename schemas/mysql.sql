-- AutoAdmin SQL schema for MySQL DataBase system.
-- @author Alexander Palamarchuk <a@palamarchuk.info>

CREATE TABLE `aa_users` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `level` enum('root','admin','user') NOT NULL DEFAULT 'user',
  `login` varchar(21) NOT NULL,
  `password` varchar(32) NOT NULL,
  `interface_level` tinyint(4) NOT NULL DEFAULT '1',
  `email` varchar(40) NOT NULL,
  `surname` varchar(21) NOT NULL,
  `firstname` varchar(21) NOT NULL,
  `middlename` varchar(21) DEFAULT NULL,
  `regdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `info` tinytext,
  `salt` varchar(8) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

CREATE TABLE `aa_authorizations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` smallint(5) unsigned NOT NULL,
  `when_enter` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `when_enter` (`when_enter`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

CREATE TABLE `aa_errors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `error_type` enum('exception','warning') DEFAULT NULL,
  `info` text,
  `authorization_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `authorization_id` (`authorization_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `aa_sections` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

CREATE TABLE `aa_interfaces` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `section_id` tinyint(3) unsigned DEFAULT NULL,
  `alias` varchar(60) NOT NULL,
  `level` tinyint(3) unsigned NOT NULL DEFAULT '5',
  `title` varchar(80) NOT NULL,
  `info` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alias` (`alias`),
  KEY `section_id` (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

CREATE TABLE `aa_access` (
  `user_id` smallint(5) unsigned NOT NULL,
  `interface_id` smallint(5) unsigned NOT NULL,
  `read` tinyint(1) NOT NULL DEFAULT '0',
  `add` tinyint(1) NOT NULL DEFAULT '0',
  `edit` tinyint(1) NOT NULL DEFAULT '0',
  `delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`interface_id`),
  KEY `interface_id` (`interface_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

CREATE TABLE `aa_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `interface_id` smallint(5) unsigned DEFAULT NULL,
  `authorization_id` int(10) unsigned DEFAULT NULL,
  `when_event` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `message` text NULL DEFAULT NULL,
  `data` text NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `interface_id` (`interface_id`),
  KEY `authorization_id` (`authorization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

ALTER TABLE `aa_access`
  ADD CONSTRAINT `aa_access_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `aa_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `aa_access_ibfk_2` FOREIGN KEY (`interface_id`) REFERENCES `aa_interfaces` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `aa_authorizations`
  ADD CONSTRAINT `aa_authorizations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `aa_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `aa_errors`
  ADD CONSTRAINT `aa_errors_ibfk_1` FOREIGN KEY (`authorization_id`) REFERENCES `aa_authorizations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `aa_interfaces`
  ADD CONSTRAINT `aa_interfaces_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `aa_sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `aa_logs`
  ADD CONSTRAINT `aa_logs_ibfk_1` FOREIGN KEY (`interface_id`) REFERENCES `aa_interfaces` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `aa_logs_ibfk_2` FOREIGN KEY (`authorization_id`) REFERENCES `aa_authorizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
