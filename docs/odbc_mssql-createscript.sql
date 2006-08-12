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
  [code] varchar(5) COLLATE Latin1_General_CI_AS NOT NULL,
  [answer] text COLLATE Latin1_General_CI_AS NOT NULL,
  [default_value] varchar(1) COLLATE Latin1_General_CI_AS DEFAULT 'N' NOT NULL,
  [sortorder] varchar(5) COLLATE Latin1_General_CI_AS NULL
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
  [scope] varchar(5) COLLATE Latin1_General_CI_AS NOT NULL,
  [gid] int DEFAULT 0 NOT NULL,
  [name] text COLLATE Latin1_General_CI_AS NOT NULL,
  [minimum] varchar(50) COLLATE Latin1_General_CI_AS NOT NULL,
  [maximum] varchar(50) COLLATE Latin1_General_CI_AS NOT NULL,
  [message] text COLLATE Latin1_General_CI_AS NOT NULL,
  [link] text COLLATE Latin1_General_CI_AS NOT NULL
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
  [cfieldname] varchar(50) COLLATE Latin1_General_CI_AS NOT NULL,
  [method] varchar(2) COLLATE Latin1_General_CI_AS NOT NULL,
  [value] varchar(5) COLLATE Latin1_General_CI_AS NOT NULL
)
ON [PRIMARY]
GO

--
-- Structure for table groups : 
--

