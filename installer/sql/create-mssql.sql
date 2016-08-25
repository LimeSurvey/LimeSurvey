-- Note that MSSQL needs to have specified if a column is NULL or not NULL because
-- depending on the database driver the default may by varying.
-- To store Unicode/UTF-8 the related columns need to be nvarchar/ntext
-- Table structure for table answers
--

CREATE TABLE [prefix_answers] (
[qid] int NOT NULL default '0',
[code] varchar(5) NOT NULL default '',
[answer] nvarchar(max) NOT NULL,
[sortorder] int NOT NULL,
[assessment_value] int NOT NULL default '0',
[language] varchar(20)NOT NULL default 'en',
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
[name] nvarchar(max) NOT NULL,
[minimum] varchar(50) NOT NULL default '',
[maximum] varchar(50) NOT NULL default '',
[message] nvarchar(max) NOT NULL,
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
[value] nvarchar(255) NOT NULL default '',
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
[defaultvalue] nvarchar(max) NULL,
PRIMARY KEY ([qid] , [specialtype], [language], [scale_id], [sqid])
);


--
-- Table structure for table expression_errors
--
CREATE TABLE [prefix_expression_errors] (
[id] int NOT NULL IDENTITY (1,1) PRIMARY KEY,
[errortime] varchar(50) NULL,
[sid] int NULL,
[gid] int NULL,
[qid] int NULL,
[gseq] int NULL,
[qseq] int NULL,
[type] varchar(50) NULL,
[eqn] varchar(max) NULL,
[prettyprint] varchar(max) NULL
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
[group_name] nvarchar(100) NOT NULL default '',
[group_order] int NOT NULL default 0,
[description] nvarchar(max) NULL,
[language] nvarchar(20) NOT NULL default 'en',
[randomization_group] nvarchar(20) NOT NULL default '',
[grelevance] nvarchar(max) NULL,
PRIMARY KEY  ([gid],[language])
)
;


--
-- Table structure for table labels
--
CREATE TABLE [prefix_labels] (
[lid] int NOT NULL default '0',
[code] nvarchar(5) NOT NULL default '',
[title] nvarchar(max) NULL,
[sortorder] int NOT NULL,
[language] varchar(20) NOT NULL default 'en',
[assessment_value] int NOT NULL default '0',
PRIMARY KEY  ([lid],[sortorder],[language]),
);
create index [labels_code_idx] on [prefix_labels] ([code]);


--
-- Table structure for table labelsets
--
CREATE TABLE [prefix_labelsets] (
[lid] int NOT NULL IDENTITY (1,1),
[label_name] nvarchar(100) NOT NULL default '',
[languages] varchar(200) default 'en' NULL,
PRIMARY KEY  ([lid])
);


--
-- Table structure for table participant_attribute
--
CREATE TABLE [prefix_participant_attribute] (
[participant_id] varchar(50) NOT NULL,
[attribute_id] int NOT NULL,
[value] nvarchar(max) NOT NULL,
PRIMARY KEY  ([participant_id],[attribute_id])
);


--
-- Table structure for table participant_attribute_names_lang
--
CREATE TABLE [prefix_participant_attribute_names_lang] (
[attribute_id] int NOT NULL,
[attribute_name] nvarchar(255) NOT NULL,
[lang] varchar(20) NOT NULL,
PRIMARY KEY  ([attribute_id],[lang])
);


--
-- Table structure for table participant_attribute_names
--
CREATE TABLE [prefix_participant_attribute_names] (
[attribute_id] int NOT NULL IDENTITY (1,1),
[attribute_type] varchar(4) NOT NULL,
[defaultname] nvarchar(255) NOT NULL,
[visible] varchar(5) NOT NULL,
PRIMARY KEY  ([attribute_id],[attribute_type])
);


--
-- Table structure for table participant_attribute_values
--
CREATE TABLE [prefix_participant_attribute_values] (
[value_id] int NOT NULL IDENTITY (1,1) PRIMARY KEY,
[attribute_id] int NOT NULL,
[value] nvarchar(max) NOT NULL
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
[firstname] nvarchar(150) NULL,
[lastname] nvarchar(150) NULL,
[email] nvarchar(max) NULL,
[language] varchar(40) NULL,
[blacklisted] varchar(1) NOT NULL,
[owner_uid] int NOT NULL,
[created_by] int NOT NULL,
[created] datetime NULL,
[modified] datetime NULL,
PRIMARY KEY  ([participant_id])
);


