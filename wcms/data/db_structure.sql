CREATE TABLE content (
  cont_id int(10) NOT NULL auto_increment,
  cont_ident varchar(80) NOT NULL default '',
  cont_timestamp int(12) NOT NULL default '0',
  cont_content text NOT NULL,
  cont_title varchar(90) NOT NULL default '',
  cont_revision varchar(8) NOT NULL default '',
  cont_parent_id int(10) NOT NULL default '0',
  cont_settings text NOT NULL,
  PRIMARY KEY  (cont_id),
  UNIQUE KEY cont_ident (cont_ident)
) TYPE=MyISAM;

CREATE TABLE sessions (
  session_id varchar(100) NOT NULL default '',
  user_id tinyint(10) NOT NULL default '0',
  session_data text,
  session_modified int(10) NOT NULL default '0',
  UNIQUE KEY session_id (session_id)
) TYPE=MyISAM COMMENT='Session Data Table';

CREATE TABLE users (
  user_id tinyint(5) NOT NULL auto_increment,
  name varchar(60) NOT NULL default '',
  password varchar(40) NOT NULL default '',
  session_id varchar(100) NOT NULL default '',
  permissions text NOT NULL,
  PRIMARY KEY  (user_id),
  UNIQUE KEY name (name,session_id)
) TYPE=MyISAM COMMENT='Users table with session IDs';