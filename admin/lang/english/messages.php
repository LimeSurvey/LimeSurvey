<?php


//BUTTON BAR TITLES
define("_ADMINISTRATION", "Administration");
define("_SURVEY", "Survey");
define("_GROUP", "Group");
define("_QUESTION", "Question");
define("_ANSWERS", "Answers");
define("_CONDITIONS", "Conditions");
define("_HELP", "Help");
define("_USERCONTROL", "User Control");
define("_ACTIVATE", "Activate Survey");
define("_DEACTIVATE", "Deactivate Survey");
define("_CHECKFIELDS", "Check Database Fields");
define("_CREATEDB", "Create Database");
define("_CREATESURVEY", "Create Survey"); //New for 0.98rc4
define("_SETUP", "PHPSurveyor Setup");
define("_DELETESURVEY", "Delete Survey");
define("_EXPORTQUESTION", "Export Question");
define("_EXPORTSURVEY", "Export Survey");
define("_EXPORTLABEL", "Export Label Set");
define("_IMPORTQUESTION", "Import Question");
define("_IMPORTGROUP", "Import Group"); //New for 0.98rc5
define("_IMPORTSURVEY", "Import Survey");
define("_IMPORTLABEL", "Import Label Set");
define("_EXPORTRESULTS", "Export Responses");
define("_BROWSERESPONSES", "Browse Responses");
define("_BROWSESAVED", "Browse Saved Responses");
define("_STATISTICS", "Quick Statistics");
define("_VIEWRESPONSE", "View Response");
define("_VIEWCONTROL", "Data View Control");
define("_DATAENTRY", "Data Entry");
define("_TOKENCONTROL", "Token Control");
define("_TOKENDBADMIN", "Token Database Administration Options");
define("_DROPTOKENS", "Delete Tokens Table");
define("_EMAILINVITE", "Email Invitation");
define("_EMAILREMIND", "Email Reminder");
define("_TOKENIFY", "Create Tokens");
define("_UPLOADCSV", "Upload CSV File");
define("_LABELCONTROL", "Label Sets Administration"); //NEW with 0.98rc3
define("_LABELSET", "Label Set"); //NEW with 0.98rc3
define("_LABELANS", "Labels"); //NEW with 0.98rc3
define("_OPTIONAL", "Optional"); //NEW with 0.98finalRC1

//DROPDOWN HEADINGS
define("_SURVEYS", "Surveys");
define("_GROUPS", "Groups");
define("_QUESTIONS", "Questions");
define("_QBYQ", "Question by Question");
define("_GBYG", "Group by Group");
define("_SBYS", "All in one");
define("_LABELSETS", "Sets"); //New with 0.98rc3

//BUTTON MOUSEOVERS
//administration bar
define("_A_HOME_BT", "Default Administration Page");
define("_A_SECURITY_BT", "Modify Security Settings");
define("_A_BADSECURITY_BT", "Activate Security");
define("_A_CHECKDB_BT", "Check Database");
define("_A_DELETE_BT", "Delete Entire Survey");
define("_A_ADDSURVEY_BT", "Create or Import New Survey");
define("_A_HELP_BT", "Show Help");
define("_A_CHECKSETTINGS", "Check Settings");
define("_A_BACKUPDB_BT", "Backup Entire Database"); //New for 0.98rc10
define("_A_TEMPLATES_BT", "Template Editor"); //New for 0.98rc9
//Survey bar
define("_S_ACTIVE_BT", "This survey is currently active");
define("_S_INACTIVE_BT", "This survey is not currently active");
define("_S_ACTIVATE_BT", "Activate this Survey");
define("_S_DEACTIVATE_BT", "De-activate this Survey");
define("_S_CANNOTACTIVATE_BT", "Cannot Activate this Survey");
define("_S_DOSURVEY_BT", "Do Survey");
define("_S_DATAENTRY_BT", "Dataentry Screen for Survey");
define("_S_PRINTABLE_BT", "Printable Version of Survey");
define("_S_EDIT_BT", "Edit Current Survey");
define("_S_DELETE_BT", "Delete Current Survey");
define("_S_EXPORT_BT", "Export this Survey");
define("_S_BROWSE_BT", "Browse Responses for this Survey");
define("_S_TOKENS_BT", "Activate/Edit Tokens for this Survey");
define("_S_ADDGROUP_BT", "Add New Group to Survey");
define("_S_MINIMISE_BT", "Hide Details of this Survey");
define("_S_MAXIMISE_BT", "Show Details of this Survey");
define("_S_CLOSE_BT", "Close this Survey");
define("_S_SAVED_BT", "View Saved but not submitted Responses"); //New in 0.99dev01
define("_S_ASSESSMENT_BT", "Set assessment rules"); //New in  0.99dev01
//Group bar
define("_G_EDIT_BT", "Edit Current Group");
define("_G_EXPORT_BT", "Export Current Group"); //New in 0.98rc5
define("_G_DELETE_BT", "Delete Current Group");
define("_G_ADDQUESTION_BT", "Add New Question to Group");
define("_G_MINIMISE_BT", "Hide Details of this Group");
define("_G_MAXIMISE_BT", "Show Details of this Group");
define("_G_CLOSE_BT", "Close this Group");
//Question bar
define("_Q_EDIT_BT", "Edit Current Question");
define("_Q_COPY_BT", "Copy Current Question"); //New in 0.98rc4
define("_Q_DELETE_BT", "Delete Current Question");
define("_Q_EXPORT_BT", "Export this Question");
define("_Q_CONDITIONS_BT", "Set Conditions for this Question");
define("_Q_ANSWERS_BT", "Edit/Add Answers for this Question");
define("_Q_LABELS_BT", "Edit/Add Label Sets");
define("_Q_MINIMISE_BT", "Hide Details of this Question");
define("_Q_MAXIMISE_BT", "Show Details of this Question");
define("_Q_CLOSE_BT", "Close this Question");
//Browse Button Bar
define("_B_ADMIN_BT", "Return to Survey Administration");
define("_B_SUMMARY_BT", "Show summary information");
define("_B_ALL_BT", "Display Responses");
define("_B_LAST_BT", "Display Last 50 Responses");
define("_B_STATISTICS_BT", "Get statistics from these responses");
define("_B_EXPORT_BT", "Export Results to Application");
define("_B_BACKUP_BT", "Backup results table as SQL file");
//Tokens Button Bar
define("_T_ALL_BT", "Display Tokens");
define("_T_ADD_BT", "Add new token entry");
define("_T_IMPORT_BT", "Import Tokens from CSV File");
define("_T_EXPORT_BT", "Export Tokens to CSV file"); //New for 0.98rc7
define("_T_INVITE_BT", "Send email invitation");
define("_T_REMIND_BT", "Send email reminder");
define("_T_TOKENIFY_BT", "Generate Tokens");
define("_T_KILL_BT", "Drop tokens table");
//Labels Button Bar
define("_L_ADDSET_BT", "Add new label set");
define("_L_EDIT_BT", "Edit label set");
define("_L_DEL_BT", "Delete label set");
//Datacontrols
define("_D_BEGIN", "Show start..");
define("_D_BACK", "Show last..");
define("_D_FORWARD", "Show next..");
define("_D_END", "Show last..");