--
-- Table structure for table permissions
--
CREATE TABLE [prefix_permissions] (
[id] int NOT NULL IDENTITY (1,1) PRIMARY KEY,
[entity] varchar(50) NOT NULL,
[entity_id] int NOT NULL,
[uid] int NOT NULL,
[permission] varchar(100) NOT NULL,
[create_p] int NOT NULL default '0',
[read_p] int NOT NULL default '0',
[update_p] int NOT NULL default '0',
[delete_p] int NOT NULL default '0',
[import_p] int NOT NULL default '0',
[export_p] int NOT NULL default '0'
);
create unique index [permissions_idx2] ON [prefix_permissions] ([entity_id],[entity],[permission],[uid]);


--
-- Table structure for table plugins
--
CREATE TABLE [prefix_plugins] (
[id] int NOT NULL identity(1,1),
[name] varchar(50) NOT NULL,
[active] int NOT NULL default '0',
PRIMARY KEY  (id)
);


--
-- Table structure for table plugin_settings
--
CREATE TABLE [prefix_plugin_settings] (
[id] int NOT NULL IDENTITY(1,1),
[plugin_id] int NOT NULL,
[model] varchar(50) NULL,
[model_id] int NULL,
[key] varchar(50) NOT NULL,
[value] nvarchar(max) NULL,
PRIMARY KEY  (id),
);


--
-- Table structure for table question_attributes
--
CREATE TABLE [prefix_question_attributes] (
[qaid] int NOT NULL IDENTITY (1,1),
[qid] int NOT NULL default '0',
[attribute] nvarchar(50) NULL,
[value] nvarchar(max) NULL,
[language] varchar(20) NULL,
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
[title] nvarchar(20) NOT NULL default '',
[question] nvarchar(max) NOT NULL,
[preg] nvarchar(max) NULL,
[help] nvarchar(max) NULL,
[other] varchar(1) NOT NULL default 'N',
[mandatory] varchar(1) NULL,
[question_order] int NOT NULL,
[language] varchar(20) NOT NULL default 'en',
[scale_id] int NOT NULL default '0',
[same_default] int NOT NULL default '0',
[relevance] varchar(max) NULL,
[modulename] nvarchar(255) NULL,
PRIMARY KEY  ([qid],[language])
);

--
-- Table structure for table quota
--
CREATE TABLE [prefix_quota] (
[id] int NOT NULL IDENTITY (1,1),
[sid] int NULL,
[name] nvarchar(255) NULL,
[qlimit] int NULL,
[action] int NULL,
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
[quotals_name] nvarchar(255) NULL,
[quotals_message] nvarchar(max) NOT NULL,
[quotals_url] nvarchar(255) NULL,
[quotals_urldescrip] nvarchar(255) NULL,
PRIMARY KEY ([quotals_id])
);


--
-- Table structure for table quota_members
--
CREATE TABLE [prefix_quota_members] (
[id] int NOT NULL IDENTITY (1,1),
[sid] int NULL,
[qid] int NULL,
[quota_id] int NULL,
[code] varchar(11) NULL,
PRIMARY KEY  ([id])
);


--
-- Table structure for table saved_control
--
CREATE TABLE [prefix_saved_control] (
[scid] int NOT NULL IDENTITY (1,1),
[sid] int NOT NULL default '0',
[srid] int NOT NULL default '0',
[identifier] nvarchar(max) NOT NULL,
[access_code] nvarchar(max) NOT NULL,
[email] nvarchar(254) NULL,
[ip] varchar(max) NOT NULL,
[saved_thisstep] varchar(max) NOT NULL,
[status] varchar(1) NOT NULL default '',
[saved_date] datetime NOT NULL,
[refurl] varchar(max) NULL,
PRIMARY KEY  ([scid])
);


--
-- Table structure for table sessions
--
CREATE TABLE [prefix_sessions] (
[id] varchar(32) NOT NULL,
[expire] int NULL,
[data] VARBINARY(MAX),
PRIMARY KEY ( [id] )
);


