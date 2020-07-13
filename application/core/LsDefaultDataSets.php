<?php
/**
 * A collection of default data sets, like surveymenus, surveymenuentries, and tutorials
 */
class LsDefaultDataSets
{

    public static function getSurveyMenuEntryData()
    {
        $sOldLanguage = App()->language;
        App()->setLanguage('en');

        $headerArray = ['menu_id','user_id','ordering','name','title','menu_title','menu_description','menu_icon','menu_icon_type','menu_class','menu_link','action','template','partial','classes','permission','permission_grade','data','getdatamethod','language','active','changed_at','changed_by','created_at','created_by', 'showincollapse'];
        $basicMenues = [
            [1,null,1,'overview', gT('Survey overview','unescaped'),gT('Overview','unescaped'),gT('Open the general survey overview','unescaped'),'list','fontawesome','','admin/survey/sa/view','','','','','','','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,0],
            [1,null,2,'generalsettings', gT('General survey settings','unescaped'),gT('General settings','unescaped'),gT('Open general survey settings','unescaped'),'gears','fontawesome','','','updatesurveylocalesettings_generalsettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_generaloptions_panel','','surveysettings','read',null,'_generalTabEditSurvey','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,1],
            [1,null,3,'surveytexts', gT('Survey text elements','unescaped'),gT('Text elements','unescaped'),gT('Survey text elements','unescaped'),'file-text-o','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/tab_edit_view','','surveylocale','read',null,'_getTextEditData','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,1],
            [1,null,4,'datasecurity', gT('Data policy settings','unescaped'),gT('Data policy settings','unescaped'),gT('Edit data policy settings','unescaped'),'shield','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/tab_edit_view_datasecurity','','surveylocale','read',null,'_getDataSecurityEditData','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,1],
            [1,null,5,'theme_options', gT('Theme options','unescaped'),gT('Theme options','unescaped'),gT('Edit theme options for this survey','unescaped'),'paint-brush','fontawesome','','admin/themeoptions/sa/updatesurvey','','','','','surveysettings','update','{"render": {"link": { "pjaxed": true, "data": {"surveyid": ["survey","sid"], "gsid":["survey","gsid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,0],
            [1,null,6,'presentation', gT('Presentation & navigation settings','unescaped'),gT('Presentation','unescaped'),gT('Edit presentation and navigation settings','unescaped'),'eye-slash','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_presentation_panel','','surveylocale','read',null,'_tabPresentationNavigation','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,0],
            [1,null,7,'tokens', gT('Survey participant settings','unescaped'),gT('Participant settings','unescaped'),gT('Set additional options for survey participants','unescaped'),'users','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_tokens_panel','','surveylocale','read',null,'_tabTokens','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,0],
            [1,null,8,'notification', gT('Notification and data management settings','unescaped'),gT('Notifications & data','unescaped'),gT('Edit settings for notification and data management','unescaped'),'feed','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_notification_panel','','surveylocale','read',null,'_tabNotificationDataManagement','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,0],
            [1,null,9,'publication', gT('Publication & access control settings','unescaped'),gT('Publication & access','unescaped'),gT('Edit settings for publication and access control','unescaped'),'key','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_publication_panel','','surveylocale','read',null,'_tabPublicationAccess','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,0],
            [2,null,1,'listQuestions',gT('Question list','unescaped'),gT('Question list','unescaped'),gT('List questions','unescaped'),'list','fontawesome','','admin/survey/sa/listquestions','','','','','surveycontent','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,1],
            [2,null,2,'listQuestionGroups',gT('Group list','unescaped'),gT('Group list','unescaped'),gT('List question groups','unescaped'),'th-list','fontawesome','','admin/survey/sa/listquestiongroups','','','','','surveycontent','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,1],
            [2,null,3,'responses',gT('Responses','unescaped'),gT('Responses','unescaped'),gT('Responses','unescaped'),'icon-browse','iconclass','','admin/responses/sa/browse/','','','','','responses','read','{"render": {"isActive": true, "link": {"data": {"surveyid": ["survey", "sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,1],
            [2,null,4,'participants', gT('Survey participants','unescaped'),gT('Survey participants','unescaped'),gT('Go to survey participant and token settings','unescaped'),'user','fontawesome','','admin/tokens/sa/index/','','','','','surveysettings','update','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,1],
            [2,null,5,'statistics',gT('Statistics','unescaped'),gT('Statistics','unescaped'),gT('Statistics','unescaped'),'bar-chart','fontawesome','','admin/statistics/sa/index/','','','','','statistics','read','{"render": {"isActive": true, "link": {"data": {"surveyid": ["survey", "sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,1],
            [2,null,6,'quotas', gT('Edit quotas','unescaped'),gT('Quotas','unescaped'),gT('Edit quotas for this survey.','unescaped'),'tasks','fontawesome','','admin/quotas/sa/index/','','','','','quotas','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,0],
            [2,null,7,'assessments', gT('Edit assessments','unescaped'),gT('Assessments','unescaped'),gT('Edit and look at the assessements for this survey.','unescaped'),'comment-o','fontawesome','','admin/assessments/sa/index/','','','','','assessments','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,0],
            [2,null,8,'surveypermissions',gT('Edit survey permissions','unescaped'),gT('Survey permissions','unescaped'),gT('Edit permissions for this survey','unescaped'),'lock','fontawesome','','admin/surveypermission/sa/view/','','','','','surveysecurity','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,0],
            [2,null,9,'emailtemplates', gT('Email templates','unescaped'),gT('Email templates','unescaped'),gT('Edit the templates for invitation, reminder and registration emails','unescaped'),'envelope-square','fontawesome','','admin/emailtemplates/sa/index/','','','','','surveylocale','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,0],
            [2,null,10,'panelintegration', gT('Edit survey panel integration','unescaped'),gT('Panel integration','unescaped'),gT('Define panel integrations for your survey','unescaped'),'link','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_integration_panel','','surveylocale','read','{"render": {"link": { "pjaxed": false}}}','_tabPanelIntegration','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,0],
            [2,null,11,'resources', gT('Add/edit resources (files/images) for this survey','unescaped'),gT('Resources','unescaped'),gT('Add/edit resources (files/images) for this survey','unescaped'),'file','fontawesome','','admin/filemanager','','','','','surveylocale','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','_tabResourceManagement','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,0],
            [2,null,12,'plugins',gT('Simple plugin settings','unescaped'), gT('Simple plugins','unescaped'), gT('Edit simple plugin settings','unescaped'),'plug','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_plugins_panel','','surveysettings','read','{"render": {"link": {"data": {"surveyid": ["survey","sid"]}}}}','_pluginTabSurvey','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,0],
            [3,null,1,'activateSurvey',gT('Activate survey','unescaped'),gT('Activate survey','unescaped'),gT('Activate survey','unescaped'),'play','fontawesome','','admin/survey/sa/activate','','','','','surveyactivation','update','{"render": {"isActive": false, "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,1],
            [3,null,2,'deactivateSurvey', gT('Stop this survey','unescaped'),gT('Stop this survey','unescaped'),gT('Stop this survey','unescaped'),'stop','fontawesome','','admin/survey/sa/deactivate','','','','','surveyactivation','update','{"render": {"isActive": true, "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,1],
            [3,null,3,'testSurvey', gT('Go to survey','unescaped'),gT('Go to survey','unescaped'),gT('Go to survey','unescaped'),'cog','fontawesome','','survey/index/','','','','','','','{"render": {"link": {"external": true, "data": {"sid": ["survey","sid"], "newtest": "Y", "lang": ["survey","language"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,1],
            [3,null,4,'surveyLogicFile',gT('Survey logic file','unescaped'),gT('Survey logic file','unescaped'),gT('Survey logic file','unescaped'),'sitemap','fontawesome','','admin/expressions/sa/survey_logic_file/','','','','','surveycontent','read','{"render": { "link": {"data": {"sid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,1],
            [3,null,5,'cpdb',gT('Central participant database','unescaped'),gT('Central participant database','unescaped'),gT('Central participant database','unescaped'),'users','fontawesome','','admin/participants/sa/displayParticipants','','','','','tokens','read','{"render": {"link": {}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,1],
        ];
        $returnArray = [];

        foreach($basicMenues as $basicMenu) {
            $returnArray[] = array_combine($headerArray, $basicMenu);
        }
        App()->setLanguage($sOldLanguage);
        return $returnArray;
    }

    public static function getTemplateDefaultTexts($mode, $language='en')
    {

        $sOldLanguage = App()->language;
        App()->setLanguage($language);
        $returnArray = array(
            'admin_detailed_notification_subject'=>gT("Response submission for survey {SURVEYNAME} with results", $mode),
            'admin_detailed_notification'=>gT("Hello,\n\nA new response was submitted for your survey '{SURVEYNAME}'.\n\nClick the following link to see the individual response:\n{VIEWRESPONSEURL}\n\nClick the following link to edit the individual response:\n{EDITRESPONSEURL}\n\nView statistics by clicking here:\n{STATISTICSURL}\n\n\nThe following answers were given by the participant:\n{ANSWERTABLE}", $mode),
            'admin_detailed_notification_css'=>'<style type="text/css">
            .printouttable {
            margin:1em auto;
            }
            .printouttable th {
            text-align: center;
            }
            .printouttable td {
            border-color: #ddf #ddf #ddf #ddf;
            border-style: solid;
            border-width: 1px;
            padding:0.1em 1em 0.1em 0.5em;
            }

            .printouttable td:first-child {
            font-weight: 700;
            text-align: right;
            padding-right: 5px;
            padding-left: 5px;

            }
            .printouttable .printanswersquestion td{
            background-color:#F7F8FF;
            }

            .printouttable .printanswersquestionhead td{
            text-align: left;
            background-color:#ddf;
            }

            .printouttable .printanswersgroup td{
            text-align: center;
            font-weight:bold;
            padding-top:1em;
            }
            </style>',
            'admin_notification_subject'=>gT("Response submission for survey {SURVEYNAME}", $mode),
            'admin_notification'=>gT("Hello,\n\nA new response was submitted for your survey '{SURVEYNAME}'.\n\nClick the following link to see the individual response:\n{VIEWRESPONSEURL}\n\nClick the following link to edit the individual response:\n{EDITRESPONSEURL}\n\nView statistics by clicking here:\n{STATISTICSURL}", $mode),
            'confirmation_subject'=>gT("Confirmation of your participation in our survey"),
            'confirmation'=>gT("Dear {FIRSTNAME},\n\nthis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}", $mode),
            'invitation_subject'=>gT("Invitation to participate in a survey", $mode),
            'invitation'=>gT("Dear {FIRSTNAME},\n\nyou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}", $mode)."\n\n".gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}", $mode)."\n\n".gT("If you are blacklisted but want to participate in this survey and want to receive invitations please click the following link:\n{OPTINURL}", $mode),
            'reminder_subject'=>gT("Reminder to participate in a survey", $mode),
            'reminder'=>gT("Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}", $mode)."\n\n".gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}", $mode),
            'registration_subject'=>gT("Survey registration confirmation", $mode),
            'registration'=>gT("Dear {FIRSTNAME},\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\nTo complete this survey, click on the following URL:\n\n{SURVEYURL}\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}.", $mode)
            );
            App()->setLanguage($sOldLanguage);
        return $returnArray;
    }

    public static function getSurveyMenuData()
    {
        $headerArray = [
            'id',
            'parent_id',
            'survey_id',
            'user_id',
            'ordering',
            'level',
            'name',
            'title',
            'position',
            'description',
            'active',
            'changed_at',
            'changed_by',
            'created_at',
            'created_by',
            'showincollapse'
        ];
        $sOldLanguage = App()->language;
        App()->setLanguage('en');
        $returnArray = [];
        $returnArray[] = array_combine($headerArray, [1,null,null,null,1,0,'settings',gT('Survey settings'),'side',gT('Survey settings'),1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,1]);
        $returnArray[] = array_combine($headerArray, [2,null,null,null,2,0,'mainmenu',gT('Survey menu'),'side',gT('Main survey menu'),1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,1]);
        $returnArray[] = array_combine($headerArray, [3,null,null,null,3,0,'quickmenu',gT('Quick menu'),'collapsed',gT('Quick menu'),1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0,0]);
        App()->setLanguage($sOldLanguage);

        return $returnArray;
    }

    public static function getBoxesData()
    {
        $sOldLanguage = App()->language;
        App()->setLanguage('en');
        $returnArray = [];
        $returnArray[] = ['position' => 1, 'url' => 'admin/survey/sa/newsurvey', 'title' => gT('Create survey'), 'ico' => 'icon-add', 'desc' => gT('Create a new survey'), 'page' => 'welcome', 'usergroup' => '-2'];
        $returnArray[] = ['position' => 2, 'url' => 'admin/survey/sa/listsurveys', 'title' => gT('List surveys'), 'ico' => 'icon-list', 'desc' => gT('List available surveys'), 'page' => 'welcome', 'usergroup' => '-1'];
        $returnArray[] = ['position' => 3, 'url' => 'admin/globalsettings', 'title' => gT('Global settings'), 'ico' => 'icon-settings', 'desc' => gT('Edit global settings'), 'page' => 'welcome', 'usergroup' => '-2'];
        $returnArray[] = ['position' => 4, 'url' => 'admin/update', 'title' => gT('ComfortUpdate'), 'ico' => 'icon-shield', 'desc' => gT('Stay safe and up to date'), 'page' => 'welcome', 'usergroup' => '-2'];
        $returnArray[] = ['position' => 5, 'url' => 'https://account.limesurvey.org/limestore', 'title' => 'LimeStore', 'ico' => 'fa fa-cart-plus', 'desc' => gT('LimeSurvey extension marketplace'), 'page' => 'welcome', 'usergroup' => '-2'];
        $returnArray[] = ['position' => 6, 'url' => 'admin/themeoptions', 'title' => gT('Themes'), 'ico' => 'icon-templates', 'desc' => gT('Themes'), 'page' => 'welcome', 'usergroup' => '-2'];

        App()->setLanguage($sOldLanguage);
        return $returnArray;

    }

    public static function getTemplateConfigurationData()
    {
        $returnArray = [];

        $returnArray[] = [
            'template_name'     =>  'vanilla',
            'sid'               =>  null,
            'gsid'              =>  null,
            'uid'               =>  null,
            'files_css'         => '{"add":["css/ajaxify.css","css/theme.css","css/custom.css"]}',
            'files_js'          =>  '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
            'files_print_css'   => '{"add":["css/print_theme.css"]}',
            'options'           => '{"ajaxmode":"off","brandlogo":"on","container":"on", "hideprivacyinfo": "off", "brandlogofile":"themes/survey/vanilla/files/logo.png","font":"noto", "showpopups":"1", "showclearall":"off", "questionhelptextposition":"top"}',
            'cssframework_name' => 'bootstrap',
            'cssframework_css'  => '{}',
            'cssframework_js'   => '',
            'packages_to_load'  => '{"add":["pjax","font-noto","moment"]}',
            'packages_ltr'      => null,
            'packages_rtl'      => null
        ];
        $returnArray[] = [
            'template_name'     =>  'fruity',
            'sid'               =>  null,
            'gsid'              =>  null,
            'uid'               =>  null,
            'files_css'         => '{"add":["css/ajaxify.css","css/animate.css","css/variations/sea_green.css","css/theme.css","css/custom.css"]}',
            'files_js'          => '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
            'files_print_css'   => '{"add":["css/print_theme.css"]}',
            'options'           => '{"ajaxmode":"off","brandlogo":"on","brandlogofile":"themes/survey/fruity/files/logo.png","container":"on","backgroundimage":"off","backgroundimagefile":null,"animatebody":"off","bodyanimation":"fadeInRight","bodyanimationduration":"500","animatequestion":"off","questionanimation":"flipInX","questionanimationduration":"500","animatealert":"off","alertanimation":"shake","alertanimationduration":"500","font":"noto","bodybackgroundcolor":"#ffffff","fontcolor":"#444444","questionbackgroundcolor":"#ffffff","questionborder":"on","questioncontainershadow":"on","checkicon":"f00c","animatecheckbox":"on","checkboxanimation":"rubberBand","checkboxanimationduration":"500","animateradio":"on","radioanimation":"zoomIn","radioanimationduration":"500","zebrastriping":"off","stickymatrixheaders":"off","greyoutselected":"off","hideprivacyinfo":"off","crosshover":"off","showpopups":"1", "showclearall":"off", "questionhelptextposition":"top","notables":"1"}',
            'cssframework_name' => 'bootstrap',
            'cssframework_css'  => '{}',
            'cssframework_js'   => '',
            'packages_to_load'  => '{"add":["pjax","font-noto","moment"]}',
            'packages_ltr'      => null,
            'packages_rtl'      => null
        ];
        $returnArray[] = [
            'template_name'     =>  'bootswatch',
            'sid'               =>  null,
            'gsid'              =>  null,
            'uid'               =>  null,
            'files_css'         => '{"add":["css/ajaxify.css","css/theme.css","css/custom.css"]}',
            'files_js'          =>  '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
            'files_print_css'   => '{"add":["css/print_theme.css"]}',
            'options'           => '{"ajaxmode":"off","brandlogo":"on","container":"on","brandlogofile":"themes/survey/bootswatch/files/logo.png", "showpopups":"1", "showclearall":"off", "questionhelptextposition":"top"}',
            'cssframework_name' => 'bootstrap',
            'cssframework_css'  => '{"replace":[["css/bootstrap.css","css/variations/flatly.min.css"]]}',
            'cssframework_js'   => '',
            'packages_to_load'  => '{"add":["pjax","font-noto","moment"]}',
            'packages_ltr'      => null,
            'packages_rtl'      => null
        ];

        return $returnArray;
    }


    public static function getSurveygroupData()
    {

        $returnArray = [
            [
            'name' => 'default',
            'title' => 'Default',
            'template' =>  null,
            'description' => 'Default survey group',
            'sortorder' => 0,
            'owner_id' => 1,
            'parent_id' => null,
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s'),
            'created_by' => 1
            ]
        ];

        return $returnArray;
    }


    public static function getTemplatesData()
    {
        $returnArray = [];

        $returnArray[] = [
            'name'          => 'vanilla',
            'folder'        => 'vanilla',
            'title'         => 'Vanilla Theme',
            'creation_date' => date('Y-m-d H:i:s'),
            'author'        =>'Louis Gac',
            'author_email'  => 'louis.gac@limesurvey.org',
            'author_url'    => 'https://www.limesurvey.org/',
            'copyright'     => 'Copyright (C) 2007-2019 The LimeSurvey Project Team\\r\\nAll rights reserved.',
            'license'       => 'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.',
            'version'       => '3.0',
            'api_version'   => '3.0',
            'view_folder'   => 'views',
            'files_folder'  => 'files',
            'description'   => '<strong>LimeSurvey Bootstrap Vanilla Survey Theme</strong><br>A clean and simple base that can be used by developers to create their own Bootstrap based theme.',
            'last_update'   => null,
            'owner_id'      => 1,
            'extends'       => '',
        ];
        $returnArray[] = [
            'name'          => 'fruity',
            'folder'        => 'fruity',
            'title'         => 'Fruity Theme',
            'creation_date' => date('Y-m-d H:i:s'),
            'author'        =>'Louis Gac',
            'author_email'  => 'louis.gac@limesurvey.org',
            'author_url'    => 'https://www.limesurvey.org/',
            'copyright'     => 'Copyright (C) 2007-2019 The LimeSurvey Project Team\\r\\nAll rights reserved.',
            'license'       => 'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.',
            'version'       => '3.0',
            'api_version'   => '3.0',
            'view_folder'   => 'views',
            'files_folder'  => 'files',
            'description'   => '<strong>LimeSurvey Fruity Theme</strong><br>A fruity theme for a flexible use. This theme offers monochromes variations and many options for easy customizations.',
            'last_update'   => null,
            'owner_id'      => 1,
            'extends'       => 'vanilla',
        ];
        $returnArray[] = [
            'name'          => 'bootswatch',
            'folder'        => 'bootswatch',
            'title'         => 'Bootswatch Theme',
            'creation_date' => date('Y-m-d H:i:s'),
            'author'        =>'Louis Gac',
            'author_email'  => 'louis.gac@limesurvey.org',
            'author_url'    => 'https://www.limesurvey.org/',
            'copyright'     => 'Copyright (C) 2007-2019 The LimeSurvey Project Team\\r\\nAll rights reserved.',
            'license'       => 'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.',
            'version'       => '3.0',
            'api_version'   => '3.0',
            'view_folder'   => 'views',
            'files_folder'  => 'files',
            'description'   => '<strong>LimeSurvey Bootwatch Theme</strong><br>Based on BootsWatch Themes: <a href="https://bootswatch.com/3/"">Visit BootsWatch page</a> ',
            'last_update'   => null,
            'owner_id'      => 1,
            'extends'       => 'vanilla',
        ];

        return $returnArray;
    }

    public static function getTutorialData()
    {
        $returnArray = [];
        $returnArray['firstStartTour'] = [
            'name' => 'firstStartTour',
            'title' => gT('Beginner tour'),
            'icon' => 'fa-rocket',
            'description' => 'The first start tour to get your first feeling into LimeSurvey',
            'active' => 1,
            'settings' => json_encode(array(
                'keyboard' => false,
                // 'orphan' => true,
                'template' => ""
                ."<div class='popover tour lstutorial__template--mainContainer'>"
                ."<div class='arrow'></div>"
                ."<button class='pull-right ls-space margin top-5 right-5 btn btn-warning btn-sm' data-role='end' data-toggle='tooltip' title='".gT('End tour','js')."'><i class='fa fa-close'></i></button>"
                ."<h3 class='popover-title lstutorial__template--title'></h3>"
                    ."<div class='popover-content lstutorial__template--content'></div>"
                    ."<div class='popover-navigation lstutorial__template--navigation'>"
                        ."<div class='row'>"
                            ."<div class='btn-group col-xs-12' role='group' aria-label='...'>"
                                ."<button class='btn btn-default col-md-6' data-role='prev'>".gT('Previous','js')."</button>"
                                ."<button class='btn btn-primary col-md-6' data-role='next'>".gT('Next','js')."</button>"
                            ."</div>"
                        ."</div>"
                    ."</div>"
                ."</div>",
                'onShown' => "(function(tour){ $('#notif-container').children().remove(); })",
                'onEnd' => "(function(tour){window.location.reload();})",
                // 'endOnOrphan' => true,
            )),
            'permission' => 'survey',
            'permission_grade' => 'create'
        ];

        return $returnArray;
    }

    public static function getTutorialEntryData()
    {
        $returnArray =[];
        $returnArray['firstStartTour'] = array(
            array(
                'teid' => 1,
                'ordering' => 1,
                'title' => gT('Welcome to LimeSurvey!'),
                'content' => gT("This tour will help you to easily get a basic understanding of LimeSurvey.")."<br/>"
                    .gT("We would like to help you with a quick tour of the most essential functions and features."),
                'settings' => json_encode(
                    array(
                        'element' => '#lime-logo',
                        'delayOnElement' => "{element: 'element'}",
                        'path' => ['/admin/index'],
                        'placement' => 'bottom',
                        'redirect' => true,
                        'onShow' => "(function(tour){ $('#welcomeModal').modal('hide'); })"
                        )
                )
            ),
            array(
                'teid' => 2,
                'ordering' => 2,
                'title' => gT('The basic functions'),
                'content' => gT("The three top boxes are the most basic functions of LimeSurvey.")."<br/>"
                    .gT("From left to right it should be 'Create survey', 'List surveys' and 'Global settings'. Best we start by creating a survey.")
                    .'<p class="alert bg-warning">'.gT("Click on the 'Create survey' box - or 'Next' in this tutorial").'</p>',
                'settings' => json_encode(array(
                    'element' => '.selector__create_survey',
                    'path' => ['/admin/index'],
                    'reflex' => true,
                    'onShow' => "(function(tour){ $('#welcomeModal').modal('hide'); })",
                    'onNext' => "(function(tour){ })",
                ))
            ),
            array(
                'teid' => 3,
                'ordering' => 3,
                'title' => gT('The survey title'),
                'content' => gT("This is the title of your survey.")."<br/>"
                .gT("Your participants will see this title in the browser's title bar and on the welcome screen.")
                ."<p class='bg-warning alert'>".gT("You have to put in at least a title for the survey to be saved.").'</p>',
                'settings' => json_encode(array(
                    'path' => ['/admin/survey/sa/newsurvey'],
                    'delayOnElement' => "{
                        element: '#surveyls_title',
                        maxDelay: 1000
                    }",
                    'element' => '#surveyls_title',
                    'redirect' => true,
                    'prev' => '-1',
                    'onNext' => "(function(tour){
                        if( $('#surveyls_title').val() == '' ) {
                            return false;
                        }
                    })",
                ))
            ),
            array(
                'teid' => 4,
                'ordering' => 4,
                'title' => gT('The survey description'),
                'content' => gT("In this field you may type a short description of your survey.")."<br/>"
                .gT("The text inserted here will be displayed on the welcome screen, which is the first thing that your respondents will see when they access your survey..").' '
                .gT("Describe your survey, but do not ask any question yet."),
                'settings' => json_encode(array(
                    'element' => '#cke_description',
                    'path' => ['/admin/survey/sa/newsurvey'],
                    'placement' => 'top',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 5,
                'ordering' => 5,
                'title' => gT('Create a sample question and question group'),
                'content' => gT("We will be creating a question group and a question in this tutorial. There is need to automatically create them."),
                'settings' => json_encode(array(
                    'element' => '.bootstrap-switch-id-createsample',
                    'path' => ['/admin/survey/sa/newsurvey'],
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 6,
                'ordering' => 6,
                'title' => gT('The welcome message'),
                'content' => gT("This message is shown directly below the survey description on the welcome page. You may leave this blank for now but it is a good way to introduce your participants to the survey."),
                'settings' => json_encode(array(
                    'element' => '#cke_welcome',
                    'placement' => 'top',
                    'path' => ['/admin/survey/sa/newsurvey'],
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 7,
                'ordering' => 7,
                'title' => gT('The end message'),
                'content' => gT("This message is shown at the end of your survey to every participant. It's a great way to say thank you or give some links or hints where to go next."),
                'settings' => json_encode(array(
                    'element' => '#cke_endtext',
                    'path' => ['/admin/survey/sa/newsurvey'],
                    'placement' => 'top',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 8,
                'ordering' => 8,
                'title' => gT('Now save your survey'),
                'content' => gT("You may play around with more settings, but let's save and start adding questions to your survey now. Just click on 'Save'."),
                'settings' => json_encode(array(
                    'element' => '#save-form-button',
                    'path' => ['/admin/survey/sa/newsurvey'],
                    'placement' => 'bottom',
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    tour.setCurrentStep(8);
                                    if(!$('#save-form-button').hasClass('disabled'))
                                        $('#save-form-button').trigger('click');
                                    return new Promise(function(res,rej){});
                                })",
                ))
            ),
            array(
                'teid' => 9,
                'ordering' => 9,
                'title' => gT('The sidebar'),
                'content' => gT('This is the sidebar.').'<br/>'
                .gT('All important settings can be reached in this sidebar.').'<br/>'
                .gT('The most important settings of your survey can be reached from this sidebar: the survey settings menu and the survey structure menu. You may resize it to fit your screen to easily navigate through the available options. If the size of the sidebar is too small, the options get collapsed and the quick-menu is displayed. If you wish to work from the quick-menu, either click on the arrow button or drag it to the left.'),
                'settings' => json_encode(array(
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}']],
                    'delayOnElement' => "{element: 'element'}",
                    'element' => '#sidebar',
                    'placement' => 'right',
                    'redirect' => false,
                    'prev' => '-1',
                    'onShow' => "(function(tour){
                        $('#adminsidepanel__sidebar--selectorSettingsButton').trigger('click');
                    })",
                ))
            ),
            array(
                'teid' => 10,
                'ordering' => 10,
                'title' => gT('The settings tab with the survey menu'),
                'content' => gT('If you click on this tab, the survey settings menu will be displayed. The most important settings of your survey are accessible from this menu.').'<br/>'
                .gT('If you want to know more about them, check our manual.'),
                'settings' => json_encode(array(
                    'element' => '#adminsidepanel__sidebar--selectorSettingsButton',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 11,
                'ordering' => 11,
                'title' => gT('The top bar'),
                'content' => gT('This is the top bar.').'<br/>'
                .gT('This bar will change as you move through the functionalities. The current bar corresponds to the "overview" tab. It contains the most important LimeSurvey functionalities such as preview and activate survey.'),
                'settings' => json_encode(array(
                    'element' => '#surveybarid',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 12,
                'ordering' => 12,
                'title' => gT('The survey structure'),
                'content' => gT('This is the structure view of your survey. Here you can see all your groups and questions.'),
                'settings' => json_encode(array(
                    'element' => '#adminsidepanel__sidebar--selectorStructureButton',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'redirect' => false,
                    'onShow' => "(function(tour){
                                    $('#adminsidepanel__sidebar--selectorStructureButton').trigger('click');
                                })",
                ))
            ),
            array(
                'teid' => 13,
                'ordering' => 13,
                'title' => gT("Let's add a question group"),
                'content' => gT("What good would your survey be without questions?").'<br/>'
                .gT('In LimeSurvey a survey is organized in groups and questions. To begin creating questions, we first need a question group.')
                .'<p class="alert bg-warning">'.gT("Click on the 'Add group' button").'</p>',
                'settings' => json_encode(array(
                    'element' => '#adminsidepanel__sidebar--selectorCreateQuestionGroup',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'right',
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    document.location.href = $('#adminsidepanel__sidebar--selectorCreateQuestionGroup').attr('href');
                                    tour.setCurrentStep(13);
                                    return new Promise(function(res,rej){});
                                })",
                ))
            ),
            array(
                'teid' => 14,
                'ordering' => 14,
                'title' => gT('Enter a title for your first question group'),
                'content' => gT('The title of the question group is visible to your survey participants (this setting can be changed later and it cannot be empty). Question groups are important because they allow the survey administrators to logically group the questions. By default, each question group (including its questions) is shown on its own page (this setting can be changed later).'),
                'settings' => json_encode(array(
                    'element' => '#group_name_en',
                    'delayOnElement' => "{
                        element: '#group_name_en',
                        maxDelay: 1000
                    }",
                    'path' => ['/admin/questiongroups/sa/add', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'redirect' => false,
                    'prev' => '-1',
                ))
            ),
            array(
                'teid' => 15,
                'ordering' => 15,
                'title' => gT('A description for your question group'),
                'content' => gT('This description is also visible to your participants.').'<br/>'
                .gT('You do not need to add a description to your question group, but sometimes it makes sense to add a little extra information for your participants.'),
                'settings' => json_encode(array(
                    'element' => 'label[for=description_en]',
                    'path' => ['/admin/questiongroups/sa/add', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'top',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 16,
                'ordering' => 16,
                'title' => gT('Advanced settings'),
                'content' => gT("For now it's best to leave these additional settings as they are. If you want to know more about randomization and relevance settings, have a look at our manual."),
                'settings' => json_encode(array(
                    'element' => '#randomization_group',
                    'path' => ['/admin/questiongroups/sa/add', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'right',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 17,
                'ordering' => 17,
                'title' => gT('Save and add a new question'),
                'content' => gT("Now when you are finished click on 'Save and add question'.").'<br/>'
                .gT('This will directly add a question to the current question group.')
                .'<p class="alert bg-warning">'.gT("Now click on 'Save and add question'.").'</p>',
                'settings' => json_encode(array(
                    'element' => '#save-and-new-question-button',
                    'path' => ['/admin/questiongroups/sa/add', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    $('#save-and-new-question-button').trigger('click');
                                    tour.setCurrentStep(17);
                                    return new Promise(function(res,rej){});
                                })",
                ))
            ),
            array(
                'teid' => 18,
                'ordering' => 18,
                'title' => gT('Set your question type.'),
                'content' => gT("LimeSurvey offers you a lot of different question types.").'<br/>'
                .gT("As you can see, the preselected question type is the 'Long free text' one. We will use in this example the 'Array' question type.").'<br/>'
                .gT("This type of question allows you to add multiple subquestions and a set of answers.")
                .'<p class="alert bg-warning">'.gT("Please select the 'Array'-type.").'</p>',
                'settings' => json_encode(array(
                    'element' => '#selector---select-questiontype-label',
                    'delayOnElement' => "{
                        element: '#selector---select-questiontype-label',
                        maxDelay: 2500
                    }",
                    'delay' => 500,
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'placement' => 'left',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 19,
                'ordering' => 19,
                'title' => gT('The title of your question'),
                'content' =>
                gT("This code is normally not shown to your participants, still it is necessary and has to be unique for the survey.").'<br>'
                .gT("This code is also the name of the variable that will be exported to SPSS or Excel.")
                .'<p class="alert bg-warning">'.gT("Please type in a code that consists only of letters and numbers, and doesn't start with a number.").'</p>',
                'settings' => json_encode(array(
                    'element' => '#title',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'placement' => 'top',
                    'redirect' => false,
                    'prev' => '-1',
                ))
            ),
            array(
                'teid' => 20,
                'ordering' => 20,
                'title' => gT('The actual question text'),
                'content' => gT('The content of this box is the actual question text shown to your participants. It may be empty, but that is not recommended. You may use all the power of our WYSIWYG editor to make your question shine.'),
                'settings' => json_encode(array(
                    'element' => '#cke_question_en',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'placement' => 'top',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 21,
                'ordering' => 21,
                'title' => gT('An additional help text for your question'),
                'content' => gT('You can add some additional help text to your question. If you decide not to offer any additional question hints, then no help text will be displayed to your respondents.'),
                'settings' => json_encode(array(
                    'element' => '#cke_help_en',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'placement' => 'top',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 22,
                'ordering' => 22,
                'title' => gT('Now save the created question'),
                'content' => gT('Next, we will create subquestions and answer options.').'<br/>'
                    .gT('Please remember that in order to have a valid code, it must contain only letters and numbers, also please check that it starts with a letter.'),
                'settings' => json_encode(array(
                    'element' => '#save-button',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'placement' => 'left',
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    $('#question_type').val('F');
                                    $('#save-button').trigger('click');
                                    tour.setCurrentStep(22);
                                    return new Promise(function(res,rej){});
                                })",
                ))
            ),
            array(
                'teid' => 23,
                'ordering' => 23,
                'title' => gT('The question bar'),
                'content' => gT('This is the question bar.').'<br/>'
                    .gT('The most important question-related options are displayed here.').'<br/>'
                    .gT('The availability of options is related to the type of question you previously chose.'),
                'settings' => json_encode(array(
                    'element' => '#questionbarid',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'backdrop' => false,
                    'redirect' => false,
                    'prev' => '-1',
                ))
            ),
            array(
                'teid' => 24,
                'ordering' => 24,
                'title' => gT('Add some subquestions to your question'),
                'content' => gT("The array question is a type that creates a matrix for the participant.").'<br/>'
                    .gT("To fully use it, you have to add subquestions as well as answer options.").'<br/>'
                    .gT("Let's start with subquestions.")
                    .'<p class="alert bg-warning">'.gT("Click on the 'Edit subquestions' button.").'</p>',
                'settings' => json_encode(array(
                    'element' => '#adminpanel__topbar--selectorAddSubquestions',
                    'delayOnElement' => "{
                        element: '#adminpanel__topbar--selectorAddSubquestions',
                        maxDelay: 1000
                    }",
                    'placement' => 'bottom',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    document.location.href = $('#adminsidepanel__topbar--selectorAddSubquestions').attr('href');
                                    tour.setCurrentStep(24);
                                    return new Promise(function(res,rej){});
                                })",
                ))
            ),
            array(
                'teid' => 25,
                'ordering' => 25,
                'title' => gT('Edit subquestions'),
                'content' => gT("You should add some subquestions for your question here.").'<br/>'
                .gT("Every row is one subquestion. We recommend the usage of logical or numerical codes for subquestions. Your participants cannot see the subquestion code, only the subquestion text itself.")
                ."<p class='bg-info alert'>".gT("Pro tip: The subquestion may even contain HTML code.").'</p>',
                'settings' => json_encode(array(
                    'element' => '#rowcontainer',
                    'delayOnElement' => "{element: 'element'}",
                    'path' => ['admin/questions/sa/subquestions/surveyid/[0-9]{4,25}/gid/[0-9]{1,25}/qid/[0-9]{4,25}'],
                    'placement' => 'bottom',
                    'redirect' => false,
                    'prev' => '-1',
                ))
            ),
            array(
                'teid' => 26,
                'ordering' => 26,
                'title' => gT('Add subquestion row'),
                'content' => sprintf(gT('Click on the plus sign %s to add another subquestion to your question.'), '<i class="icon-add text-success"></i>')
                ."<p class='bg-warning alert'>".gT('Please add at least two subquestions')."</p>",
                'settings' => json_encode(array(
                    'element' => '#rowcontainer>tr:first-of-type .btnaddanswer',
                    'path' => ['admin/questions/sa/subquestions/surveyid/[0-9]{4,25}/gid/[0-9]{1,25}/qid/[0-9]{4,25}'],
                    'placement' => 'left',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 27,
                'ordering' => 27,
                'title' => gT('Now save the subquestions'),
                'content' => gT("You may save empty subquestions, but that would be pointless.")
                ."<p class='bg-warning alert'>".gT("Save and close now and let's edit the answer options.").'</p>',
                'settings' => json_encode(array(
                    'element' => '#save-and-close-button',
                    'path' => ['admin/questions/sa/subquestions/surveyid/[0-9]{4,25}/gid/[0-9]{1,25}/qid/[0-9]{4,25}'],
                    'placement' => 'left',
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    $('#save-and-close-button').trigger('click');
                                    tour.setCurrentStep(27);
                                    return new Promise(function(res,rej){});
                                })"
                ))
            ),
            array(
                'teid' => 28,
                'ordering' => 28,
                'title' => gT('Add some answer options to your question'),
                'content' => gT("Now that we've got some subquestions, we have to add answer options as well.").'<br/>'
                .gT("The answer options will be shown for each subquestion.")
                .'<p class="alert bg-warning">'.gT("Click on the 'Edit answer options' button.").'</p>',
                'settings' => json_encode(array(
                    'element' => '#adminpanel__topbar--selectorAddAnswerOptions',
                    'delayOnElement' => "{
                        element: '#adminpanel__topbar--selectorAddAnswerOptions',
                        maxDelay: 1000
                    }",
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'reflex' => true,
                    'redirect' => false,
                    'prev' => '-1',
                    'onNext' => "(function(tour){
                                    document.location.href = $('#adminsidepanel__topbar--selectorAddAnswerOptions').attr('href');
                                    tour.setCurrentStep(28);
                                    return new Promise(function(res,rej){});
                                })",
                ))
            ),
            array(
                'teid' => 29,
                'ordering' => 29,
                'title' => gT('Edit answer options'),
                'content' => gT("As you can see, editing answer options is quite similar to editing subquestions.").'<br/>'
                .gT('Remember the plus button').'<i class="icon-add text-success"></i>?'.'<br/>'
                .'<p class="alert bg-warning">'.gT("Please add at least two answer options to proceed.").'</p>',
                'settings' => json_encode(array(
                    'element' => '#rowcontainer',
                    'delayOnElement' => "{element: 'element'}",
                    'path' => ['admin/questions/sa/answeroptions/surveyid/[0-9]{4,25}/gid/[0-9]{1,25}/qid/[0-9]{4,25}'],
                    'placement' => 'bottom',
                    'redirect' => false,
                    'prev' => '-1',
                ))
            ),
            array(
                'teid' => 30,
                'ordering' => 30,
                'title' => gT('Now save the answer options'),
                'content' => gT("Click on 'Save and close' or 'Next' to proceed."),
                'settings' => json_encode(array(
                    'element' => '#save-and-close-button',
                    'path' => ['admin/questions/sa/answeroptions/surveyid/[0-9]{4,25}/gid/[0-9]{1,25}/qid/[0-9]{4,25}'],
                    'placement' => 'left',
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    $('#save-and-close-button').trigger('click');
                                    tour.setCurrentStep(30);
                                    return new Promise(function(res,rej){});
                                })"
                ))
            ),
            array(
                'teid' => 31,
                'ordering' => 31,
                'title' => gT('Preview survey'),
                'content' => gT("Let's have a look at your first survey.").'<br/>'
                .gT("Just click on this button and a new window will open, where you can test run your survey.").'<br/>'
                .gT("Please be aware that your answers will not be saved, because the survey isn't active yet.")
                .'<p class="alert bg-warning">'.gT("Click on 'Preview survey' and return to this window when you are done testing.").'</p>',
                'settings' => json_encode(array(
                    'element' => '.selector__topbar--previewSurvey',
                    'delayOnElement' => "{
                        element: '.selector__topbar--previewSurvey',
                        maxDelay: 1000
                    }",
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'redirect' => false,
                    'prev' => '-1',
                ))
            ),
            array(
                'teid' => 32,
                'ordering' => 32,
                'title' => gT('Easy navigation with the "breadcrumbs"'),
                'content' => gT('You can see the "breadcrumbs" In the top bar of the admin interface.').'<br/>'
                .gT("They represent an easy way to get back to any previous setting, and provide a general overview of where you are.")
                .'<p class="alert bg-warning">'.gT("Click on the name of your survey to get back to the survey settings overview.").'</p>',
                'settings' => json_encode(array(
                    'element' => '#breadcrumb__survey--overview',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    tour.setCurrentStep(32);
                                    document.location.href = $('#breadcrumb__survey--overview').attr('href');
                                    return new Promise(function(res,rej){});
                                })",
                ))
            ),
            array(
                'teid' => 33,
                'ordering' => 33,
                'title' => gT('Finally, activate your survey'),
                'content' => gT("Now, activate your survey.").'<br/>'
                .gT("You can create as many surveys as you like.")
                .'<p class="alert bg-warning">'.gT("Click on 'Activate this survey'").'</p>',
                'settings' => json_encode(array(
                    'element' => '#ls-activate-survey',
                    'delayOnElement' => "{element: 'element'}",
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'reflex' => true,
                    'redirect' => false,
                    'prev' => '-1',
                    'onNext' => "(function(tour){
                            document.location.href = $('#ls-activate-survey').attr('href');
                            tour.setCurrentStep(33);
                            return new Promise(function(res,rej){});
                        })",
                ))
            ),
            array(
                'teid' => 34,
                'ordering' => 34,
                'title' => gT('Activation settings'),
                'content' => gT('These settings cannot be changed once the survey is online.').'<br/>'
                .gT("For this simple survey the default settings are ok, but read the disclaimer carefully when you activate your own surveys.").'<br/>'
                .gT("For more information consult our manual, or our forums.")
                .'<p class="alert bg-warning">'.gT('Now click on "Save & activate survey"').'</p>',
                'settings' => json_encode(array(
                    'element' => '#activateSurvey__basicSettings--proceed',
                    'delayOnElement' => "{
                        element: '#activateSurvey__basicSettings--proceed',
                        maxDelay: 1000
                    }",
                    'path' => ['/admin/survey/sa/activate', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => '',
                    'reflex' => true,
                    'redirect' => false,
                    'prev' => '-1',
                    'onNext' => "(function(tour){
                            $('#activateSurvey__basicSettings--proceed').trigger('click');
                            tour.setCurrentStep(34);
                            return new Promise(function(res,rej){});
                        })",
                ))
            ),
            array(
                'teid' => 35,
                'ordering' => 35,
                'title' => gT('Activate survey participants table'),
                'content' => gT("Here you can select to start your survey in closed access mode.")."<br/>"
                .gT("For our simple survey it is better to start in open access mode.")."<br/>"
                .gT("The closed access mode needs a participant list, which you may create by clicking on the menu entry 'Participants'.")."<br/>"
                .gT("For more information please consult our manual or our forum.")
                .'<p class="alert bg-warning">'.gT("Click on 'No, thanks'").'</p>',
                'settings' => json_encode(array(
                    'element' => '#activateTokenTable__selector--no',
                    'delayOnElement' => "{
                        element: '#activateTokenTable__selector--no',
                        maxDelay: 1000
                    }",
                    'path' => ['/admin/survey/sa/activate', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'reflex' => true,
                    'redirect' => false,
                    'prev' => '-1',
                    'onNext' => "(function(tour){
                            $('#activateTokenTable__selector--no').trigger('click');
                            tour.setCurrentStep(35);
                            return new Promise(function(res,rej){});
                        })",
                ))
            ),
            array(
                'teid' => 36,
                'ordering' => 36,
                'title' => gT('Share this link'),
                'content' => gT("Just share this link with some of your friends and of course, test it yourself.")
                .'<p class="alert bg-success lstutorial__typography--white">'.gT("Thank you for taking the tour!").'</p>',
                'settings' => json_encode(array(
                    'element' => '#adminpanel__surveysummary--mainLanguageLink',
                    'delayOnElement' => "{
                        element: '#adminpanel__surveysummary--mainLanguageLink',
                        maxDelay: 1000
                    }",
                    'path' => ['/'.'(index.php)?'],
                    'placement' => 'top',
                    'redirect' => false,
                    'prev' => '-1',
                    'onHide' => '(function(){window.location.reload()})'
                ))
            ),
        );

        return $returnArray;
    }

    /**
     * Get data for plugins installed by default.
     * Also install all core plugins, but set to inactive.
     *
     * @return array
     */
    public static function getDefaultPluginsData()
    {
        /**
         * @param string $name Name of plugin
         * @param int $active
         * @return array
         */
        $addRow = function ($name, $active = 0) {
            return [
                'id' => null,
                'name'               => $name,
                'plugin_type'        => 'core',
                'active'             => $active,
                'version'            => '1.0.0',
                'load_error'         => 0,
                'load_error_message' => null
            ];
        };

        return [
            $addRow('UpdateCheck', 1),
            $addRow('PasswordRequirement', 1),
            $addRow('Authdb', 1),
            // Inactive plugins below.
            $addRow('AuthLDAP'),
            $addRow('AuditLog'),
            $addRow('Authwebserver'),
            $addRow('ExportR', 1),
            $addRow('ExportSTATAxml', 1),
            $addRow('oldUrlCompat'),
            $addRow('expressionQuestionHelp'),
            $addRow('expressionQuestionForAll'),
            $addRow('expressionFixedDbVar'),
            $addRow('customToken'),
            $addRow('mailSenderToFrom'),
        ];
    }

    public static function getDefaultUserSettings()
    {
        return [
            ['stg_name' => 'editorPreset', 'stg_value' => 'wysiwyg'],
            ['stg_name' => 'showScriptEditor', 'stg_value' => '0'],
            ['stg_name' => 'noViewMode', 'stg_value' => '0'],
            ['stg_name' => 'answeroptionprefix', 'stg_value' => 'AO'],
            ['stg_name' => 'subquestionprefix', 'stg_value' => 'SQ'],
            ['stg_name' => 'lock_organizer', 'stg_value' => '0'],
        ];
    }

    public static function fileTypeIcons() {
        /**
         * Copied from https://github.com/DirectoryLister/DirectoryLister
         * Copyright 2017 Chris Kankiewicz 
         */
        return array(

            // Archives
            '7z'    => 'fa-file-archive-o',
            'bz'    => 'fa-file-archive-o',
            'gz'    => 'fa-file-archive-o',
            'rar'   => 'fa-file-archive-o',
            'tar'   => 'fa-file-archive-o',
            'zip'   => 'fa-file-archive-o',

            // Audio
            'aac'   => 'fa-music',
            'flac'  => 'fa-music',
            'mid'   => 'fa-music',
            'midi'  => 'fa-music',
            'mp3'   => 'fa-music',
            'ogg'   => 'fa-music',
            'wma'   => 'fa-music',
            'wav'   => 'fa-music',

            // Code
            'c'     => 'fa-code',
            'class' => 'fa-code',
            'cpp'   => 'fa-code',
            'css'   => 'fa-code',
            'erb'   => 'fa-code',
            'htm'   => 'fa-code',
            'html'  => 'fa-code',
            'java'  => 'fa-code',
            'js'    => 'fa-code',
            'php'   => 'fa-code',
            'pl'    => 'fa-code',
            'py'    => 'fa-code',
            'rb'    => 'fa-code',
            'xhtml' => 'fa-code',
            'xml'   => 'fa-code',

            // Databases
            'accdb' => 'fa-hdd-o',
            'db'    => 'fa-hdd-o',
            'dbf'   => 'fa-hdd-o',
            'mdb'   => 'fa-hdd-o',
            'pdb'   => 'fa-hdd-o',
            'sql'   => 'fa-hdd-o',

            // Documents
            'csv'   => 'fa-file-text',
            'doc'   => 'fa-file-text',
            'docx'  => 'fa-file-text',
            'odt'   => 'fa-file-text',
            'pdf'   => 'fa-file-text',
            'xls'   => 'fa-file-text',
            'xlsx'  => 'fa-file-text',

            // Executables
            'app'   => 'fa-list-alt',
            'bat'   => 'fa-list-alt',
            'com'   => 'fa-list-alt',
            'exe'   => 'fa-list-alt',
            'jar'   => 'fa-list-alt',
            'msi'   => 'fa-list-alt',
            'vb'    => 'fa-list-alt',

            // Fonts
            'eot'   => 'fa-font',
            'otf'   => 'fa-font',
            'ttf'   => 'fa-font',
            'woff'  => 'fa-font',

            // Game Files
            'gam'   => 'fa-gamepad',
            'nes'   => 'fa-gamepad',
            'rom'   => 'fa-gamepad',
            'sav'   => 'fa-floppy-o',

            // Images
            'bmp'   => 'fa-picture-o',
            'gif'   => 'fa-picture-o',
            'jpg'   => 'fa-picture-o',
            'jpeg'  => 'fa-picture-o',
            'png'   => 'fa-picture-o',
            'psd'   => 'fa-picture-o',
            'tga'   => 'fa-picture-o',
            'tif'   => 'fa-picture-o',

            // Package Files
            'box'   => 'fa-archive',
            'deb'   => 'fa-archive',
            'rpm'   => 'fa-archive',

            // Scripts
            'bat'   => 'fa-terminal',
            'cmd'   => 'fa-terminal',
            'sh'    => 'fa-terminal',

            // Text
            'cfg'   => 'fa-file-text',
            'ini'   => 'fa-file-text',
            'log'   => 'fa-file-text',
            'md'    => 'fa-file-text',
            'rtf'   => 'fa-file-text',
            'txt'   => 'fa-file-text',

            // Vector Images
            'ai'    => 'fa-picture-o',
            'drw'   => 'fa-picture-o',
            'eps'   => 'fa-picture-o',
            'ps'    => 'fa-picture-o',
            'svg'   => 'fa-picture-o',

            // Video
            'avi'   => 'fa-youtube-play',
            'flv'   => 'fa-youtube-play',
            'mkv'   => 'fa-youtube-play',
            'mov'   => 'fa-youtube-play',
            'mp4'   => 'fa-youtube-play',
            'mpg'   => 'fa-youtube-play',
            'ogv'   => 'fa-youtube-play',
            'webm'  => 'fa-youtube-play',
            'wmv'   => 'fa-youtube-play',
            'swf'   => 'fa-youtube-play',

            // Other
            'bak'   => 'fa-floppy',
            'msg'   => 'fa-envelope',

            // Blank
            'blank' => 'fa-file'

        );
    }

    static function getBaseLabelSets($language = 'en') {
        $sOldLanguage = App()->language;
        Yii::app()->setLanguage($language);

        $returnArray = [
            'likert-5-point' => [
                '1' => gT("Strongly disagree"),
                '2' => gT('Disagree'),
                '3' => gT('Neither agree nor disagree'),
                '4' => gT('Agree'),
                '5' => gT('Strongly agree'),
            ],
            'likert-4-point' => [
                '1' => gT('Strongly disagree'),
                '2' => gT('Disagree'),
                '3' => gT('Agree'),
                '4' => gT('Strongly agree'),
            ],
        ];

        Yii::app()->setLanguage($sOldLanguage);

        return $returnArray;
    }

    public static function getBaseQuestionThemeEntries()
    {
        $aBaseQuestionThemes = [
            array(
                "name" => "5pointchoice",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/5pointchoice",
                "image_path" => "/assets/images/screenshots/5.png",
                "title" => "5 Point Choice",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "5 point choice question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "5",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Single choice questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"0\",\"assessable\":\"0\",\"class\":\"choice-5-pt-radio\"}",
            ),
            array(
                "name" => "arrays/10point",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/arrays/10point",
                "image_path" => "/assets/images/screenshots/B.png",
                "title" => "Array (10 Point Choice)",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Array (10 point choice) question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "B",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Arrays",
                "settings" => "{\"subquestions\":\"1\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"0\",\"assessable\":\"1\",\"class\":\"array-10-pt\"}",
            ),
            array(
                "name" => "arrays/5point",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/arrays/5point",
                "image_path" => "/assets/images/screenshots/A.png",
                "title" => "Array (5 Point Choice)",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Array (5 point choice) question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "A",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Arrays",
                "settings" => "{\"subquestions\":\"1\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"0\",\"assessable\":\"1\",\"class\":\"array-5-pt\"}",
            ),
            array(
                "name" => "arrays/array",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/arrays/array",
                "image_path" => "/assets/images/screenshots/F.png",
                "title" => "Array",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Array question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "F",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Arrays",
                "settings" => "{\"subquestions\":\"1\",\"answerscales\":\"1\",\"hasdefaultvalues\":\"0\",\"assessable\":\"1\",\"class\":\"array-flexible-row\"}",
            ),
            array(
                "name" => "arrays/column",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/arrays/column",
                "image_path" => "/assets/images/screenshots/H.png",
                "title" => "Array by column",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Array by column question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "H",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Arrays",
                "settings" => "{\"subquestions\":\"1\",\"answerscales\":\"1\",\"hasdefaultvalues\":\"0\",\"assessable\":\"1\",\"class\":\"array-flexible-column\"}",
            ),
            array(
                "name" => "arrays/dualscale",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/arrays/dualscale",
                "image_path" => "/assets/images/screenshots/1.png",
                "title" => "Array dual scale",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Array dual scale question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "1",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Arrays",
                "settings" => "{\"subquestions\":\"1\",\"answerscales\":\"2\",\"hasdefaultvalues\":\"0\",\"assessable\":\"1\",\"class\":\"array-flexible-duel-scale\"}",
            ),
            array(
                "name" => "arrays/increasesamedecrease",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/arrays/increasesamedecrease",
                "image_path" => "/assets/images/screenshots/E.png",
                "title" => "Array (Increase/Same/Decrease)",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Array (Increase/Same/Decrease) question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "E",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Arrays",
                "settings" => "{\"subquestions\":\"1\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"0\",\"assessable\":\"1\",\"class\":\"array-increase-same-decrease\"}",
            ),
            array(
                "name" => "arrays/multiflexi",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/arrays/multiflexi",
                "image_path" => "/assets/images/screenshots/COLON.png",
                "title" => "Array (Numbers)",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Array (Numbers) question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => ":",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Arrays",
                "settings" => "{\"subquestions\":\"2\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"0\",\"assessable\":\"1\",\"class\":\"array-multi-flexi\"}",
            ),
            array(
                "name" => "arrays/texts",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/arrays/texts",
                "image_path" => "/assets/images/screenshots/;.png",
                "title" => "Array (Texts)",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Array (Texts) question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => ";",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Arrays",
                "settings" => "{\"subquestions\":\"2\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"0\",\"assessable\":\"0\",\"class\":\"array-multi-flexi-text\"}",
            ),
            array(
                "name" => "arrays/yesnouncertain",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/arrays/yesnouncertain",
                "image_path" => "/assets/images/screenshots/C.png",
                "title" => "Array (Yes/No/Uncertain)",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Array (Yes/No/Uncertain) question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "C",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Arrays",
                "settings" => "{\"subquestions\":\"1\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"0\",\"assessable\":\"1\",\"class\":\"array-yes-uncertain-no\"}",
            ),
            array(
                "name" => "boilerplate",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/boilerplate",
                "image_path" => "/assets/images/screenshots/X.png",
                "title" => "Text display",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Text display question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "X",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Mask questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"0\",\"assessable\":\"0\",\"class\":\"boilerplate\"}",
            ),
            array(
                "name" => "date",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/date",
                "image_path" => "/assets/images/screenshots/D.png",
                "title" => "Date/Time",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Date/Time question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "D",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Mask questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"1\",\"assessable\":\"0\",\"class\":\"date\"}",
            ),
            array(
                "name" => "equation",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/equation",
                "image_path" => "/assets/images/screenshots/EQUATION.png",
                "title" => "Equation",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Equation question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "*",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Mask questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"0\",\"assessable\":\"0\",\"class\":\"equation\"}",
            ),
            array(
                "name" => "file_upload",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/file_upload",
                "image_path" => "/assets/images/screenshots/PIPE.png",
                "title" => "File upload",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "File upload question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "|",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Mask questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"0\",\"assessable\":\"0\",\"class\":\"upload-files\"}",
            ),
            array(
                "name" => "gender",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/gender",
                "image_path" => "/assets/images/screenshots/G.png",
                "title" => "Gender",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Gender question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "G",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Mask questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"0\",\"assessable\":\"0\",\"class\":\"gender\"}",
            ),
            array(
                "name" => "hugefreetext",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/hugefreetext",
                "image_path" => "/assets/images/screenshots/U.png",
                "title" => "Huge Free Text",
                "creation_date" => "1970-01-01 01:00:00",
                "author" => "Patrick Teichmann",
                "author_email" => "patrick.teichmann@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Huge free text question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "U",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Text questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"1\",\"assessable\":\"0\",\"class\":\"text-huge\"}",
            ),
            array(
                "name" => "language",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/language",
                "image_path" => "/assets/images/screenshots/I.png",
                "title" => "Language switch",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Language switch question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "I",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Mask questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"0\",\"assessable\":\"0\",\"class\":\"language\"}",
            ),
            array(
                "name" => "listradio",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/listradio",
                "image_path" => "/assets/images/screenshots/L.png",
                "title" => "List (Radio)",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "List (radio) question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "L",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Single choice questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"1\",\"hasdefaultvalues\":\"1\",\"assessable\":\"1\",\"class\":\"list-radio\"}",
            ),
            array(
                "name" => "list_dropdown",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/list_dropdown",
                "image_path" => "/assets/images/screenshots/!.png",
                "title" => "List (Dropdown)",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "List (dropdown) question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "!",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Single choice questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"1\",\"hasdefaultvalues\":\"1\",\"assessable\":\"1\",\"class\":\"list-dropdown\"}",
            ),
            array(
                "name" => "list_with_comment",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/list_with_comment",
                "image_path" => "/assets/images/screenshots/O.png",
                "title" => "List with comment",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "List with comment question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "O",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Single choice questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"1\",\"hasdefaultvalues\":\"1\",\"assessable\":\"1\",\"class\":\"list-with-comment\"}",
            ),
            array(
                "name" => "longfreetext",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/longfreetext",
                "image_path" => "/assets/images/screenshots/T.png",
                "title" => "Long free text",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Long free text question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "T",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Text questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"1\",\"assessable\":\"0\",\"class\":\"text-long\"}",
            ),
            array(
                "name" => "multiplechoice",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/multiplechoice",
                "image_path" => "/assets/images/screenshots/M.png",
                "title" => "Multiple choice",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Multiple choice question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "M",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Multiple choice questions",
                "settings" => "{\"subquestions\":\"1\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"1\",\"assessable\":\"1\",\"class\":\"multiple-opt\"}",
            ),
            array(
                "name" => "multiplechoice_with_comments",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/multiplechoice_with_comments",
                "image_path" => "/assets/images/screenshots/P.png",
                "title" => "Multiple choice with comments",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Multiple choice with comments question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "P",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Multiple choice questions",
                "settings" => "{\"subquestions\":\"1\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"1\",\"assessable\":\"1\",\"class\":\"multiple-opt-comments\"}",
            ),
            array(
                "name" => "multiplenumeric",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/multiplenumeric",
                "image_path" => "/assets/images/screenshots/K.png",
                "title" => "Multiple Numerical Input",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Multiple numerical input question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "K",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Mask questions",
                "settings" => "{\"subquestions\":\"1\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"1\",\"assessable\":\"1\",\"class\":\"numeric-multi\"}",
            ),
            array(
                "name" => "multipleshorttext",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/multipleshorttext",
                "image_path" => "/assets/images/screenshots/Q.png",
                "title" => "Multiple Short Text",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Multiple short text question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "Q",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Text questions",
                "settings" => "{\"subquestions\":\"1\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"1\",\"assessable\":\"0\",\"class\":\"multiple-short-txt\"}",
            ),
            array(
                "name" => "numerical",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/numerical",
                "image_path" => "/assets/images/screenshots/N.png",
                "title" => "Numerical Input",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Numerical input question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "N",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Mask questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"1\",\"assessable\":\"0\",\"class\":\"numeric\"}",
            ),
            array(
                "name" => "ranking",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/ranking",
                "image_path" => "/assets/images/screenshots/R.png",
                "title" => "Ranking",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Ranking question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "R",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Mask questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"1\",\"hasdefaultvalues\":\"0\",\"assessable\":\"1\",\"class\":\"ranking\"}",
            ),
            array(
                "name" => "shortfreetext",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/shortfreetext",
                "image_path" => "/assets/images/screenshots/S.png",
                "title" => "Short Free Text",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Short free text question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "S",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Text questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"1\",\"assessable\":\"0\",\"class\":\"text-short\"}",
            ),
            array(
                "name" => "yesno",
                "visible" => "Y",
                "xml_path" => "application/views/survey/questions/answer/yesno",
                "image_path" => "/assets/images/screenshots/Y.png",
                "title" => "Yes/No",
                "creation_date" => "2018-09-08 00:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Yes/No question type configuration",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "Y",
                "core_theme" => 1,
                "extends" => "",
                "group" => "Mask questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"1\",\"assessable\":\"0\",\"class\":\"yes-no\"}",
            ),
            array(
                "name" => "bootstrap_buttons",
                "visible" => "Y",
                "xml_path" => "themes/question/bootstrap_buttons/survey/questions/answer/listradio",
                "image_path" => "/themes/question/bootstrap_buttons/survey/questions/answer/listradio/assets/bootstrap_buttons_listradio.png",
                "title" => "Bootstrap buttons",
                "creation_date" => "1970-01-01 01:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "New implementation of the Bootstrap buttons question theme",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "L",
                "core_theme" => 1,
                "extends" => "L",
                "group" => "Single choice questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"1\",\"hasdefaultvalues\":\"1\",\"assessable\":\"1\",\"class\":\"list-radio\"}",
            ),
            array(
                "name" => "bootstrap_buttons",
                "visible" => "Y",
                "xml_path" => "themes/question/bootstrap_buttons/survey/questions/answer/multiplechoice",
                "image_path" => "/themes/question/bootstrap_buttons/survey/questions/answer/multiplechoice/assets/bootstrap_buttons_multiplechoice.png",
                "title" => "Bootstrap buttons",
                "creation_date" => "1970-01-01 01:00:00",
                "author" => "Dominik Vitt",
                "author_email" => "dominik.vitt@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "New implementation of the Bootstrap buttons question theme",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "M",
                "core_theme" => 1,
                "extends" => "M",
                "group" => "Multiple choice questions",
                "settings" => "{\"subquestions\":\"1\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"1\",\"assessable\":\"1\",\"class\":\"multiple-opt\"}",
            ),
            array(
                "name" => "browserdetect",
                "visible" => "Y",
                "xml_path" => "themes/question/browserdetect/survey/questions/answer/shortfreetext",
                "image_path" => "/assets/images/screenshots/S.png",
                "title" => "Browser detect",
                "creation_date" => "2017-07-09 00:00:00",
                "author" => "Markus Flür",
                "author_email" => "mfluer@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2017 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Browser, Platform and Proxy detection",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "S",
                "core_theme" => 1,
                "extends" => "S",
                "group" => "Text questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"1\",\"assessable\":\"0\",\"class\":\"text-short\"}",
            ),
            array(
                "name" => "image_select-listradio",
                "visible" => "Y",
                "xml_path" => "themes/question/image_select/survey/questions/answer/listradio",
                "image_path" => "/assets/images/screenshots/L.png",
                "title" => "Image Select List (radio)",
                "creation_date" => "1970-01-01 01:00:00",
                "author" => "Markus Flür",
                "author_email" => "mfluer@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2016 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "List Radio with images.",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "L",
                "core_theme" => 1,
                "extends" => "L",
                "group" => "Single choice questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"1\",\"hasdefaultvalues\":\"1\",\"assessable\":\"1\",\"class\":\"list-radio\"}",
            ),
            array(
                "name" => "image_select-multiplechoice",
                "visible" => "Y",
                "xml_path" => "themes/question/image_select/survey/questions/answer/multiplechoice",
                "image_path" => "/assets/images/screenshots/M.png",
                "title" => "Image Select Multiple choice",
                "creation_date" => "1970-01-01 01:00:00",
                "author" => "Markus Flür",
                "author_email" => "mfluer@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2016 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Multiplechoice with images.",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "M",
                "core_theme" => 1,
                "extends" => "M",
                "group" => "Multiple choice questions",
                "settings" => "{\"subquestions\":\"1\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"1\",\"assessable\":\"1\",\"class\":\"multiple-opt\"}",
            ),
            array(
                "name" => "inputondemand",
                "visible" => "Y",
                "xml_path" => "themes/question/inputondemand/survey/questions/answer/multipleshorttext",
                "image_path" => "/assets/images/screenshots/Q.png",
                "title" => "Input on demand",
                "creation_date" => "2019-10-04 00:00:00",
                "author" => "Markus Flür",
                "author_email" => "mfluer@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2019 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "Hide not needed input fields in multiple shorttext",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "Q",
                "core_theme" => 1,
                "extends" => "Q",
                "group" => "Text questions",
                "settings" => "{\"subquestions\":\"1\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"1\",\"assessable\":\"0\",\"class\":\"multiple-short-txt\"}",
            ),
            array(
                "name" => "ranking_advanced",
                "visible" => "Y",
                "xml_path" => "themes/question/ranking_advanced/survey/questions/answer/ranking",
                "image_path" => "/assets/images/screenshots/R.png",
                "title" => "Ranking Advanced",
                "creation_date" => "1970-01-01 01:00:00",
                "author" => "Markus Flür",
                "author_email" => "mfluer@limesurvey.org",
                "author_url" => "http://www.limesurvey.org",
                "copyright" => "Copyright (C) 2005 - 2017 LimeSurvey Gmbh, Inc. All rights reserved.",
                "license" => "GNU General Public License version 2 or later",
                "version" => "1.0",
                "api_version" => "1",
                "description" => "New implementation of the ranking question",
                "last_update" => "2019-09-23 15:05:59",
                "owner_id" => 1,
                "theme_type" => "question_theme",
                "question_type" => "R",
                "core_theme" => 1,
                "extends" => "R",
                "group" => "Mask questions",
                "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"1\",\"hasdefaultvalues\":\"0\",\"assessable\":\"1\",\"class\":\"ranking\"}",
            ),
        ];

        return $aBaseQuestionThemes;
    }
    
    /** 
     * All translations that are used in files that can or should not be searched by the translation script.
     * This function has no functionality except for being searchable by the translation script.
     */
    public static function mockTranslateArrayContainer()
    {
        $translationArray = [
            gT("Survey container"),
            gT("Hide privacy info"),
            gT("Show popups"),
            gT("Popup"),
            gT("On page"),
            gT("Fix automatically numeric value"),
            gT("For expression"),
            gT("Brandlogo"),
            gT("Brandlogo file"),
            gT('Preview image'),
            gT("Bootstrap theme"),
            gT("Bootswatch theme"),
            gT("Question borders"),
            gT("Question shadow"),
            gT("Zebra-striped questions"),
            gT("Sticky array headers"),
            gT("Dim answered array rows"),
            gT("Hide privacy info"),
            gT("Cross-hover in matrix questions"),
            gT("Background color"),
            gT("Font color"),
            gT("Question background color"),
            gT("Check icon"),
            gT("Background image"),
            gT("Background image file"),
            gT("Logo"),
            gT("Logo file"),
            gT("Animate body"),
            gT("Body animation"),
            gT("Duration"),
            gT("Animate question"),
            gT("Question animation"),
            gT("Animate alert"),
            gT("Alert animation"),
            gT("Animate checkbox"),
            gT("Alert animation"),
            gT("Animate radio buttons"),
            gT("Radio button animation"),
            gT("Select font:"),
            gT("Select variation:"),
            gT("Fruity fonts"),
            gT("Fruity variations")

        ];
    }
}
