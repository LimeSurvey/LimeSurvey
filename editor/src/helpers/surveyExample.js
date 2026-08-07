export const surveyExample = {
  sid: 596477,
  gsid: 1,
  active: false,
  language: 'en',
  expires: null,
  startDate: null,
  anonymized: false,
  saveTimings: null,
  additionalLanguages: '',
  datestamp: null,
  useCookie: null,
  allowRegister: null,
  allowSave: null,
  autoNumberStart: 0,
  autoRedirect: null,
  allowPrev: null,
  hasSurveyUpdatePermission: true,
  printAnswers: null,
  ipAddr: null,
  ipAnonymize: null,
  refUrl: null,
  dateCreated: '2023-10-04T10:27:05.000Z',
  publicStatistics: null,
  publicGraphs: null,
  listPublic: null,
  sendConfirmation: null,
  tokenAnswersPersistence: null,
  assessments: null,
  useCaptcha: null,
  useTokens: false,
  bounceEmail: 'inherit',
  attributeDescriptions: null,
  emailResponseTo: 'inherit',
  emailNotificationTo: 'inherit',
  tokenLength: -1,
  showXQuestions: null,
  showGroupInfo: 'I',
  showNoAnswer: null,
  showQNumCode: 'I',
  bounceTime: 0,
  bounceProcessing: false,
  bounceAccountType: null,
  bounceAccountHost: null,
  bounceAccountPass: null,
  bounceAccountEncryption: null,
  bounceAccountUser: null,
  showWelcome: true,
  showProgress: null,
  questionIndex: -1,
  navigationDelay: -1,
  noKeyboard: null,
  allowedItAfterCompletion: null,
  googleAnalyticsStyle: 0,
  googleAnalyticsApiKey: null,
  showSurveyPolicyNotice: 0,
  languageSettings: {
    en: {
      sid: 596477,
      language: 'en',
      title: 'Example Survey',
      description: '',
      welcomeText: '',
      endText: '',
      policyNotice: '',
      policyError: '',
      policyNoticeLabel: '',
      url: '',
      urlDescription: '',
      dateFormat: 9,
      numberFormat: 0,
    },
    fr: {
      sid: 596477,
      language: 'fr',
      title: 'Example Survey',
      description: '',
      welcomeText: '',
      endText: '',
      policyNotice: '',
      policyError: '',
      policyNoticeLabel: '',
      url: '',
      urlDescription: '',
      dateFormat: 9,
      numberFormat: 0,
    },
  },
  languages: ['en', 'fr'],
  groupsList: {
    1: 'Default',
  },
  surveyGroup: {
    gsid: 1,
    name: 'default',
    title: 'Default',
    description: 'Default group survey',
    sortOrder: 0,
    alwaysAvailable: true,
  },
  owner: {
    uid: 1,
    name: 'admin',
    fullName: 'Administrator',
    parentId: 0,
    lang: 'en',
    email: 'your-email@example.net',
    htmlEditorMode: 'default',
    templateEditorMode: 'default',
    questionSelectorMode: 'default',
    dateFormat: 1,
    lastLogin: '2024-10-22 07:40:10',
    created: '2024-04-25 12:04:14',
    modified: '2024-10-22 07:40:10',
    userStatus: 1,
  },
  surveyMenus: {
    settings: {
      id: '1',
      name: 'settings',
      ordering: '1',
      level: '0',
      title: 'Survey settings',
      description: 'Survey settings',
      entries: {
        overview: {
          id: '1',
          menuId: '1',
          userId: null,
          ordering: '1',
          name: 'overview',
          title: 'Survey overview',
          menuTitle: 'Overview',
          menuDescription: 'Open the general survey overview',
          menuIcon: 'ri-bar-chart-horizontal-line',
          menuClass: '',
          menuLink: 'surveyAdministration/view',
          action: '',
          template: '',
          partial: '',
          classes: '',
          permission: '',
          permissionGrade: '',
          data: '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}',
          getDataMethod: '',
          language: 'en-GB',
          showInCollapse: '0',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        generalsettings: {
          id: '2',
          menuId: '1',
          userId: null,
          ordering: '2',
          name: 'generalsettings',
          title: 'General survey settings',
          menuTitle: 'General settings',
          menuDescription: 'Open general survey settings',
          menuIcon: 'ri-tools-line',
          menuClass: '',
          menuLink: '',
          action: 'updatesurveylocalesettings_generalsettings',
          template: 'editLocalSettings_main_view',
          partial: '/admin/survey/subview/accordion/_generaloptions_panel',
          classes: '',
          permission: 'surveysettings',
          permissionGrade: 'read',
          data: null,
          getDataMethod: 'generalTabEditSurvey',
          language: 'en-GB',
          showInCollapse: '1',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        surveytexts: {
          id: '3',
          menuId: '1',
          userId: null,
          ordering: '3',
          name: 'surveytexts',
          title: 'Survey text elements',
          menuTitle: 'Text elements',
          menuDescription: 'Survey text elements',
          menuIcon: 'ri-text-spacing',
          menuClass: '',
          menuLink: '',
          action: 'updatesurveylocalesettings',
          template: 'editLocalSettings_main_view',
          partial: '/admin/survey/subview/tab_edit_view',
          classes: '',
          permission: 'surveylocale',
          permissionGrade: 'read',
          data: null,
          getDataMethod: 'getTextEditData',
          language: 'en-GB',
          showInCollapse: '1',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        datasecurity: {
          id: '4',
          menuId: '1',
          userId: null,
          ordering: '4',
          name: 'datasecurity',
          title: 'Privacy policy settings',
          menuTitle: 'Privacy policy',
          menuDescription: 'Edit privacy policy settings',
          menuIcon: 'ri-shield-line',
          menuClass: '',
          menuLink: '',
          action: 'updatesurveylocalesettings',
          template: 'editLocalSettings_main_view',
          partial: '/admin/survey/subview/tab_edit_view_datasecurity',
          classes: '',
          permission: 'surveylocale',
          permissionGrade: 'read',
          data: null,
          getDataMethod: 'getDataSecurityEditData',
          language: 'en-GB',
          showInCollapse: '1',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        theme_options: {
          id: '5',
          menuId: '1',
          userId: null,
          ordering: '5',
          name: 'theme_options',
          title: 'Theme options',
          menuTitle: 'Theme options',
          menuDescription: 'Edit theme options for this survey',
          menuIcon: 'ri-contrast-drop-fill',
          menuClass: '',
          menuLink: 'ThemeOptions/updateSurvey',
          action: '',
          template: '',
          partial: '',
          classes: '',
          permission: 'surveysettings',
          permissionGrade: 'update',
          data: '{"render": {"link": { "pjaxed": true, "data": {"surveyid": ["survey","sid"], "gsid":["survey","gsid"]}}}}',
          getDataMethod: '',
          language: 'en-GB',
          showInCollapse: '0',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        presentation: {
          id: '6',
          menuId: '1',
          userId: null,
          ordering: '6',
          name: 'presentation',
          title: 'Presentation &amp; navigation settings',
          menuTitle: 'Presentation',
          menuDescription: 'Edit presentation and navigation settings',
          menuIcon: 'ri-slideshow-line',
          menuClass: '',
          menuLink: '',
          action: 'updatesurveylocalesettings',
          template: 'editLocalSettings_main_view',
          partial: '/admin/survey/subview/accordion/_presentation_panel',
          classes: '',
          permission: 'surveylocale',
          permissionGrade: 'read',
          data: null,
          getDataMethod: 'tabPresentationNavigation',
          language: 'en-GB',
          showInCollapse: '0',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        tokens: {
          id: '7',
          menuId: '1',
          userId: null,
          ordering: '7',
          name: 'tokens',
          title: 'Survey participant settings',
          menuTitle: 'Participant settings',
          menuDescription: 'Set additional options for survey participants',
          menuIcon: 'ri-body-scan-fill',
          menuClass: '',
          menuLink: '',
          action: 'updatesurveylocalesettings',
          template: 'editLocalSettings_main_view',
          partial: '/admin/survey/subview/accordion/_tokens_panel',
          classes: '',
          permission: 'surveylocale',
          permissionGrade: 'read',
          data: null,
          getDataMethod: 'tabTokens',
          language: 'en-GB',
          showInCollapse: '0',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        notification: {
          id: '8',
          menuId: '1',
          userId: null,
          ordering: '8',
          name: 'notification',
          title: 'Notification and data management settings',
          menuTitle: 'Notifications &amp; data',
          menuDescription: 'Edit settings for notification and data management',
          menuIcon: 'ri-notification-line',
          menuClass: '',
          menuLink: '',
          action: 'updatesurveylocalesettings',
          template: 'editLocalSettings_main_view',
          partial: '/admin/survey/subview/accordion/_notification_panel',
          classes: '',
          permission: 'surveylocale',
          permissionGrade: 'read',
          data: null,
          getDataMethod: 'tabNotificationDataManagement',
          language: 'en-GB',
          showInCollapse: '0',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        publication: {
          id: '9',
          menuId: '1',
          userId: null,
          ordering: '9',
          name: 'publication',
          title: 'Publication &amp; access control settings',
          menuTitle: 'Publication &amp; access',
          menuDescription: 'Edit settings for publication and access control',
          menuIcon: 'ri-key-line',
          menuClass: '',
          menuLink: '',
          action: 'updatesurveylocalesettings',
          template: 'editLocalSettings_main_view',
          partial: '/admin/survey/subview/accordion/_publication_panel',
          classes: '',
          permission: 'surveylocale',
          permissionGrade: 'read',
          data: null,
          getDataMethod: 'tabPublicationAccess',
          language: 'en-GB',
          showInCollapse: '0',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        surveypermissions: {
          id: '10',
          menuId: '1',
          userId: null,
          ordering: '10',
          name: 'surveypermissions',
          title: 'Edit survey permissions',
          menuTitle: 'Survey permissions',
          menuDescription: 'Edit permissions for this survey',
          menuIcon: 'ri-lock-password-line',
          menuClass: '',
          menuLink: 'surveyPermissions/index',
          action: '',
          template: '',
          partial: '',
          classes: '',
          permission: 'surveysecurity',
          permissionGrade: 'read',
          data: '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}',
          getDataMethod: '',
          language: 'en-GB',
          showInCollapse: '0',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
      },
    },
    mainmenu: {
      id: '2',
      name: 'mainmenu',
      ordering: '2',
      level: '0',
      title: 'Survey menu',
      description: 'Main survey menu',
      entries: {
        listQuestions: {
          id: '11',
          menuId: '2',
          userId: null,
          ordering: '1',
          name: 'listQuestions',
          title: 'Overview questions &amp; groups',
          menuTitle: 'Overview questions &amp; groups',
          menuDescription:
            'Overview of questions and groups where you can add, edit and reorder them',
          menuIcon: '',
          menuClass: '',
          menuLink: 'questionAdministration/listQuestions',
          action: '',
          template: '',
          partial: '',
          classes: '',
          permission: 'surveycontent',
          permissionGrade: 'read',
          data: '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}',
          getDataMethod: '',
          language: 'en-GB',
          showInCollapse: '1',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        participants: {
          id: '12',
          menuId: '2',
          userId: null,
          ordering: '4',
          name: 'participants',
          title: 'Survey participants',
          menuTitle: 'Survey participants',
          menuDescription: 'Go to survey participant and token settings',
          menuIcon: '',
          menuClass: '',
          menuLink: 'admin/tokens/sa/index/',
          action: '',
          template: '',
          partial: '',
          classes: '',
          permission: 'tokens',
          permissionGrade: 'read',
          data: '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}',
          getDataMethod: '',
          language: 'en-GB',
          showInCollapse: '1',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        emailtemplates: {
          id: '13',
          menuId: '2',
          userId: null,
          ordering: '5',
          name: 'emailtemplates',
          title: 'Email templates',
          menuTitle: 'Email templates',
          menuDescription:
            'Edit the templates for invitation, reminder and registration emails',
          menuIcon: '',
          menuClass: '',
          menuLink: 'admin/emailtemplates/sa/index/',
          action: '',
          template: '',
          partial: '',
          classes: '',
          permission: 'surveylocale',
          permissionGrade: 'read',
          data: '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}',
          getDataMethod: '',
          language: 'en-GB',
          showInCollapse: '0',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        failedemail: {
          id: '14',
          menuId: '2',
          userId: null,
          ordering: '6',
          name: 'failedemail',
          title: 'Failed email notifications',
          menuTitle: 'Failed email notifications',
          menuDescription: 'View and resend failed email notifications',
          menuIcon: '',
          menuClass: '',
          menuLink: 'failedEmail/index/',
          action: '',
          template: '',
          partial: '',
          classes: '',
          permission: 'surveylocale',
          permissionGrade: 'read',
          data: '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}',
          getDataMethod: '',
          language: 'en-GB',
          showInCollapse: '0',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        quotas: {
          id: '15',
          menuId: '2',
          userId: null,
          ordering: '7',
          name: 'quotas',
          title: 'Edit quotas',
          menuTitle: 'Quotas',
          menuDescription: 'Edit quotas for this survey.',
          menuIcon: '',
          menuClass: '',
          menuLink: 'quotas/index/',
          action: '',
          template: '',
          partial: '',
          classes: '',
          permission: 'quotas',
          permissionGrade: 'read',
          data: '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}',
          getDataMethod: '',
          language: 'en-GB',
          showInCollapse: '0',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        assessments: {
          id: '16',
          menuId: '2',
          userId: null,
          ordering: '8',
          name: 'assessments',
          title: 'Edit assessments',
          menuTitle: 'Assessments',
          menuDescription: 'Edit and look at the assessements for this survey.',
          menuIcon: '',
          menuClass: '',
          menuLink: 'assessment/index',
          action: '',
          template: '',
          partial: '',
          classes: '',
          permission: 'assessments',
          permissionGrade: 'read',
          data: '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}',
          getDataMethod: '',
          language: 'en-GB',
          showInCollapse: '0',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        panelintegration: {
          id: '17',
          menuId: '2',
          userId: null,
          ordering: '9',
          name: 'panelintegration',
          title: 'Edit survey panel integration',
          menuTitle: 'Panel integration',
          menuDescription: 'Define panel integrations for your survey',
          menuIcon: '',
          menuClass: '',
          menuLink: '',
          action: 'updatesurveylocalesettings',
          template: 'editLocalSettings_main_view',
          partial: '/admin/survey/subview/accordion/_integration_panel',
          classes: '',
          permission: 'surveylocale',
          permissionGrade: 'read',
          data: '{"render": {"link": { "pjaxed": false}}}',
          getDataMethod: 'tabPanelIntegration',
          language: 'en-GB',
          showInCollapse: '0',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        responses: {
          id: '18',
          menuId: '2',
          userId: null,
          ordering: '10',
          name: 'responses',
          title: 'Responses',
          menuTitle: 'Responses',
          menuDescription: 'Responses',
          menuIcon: '',
          menuClass: '',
          menuLink: 'responses/browse/',
          action: '',
          template: '',
          partial: '',
          classes: '',
          permission: 'responses',
          permissionGrade: 'read',
          data: '{"render": {"isActive": true, "link": {"data": {"surveyId": ["survey", "sid"]}}}}',
          getDataMethod: '',
          language: 'en-GB',
          showInCollapse: '1',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        statistics: {
          id: '19',
          menuId: '2',
          userId: null,
          ordering: '11',
          name: 'statistics',
          title: 'Statistics',
          menuTitle: 'Statistics',
          menuDescription: 'Statistics',
          menuIcon: '',
          menuClass: '',
          menuLink: 'admin/statistics/sa/index/',
          action: '',
          template: '',
          partial: '',
          classes: '',
          permission: 'statistics',
          permissionGrade: 'read',
          data: '{"render": {"isActive": true, "link": {"data": {"surveyid": ["survey", "sid"]}}}}',
          getDataMethod: '',
          language: 'en-GB',
          showInCollapse: '1',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        resources: {
          id: '20',
          menuId: '2',
          userId: null,
          ordering: '12',
          name: 'resources',
          title: 'Add/edit resources (files/images) for this survey',
          menuTitle: 'Resources',
          menuDescription: 'Add/edit resources (files/images) for this survey',
          menuIcon: '',
          menuClass: '',
          menuLink: '',
          action: 'updatesurveylocalesettings',
          template: 'editLocalSettings_main_view',
          partial: '/admin/survey/subview/accordion/_resources_panel',
          classes: '',
          permission: 'surveylocale',
          permissionGrade: 'read',
          data: '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}',
          getDataMethod: 'tabResourceManagement',
          language: 'en-GB',
          showInCollapse: '0',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        plugins: {
          id: '21',
          menuId: '2',
          userId: null,
          ordering: '13',
          name: 'plugins',
          title: 'Simple plugin settings',
          menuTitle: 'Simple plugins',
          menuDescription: 'Edit simple plugin settings',
          menuIcon: '',
          menuClass: '',
          menuLink: '',
          action: 'updatesurveylocalesettings',
          template: 'editLocalSettings_main_view',
          partial: '/admin/survey/subview/accordion/_plugins_panel',
          classes: '',
          permission: 'surveysettings',
          permissionGrade: 'read',
          data: '{"render": {"link": {"data": {"surveyid": ["survey","sid"]}}}}',
          getDataMethod: 'pluginTabSurvey',
          language: 'en-GB',
          showInCollapse: '0',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
      },
    },
    quickmenu: {
      id: '3',
      name: 'quickmenu',
      ordering: '3',
      level: '0',
      title: 'Quick menu',
      description: 'Quick menu',
      entries: {
        activateSurvey: {
          id: '22',
          menuId: '3',
          userId: null,
          ordering: '1',
          name: 'activateSurvey',
          title: 'Activate survey',
          menuTitle: 'Activate survey',
          menuDescription: 'Activate survey',
          menuIcon: 'ri-play-fill',
          menuClass: '',
          menuLink: 'surveyAdministration/activate',
          action: '',
          template: '',
          partial: '',
          classes: '',
          permission: 'surveyactivation',
          permissionGrade: 'update',
          data: '{"render": {"isActive": false, "link": {"data": {"iSurveyID": ["survey","sid"]}}}}',
          getDataMethod: '',
          language: 'en-GB',
          showInCollapse: '1',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        deactivateSurvey: {
          id: '23',
          menuId: '3',
          userId: null,
          ordering: '2',
          name: 'deactivateSurvey',
          title: 'Stop survey',
          menuTitle: 'Stop survey',
          menuDescription: 'Stop this survey',
          menuIcon: 'ri-stop-fill',
          menuClass: '',
          menuLink: 'surveyAdministration/deactivate',
          action: '',
          template: '',
          partial: '',
          classes: '',
          permission: 'surveyactivation',
          permissionGrade: 'update',
          data: '{"render": {"isActive": true, "link": {"data": {"surveyid": ["survey","sid"]}}}}',
          getDataMethod: '',
          language: 'en-GB',
          showInCollapse: '1',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        testSurvey: {
          id: '24',
          menuId: '3',
          userId: null,
          ordering: '3',
          name: 'testSurvey',
          title: 'Go to survey',
          menuTitle: 'Go to survey',
          menuDescription: 'Go to survey',
          menuIcon: 'ri-settings-5-fill',
          menuClass: '',
          menuLink: 'survey/index/',
          action: '',
          template: '',
          partial: '',
          classes: '',
          permission: '',
          permissionGrade: '',
          data: '{"render": {"link": {"external": true, "data": {"sid": ["survey","sid"], "newtest": "Y", "lang": ["survey","language"]}}}}',
          getDataMethod: '',
          language: 'en-GB',
          showInCollapse: '1',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        surveyLogicFile: {
          id: '25',
          menuId: '3',
          userId: null,
          ordering: '4',
          name: 'surveyLogicFile',
          title: 'Survey logic file',
          menuTitle: 'Survey logic file',
          menuDescription: 'Survey logic file',
          menuIcon: 'ri-git-branch-fill',
          menuClass: '',
          menuLink: 'admin/expressions/sa/survey_logic_file/',
          action: '',
          template: '',
          partial: '',
          classes: '',
          permission: 'surveycontent',
          permissionGrade: 'read',
          data: '{"render": { "link": {"data": {"sid": ["survey","sid"]}}}}',
          getDataMethod: '',
          language: 'en-GB',
          showInCollapse: '1',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
        cpdb: {
          id: '26',
          menuId: '3',
          userId: null,
          ordering: '5',
          name: 'cpdb',
          title: 'Central participant database',
          menuTitle: 'Central participant database',
          menuDescription: 'Central participant database',
          menuIcon: 'ri-group-fill',
          menuClass: '',
          menuLink: 'admin/participants/sa/displayParticipants',
          action: '',
          template: '',
          partial: '',
          classes: '',
          permission: 'tokens',
          permissionGrade: 'read',
          data: '{"render": {"link": {}}}',
          getDataMethod: '',
          language: 'en-GB',
          showInCollapse: '1',
          active: '1',
          changedAt: '2023-12-08 10:19:48',
          changedBy: '0',
          createdAt: '2023-12-08 10:19:48',
          createdBy: '0',
        },
      },
    },
  },
  questionGroups: [
    {
      gid: 4,
      sid: 596477,
      sortOrder: 1,
      randomizationGroup: '',
      gRelevance: '1',
      l10ns: {
        en: {
          id: 4,
          gid: 4,
          groupName: 'Text questions',
          description: '',
          language: 'en',
        },
      },
      questions: [
        {
          qid: 63,
          parentQid: 0,
          sid: 596477,
          type: 'T',
          title: 'Q00',
          preg: '',
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 1,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: 'longfreetext',
          moduleName: null,
          gid: 4,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 63,
              qid: 63,
              question: 'Long free text',
              help: 'This is a question help text.',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            random_group: {
              'qid': '63',
              '': {
                qaid: '220',
                value: '',
              },
            },
            em_validation_q: {
              'qid': '63',
              '': {
                qaid: '221',
                value: '',
              },
            },
            em_validation_q_tip: {
              qid: '63',
              en: {
                qaid: '222',
                value: '',
              },
            },
            hide_tip: {
              'qid': '63',
              '': {
                qaid: '223',
                value: '0',
              },
            },
            text_input_width: {
              'qid': '63',
              '': {
                qaid: '224',
                value: '',
              },
            },
            input_size: {
              'qid': '63',
              '': {
                qaid: '225',
                value: '',
              },
            },
            display_rows: {
              'qid': '63',
              '': {
                qaid: '226',
                value: '',
              },
            },
            hidden: {
              'qid': '63',
              '': {
                qaid: '227',
                value: '0',
              },
            },
            cssclass: {
              'qid': '63',
              '': {
                qaid: '228',
                value: '',
              },
            },
            maximum_chars: {
              'qid': '63',
              '': {
                qaid: '229',
                value: '',
              },
            },
            page_break: {
              'qid': '63',
              '': {
                qaid: '230',
                value: '0',
              },
            },
            time_limit: {
              'qid': '63',
              '': {
                qaid: '231',
                value: '',
              },
            },
            time_limit_action: {
              'qid': '63',
              '': {
                qaid: '232',
                value: '1',
              },
            },
            time_limit_disable_next: {
              'qid': '63',
              '': {
                qaid: '233',
                value: '0',
              },
            },
            time_limit_disable_prev: {
              'qid': '63',
              '': {
                qaid: '234',
                value: '0',
              },
            },
            time_limit_countdown_message: {
              qid: '63',
              en: {
                qaid: '235',
                value: '',
              },
            },
            time_limit_timer_style: {
              'qid': '63',
              '': {
                qaid: '236',
                value: '',
              },
            },
            time_limit_message_delay: {
              'qid': '63',
              '': {
                qaid: '237',
                value: '',
              },
            },
            time_limit_message: {
              qid: '63',
              en: {
                qaid: '238',
                value: '',
              },
            },
            time_limit_message_style: {
              'qid': '63',
              '': {
                qaid: '239',
                value: '',
              },
            },
            time_limit_warning: {
              'qid': '63',
              '': {
                qaid: '240',
                value: '',
              },
            },
            time_limit_warning_display_time: {
              'qid': '63',
              '': {
                qaid: '241',
                value: '',
              },
            },
            time_limit_warning_message: {
              qid: '63',
              en: {
                qaid: '242',
                value: '',
              },
            },
            time_limit_warning_style: {
              'qid': '63',
              '': {
                qaid: '243',
                value: '',
              },
            },
            time_limit_warning_2: {
              'qid': '63',
              '': {
                qaid: '244',
                value: '',
              },
            },
            time_limit_warning_2_display_time: {
              'qid': '63',
              '': {
                qaid: '245',
                value: '',
              },
            },
            time_limit_warning_2_message: {
              qid: '63',
              en: {
                qaid: '246',
                value: '',
              },
            },
            time_limit_warning_2_style: {
              'qid': '63',
              '': {
                qaid: '247',
                value: '',
              },
            },
            statistics_showgraph: {
              'qid': '63',
              '': {
                qaid: '248',
                value: '1',
              },
            },
            statistics_graphtype: {
              'qid': '63',
              '': {
                qaid: '249',
                value: '0',
              },
            },
            save_as_default: {
              'qid': '63',
              '': {
                qaid: '250',
                value: 'N',
              },
            },
          },
          answers: [],
        },
        {
          qid: 64,
          parentQid: 0,
          sid: 596477,
          type: 'S',
          title: 'G01Q02',
          preg: '',
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 2,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: 'shortfreetext',
          moduleName: '',
          gid: 4,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 65,
              qid: 64,
              question: 'Short free text',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            random_group: {
              'qid': '64',
              '': {
                qaid: '251',
                value: '',
              },
            },
            em_validation_q: {
              'qid': '64',
              '': {
                qaid: '252',
                value: '',
              },
            },
            em_validation_q_tip: {
              qid: '64',
              en: {
                qaid: '253',
                value: '',
              },
            },
            prefix: {
              qid: '64',
              en: {
                qaid: '254',
                value: '',
              },
            },
            suffix: {
              qid: '64',
              en: {
                qaid: '255',
                value: '',
              },
            },
            hide_tip: {
              'qid': '64',
              '': {
                qaid: '256',
                value: '0',
              },
            },
            text_input_width: {
              'qid': '64',
              '': {
                qaid: '257',
                value: '',
              },
            },
            input_size: {
              'qid': '64',
              '': {
                qaid: '258',
                value: '',
              },
            },
            display_rows: {
              'qid': '64',
              '': {
                qaid: '259',
                value: '',
              },
            },
            hidden: {
              'qid': '64',
              '': {
                qaid: '260',
                value: '0',
              },
            },
            cssclass: {
              'qid': '64',
              '': {
                qaid: '261',
                value: '',
              },
            },
            maximum_chars: {
              'qid': '64',
              '': {
                qaid: '262',
                value: '',
              },
            },
            page_break: {
              'qid': '64',
              '': {
                qaid: '263',
                value: '0',
              },
            },
            numbers_only: {
              'qid': '64',
              '': {
                qaid: '264',
                value: '0',
              },
            },
            time_limit: {
              'qid': '64',
              '': {
                qaid: '265',
                value: '',
              },
            },
            time_limit_action: {
              'qid': '64',
              '': {
                qaid: '266',
                value: '1',
              },
            },
            time_limit_disable_next: {
              'qid': '64',
              '': {
                qaid: '267',
                value: '0',
              },
            },
            time_limit_disable_prev: {
              'qid': '64',
              '': {
                qaid: '268',
                value: '0',
              },
            },
            time_limit_countdown_message: {
              qid: '64',
              en: {
                qaid: '269',
                value: '',
              },
            },
            time_limit_timer_style: {
              'qid': '64',
              '': {
                qaid: '270',
                value: '',
              },
            },
            time_limit_message_delay: {
              'qid': '64',
              '': {
                qaid: '271',
                value: '',
              },
            },
            time_limit_message: {
              qid: '64',
              en: {
                qaid: '272',
                value: '',
              },
            },
            time_limit_message_style: {
              'qid': '64',
              '': {
                qaid: '273',
                value: '',
              },
            },
            time_limit_warning: {
              'qid': '64',
              '': {
                qaid: '274',
                value: '',
              },
            },
            time_limit_warning_display_time: {
              'qid': '64',
              '': {
                qaid: '275',
                value: '',
              },
            },
            time_limit_warning_message: {
              qid: '64',
              en: {
                qaid: '276',
                value: '',
              },
            },
            time_limit_warning_style: {
              'qid': '64',
              '': {
                qaid: '277',
                value: '',
              },
            },
            time_limit_warning_2: {
              'qid': '64',
              '': {
                qaid: '278',
                value: '',
              },
            },
            time_limit_warning_2_display_time: {
              'qid': '64',
              '': {
                qaid: '279',
                value: '',
              },
            },
            time_limit_warning_2_message: {
              qid: '64',
              en: {
                qaid: '280',
                value: '',
              },
            },
            time_limit_warning_2_style: {
              'qid': '64',
              '': {
                qaid: '281',
                value: '',
              },
            },
            statistics_showmap: {
              'qid': '64',
              '': {
                qaid: '282',
                value: '1',
              },
            },
            statistics_showgraph: {
              'qid': '64',
              '': {
                qaid: '283',
                value: '1',
              },
            },
            statistics_graphtype: {
              'qid': '64',
              '': {
                qaid: '284',
                value: '0',
              },
            },
            location_mapservice: {
              'qid': '64',
              '': {
                qaid: '285',
                value: '0',
              },
            },
            location_nodefaultfromip: {
              'qid': '64',
              '': {
                qaid: '286',
                value: '0',
              },
            },
            location_postal: {
              'qid': '64',
              '': {
                qaid: '287',
                value: '0',
              },
            },
            location_city: {
              'qid': '64',
              '': {
                qaid: '288',
                value: '0',
              },
            },
            location_state: {
              'qid': '64',
              '': {
                qaid: '289',
                value: '0',
              },
            },
            location_country: {
              'qid': '64',
              '': {
                qaid: '290',
                value: '0',
              },
            },
            location_mapzoom: {
              'qid': '64',
              '': {
                qaid: '291',
                value: '11',
              },
            },
            location_defaultcoordinates: {
              'qid': '64',
              '': {
                qaid: '292',
                value: '',
              },
            },
            location_mapwidth: {
              'qid': '64',
              '': {
                qaid: '293',
                value: '500',
              },
            },
            location_mapheight: {
              'qid': '64',
              '': {
                qaid: '294',
                value: '300',
              },
            },
            save_as_default: {
              'qid': '64',
              '': {
                qaid: '295',
                value: 'N',
              },
            },
          },
          answers: [],
        },
        {
          qid: 66,
          parentQid: 0,
          sid: 596477,
          type: 'Q',
          title: 'G01Q04',
          preg: '',
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 4,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: 'multipleshorttext',
          moduleName: '',
          gid: 4,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 67,
              qid: 66,
              question: 'Multiple short text',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            min_answers: {
              'qid': '66',
              '': {
                qaid: '327',
                value: '',
              },
            },
            max_answers: {
              'qid': '66',
              '': {
                qaid: '328',
                value: '',
              },
            },
            array_filter_style: {
              'qid': '66',
              '': {
                qaid: '329',
                value: '0',
              },
            },
            array_filter_exclude: {
              'qid': '66',
              '': {
                qaid: '330',
                value: '',
              },
            },
            array_filter: {
              'qid': '66',
              '': {
                qaid: '331',
                value: '',
              },
            },
            exclude_all_others: {
              'qid': '66',
              '': {
                qaid: '332',
                value: '',
              },
            },
            random_group: {
              'qid': '66',
              '': {
                qaid: '333',
                value: '',
              },
            },
            em_validation_q: {
              'qid': '66',
              '': {
                qaid: '334',
                value: '',
              },
            },
            em_validation_q_tip: {
              qid: '66',
              en: {
                qaid: '335',
                value: '',
              },
            },
            em_validation_sq: {
              'qid': '66',
              '': {
                qaid: '336',
                value: '',
              },
            },
            em_validation_sq_tip: {
              qid: '66',
              en: {
                qaid: '337',
                value: '',
              },
            },
            prefix: {
              qid: '66',
              en: {
                qaid: '338',
                value: '',
              },
            },
            suffix: {
              qid: '66',
              en: {
                qaid: '339',
                value: '',
              },
            },
            text_input_columns: {
              'qid': '66',
              '': {
                qaid: '340',
                value: '',
              },
            },
            label_input_columns: {
              'qid': '66',
              '': {
                qaid: '341',
                value: '',
              },
            },
            hide_tip: {
              'qid': '66',
              '': {
                qaid: '342',
                value: '0',
              },
            },
            display_rows: {
              'qid': '66',
              '': {
                qaid: '343',
                value: '',
              },
            },
            input_size: {
              'qid': '66',
              '': {
                qaid: '344',
                value: '',
              },
            },
            random_order: {
              'qid': '66',
              '': {
                qaid: '345',
                value: '0',
              },
            },
            hidden: {
              'qid': '66',
              '': {
                qaid: '346',
                value: '0',
              },
            },
            cssclass: {
              'qid': '66',
              '': {
                qaid: '347',
                value: '',
              },
            },
            maximum_chars: {
              'qid': '66',
              '': {
                qaid: '348',
                value: '',
              },
            },
            page_break: {
              'qid': '66',
              '': {
                qaid: '349',
                value: '0',
              },
            },
            numbers_only: {
              'qid': '66',
              '': {
                qaid: '350',
                value: '0',
              },
            },
            statistics_showgraph: {
              'qid': '66',
              '': {
                qaid: '351',
                value: '1',
              },
            },
            statistics_graphtype: {
              'qid': '66',
              '': {
                qaid: '352',
                value: '0',
              },
            },
            save_as_default: {
              'qid': '66',
              '': {
                qaid: '353',
                value: 'N',
              },
            },
          },
          subquestions: [
            {
              qid: 67,
              parentQid: 66,
              sid: 596477,
              type: 'T',
              title: 'SQ001',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 0,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 4,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 68,
                  qid: 67,
                  question: 'Example input 1',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 68,
              parentQid: 66,
              sid: 596477,
              type: 'T',
              title: 'SQ003',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 1,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 4,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 69,
                  qid: 68,
                  question: 'Example input 2',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 69,
              parentQid: 66,
              sid: 596477,
              type: 'T',
              title: 'SQ002',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 2,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 4,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 70,
                  qid: 69,
                  question: 'Example input 3',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
          ],
          answers: [],
        },
        {
          qid: 118,
          parentQid: 0,
          sid: 596477,
          type: 'Y',
          title: 'G01Q22',
          preg: null,
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 5,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: 'yesno',
          moduleName: '',
          gid: 4,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 123,
              qid: 118,
              question: 'Yes/No',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            random_group: {
              'qid': '118',
              '': {
                qaid: '803',
                value: '',
              },
            },
            display_type: {
              'qid': '118',
              '': {
                qaid: '804',
                value: '0',
              },
            },
            hide_tip: {
              'qid': '118',
              '': {
                qaid: '805',
                value: '0',
              },
            },
            hidden: {
              'qid': '118',
              '': {
                qaid: '806',
                value: '0',
              },
            },
            cssclass: {
              'qid': '118',
              '': {
                qaid: '807',
                value: '',
              },
            },
            printable_help: {
              qid: '118',
              en: {
                qaid: '808',
                value: '',
              },
            },
            page_break: {
              'qid': '118',
              '': {
                qaid: '809',
                value: '0',
              },
            },
            scale_export: {
              'qid': '118',
              '': {
                qaid: '810',
                value: '0',
              },
            },
            public_statistics: {
              'qid': '118',
              '': {
                qaid: '811',
                value: '0',
              },
            },
            statistics_showgraph: {
              'qid': '118',
              '': {
                qaid: '812',
                value: '1',
              },
            },
            statistics_graphtype: {
              'qid': '118',
              '': {
                qaid: '813',
                value: '0',
              },
            },
            save_as_default: {
              'qid': '118',
              '': {
                qaid: '814',
                value: 'N',
              },
            },
          },
          answers: [],
        },
      ],
    },
    {
      gid: 5,
      sid: 596477,
      sortOrder: 2,
      randomizationGroup: '',
      gRelevance: '',
      l10ns: {
        en: {
          id: 5,
          gid: 5,
          groupName: 'Array questions',
          description: '',
          language: 'en',
        },
      },
      questions: [
        {
          qid: 70,
          parentQid: 0,
          sid: 596477,
          type: 'F',
          title: 'G02Q05',
          preg: null,
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 1,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: 'arrays/array',
          moduleName: '',
          gid: 5,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 72,
              qid: 70,
              question: 'Array',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            min_answers: {
              'qid': '70',
              '': {
                qaid: '354',
                value: '',
              },
            },
            max_answers: {
              'qid': '70',
              '': {
                qaid: '355',
                value: '',
              },
            },
            array_filter_style: {
              'qid': '70',
              '': {
                qaid: '356',
                value: '0',
              },
            },
            array_filter: {
              'qid': '70',
              '': {
                qaid: '357',
                value: '',
              },
            },
            array_filter_exclude: {
              'qid': '70',
              '': {
                qaid: '358',
                value: '',
              },
            },
            exclude_all_others: {
              'qid': '70',
              '': {
                qaid: '359',
                value: '',
              },
            },
            random_group: {
              'qid': '70',
              '': {
                qaid: '360',
                value: '',
              },
            },
            em_validation_q: {
              'qid': '70',
              '': {
                qaid: '361',
                value: '',
              },
            },
            em_validation_q_tip: {
              qid: '70',
              en: {
                qaid: '362',
                value: '',
              },
            },
            answer_width: {
              'qid': '70',
              '': {
                qaid: '363',
                value: '',
              },
            },
            repeat_headings: {
              'qid': '70',
              '': {
                qaid: '364',
                value: '',
              },
            },
            hide_tip: {
              'qid': '70',
              '': {
                qaid: '365',
                value: '0',
              },
            },
            random_order: {
              'qid': '70',
              '': {
                qaid: '366',
                value: '0',
              },
            },
            hidden: {
              'qid': '70',
              '': {
                qaid: '367',
                value: '0',
              },
            },
            cssclass: {
              'qid': '70',
              '': {
                qaid: '368',
                value: '',
              },
            },
            use_dropdown: {
              'qid': '70',
              '': {
                qaid: '369',
                value: '0',
              },
            },
            printable_help: {
              qid: '70',
              en: {
                qaid: '370',
                value: '',
              },
            },
            page_break: {
              'qid': '70',
              '': {
                qaid: '371',
                value: '0',
              },
            },
            scale_export: {
              'qid': '70',
              '': {
                qaid: '372',
                value: '0',
              },
            },
            public_statistics: {
              'qid': '70',
              '': {
                qaid: '373',
                value: '0',
              },
            },
            statistics_showgraph: {
              'qid': '70',
              '': {
                qaid: '374',
                value: '1',
              },
            },
            statistics_graphtype: {
              'qid': '70',
              '': {
                qaid: '375',
                value: '0',
              },
            },
            save_as_default: {
              'qid': '70',
              '': {
                qaid: '376',
                value: 'N',
              },
            },
          },
          subquestions: [
            {
              qid: 71,
              parentQid: 70,
              sid: 596477,
              type: 'T',
              title: 'SQ001',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 0,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 5,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 73,
                  qid: 71,
                  question: 'Subquestion 1',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 74,
              parentQid: 70,
              sid: 596477,
              type: 'T',
              title: 'SQ002',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 1,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 5,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 76,
                  qid: 74,
                  question: 'Subquestion 2',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 75,
              parentQid: 70,
              sid: 596477,
              type: 'T',
              title: 'SQ003',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 2,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 5,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 77,
                  qid: 75,
                  question: 'Subquestion 3',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
          ],
          answers: [
            {
              aid: 33,
              qid: 70,
              code: 'AO01',
              sortOrder: 0,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 33,
                  aid: 33,
                  answer: 'Answer 1',
                  language: 'en',
                },
              },
            },
            {
              aid: 34,
              qid: 70,
              code: 'AO02',
              sortOrder: 1,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 34,
                  aid: 34,
                  answer: 'Answer 2',
                  language: 'en',
                },
              },
            },
            {
              aid: 35,
              qid: 70,
              code: 'AO03',
              sortOrder: 2,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 35,
                  aid: 35,
                  answer: 'Answer 3',
                  language: 'en',
                },
              },
            },
          ],
        },
        {
          qid: 888,
          parentQid: 0,
          sid: 596477,
          type: ';',
          title: 'G01Q01',
          preg: '',
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 1,
          scaleId: 0,
          sameDefault: false,
          questionThemeName: 'arrays/texts',
          moduleName: '',
          gid: 275,
          relevance: '1',
          sameScript: false,
          l10ns: {
            en: {
              id: 478,
              qid: 888,
              question: '',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            save_as_default: {
              '': 'N',
            },
            statistics_graphtype: {
              '': '0',
            },
            statistics_showgraph: {
              '': '1',
            },
            show_totals: {
              '': 'X',
            },
            show_grand_total: {
              '': '0',
            },
            numbers_only: {
              '': '0',
            },
            page_break: {
              '': '0',
            },
            maximum_chars: {
              '': '',
            },
            cssclass: {
              '': '',
            },
            hidden: {
              '': '0',
            },
            random_order: {
              '': '0',
            },
            hide_tip: {
              '': '0',
            },
            input_size: {
              '': '',
            },
            placeholder: {
              en: '',
            },
            repeat_headings: {
              '': '',
            },
            answer_width: {
              '': '',
            },
            em_validation_sq_tip: {
              en: '',
            },
            em_validation_sq: {
              '': '',
            },
            em_validation_q_tip: {
              en: '',
            },
            em_validation_q: {
              '': '',
            },
            random_group: {
              '': '',
            },
            array_filter: {
              '': '',
            },
            array_filter_style: {
              '': '0',
            },
            array_filter_exclude: {
              '': '',
            },
            max_answers: {
              '': '',
            },
            min_answers: {
              '': '',
            },
          },
          subquestions: [
            {
              qid: 237,
              parentQid: 236,
              sid: 596477,
              type: 'T',
              title: 'SQ001',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 0,
              scaleId: 0,
              sameDefault: false,
              questionThemeName: null,
              moduleName: null,
              gid: 275,
              relevance: '1',
              sameScript: false,
              l10ns: {
                en: {
                  id: 479,
                  qid: 237,
                  question: 'Y1',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 238,
              parentQid: 236,
              sid: 596477,
              type: 'T',
              title: 'SQ001',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 1,
              scaleId: 1,
              sameDefault: false,
              questionThemeName: null,
              moduleName: null,
              gid: 275,
              relevance: '',
              sameScript: false,
              l10ns: {
                en: {
                  id: 480,
                  qid: 238,
                  question: 'X1',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 239,
              parentQid: 236,
              sid: 596477,
              type: 'T',
              title: 'SQ003',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 2,
              scaleId: 0,
              sameDefault: false,
              questionThemeName: null,
              moduleName: null,
              gid: 275,
              relevance: '1',
              sameScript: false,
              l10ns: {
                en: {
                  id: 481,
                  qid: 239,
                  question: 'Y2',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 240,
              parentQid: 236,
              sid: 596477,
              type: 'T',
              title: 'SQ002',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 3,
              scaleId: 0,
              sameDefault: false,
              questionThemeName: null,
              moduleName: null,
              gid: 275,
              relevance: '1',
              sameScript: false,
              l10ns: {
                en: {
                  id: 482,
                  qid: 240,
                  question: 'Y3',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 241,
              parentQid: 236,
              sid: 596477,
              type: 'T',
              title: 'SQ003',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 4,
              scaleId: 1,
              sameDefault: false,
              questionThemeName: null,
              moduleName: null,
              gid: 275,
              relevance: '',
              sameScript: false,
              l10ns: {
                en: {
                  id: 483,
                  qid: 241,
                  question: 'X2',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 242,
              parentQid: 236,
              sid: 596477,
              type: 'T',
              title: 'SQ002',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 5,
              scaleId: 1,
              sameDefault: false,
              questionThemeName: null,
              moduleName: null,
              gid: 275,
              relevance: '',
              sameScript: false,
              l10ns: {
                en: {
                  id: 484,
                  qid: 242,
                  question: 'X3',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
          ],
          answers: [],
        },
        {
          qid: 236,
          parentQid: 0,
          sid: 596477,
          type: ':',
          title: 'G01Q01',
          preg: '',
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 1,
          scaleId: 0,
          sameDefault: false,
          questionThemeName: 'arrays/multiflexi',
          moduleName: '',
          gid: 275,
          relevance: '1',
          sameScript: false,
          l10ns: {
            en: {
              id: 478,
              qid: 236,
              question: '',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            public_statistics: {
              '': '0',
            },
            scale_export: {
              '': '0',
            },
            printable_help: {
              en: '',
            },
            multiflexible_max: {
              '': '',
            },
            multiflexible_step: {
              '': '1',
            },
            input_boxes: {
              '': '0',
            },
            multiflexible_min: {
              '': '',
            },
            parent_order: {
              '': '',
            },
            reverse: {
              '': '0',
            },
            multiflexible_checkbox: {
              '': '0',
            },
            save_as_default: {
              '': 'N',
            },
            statistics_graphtype: {
              '': '0',
            },
            statistics_showgraph: {
              '': '1',
            },
            show_totals: {
              '': 'X',
            },
            show_grand_total: {
              '': '0',
            },
            numbers_only: {
              '': '0',
            },
            page_break: {
              '': '0',
            },
            maximum_chars: {
              '': '',
            },
            cssclass: {
              '': '',
            },
            hidden: {
              '': '0',
            },
            random_order: {
              '': '0',
            },
            hide_tip: {
              '': '0',
            },
            input_size: {
              '': '',
            },
            placeholder: {
              en: '',
            },
            repeat_headings: {
              '': '',
            },
            answer_width: {
              '': '',
            },
            em_validation_sq_tip: {
              en: '',
            },
            em_validation_sq: {
              '': '',
            },
            em_validation_q_tip: {
              en: '',
            },
            em_validation_q: {
              '': '',
            },
            random_group: {
              '': '',
            },
            array_filter: {
              '': '',
            },
            array_filter_style: {
              '': '0',
            },
            array_filter_exclude: {
              '': '',
            },
            max_answers: {
              '': '',
            },
            min_answers: {
              '': '',
            },
          },
          subquestions: [
            {
              qid: 237,
              parentQid: 236,
              sid: 596477,
              type: 'T',
              title: 'SQ001',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 0,
              scaleId: 0,
              sameDefault: false,
              questionThemeName: null,
              moduleName: null,
              gid: 275,
              relevance: '1',
              sameScript: false,
              l10ns: {
                en: {
                  id: 479,
                  qid: 237,
                  question: 'Y1',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 239,
              parentQid: 236,
              sid: 596477,
              type: 'T',
              title: 'SQ003',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 1,
              scaleId: 0,
              sameDefault: false,
              questionThemeName: null,
              moduleName: null,
              gid: 275,
              relevance: '1',
              sameScript: false,
              l10ns: {
                en: {
                  id: 481,
                  qid: 239,
                  question: 'Y2',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 240,
              parentQid: 236,
              sid: 596477,
              type: 'T',
              title: 'SQ002',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 2,
              scaleId: 0,
              sameDefault: false,
              questionThemeName: null,
              moduleName: null,
              gid: 275,
              relevance: '1',
              sameScript: false,
              l10ns: {
                en: {
                  id: 482,
                  qid: 240,
                  question: 'Y3',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 238,
              parentQid: 236,
              sid: 596477,
              type: 'T',
              title: 'SQ001',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 3,
              scaleId: 1,
              sameDefault: false,
              questionThemeName: null,
              moduleName: null,
              gid: 275,
              relevance: '',
              sameScript: false,
              l10ns: {
                en: {
                  id: 480,
                  qid: 238,
                  question: 'X1',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 241,
              parentQid: 236,
              sid: 596477,
              type: 'T',
              title: 'SQ003',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 4,
              scaleId: 1,
              sameDefault: false,
              questionThemeName: null,
              moduleName: null,
              gid: 275,
              relevance: '',
              sameScript: false,
              l10ns: {
                en: {
                  id: 483,
                  qid: 241,
                  question: 'X2',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 242,
              parentQid: 236,
              sid: 596477,
              type: 'T',
              title: 'SQ002',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 5,
              scaleId: 1,
              sameDefault: false,
              questionThemeName: null,
              moduleName: null,
              gid: 275,
              relevance: '',
              sameScript: false,
              l10ns: {
                en: {
                  id: 484,
                  qid: 242,
                  question: 'X3',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
          ],
          answers: [],
        },
        {
          qid: 86,
          parentQid: 0,
          sid: 596477,
          type: 'H',
          title: 'G02Q010',
          preg: null,
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 4,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: 'arrays/column',
          moduleName: '',
          gid: 5,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 88,
              qid: 86,
              question: 'Array by column',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            public_statistics: {
              'qid': '86',
              '': {
                qaid: '461',
                value: '0',
              },
            },
            scale_export: {
              'qid': '86',
              '': {
                qaid: '460',
                value: '0',
              },
            },
            page_break: {
              'qid': '86',
              '': {
                qaid: '459',
                value: '0',
              },
            },
            printable_help: {
              qid: '86',
              en: {
                qaid: '458',
                value: '',
              },
            },
            cssclass: {
              'qid': '86',
              '': {
                qaid: '457',
                value: '',
              },
            },
            hidden: {
              'qid': '86',
              '': {
                qaid: '456',
                value: '0',
              },
            },
            answer_width_bycolumn: {
              'qid': '86',
              '': {
                qaid: '455',
                value: '',
              },
            },
            hide_tip: {
              'qid': '86',
              '': {
                qaid: '454',
                value: '0',
              },
            },
            random_order: {
              'qid': '86',
              '': {
                qaid: '453',
                value: '0',
              },
            },
            em_validation_q_tip: {
              qid: '86',
              en: {
                qaid: '452',
                value: '',
              },
            },
            em_validation_q: {
              'qid': '86',
              '': {
                qaid: '451',
                value: '',
              },
            },
            random_group: {
              'qid': '86',
              '': {
                qaid: '450',
                value: '',
              },
            },
            statistics_showgraph: {
              'qid': '86',
              '': {
                qaid: '462',
                value: '1',
              },
            },
            statistics_graphtype: {
              'qid': '86',
              '': {
                qaid: '463',
                value: '0',
              },
            },
            save_as_default: {
              'qid': '86',
              '': {
                qaid: '464',
                value: 'N',
              },
            },
          },
          subquestions: [
            {
              qid: 87,
              parentQid: 86,
              sid: 596477,
              type: 'T',
              title: 'SQ001',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 0,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 5,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 89,
                  qid: 87,
                  question: 'Subquestion 1',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 88,
              parentQid: 86,
              sid: 596477,
              type: 'T',
              title: 'SQ002',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 1,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 5,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 90,
                  qid: 88,
                  question: 'Subquestion 2',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 89,
              parentQid: 86,
              sid: 596477,
              type: 'T',
              title: 'SQ003',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 2,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 5,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 91,
                  qid: 89,
                  question: 'Subquestion 3',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
          ],
          answers: [
            {
              aid: 40,
              qid: 86,
              code: 'AO01',
              sortOrder: 0,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 40,
                  aid: 40,
                  answer: 'Answer 1',
                  language: 'en',
                },
              },
            },
            {
              aid: 41,
              qid: 86,
              code: 'AO02',
              sortOrder: 1,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 41,
                  aid: 41,
                  answer: 'Answer 2',
                  language: 'en',
                },
              },
            },
            {
              aid: 42,
              qid: 86,
              code: 'AO03',
              sortOrder: 2,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 42,
                  aid: 42,
                  answer: 'Answer 3',
                  language: 'en',
                },
              },
            },
          ],
        },
        {
          qid: 90,
          parentQid: 0,
          sid: 596477,
          type: '1',
          title: 'G02Q09',
          preg: null,
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 5,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: 'arrays/dualscale',
          moduleName: '',
          gid: 5,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 92,
              qid: 90,
              question: 'Array dual scale',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            min_answers: {
              'qid': '90',
              '': {
                qaid: '465',
                value: '',
              },
            },
            max_answers: {
              'qid': '90',
              '': {
                qaid: '466',
                value: '',
              },
            },
            array_filter_exclude: {
              'qid': '90',
              '': {
                qaid: '467',
                value: '',
              },
            },
            array_filter: {
              'qid': '90',
              '': {
                qaid: '468',
                value: '',
              },
            },
            array_filter_style: {
              'qid': '90',
              '': {
                qaid: '469',
                value: '0',
              },
            },
            random_group: {
              'qid': '90',
              '': {
                qaid: '470',
                value: '',
              },
            },
            repeat_headings: {
              'qid': '90',
              '': {
                qaid: '471',
                value: '',
              },
            },
            random_order: {
              'qid': '90',
              '': {
                qaid: '472',
                value: '0',
              },
            },
            hide_tip: {
              'qid': '90',
              '': {
                qaid: '473',
                value: '0',
              },
            },
            answer_width: {
              'qid': '90',
              '': {
                qaid: '474',
                value: '',
              },
            },
            hidden: {
              'qid': '90',
              '': {
                qaid: '475',
                value: '0',
              },
            },
            cssclass: {
              'qid': '90',
              '': {
                qaid: '476',
                value: '',
              },
            },
            dualscale_headerA: {
              qid: '90',
              en: {
                qaid: '477',
                value: '',
              },
            },
            dualscale_headerB: {
              qid: '90',
              en: {
                qaid: '478',
                value: '',
              },
            },
            use_dropdown: {
              'qid': '90',
              '': {
                qaid: '479',
                value: '0',
              },
            },
            dropdown_prepostfix: {
              qid: '90',
              en: {
                qaid: '480',
                value: '',
              },
            },
            dropdown_separators: {
              'qid': '90',
              '': {
                qaid: '481',
                value: '',
              },
            },
            printable_help: {
              qid: '90',
              en: {
                qaid: '482',
                value: '',
              },
            },
            page_break: {
              'qid': '90',
              '': {
                qaid: '483',
                value: '0',
              },
            },
            scale_export: {
              'qid': '90',
              '': {
                qaid: '484',
                value: '0',
              },
            },
            public_statistics: {
              'qid': '90',
              '': {
                qaid: '485',
                value: '0',
              },
            },
            statistics_showgraph: {
              'qid': '90',
              '': {
                qaid: '486',
                value: '1',
              },
            },
            statistics_graphtype: {
              'qid': '90',
              '': {
                qaid: '487',
                value: '0',
              },
            },
            save_as_default: {
              'qid': '90',
              '': {
                qaid: '488',
                value: 'N',
              },
            },
          },
          subquestions: [
            {
              qid: 91,
              parentQid: 90,
              sid: 596477,
              type: 'T',
              title: 'SQ001',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 0,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 5,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 93,
                  qid: 91,
                  question: 'Subquestion 1',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 92,
              parentQid: 90,
              sid: 596477,
              type: 'T',
              title: 'SQ002',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 1,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 5,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 94,
                  qid: 92,
                  question: 'Subquestion 2',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 93,
              parentQid: 90,
              sid: 596477,
              type: 'T',
              title: 'SQ003',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 2,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 5,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 95,
                  qid: 93,
                  question: 'Subquestion 3',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
          ],
          answers: [
            {
              aid: 63,
              qid: 90,
              code: 'AO01',
              sortOrder: 0,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 63,
                  aid: 63,
                  answer: 'Answer 1',
                  language: 'en',
                },
              },
            },
            {
              aid: 64,
              qid: 90,
              code: 'AO02',
              sortOrder: 1,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 64,
                  aid: 64,
                  answer: 'Answer 2',
                  language: 'en',
                },
              },
            },
            {
              aid: 65,
              qid: 90,
              code: 'AO03',
              sortOrder: 2,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 65,
                  aid: 65,
                  answer: 'Answer 3',
                  language: 'en',
                },
              },
            },
            {
              aid: 66,
              qid: 90,
              code: 'AO01',
              sortOrder: 3,
              assessmentValue: 0,
              scaleId: 1,
              l10ns: {
                en: {
                  id: 66,
                  aid: 66,
                  answer: 'Answer 1',
                  language: 'en',
                },
              },
            },
            {
              aid: 67,
              qid: 90,
              code: 'AO02',
              sortOrder: 4,
              assessmentValue: 0,
              scaleId: 1,
              l10ns: {
                en: {
                  id: 67,
                  aid: 67,
                  answer: 'Answer 2',
                  language: 'en',
                },
              },
            },
            {
              aid: 68,
              qid: 90,
              code: 'AO03',
              sortOrder: 5,
              assessmentValue: 0,
              scaleId: 1,
              l10ns: {
                en: {
                  id: 68,
                  aid: 68,
                  answer: 'Answer 3',
                  language: 'en',
                },
              },
            },
          ],
        },
      ],
    },
    {
      gid: 6,
      sid: 596477,
      sortOrder: 3,
      randomizationGroup: '',
      gRelevance: '',
      l10ns: {
        en: {
          id: 6,
          gid: 6,
          groupName: 'Multiple choice questions',
          description: '',
          language: 'en',
        },
      },
      questions: [
        {
          qid: 94,
          parentQid: 0,
          sid: 596477,
          type: 'M',
          title: 'G03Q10',
          preg: null,
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 1,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: 'multiplechoice',
          moduleName: '',
          gid: 6,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 97,
              qid: 94,
              question: 'Multiple choice',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            min_answers: {
              'qid': '94',
              '': {
                qaid: '489',
                value: '',
              },
            },
            max_answers: {
              'qid': '94',
              '': {
                qaid: '490',
                value: '',
              },
            },
            array_filter_exclude: {
              'qid': '94',
              '': {
                qaid: '491',
                value: '',
              },
            },
            array_filter_style: {
              'qid': '94',
              '': {
                qaid: '492',
                value: '0',
              },
            },
            assessment_value: {
              'qid': '94',
              '': {
                qaid: '493',
                value: '1',
              },
            },
            other_numbers_only: {
              'qid': '94',
              '': {
                qaid: '494',
                value: '0',
              },
            },
            array_filter: {
              'qid': '94',
              '': {
                qaid: '495',
                value: '',
              },
            },
            exclude_all_others: {
              'qid': '94',
              '': {
                qaid: '496',
                value: '',
              },
            },
            exclude_all_others_auto: {
              'qid': '94',
              '': {
                qaid: '497',
                value: '0',
              },
            },
            random_group: {
              'qid': '94',
              '': {
                qaid: '498',
                value: '',
              },
            },
            em_validation_q: {
              'qid': '94',
              '': {
                qaid: '499',
                value: '',
              },
            },
            em_validation_q_tip: {
              qid: '94',
              en: {
                qaid: '500',
                value: '',
              },
            },
            other_replace_text: {
              qid: '94',
              en: {
                qaid: '501',
                value: '',
              },
            },
            display_columns: {
              'qid': '94',
              '': {
                qaid: '502',
                value: '',
              },
            },
            hide_tip: {
              'qid': '94',
              '': {
                qaid: '503',
                value: '0',
              },
            },
            random_order: {
              'qid': '94',
              '': {
                qaid: '504',
                value: '0',
              },
            },
            hidden: {
              'qid': '94',
              '': {
                qaid: '505',
                value: '0',
              },
            },
            cssclass: {
              'qid': '94',
              '': {
                qaid: '506',
                value: '',
              },
            },
            other_position: {
              'qid': '94',
              '': {
                qaid: '507',
                value: 'end',
              },
            },
            other_position_code: {
              'qid': '94',
              '': {
                qaid: '508',
                value: '',
              },
            },
            printable_help: {
              qid: '94',
              en: {
                qaid: '509',
                value: '',
              },
            },
            page_break: {
              'qid': '94',
              '': {
                qaid: '510',
                value: '0',
              },
            },
            scale_export: {
              'qid': '94',
              '': {
                qaid: '511',
                value: '0',
              },
            },
            public_statistics: {
              'qid': '94',
              '': {
                qaid: '512',
                value: '0',
              },
            },
            statistics_showgraph: {
              'qid': '94',
              '': {
                qaid: '513',
                value: '1',
              },
            },
            statistics_graphtype: {
              'qid': '94',
              '': {
                qaid: '514',
                value: '0',
              },
            },
            save_as_default: {
              'qid': '94',
              '': {
                qaid: '515',
                value: 'N',
              },
            },
          },
          subquestions: [
            {
              qid: 95,
              parentQid: 94,
              sid: 596477,
              type: 'T',
              title: 'SQ001',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 0,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 6,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 98,
                  qid: 95,
                  question: 'Subquestion 1',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 96,
              parentQid: 94,
              sid: 596477,
              type: 'T',
              title: 'SQ002',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 1,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 6,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 99,
                  qid: 96,
                  question: 'Subquestion 2',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 97,
              parentQid: 94,
              sid: 596477,
              type: 'T',
              title: 'SQ003',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 2,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 6,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 100,
                  qid: 97,
                  question: 'Subquestion 3',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
          ],
          answers: [],
        },
        {
          qid: 98,
          parentQid: 0,
          sid: 596477,
          type: 'P',
          title: 'G03Q11',
          preg: '',
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 2,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: 'multiplechoice_with_comments',
          moduleName: '',
          gid: 6,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 101,
              qid: 98,
              question: 'Multiple choice with comments',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            min_answers: {
              'qid': '98',
              '': {
                qaid: '516',
                value: '',
              },
            },
            max_answers: {
              'qid': '98',
              '': {
                qaid: '517',
                value: '',
              },
            },
            array_filter_exclude: {
              'qid': '98',
              '': {
                qaid: '518',
                value: '',
              },
            },
            other_numbers_only: {
              'qid': '98',
              '': {
                qaid: '519',
                value: '0',
              },
            },
            other_comment_mandatory: {
              'qid': '98',
              '': {
                qaid: '520',
                value: '0',
              },
            },
            assessment_value: {
              'qid': '98',
              '': {
                qaid: '521',
                value: '1',
              },
            },
            array_filter: {
              'qid': '98',
              '': {
                qaid: '522',
                value: '',
              },
            },
            array_filter_style: {
              'qid': '98',
              '': {
                qaid: '523',
                value: '0',
              },
            },
            commented_checkbox: {
              'qid': '98',
              '': {
                qaid: '524',
                value: 'checked',
              },
            },
            commented_checkbox_auto: {
              'qid': '98',
              '': {
                qaid: '525',
                value: '1',
              },
            },
            exclude_all_others: {
              'qid': '98',
              '': {
                qaid: '526',
                value: '',
              },
            },
            exclude_all_others_auto: {
              'qid': '98',
              '': {
                qaid: '527',
                value: '0',
              },
            },
            random_group: {
              'qid': '98',
              '': {
                qaid: '528',
                value: '',
              },
            },
            em_validation_q: {
              'qid': '98',
              '': {
                qaid: '529',
                value: '',
              },
            },
            em_validation_q_tip: {
              qid: '98',
              en: {
                qaid: '530',
                value: '',
              },
            },
            choice_input_columns: {
              'qid': '98',
              '': {
                qaid: '531',
                value: '',
              },
            },
            text_input_columns: {
              'qid': '98',
              '': {
                qaid: '532',
                value: '',
              },
            },
            other_replace_text: {
              qid: '98',
              en: {
                qaid: '533',
                value: '',
              },
            },
            hide_tip: {
              'qid': '98',
              '': {
                qaid: '534',
                value: '0',
              },
            },
            random_order: {
              'qid': '98',
              '': {
                qaid: '535',
                value: '0',
              },
            },
            hidden: {
              'qid': '98',
              '': {
                qaid: '536',
                value: '0',
              },
            },
            cssclass: {
              'qid': '98',
              '': {
                qaid: '537',
                value: '',
              },
            },
            other_position: {
              'qid': '98',
              '': {
                qaid: '538',
                value: 'end',
              },
            },
            other_position_code: {
              'qid': '98',
              '': {
                qaid: '539',
                value: '',
              },
            },
            printable_help: {
              qid: '98',
              en: {
                qaid: '540',
                value: '',
              },
            },
            page_break: {
              'qid': '98',
              '': {
                qaid: '541',
                value: '0',
              },
            },
            scale_export: {
              'qid': '98',
              '': {
                qaid: '542',
                value: '0',
              },
            },
            public_statistics: {
              'qid': '98',
              '': {
                qaid: '543',
                value: '0',
              },
            },
            statistics_showgraph: {
              'qid': '98',
              '': {
                qaid: '544',
                value: '1',
              },
            },
            save_as_default: {
              'qid': '98',
              '': {
                qaid: '545',
                value: 'N',
              },
            },
          },
          subquestions: [
            {
              qid: 99,
              parentQid: 98,
              sid: 596477,
              type: 'T',
              title: 'SQ001',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 0,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 6,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 102,
                  qid: 99,
                  question: 'Subquestion 1',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 100,
              parentQid: 98,
              sid: 596477,
              type: 'T',
              title: 'SQ002',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 1,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 6,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 103,
                  qid: 100,
                  question: 'Subquestion 2',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 101,
              parentQid: 98,
              sid: 596477,
              type: 'T',
              title: 'SQ003',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 2,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 6,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 104,
                  qid: 101,
                  question: 'Subquestion 3',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
          ],
          answers: [],
        },
        {
          qid: 102,
          parentQid: 0,
          sid: 596477,
          type: 'M',
          title: 'G03Q12',
          preg: '',
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 3,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: 'bootstrap_buttons_multi',
          moduleName: '',
          gid: 6,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 105,
              qid: 102,
              question: 'Multiple choice buttons',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            min_answers: {
              'qid': '102',
              '': {
                qaid: '546',
                value: '',
              },
            },
            max_answers: {
              'qid': '102',
              '': {
                qaid: '547',
                value: '',
              },
            },
            other_numbers_only: {
              'qid': '102',
              '': {
                qaid: '548',
                value: '0',
              },
            },
            array_filter_exclude: {
              'qid': '102',
              '': {
                qaid: '549',
                value: '',
              },
            },
            assessment_value: {
              'qid': '102',
              '': {
                qaid: '550',
                value: '1',
              },
            },
            array_filter: {
              'qid': '102',
              '': {
                qaid: '551',
                value: '',
              },
            },
            array_filter_style: {
              'qid': '102',
              '': {
                qaid: '552',
                value: '0',
              },
            },
            exclude_all_others: {
              'qid': '102',
              '': {
                qaid: '553',
                value: '',
              },
            },
            exclude_all_others_auto: {
              'qid': '102',
              '': {
                qaid: '554',
                value: '0',
              },
            },
            random_group: {
              'qid': '102',
              '': {
                qaid: '555',
                value: '',
              },
            },
            em_validation_q: {
              'qid': '102',
              '': {
                qaid: '556',
                value: '',
              },
            },
            em_validation_q_tip: {
              qid: '102',
              en: {
                qaid: '557',
                value: '',
              },
            },
            other_replace_text: {
              qid: '102',
              en: {
                qaid: '558',
                value: '',
              },
            },
            hide_tip: {
              'qid': '102',
              '': {
                qaid: '559',
                value: '0',
              },
            },
            random_order: {
              'qid': '102',
              '': {
                qaid: '560',
                value: '0',
              },
            },
            hidden: {
              'qid': '102',
              '': {
                qaid: '561',
                value: '0',
              },
            },
            cssclass: {
              'qid': '102',
              '': {
                qaid: '562',
                value: '',
              },
            },
            other_position: {
              'qid': '102',
              '': {
                qaid: '563',
                value: 'end',
              },
            },
            other_position_code: {
              'qid': '102',
              '': {
                qaid: '564',
                value: '',
              },
            },
            printable_help: {
              qid: '102',
              en: {
                qaid: '565',
                value: '',
              },
            },
            page_break: {
              'qid': '102',
              '': {
                qaid: '566',
                value: '0',
              },
            },
            scale_export: {
              'qid': '102',
              '': {
                qaid: '567',
                value: '0',
              },
            },
            public_statistics: {
              'qid': '102',
              '': {
                qaid: '568',
                value: '0',
              },
            },
            statistics_showgraph: {
              'qid': '102',
              '': {
                qaid: '569',
                value: '1',
              },
            },
            statistics_graphtype: {
              'qid': '102',
              '': {
                qaid: '570',
                value: '0',
              },
            },
            save_as_default: {
              'qid': '102',
              '': {
                qaid: '571',
                value: 'N',
              },
            },
          },
          subquestions: [
            {
              qid: 103,
              parentQid: 102,
              sid: 596477,
              type: 'T',
              title: 'SQ001',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 0,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 6,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 106,
                  qid: 103,
                  question: 'Subquestion 1',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 104,
              parentQid: 102,
              sid: 596477,
              type: 'T',
              title: 'SQ002',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 1,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 6,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 107,
                  qid: 104,
                  question: 'Subquestion 2',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 105,
              parentQid: 102,
              sid: 596477,
              type: 'T',
              title: 'SQ003',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 2,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 6,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 108,
                  qid: 105,
                  question: 'Subquestion 3',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
          ],
          answers: [],
        },
      ],
    },
    {
      gid: 7,
      sid: 596477,
      sortOrder: 4,
      randomizationGroup: '',
      gRelevance: '',
      l10ns: {
        en: {
          id: 7,
          gid: 7,
          groupName: 'Single choice questions',
          description: '',
          language: 'en',
        },
      },
      questions: [
        {
          qid: 106,
          parentQid: 0,
          sid: 596477,
          type: 'L',
          title: 'G04Q13',
          preg: null,
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 1,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: 'listradio',
          moduleName: '',
          gid: 7,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 110,
              qid: 106,
              question: 'List (Radio)',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            array_filter_exclude: {
              'qid': '106',
              '': {
                qaid: '572',
                value: '',
              },
            },
            array_filter: {
              'qid': '106',
              '': {
                qaid: '573',
                value: '',
              },
            },
            other_comment_mandatory: {
              'qid': '106',
              '': {
                qaid: '574',
                value: '0',
              },
            },
            other_numbers_only: {
              'qid': '106',
              '': {
                qaid: '575',
                value: '0',
              },
            },
            array_filter_style: {
              'qid': '106',
              '': {
                qaid: '576',
                value: '0',
              },
            },
            random_group: {
              'qid': '106',
              '': {
                qaid: '577',
                value: '',
              },
            },
            em_validation_q: {
              'qid': '106',
              '': {
                qaid: '578',
                value: '',
              },
            },
            em_validation_q_tip: {
              qid: '106',
              en: {
                qaid: '579',
                value: '',
              },
            },
            other_replace_text: {
              qid: '106',
              en: {
                qaid: '580',
                value: '',
              },
            },
            answer_order: {
              'qid': '106',
              '': {
                qaid: '581',
                value: 'normal',
              },
            },
            display_columns: {
              'qid': '106',
              '': {
                qaid: '582',
                value: '',
              },
            },
            hide_tip: {
              'qid': '106',
              '': {
                qaid: '583',
                value: '0',
              },
            },
            hidden: {
              'qid': '106',
              '': {
                qaid: '584',
                value: '0',
              },
            },
            cssclass: {
              'qid': '106',
              '': {
                qaid: '585',
                value: '',
              },
            },
            other_position: {
              'qid': '106',
              '': {
                qaid: '586',
                value: 'default',
              },
            },
            other_position_code: {
              'qid': '106',
              '': {
                qaid: '587',
                value: '',
              },
            },
            printable_help: {
              qid: '106',
              en: {
                qaid: '588',
                value: '',
              },
            },
            page_break: {
              'qid': '106',
              '': {
                qaid: '589',
                value: '0',
              },
            },
            scale_export: {
              'qid': '106',
              '': {
                qaid: '590',
                value: '0',
              },
            },
            time_limit: {
              'qid': '106',
              '': {
                qaid: '591',
                value: '',
              },
            },
            time_limit_action: {
              'qid': '106',
              '': {
                qaid: '592',
                value: '1',
              },
            },
            time_limit_disable_next: {
              'qid': '106',
              '': {
                qaid: '593',
                value: '0',
              },
            },
            time_limit_disable_prev: {
              'qid': '106',
              '': {
                qaid: '594',
                value: '0',
              },
            },
            time_limit_countdown_message: {
              qid: '106',
              en: {
                qaid: '595',
                value: '',
              },
            },
            time_limit_timer_style: {
              'qid': '106',
              '': {
                qaid: '596',
                value: '',
              },
            },
            time_limit_message_delay: {
              'qid': '106',
              '': {
                qaid: '597',
                value: '',
              },
            },
            time_limit_message: {
              qid: '106',
              en: {
                qaid: '598',
                value: '',
              },
            },
            time_limit_message_style: {
              'qid': '106',
              '': {
                qaid: '599',
                value: '',
              },
            },
            time_limit_warning: {
              'qid': '106',
              '': {
                qaid: '600',
                value: '',
              },
            },
            time_limit_warning_display_time: {
              'qid': '106',
              '': {
                qaid: '601',
                value: '',
              },
            },
            time_limit_warning_message: {
              qid: '106',
              en: {
                qaid: '602',
                value: '',
              },
            },
            time_limit_warning_style: {
              'qid': '106',
              '': {
                qaid: '603',
                value: '',
              },
            },
            time_limit_warning_2: {
              'qid': '106',
              '': {
                qaid: '604',
                value: '',
              },
            },
            time_limit_warning_2_display_time: {
              'qid': '106',
              '': {
                qaid: '605',
                value: '',
              },
            },
            time_limit_warning_2_message: {
              qid: '106',
              en: {
                qaid: '606',
                value: '',
              },
            },
            time_limit_warning_2_style: {
              'qid': '106',
              '': {
                qaid: '607',
                value: '',
              },
            },
            public_statistics: {
              'qid': '106',
              '': {
                qaid: '608',
                value: '0',
              },
            },
            statistics_showgraph: {
              'qid': '106',
              '': {
                qaid: '609',
                value: '1',
              },
            },
            statistics_graphtype: {
              'qid': '106',
              '': {
                qaid: '610',
                value: '0',
              },
            },
            save_as_default: {
              'qid': '106',
              '': {
                qaid: '611',
                value: 'N',
              },
            },
          },
          answers: [
            {
              aid: 76,
              qid: 106,
              code: 'AO01',
              sortOrder: 0,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 76,
                  aid: 76,
                  answer: 'Answer 1',
                  language: 'en',
                },
              },
            },
            {
              aid: 77,
              qid: 106,
              code: 'AO02',
              sortOrder: 1,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 77,
                  aid: 77,
                  answer: 'Answer 2',
                  language: 'en',
                },
              },
            },
            {
              aid: 78,
              qid: 106,
              code: 'AO03',
              sortOrder: 2,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 78,
                  aid: 78,
                  answer: 'Answer 3',
                  language: 'en',
                },
              },
            },
            {
              aid: 79,
              qid: 106,
              code: 'AO04',
              sortOrder: 3,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 79,
                  aid: 79,
                  answer: 'Answer 4',
                  language: 'en',
                },
              },
            },
          ],
        },
        {
          qid: 107,
          parentQid: 0,
          sid: 596477,
          type: 'O',
          title: 'G04Q14',
          preg: null,
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 2,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: 'list_with_comment',
          moduleName: '',
          gid: 7,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 111,
              qid: 107,
              question: 'List with comment',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            random_group: {
              'qid': '107',
              '': {
                qaid: '612',
                value: '',
              },
            },
            em_validation_q: {
              'qid': '107',
              '': {
                qaid: '613',
                value: '',
              },
            },
            em_validation_q_tip: {
              qid: '107',
              en: {
                qaid: '614',
                value: '',
              },
            },
            answer_order: {
              'qid': '107',
              '': {
                qaid: '615',
                value: 'normal',
              },
            },
            hide_tip: {
              'qid': '107',
              '': {
                qaid: '616',
                value: '0',
              },
            },
            hidden: {
              'qid': '107',
              '': {
                qaid: '617',
                value: '0',
              },
            },
            cssclass: {
              'qid': '107',
              '': {
                qaid: '618',
                value: '',
              },
            },
            use_dropdown: {
              'qid': '107',
              '': {
                qaid: '619',
                value: '0',
              },
            },
            printable_help: {
              qid: '107',
              en: {
                qaid: '620',
                value: '',
              },
            },
            page_break: {
              'qid': '107',
              '': {
                qaid: '621',
                value: '0',
              },
            },
            scale_export: {
              'qid': '107',
              '': {
                qaid: '622',
                value: '0',
              },
            },
            public_statistics: {
              'qid': '107',
              '': {
                qaid: '623',
                value: '0',
              },
            },
            statistics_showgraph: {
              'qid': '107',
              '': {
                qaid: '624',
                value: '1',
              },
            },
            statistics_graphtype: {
              'qid': '107',
              '': {
                qaid: '625',
                value: '0',
              },
            },
            save_as_default: {
              'qid': '107',
              '': {
                qaid: '626',
                value: 'N',
              },
            },
          },
          answers: [
            {
              aid: 84,
              qid: 107,
              code: 'AO01',
              sortOrder: 0,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 84,
                  aid: 84,
                  answer: 'Answer 1',
                  language: 'en',
                },
              },
            },
            {
              aid: 85,
              qid: 107,
              code: 'AO02',
              sortOrder: 1,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 85,
                  aid: 85,
                  answer: 'Answer 2',
                  language: 'en',
                },
              },
            },
            {
              aid: 86,
              qid: 107,
              code: 'AO03',
              sortOrder: 2,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 86,
                  aid: 86,
                  answer: 'Answer 3',
                  language: 'en',
                },
              },
            },
            {
              aid: 87,
              qid: 107,
              code: 'AO04',
              sortOrder: 3,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 87,
                  aid: 87,
                  answer: 'Answer 4',
                  language: 'en',
                },
              },
            },
          ],
        },
        {
          qid: 108,
          parentQid: 0,
          sid: 596477,
          type: '5',
          title: 'G04Q15',
          preg: null,
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 3,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: '5pointchoice',
          moduleName: '',
          gid: 7,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 112,
              qid: 108,
              question: '5 Point choice',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            random_group: {
              'qid': '108',
              '': {
                qaid: '627',
                value: '',
              },
            },
            em_validation_q: {
              'qid': '108',
              '': {
                qaid: '628',
                value: '',
              },
            },
            em_validation_q_tip: {
              qid: '108',
              en: {
                qaid: '629',
                value: '',
              },
            },
            slider_rating: {
              'qid': '108',
              '': {
                qaid: '630',
                value: '0',
              },
            },
            hide_tip: {
              'qid': '108',
              '': {
                qaid: '631',
                value: '0',
              },
            },
            hidden: {
              'qid': '108',
              '': {
                qaid: '632',
                value: '0',
              },
            },
            cssclass: {
              'qid': '108',
              '': {
                qaid: '633',
                value: '',
              },
            },
            printable_help: {
              qid: '108',
              en: {
                qaid: '634',
                value: '',
              },
            },
            page_break: {
              'qid': '108',
              '': {
                qaid: '635',
                value: '0',
              },
            },
            public_statistics: {
              'qid': '108',
              '': {
                qaid: '636',
                value: '0',
              },
            },
            statistics_showgraph: {
              'qid': '108',
              '': {
                qaid: '637',
                value: '1',
              },
            },
            statistics_graphtype: {
              'qid': '108',
              '': {
                qaid: '638',
                value: '0',
              },
            },
            save_as_default: {
              'qid': '108',
              '': {
                qaid: '639',
                value: 'N',
              },
            },
          },
          answers: [
            {
              aid: 88,
              qid: 108,
              code: 'AO01',
              sortOrder: 0,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 88,
                  aid: 88,
                  answer: 'Answer 1',
                  language: 'en',
                },
              },
            },
            {
              aid: 89,
              qid: 108,
              code: 'AO02',
              sortOrder: 1,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 89,
                  aid: 89,
                  answer: 'Answer 2',
                  language: 'en',
                },
              },
            },
            {
              aid: 90,
              qid: 108,
              code: 'AO03',
              sortOrder: 2,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 90,
                  aid: 90,
                  answer: 'Answer 3',
                  language: 'en',
                },
              },
            },
            {
              aid: 91,
              qid: 108,
              code: 'AO04',
              sortOrder: 3,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 91,
                  aid: 91,
                  answer: 'Answer 4',
                  language: 'en',
                },
              },
            },
          ],
        },
        {
          qid: 109,
          parentQid: 0,
          sid: 596477,
          type: '!',
          title: 'G04Q16',
          preg: null,
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 4,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: 'list_dropdown',
          moduleName: '',
          gid: 7,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 113,
              qid: 109,
              question: 'List dropdown.',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            other_comment_mandatory: {
              'qid': '109',
              '': {
                qaid: '640',
                value: '0',
              },
            },
            random_group: {
              'qid': '109',
              '': {
                qaid: '641',
                value: '',
              },
            },
            em_validation_q: {
              'qid': '109',
              '': {
                qaid: '642',
                value: '',
              },
            },
            em_validation_q_tip: {
              qid: '109',
              en: {
                qaid: '643',
                value: '',
              },
            },
            category_separator: {
              'qid': '109',
              '': {
                qaid: '644',
                value: '',
              },
            },
            answer_order: {
              'qid': '109',
              '': {
                qaid: '645',
                value: 'normal',
              },
            },
            other_replace_text: {
              qid: '109',
              en: {
                qaid: '646',
                value: '',
              },
            },
            hide_tip: {
              'qid': '109',
              '': {
                qaid: '647',
                value: '0',
              },
            },
            hidden: {
              'qid': '109',
              '': {
                qaid: '648',
                value: '0',
              },
            },
            cssclass: {
              'qid': '109',
              '': {
                qaid: '649',
                value: '',
              },
            },
            other_position: {
              'qid': '109',
              '': {
                qaid: '650',
                value: 'default',
              },
            },
            other_position_code: {
              'qid': '109',
              '': {
                qaid: '651',
                value: '',
              },
            },
            dropdown_size: {
              'qid': '109',
              '': {
                qaid: '652',
                value: '',
              },
            },
            printable_help: {
              qid: '109',
              en: {
                qaid: '653',
                value: '',
              },
            },
            dropdown_prefix: {
              'qid': '109',
              '': {
                qaid: '654',
                value: '0',
              },
            },
            page_break: {
              'qid': '109',
              '': {
                qaid: '655',
                value: '0',
              },
            },
            scale_export: {
              'qid': '109',
              '': {
                qaid: '656',
                value: '0',
              },
            },
            time_limit: {
              'qid': '109',
              '': {
                qaid: '657',
                value: '',
              },
            },
            time_limit_action: {
              'qid': '109',
              '': {
                qaid: '658',
                value: '1',
              },
            },
            time_limit_disable_next: {
              'qid': '109',
              '': {
                qaid: '659',
                value: '0',
              },
            },
            time_limit_disable_prev: {
              'qid': '109',
              '': {
                qaid: '660',
                value: '0',
              },
            },
            time_limit_countdown_message: {
              qid: '109',
              en: {
                qaid: '661',
                value: '',
              },
            },
            time_limit_timer_style: {
              'qid': '109',
              '': {
                qaid: '662',
                value: '',
              },
            },
            time_limit_message_delay: {
              'qid': '109',
              '': {
                qaid: '663',
                value: '',
              },
            },
            time_limit_message: {
              qid: '109',
              en: {
                qaid: '664',
                value: '',
              },
            },
            time_limit_message_style: {
              'qid': '109',
              '': {
                qaid: '665',
                value: '',
              },
            },
            time_limit_warning: {
              'qid': '109',
              '': {
                qaid: '666',
                value: '',
              },
            },
            time_limit_warning_display_time: {
              'qid': '109',
              '': {
                qaid: '667',
                value: '',
              },
            },
            time_limit_warning_message: {
              qid: '109',
              en: {
                qaid: '668',
                value: '',
              },
            },
            time_limit_warning_style: {
              'qid': '109',
              '': {
                qaid: '669',
                value: '',
              },
            },
            time_limit_warning_2: {
              'qid': '109',
              '': {
                qaid: '670',
                value: '',
              },
            },
            time_limit_warning_2_display_time: {
              'qid': '109',
              '': {
                qaid: '671',
                value: '',
              },
            },
            time_limit_warning_2_message: {
              qid: '109',
              en: {
                qaid: '672',
                value: '',
              },
            },
            time_limit_warning_2_style: {
              'qid': '109',
              '': {
                qaid: '673',
                value: '',
              },
            },
            public_statistics: {
              'qid': '109',
              '': {
                qaid: '674',
                value: '0',
              },
            },
            statistics_showgraph: {
              'qid': '109',
              '': {
                qaid: '675',
                value: '1',
              },
            },
            statistics_graphtype: {
              'qid': '109',
              '': {
                qaid: '676',
                value: '0',
              },
            },
            save_as_default: {
              'qid': '109',
              '': {
                qaid: '677',
                value: 'N',
              },
            },
          },
          answers: [
            {
              aid: 96,
              qid: 109,
              code: 'AO01',
              sortOrder: 0,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 96,
                  aid: 96,
                  answer: 'Answer 1',
                  language: 'en',
                },
              },
            },
            {
              aid: 97,
              qid: 109,
              code: 'AO02',
              sortOrder: 1,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 97,
                  aid: 97,
                  answer: 'Answer 2',
                  language: 'en',
                },
              },
            },
            {
              aid: 98,
              qid: 109,
              code: 'AO03',
              sortOrder: 2,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 98,
                  aid: 98,
                  answer: 'Answer 3',
                  language: 'en',
                },
              },
            },
            {
              aid: 99,
              qid: 109,
              code: 'AO04',
              sortOrder: 3,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 99,
                  aid: 99,
                  answer: 'Answer 4',
                  language: 'en',
                },
              },
            },
          ],
        },
        {
          qid: 110,
          parentQid: 0,
          sid: 596477,
          type: 'L',
          title: 'G04Q17',
          preg: null,
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 5,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: 'bootstrap_buttons',
          moduleName: '',
          gid: 7,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 114,
              qid: 110,
              question: 'Single choice buttons',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            array_filter_exclude: {
              'qid': '110',
              '': {
                qaid: '678',
                value: '',
              },
            },
            array_filter: {
              'qid': '110',
              '': {
                qaid: '679',
                value: '',
              },
            },
            other_comment_mandatory: {
              'qid': '110',
              '': {
                qaid: '680',
                value: '0',
              },
            },
            other_numbers_only: {
              'qid': '110',
              '': {
                qaid: '681',
                value: '0',
              },
            },
            array_filter_style: {
              'qid': '110',
              '': {
                qaid: '682',
                value: '0',
              },
            },
            random_group: {
              'qid': '110',
              '': {
                qaid: '683',
                value: '',
              },
            },
            em_validation_q: {
              'qid': '110',
              '': {
                qaid: '684',
                value: '',
              },
            },
            em_validation_q_tip: {
              qid: '110',
              en: {
                qaid: '685',
                value: '',
              },
            },
            answer_order: {
              'qid': '110',
              '': {
                qaid: '686',
                value: 'normal',
              },
            },
            hide_tip: {
              'qid': '110',
              '': {
                qaid: '687',
                value: '0',
              },
            },
            other_replace_text: {
              qid: '110',
              en: {
                qaid: '688',
                value: '',
              },
            },
            hidden: {
              'qid': '110',
              '': {
                qaid: '689',
                value: '0',
              },
            },
            cssclass: {
              'qid': '110',
              '': {
                qaid: '690',
                value: '',
              },
            },
            other_position: {
              'qid': '110',
              '': {
                qaid: '691',
                value: 'default',
              },
            },
            other_position_code: {
              'qid': '110',
              '': {
                qaid: '692',
                value: '',
              },
            },
            printable_help: {
              qid: '110',
              en: {
                qaid: '693',
                value: '',
              },
            },
            page_break: {
              'qid': '110',
              '': {
                qaid: '694',
                value: '0',
              },
            },
            scale_export: {
              'qid': '110',
              '': {
                qaid: '695',
                value: '0',
              },
            },
            time_limit: {
              'qid': '110',
              '': {
                qaid: '696',
                value: '',
              },
            },
            time_limit_action: {
              'qid': '110',
              '': {
                qaid: '697',
                value: '1',
              },
            },
            time_limit_disable_next: {
              'qid': '110',
              '': {
                qaid: '698',
                value: '0',
              },
            },
            time_limit_disable_prev: {
              'qid': '110',
              '': {
                qaid: '699',
                value: '0',
              },
            },
            time_limit_countdown_message: {
              qid: '110',
              en: {
                qaid: '700',
                value: '',
              },
            },
            time_limit_timer_style: {
              'qid': '110',
              '': {
                qaid: '701',
                value: '',
              },
            },
            time_limit_message_delay: {
              'qid': '110',
              '': {
                qaid: '702',
                value: '',
              },
            },
            time_limit_message: {
              qid: '110',
              en: {
                qaid: '703',
                value: '',
              },
            },
            time_limit_message_style: {
              'qid': '110',
              '': {
                qaid: '704',
                value: '',
              },
            },
            time_limit_warning: {
              'qid': '110',
              '': {
                qaid: '705',
                value: '',
              },
            },
            time_limit_warning_display_time: {
              'qid': '110',
              '': {
                qaid: '706',
                value: '',
              },
            },
            time_limit_warning_message: {
              qid: '110',
              en: {
                qaid: '707',
                value: '',
              },
            },
            time_limit_warning_style: {
              'qid': '110',
              '': {
                qaid: '708',
                value: '',
              },
            },
            time_limit_warning_2: {
              'qid': '110',
              '': {
                qaid: '709',
                value: '',
              },
            },
            time_limit_warning_2_display_time: {
              'qid': '110',
              '': {
                qaid: '710',
                value: '',
              },
            },
            time_limit_warning_2_message: {
              qid: '110',
              en: {
                qaid: '711',
                value: '',
              },
            },
            time_limit_warning_2_style: {
              'qid': '110',
              '': {
                qaid: '712',
                value: '',
              },
            },
            public_statistics: {
              'qid': '110',
              '': {
                qaid: '713',
                value: '0',
              },
            },
            statistics_showgraph: {
              'qid': '110',
              '': {
                qaid: '714',
                value: '1',
              },
            },
            statistics_graphtype: {
              'qid': '110',
              '': {
                qaid: '715',
                value: '0',
              },
            },
            button_size: {
              'qid': '110',
              '': {
                qaid: '716',
                value: 'default',
              },
            },
            save_as_default: {
              'qid': '110',
              '': {
                qaid: '717',
                value: 'N',
              },
            },
          },
          answers: [
            {
              aid: 104,
              qid: 110,
              code: 'AO01',
              sortOrder: 0,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 104,
                  aid: 104,
                  answer: 'Answer 1',
                  language: 'en',
                },
              },
            },
            {
              aid: 105,
              qid: 110,
              code: 'AO02',
              sortOrder: 1,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 105,
                  aid: 105,
                  answer: 'Answer 2',
                  language: 'en',
                },
              },
            },
            {
              aid: 106,
              qid: 110,
              code: 'AO03',
              sortOrder: 2,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 106,
                  aid: 106,
                  answer: 'Answer 3',
                  language: 'en',
                },
              },
            },
            {
              aid: 107,
              qid: 110,
              code: 'AO04',
              sortOrder: 3,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  id: 107,
                  aid: 107,
                  answer: 'Answer 4',
                  language: 'en',
                },
              },
            },
          ],
        },
      ],
    },
    {
      gid: 8,
      sid: 596477,
      sortOrder: 5,
      randomizationGroup: '',
      gRelevance: '',
      l10ns: {
        en: {
          id: 8,
          gid: 8,
          groupName: 'Mask questions',
          description: '',
          language: 'en',
        },
      },
      questions: [
        {
          qid: 111,
          parentQid: 0,
          sid: 596477,
          type: 'K',
          title: 'G05Q18',
          preg: '',
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 1,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: 'multiplenumeric',
          moduleName: '',
          gid: 8,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 116,
              qid: 111,
              question: '<h3>Multiple numerical input</h3>\r\n',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            min_answers: {
              'qid': '111',
              '': {
                qaid: '718',
                value: '',
              },
            },
            max_answers: {
              'qid': '111',
              '': {
                qaid: '719',
                value: '',
              },
            },
            array_filter: {
              'qid': '111',
              '': {
                qaid: '720',
                value: '',
              },
            },
            array_filter_exclude: {
              'qid': '111',
              '': {
                qaid: '721',
                value: '',
              },
            },
            array_filter_style: {
              'qid': '111',
              '': {
                qaid: '722',
                value: '0',
              },
            },
            exclude_all_others: {
              'qid': '111',
              '': {
                qaid: '723',
                value: '',
              },
            },
            random_group: {
              'qid': '111',
              '': {
                qaid: '724',
                value: '',
              },
            },
            em_validation_q: {
              'qid': '111',
              '': {
                qaid: '725',
                value: '',
              },
            },
            em_validation_q_tip: {
              qid: '111',
              en: {
                qaid: '726',
                value: '',
              },
            },
            em_validation_sq: {
              'qid': '111',
              '': {
                qaid: '727',
                value: '',
              },
            },
            em_validation_sq_tip: {
              qid: '111',
              en: {
                qaid: '728',
                value: '',
              },
            },
            prefix: {
              qid: '111',
              en: {
                qaid: '729',
                value: '',
              },
            },
            suffix: {
              qid: '111',
              en: {
                qaid: '730',
                value: '',
              },
            },
            label_input_columns: {
              'qid': '111',
              '': {
                qaid: '731',
                value: '',
              },
            },
            random_order: {
              'qid': '111',
              '': {
                qaid: '732',
                value: '0',
              },
            },
            text_input_width: {
              'qid': '111',
              '': {
                qaid: '733',
                value: '',
              },
            },
            hide_tip: {
              'qid': '111',
              '': {
                qaid: '734',
                value: '0',
              },
            },
            input_size: {
              'qid': '111',
              '': {
                qaid: '735',
                value: '',
              },
            },
            hidden: {
              'qid': '111',
              '': {
                qaid: '736',
                value: '0',
              },
            },
            cssclass: {
              'qid': '111',
              '': {
                qaid: '737',
                value: '',
              },
            },
            printable_help: {
              qid: '111',
              en: {
                qaid: '738',
                value: '',
              },
            },
            value_range_allows_missing: {
              'qid': '111',
              '': {
                qaid: '739',
                value: '1',
              },
            },
            num_value_int_only: {
              'qid': '111',
              '': {
                qaid: '740',
                value: '0',
              },
            },
            min_num_value_n: {
              'qid': '111',
              '': {
                qaid: '741',
                value: '',
              },
            },
            min_num_value: {
              'qid': '111',
              '': {
                qaid: '742',
                value: '',
              },
            },
            maximum_chars: {
              'qid': '111',
              '': {
                qaid: '743',
                value: '',
              },
            },
            equals_num_value: {
              'qid': '111',
              '': {
                qaid: '744',
                value: '',
              },
            },
            max_num_value: {
              'qid': '111',
              '': {
                qaid: '745',
                value: '',
              },
            },
            max_num_value_n: {
              'qid': '111',
              '': {
                qaid: '746',
                value: '',
              },
            },
            page_break: {
              'qid': '111',
              '': {
                qaid: '747',
                value: '0',
              },
            },
            public_statistics: {
              'qid': '111',
              '': {
                qaid: '748',
                value: '0',
              },
            },
            statistics_showgraph: {
              'qid': '111',
              '': {
                qaid: '749',
                value: '1',
              },
            },
            statistics_graphtype: {
              'qid': '111',
              '': {
                qaid: '750',
                value: '0',
              },
            },
            slider_layout: {
              'qid': '111',
              '': {
                qaid: '751',
                value: '0',
              },
            },
            slider_orientation: {
              'qid': '111',
              '': {
                qaid: '752',
                value: '0',
              },
            },
            slider_handle: {
              'qid': '111',
              '': {
                qaid: '753',
                value: '0',
              },
            },
            slider_custom_handle: {
              'qid': '111',
              '': {
                qaid: '754',
                value: 'f1ae',
              },
            },
            slider_min: {
              'qid': '111',
              '': {
                qaid: '755',
                value: '',
              },
            },
            slider_max: {
              'qid': '111',
              '': {
                qaid: '756',
                value: '',
              },
            },
            slider_accuracy: {
              'qid': '111',
              '': {
                qaid: '757',
                value: '',
              },
            },
            slider_middlestart: {
              'qid': '111',
              '': {
                qaid: '758',
                value: '0',
              },
            },
            slider_reversed: {
              'qid': '111',
              '': {
                qaid: '759',
                value: '0',
              },
            },
            slider_reset: {
              'qid': '111',
              '': {
                qaid: '760',
                value: '0',
              },
            },
            slider_default: {
              'qid': '111',
              '': {
                qaid: '761',
                value: '',
              },
            },
            slider_default_set: {
              'qid': '111',
              '': {
                qaid: '762',
                value: '1',
              },
            },
            slider_showminmax: {
              'qid': '111',
              '': {
                qaid: '763',
                value: '0',
              },
            },
            slider_separator: {
              'qid': '111',
              '': {
                qaid: '764',
                value: '|',
              },
            },
            save_as_default: {
              'qid': '111',
              '': {
                qaid: '765',
                value: 'N',
              },
            },
          },
          subquestions: [
            {
              qid: 112,
              parentQid: 111,
              sid: 596477,
              type: 'T',
              title: 'SQ001',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 0,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 8,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 117,
                  qid: 112,
                  question: 'Subquestion 1',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 113,
              parentQid: 111,
              sid: 596477,
              type: 'T',
              title: 'SQ003',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 1,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 8,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 118,
                  qid: 113,
                  question: 'Subquestion 2',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
            {
              qid: 114,
              parentQid: 111,
              sid: 596477,
              type: 'T',
              title: 'SQ002',
              preg: null,
              other: false,
              mandatory: null,
              encrypted: false,
              sortOrder: 2,
              scaleId: 0,
              sameDefault: null,
              questionThemeName: 'longfreetext',
              moduleName: null,
              gid: 8,
              relevance: '1',
              sameScript: null,
              l10ns: {
                en: {
                  id: 119,
                  qid: 114,
                  question: 'Subquestion 3',
                  help: null,
                  script: null,
                  language: 'en',
                },
              },
              attributes: [],
              answers: [],
            },
          ],
          answers: [],
        },
        {
          qid: 115,
          parentQid: 0,
          sid: 596477,
          type: '*',
          title: 'G01Q19',
          preg: null,
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 2,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: 'equation',
          moduleName: '',
          gid: 8,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 120,
              qid: 115,
              question: 'Equation',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            equation: {
              'qid': '115',
              '': {
                qaid: '766',
                value: '',
              },
            },
            hidden: {
              'qid': '115',
              '': {
                qaid: '767',
                value: '0',
              },
            },
            cssclass: {
              'qid': '115',
              '': {
                qaid: '768',
                value: '',
              },
            },
            printable_help: {
              qid: '115',
              en: {
                qaid: '769',
                value: '',
              },
            },
            page_break: {
              'qid': '115',
              '': {
                qaid: '770',
                value: '0',
              },
            },
            scale_export: {
              'qid': '115',
              '': {
                qaid: '771',
                value: '0',
              },
            },
            numbers_only: {
              'qid': '115',
              '': {
                qaid: '772',
                value: '0',
              },
            },
            public_statistics: {
              'qid': '115',
              '': {
                qaid: '773',
                value: '0',
              },
            },
            statistics_showgraph: {
              'qid': '115',
              '': {
                qaid: '774',
                value: '1',
              },
            },
            statistics_graphtype: {
              'qid': '115',
              '': {
                qaid: '775',
                value: '0',
              },
            },
            save_as_default: {
              'qid': '115',
              '': {
                qaid: '776',
                value: 'N',
              },
            },
          },
          answers: [],
        },
        {
          qid: 116,
          parentQid: 0,
          sid: 596477,
          type: '|',
          title: 'G01Q20',
          preg: null,
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 3,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: 'file_upload',
          moduleName: '',
          gid: 8,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 121,
              qid: 116,
              question: 'File upload',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            random_group: {
              'qid': '116',
              '': {
                qaid: '777',
                value: '',
              },
            },
            hide_tip: {
              'qid': '116',
              '': {
                qaid: '778',
                value: '0',
              },
            },
            hidden: {
              'qid': '116',
              '': {
                qaid: '779',
                value: '0',
              },
            },
            cssclass: {
              'qid': '116',
              '': {
                qaid: '780',
                value: '',
              },
            },
            page_break: {
              'qid': '116',
              '': {
                qaid: '781',
                value: '0',
              },
            },
            max_filesize: {
              'qid': '116',
              '': {
                qaid: '782',
                value: '10240',
              },
            },
            max_num_of_files: {
              'qid': '116',
              '': {
                qaid: '783',
                value: '1',
              },
            },
            min_num_of_files: {
              'qid': '116',
              '': {
                qaid: '784',
                value: '0',
              },
            },
            allowed_filetypes: {
              'qid': '116',
              '': {
                qaid: '785',
                value: 'png, gif, doc, odt, jpg, jpeg, pdf, png, heic',
              },
            },
            statistics_showgraph: {
              'qid': '116',
              '': {
                qaid: '786',
                value: '1',
              },
            },
            statistics_graphtype: {
              'qid': '116',
              '': {
                qaid: '787',
                value: '0',
              },
            },
            show_title: {
              'qid': '116',
              '': {
                qaid: '788',
                value: '1',
              },
            },
            show_comment: {
              'qid': '116',
              '': {
                qaid: '789',
                value: '1',
              },
            },
            save_as_default: {
              'qid': '116',
              '': {
                qaid: '790',
                value: 'N',
              },
            },
          },
          answers: [],
        },
        {
          qid: 117,
          parentQid: 0,
          sid: 596477,
          type: 'G',
          title: 'G05Q21',
          preg: null,
          other: false,
          mandatory: false,
          encrypted: false,
          sortOrder: 4,
          scaleId: 0,
          sameDefault: null,
          questionThemeName: 'gender',
          moduleName: '',
          gid: 8,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              id: 122,
              qid: 117,
              question: 'Gender',
              help: '',
              script: '',
              language: 'en',
            },
          },
          attributes: {
            random_group: {
              'qid': '117',
              '': {
                qaid: '791',
                value: '',
              },
            },
            display_type: {
              'qid': '117',
              '': {
                qaid: '792',
                value: '0',
              },
            },
            hide_tip: {
              'qid': '117',
              '': {
                qaid: '793',
                value: '0',
              },
            },
            hidden: {
              'qid': '117',
              '': {
                qaid: '794',
                value: '0',
              },
            },
            cssclass: {
              'qid': '117',
              '': {
                qaid: '795',
                value: '',
              },
            },
            printable_help: {
              qid: '117',
              en: {
                qaid: '796',
                value: '',
              },
            },
            page_break: {
              'qid': '117',
              '': {
                qaid: '797',
                value: '0',
              },
            },
            scale_export: {
              'qid': '117',
              '': {
                qaid: '798',
                value: '0',
              },
            },
            public_statistics: {
              'qid': '117',
              '': {
                qaid: '799',
                value: '0',
              },
            },
            statistics_showgraph: {
              'qid': '117',
              '': {
                qaid: '800',
                value: '1',
              },
            },
            statistics_graphtype: {
              'qid': '117',
              '': {
                qaid: '801',
                value: '0',
              },
            },
            save_as_default: {
              'qid': '117',
              '': {
                qaid: '802',
                value: 'N',
              },
            },
          },
          answers: [],
        },
      ],
    },
  ],
}