--
-- Table structure for table settings_global
--
CREATE TABLE [prefix_settings_global] (
[stg_name] varchar(50) NOT NULL default '',
[stg_value] nvarchar(255) NOT NULL default '',
PRIMARY KEY  ([stg_name])
);


--
-- Table structure for table survey links
--
CREATE TABLE [prefix_survey_links] (
[participant_id] varchar(50) NOT NULL,
[token_id] int NOT NULL,
[survey_id] int NOT NULL,
[date_created] datetime  NULL,
[date_invited] datetime NULL,
[date_completed] datetime NULL,
PRIMARY KEY  ([participant_id],[token_id],[survey_id])
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
[admin] nvarchar(50) NULL,
[active] varchar(1) NOT NULL default 'N',
[expires] DATETIME NULL,
[startdate] DATETIME NULL,
[adminemail] nvarchar(254) NULL,
[anonymized] varchar(1) NOT NULL default 'N',
[faxto] nvarchar(20) NULL,
[format] varchar(1) NULL,
[savetimings] varchar(1) NOT NULL default 'N',
[template] nvarchar(100) default 'default',
[language] varchar(50) NULL,
[additional_languages] varchar(255) NULL,
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
[datecreated] DATETIME NULL,
[publicstatistics] varchar(1) NOT NULL default 'N',
[publicgraphs] varchar(1) NOT NULL default 'N',
[listpublic] varchar(1) NOT NULL default 'N',
[htmlemail] varchar(1) NOT NULL default 'N',
[sendconfirmation] varchar(1) NOT NULL default 'Y',
[tokenanswerspersistence] varchar(1) NOT NULL default 'N',
[assessments] varchar(1) NOT NULL default 'N',
[usecaptcha] varchar(1) NOT NULL default 'N',
[usetokens] varchar(1) NOT NULL default 'N',
[bounce_email] nvarchar(254) NULL,
[attributedescriptions] varchar(max) NULL,
[emailresponseto] varchar(max) NULL,
[emailnotificationto] varchar(max) NULL,
[tokenlength] int NOT NULL default '15',
[showxquestions] varchar(1) NULL default 'Y',
[showgroupinfo] varchar(1) NULL default 'B',
[shownoanswer] varchar(1) NULL default 'Y',
[showqnumcode] varchar(1) NULL default 'X',
[bouncetime] int NULL,
[bounceprocessing] varchar(1) NULL default 'N',
[bounceaccounttype] varchar(4) NULL,
[bounceaccounthost] varchar(200) NULL,
[bounceaccountpass] nvarchar(100) NULL,
[bounceaccountencryption] varchar(3) NULL,
[bounceaccountuser] nvarchar(200) NULL,
[showwelcome] varchar(1) NULL default 'Y',
[showprogress] varchar(1) NULL default 'Y',
[questionindex] int NOT NULL default '0',
[navigationdelay] int NOT NULL default '0',
[nokeyboard] varchar(1) NULL default 'N',
[alloweditaftercompletion] varchar(1) NULL default 'N',
[googleanalyticsstyle] varchar(1) NULL,
[googleanalyticsapikey] varchar(25) NULL,
PRIMARY KEY  ([sid])
);


--
-- Table structure for table surveys_languagesettings
--
CREATE TABLE [prefix_surveys_languagesettings] (
[surveyls_survey_id] int NOT NULL,
[surveyls_language] varchar(45) NOT NULL DEFAULT 'en',
[surveyls_title] nvarchar(200) NOT NULL,
[surveyls_description] nvarchar(max) NULL,
[surveyls_welcometext] nvarchar(max) NULL,
[surveyls_endtext] nvarchar(max) NULL,
[surveyls_url] nvarchar(max) NULL,
[surveyls_urldescription] nvarchar(255) NULL,
[surveyls_email_invite_subj] nvarchar(255) NULL,
[surveyls_email_invite] nvarchar(max) NULL,
[surveyls_email_remind_subj] nvarchar(255) NULL,
[surveyls_email_remind] nvarchar(max) NULL,
[surveyls_email_register_subj] nvarchar(255) NULL,
[surveyls_email_register] nvarchar(max) NULL,
[surveyls_email_confirm_subj] nvarchar(255) NULL,
[surveyls_email_confirm] nvarchar(max) NULL,
[surveyls_dateformat] int NOT NULL DEFAULT 1,
[surveyls_attributecaptions] nvarchar(max) NULL,
[email_admin_notification_subj] nvarchar(255) NULL,
[email_admin_notification] nvarchar(max) NULL,
[email_admin_responses_subj] nvarchar(255) NULL,
[email_admin_responses] nvarchar(max) NULL,
[surveyls_numberformat] int NOT NULL DEFAULT 0,
[attachments] nvarchar(max) NULL default NULL,
PRIMARY KEY ([surveyls_survey_id],[surveyls_language])
);


