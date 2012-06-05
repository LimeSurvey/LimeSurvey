--
-- Table structure for table answers
--

CREATE TABLE [prefix_answers] (
  [qid] INT NOT NULL default '0',
  [code] VARCHAR(5) NOT NULL default '',
  [answer] varchar(max) NOT NULL,
  [sortorder] INT NOT NULL,
  [assessment_value] INT NOT NULL default '0',
  [language] VARCHAR(20) default 'en',
  [scale_id] int NOT NULL default '0',
  PRIMARY KEY  ([qid],[code],[language],[scale_id])
);


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
);


--
-- Table structure for table conditions
--
CREATE TABLE [prefix_conditions] (
  [cid] INT NOT NULL IDENTITY (1,1),
  [qid] INT NOT NULL default '0',
  [cqid] INT NOT NULL default '0',
  [cfieldname] VARCHAR(50) NOT NULL default '',
  [method] char(5) NOT NULL default '',
  [value] VARCHAR(255) NOT NULL default '',
  [scenario] INT NOT NULL default '1',
  PRIMARY KEY  ([cid])
);


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
  PRIMARY KEY ([qid] , [specialtype], [language], [scale_id], [sqid])
);


--
-- Table structure for table expression_errors
--
CREATE TABLE [prefix_expression_errors] (
  [id] integer NOT NULL IDENTITY (1,1) PRIMARY KEY,
  [errortime] varchar(50) DEFAULT NULL,
  [sid] integer DEFAULT NULL,
  [gid] integer DEFAULT NULL,
  [qid] integer DEFAULT NULL,
  [gseq] integer DEFAULT NULL,
  [qseq] integer DEFAULT NULL,
  [type] varchar(50) ,
  [eqn] varchar(max),
  [prettyprint] varchar(max)
);


--
-- Create failed_login_attempts
--
CREATE TABLE [prefix_failed_login_attempts] (
  [id] INT NOT NULL IDENTITY (1,1) PRIMARY KEY,
  [ip] varchar(37) NOT NULL,
  [last_attempt] varchar(20) NOT NULL,
  [number_attempts] int NOT NULL
);


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
  [randomization_group] VARCHAR(20) NOT NULL default '',
  [grelevance] varchar(max) DEFAULT NULL,
  PRIMARY KEY  ([gid],[language])
)
;


--
-- Table structure for table labels
--
CREATE TABLE [prefix_labels] (
  [lid] INT NOT NULL default '0',
  [code] VARCHAR(5) NOT NULL default '',
  [title] VARCHAR(max),
  [sortorder] INT NOT NULL,
  [language] VARCHAR(20) default 'en',
  [assessment_value] INT NOT NULL default '0',
  PRIMARY KEY  ([lid],[sortorder],[language]),
);


--
-- Table structure for table labelsets
--
CREATE TABLE [prefix_labelsets] (
  [lid] INT NOT NULL IDENTITY (1,1),
  [label_name] VARCHAR(100) NOT NULL default '',
  [languages] VARCHAR(200) default 'en',
  PRIMARY KEY  ([lid])
);


--
-- Table structure for table participant_attribute
--
CREATE TABLE [prefix_participant_attribute] (
  [participant_id] varchar(50) NOT NULL,
  [attribute_id] int NOT NULL,
  [value] varchar(50) NOT NULL,
  PRIMARY KEY  ([participant_id],[attribute_id])
);


--
-- Table structure for table participant_attribute_names_lang
--
CREATE TABLE [prefix_participant_attribute_names_lang] (
  [attribute_id] int NOT NULL,
  [attribute_name] varchar(30) NOT NULL,
  [lang] varchar(20) NOT NULL,
  PRIMARY KEY  ([attribute_id],[lang])
);


--
-- Table structure for table participant_attribute_names
--
CREATE TABLE [prefix_participant_attribute_names] (
  [attribute_id] int NOT NULL IDENTITY (1,1),
  [attribute_type] varchar(4) NOT NULL,
  [visible] char(5) NOT NULL,
  PRIMARY KEY  ([attribute_id],[attribute_type])
);


