-- 
-- Table structure for table answers
-- 

CREATE TABLE [prefix_answers] (
  [qid] INT NOT NULL default '0',
  [code] VARCHAR(5) NOT NULL default '',
  [answer] varchar(8000) NOT NULL,
  [sortorder] INT NOT NULL,
  [assessment_value] INT NOT NULL default '0',
  [language] VARCHAR(20) default 'en',
  [scale_id] tinyint NOT NULL default '0',
  PRIMARY KEY  ([qid],[code],[language],[scale_id])
) 
;


-- 
-- Table structure for table assessments
-- 

CREATE TABLE [prefix_assessments] (
  [id] INT NOT NULL IDENTITY (1,1),
  [sid] INT NOT NULL default '0',
  [scope] VARCHAR(5) NOT NULL default '',
  [gid] INT NOT NULL default '0',
  [name] varchar(max) NOT NULL,
  [minimum] VARCHAR(50) NOT NULL default '',
  [maximum] VARCHAR(50) NOT NULL default '',
  [message] varchar(max) NOT NULL,
  [language] VARCHAR(20) NOT NULL default 'en',
  PRIMARY KEY  ([id],[language])
) 
;



-- 
-- Table structure for table conditions
-- 

CREATE TABLE [prefix_conditions] (
  [cid] INT NOT NULL IDENTITY (1,1),
  [qid] INT NOT NULL default '0',
  [scenario] INT NOT NULL default '1',
  [cqid] INT NOT NULL default '0',
  [cfieldname] VARCHAR(50) NOT NULL default '',
  [method] char(5) NOT NULL default '',
  [value] VARCHAR(255) NOT NULL default '',
  PRIMARY KEY  ([cid])
) 
;


-- 
-- Table structure for table defaultvalues
-- 
CREATE TABLE [prefix_defaultvalues] (
  [qid] integer NOT NULL default '0',
  [scale_id] tinyint NOT NULL default '0',
  [sqid] integer NOT NULL default '0',
  [language] varchar(20) NOT NULL,
  [specialtype] varchar(20) NOT NULL default '',
  [defaultvalue] varchar(max),
  CONSTRAINT pk_defaultvalues_qlss PRIMARY KEY ([qid] , [scale_id], [language], [specialtype], [sqid]))
                              
                              
-- 
-- Table structure for table groups
-- 

CREATE TABLE [prefix_groups] (
  [gid] INT NOT NULL IDENTITY (1,1),
  [sid] INT NOT NULL default '0',
  [group_name] VARCHAR(100) NOT NULL default '',
  [group_order] INT NOT NULL default '0',
  [description] varchar(max),
  [language] VARCHAR(20) default 'en',
  PRIMARY KEY  ([gid],[language])
) 
;


-- 
-- Table structure for table labels
-- 

CREATE TABLE [prefix_labels] (
  [lid] INT NOT NULL default '0',
  [code] VARCHAR(5) NOT NULL default '',
  [title] VARCHAR(4000),
  [sortorder] INT NOT NULL,
  [language] VARCHAR(20) default 'en',
  [assessment_value] INT NOT NULL default '0',
  PRIMARY KEY  ([lid],[sortorder],[language]),
) 
;

CREATE INDEX labels_code_idx 
  ON [prefix_labels] ([code])
;


-- 
-- Table structure for table labelsets
-- 

CREATE TABLE [prefix_labelsets] (
  [lid] INT NOT NULL IDENTITY (1,1),
  [label_name] VARCHAR(100) NOT NULL default '',
  [languages] VARCHAR(200) default 'en',
  PRIMARY KEY  ([lid])
) 
;


-- 
-- Table structure for table question_attributes
-- 

CREATE TABLE [prefix_question_attributes] (
  [qaid] INT NOT NULL IDENTITY (1,1),
  [qid] INT NOT NULL default '0',
  [attribute] VARCHAR(50) default NULL,
  [value] varchar(max) default NULL,
  PRIMARY KEY  ([qaid])
) 
;


-- 
-- Table structure for table quota
-- 
CREATE TABLE [prefix_quota] (
  [id] int NOT NULL IDENTITY (1,1),
  [sid] int ,
  [name] varchar(255),
  [qlimit] int ,
  [action] int ,
  [active] int NOT NULL default '1',
  [autoload_url] int NOT NULL default '0',
  PRIMARY KEY  ([id])
);

