--
-- Table structure for table answers
--

CREATE TABLE [prefix_answers] (
  [qid] int NOT NULL default '0',
  [code] varchar(5) NOT NULL default '',
  [answer] varchar(max) NOT NULL,
  [sortorder] int NOT NULL,
  [assessment_value] int NOT NULL default '0',
  [language] varchar(20) default 'en',
  [scale_id] int NOT NULL default '0',
  PRIMARY KEY  ([qid],[code],[language],[scale_id])
);


--
-- Table structure for table assessments
--
CREATE TABLE [prefix_assessments] (
  [id] int NOT NULL IDENTITY (1,1),
  [sid] int NOT NULL default '0',
  [scope] varchar(5) NOT NULL default '',
  [gid] int NOT NULL default '0',
  [name] varchar(max) NOT NULL,
  [minimum] varchar(50) NOT NULL default '',
  [maximum] varchar(50) NOT NULL default '',
  [message] varchar(max) NOT NULL,
  [language] varchar(20) NOT NULL default 'en',
  PRIMARY KEY  ([id],[language])
);


--
-- Table structure for table conditions
--
CREATE TABLE [prefix_conditions] (
  [cid] int NOT NULL IDENTITY (1,1),
  [qid] int NOT NULL default 0,
  [cqid] int NOT NULL default 0,
  [cfieldname] varchar(50) NOT NULL default '',
  [method] varchar(5) NOT NULL default '',
  [value] varchar(255) NOT NULL default '',
  [scenario] int NOT NULL default 1,
  PRIMARY KEY  ([cid])
);


--
-- Table structure for table defaultvalues
--
CREATE TABLE [prefix_defaultvalues] (
  [qid] int NOT NULL default 0,
  [scale_id] int NOT NULL default 0,
  [sqid] int NOT NULL default 0,
  [language] varchar(20) NOT NULL,
  [specialtype] varchar(20) NOT NULL default '',
  [defaultvalue] varchar(max),
  PRIMARY KEY ([qid] , [specialtype], [language], [scale_id], [sqid])
);


--
-- Table structure for table expression_errors
--
CREATE TABLE [prefix_expression_errors] (
  [id] int NOT NULL IDENTITY (1,1) PRIMARY KEY,
  [errortime] varchar(50),
  [sid] int,
  [gid] int,
  [qid] int,
  [gseq] int,
  [qseq] int,
  [type] varchar(50),
  [eqn] varchar(max),
  [prettyprint] varchar(max)
);


--
-- Create failed_login_attempts
--
CREATE TABLE [prefix_failed_login_attempts] (
  [id] int NOT NULL IDENTITY (1,1) PRIMARY KEY,
  [ip] varchar(40) NOT NULL,
  [last_attempt] varchar(20) NOT NULL,
  [number_attempts] int NOT NULL
);


--
-- Table structure for table groups
--
CREATE TABLE [prefix_groups] (
  [gid] int NOT NULL IDENTITY (1,1),
  [sid] int NOT NULL default 0,
  [group_name] varchar(100) NOT NULL default '',
  [group_order] int NOT NULL default 0,
  [description] varchar(max),
  [language] varchar(20) default 'en',
  [randomization_group] varchar(20) NOT NULL default '',
  [grelevance] varchar(max),
  PRIMARY KEY  ([gid],[language])
)
;


--
-- Table structure for table labels
--
CREATE TABLE [prefix_labels] (
  [lid] int NOT NULL default '0',
  [code] varchar(5) NOT NULL default '',
  [title] varchar(max),
  [sortorder] int NOT NULL,
  [language] varchar(20) default 'en',
  [assessment_value] int NOT NULL default '0',
  PRIMARY KEY  ([lid],[sortorder],[language]),
);


--
-- Table structure for table labelsets
--
CREATE TABLE [prefix_labelsets] (
  [lid] int NOT NULL IDENTITY (1,1),
  [label_name] varchar(100) NOT NULL default '',
  [languages] varchar(200) default 'en',
  PRIMARY KEY  ([lid])
);


--
-- Table structure for table participant_attribute
--
CREATE TABLE [prefix_participant_attribute] (
  [participant_id] varchar(50) NOT NULL,
  [attribute_id] int NOT NULL,
  [value] varchar(max) NOT NULL,
  PRIMARY KEY  ([participant_id],[attribute_id])
);


--
-- Table structure for table participant_attribute_names_lang
--
CREATE TABLE [prefix_participant_attribute_names_lang] (
  [attribute_id] int NOT NULL,
  [attribute_name] varchar(255) NOT NULL,
  [lang] varchar(20) NOT NULL,
  PRIMARY KEY  ([attribute_id],[lang])
);


--
-- Table structure for table participant_attribute_names
--
CREATE TABLE [prefix_participant_attribute_names] (
  [attribute_id] int NOT NULL IDENTITY (1,1),
  [attribute_type] varchar(4) NOT NULL,
  [visible] varchar(5) NOT NULL,
  PRIMARY KEY  ([attribute_id],[attribute_type])
);


