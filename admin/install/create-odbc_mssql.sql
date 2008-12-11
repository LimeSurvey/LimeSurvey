-- LimeSurvey MS SQL Server 2000 database schema

-- --------------------------------------------------------

CREATE TABLE [prefix_quota] (
  [id] int NOT NULL IDENTITY (1,1),
  [sid] int ,
  [name] varchar(255),
  [qlimit] int ,
  [action] int ,
  [active] int NOT NULL default '1',
  PRIMARY KEY  ([id])
);





CREATE TABLE [prefix_quota_members] (
  [id] int NOT NULL IDENTITY (1,1),
  [sid] int ,
  [qid] int ,
  [quota_id] int ,
  [code] varchar(5) ,
  PRIMARY KEY  ([id])
);




-- 
-- Table structure for table [answers]
-- 

CREATE TABLE [prefix_answers] (
  [qid] INT NOT NULL default '0',
  [code] VARCHAR(5) NOT NULL default '',
  [answer] varchar(255) NOT NULL,
  [default_value] char(1) NOT NULL default 'N',
  [sortorder] INT NOT NULL,
  [language] VARCHAR(20) default 'en',
  PRIMARY KEY  ([qid],[code],[language])
) 
;

-- --------------------------------------------------------

-- 
-- Table structure for table [assessments]
-- 

CREATE TABLE [prefix_assessments] (
  [id] INT NOT NULL IDENTITY (1,1),
  [sid] INT NOT NULL default '0',
  [scope] VARCHAR(5) NOT NULL default '',
  [gid] INT NOT NULL default '0',
  [name] text NOT NULL,
  [minimum] VARCHAR(50) NOT NULL default '',
  [maximum] VARCHAR(50) NOT NULL default '',
  [message] text NOT NULL,
  [link] text NOT NULL,
  PRIMARY KEY  ([id])
) 
;

-- --------------------------------------------------------

-- 
-- Table structure for table [conditions]
-- 

CREATE TABLE [prefix_conditions] (
  [cid] INT NOT NULL IDENTITY (1,1),
  [qid] INT NOT NULL default '0',
  [scenario] INT NOT NULL default '1',
  [cqid] INT NOT NULL default '0',
  [cfieldname] VARCHAR(50) NOT NULL default '',
  [method] char(2) NOT NULL default '',
  [value] VARCHAR(255) NOT NULL default '',
  PRIMARY KEY  ([cid])
) 
;

-- 
-- Table structure for table [groups]
-- 

CREATE TABLE [prefix_groups] (
  [gid] INT NOT NULL IDENTITY (1,1),
  [sid] INT NOT NULL default '0',
  [group_name] VARCHAR(100) NOT NULL default '',
  [group_order] INT NOT NULL default '0',
  [description] text,
  [language] VARCHAR(20) default 'en',
  PRIMARY KEY  ([gid],[language])
) 
;

-- --------------------------------------------------------

-- 
-- Table structure for table [labels]
-- 

CREATE TABLE [prefix_labels] (
  [lid] INT NOT NULL default '0',
  [code] VARCHAR(5) NOT NULL default '',
  [title] VARCHAR(4000),
  [sortorder] INT NOT NULL,
  [language] VARCHAR(20) default 'en',
  PRIMARY KEY  ([lid],[sortorder],[language]),
) 
;

CREATE INDEX labels_code_idx 
  ON [prefix_labels] ([code])
;

-- --------------------------------------------------------

-- 
-- Table structure for table [labelsets]
-- 

CREATE TABLE [prefix_labelsets] (
  [lid] INT NOT NULL IDENTITY (1,1),
  [label_name] VARCHAR(100) NOT NULL default '',
  [languages] VARCHAR(200) default 'en',
  PRIMARY KEY  ([lid])
) 
;

-- --------------------------------------------------------

-- 
-- Table structure for table [question_attributes]
-- 

CREATE TABLE [prefix_question_attributes] (
  [qaid] INT NOT NULL IDENTITY (1,1),
  [qid] INT NOT NULL default '0',
  [attribute] VARCHAR(50) default NULL,
  [value] VARCHAR(20) default NULL,
  PRIMARY KEY  ([qaid])
) 
;

