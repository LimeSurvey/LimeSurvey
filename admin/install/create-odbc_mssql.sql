--
-- Structure for table answers :
--

CREATE TABLE [dbo].[prefix_answers] (
  [qid] int DEFAULT 0 NOT NULL,
  [code] nvarchar(5)  NOT NULL,
  [answer] ntext  NOT NULL,
  [default_value] nvarchar(1)  DEFAULT 'N' NOT NULL,
  [sortorder] nvarchar(5)  NULL
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
;

--
-- Structure for table assessments :
--

CREATE TABLE [dbo].[prefix_assessments] (
  [id] int IDENTITY(1, 1) NOT NULL,
  [sid] int DEFAULT 0 NOT NULL,
  [scope] nvarchar(5)  NOT NULL,
  [gid] int DEFAULT 0 NOT NULL,
  [name] text  NOT NULL,
  [minimum] nvarchar(50)  NOT NULL,
  [maximum] nvarchar(50)  NOT NULL,
  [message] ntext  NOT NULL,
  [link] ntext  NOT NULL
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
;

--
-- Structure for table conditions :
--

CREATE TABLE [dbo].[prefix_conditions] (
  [cid] int IDENTITY(1, 1) NOT NULL,
  [qid] int DEFAULT 0 NOT NULL,
  [cqid] int DEFAULT 0 NOT NULL,
  [cfieldname] nvarchar(50)  NOT NULL,
  [method] nvarchar(2)  NOT NULL,
  [value] nvarchar(5)  NOT NULL
)
ON [PRIMARY]
;

--
-- Structure for table groups :
--

CREATE TABLE [dbo].[prefix_groups] (
  [gid] int IDENTITY(1, 1) NOT NULL,
  [sid] int DEFAULT 0 NOT NULL,
  [group_name] nvarchar(100)  NOT NULL,
  [description] ntext  NULL,
  [sortorder] nvarchar(5)  NULL
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
;

--
-- Structure for table labels :
--

CREATE TABLE [dbo].[prefix_labels] (
  [lid] int DEFAULT 0 NOT NULL,
  [code] nvarchar(5)  NOT NULL,
  [title] nvarchar(100)  NOT NULL,
  [sortorder] nvarchar(5)  NULL
)
ON [PRIMARY]
;

--
-- Structure for table labelsets :
--

CREATE TABLE [dbo].[prefix_labelsets] (
  [lid] int IDENTITY(1, 1) NOT NULL,
  [label_name] nvarchar(100)  NOT NULL
)
ON [PRIMARY]
;

--
-- Structure for table question_attributes :
--

CREATE TABLE [dbo].[prefix_question_attributes] (
  [qaid] int IDENTITY(1, 1) NOT NULL,
  [qid] int NOT NULL,
  [attribute] nvarchar(50)  NULL,
  [value] nvarchar(20)  NULL
)
ON [PRIMARY]
;


CREATE TABLE [dbo].[prefix_settings_global] (
  [stg_name] nvarchar(50) NOT NULL,
  [stg_value] nvarchar(255) NULL
)
ON [PRIMARY]
;


--
-- Structure for table questions :
--

CREATE TABLE [dbo].[prefix_questions] (
  [qid] int IDENTITY(1, 1) NOT NULL,
  [sid] int DEFAULT 0 NOT NULL,
  [gid] int DEFAULT 0 NOT NULL,
  [type] nvarchar(1)  DEFAULT 'T' NOT NULL,
  [title] nvarchar(20)  NOT NULL,
  [question] ntext  NOT NULL,
  [preg] ntext  NULL,
  [help] ntext  NULL,
  [other] nvarchar(1)  DEFAULT 'N' NOT NULL,
  [mandatory] nvarchar(1)  NULL,
  [lid] int DEFAULT 0 NOT NULL
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
;

--
-- Structure for table saved_control :
--

CREATE TABLE [dbo].[prefix_saved_control] (
  [scid] int IDENTITY(1, 1) NOT NULL,
  [sid] int CONSTRAINT [DF__saved_contr__sid__108B795B] DEFAULT 0 NOT NULL,
  [srid] int CONSTRAINT [DF__saved_cont__srid__117F9D94] DEFAULT 0 NOT NULL,
  [identifier] ntext  NOT NULL,
  [access_code] ntext  NOT NULL,
  [email] nvarchar(200)  NULL,
  [ip] ntext  NOT NULL,
  [refurl] ntext  NULL,
  [saved_thisstep] ntext  NOT NULL,
  [status] nvarchar(1)  NOT NULL,
  [saved_date] datetime NULL
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
;

--
-- Structure for table surveys :
--

CREATE TABLE [dbo].[prefix_surveys] (
  [sid] int NOT NULL,
  [short_title] nvarchar(200)  NOT NULL,
  [description] ntext  NULL,
  [datecreated] datetime NULL,
  [admin] nvarchar(50)  NULL,
  [active] nvarchar(1)  DEFAULT 'N' NOT NULL,
  [welcome] ntext  NULL,
  [useexpiry] nvarchar(1)  DEFAULT 'N' NOT NULL,
  [expires] datetime NULL,
  [adminemail] nvarchar(100)  NULL,
  [private] nvarchar(1)  NULL,
  [faxto] nvarchar(20)  NULL,
  [format] nvarchar(1)  NULL,
  [template] nvarchar(100)  DEFAULT 'default' NULL,
  [url] nvarchar(255)  NULL,
  [urldescrip] nvarchar(255)  NULL,
  [language] nvarchar(50)  NULL,
  [datestamp] nvarchar(1)  DEFAULT 'N' NULL,
  [ipaddr] nvarchar(1)  DEFAULT 'N' NULL,
  [refurl] nvarchar(1)  DEFAULT 'N' NULL,
  [usecookie] nvarchar(1)  DEFAULT 'N' NULL,
  [notification] nvarchar(1)  DEFAULT '0' NULL,
  [allowregister] nvarchar(1)  DEFAULT 'N' NULL,
  [attribute1] nvarchar(255)  NULL,
  [attribute2] nvarchar(255)  NULL,
  [email_invite_subj] nvarchar(255)  NULL,
  [email_invite] ntext  NULL,
  [email_remind_subj] nvarchar(255)  NULL,
  [email_remind] ntext  NULL,
  [email_register_subj] nvarchar(255)  NULL,
  [email_register] ntext  NULL,
  [email_confirm_subj] nvarchar(255)  NULL,
  [email_confirm] ntext  NULL,
  [allowsave] nvarchar(1)  DEFAULT 'Y' NULL,
  [autonumber_start] bigint DEFAULT 19533676560910059 NULL,
  [autoredirect] nvarchar(1)  DEFAULT 'N' NULL,
  [allowprev] nvarchar(1)  DEFAULT 'Y' NULL,
  [groupset] nvarchar(255)  NULL
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
;

--
-- Structure for table users :
--

CREATE TABLE [dbo].[prefix_users] (
  [user] nvarchar(20)  NOT NULL,
  [password] nvarchar(20)  NOT NULL,
  [security] nvarchar(10)  NOT NULL
)
ON [PRIMARY]
;

--
-- Definition for indices :
--

ALTER TABLE [dbo].[prefix_assessments]
ADD CONSTRAINT [PK_assessments]
PRIMARY KEY CLUSTERED ([id])
ON [PRIMARY]
;

ALTER TABLE [dbo].[prefix_conditions]
ADD CONSTRAINT [PK_conditions]
PRIMARY KEY CLUSTERED ([cid])
ON [PRIMARY]
;

ALTER TABLE [dbo].[prefix_groups]
ADD CONSTRAINT [PK_groups]
PRIMARY KEY CLUSTERED ([gid])
ON [PRIMARY]
;

ALTER TABLE [dbo].[prefix_labelsets]
ADD CONSTRAINT [PK_labelsets]
PRIMARY KEY CLUSTERED ([lid])
ON [PRIMARY]
;

ALTER TABLE [dbo].[prefix_question_attributes]
ADD CONSTRAINT [PK_question_attributes]
PRIMARY KEY CLUSTERED ([qaid])
ON [PRIMARY]
;

ALTER TABLE [dbo].[prefix_questions]
ADD CONSTRAINT [PK_questions]
PRIMARY KEY CLUSTERED ([qid])
ON [PRIMARY]
;

ALTER TABLE [dbo].[prefix_saved_control]
ADD CONSTRAINT [PK_saved_control]
PRIMARY KEY CLUSTERED ([scid])
ON [PRIMARY]
;

ALTER TABLE [dbo].[prefix_surveys]
ADD CONSTRAINT [PK_surveys]
PRIMARY KEY CLUSTERED ([sid])
ON [PRIMARY];

ALTER TABLE [dbo].[prefix_settings_global]
ADD CONSTRAINT [PK_settings_global]
PRIMARY KEY CLUSTERED ([stg_name])
ON [PRIMARY];

-- if you change the database scheme then change this version too and implement the changes in the upgrade_*.php too

INSERT INTO [prefix_settings_global] values('DBVersion','109');
