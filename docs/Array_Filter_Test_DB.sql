#------------------------------------------
# PHPSurveyor Database Dump of `phpsurveyor`
# Date of Dump: 25-Jul-2006
#------------------------------------------


#------------------------------------------
# Table definition for answers
#------------------------------------------
DROP TABLE IF EXISTS answers;

CREATE TABLE answers (
    qid int(11) DEFAULT '0' NOT NULL,
    code varchar(5) DEFAULT '' NOT NULL,
    answer text DEFAULT '' NOT NULL,
    default_value char(1) DEFAULT 'N' NOT NULL,
    sortorder varchar(5),
   PRIMARY KEY (qid, code)
);


#------------------------------------------
# Table data for answers
#------------------------------------------
INSERT INTO answers VALUES("205","1","1","N","00000");
INSERT INTO answers VALUES("205","2","2","N","00001");
INSERT INTO answers VALUES("205","3","3","N","00002");
INSERT INTO answers VALUES("205","4","4","N","00003");
INSERT INTO answers VALUES("205","5","5","N","00004");
INSERT INTO answers VALUES("206","1","1","N","00000");
INSERT INTO answers VALUES("206","2","2","N","00001");
INSERT INTO answers VALUES("206","3","3","N","00002");
INSERT INTO answers VALUES("206","4","4","N","00003");
INSERT INTO answers VALUES("206","5","5","N","00004");
INSERT INTO answers VALUES("207","1","1","N","00000");
INSERT INTO answers VALUES("207","2","2","N","00001");
INSERT INTO answers VALUES("207","3","3","N","00002");
INSERT INTO answers VALUES("207","4","4","N","00003");
INSERT INTO answers VALUES("207","5","5","N","00004");
INSERT INTO answers VALUES("208","1","1","N","00000");
INSERT INTO answers VALUES("208","2","2","N","00001");
INSERT INTO answers VALUES("208","3","3","N","00002");
INSERT INTO answers VALUES("208","4","4","N","00003");
INSERT INTO answers VALUES("208","5","5","N","00004");



#------------------------------------------
# Table definition for assessments
#------------------------------------------
DROP TABLE IF EXISTS assessments;

CREATE TABLE assessments (
    id int(11) NOT NULL auto_increment,
    sid int(11) DEFAULT '0' NOT NULL,
    scope varchar(5) DEFAULT '' NOT NULL,
    gid int(11) DEFAULT '0' NOT NULL,
    name text DEFAULT '' NOT NULL,
    minimum varchar(50) DEFAULT '' NOT NULL,
    maximum varchar(50) DEFAULT '' NOT NULL,
    message text DEFAULT '' NOT NULL,
    link text DEFAULT '' NOT NULL,
   PRIMARY KEY (id)
);


#------------------------------------------
# Table data for assessments
#------------------------------------------



#------------------------------------------
# Table definition for conditions
#------------------------------------------
DROP TABLE IF EXISTS conditions;

CREATE TABLE conditions (
    cid int(11) NOT NULL auto_increment,
    qid int(11) DEFAULT '0' NOT NULL,
    cqid int(11) DEFAULT '0' NOT NULL,
    cfieldname varchar(50) DEFAULT '' NOT NULL,
    method char(2) DEFAULT '' NOT NULL,
    value varchar(5) DEFAULT '' NOT NULL,
   PRIMARY KEY (cid)
);


#------------------------------------------
# Table data for conditions
#------------------------------------------



#------------------------------------------
# Table definition for groups
#------------------------------------------
DROP TABLE IF EXISTS groups;

CREATE TABLE groups (
    gid int(11) NOT NULL auto_increment,
    sid int(11) DEFAULT '0' NOT NULL,
    group_name varchar(100) DEFAULT '' NOT NULL,
    group_order varchar(45),
    description text,
   PRIMARY KEY (gid)
);


#------------------------------------------
# Table data for groups
#------------------------------------------
INSERT INTO groups VALUES("44","36826","B Group","","");



#------------------------------------------
# Table definition for labels
#------------------------------------------
DROP TABLE IF EXISTS labels;

CREATE TABLE labels (
    lid int(11) DEFAULT '0' NOT NULL,
    code varchar(5) DEFAULT '' NOT NULL,
    title varchar(100) DEFAULT '' NOT NULL,
    sortorder varchar(5),
   PRIMARY KEY (lid, code)
);


#------------------------------------------
# Table data for labels
#------------------------------------------
INSERT INTO labels VALUES("8","1","Better","00000");
INSERT INTO labels VALUES("8","2","Same","00001");
INSERT INTO labels VALUES("8","3","Worse","00002");



#------------------------------------------
# Table definition for labelsets
#------------------------------------------
DROP TABLE IF EXISTS labelsets;

CREATE TABLE labelsets (
    lid int(11) NOT NULL auto_increment,
    label_name varchar(100) DEFAULT '' NOT NULL,
   PRIMARY KEY (lid)
);


#------------------------------------------
# Table data for labelsets
#------------------------------------------
INSERT INTO labelsets VALUES("8","Better/Same/Worse");



#------------------------------------------
# Table definition for question_attributes
#------------------------------------------
DROP TABLE IF EXISTS question_attributes;

CREATE TABLE question_attributes (
    qaid int(11) NOT NULL auto_increment,
    qid int(11) DEFAULT '0' NOT NULL,
    attribute varchar(50),
    value varchar(20),
   PRIMARY KEY (qaid)
);