//DATA LABELS
//surveys
define("_SL_TITLE", "Title:");
define("_SL_SURVEYURL", "Survey URL:"); //new in 0.98rc5
define("_SL_DESCRIPTION", "Description:");
define("_SL_WELCOME", "Welcome:");
define("_SL_ADMIN", "Administrator:");
define("_SL_EMAIL", "Admin Email:");
define("_SL_FAXTO", "Fax To:");
define("_SL_ANONYMOUS", "Anonymous?");
define("_SL_EXPIRES", "Expires:");
define("_SL_FORMAT", "Format:");
define("_SL_DATESTAMP", "Date Stamp?");
define("_SL_TEMPLATE", "Template:");
define("_SL_LANGUAGE", "Language:");
define("_SL_LINK", "Link:");
define("_SL_URL", "End URL:");
define("_SL_URLDESCRIP", "URL Descrip:");
define("_SL_STATUS", "Status:");
define("_SL_SELSQL", "Select SQL File:");
define("_SL_USECOOKIES", "Use Cookies?"); //NEW with 098rc3
define("_SL_NOTIFICATION", "Notification:"); //New with 098rc5
define("_SL_ALLOWREGISTER", "Allow public registration?"); //New with 0.98rc9
define("_SL_ATTRIBUTENAMES", "Token Attribute Names:"); //New with 0.98rc9
define("_SL_EMAILINVITE_SUBJ", "Invitation Email Subject:"); //New with 0.99dev01
define("_SL_EMAILINVITE", "Invitation Email:"); //New with 0.98rc9
define("_SL_EMAILREMIND_SUBJ", "Email Reminder Subject:"); //New with 0.99dev01
define("_SL_EMAILREMIND", "Email Reminder:"); //New with 0.98rc9
define("_SL_EMAILREGISTER_SUBJ", "Public registration Email Subject:"); //New with 0.99dev01
define("_SL_EMAILREGISTER", "Public registration Email:"); //New with 0.98rc9
define("_SL_EMAILCONFIRM_SUBJ", "Confirmation Email Subject"); //New with 0.99dev01
define("_SL_EMAILCONFIRM", "Confirmation Email"); //New with 0.98rc9
define("_SL_REPLACEOK", "This will replace the existing text. Continue?"); //New with 0.98rc9
define("_SL_ALLOWSAVE", "Allow Saves?"); //New with 0.99dev01
define("_SL_AUTONUMBER", "Start ID numbers at:"); //New with 0.99dev01
define("_SL_AUTORELOAD", "Automatically load URL when survey complete?"); //New with 0.99dev01
define("_SL_ALLOWPREV", "Show [<< Prev] button"); //New with 0.99dev01
define("_SL_USE_DEFAULT","Use default");
define("_SL_UPD_SURVEY","Update survey");

//groups
define("_GL_TITLE", "Title:");
define("_GL_DESCRIPTION", "Description:");
//questions
define("_QL_CODE", "Code:");
define("_QL_QUESTION", "Question:");
define("_QL_VALIDATION", "Validation:"); //New in VALIDATION VERSION
define("_QL_HELP", "Help:");
define("_QL_TYPE", "Type:");
define("_QL_GROUP", "Group:");
define("_QL_MANDATORY", "Mandatory:");
define("_QL_OTHER", "Other:");
define("_QL_LABELSET", "Label Set:");
define("_QL_COPYANS", "Copy Answers?"); //New in 0.98rc3
define("_QL_QUESTIONATTRIBUTES", "Question Attributes:"); //New in 0.99dev01
define("_QL_COPYATT", "Copy Attributes?"); //New in 0.99dev01
//answers
define("_AL_CODE", "Code");
define("_AL_ANSWER", "Answer");
define("_AL_DEFAULT", "Default");
define("_AL_MOVE", "Move");
define("_AL_ACTION", "Action");
define("_AL_UP", "Up");
define("_AL_DN", "Dn");
define("_AL_SAVE", "Save");
define("_AL_DEL", "Del");
define("_AL_ADD", "Add");
define("_AL_FIXSORT", "Fix Sort");
define("_AL_SORTALPHA", "Sort Alpha"); //New in 0.98rc8 - Sort Answers Alphabetically
//users
define("_UL_USER", "User");
define("_UL_PASSWORD", "Password");
define("_UL_SECURITY", "Security");
define("_UL_ACTION", "Action");
define("_UL_EDIT", "Edit");
define("_UL_DEL", "Delete");
define("_UL_ADD", "Add");
define("_UL_TURNOFF", "Turn Off Security");
//tokens
define("_TL_FIRST", "First Name");
define("_TL_LAST", "Last Name");
define("_TL_EMAIL", "Email");
define("_TL_TOKEN", "Token");
define("_TL_INVITE", "Invite sent?");
define("_TL_DONE", "Completed?");
define("_TL_ACTION", "Actions");
define("_TL_ATTR1", "Att_1"); //New for 0.98rc7
define("_TL_ATTR2", "Att_2"); //New for 0.98rc7
define("_TL_MPID", "MPID"); //New for 0.98rc7
//labels
define("_LL_NAME", "Set Name"); //NEW with 098rc3
define("_LL_CODE", "Code"); //NEW with 098rc3
define("_LL_ANSWER", "Title"); //NEW with 098rc3
define("_LL_SORTORDER", "Order"); //NEW with 098rc3
define("_LL_ACTION", "Action"); //New with 098rc3

