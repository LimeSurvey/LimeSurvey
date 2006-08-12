-- SQL Manager 2005 Lite for SQL Server (2.4.0.1)
-- ---------------------------------------
-- Host      : nobodys
-- Database  : phpsurveyor
-- Version:  : Microsoft SQL Server  8.00.760


--
-- Structure for table answers : 
--

CREATE TABLE [dbo].[answers] (
  [qid] int DEFAULT 0 NOT NULL,
  [code] varchar(5)  NOT NULL,
  [answer] text  NOT NULL,
  [default_value] varchar(1)  DEFAULT 'N' NOT NULL,
  [sortorder] varchar(5)  NULL
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

--
-- Structure for table assessments : 
--

CREATE TABLE [dbo].[assessments] (
  [id] int IDENTITY(1, 1) NOT NULL,
  [sid] int DEFAULT 0 NOT NULL,
  [scope] varchar(5)  NOT NULL,
  [gid] int DEFAULT 0 NOT NULL,
  [name] text  NOT NULL,
  [minimum] varchar(50)  NOT NULL,
  [maximum] varchar(50)  NOT NULL,
  [message] text  NOT NULL,
  [link] text  NOT NULL
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

--
-- Structure for table conditions : 
--

CREATE TABLE [dbo].[conditions] (
  [cid] int IDENTITY(1, 1) NOT NULL,
  [qid] int DEFAULT 0 NOT NULL,
  [cqid] int DEFAULT 0 NOT NULL,
  [cfieldname] varchar(50)  NOT NULL,
  [method] varchar(2)  NOT NULL,
  [value] varchar(5)  NOT NULL
)
ON [PRIMARY]
GO

--
-- Structure for table groups : 
--

CREATE TABLE [dbo].[groups] (
  [gid] int IDENTITY(1, 1) NOT NULL,
  [sid] int DEFAULT 0 NOT NULL,
  [group_name] varchar(100)  NOT NULL,
  [description] text  NULL,
  [sortorder] varchar(5)  NULL
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

--
-- Structure for table labels : 
--

CREATE TABLE [dbo].[labels] (
  [lid] int DEFAULT 0 NOT NULL,
  [code] varchar(5)  NOT NULL,
  [title] varchar(100)  NOT NULL,
  [sortorder] varchar(5)  NULL
)
ON [PRIMARY]
GO

--
-- Structure for table labelsets : 
--

CREATE TABLE [dbo].[labelsets] (
  [lid] int IDENTITY(1, 1) NOT NULL,
  [label_name] varchar(100)  NOT NULL
)
ON [PRIMARY]
GO

--
-- Structure for table question_attributes : 
--

CREATE TABLE [dbo].[question_attributes] (
  [qaid] int IDENTITY(1, 1) NOT NULL,
  [qid] int NOT NULL,
  [attribute] varchar(50)  NULL,
  [value] varchar(20)  NULL
)
ON [PRIMARY]
GO

--
-- Structure for table questions : 
--

CREATE TABLE [dbo].[questions] (
  [qid] int IDENTITY(1, 1) NOT NULL,
  [sid] int DEFAULT 0 NOT NULL,
  [gid] int DEFAULT 0 NOT NULL,
  [type] varchar(1)  DEFAULT 'T' NOT NULL,
  [title] varchar(20)  NOT NULL,
  [question] text  NOT NULL,
  [preg] text  NULL,
  [help] text  NULL,
  [other] varchar(1)  DEFAULT 'N' NOT NULL,
  [mandatory] varchar(1)  NULL,
  [lid] int DEFAULT 0 NOT NULL
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

--
-- Structure for table saved_control : 
--

CREATE TABLE [dbo].[saved_control] (
  [scid] int IDENTITY(1, 1) NOT NULL,
  [sid] int CONSTRAINT [DF__saved_contr__sid__108B795B] DEFAULT 0 NOT NULL,
  [srid] int CONSTRAINT [DF__saved_cont__srid__117F9D94] DEFAULT 0 NOT NULL,
  [identifier] text  NOT NULL,
  [access_code] text  NOT NULL,
  [email] varchar(200)  NULL,
  [ip] text  NOT NULL,
  [refurl] text  NULL,
  [saved_thisstep] text  NOT NULL,
  [status] varchar(1)  NOT NULL,
  [saved_date] datetime NULL
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

--
-- Structure for table surveys : 
--

CREATE TABLE [dbo].[surveys] (
  [sid] int NOT NULL,
  [short_title] varchar(200)  NOT NULL,
  [description] text  NULL,
  [datecreated] datetime NULL,
  [admin] varchar(50)  NULL,
  [active] varchar(1)  DEFAULT 'N' NOT NULL,
  [welcome] text  NULL,
  [useexpiry] varchar(1)  DEFAULT 'N' NOT NULL,
  [expires] datetime NULL,
  [adminemail] varchar(100)  NULL,
  [private] varchar(1)  NULL,
  [faxto] varchar(20)  NULL,
  [format] varchar(1)  NULL,
  [template] varchar(100)  DEFAULT 'default' NULL,
  [url] varchar(255)  NULL,
  [urldescrip] varchar(255)  NULL,
  [language] varchar(50)  NULL,
  [datestamp] varchar(1)  DEFAULT 'N' NULL,
  [ipaddr] varchar(1)  DEFAULT 'N' NULL,
  [refurl] varchar(1)  DEFAULT 'N' NULL,
  [usecookie] varchar(1)  DEFAULT 'N' NULL,
  [notification] varchar(1)  DEFAULT '0' NULL,
  [allowregister] varchar(1)  DEFAULT 'N' NULL,
  [attribute1] varchar(255)  NULL,
  [attribute2] varchar(255)  NULL,
  [email_invite_subj] varchar(255)  NULL,
  [email_invite] text  NULL,
  [email_remind_subj] varchar(255)  NULL,
  [email_remind] text  NULL,
  [email_register_subj] varchar(255)  NULL,
  [email_register] text  NULL,
  [email_confirm_subj] varchar(255)  NULL,
  [email_confirm] text  NULL,
  [allowsave] varchar(1)  DEFAULT 'Y' NULL,
  [autonumber_start] bigint DEFAULT 19533676560910059 NULL,
  [autoredirect] varchar(1)  DEFAULT 'N' NULL,
  [allowprev] varchar(1)  DEFAULT 'Y' NULL
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

--
-- Structure for table users : 
--

CREATE TABLE [dbo].[users] (
  [user] varchar(20)  NOT NULL,
  [password] varchar(20)  NOT NULL,
  [security] varchar(10)  NOT NULL
)
ON [PRIMARY]
GO

--
-- Definition for indices : 
--

ALTER TABLE [dbo].[assessments]
ADD CONSTRAINT [PK_assessments] 
PRIMARY KEY CLUSTERED ([id])
ON [PRIMARY]
GO

ALTER TABLE [dbo].[conditions]
ADD CONSTRAINT [PK_conditions] 
PRIMARY KEY CLUSTERED ([cid])
ON [PRIMARY]
GO

ALTER TABLE [dbo].[groups]
ADD CONSTRAINT [PK_groups] 
PRIMARY KEY CLUSTERED ([gid])
ON [PRIMARY]
GO

ALTER TABLE [dbo].[labelsets]
ADD CONSTRAINT [PK_labelsets] 
PRIMARY KEY CLUSTERED ([lid])
ON [PRIMARY]
GO

ALTER TABLE [dbo].[question_attributes]
ADD CONSTRAINT [PK_question_attributes] 
PRIMARY KEY CLUSTERED ([qaid])
ON [PRIMARY]
GO

ALTER TABLE [dbo].[questions]
ADD CONSTRAINT [PK_questions] 
PRIMARY KEY CLUSTERED ([qid])
ON [PRIMARY]
GO

ALTER TABLE [dbo].[saved_control]
ADD CONSTRAINT [PK_saved_control] 
PRIMARY KEY CLUSTERED ([scid])
ON [PRIMARY]
GO

ALTER TABLE [dbo].[surveys]
ADD CONSTRAINT [PK_surveys] 
PRIMARY KEY CLUSTERED ([sid])
ON [PRIMARY]
GO