-- --------------------------------------------------------

-- 
-- Table structure for table [questions]
-- 

CREATE TABLE [prefix_questions] (
  [qid] INT NOT NULL IDENTITY (1,1),
  [sid] INT NOT NULL default '0',
  [gid] INT NOT NULL default '0',
  [type] char(1) NOT NULL default 'T',
  [title] VARCHAR(20) NOT NULL default '',
  [question] text NOT NULL,
  [preg] text,
  [help] text,
  [other] char(1) NOT NULL default 'N',
  [mandatory] char(1) default NULL,
  [lid] INT NOT NULL default '0',
  [lid1] INT NOT NULL default '0',
  [question_order] INT NOT NULL,
  [language] VARCHAR(20) default 'en',
  PRIMARY KEY  ([qid],[language])
) 
;

-- --------------------------------------------------------


-- 
-- Table structure for table [saved_control]
-- 

CREATE TABLE [prefix_saved_control] (
  [scid] INT NOT NULL IDENTITY (1,1),
  [sid] INT NOT NULL default '0',
  [srid] INT NOT NULL default '0',
  [identifier] varchar(255) NOT NULL,
  [access_code] text NOT NULL,
  [email] VARCHAR(320) default NULL,
  [ip] text NOT NULL,
  [saved_thisstep] text NOT NULL,
  [status] char(1) NOT NULL default '',
  [saved_date] datetime, 
  [refurl] text,
  PRIMARY KEY  ([scid])
) 
;

-- --------------------------------------------------------

-- 
-- Table structure for table [surveys]
-- 

CREATE TABLE [prefix_surveys] (
  [sid] INT NOT NULL,
  [owner_id] INT NOT NULL,
  [admin] VARCHAR(50) default NULL,
  [active] char(1) NOT NULL default 'N',
  [startdate] DATETIME default NULL,
  [expires] DATETIME default NULL,
  [adminemail] VARCHAR(320) default NULL,
  [private] char(1) default NULL,
  [faxto] VARCHAR(20) default NULL,
  [format] char(1) default NULL,
  [template] VARCHAR(100) default 'default',
  [url] VARCHAR(255) default NULL,
  [language] VARCHAR(50) default NULL,
  [additional_languages] VARCHAR(255) default NULL,
  [datestamp] char(1) default 'N',
  [usecookie] char(1) default 'N',
  [notification] char(1) default '0',
  [allowregister] char(1) default 'N',
  [attribute1] VARCHAR(255) default NULL,
  [attribute2] VARCHAR(255) default NULL,
  [allowsave] char(1) default 'Y',
  [autonumber_start] bigINT default '0',
  [autoredirect] char(1) default 'N',
  [allowprev] char(1) default 'Y',
  [printanswers] char(1) default 'N',
  [ipaddr] char(1) default 'N',
  [usestartdate] char(1) NOT NULL default 'N',
  [useexpiry] char(1) NOT NULL default 'N',
  [refurl] char(1) default 'N',
  [datecreated] DATETIME default NULL,
  [listpublic] char(1) default 'N',
  [publicstatistics] char(1) default 'N',
  [htmlemail] char(1) default 'N',
  [tokenanswerspersistence] char(1) default 'N',
  [usecaptcha] char(1) default 'N',
  [bounce_email] VARCHAR(320) default NULL,
  PRIMARY KEY  ([sid])
) 
;
-- 
-- Table structure for table [surveys_languagesettings]
-- 

