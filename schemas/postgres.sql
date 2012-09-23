-- AutoAdmin SQL schema for PostgreSQL DataBase system.
-- @author Alexander Palamarchuk <a@palamarchuk.info>

CREATE SCHEMA admin AUTHORIZATION postgres;

CREATE TYPE admin.aa_error_type AS ENUM ('exception', 'warning');
CREATE TYPE admin.aa_user_level AS ENUM ('root', 'admin', 'user');

CREATE SEQUENCE admin.aa_authorizations_id_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;
CREATE SEQUENCE admin.aa_errors_id_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;
CREATE SEQUENCE admin.aa_users_id_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;
CREATE SEQUENCE admin.aa_sections_id_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;
CREATE SEQUENCE admin.aa_logs_id_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;
CREATE SEQUENCE admin.aa_interfaces_id_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;
  
CREATE TABLE admin.aa_users (
  id SERIAL, 
  level admin.aa_user_level DEFAULT 'user'::admin.aa_user_level NOT NULL, 
  login VARCHAR(21) NOT NULL, 
  password VARCHAR(32) NOT NULL, 
  interface_level SMALLINT DEFAULT 1 NOT NULL,
  email VARCHAR(40) NOT NULL, 
  surname VARCHAR(21) NOT NULL, 
  firstname VARCHAR(21) NOT NULL, 
  middlename VARCHAR(21) DEFAULT NULL::character varying, 
  regdate TIMESTAMP WITHOUT TIME ZONE NOT NULL, 
  info TEXT, 
  salt VARCHAR(8), 
  disabled BOOLEAN DEFAULT false NOT NULL, 
  CONSTRAINT aa_users_idx UNIQUE(login), 
  CONSTRAINT aa_users_pkey PRIMARY KEY(id)
) WITHOUT OIDS;

CREATE TABLE admin.aa_authorizations (
  id SERIAL, 
  user_id SMALLINT NOT NULL, 
  when_enter TIMESTAMP WITHOUT TIME ZONE NOT NULL, 
  ip VARCHAR(15) NOT NULL, 
  CONSTRAINT aa_authorizations_pkey PRIMARY KEY(id), 
  CONSTRAINT aa_authorizations_fk FOREIGN KEY (user_id)
    REFERENCES admin.aa_users(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE TABLE admin.aa_errors (
  id SERIAL, 
  error_type admin.aa_error_type, 
  info TEXT, 
  authorization_id INTEGER, 
  CONSTRAINT aa_errors_pkey PRIMARY KEY(id), 
  CONSTRAINT aa_errors_fk FOREIGN KEY (authorization_id)
    REFERENCES admin.aa_authorizations(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;
  
CREATE TABLE admin.aa_sections (
  id SERIAL, 
  title VARCHAR(40) NOT NULL, 
  CONSTRAINT aa_sections_pkey PRIMARY KEY(id)
) WITHOUT OIDS;

CREATE TABLE admin.aa_interfaces (
  id SERIAL, 
  section_id SMALLINT, 
  alias VARCHAR(60) NOT NULL, 
  level SMALLINT DEFAULT 5 NOT NULL, 
  title VARCHAR(80) NOT NULL, 
  info TEXT, 
  CONSTRAINT aa_interfaces_idx UNIQUE(alias), 
  CONSTRAINT aa_interfaces_pkey PRIMARY KEY(id), 
  CONSTRAINT aa_interfaces_fk FOREIGN KEY (section_id)
    REFERENCES admin.aa_sections(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE TABLE admin.aa_access (
  user_id INTEGER NOT NULL, 
  interface_id INTEGER NOT NULL, 
  read BOOLEAN DEFAULT false NOT NULL, 
  add BOOLEAN DEFAULT false NOT NULL, 
  edit BOOLEAN DEFAULT false NOT NULL, 
  delete BOOLEAN DEFAULT false NOT NULL, 
  CONSTRAINT aa_access_pkey PRIMARY KEY(user_id, interface_id), 
  CONSTRAINT aa_access_fk FOREIGN KEY (interface_id)
    REFERENCES admin.aa_interfaces(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE, 
  CONSTRAINT aa_access_fk1 FOREIGN KEY (user_id)
    REFERENCES admin.aa_users(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE TABLE admin.aa_logs (
  id BIGSERIAL, 
  interface_id INTEGER, 
  authorization_id INTEGER, 
  when_event TIMESTAMP WITHOUT TIME ZONE NOT NULL, 
  message TEXT, 
  data TEXT, 
  CONSTRAINT aa_logs_pkey PRIMARY KEY(id), 
  CONSTRAINT aa_logs_fk FOREIGN KEY (interface_id)
    REFERENCES admin.aa_interfaces(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE, 
  CONSTRAINT aa_logs_fk2 FOREIGN KEY (authorization_id)
    REFERENCES admin.aa_authorizations(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE INDEX aa_logs_idx ON admin.aa_logs
  USING btree (when_event);