--
-- Table structure for table participant_attribute_values
--
CREATE TABLE [prefix_participant_attribute_values] (
  [value_id] int NOT NULL IDENTITY (1,1) PRIMARY KEY,
  [attribute_id] int NOT NULL,
  [value] varchar(max) NOT NULL
);


--
-- Table structure for table participant shares
--
CREATE TABLE [prefix_participant_shares] (
  [participant_id] varchar(50) NOT NULL,
  [share_uid] int NOT NULL,
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
  [qaid] int NOT NULL IDENTITY (1,1),
  [qid] int NOT NULL default '0',
  [attribute] varchar(50),
  [value] varchar(max),
  [language] varchar(20),
  PRIMARY KEY  ([qaid])
);


--
-- Table structure for table questions
--
CREATE TABLE [prefix_questions] (
  [qid] int NOT NULL IDENTITY (1,1),
  [parent_qid] int NOT NULL default '0',
  [sid] int NOT NULL default '0',
  [gid] int NOT NULL default '0',
  [type] varchar(1) NOT NULL default 'T',
  [title] varchar(20) NOT NULL default '',
  [question] varchar(max) NOT NULL,
  [preg] varchar(max),
  [help] varchar(max),
  [other] varchar(1) NOT NULL default 'N',
  [mandatory] varchar(1),
  [question_order] int NOT NULL,
  [language] varchar(20) default 'en',
  [scale_id] int NOT NULL default '0',
  [same_default] int NOT NULL default '0',
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
  [autoload_url] int NOT NULL default 0,
  PRIMARY KEY  ([id])
);


--
-- Table structure for table quota_languagesettings
--
CREATE TABLE [prefix_quota_languagesettings] (
  [quotals_id] int NOT NULL IDENTITY (1,1),
  [quotals_quota_id] int NOT NULL default 0,
  [quotals_language] varchar(45) NOT NULL default 'en',
  [quotals_name] varchar(255),
  [quotals_message] varchar(max) NOT NULL,
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
  [scid] int NOT NULL IDENTITY (1,1),
  [sid] int NOT NULL default '0',
  [srid] int NOT NULL default '0',
  [identifier] varchar(max) NOT NULL,
  [access_code] varchar(max) NOT NULL,
  [email] varchar(320),
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
  [expire] int,
  [data] text,
  PRIMARY KEY ( [id] )
);


--
-- Table structure for table settings_global
--
CREATE TABLE [prefix_settings_global] (
  [stg_name] varchar(50) NOT NULL default '',
  [stg_value] varchar(255) NOT NULL default '',
  PRIMARY KEY  ([stg_name])
);


--
-- Table structure for table survey links
--
CREATE TABLE [prefix_survey_links] (
  [participant_id] varchar(50) NOT NULL,
  [token_id] int NOT NULL,
  [survey_id] int NOT NULL,
  [date_created] datetime,
  [date_invited] datetime,
  [date_completed] datetime
  PRIMARY KEY  ([participant_id],[token_id],[survey_id])
);


--
-- Table structure for table survey_permissions
--
CREATE TABLE [prefix_survey_permissions] (
  [sid] int NOT NULL,
  [uid] int NOT NULL,
  [permission] varchar(20) NOT NULL,
  [create_p] int NOT NULL default '0',
  [read_p] int NOT NULL default '0',
  [update_p] int NOT NULL default '0',
  [delete_p] int NOT NULL default '0',
  [import_p] int NOT NULL default '0',
  [export_p] int NOT NULL default '0',
  PRIMARY KEY ([sid], [uid],[permission])
);


--
-- Table structure for table survey_url_parameters
--
CREATE TABLE prefix_survey_url_parameters (
  [id] int NOT NULL IDENTITY (1,1) PRIMARY KEY,
  [sid] int NOT NULL,
  [parameter] varchar(50) NOT NULL,
  [targetqid] int NULL,
  [targetsqid] int NULL
);