CREATE TABLE [dbo].[groups] (
  [gid] int IDENTITY(1, 1) NOT NULL,
  [sid] int DEFAULT 0 NOT NULL,
  [group_name] varchar(100) COLLATE Latin1_General_CI_AS NOT NULL,
  [description] text COLLATE Latin1_General_CI_AS NULL,
  [sortorder] varchar(5) COLLATE Latin1_General_CI_AS NULL
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

--
-- Structure for table labels : 
--

CREATE TABLE [dbo].[labels] (
  [lid] int DEFAULT 0 NOT NULL,
  [code] varchar(5) COLLATE Latin1_General_CI_AS NOT NULL,
  [title] varchar(100) COLLATE Latin1_General_CI_AS NOT NULL,
  [sortorder] varchar(5) COLLATE Latin1_General_CI_AS NULL
)
ON [PRIMARY]
GO

--
-- Structure for table labelsets : 
--

CREATE TABLE [dbo].[labelsets] (
  [lid] int IDENTITY(1, 1) NOT NULL,
  [label_name] varchar(100) COLLATE Latin1_General_CI_AS NOT NULL
)
ON [PRIMARY]
GO

--
-- Structure for table question_attributes : 
--

CREATE TABLE [dbo].[question_attributes] (
  [qaid] int IDENTITY(1, 1) NOT NULL,
  [qid] int NOT NULL,
  [attribute] varchar(50) COLLATE Latin1_General_CI_AS NULL,
  [value] varchar(20) COLLATE Latin1_General_CI_AS NULL
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
  [type] varchar(1) COLLATE Latin1_General_CI_AS DEFAULT 'T' NOT NULL,
  [title] varchar(20) COLLATE Latin1_General_CI_AS NOT NULL,
  [question] text COLLATE Latin1_General_CI_AS NOT NULL,
  [preg] text COLLATE Latin1_General_CI_AS NULL,
  [help] text COLLATE Latin1_General_CI_AS NULL,
  [other] varchar(1) COLLATE Latin1_General_CI_AS DEFAULT 'N' NOT NULL,
  [mandatory] varchar(1) COLLATE Latin1_General_CI_AS NULL,
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
  [identifier] text COLLATE Latin1_General_CI_AS NOT NULL,
  [access_code] text COLLATE Latin1_General_CI_AS NOT NULL,
  [email] varchar(200) COLLATE Latin1_General_CI_AS NULL,
  [ip] text COLLATE Latin1_General_CI_AS NOT NULL,
  [refurl] text COLLATE Latin1_General_CI_AS NULL,
  [saved_thisstep] text COLLATE Latin1_General_CI_AS NOT NULL,
  [status] varchar(1) COLLATE Latin1_General_CI_AS NOT NULL,
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
  [short_title] varchar(200) COLLATE Latin1_General_CI_AS NOT NULL,
  [description] text COLLATE Latin1_General_CI_AS NULL,
  [datecreated] datetime NULL,
  [admin] varchar(50) COLLATE Latin1_General_CI_AS NULL,
  [active] varchar(1) COLLATE Latin1_General_CI_AS DEFAULT 'N' NOT NULL,
  [welcome] text COLLATE Latin1_General_CI_AS NULL,
  [useexpiry] varchar(1) COLLATE Latin1_General_CI_AS DEFAULT 'N' NOT NULL,
  [expires] datetime NULL,
  [adminemail] varchar(100) COLLATE Latin1_General_CI_AS NULL,
  [private] varchar(1) COLLATE Latin1_General_CI_AS NULL,
  [faxto] varchar(20) COLLATE Latin1_General_CI_AS NULL,
  [format] varchar(1) COLLATE Latin1_General_CI_AS NULL,
  [template] varchar(100) COLLATE Latin1_General_CI_AS DEFAULT 'default' NULL,
  [url] varchar(255) COLLATE Latin1_General_CI_AS NULL,
  [urldescrip] varchar(255) COLLATE Latin1_General_CI_AS NULL,
  [language] varchar(50) COLLATE Latin1_General_CI_AS NULL,
  [datestamp] varchar(1) COLLATE Latin1_General_CI_AS DEFAULT 'N' NULL,
  [ipaddr] varchar(1) COLLATE Latin1_General_CI_AS DEFAULT 'N' NULL,
  [refurl] varchar(1) COLLATE Latin1_General_CI_AS DEFAULT 'N' NULL,
  [usecookie] varchar(1) COLLATE Latin1_General_CI_AS DEFAULT 'N' NULL,
  [notification] varchar(1) COLLATE Latin1_General_CI_AS DEFAULT '0' NULL,
  [allowregister] varchar(1) COLLATE Latin1_General_CI_AS DEFAULT 'N' NULL,
  [attribute1] varchar(255) COLLATE Latin1_General_CI_AS NULL,
  [attribute2] varchar(255) COLLATE Latin1_General_CI_AS NULL,
  [email_invite_subj] varchar(255) COLLATE Latin1_General_CI_AS NULL,
  [email_invite] text COLLATE Latin1_General_CI_AS NULL,
  [email_remind_subj] varchar(255) COLLATE Latin1_General_CI_AS NULL,
  [email_remind] text COLLATE Latin1_General_CI_AS NULL,
  [email_register_subj] varchar(255) COLLATE Latin1_General_CI_AS NULL,
  [email_register] text COLLATE Latin1_General_CI_AS NULL,
  [email_confirm_subj] varchar(255) COLLATE Latin1_General_CI_AS NULL,
  [email_confirm] text COLLATE Latin1_General_CI_AS NULL,
  [allowsave] varchar(1) COLLATE Latin1_General_CI_AS DEFAULT 'Y' NULL,
  [autonumber_start] bigint DEFAULT 19533676560910059 NULL,
  [autoredirect] varchar(1) COLLATE Latin1_General_CI_AS DEFAULT 'N' NULL,
  [allowprev] varchar(1) COLLATE Latin1_General_CI_AS DEFAULT 'Y' NULL
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

--
-- Structure for table users : 
--

CREATE TABLE [dbo].[users] (
  [user] varchar(20) COLLATE Latin1_General_CI_AS NOT NULL,
  [password] varchar(20) COLLATE Latin1_General_CI_AS NOT NULL,
  [security] varchar(10) COLLATE Latin1_General_CI_AS NOT NULL
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