if EXISTS (select * from dbo.sysobjects where id = object_id(N'[dbo].[prefix_surveys_languagesettings]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
DROP TABLE [dbo].[prefix_surveys_languagesettings]
;

CREATE TABLE [prefix_surveys_languagesettings] (
  [surveyls_survey_id] INT NOT NULL DEFAULT 0, 
  [surveyls_language] VARCHAR(45) NOT NULL DEFAULT 'en',
  [surveyls_title] VARCHAR(200) NOT NULL,
  [surveyls_description] TEXT NULL,
  [surveyls_welcometext] TEXT NULL,
  [surveyls_urldescription] VARCHAR(255) NULL,
  [surveyls_email_invite_subj] VARCHAR(255) NULL,
  [surveyls_email_invite] TEXT NULL,
  [surveyls_email_remind_subj] VARCHAR(255) NULL,
  [surveyls_email_remind] TEXT NULL,
  [surveyls_email_register_subj] VARCHAR(255) NULL,
  [surveyls_email_register] TEXT NULL,
  [surveyls_email_confirm_subj] VARCHAR(255) NULL,
  [surveyls_email_confirm] TEXT NULL,
  PRIMARY KEY ([surveyls_survey_id],[surveyls_language])
)
;

-- 
-- Table structure for table [users]
-- 

CREATE TABLE [prefix_users] (
  [uid] INT NOT NULL IDENTITY (1,1) PRIMARY KEY,
  [users_name] VARCHAR(64) NOT NULL UNIQUE default '',
  [password] TEXT NOT NULL default '', 
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
  [htmleditormode] char(7) default 'default'
) 
;

-- 
-- Table structure for table [surveys_rights]
-- 

CREATE TABLE [prefix_surveys_rights] (
	[sid] INT NOT NULL default '0', 
	[uid] INT NOT NULL default '0', 
	[edit_survey_property] TINYINT NOT NULL default '0',
	[define_questions] TINYINT NOT NULL default '0',
	[browse_response] TINYINT NOT NULL default '0',
	[export] TINYINT NOT NULL default '0',
	[delete_survey] TINYINT NOT NULL default '0',
	[activate_survey] TINYINT NOT NULL default '0',
	PRIMARY KEY ([sid], [uid])
) 
;

-- 
-- Table structure for table [user_groups]
-- 

CREATE TABLE [prefix_user_groups] (
	[ugid] INT NOT NULL IDENTITY (1,1) PRIMARY KEY, 
	[name] VARCHAR(20) NOT NULL UNIQUE,
	[description] TEXT NOT NULL default '',
	[owner_id] INT NOT NULL  
) 
;

-- 
-- Table structure for table [user_in_groups]
-- 

CREATE TABLE [prefix_user_in_groups] (
	[ugid] INT NOT NULL, 
	[uid] INT NOT NULL 
) 
;

--
-- Table structure for table [settings_global]
--

CREATE TABLE [prefix_settings_global] (
  [stg_name] VARCHAR(50) NOT NULL default '',
  [stg_value] VARCHAR(255) NOT NULL default '',
  PRIMARY KEY  ([stg_name])
);


CREATE TABLE [prefix_templates_rights] (
						  [uid] int NOT NULL,
						  [folder] varchar(255) NOT NULL,
						  [use] int NOT NULL,
						  PRIMARY KEY  ([uid],[folder])
						  );
						  
CREATE TABLE [prefix_templates] (
						  [folder] varchar(255) NOT NULL,
						  [creator] int NOT NULL,
						  PRIMARY KEY  ([folder])
						  );

--
-- Table [settings_global]
--

INSERT INTO [prefix_settings_global] VALUES ('DBVersion', '130');
INSERT INTO [prefix_settings_global] VALUES ('SessionName', '$sessionname');

--
-- Table [users]
--

INSERT INTO [prefix_users] VALUES ('$defaultuser', '$defaultpass', '$siteadminname', 0, '$defaultlang', '$siteadminemail', 1,1,1,1,1,1,1,'default');



--
-- indexes 
--
create index [answers_idx2] on [prefix_answers] ([sortorder]);
create index [assessments_idx2] on [prefix_assessments] ([sid]);
create index [assessments_idx3] on [prefix_assessments] ([gid]);
create index [conditions_idx2] on [prefix_conditions] ([qid]);
create index [conditions_idx3] on [prefix_conditions] ([cqid]);
create index [groups_idx2] on [prefix_groups] ([sid]);
create index [questions_idx2] on [prefix_questions] ([sid]);
create index [questions_idx3] on [prefix_questions] ([gid]);
create index [questions_idx4] on [prefix_questions] ([type]);
create index [question_attributes_idx2] on [prefix_question_attributes] ([qid]);
create index [quota_idx2] on [prefix_quota] ([sid]);
create index [saved_control_idx2] on [prefix_saved_control] ([sid]);
create index [user_in_groups_idx1] on [prefix_user_in_groups] ([ugid], [uid]);

