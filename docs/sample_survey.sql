#<pre>
# SURVEYOR SURVEY DUMP
#
# This is a dumped survey from the PHPSurveyor Script
# http://www.phpsurveyor.org/

# NEW TABLE
# SURVEYS TABLE
#
INSERT INTO surveys (`sid`, `short_title`, `description`, `admin`, `active`, `welcome`, `expires`, `adminemail`, `private`, `faxto`, `format`, `template`, `url`, `urldescrip`, `language`, `datestamp`, `usecookie`, `notification`, `allowregister`, `attribute1`, `attribute2`, `email_invite_subj`, `email_invite`, `email_remind_subj`, `email_remind`, `email_register_subj`, `email_register`, `email_confirm_subj`, `email_confirm`, `allowsave`, `autonumber_start`, `autoredirect`, `allowprev`, `ipaddr`) VALUES ('40', 'Sample Survey', 'This is a sample survey showing of all the question types you can use in PHPSurveyor.', 'Administratorname', 'N', 'This is the welcome text for the survey!\r<br />You can can edit it in the survey properties.', '0000-00-00', 'admin@localhost', 'Y', '000-00000000', 'G', 'default', '', '', 'english', 'Y', 'N', '0', 'N', '', '', 'Invitation to participate in survey', 'Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\nThe survey is titled:\n&quot;{SURVEYNAME}&quot;\n\n&quot;{SURVEYDESCRIPTION}&quot;\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}', 'Reminder to participate in survey', 'Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n&quot;{SURVEYNAME}&quot;\n\n&quot;{SURVEYDESCRIPTION}&quot;\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}', 'Survey Registration Confirmation', 'Dear {FIRSTNAME},\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\nTo complete this survey, click on the following URL:\n\n{SURVEYURL}\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}.', 'Confirmation of completed survey', 'Dear {FIRSTNAME},\n\nThis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}', 'Y', '0', 'N', 'Y', 'N');

# NEW TABLE
# GROUPS TABLE
#
INSERT INTO groups (`gid`, `sid`, `group_name`, `description`) VALUES ('37', '40', 'Array Questions', 'This is a group with the different array questions.');
INSERT INTO groups (`gid`, `sid`, `group_name`, `description`) VALUES ('35', '40', 'Text Questions', '');
INSERT INTO groups (`gid`, `sid`, `group_name`, `description`) VALUES ('36', '40', 'Mask Questions', 'This is the group description.');
INSERT INTO groups (`gid`, `sid`, `group_name`, `description`) VALUES ('33', '40', 'Single Choice Questions', '');
INSERT INTO groups (`gid`, `sid`, `group_name`, `description`) VALUES ('34', '40', 'Multiple Choice Questions', 'This group consist only of Multiple Choice questions.');

