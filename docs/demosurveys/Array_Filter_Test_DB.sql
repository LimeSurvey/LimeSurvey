#<pre>
# SURVEYOR SURVEY DUMP
#
# This is a dumped survey from the PHPSurveyor Script
# http://www.phpsurveyor.org/

# NEW TABLE
# SURVEYS TABLE
#
INSERT INTO surveys (`sid`, `short_title`, `description`, `admin`, `active`, `welcome`, `expires`, `adminemail`, `private`, `faxto`, `format`, `template`, `url`, `urldescrip`, `language`, `datestamp`, `usecookie`, `notification`, `allowregister`, `attribute1`, `attribute2`, `email_invite_subj`, `email_invite`, `email_remind_subj`, `email_remind`, `email_register_subj`, `email_register`, `email_confirm_subj`, `email_confirm`, `allowsave`, `autonumber_start`, `autoredirect`, `allowprev`, `ipaddr`, `useexpiry`, `refurl`, `datecreated`) VALUES ('36826', 'FIlter Test', 'A Survey to Test Array Filter', '', 'N', '', '1980-01-01', '', 'Y', '', 'G', 'default', '', '', 'english', 'N', 'N', '0', 'N', '', '', 'Invitation to participate in survey', 'Dear {FIRSTNAME},\n\n\n\nYou have been invited to participate in a survey.\n\n\n\nThe survey is titled:\n\n\"{SURVEYNAME}\"\n\n\n\n\"{SURVEYDESCRIPTION}\"\n\n\n\nTo participate, please click on the link below.\n\n\n\nSincerely,\n\n\n\n{ADMINNAME} ({ADMINEMAIL})\n\n\n\n----------------------------------------------\n\nClick here to do the survey:\n\n{SURVEYURL}', 'Reminder to participate in survey', 'Dear {FIRSTNAME},\n\n\n\nRecently we invited you to participate in a survey.\n\n\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\n\n\nThe survey is titled:\n\n\"{SURVEYNAME}\"\n\n\n\n\"{SURVEYDESCRIPTION}\"\n\n\n\nTo participate, please click on the link below.\n\n\n\nSincerely,\n\n\n\n{ADMINNAME} ({ADMINEMAIL})\n\n\n\n----------------------------------------------\n\nClick here to do the survey:\n\n{SURVEYURL}', 'Survey Registration Confirmation', 'Dear {FIRSTNAME},\n\n\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\n\n\nTo complete this survey, click on the following URL:\n\n\n\n{SURVEYURL}\n\n\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}.', 'Confirmation of completed survey', 'Dear {FIRSTNAME},\n\n\n\nThis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\n\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\n\n\nSincerely,\n\n\n\n{ADMINNAME}', 'Y', '0', 'N', 'Y', 'N', 'N', 'N', '');

# NEW TABLE
# GROUPS TABLE
#
INSERT INTO groups (`gid`, `sid`, `group_name`, `group_order`, `description`, `sortorder`) VALUES ('44', '36826', 'B Group', '', '', '');

# NEW TABLE
# QUESTIONS TABLE
#
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('205', '36826', '44', 'M', '0001', 'Multi Selection', '', '', 'Y', 'Y', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('206', '36826', '44', 'A', '0002', '5 Point Array Filtered', '', '', 'N', 'Y', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('207', '36826', '44', 'B', '0003', '10 Point Array Filtered', '', '', 'N', 'Y', '0');
INSERT INTO questions (`qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `lid`) VALUES ('208', '36826', '44', 'F', '0004', 'Flixible Array Filtered', '', '', 'N', 'Y', '8');

# NEW TABLE
# ANSWERS TABLE
#
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('205', '1', '1', 'N', '00000');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('205', '2', '2', 'N', '00001');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('205', '3', '3', 'N', '00002');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('205', '4', '4', 'N', '00003');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('205', '5', '5', 'N', '00004');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('206', '1', '1', 'N', '00000');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('206', '2', '2', 'N', '00001');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('206', '3', '3', 'N', '00002');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('206', '4', '4', 'N', '00003');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('206', '5', '5', 'N', '00004');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('207', '1', '1', 'N', '00000');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('207', '2', '2', 'N', '00001');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('207', '3', '3', 'N', '00002');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('207', '4', '4', 'N', '00003');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('207', '5', '5', 'N', '00004');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('208', '1', '1', 'N', '00000');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('208', '2', '2', 'N', '00001');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('208', '3', '3', 'N', '00002');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('208', '4', '4', 'N', '00003');
INSERT INTO answers (`qid`, `code`, `answer`, `default_value`, `sortorder`) VALUES ('208', '5', '5', 'N', '00004');

# NEW TABLE
# CONDITIONS TABLE
#

# NEW TABLE
# LABELSETS TABLE
#
INSERT INTO labelsets (`lid`, `label_name`) VALUES ('8', 'Better/Same/Worse');

# NEW TABLE
# LABELS TABLE
#
INSERT INTO labels (`lid`, `code`, `title`, `sortorder`) VALUES ('8', '1', 'Better', '00000');
INSERT INTO labels (`lid`, `code`, `title`, `sortorder`) VALUES ('8', '2', 'Same', '00001');
INSERT INTO labels (`lid`, `code`, `title`, `sortorder`) VALUES ('8', '3', 'Worse', '00002');

# NEW TABLE
# QUESTION_ATTRIBUTES TABLE
#
INSERT INTO question_attributes (`qaid`, `qid`, `attribute`, `value`) VALUES ('6', '208', 'array_filter', '0001');
INSERT INTO question_attributes (`qaid`, `qid`, `attribute`, `value`) VALUES ('5', '207', 'array_filter', '0001');
INSERT INTO question_attributes (`qaid`, `qid`, `attribute`, `value`) VALUES ('4', '206', 'array_filter', '0001');

# NEW TABLE
# ASSESSMENTS TABLE
#
#</pre>