--
-- Table structure for table user_groups
--
CREATE TABLE [prefix_user_groups] (
[ugid] int NOT NULL IDENTITY (1,1) PRIMARY KEY,
[name] nvarchar(20) NOT NULL UNIQUE,
[description] nvarchar(max) NOT NULL,
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
[users_name] nvarchar(64) NOT NULL UNIQUE default '',
[password] nvarchar(max) NOT NULL,
[full_name] nvarchar(50) NOT NULL,
[parent_id] int NOT NULL,
[lang] varchar(20) NULL,
[email] nvarchar(254) NULL,
[htmleditormode] varchar(7) NULL default 'default',
[templateeditormode] varchar(7) NOT NULL default 'default',
[questionselectormode] varchar(7)  NOT NULL default 'default',
[one_time_pw] nvarchar(max) NULL,
[dateformat] int NOT NULL DEFAULT 1,
[created] datetime NULL,
[modified] datetime NULL
);


--
-- Table structure for table templates
--
CREATE TABLE [prefix_templates] (
[folder] nvarchar(50) NOT NULL,
[creator] int NOT NULL,
PRIMARY KEY  ([folder])
);

--
-- Table structure & data for boxes
--

CREATE TABLE prefix_boxes (
  [id] int NOT NULL IDENTITY,
  [position] int DEFAULT NULL ,
  [url] varchar(max) NOT NULL ,
  [title] varchar(max) NOT NULL ,
  [ico] varchar(max) DEFAULT NULL,
  [desc] varchar(max) NOT NULL ,
  [page] varchar(max) NOT NULL ,
  [usergroup] int NOT NULL,
  PRIMARY KEY ([id])
);

INSERT INTO prefix_boxes ([position], [url], [title], [ico], [desc], [page], [usergroup]) VALUES
(1, 'admin/survey/sa/newsurvey', 'Create survey', 'add', 'Create a new survey', 'welcome', '-2'),
(2, 'admin/survey/sa/listsurveys', 'List surveys', 'list', 'List available surveys', 'welcome', '-1'),
(3, 'admin/globalsettings', 'Global settings', 'settings', 'Edit global settings', 'welcome', '-2'),
(4, 'admin/update', 'ComfortUpdate', 'shield', 'Stay safe and up to date', 'welcome', '-2'),
(5, 'admin/labels/sa/view', 'Label sets', 'label','Edit label sets', 'welcome', '-2'),
(6, 'admin/templates/sa/view', 'Template editor', 'templates', 'Edit LimeSurvey templates', 'welcome', '-2');



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

--
-- Notification table
--
CREATE TABLE prefix_notifications (
    [id] int NOT NULL IDENTITY,
    [entity] nvarchar(15) NOT NULL,
    [entity_id] int NOT NULL,
    [title] nvarchar(255) NOT NULL,
    [message] nvarchar(max) NOT NULL,
    [status] nvarchar(15) NOT NULL DEFAULT 'new',
    [importance] int NOT NULL DEFAULT 1,
    [display_class] nvarchar(31) DEFAULT 'default',
    [created] datetime NOT NULL,
    [first_read] datetime DEFAULT NULL,
    PRIMARY KEY ([id])
);
CREATE INDEX [notif_index] ON [prefix_notifications] ([entity_id],[entity],[status]);

--
-- Version Info
--
INSERT INTO [prefix_settings_global] VALUES ('DBVersion', '260');