//QUESTION TYPES
define("_5PT", "5 Point Choice");
define("_DATE", "Date");
define("_GENDER", "Gender");
define("_LIST", "List (Radio)"); //Changed with 0.99dev01
define("_LIST_DROPDOWN", "List (Dropdown)"); //New with 0.99dev01
define("_LISTWC", "List With Comment");
define("_MULTO", "Multiple Options");
define("_MULTOC", "Multiple Options with Comments");
define("_MULTITEXT", "Multiple Short Text");
define("_NUMERICAL", "Numerical Input");
define("_RANK", "Ranking");
define("_STEXT", "Short free text");
define("_LTEXT", "Long free text");
define("_HTEXT", "Huge free text"); //New with 0.99dev01
define("_YESNO", "Yes/No");
define("_ARR5", "Array (5 Point Choice)");
define("_ARR10", "Array (10 Point Choice)");
define("_ARRYN", "Array (Yes/No/Uncertain)");
define("_ARRMV", "Array (Increase, Same, Decrease)");
define("_ARRFL", "Array (Flexible Labels)"); //Release 0.98rc3
define("_ARRFLC", "Array (Flexible Labels) by Column"); //Release 0.98rc8
define("_SINFL", "Single (Flexible Labels)"); //(FOR LATER RELEASE)
define("_EMAIL", "Email Address"); //FOR LATER RELEASE
define("_BOILERPLATE", "Boilerplate Question"); //New in 0.98rc6
define("_LISTFL_DROPDOWN", "List (Flexible Labels) (Dropdown)"); //New in 0.99dev01
define("_LISTFL_RADIO", "List (Flexible Labels) (Radio)"); //New in 0.99dev01

//GENERAL WORDS AND PHRASES
define("_AD_YES", "Yes");
define("_AD_NO", "No");
define("_AD_CANCEL", "Cancel");
define("_AD_CHOOSE", "Please Choose..");
define("_AD_OR", "OR"); //New in 0.98rc4
define("_ERROR", "Error");
define("_SUCCESS", "Success");
define("_REQ", "*Required");
define("_ADDS", "Add Survey");
define("_ADDG", "Add Group");
define("_ADDQ", "Add Question");
define("_ADDA", "Add Answer"); //New in 0.98rc4
define("_COPYQ", "Copy Question"); //New in 0.98rc4
define("_ADDU", "Add User");
define("_SEARCH", "Search"); //New in 0.98rc4
define("_SAVE", "Save Changes");
define("_NONE", "None"); //as in "Do not display anything", "or none chosen";
define("_GO_ADMIN", "Main Admin Screen"); //text to display to return/display main administration screen
define("_CONTINUE", "Continue");
define("_WARNING", "Warning");
define("_USERNAME", "User name");
define("_PASSWORD", "Password");
define("_DELETE", "Delete");
define("_CLOSEWIN", "Close Window");
define("_TOKEN", "Token");
define("_DATESTAMP", "Date Stamp"); //Referring to the datestamp or time response submitted
define("_COMMENT", "Comment");
define("_FROM", "From"); //For emails
define("_SUBJECT", "Subject"); //For emails
define("_MESSAGE", "Message"); //For emails
define("_RELOADING", "Reloading Screen. Please wait.");
define("_ADD", "Add");
define("_UPDATE", "Update");
define("_BROWSE", "Browse"); //New in 098rc5
define("_AND", "and"); //New with 0.98rc8
define("_SQL", "SQL"); //New with 0.98rc8
define("_PERCENTAGE", "Percentage"); //New with 0.98rc8
define("_COUNT", "Count"); //New with 0.98rc8

//SURVEY STATUS MESSAGES (new in 0.98rc3)
define("_SS_NOGROUPS", "Number of groups in survey:"); //NEW for release 0.98rc3
define("_SS_NOQUESTS", "Number of questions in survey:"); //NEW for release 0.98rc3
define("_SS_ANONYMOUS", "This survey is anonymous."); //NEW for release 0.98rc3
define("_SS_TRACKED", "This survey is NOT anonymous."); //NEW for release 0.98rc3
define("_SS_DATESTAMPED", "Responses will be date stamped"); //NEW for release 0.98rc3
define("_SS_COOKIES", "It uses cookies for access control."); //NEW for release 0.98rc3
define("_SS_QBYQ", "It is presented question by question."); //NEW for release 0.98rc3
define("_SS_GBYG", "It is presented group by group."); //NEW for release 0.98rc3
define("_SS_SBYS", "It is presented on one single page."); //NEW for release 0.98rc3
define("_SS_ACTIVE", "Survey is currently active."); //NEW for release 0.98rc3
define("_SS_NOTACTIVE", "Survey is not currently active."); //NEW for release 0.98rc3
define("_SS_SURVEYTABLE", "Survey table name is:"); //NEW for release 0.98rc3
define("_SS_CANNOTACTIVATE", "Survey cannot be activated yet."); //NEW for release 0.98rc3
define("_SS_ADDGROUPS", "You need to add groups"); //NEW for release 0.98rc3
define("_SS_ADDQUESTS", "You need to add questions"); //NEW for release 0.98rc3
define("_SS_ALLOWREGISTER", "If tokens are used, the public may register for this survey"); //NEW for release 0.98rc9
define("_SS_ALLOWSAVE", "Participants can save partially finished surveys"); //NEW for release 0.99dev01

//QUESTION STATUS MESSAGES (new in 0.98rc4)
define("_QS_MANDATORY", "Mandatory Question"); //New for release 0.98rc4
define("_QS_OPTIONAL", "Optional Question"); //New for release 0.98rc4
define("_QS_NOANSWERS", "You need to add answers to this question"); //New for release 0.98rc4
define("_QS_NOLID", "You need to choose a Label Set for this question"); //New for release 0.98rc4
define("_QS_COPYINFO", "Note: You MUST enter a new question code"); //New for release 0.98rc4

//General Setup Messages
define("_ST_NODB1", "The defined surveyor database does not exist");
define("_ST_NODB2", "Either your selected database has not yet been created or there is a problem accessing it.");
define("_ST_NODB3", "PHPSurveyor can attempt to create this database for you.");
define("_ST_NODB4", "Your selected database name is:");
define("_ST_CREATEDB", "Create Database");