-- 
-- Table structure for table quota_languagesettings
-- 
CREATE TABLE [prefix_quota_languagesettings] (
  [quotals_id] int NOT NULL IDENTITY (1,1),
  [quotals_quota_id] int,
  [quotals_language] varchar(45) NOT NULL default 'en',
  [quotals_name] varchar(255),
  [quotals_message] varchar(max),
  [quotals_url] varchar(255),
  [quotals_urldescrip] varchar(255),
  PRIMARY KEY ([quotals_id])
);


-- 
-- Table structure for table quota_members
--  
CREATE TABLE [prefix_quota_members] (
  [id] int NOT NULL IDENTITY (1,1),
  [sid] int ,
  [qid] int ,
  [quota_id] int ,
  [code] varchar(11) ,
  PRIMARY KEY  ([id])
);



-- 
-- Table structure for table questions
-- 
CREATE TABLE [prefix_questions] (
  [qid] INT NOT NULL IDENTITY (1,1),
  [parent_qid] INT NOT NULL default '0',
  [sid] INT NOT NULL default '0',
  [gid] INT NOT NULL default '0',
  [type] char(1) NOT NULL default 'T',
  [title] VARCHAR(20) NOT NULL default '',
  [question] varchar(max) NOT NULL,
  [preg] varchar(max),
  [help] varchar(max),
  [other] char(1) NOT NULL default 'N',
  [mandatory] char(1) default NULL,
  [question_order] INT NOT NULL,
  [language] VARCHAR(20) default 'en',
  [scale_id] tinyint NOT NULL default '0',
  [same_default] tinyint NOT NULL default '0',
  PRIMARY KEY  ([qid],[language])
) 
;

-- --------------------------------------------------------


-- 
-- Table structure for table saved_control
-- 

CREATE TABLE [prefix_saved_control] (
  [scid] INT NOT NULL IDENTITY (1,1),
  [sid] INT NOT NULL default '0',
  [srid] INT NOT NULL default '0',
  [identifier] varchar(255) NOT NULL,
  [access_code] varchar(max) NOT NULL,
  [email] VARCHAR(320) default NULL,
  [ip] varchar(max) NOT NULL,
  [saved_thisstep] varchar(max) NOT NULL,
  [status] char(1) NOT NULL default '',
  [saved_date] datetime, 
  [refurl] varchar(max),
  PRIMARY KEY  ([scid])
) 
;


-- 
-- Table structure for table sessions
-- 
CREATE TABLE prefix_sessions(
    sesskey VARCHAR( 64 ) NOT NULL DEFAULT '',
    expiry DATETIME NOT NULL ,
    expireref VARCHAR( 250 ) DEFAULT '',
    created DATETIME NOT NULL ,
    modified DATETIME NOT NULL ,
    sessdata varchar(max),
    CONSTRAINT pk_sessions_sesskey PRIMARY KEY ( [sesskey] ));
create index [idx_expiry] on [prefix_sessions] ([expiry]);
create index [idx_expireref] on [prefix_sessions] ([expireref]);


--
-- Table structure for table settings_global
--
CREATE TABLE [prefix_settings_global] (
  [stg_name] VARCHAR(50) NOT NULL default '',
  [stg_value] VARCHAR(255) NOT NULL default '',
  PRIMARY KEY  ([stg_name])
);