--
-- Table structure for table participant_attribute_values
--
CREATE TABLE [prefix_participant_attribute_values] (
  [value_id] integer NOT NULL IDENTITY (1,1) PRIMARY KEY,
  [attribute_id] integer NOT NULL,
  [value] varchar(20) NOT NULL
);


--
-- Table structure for table participant shares
--
CREATE TABLE [prefix_participant_shares] (
    [participant_id] varchar(50) NOT NULL,
    [share_uid] integer NOT NULL,
    [date_added] datetime NOT NULL,
    [can_edit] varchar(5) NOT NULL,
    PRIMARY KEY  ([participant_id],[share_uid])
);


--
-- Table structure for table participants
--
CREATE TABLE [prefix_participants] (
  [participant_id] varchar(50) NOT NULL,
  [firstname] varchar(40),
  [lastname] varchar(40),
  [email] varchar(80),
  [language] varchar(40),
  [blacklisted] varchar(1) NOT NULL,
  [owner_uid] int NOT NULL,
  PRIMARY KEY  ([participant_id])
);


--
-- Table structure for table question_attributes
--
CREATE TABLE [prefix_question_attributes] (
  [qaid] INT NOT NULL IDENTITY (1,1),
  [qid] INT NOT NULL default '0',
  [attribute] VARCHAR(50) default NULL,
  [value] varchar(max) default NULL,
  [language] VARCHAR(20) default NULL,
  PRIMARY KEY  ([qaid])
);


--
-- Table structure for table questions
--
CREATE TABLE [prefix_questions] (
  [qid] INT NOT NULL IDENTITY (1,1),
  [parent_qid] INT NOT NULL default '0',
  [sid] INT NOT NULL default '0',
  [gid] INT NOT NULL default '0',
  [type] varchar(1) NOT NULL default 'T',
  [title] varchar(20) NOT NULL default '',
  [question] varchar(max) NOT NULL,
  [preg] varchar(max),
  [help] varchar(max),
  [other] varchar(1) NOT NULL default 'N',
  [mandatory] varchar(1) default NULL,
  [question_order] INT NOT NULL,
  [language] varchar(20) default 'en',
  [scale_id] INT NOT NULL default '0',
  [same_default] INT NOT NULL default '0',
  [relevance] varchar(max),
  PRIMARY KEY  ([qid],[language])
);

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
  [quotals_quota_id] int NOT NULL default '0',
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
-- Table structure for table saved_control
--
CREATE TABLE [prefix_saved_control] (
  [scid] INT NOT NULL IDENTITY (1,1),
  [sid] INT NOT NULL default '0',
  [srid] INT NOT NULL default '0',
  [identifier] varchar(max) NOT NULL,
  [access_code] varchar(max) NOT NULL,
  [email] VARCHAR(320),
  [ip] varchar(max) NOT NULL,
  [saved_thisstep] varchar(max) NOT NULL,
  [status] varchar(1) NOT NULL default '',
  [saved_date] datetime NOT NULL,
  [refurl] varchar(max),
  PRIMARY KEY  ([scid])
);


--
-- Table structure for table sessions
--
CREATE TABLE [prefix_sessions] (
      [id] varchar(32) NOT NULL,
      [expire] int default NULL,
      [data] varchar(max),
      PRIMARY KEY ( [id] )
);


--
-- Table structure for table settings_global
--
CREATE TABLE [prefix_settings_global] (
  [stg_name] VARCHAR(50) NOT NULL default '',
  [stg_value] VARCHAR(255) NOT NULL default '',
  PRIMARY KEY  ([stg_name])
);


--
-- Table structure for table survey links
--
CREATE TABLE [prefix_survey_links] (
  [participant_id] varchar(50) NOT NULL,
  [token_id] integer NOT NULL,
  [survey_id] integer NOT NULL,
  [date_created] datetime
  PRIMARY KEY  ([participant_id],[token_id],[survey_id])
);