//USER CONTROL MESSAGES
define("_UC_CREATE", "Creating default htaccess file");
define("_UC_NOCREATE", "Couldn't create htaccess file. Check your config.php for \$homedir setting, and that you have write permission in the correct directory.");
define("_UC_SEC_DONE", "Security Levels are now set up!");
define("_UC_CREATE_DEFAULT", "Creating default users");
define("_UC_UPDATE_TABLE", "Updating users table");
define("_UC_HTPASSWD_ERROR", "Error occurred creating htpasswd file");
define("_UC_HTPASSWD_EXPLAIN", "If you are using a windows server it is recommended that you copy the apache htpasswd.exe file into your admin folder for this function to work properly. This file is usually found in /apache group/apache/bin/");
define("_UC_SEC_REMOVE", "Removing security settings");
define("_UC_ALL_REMOVED", "Access file, password file and user database deleted");
define("_UC_ADD_USER", "Adding User");
define("_UC_ADD_MISSING", "Could not add user. Username and/or password were not supplied");
define("_UC_DEL_USER", "Deleting User");
define("_UC_DEL_MISSING", "Could not delete user. Username was not supplied.");
define("_UC_MOD_USER", "Modifying User");
define("_UC_MOD_MISSING", "Could not modify user. Username and/or password were not supplied");
define("_UC_TURNON_MESSAGE1", "<p>You have not yet initialised security settings for your survey system and subsequently there are no restrictions on access.</p>\nIf you click on the 'initialise security' button below, standard APACHE security settings will be added to the administration directory of this script. You will then need to use the default access username and password to access the administration and data entry scripts.");
define("_UC_TURNON_MESSAGE2", "It is highly recommended that once your security system has been initialised you change this default password.");
define("_UC_INITIALISE", "Initialise Security");
define("_UC_NOUSERS", "No users exist in your table. We recommend you 'turn off' security. You can then 'turn it on' again.");
define("_UC_TURNOFF", "Turn Off Security");

//Activate and deactivate messages
define("_AC_MULTI_NOANSWER", "This question is a multiple answer type question but has no answers.");
define("_AC_NOTYPE", "This question does not have a question 'type' set.");
define("_AC_NOLID", "This question requires a Labelset, but none is set."); //New for 0.98rc8
define("_AC_CON_OUTOFORDER", "This question has a condition set, however the condition is based on a question that appears after it.");
define("_AC_FAIL", "Survey does not pass consistency check");
define("_AC_PROBS", "The following problems have been found:");
define("_AC_CANNOTACTIVATE", "The survey cannot be activated until these problems have been resolved");
define("_AC_READCAREFULLY", "READ THIS CAREFULLY BEFORE PROCEEDING");
define("_AC_ACTIVATE_MESSAGE1", "You should only activate a survey when you are absolutely certain that your survey setup is finished and will not need changing.");
define("_AC_ACTIVATE_MESSAGE2", "Once a survey is activated you can no longer:<ul><li>Add or delete groups</li><li>Add or remove answers to Multiple Answer questions</li><li>Add or delete questions</li></ul>");
define("_AC_ACTIVATE_MESSAGE3", "However you can still:<ul><li>Edit (change) your questions code, text or type</li><li>Edit (change) your group names</li><li>Add, Remove or Edit pre-defined question answers (except for Multi-answer questions)</li><li>Change survey name or description</li></ul>");
define("_AC_ACTIVATE_MESSAGE4", "Once data has been entered into this survey, if you want to add or remove groups or questions, you will need to de-activate this survey, which will move all data that has already been entered into a separate archived table.");
define("_AC_ACTIVATE", "Activate");
define("_AC_ACTIVATED", "Survey has been activated. Results table has been succesfully created.");
define("_AC_NOTACTIVATED", "Survey could not be actived.");
define("_AC_NOTPRIVATE", "This is not an anonymous survey. A token table must also be created.");
define("_AC_REGISTRATION", "This survey allows public registration. A token table must also be created.");
define("_AC_CREATETOKENS", "Initialise Tokens");
define("_AC_SURVEYACTIVE", "This survey is now active, and responses can be recorded.");
define("_AC_DEACTIVATE_MESSAGE1", "In an active survey, a table is created to store all the data-entry records.");
define("_AC_DEACTIVATE_MESSAGE2", "When you de-activate a survey all the data entered in the original table will be moved elsewhere, and when you activate the survey again, the table will be empty. You will not be able to access this data using PHPSurveyor any more.");
define("_AC_DEACTIVATE_MESSAGE3", "De-activated survey data can only be accessed by system administrators using a MySQL data access tool like phpmyadmin. If your survey uses tokens, this table will also be renamed and will only be accessible by system administrators.");
define("_AC_DEACTIVATE_MESSAGE4", "Your responses table will be renamed to:");
define("_AC_DEACTIVATE_MESSAGE5", "You should export your responses before de-activating. Click \"Cancel\" to return to the main admin screen without de-activating this survey.");
define("_AC_DEACTIVATE", "De-Activate");
define("_AC_DEACTIVATED_MESSAGE1", "The responses table has been renamed to: ");
define("_AC_DEACTIVATED_MESSAGE2", "The responses to this survey are no longer available using PHPSurveyor.");
define("_AC_DEACTIVATED_MESSAGE3", "You should note the name of this table in case you need to access this information later.");
define("_AC_DEACTIVATED_MESSAGE4", "The tokens table associated with this survey has been renamed to: ");

//CHECKFIELDS
define("_CF_CHECKTABLES", "Checking to ensure all tables exist");
define("_CF_CHECKFIELDS", "Checking to ensure all fields exist");
define("_CF_CHECKING", "Checking");
define("_CF_TABLECREATED", "Table Created");
define("_CF_FIELDCREATED", "Field Created");
define("_CF_OK", "OK");
define("_CFT_PROBLEM", "It appears as if some tables or fields are missing from your database.");

//CREATE DATABASE (createdb.php)
define("_CD_DBCREATED", "Database has been created.");
define("_CD_POPULATE_MESSAGE", "Please click below to populate the database");
define("_CD_POPULATE", "Populate Database");
define("_CD_NOCREATE", "Could not create database");
define("_CD_NODBNAME", "Database Information not provided. This script must be run from admin.php only.");