--
-- Table structure for table surveys
--
CREATE TABLE [prefix_surveys] (
  [sid] int NOT NULL,
  [owner_id] int NOT NULL,
  [admin] varchar(50),
  [active] varchar(1) NOT NULL default 'N',
  [expires] DATETIME,
  [startdate] DATETIME,
  [adminemail] varchar(320),
  [anonymized] varchar(1) NOT NULL default 'N',
  [faxto] varchar(20),
  [format] varchar(1),
  [savetimings] varchar(1) NOT NULL default 'N',
  [template] varchar(100) default 'default',
  [language] varchar(50),
  [additional_languages] varchar(255),
  [datestamp] varchar(1) NOT NULL default 'N',
  [usecookie] varchar(1) NOT NULL default 'N',
  [allowregister] varchar(1) NOT NULL default 'N',
  [allowsave] varchar(1) NOT NULL default 'Y',
  [autonumber_start] int NOT NULL default '0',
  [autoredirect] varchar(1) NOT NULL default 'N',
  [allowprev] varchar(1) NOT NULL default 'N',
  [printanswers] varchar(1) NOT NULL default 'N',
  [ipaddr] varchar(1) NOT NULL default 'N',
  [refurl] varchar(1) NOT NULL default 'N',
  [datecreated] DATETIME,
  [publicstatistics] varchar(1) NOT NULL default 'N',
  [publicgraphs] varchar(1) NOT NULL default 'N',
  [listpublic] varchar(1) NOT NULL default 'N',
  [htmlemail] varchar(1) NOT NULL default 'N',
  [sendconfirmation] varchar(1) NOT NULL default 'Y',
  [tokenanswerspersistence] varchar(1) NOT NULL default 'N',
  [assessments] varchar(1) NOT NULL default 'N',
  [usecaptcha] varchar(1) NOT NULL default 'N',
  [usetokens] varchar(1) NOT NULL default 'N',
  [bounce_email] varchar(320),
  [attributedescriptions] varchar(max),
  [emailresponseto] varchar(max),
  [emailnotificationto] varchar(max),
  [tokenlength] int NOT NULL default '15',
  [showxquestions] varchar(1) default 'Y',
  [showgroupinfo] varchar(1) default 'B',
  [shownoanswer] varchar(1) default 'Y',
  [showqnumcode] varchar(1) default 'X',
  [bouncetime] int,
  [bounceprocessing] varchar(1) default 'N',
  [bounceaccounttype] varchar(4),
  [bounceaccounthost] varchar(200),
  [bounceaccountpass] varchar(100),
  [bounceaccountencryption] varchar(3),
  [bounceaccountuser] varchar(200),
  [showwelcome] varchar(1) default 'Y',
  [showprogress] varchar(1) default 'Y',
  [allowjumps] varchar(1) default 'N',
  [navigationdelay] int NOT NULL default '0',
  [nokeyboard] varchar(1) default 'N',
  [alloweditaftercompletion] varchar(1) default 'N',
  [googleanalyticsstyle] varchar(1),
  [googleanalyticsapikey] varchar(25),
  PRIMARY KEY  ([sid])
);


--
-- Table structure for table surveys_languagesettings
--
CREATE TABLE [prefix_surveys_languagesettings] (
  [surveyls_survey_id] int NOT NULL,
  [surveyls_language] varchar(45) NOT NULL DEFAULT 'en',
  [surveyls_title] varchar(200) NOT NULL,
  [surveyls_description] varchar(max) NULL,
  [surveyls_welcometext] varchar(max) NULL,
  [surveyls_endtext] varchar(max) NULL,
  [surveyls_url] varchar(255) NULL,
  [surveyls_urldescription] varchar(255) NULL,
  [surveyls_email_invite_subj] varchar(255) NULL,
  [surveyls_email_invite] varchar(max) NULL,
  [surveyls_email_remind_subj] varchar(255) NULL,
  [surveyls_email_remind] varchar(max) NULL,
  [surveyls_email_register_subj] varchar(255) NULL,
  [surveyls_email_register] varchar(max) NULL,
  [surveyls_email_confirm_subj] varchar(255) NULL,
  [surveyls_email_confirm] varchar(max) NULL,
  [surveyls_dateformat] int NOT NULL DEFAULT 1,
  [surveyls_attributecaptions] varchar(max) NULL,
  [email_admin_notification_subj] varchar(255) NULL,
  [email_admin_notification] varchar(max) NULL,
  [email_admin_responses_subj] varchar(255) NULL,
  [email_admin_responses] varchar(max) NULL,
  [surveyls_numberformat] int NOT NULL DEFAULT 0,
  PRIMARY KEY ([surveyls_survey_id],[surveyls_language])
);


--
-- Table structure for table user_groups
--
CREATE TABLE [prefix_user_groups] (
  [ugid] int NOT NULL IDENTITY (1,1) PRIMARY KEY,
  [name] varchar(20) NOT NULL UNIQUE,
  [description] varchar(max) NOT NULL,
  [owner_id] int NOT NULL
);


--
-- Table structure for table user_in_groups
--
CREATE TABLE [prefix_user_in_groups] (
  [ugid] int NOT NULL,
  [uid] int NOT NULL,
  PRIMARY KEY ([ugid],[uid])
);


--
-- Table structure for table users
--
CREATE TABLE [prefix_users] (
  [uid] int NOT NULL IDENTITY (1,1) PRIMARY KEY,
  [users_name] varchar(64) NOT NULL UNIQUE default '',
  [password] text NOT NULL,
  [full_name] varchar(50) NOT NULL,
  [parent_id] int NOT NULL,
  [lang] varchar(20),
  [email] varchar(320),
  [create_survey] int NOT NULL default '0',
  [create_user] int NOT NULL default '0',
  [participant_panel] int NOT NULL default '0',
  [delete_user] int NOT NULL default '0',
  [superadmin] int NOT NULL default '0',
  [configurator] int NOT NULL default '0',
  [manage_template] int NOT NULL default '0',
  [manage_label] int NOT NULL default '0',
  [htmleditormode] varchar(7) default 'default',
  [templateeditormode] varchar(7) NOT NULL default 'default',
  [questionselectormode] varchar(7)  NOT NULL default 'default',
  [one_time_pw] text,
  [dateformat] int NOT NULL DEFAULT 1
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
INSERT INTO [prefix_settings_global] VALUES ('DBVersion', '164');