# NEW TABLE
# QUESTIONS TABLE
#
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('168', '40', '36', 'X', 'X', 'Type X - Boilerplate question', '', 'This is the boilerplate question type. It\'s not so much a question but a simple text display.', 'N', 'Y', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('170', '40', '36', 'G', 'G', 'Type G - Gender question?', '', 'This is a standard question aksing for the participiant\'s sex.', 'N', 'N', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('171', '40', '36', 'N', 'N', 'Type N - Numerical Input Question', '', '', 'N', 'N', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('172', '40', '36', 'R', 'R', 'Type R - Ranking Question', '', '', 'N', 'N', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('173', '40', '36', 'Y', 'Y', 'Type Y - Yes/No Question', '', '', 'N', 'N', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('174', '40', '37', 'A', 'A', 'Type A - Array 5 Point Choice Question', '', '', 'N', 'N', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('175', '40', '37', 'B', 'B', 'Type B - Array 10 point choice Question', '', 'This is the help text for this question. ', 'N', 'N', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('176', '40', '37', 'C', 'C', 'Type C - Array Yes/No/Uncertain Question', '', '', 'N', 'N', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('177', '40', '37', 'E', 'E', 'Type E - Array Increase/Same/Decrease Question', '', 'This is the questions help text.', 'N', 'N', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('178', '40', '37', 'F', 'F', 'Type F - Array using Flexible Labels question', '', 'This is a array using a flexible label set. Flexible labels sets can be created with as many answer as you like to. We created only one for this survey that will be re-used over and over.', 'N', 'N', '6');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('179', '40', '37', 'H', 'H', 'Type H - Array Flexible Labels by Column question', '', 'This is the same question type as before just the orientation has changed', 'N', 'N', '6');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('169', '40', '36', 'D', 'D', 'Type D - Date question?', '', 'Helptext', 'N', 'N', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('161', '40', '33', 'Z', 'Z', 'Type Z - List Flexible Labels Dropdown', '', 'This question is reusing the same label set as some of the array questions.', 'N', 'N', '6');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('162', '40', '34', 'P', 'P', 'Type P: Multiple Options Question with Comments ', '', 'This is a Multiple Options Question with the ability to comment it.', 'Y', 'N', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('163', '40', '34', 'M', 'M', 'Type M - Multiple Options Question', '', 'This is a type M multiple Options questions.', 'Y', 'N', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('164', '40', '35', 'Q', 'Q', 'Type Q - Multiple Short Text Question', '', 'This is a Multiple Short Text Question', 'N', 'N', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('165', '40', '35', 'T', 'T', 'Type T - Long Text Question?', '', 'Helptext', 'N', 'N', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('166', '40', '35', 'S', 'S', 'Type S - Short Free Text?', '', 'helptext', 'N', 'N', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('167', '40', '35', 'U', 'U', 'Type U - Huge Free text?', '', 'Helptext', 'N', 'N', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('160', '40', '33', 'W', 'W', 'Type W - List Flexible Labels Dropdown question type', '', 'This question is reusing the same label set as some of the array questions.', 'N', 'N', '6');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('159', '40', '33', 'O', 'O', 'Type O : List with Comment Question', '', '', 'N', 'N', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('158', '40', '33', 'L', 'L', 'Type L - List Radio qeustion', '', '', 'Y', 'N', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('156', '40', '33', '5', '5', 'Type 5 - 5 point choice', '', 'Help2', 'N', 'N', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('157', '40', '33', '!', '!', 'Type ! - List Dropdown Question', '', '', 'Y', 'N', '0');

# NEW TABLE
# ANSWERS TABLE
#
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('172', 'R1', 'Red', 'N', '00000');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('172', 'R2', 'Green', 'N', '00001');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('172', 'R3', 'Blue', 'N', '00002');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('174', 'A1', 'FBI', 'N', '00000');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('174', 'A2', 'CIA', 'N', '00001');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('174', 'A3', 'G5', 'N', '00002');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('174', 'A4', 'NASA', 'N', '00003');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('175', 'B1', 'Darth Vader', 'N', '00000');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('175', 'B2', 'Luke Skywalker', 'N', '00001');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('175', 'B3', 'Princess Leia', 'N', '00002');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('175', 'B4', 'Jabba the Hut', 'N', '00003');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('176', 'C1', 'I am blonde', 'N', '00000');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('176', 'C2', 'I am blue', 'N', '00001');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('176', 'C3', 'I am pissed', 'N', '00002');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('176', 'C4', 'I am drunk', 'N', '00003');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('177', 'E1', 'Pain', 'N', '00000');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('177', 'E2', 'Pleasure', 'N', '00001');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('177', 'E3', 'Luck', 'N', '00002');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('177', 'E4', 'Happiness', 'N', '00003');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('178', 'F1', 'Deannan Troi', 'N', '00000');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('178', 'F2', 'Wesley Crusher', 'N', '00001');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('178', 'F3', 'Jean-Luc Picard', 'N', '00002');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('178', 'F4', 'Seven of  Nine', 'N', '00003');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('179', 'H1', 'Deannan Troi', 'N', '00000');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('179', 'H2', 'Wesley Crusher', 'N', '00001');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('179', 'H3', 'Data', 'N', '00002');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('179', 'H4', 'Jean-Luc Picard', 'N', '00003');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('162', 'P1', 'I am glad', 'N', '00000');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('162', 'P2', 'I don\'t care', 'N', '00001');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('162', 'P3', 'I am unhappy', 'N', '00002');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('163', 'M1', 'Yes', 'N', '00000');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('163', 'M2', 'No', 'N', '00001');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('163', 'M3', 'Maybe', 'N', '00002');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('164', 'Q1', 'blue', 'N', '00000');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('164', 'Q2', 'red', 'N', '00001');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('164', 'Q3', 'green', 'N', '00002');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('159', 'O1', 'Red', 'N', '00000');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('159', 'O2', 'Green', 'N', '00001');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('159', 'O3', 'Blue', 'N', '00002');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('158', 'L1', ' Green', 'N', '00000');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('158', 'L2', 'Red', 'N', '00001');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('158', 'L3', 'Blue', 'N', '00002');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('157', 'EC1', 'Green', 'N', '00000');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('157', 'EC2', 'Red', 'N', '00001');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('157', 'EC3', 'Blue', 'N', '00002');

# NEW TABLE
# CONDITIONS TABLE
#

# NEW TABLE
# LABELSETS TABLE
#
INSERT INTO labelsets (`lid`, `label_name`) VALUES ('6', 'Test Labelset');

# NEW TABLE
# LABELS TABLE
#
INSERT INTO labels (`lid`, `code`, `title`, `sortorder`) VALUES ('6', 'TL1', '6 - Like it very much', '00000');
INSERT INTO labels (`lid`, `code`, `title`, `sortorder`) VALUES ('6', 'TL6', '1 - Dont like it at all', '00005');
INSERT INTO labels (`lid`, `code`, `title`, `sortorder`) VALUES ('6', 'TL5', '2', '00004');
INSERT INTO labels (`lid`, `code`, `title`, `sortorder`) VALUES ('6', 'TL4', '3', '00003');
INSERT INTO labels (`lid`, `code`, `title`, `sortorder`) VALUES ('6', 'TL3', '4', '00002');
INSERT INTO labels (`lid`, `code`, `title`, `sortorder`) VALUES ('6', 'TL2', '5', '00001');

# NEW TABLE
# QUESTION_ATTRIBUTES TABLE
#

# NEW TABLE
# ASSESSMENTS TABLE
#
#</pre>