//DATABASE MODIFICATION MESSAGES
define("_DB_FAIL_GROUPNAME", "Group could not be added. It is missing the mandatory group name");
define("_DB_FAIL_GROUPUPDATE", "Group could not be updated");
define("_DB_FAIL_GROUPDELETE", "Group could not be deleted");
define("_DB_FAIL_NEWQUESTION", "Question could not be created.");
define("_DB_FAIL_QUESTIONTYPECONDITIONS", "Question could not be updated. There are conditions for other questions that rely on the answers to this question and changing the type will cause problems. You must delete these conditions before you can change the type of this question.");
define("_DB_FAIL_QUESTIONUPDATE", "Question could not be updated");
define("_DB_FAIL_QUESTIONDELCONDITIONS", "Question could not be deleted. There are conditions for other questions that rely on this question. You cannot delete this question until those conditions are removed");
define("_DB_FAIL_QUESTIONDELETE", "Question could not be deleted");
define("_DB_FAIL_NEWANSWERMISSING", "Answer could not be added. You must include both a Code and an Answer");
define("_DB_FAIL_NEWANSWERDUPLICATE", "Answer could not be added. There is already an answer with this code");
define("_DB_FAIL_ANSWERUPDATEMISSING", "Answer could not be updated. You must include both a Code and an Answer");
define("_DB_FAIL_ANSWERUPDATEDUPLICATE", "Answer could not be updated. There is already an answer with this code");
define("_DB_FAIL_ANSWERUPDATECONDITIONS", "Answer could not be updated. You have changed the answer code, but there are conditions to other questions which are dependant upon the old answer code to this question. You must delete these conditions before you can change the code to this answer.");
define("_DB_FAIL_ANSWERDELCONDITIONS", "Answer could not be deleted. There are conditions for other questions that rely on this answer. You cannot delete this answer until those conditions are removed");
define("_DB_FAIL_NEWSURVEY_TITLE", "Survey could not be created because it did not have a short title");
define("_DB_FAIL_NEWSURVEY", "Survey could not be created");
define("_DB_FAIL_SURVEYUPDATE", "Survey could not be updated");
define("_DB_FAIL_SURVEYDELETE", "Survey could not be deleted");

//DELETE SURVEY MESSAGES
define("_DS_NOSID", "You have not selected a survey to delete");
define("_DS_DELMESSAGE1", "You are about to delete this survey");
define("_DS_DELMESSAGE2", "This process will delete this survey, and all related groups, questions answers and conditions.");
define("_DS_DELMESSAGE3", "We recommend that before you delete this survey you export the entire survey from the main administration screen.");
define("_DS_SURVEYACTIVE", "This survey is active and a responses table exists. If you delete this survey, these responses will be deleted. We recommend that you export the responses before deleting this survey.");
define("_DS_SURVEYTOKENS", "This survey has an associated tokens table. If you delete this survey this tokens table will be deleted. We recommend that you export or backup these tokens before deleting this survey.");
define("_DS_DELETED", "This survey has been deleted.");

//DELETE QUESTION AND GROUP MESSAGES
define("_DG_RUSURE", "Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?"); //New for 098rc5
define("_DQ_RUSURE", "Deleting this question will also delete any answers it includes. Are you sure you want to continue?"); //New for 098rc5

//EXPORT MESSAGES
define("_EQ_NOQID", "No QID has been provided. Cannot dump question.");
define("_ES_NOSID", "No SID has been provided. Cannot dump survey");

//EXPORT RESULTS
define("_EX_FROMSTATS", "Filtered from Statistics Script");
define("_EX_HEADINGS", "Questions");
define("_EX_ANSWERS", "Answers");
define("_EX_FORMAT", "Format");
define("_EX_HEAD_ABBREV", "Abbreviated headings");
define("_EX_HEAD_FULL", "Full headings");
define("_EX_ANS_ABBREV", "Answer Codes");
define("_EX_ANS_FULL", "Full Answers");
define("_EX_FORM_WORD", "Microsoft Word");
define("_EX_FORM_EXCEL", "Microsoft Excel");
define("_EX_FORM_CSV", "CSV Comma Delimited");
define("_EX_EXPORTDATA", "Export Data");
define("_EX_COLCONTROLS", "Column Control"); //New for 0.98rc7
define("_EX_TOKENCONTROLS", "Token Control"); //New for 0.98rc7
define("_EX_COLSELECT", "Choose columns"); //New for 0.98rc7
define("_EX_COLOK", "Choose the columns you wish to export. Leave all unselected to export all columns."); //New for 0.98rc7
define("_EX_COLNOTOK", "Your survey contains more than 255 columns of responses. Spreadsheet applications such as Excel are limited to loading no more than 255. Select the columns you wish to export in the list below."); //New for 0.98rc7
define("_EX_TOKENMESSAGE", "Your survey can export associated token data with each response. Select any additional fields you would like to export."); //New for 0.98rc7
define("_EX_TOKSELECT", "Choose Token Fields"); //New for 0.98rc7

//IMPORT SURVEY MESSAGES
define("_IS_FAILUPLOAD", "An error occurred uploading your file. This may be caused by incorrect permissions in your admin folder.");
define("_IS_OKUPLOAD", "File upload succeeded.");
define("_IS_READFILE", "Reading file..");
define("_IS_WRONGFILE", "This file is not a PHPSurveyor survey file. Import failed.");
define("_IS_IMPORTSUMMARY", "Survey Import Summary");
define("_IS_SUCCESS", "Import of Survey is completed.");
define("_IS_IMPFAILED", "Import of this survey file failed");
define("_IS_FILEFAILS", "File does not contain PHPSurveyor data in the correct format.");

//IMPORT GROUP MESSAGES
define("_IG_IMPORTSUMMARY", "Group Import Summary");
define("_IG_SUCCESS", "Import of Group is completed.");
define("_IG_IMPFAILED", "Import of this group file failed");
define("_IG_WRONGFILE", "This file is not a PHPSurveyor group file. Import failed.");

//IMPORT QUESTION MESSAGES
define("_IQ_NOSID", "No SID (Survey) has been provided. Cannot import question.");
define("_IQ_NOGID", "No GID (Group) has been provided. Cannot import question");
define("_IQ_WRONGFILE", "This file is not a PHPSurveyor question file. Import failed.");
define("_IQ_IMPORTSUMMARY", "Question Import Summary");
define("_IQ_SUCCESS", "Import of Question is completed");

//IMPORT LABELSET MESSAGES
define("_IL_DUPLICATE", "There was a duplicate labelset, so this set was not imported. The duplicate will be used instead.");

//BROWSE RESPONSES MESSAGES
define("_BR_NOSID", "You have not selected a survey to browse.");
define("_BR_NOTACTIVATED", "This survey has not been activated. There are no results to browse.");
define("_BR_NOSURVEY", "There is no matching survey.");
define("_BR_EDITRESPONSE", "Edit this entry");
define("_BR_DELRESPONSE", "Delete this entry");
define("_BR_DISPLAYING", "Records Displayed:");
define("_BR_STARTING", "Starting From:");
define("_BR_SHOW", "Show");
define("_DR_RUSURE", "Are you sure you want to delete this entry?"); //New for 0.98rc6