-- 
-- Table structure for table surveys
-- 
CREATE TABLE [prefix_surveys] (
  [sid] INT NOT NULL,
  [owner_id] INT NOT NULL,
  [admin] VARCHAR(50) default NULL,
  [active] char(1) NOT NULL default 'N',
  [startdate] DATETIME default NULL,
  [expires] DATETIME default NULL,
  [adminemail] VARCHAR(320) default NULL,
  [anonymized] char(1) NOT NULL default 'N',
  [faxto] VARCHAR(20) default NULL,
  [format] char(1) default NULL,
  [savetimings] char(1) default 'N',
  [template] VARCHAR(100) default 'default',
  [language] VARCHAR(50) default NULL,
  [additional_languages] VARCHAR(255) default NULL,
  [datestamp] char(1) default 'N',
  [usecookie] char(1) default 'N',
  [allowregister] char(1) default 'N',
  [allowsave] char(1) default 'Y',
  [autonumber_start] bigint default '0',
  [autoredirect] char(1) default 'N',
  [showXquestions] char(1) default 'Y',
  [showgroupinfo] char(1) default 'B',
  [shownoanswer] char(1) default 'Y',
  [showqnumcode] char(1) default 'X',
  [showwelcome] char(1) default 'Y',
  [allowprev] char(1) default 'Y',
  [printanswers] char(1) default 'N',
  [ipaddr] char(1) default 'N',
  [refurl] char(1) default 'N',
  [datecreated] DATETIME default NULL,
  [listpublic] char(1) default 'N',
  [publicstatistics] char(1) default 'N',
  [publicgraphs] char(1) default 'N',
  [htmlemail] char(1) default 'N',
  [tokenanswerspersistence] char(1) default 'N',
  [assessments] char(1) default 'N',
  [usecaptcha] char(1) default 'N',
  [usetokens] char(1) default 'N',
  [bounce_email] VARCHAR(320) default NULL,
  [attributedescriptions] varchar(max),
  [emailresponseto] varchar(max),
  [emailnotificationto] varchar(max),
  [tokenlength] tinyint default '15',
  [bouncetime] bigint,
  [bounceprocessing] varchar(1) default 'N',
  [bounceaccounttype] varchar(4) default NULL,
  [bounceaccounthost] varchar(200) default NULL,
  [bounceaccountuser] varchar(200) default NULL,
  [bounceaccountpass] varchar(100) default NULL,
  [bounceaccountencryption] varchar(3) default NULL,
  [showprogress] char(1) default 'N',
  [allowjumps] char(1) default 'N',
  [navigationdelay] tinyint default '0',
  [nokeyboard] char(1) default 'N',
  [alloweditaftercompletion] char(1) default 'N',
  
  PRIMARY KEY  ([sid])
) 
;


-- 
-- Table structure for table surveys_languagesettings
-- 
CREATE TABLE [prefix_surveys_languagesettings] (
  [surveyls_survey_id] INT NOT NULL DEFAULT 0, 
  [surveyls_language] VARCHAR(45) NOT NULL DEFAULT 'en',
  [surveyls_title] VARCHAR(200) NOT NULL,
  [surveyls_description] varchar(max) NULL,
  [surveyls_welcometext] varchar(max) NULL,
  [surveyls_endtext] varchar(max) NULL,
  [surveyls_url] VARCHAR(255) NULL,
  [surveyls_urldescription] VARCHAR(255) NULL,
  [surveyls_email_invite_subj] VARCHAR(255) NULL,
  [surveyls_email_invite] varchar(max) NULL,
  [surveyls_email_remind_subj] VARCHAR(255) NULL,
  [surveyls_email_remind] varchar(max) NULL,
  [surveyls_email_register_subj] VARCHAR(255) NULL,
  [surveyls_email_register] varchar(max) NULL,
  [surveyls_email_confirm_subj] VARCHAR(255) NULL,
  [surveyls_email_confirm] varchar(max) NULL,
  [surveyls_dateformat] INT NOT NULL DEFAULT 1,
  [email_admin_notification_subj] VARCHAR(255) NULL,
  [email_admin_notification] varchar(max) NULL,
  [email_admin_responses_subj] VARCHAR(255) NULL,
  [email_admin_responses] varchar(max) NULL,
  [surveyls_numberformat] INT NOT NULL DEFAULT 0,
  PRIMARY KEY ([surveyls_survey_id],[surveyls_language])
)
;


-- 
-- Table structure for table survey_permissions
-- 
CREATE TABLE [prefix_survey_permissions] (
    [sid] INT NOT NULL,         
    [uid] INT NOT NULL,         
    [permission] VARCHAR(20) NOT NULL,       
    [create_p] TINYINT NOT NULL default '0', 
    [read_p] TINYINT NOT NULL default '0', 
    [update_p] TINYINT NOT NULL default '0', 
    [delete_p] TINYINT NOT NULL default '0', 
    [import_p] TINYINT NOT NULL default '0', 
    [export_p] TINYINT NOT NULL default '0', 
    PRIMARY KEY ([sid], [uid],[permission])
);


