-- AutoAdmin SQL schema for SQL Server DataBase system.
-- @author Alexander Palamarchuk <a@palamarchuk.info>

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO,MSSQL' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE "aa_access" (
  "user_id" smallint(5) unsigned NOT NULL,
  "interface_id" smallint(5) unsigned NOT NULL,
  "read" tinyint(1) NOT NULL DEFAULT '0',
  "add" tinyint(1) NOT NULL DEFAULT '0',
  "edit" tinyint(1) NOT NULL DEFAULT '0',
  "delete" tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY ("user_id","interface_id"),
  KEY "interface_id" ("interface_id"),
  CONSTRAINT "aa_access_ibfk_1" FOREIGN KEY ("user_id") REFERENCES "aa_users" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "aa_access_ibfk_2" FOREIGN KEY ("interface_id") REFERENCES "aa_interfaces" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);
LOCK TABLES "aa_access" WRITE;
/*!40000 ALTER TABLE "aa_access" DISABLE KEYS */;
/*!40000 ALTER TABLE "aa_access" ENABLE KEYS */;
UNLOCK TABLES;

CREATE TABLE "aa_authorizations" (
  "id" int(10) unsigned NOT NULL,
  "user_id" smallint(5) unsigned NOT NULL,
  "when_enter" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "ip" varchar(15) NOT NULL,
  PRIMARY KEY ("id"),
  KEY "user_id" ("user_id"),
  KEY "when_enter" ("when_enter"),
  CONSTRAINT "aa_authorizations_ibfk_1" FOREIGN KEY ("user_id") REFERENCES "aa_users" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);
LOCK TABLES "aa_authorizations" WRITE;
/*!40000 ALTER TABLE "aa_authorizations" DISABLE KEYS */;
/*!40000 ALTER TABLE "aa_authorizations" ENABLE KEYS */;
UNLOCK TABLES;

CREATE TABLE "aa_errors" (
  "id" int(10) unsigned NOT NULL,
  "error_type" enum('exception','warning') DEFAULT NULL,
  "info" text,
  "authorization_id" int(10) unsigned DEFAULT NULL,
  PRIMARY KEY ("id"),
  KEY "authorization_id" ("authorization_id"),
  CONSTRAINT "aa_errors_ibfk_1" FOREIGN KEY ("authorization_id") REFERENCES "aa_authorizations" ("id") ON DELETE SET NULL ON UPDATE CASCADE
);
LOCK TABLES "aa_errors" WRITE;
/*!40000 ALTER TABLE "aa_errors" DISABLE KEYS */;
/*!40000 ALTER TABLE "aa_errors" ENABLE KEYS */;
UNLOCK TABLES;

CREATE TABLE "aa_interfaces" (
  "id" smallint(5) unsigned NOT NULL,
  "section_id" tinyint(3) unsigned DEFAULT NULL,
  "alias" varchar(60) NOT NULL,
  "level" tinyint(3) unsigned NOT NULL DEFAULT '5',
  "title" varchar(80) NOT NULL,
  "info" text,
  PRIMARY KEY ("id"),
  UNIQUE KEY "alias" ("alias"),
  KEY "section_id" ("section_id"),
  CONSTRAINT "aa_interfaces_ibfk_1" FOREIGN KEY ("section_id") REFERENCES "aa_sections" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);
LOCK TABLES "aa_interfaces" WRITE;
/*!40000 ALTER TABLE "aa_interfaces" DISABLE KEYS */;
/*!40000 ALTER TABLE "aa_interfaces" ENABLE KEYS */;
UNLOCK TABLES;

CREATE TABLE "aa_logs" (
  "id" bigint(20) unsigned NOT NULL,
  "interface_id" smallint(5) unsigned DEFAULT NULL,
  "authorization_id" int(10) unsigned DEFAULT NULL,
  "when_event" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "message" text,
  "data" text,
  PRIMARY KEY ("id"),
  KEY "interface_id" ("interface_id"),
  KEY "authorization_id" ("authorization_id"),
  CONSTRAINT "aa_logs_ibfk_1" FOREIGN KEY ("interface_id") REFERENCES "aa_interfaces" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "aa_logs_ibfk_2" FOREIGN KEY ("authorization_id") REFERENCES "aa_authorizations" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);
LOCK TABLES "aa_logs" WRITE;
/*!40000 ALTER TABLE "aa_logs" DISABLE KEYS */;
/*!40000 ALTER TABLE "aa_logs" ENABLE KEYS */;
UNLOCK TABLES;

CREATE TABLE "aa_sections" (
  "id" tinyint(3) unsigned NOT NULL,
  "title" varchar(40) NOT NULL,
  PRIMARY KEY ("id")
);
LOCK TABLES "aa_sections" WRITE;
/*!40000 ALTER TABLE "aa_sections" DISABLE KEYS */;
/*!40000 ALTER TABLE "aa_sections" ENABLE KEYS */;
UNLOCK TABLES;

CREATE TABLE "aa_users" (
  "id" smallint(5) unsigned NOT NULL,
  "level" enum('root','admin','user') NOT NULL DEFAULT 'user',
  "login" varchar(21) NOT NULL,
  "password" varchar(32) NOT NULL,
  "interface_level" tinyint(4) NOT NULL DEFAULT '1',
  "email" varchar(40) NOT NULL,
  "surname" varchar(21) NOT NULL,
  "firstname" varchar(21) NOT NULL,
  "middlename" varchar(21) DEFAULT NULL,
  "regdate" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "info" tinytext,
  "salt" varchar(8) DEFAULT NULL,
  "disabled" tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY ("id"),
  UNIQUE KEY "login" ("login")
);
LOCK TABLES "aa_users" WRITE;
/*!40000 ALTER TABLE "aa_users" DISABLE KEYS */;
/*!40000 ALTER TABLE "aa_users" ENABLE KEYS */;
UNLOCK TABLES;

/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