//STATISTICS MESSAGES
define("_ST_FILTERSETTINGS", "Filter Settings");
define("_ST_VIEWALL", "View summary of all available fields"); //New with 0.98rc8
define("_ST_SHOWRESULTS", "View Stats"); //New with 0.98rc8
define("_ST_CLEAR", "Clear"); //New with 0.98rc8
define("_ST_RESPONECONT", "Responses Containing"); //New with 0.98rc8
define("_ST_NOGREATERTHAN", "Number greater than"); //New with 0.98rc8
define("_ST_NOLESSTHAN", "Number Less Than"); //New with 0.98rc8
define("_ST_DATEEQUALS", "Date (YYYY-MM-DD) equals"); //New with 0.98rc8
define("_ST_ORBETWEEN", "OR between"); //New with 0.98rc8
define("_ST_RESULTS", "Results"); //New with 0.98rc8 (Plural)
define("_ST_RESULT", "Result"); //New with 0.98rc8 (Singular)
define("_ST_RECORDSRETURNED", "No of records in this query"); //New with 0.98rc8
define("_ST_TOTALRECORDS", "Total records in survey"); //New with 0.98rc8
define("_ST_PERCENTAGE", "Percentage of total"); //New with 0.98rc8
define("_ST_FIELDSUMMARY", "Field Summary for"); //New with 0.98rc8
define("_ST_CALCULATION", "Calculation"); //New with 0.98rc8
define("_ST_SUM", "Sum"); //New with 0.98rc8 - Mathematical
define("_ST_STDEV", "Standard Deviation"); //New with 0.98rc8 - Mathematical
define("_ST_AVERAGE", "Average"); //New with 0.98rc8 - Mathematical
define("_ST_MIN", "Minimum"); //New with 0.98rc8 - Mathematical
define("_ST_MAX", "Maximum"); //New with 0.98rc8 - Mathematical
define("_ST_Q1", "1st Quartile (Q1)"); //New with 0.98rc8 - Mathematical
define("_ST_Q2", "2nd Quartile (Median)"); //New with 0.98rc8 - Mathematical
define("_ST_Q3", "3rd Quartile (Q3)"); //New with 0.98rc8 - Mathematical
define("_ST_NULLIGNORED", "*Null values are ignored in calculations"); //New with 0.98rc8
define("_ST_QUARTMETHOD", "*Q1 and Q3 calculated using <a href='http://mathforum.org/library/drmath/view/60969.html' target='_blank'>minitab method</a>"); //New with 0.98rc8

//DATA ENTRY MESSAGES
define("_DE_NOMODIFY", "Cannot be modified");
define("_DE_UPDATE", "Update Entry");
define("_DE_NOSID", "You have not selected a survey for data-entry.");
define("_DE_NOEXIST", "The survey you selected does not exist");
define("_DE_NOTACTIVE", "This survey is not yet active. Your response cannot be saved");
define("_DE_INSERT", "Inserting Data");
define("_DE_RECORD", "The entry was assigned the following record id: ");
define("_DE_ADDANOTHER", "Add Another Record");
define("_DE_VIEWTHISONE", "View This Record");
define("_DE_BROWSE", "Browse Responses");
define("_DE_DELRECORD", "Record Deleted");
define("_DE_UPDATED", "Record has been updated.");
define("_DE_EDITING", "Editing Response");
define("_DE_QUESTIONHELP", "Help about this question");
define("_DE_CONDITIONHELP1", "Only answer this if the following conditions are met:"); 
define("_DE_CONDITIONHELP2", "to question {QUESTION}, you answered {ANSWER}"); //This will be a tricky one depending on your languages syntax. {ANSWER} is replaced with ALL ANSWERS, seperated by _DE_OR (OR).
define("_DE_AND", "AND");
define("_DE_OR", "OR");
define("_DE_SAVEENTRY", "Save as a partially completed survey"); //New in 0.99dev01
define("_DE_SAVEID", "Identifier:"); //New in 0.99dev01
define("_DE_SAVEPW", "Password:"); //New in 0.99dev01
define("_DE_SAVEPWCONFIRM", "Confirm Password:"); //New in 0.99dev01
define("_DE_SAVEEMAIL", "Email:"); //New in 0.99dev01