-- 
-- Table structure for table user_groups
-- 
CREATE TABLE [prefix_user_groups] (
	[ugid] INT NOT NULL IDENTITY (1,1) PRIMARY KEY, 
	[name] VARCHAR(20) NOT NULL UNIQUE,
	[description] varchar(max) NOT NULL default '',
	[owner_id] INT NOT NULL  
) 
;

-- 
-- Table structure for table user_in_groups
-- 

CREATE TABLE [prefix_user_in_groups] (
	[ugid] INT NOT NULL, 
	[uid] INT NOT NULL 
) 
;


-- 
-- Table structure for table users
-- 

CREATE TABLE [prefix_users] (
  [uid] INT NOT NULL IDENTITY (1,1) PRIMARY KEY,
  [users_name] VARCHAR(64) NOT NULL UNIQUE default '',
  [password] varchar(8000) NOT NULL default '', 
  [full_name] VARCHAR(50) NOT NULL,
  [parent_id] INT NOT NULL, 
  [lang] VARCHAR(20),
  [email] VARCHAR(320) NOT NULL UNIQUE,
  [create_survey] TINYINT NOT NULL default '0',
  [create_user] TINYINT NOT NULL default '0',
  [delete_user] TINYINT NOT NULL default '0',
  [superadmin] TINYINT NOT NULL default '0',
  [configurator] TINYINT NOT NULL default '0',
  [manage_template] TINYINT NOT NULL default '0',
  [manage_label] TINYINT NOT NULL default '0',
  [htmleditormode] char(7) default 'default',
  [templateeditormode] char(7) default 'default',
  [questionselectormode] char(7) default 'default',
  [one_time_pw] varchar(max),
  [dateformat] INT NOT NULL DEFAULT 1
);


--
-- Table structure for table templates_rights
--
CREATE TABLE [prefix_templates_rights] (
						  [uid] int NOT NULL,
						  [folder] varchar(255) NOT NULL,
						  [use] int NOT NULL,
						  PRIMARY KEY  ([uid],[folder])
						  );
						  

--
-- Table structure for table templates
--						  
CREATE TABLE [prefix_templates] (
						  [folder] varchar(255) NOT NULL,
						  [creator] int NOT NULL,
						  PRIMARY KEY  ([folder])
						  );


--
-- Create failed_login_attempts
--

CREATE TABLE [prefix_failed_login_attempts] (
  [id] int(11) NOT NULL AUTO_INCREMENT,
  [ip] varchar(37) NOT NULL,
  [last_attempt] varchar(20) NOT NULL,
  [number_attempts] int(11) NOT NULL,
  PRIMARY KEY ([id])
);


--
-- Secondary indexes 
--
create index [answers_idx2] on [prefix_answers] ([sortorder]);
create index [assessments_idx2] on [prefix_assessments] ([sid]);
create index [assessments_idx3] on [prefix_assessments] ([gid]);
create index [conditions_idx2] on [prefix_conditions] ([qid]);
create index [conditions_idx3] on [prefix_conditions] ([cqid]);
create index [groups_idx2] on [prefix_groups] ([sid]);
create index [question_attributes_idx2] on [prefix_question_attributes] ([qid]);
create index [questions_idx2] on [prefix_questions] ([sid]);
create index [questions_idx3] on [prefix_questions] ([gid]);
create index [questions_idx4] on [prefix_questions] ([type]);
create index [quota_idx2] on [prefix_quota] ([sid]);
create index [saved_control_idx2] on [prefix_saved_control] ([sid]);
create index [user_in_groups_idx1] on [prefix_user_in_groups] ([ugid], [uid]);
create index [parent_qid_idx] on [prefix_questions] ([parent_qid]);      

--
-- Version Info
--
INSERT INTO [prefix_settings_global] VALUES ('DBVersion', '146');
INSERT INTO [prefix_settings_global] VALUES ('SessionName', '$sessionname');


--
-- Create admin user
--
INSERT INTO [prefix_users] ([users_name], [password], [full_name], [parent_id], [lang] ,[email], [create_survey], [create_user] ,[delete_user] ,[superadmin] ,[configurator] ,[manage_template] , [manage_label]) VALUES ('$defaultuser', '$defaultpass', '$siteadminname', 0, '$defaultlang', '$siteadminemail', 1,1,1,1,1,1,1);