#------------------------------------------
# Table data for question_attributes
#------------------------------------------
INSERT INTO question_attributes VALUES("6","208","array_filter","0001");
INSERT INTO question_attributes VALUES("5","207","array_filter","0001");
INSERT INTO question_attributes VALUES("4","206","array_filter","0001");



#------------------------------------------
# Table definition for questions
#------------------------------------------
DROP TABLE IF EXISTS questions;

CREATE TABLE questions (
    qid int(11) NOT NULL auto_increment,
    sid int(11) DEFAULT '0' NOT NULL,
    gid int(11) DEFAULT '0' NOT NULL,
    type char(1) DEFAULT 'T' NOT NULL,
    title varchar(20) DEFAULT '' NOT NULL,
    question text DEFAULT '' NOT NULL,
    preg text,
    help text,
    other char(1) DEFAULT 'N' NOT NULL,
    mandatory char(1),
    lid int(11) DEFAULT '0' NOT NULL,
   PRIMARY KEY (qid)
);


#------------------------------------------
# Table data for questions
#------------------------------------------
INSERT INTO questions VALUES("205","36826","44","M","0001","Multi Selection","","","Y","Y","0");
INSERT INTO questions VALUES("206","36826","44","A","0002","5 Point Array Filtered","","","N","Y","0");
INSERT INTO questions VALUES("207","36826","44","B","0003","10 Point Array Filtered","","","N","Y","0");
INSERT INTO questions VALUES("208","36826","44","F","0004","Flixible Array Filtered","","","N","Y","8");



#------------------------------------------
# Table definition for saved
#------------------------------------------
DROP TABLE IF EXISTS saved;

CREATE TABLE saved (
    saved_id int(11) DEFAULT '' NOT NULL,
    scid int(11) DEFAULT '0' NOT NULL,
    datestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    fieldname text DEFAULT '' NOT NULL,
    value text DEFAULT '' NOT NULL,
    ipaddr mediumtext,
   PRIMARY KEY (saved_id)
);


#------------------------------------------
# Table data for saved
#------------------------------------------



#------------------------------------------
# Table definition for saved_control
#------------------------------------------
DROP TABLE IF EXISTS saved_control;

CREATE TABLE saved_control (
    scid int(11) DEFAULT '' NOT NULL,
    sid int(11) DEFAULT '0' NOT NULL,
    identifier text DEFAULT '' NOT NULL,
    access_code text DEFAULT '' NOT NULL,
    email varchar(200),
    ip text DEFAULT '' NOT NULL,
    saved_thisstep text DEFAULT '' NOT NULL,
    status char(1) DEFAULT '' NOT NULL,
    saved_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    refurl text DEFAULT '' NOT NULL,
   PRIMARY KEY (scid)
);


#------------------------------------------
# Table data for saved_control
#------------------------------------------



#------------------------------------------
# Table definition for surveys
#------------------------------------------
DROP TABLE IF EXISTS surveys;

CREATE TABLE surveys (
    sid int(11) DEFAULT '' NOT NULL,
    short_title varchar(200) DEFAULT '' NOT NULL,
    description text,
    admin varchar(50),
    active char(1) DEFAULT 'N' NOT NULL,
    welcome text,
    expires date,
    adminemail varchar(100),
    private char(1),
    faxto varchar(20),
    format char(1),
    template varchar(100) DEFAULT 'default',
    url varchar(255),
    urldescrip varchar(255),
    language varchar(50),
    datestamp char(1) DEFAULT 'N',
    usecookie char(1) DEFAULT 'N',
    notification char(1) DEFAULT '0',
    allowregister char(1) DEFAULT 'N',
    attribute1 varchar(255),
    attribute2 varchar(255),
    email_invite_subj varchar(255),
    email_invite text,
    email_remind_subj varchar(255),
    email_remind text,
    email_register_subj varchar(255),
    email_register text,
    email_confirm_subj varchar(255),
    email_confirm text,
    allowsave char(1) DEFAULT 'Y',
    autonumber_start bigint(11) DEFAULT '0',
    autoredirect char(1) DEFAULT 'N',
    allowprev char(1) DEFAULT 'Y',
    ipaddr char(1) DEFAULT 'N',
    useexpiry char(1) DEFAULT 'N' NOT NULL,
    refurl char(1) DEFAULT 'N',
   PRIMARY KEY (sid)
);


#------------------------------------------
# Table data for surveys
#------------------------------------------
INSERT INTO surveys VALUES("36826","FIlter Test","A Survey to Test Array Filter","","N","","1980-01-01","","Y","","G","default","","","english","N","N","0","N","","","Invitation to participate in survey","Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}","Reminder to participate in survey","Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}","Survey Registration Confirmation","Dear {FIRSTNAME},\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\nTo complete this survey, click on the following URL:\n\n{SURVEYURL}\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}.","Confirmation of completed survey","Dear {FIRSTNAME},\n\nThis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}","Y","0","N","Y","N","N","N");



#------------------------------------------
# Table definition for users
#------------------------------------------
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    user varchar(20) DEFAULT '' NOT NULL,
    password varchar(20) DEFAULT '' NOT NULL,
    security varchar(10) DEFAULT '' NOT NULL
);


#------------------------------------------
# Table data for users
#------------------------------------------