//TOKEN CONTROL MESSAGES
define("_TC_TOTALCOUNT", "Total Records in this Token Table:"); //New in 0.98rc4
define("_TC_NOTOKENCOUNT", "Total With No Unique Token:"); //New in 0.98rc4
define("_TC_INVITECOUNT", "Total Invitations Sent:"); //New in 0.98rc4
define("_TC_COMPLETEDCOUNT", "Total Surveys Completed:"); //New in 0.98rc4
define("_TC_NOSID", "You have not selected a survey");
define("_TC_DELTOKENS", "About to delete tokens table for this survey.");
define("_TC_DELTOKENSINFO", "If you delete this table tokens will no longer be required to access this survey. A backup of this table will be made if you proceed. Your system administrator will be able to access this table.");
define("_TC_DELETETOKENS", "Delete Tokens");
define("_TC_TOKENSGONE", "The tokens table has now been removed and tokens are no longer required to access this survey. A backup of this table has been made and can be accessed by your system administrator.");
define("_TC_NOTINITIALISED", "Tokens have not been initialised for this survey.");
define("_TC_INITINFO", "If you initialise tokens for this survey, the survey will only be accessible to users who have been assigned a token.");
define("_TC_INITQ", "Do you want to create a tokens table for this survey?");
define("_TC_INITTOKENS", "Initialise Tokens");
define("_TC_CREATED", "A token table has been created for this survey.");
define("_TC_DELETEALL", "Delete all token entries");
define("_TC_DELETEALL_RUSURE", "Are you really sure you want to delete ALL token entries?");
define("_TC_ALLDELETED", "All token entries have been deleted");
define("_TC_CLEARINVITES", "Set all entries to 'N' invitation sent");
define("_TC_CLEARINV_RUSURE", "Are you really sure you want to reset all invitation records to NO?");
define("_TC_CLEARTOKENS", "Delete all unique token numbers");
define("_TC_CLEARTOKENS_RUSURE", "Are you sure you want to delete all unique token numbers?");
define("_TC_TOKENSCLEARED", "All unique token numbers have been removed");
define("_TC_INVITESCLEARED", "All invite entries have been set to N");
define("_TC_EDIT", "Edit Token Entry");
define("_TC_DEL", "Delete Token Entry");
define("_TC_DO", "Do Survey");
define("_TC_VIEW", "View Response");
define("_TC_INVITET", "Send invitation email to this entry");
define("_TC_REMINDT", "Send reminder email to this entry");
define("_TC_INVITESUBJECT", "Invitation to participate in {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSUBJECT", "Reminder to participate in {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSTARTAT", "Start at TID No:");
define("_TC_REMINDTID", "Sending to TID No:");
define("_TC_CREATETOKENSINFO", "Clicking yes will generate tokens for all those in this token list that have not been issued one. Is this OK?");
define("_TC_TOKENSCREATED", "{TOKENCOUNT} tokens have been created"); //Leave {TOKENCOUNT} for replacement in script with the number of tokens created
define("_TC_TOKENDELETED", "Token has been deleted.");
define("_TC_SORTBY", "Sort by: ");
define("_TC_ADDEDIT", "Add or Edit Token");
define("_TC_TOKENCREATEINFO", "You can leave this blank, and automatically generate tokens using 'Create Tokens'");
define("_TC_TOKENADDED", "Added New Token");
define("_TC_TOKENUPDATED", "Updated Token");
define("_TC_UPLOADINFO", "File should be a standard CSV (comma delimited) file with no quotes. The first line should contain header information (will be removed). Data should be ordered as \"firstname, lastname, email, [token], [attribute1], [attribute2]\".");
define("_TC_UPLOADFAIL", "Upload file not found. Check your permissions and path for the upload directory"); //New for 0.98rc5
define("_TC_IMPORT", "Importing CSV File");
define("_TC_CREATE", "Creating Token Entries");
define("_TC_TOKENS_CREATED", "{TOKENCOUNT} Records Created");
define("_TC_NONETOSEND", "There were no eligible emails to send. This will be because none satisfied the criteria of - having an email address, not having been sent an invitation already, having already completed the survey and having a token.");
define("_TC_NOREMINDERSTOSEND", "There were no eligible emails to send. This will be because none satisfied the criteria of - having an email address, having been sent an invitation, but not having yet completed the survey.");
define("_TC_NOEMAILTEMPLATE", "Invitation Template cannot be found. This file must exist in the default template folder.");
define("_TC_NOREMINDTEMPLATE", "Reminder Template cannot be found. This file must exist in the default template folder.");
define("_TC_SENDEMAIL", "Send Invitations");
define("_TC_SENDINGEMAILS", "Sending Invitations");
define("_TC_SENDINGREMINDERS", "Sending Reminders");
define("_TC_EMAILSTOGO", "There are more emails pending than can be sent in one batch. Continue sending emails by clicking below.");
define("_TC_EMAILSREMAINING", "There are {EMAILCOUNT} emails still to be sent."); //Leave {EMAILCOUNT} for replacement in script by number of emails remaining
define("_TC_SENDREMIND", "Send Reminders");
define("_TC_INVITESENTTO", "Invitation Sent To:"); //is followed by token name
define("_TC_REMINDSENTTO", "Reminder Sent To:"); //is followed by token name
define("_TC_UPDATEDB", "Update tokens table with new fields"); //New for 0.98rc7
define("_TC_EMAILINVITE_SUBJ", "Invitation to participate in survey"); //New for 0.99dev01
define("_TC_EMAILINVITE", "Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\n"
						 ."The survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
						 ."To participate, please click on the link below.\n\nSincerely,\n\n"
						 ."{ADMINNAME} ({ADMINEMAIL})\n\n"
						 ."----------------------------------------------\n"
						 ."Click here to do the survey:\n"
						 ."{SURVEYURL}"); //New for 0.98rc9 - default Email Invitation
define("_TC_EMAILREMIND_SUBJ", "Reminder to participate in survey"); //New for 0.99dev01
define("_TC_EMAILREMIND", "Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\n"
						 ."We note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\n"
						 ."The survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
						 ."To participate, please click on the link below.\n\nSincerely,\n\n"
						 ."{ADMINNAME} ({ADMINEMAIL})\n\n"
						 ."----------------------------------------------\n"
						 ."Click here to do the survey:\n"
						 ."{SURVEYURL}"); //New for 0.98rc9 - default Email Reminder
define("_TC_EMAILREGISTER_SUBJ", "Survey Registration Confirmation"); //New for 0.99dev01
define("_TC_EMAILREGISTER", "Dear {FIRSTNAME},\n\n"
						  ."You, or someone using your email address, have registered to "
						  ."participate in an online survey titled {SURVEYNAME}.\n\n"
						  ."To complete this survey, click on the following URL:\n\n"
						  ."{SURVEYURL}\n\n"
						  ."If you have any questions about this survey, or if you "
						  ."did not register to participate and believe this email "
						  ."is in error, please contact {ADMINNAME} at {ADMINEMAIL}.");//NEW for 0.98rc9
define("_TC_EMAILCONFIRM_SUBJ", "Confirmation of completed survey"); //New for 0.99dev01
define("_TC_EMAILCONFIRM", "Dear {FIRSTNAME},\n\nThis email is to confirm that you have completed the survey titled {SURVEYNAME} "
						  ."and your response has been saved. Thank you for participating.\n\n"
						  ."If you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\n"
						  ."Sincerely,\n\n"
						  ."{ADMINNAME}"); //New for 0.98rc9 - Confirmation Email

//labels.php
define("_LB_NEWSET", "Create New Label Set");
define("_LB_EDITSET", "Edit Label Set");
define("_LB_FAIL_UPDATESET", "Update of Label Set failed");
define("_LB_FAIL_INSERTSET", "Insert of new Label Set failed");
define("_LB_FAIL_DELSET", "Couldn't Delete Label Set - There are questions that rely on this. You must delete these questions first.");
define("_LB_ACTIVEUSE", "You cannot change codes, add or delete entries in this label set because it is being used by an active survey.");
define("_LB_TOTALUSE", "Some surveys currently use this label set. Modifying the codes, adding or deleting entries to this label set may produce undesired results in other surveys.");
//Export Labels
define("_EL_NOLID", "No LID has been provided. Cannot dump label set.");
//Import Labels
define("_IL_GOLABELADMIN", "Return to Labels Admin");

//PHPSurveyor System Summary
define("_PS_TITLE", "PHPSurveyor System Summary");
define("_PS_DBNAME", "Database Name");
define("_PS_DEFLANG", "Default Language");
define("_PS_CURLANG", "Current Language");
define("_PS_USERS", "Users");
define("_PS_ACTIVESURVEYS", "Active Surveys");
define("_PS_DEACTSURVEYS", "De-activated Surveys");
define("_PS_ACTIVETOKENS", "Active Token Tables");
define("_PS_DEACTTOKENS", "De-activated Token Tables");
define("_PS_CHECKDBINTEGRITY", "Check PHPSurveyor Data Integrity"); //New with 0.98rc8

//Notification Levels
define("_NT_NONE", "No email notification"); //New with 098rc5
define("_NT_SINGLE", "Basic email notification"); //New with 098rc5
define("_NT_RESULTS", "Send email notification with result codes"); //New with 098rc5