--
-- Table structure for table survey_permissions
--
CREATE TABLE [prefix_survey_permissions] (
    [sid] INT NOT NULL,
    [uid] INT NOT NULL,
    [permission] VARCHAR(20) NOT NULL,
    [create_p] INT NOT NULL default '0',
    [read_p] INT NOT NULL default '0',
    [update_p] INT NOT NULL default '0',
    [delete_p] INT NOT NULL default '0',
    [import_p] INT NOT NULL default '0',
    [export_p] INT NOT NULL default '0',
    PRIMARY KEY ([sid], [uid],[permission])
);


--
-- Table structure for table survey_url_parameters
--
CREATE TABLE prefix_survey_url_parameters (
	[id] INT NOT NULL IDENTITY (1,1) PRIMARY KEY,
	[sid] INT NOT NULL,
	[parameter] VARCHAR(50) NOT NULL,
	[targetqid] INT NULL,
	[targetsqid] INT NULL
);


--
-- Table structure for table surveys
--
CREATE TABLE [prefix_surveys] (
  [sid] INT NOT NULL,
  [owner_id] INT NOT NULL,
  [admin] VARCHAR(50) default NULL,
  [active] VARCHAR(1) NOT NULL default 'N',
  [expires] DATETIME default NULL,
  [startdate] DATETIME default NULL,
  [adminemail] VARCHAR(320) default NULL,
  [anonymized] VARCHAR(1) NOT NULL default 'N',
  [faxto] VARCHAR(20) default NULL,
  [format] VARCHAR(1) default NULL,
  [savetimings] char(1) NOT NULL default 'N',
  [template] VARCHAR(100) default 'default',
  [language] VARCHAR(50) default NULL,
  [additional_languages] VARCHAR(255) default NULL,
  [datestamp] VARCHAR(1) NOT NULL default 'N',
  [usecookie] VARCHAR(1) NOT NULL default 'N',
  [allowregister] VARCHAR(1) NOT NULL default 'N',
  [allowsave] VARCHAR(1) NOT NULL default 'Y',
  [autonumber_start] int NOT NULL default '0',
  [autoredirect] VARCHAR(1) NOT NULL default 'N',
  [allowprev] VARCHAR(1) NOT NULL default 'N',
  [printanswers] VARCHAR(1) NOT NULL default 'N',
  [ipaddr] VARCHAR(1) NOT NULL default 'N',
  [refurl] VARCHAR(1) NOT NULL default 'N',
  [datecreated] DATETIME default NULL,
  [publicstatistics] VARCHAR(1) NOT NULL default 'N',
  [publicgraphs] VARCHAR(1) NOT NULL default 'N',
  [listpublic] VARCHAR(1) NOT NULL default 'N',
  [htmlemail] VARCHAR(1) NOT NULL default 'N',
  [sendconfirmation] VARCHAR(1) NOT NULL default 'Y',
  [tokenanswerspersistence] VARCHAR(1) NOT NULL default 'N',
  [assessments] VARCHAR(1) NOT NULL default 'N',
  [usecaptcha] VARCHAR(1) NOT NULL default 'N',
  [usetokens] VARCHAR(1) NOT NULL default 'N',
  [bounce_email] VARCHAR(320) default NULL,
  [attributedescriptions] varchar(max),
  [emailresponseto] varchar(max),
  [emailnotificationto] varchar(max),
  [tokenlength] int NOT NULL default '15',
  [showxquestions] VARCHAR(1) default 'Y',
  [showgroupinfo] VARCHAR(1) default 'B',
  [shownoanswer] VARCHAR(1) default 'Y',
  [showqnumcode] VARCHAR(1) default 'X',
  [bouncetime] int,
  [bounceprocessing] varchar(1) default 'N',
  [bounceaccounttype] varchar(4),
  [bounceaccounthost] varchar(200),
  [bounceaccountpass] varchar(100),
  [bounceaccountencryption] varchar(3),
  [bounceaccountuser] varchar(200),
  [showwelcome] VARCHAR(1) default 'Y',
  [showprogress] VARCHAR(1) default 'Y',
  [allowjumps] VARCHAR(1) default 'N',
  [navigationdelay] int default '0',
  [nokeyboard] VARCHAR(1) default 'N',
  [alloweditaftercompletion] char(1) default 'N',
  [googleanalyticsstyle] VARCHAR(1) DEFAULT NULL,
  [googleanalyticsapikey] VARCHAR(25) DEFAULT NULL,

  PRIMARY KEY  ([sid])
);