//CONDITIONS TRANSLATIONS
define("_CD_CONDITIONDESIGNER", "Condition Designer"); //New with 098rc9
define("_CD_ONLYSHOW", "Only show question {QID} IF"); //New with 098rc9 - {QID} is repleaced leave there
define("_CD_AND", "AND"); //New with 098rc9
define("_CD_COPYCONDITIONS", "Copy Conditions"); //New with 098rc9
define("_CD_CONDITION", "Condition"); //New with 098rc9
define("_CD_ADDCONDITION", "Add Condition"); //New with 098rc9
define("_CD_EQUALS", "Equals"); //New with 098rc9
define("_CD_COPYRUSURE", "Are you sure you want to copy these condition(s) to the questions you have selected?"); //New with 098rc9
define("_CD_NODIRECT", "You cannot run this script directly."); //New with 098rc9
define("_CD_NOSID", "You have not selected a Survey."); //New with 098rc9
define("_CD_NOQID", "You have not selected a Question."); //New with 098rc9
define("_CD_DIDNOTCOPYQ", "Did not copy questions"); //New with 098rc9
define("_CD_NOCONDITIONTOCOPY", "No condition selected to copy from"); //New with 098rc9
define("_CD_NOQUESTIONTOCOPYTO", "No question selected to copy condition to"); //New with 098rc9

//TEMPLATE EDITOR TRANSLATIONS
define("_TP_CREATENEW", "Create new template"); //New with 098rc9
define("_TP_NEWTEMPLATECALLED", "Create new template called:"); //New with 098rc9
define("_TP_DEFAULTNEWTEMPLATE", "NewTemplate"); //New with 098rc9 (default name for new template)
define("_TP_CANMODIFY", "This template can be modified"); //New with 098rc9
define("_TP_CANNOTMODIFY", "This template cannot be modified"); //New with 098rc9
define("_TP_RENAME", "Rename this template");  //New with 098rc9
define("_TP_RENAMETO", "Rename this template to:"); //New with 098rc9
define("_TP_COPY", "Make a copy of this template");  //New with 098rc9
define("_TP_COPYTO", "Create a copy of this template called:"); //New with 098rc9
define("_TP_COPYOF", "copy_of_"); //New with 098rc9 (prefix to default copy name)
define("_TP_FILECONTROL", "File Control:"); //New with 098rc9
define("_TP_STANDARDFILES", "Standard Files:");  //New with 098rc9
define("_TP_NOWEDITING", "Now editing:");  //New with 098rc9
define("_TP_OTHERFILES", "Other Files:"); //New with 098rc9
define("_TP_PREVIEW", "Preview:"); //New with 098rc9
define("_TP_DELETEFILE", "Delete"); //New with 098rc9
define("_TP_UPLOADFILE", "Upload"); //New with 098rc9
define("_TP_SCREEN", "Screen:"); //New with 098rc9
define("_TP_WELCOMEPAGE", "Welcome Page"); //New with 098rc9
define("_TP_QUESTIONPAGE", "Question Page"); //New with 098rc9
define("_TP_SUBMITPAGE", "Submit Page");
define("_TP_COMPLETEDPAGE", "Completed Page"); //New with 098rc9
define("_TP_CLEARALLPAGE", "Clear All Page"); //New with 098rc9
define("_TP_REGISTERPAGE", "Register Page"); //New with 098finalRC1
define("_TP_EXPORT", "Export Template"); //New with 098rc10
define("_TP_LOADPAGE", "Load Page"); //New with 0.99dev01
define("_TP_SAVEPAGE", "Save Page"); //New with 0.99dev01

//Saved Surveys
define("_SV_RESPONSES", "Saved Responses:");
define("_SV_IDENTIFIER", "Identifier");
define("_SV_RESPONSECOUNT", "Answered");
define("_SV_IP", "IP Address");
define("_SV_DATE", "Date Saved");
define("_SV_REMIND", "Remind");
define("_SV_EDIT", "Edit");

//VVEXPORT/IMPORT
define("_VV_IMPORTFILE", "Import a VV survey file");
define("_VV_EXPORTFILE", "Export a VV survey file");
define("_VV_FILE", "File:");
define("_VV_SURVEYID", "Survey ID:");
define("_VV_EXCLUDEID", "Exclude record IDs?");
define("_VV_INSERT", "When an imported record matches an existing record ID:");
define("_VV_INSERT_ERROR", "Report an error (and skip the new record).");
define("_VV_INSERT_RENUMBER", "Renumber the new record.");
define("_VV_INSERT_IGNORE", "Ignore the new record.");
define("_VV_INSERT_REPLACE", "Replace the existing record.");
define("_VV_DONOTREFRESH", "Important Note:<br />Do NOT refresh this page, as this will import the file again and produce duplicates");
define("_VV_IMPORTNUMBER", "Total records imported:");
define("_VV_ENTRYFAILED", "Import Failed on Record");
define("_VV_BECAUSE", "because");
define("_VV_EXPORTDEACTIVATE", "Export, then de-activate survey");
define("_VV_EXPORTONLY", "Export but leave survey active");
define("_VV_RUSURE", "If you have chosen to export and de-activate, this will rename your current responses table and it will not be easy to restore it. Are you sure?");

//ASSESSMENTS
define("_AS_TITLE", "Assessments");
define("_AS_DESCRIPTION", "If you create any assessments in this page, for the currently selected survey, the assessment will be performed at the end of the survey after submission");
define("_AS_NOSID", "No SID Provided");
define("_AS_SCOPE", "Scope");
define("_AS_MINIMUM", "Minimum");
define("_AS_MAXIMUM", "Maximum");
define("_AS_GID", "Group");
define("_AS_NAME", "Name/Header");
define("_AS_HEADING", "Heading");
define("_AS_MESSAGE", "Message");
define("_AS_URL", "URL");
define("_AS_SCOPE_GROUP", "Group");
define("_AS_SCOPE_TOTAL", "Total");
define("_AS_ACTIONS", "Actions");
define("_AS_EDIT", "Edit");
define("_AS_DELETE", "Delete");
define("_AS_ADD", "Add");
define("_AS_UPDATE", "Update");

//Question Number regeneration
define("_RE_REGENNUMBER", "Regenerate Question Numbers:"); //NEW for release 0.99dev2
define("_RE_STRAIGHT", "Straight"); //NEW for release 0.99dev2
define("_RE_BYGROUP", "By Group"); //NEW for release 0.99dev2
?>