--
-- Table structure for table surveys_languagesettings
--
CREATE TABLE [prefix_surveys_languagesettings] (
  [surveyls_survey_id] INT NOT NULL,
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
  [surveyls_attributecaptions] varchar(max) NULL,
  [email_admin_notification_subj] VARCHAR(255) NULL,
  [email_admin_notification] varchar(max) NULL,
  [email_admin_responses_subj] VARCHAR(255) NULL,
  [email_admin_responses] varchar(max) NULL,
  [surveyls_numberformat] INT NOT NULL DEFAULT 0,
  PRIMARY KEY ([surveyls_survey_id],[surveyls_language])
);


--
-- Table structure for table user_groups
--
CREATE TABLE [prefix_user_groups] (
	[ugid] INT NOT NULL IDENTITY (1,1) PRIMARY KEY,
	[name] VARCHAR(20) NOT NULL UNIQUE,
	[description] varchar(max) NOT NULL,
	[owner_id] INT NOT NULL
);


--
-- Table structure for table user_in_groups
--
CREATE TABLE [prefix_user_in_groups] (
	[ugid] INT NOT NULL,
	[uid] INT NOT NULL,
    PRIMARY KEY ([ugid],[uid])
);


--
-- Table structure for table users
--
CREATE TABLE [prefix_users] (
  [uid] INT NOT NULL IDENTITY (1,1) PRIMARY KEY,
  [users_name] VARCHAR(64) NOT NULL UNIQUE default '',
  [password] binary NOT NULL,
  [full_name] VARCHAR(50) NOT NULL,
  [parent_id] INT NOT NULL,
  [lang] VARCHAR(20),
  [email] VARCHAR(320),
  [create_survey] INT NOT NULL default '0',
  [create_user] INT NOT NULL default '0',
  [participant_panel] INT NOT NULL default '0',
  [delete_user] INT NOT NULL default '0',
  [superadmin] INT NOT NULL default '0',
  [configurator] INT NOT NULL default '0',
  [manage_template] INT NOT NULL default '0',
  [manage_label] INT NOT NULL default '0',
  [htmleditormode] char(7) default 'default',
  [templateeditormode] char(7) NOT NULL default 'default',
  [questionselectormode] char(7)  NOT NULL default 'default',
  [one_time_pw] binary,
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
-- Secondary indexes
--
create index [answers_idx2] on [prefix_answers] ([sortorder]);
create index [assessments_idx2] on [prefix_assessments] ([sid]);
create index [assessments_idx3] on [prefix_assessments] ([gid]);
create index [conditions_idx2] on [prefix_conditions] ([qid]);
create index [conditions_idx3] on [prefix_conditions] ([cqid]);
create index [groups_idx2] on [prefix_groups] ([sid]);
create index [question_attributes_idx2] on [prefix_question_attributes] ([qid]);
create index [question_attributes_idx3] on [prefix_question_attributes] ([attribute]);
create index [questions_idx2] on [prefix_questions] ([sid]);
create index [questions_idx3] on [prefix_questions] ([gid]);
create index [questions_idx4] on [prefix_questions] ([type]);
create index [quota_idx2] on [prefix_quota] ([sid]);
create index [saved_control_idx2] on [prefix_saved_control] ([sid]);
create index [parent_qid_idx] on [prefix_questions] ([parent_qid]);
create index [labels_code_idx] on [prefix_labels] ([code]);


--
-- Version Info
--
INSERT INTO [prefix_settings_global] VALUES ('DBVersion', '157');
