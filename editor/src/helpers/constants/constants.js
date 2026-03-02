export const MINIMUM_INPUT_WIDTH_PERCENT = 15
export const FOCUS_ANIMATION_DURATION_IN_MS = 1000

export const SCALE_1 = 0
export const SCALE_2 = 1

export const themeOptions = [
  // {
  //   label: 'BootsWatch',
  //   value: 'bootswatch',
  // },
  // {
  //   label: 'Fruity',
  //   value: 'fruity',
  // },
  {
    label: 'Fruity_twentythree',
    value: 'fruity_twentythree',
  },
  // {
  //   label: 'Vanilla',
  //   value: 'vanilla',
  // },
]

export const NEW_OBJECT_ID_PREFIX = 'temp__'

export const SOFT_MANDATORY = 'S'

export const ignoreUpdate = '_________________________ignore_update________'

export const STATES = {
  ATTRIBUTE_DESCRIPTIONS: 'attribute_descriptions',
  QUESTION_SETTINGS_OPTIONS: 'question_settings_options',
  IS_SURVEY_ACTIVE: 'is_survey_active',
  CODE_TO_QUESTION: 'code_to_question',
  SEARCHED_TERM: 'searched_term',
  IS_EDITOR_STRUCTURE_PANEL_OPEN: 'is_editor_structure_panel_open',
  IS_PREVIEW_MODE: 'is_preview_mode_active',
  IS_SURVEY_SETTINGS_OPEN: 'is_question_settings_open',
  IS_ADDING_QUESTION_OR_GROUP: 'is_adding_questions_or_group',
  CLICKED_QUESTION_GROUP_INDEX: 'clicked_question_group_index',
  SURVEY_ID: 'survey_id',
  SAVE_STATE: 'save_state',
  FOCUSED_ENTITY: 'focused_entity',
  SURVEY_SERVICE: 'survey_service',
  SURVEY: 'survey',
  USER_DETAIL: 'user_detail',
  AUTH: 'auth',
  BUFFER: 'buffer',
  BUFFER_HASH: 'buffer_hash',
  ERRORS: 'errors',
  ERROR_MESSAGES: 'errors_messages',
  USER_SETTINGS: 'user_settings',
  IS_PATCH_SURVEY_RUNNING: 'is_patch_survey_running',
  NUMBER_OF_QUESTIONS: 'number_of_questions',
  HAS_SURVEY_UPDATE_PERMISSION: 'has_survey_update_permission',
  IS_SURVEY_SHARE_MENU_OPEN: 'is_survey_share_menu_open',
  SITE_SETTINGS: 'site_settings',
  SURVEY_GROUPS: 'survey_groups',
  HELPER_SETTINGS: 'helper_settings',
  SURVEY_HASH: 'survey_hash',
  SURVEY_REFRESH_REQUIRED: 'survey_refresh_required',
  ALL_AVAILABLE_LANGUAGES: 'all_available_languages',
  SAVE_STATUS: 'save_status',
  SURVEY_PUBLISH_RUNNING: 'survey_publish_running',
  ACTIVE_LANGUAGE: 'active_language',
  SURVEY_ARCHIVES: 'survey_archived_responses',
  OPERATION_FINISH_SUBSCRIPTIONS: 'operation_finish_subscriptions',
  SURVEY_QUESTIONS_FIELDNAME: 'survey_questions_fieldname',
  START_EDITOR_TUTORIAL: 'start_editor_tutorial',
  SURVEY_RESPONSES: 'survey_responses',
  SURVEY_STATISTICS: 'survey_statistics',
  EDITOR_HELP_APPEARED: 'editor_help_appeared',
  USER_PERMISSIONS: 'user_permissions',
  HAS_RESPONSES_READ_PERMISSION: 'has_responses_read_permission',
  HAS_RESPONSES_UPDATE_PERMISSION: 'has_responses_update_permission',
  SURVEY_REQUEST_UTC_TIMESTAMP: 'survey_request_utc_timestamp',
}

export const TUTORIALS = {
  EDITOR_TUTORIAL: 'editorTutorial',
}

export const SURVEY_MENU_TITLES = {
  // overview: 'overview',
  generalSettings: 'generalsettings',
  surveyTexts: 'surveytexts',
  dataSecurity: 'datasecurity',
  themeOptions: 'theme_options',
  presentation: 'presentation',
  tokens: 'tokens',
  notification: 'notification',
  publication: 'publication',
  surveyPermissions: 'surveypermissions',
  listQuestions: 'listQuestions',
  participants: 'participants',
  emailTemplates: 'emailtemplates',
  failedEmail: 'failedemail',
  quotas: 'quotas',
  assessments: 'assessments',
  panelIntegration: 'panelintegration',
  responses: 'responses',
  statistics: 'statistics',
  resources: 'resources',
  plugins: 'plugins',
  activateSurvey: 'activateSurvey',
  deactivateSurvey: 'deactivateSurvey',
  testSurvey: 'testSurvey',
  surveyLogicFile: 'surveyLogicFile',
  cpdb: 'cpdb',
  // sharing
  sharingOverview: 'sharingOverview',
  participantsList: 'participantsList',
}

export const PAGES = {
  RESPONSES: 'responses',
  EDITOR: 'survey',
  SHARE: 'sharing',
}

export const RESPONSES_TITLES = {
  list: '',
}

export const URLS = {
  ADMIN: '/admin',
  SURVEY_OVERVIEW: '/surveyAdministration/view/',
}

export const placeholderStandardFields = {
  EMAIL: 'Email address participant',
  FIRSTNAME: 'First name participant',
  LASTNAME: 'Last name participant',
  QID: 'Question ID',
  GID: 'Question ID group',
  SID: 'Survey ID',
  EXPIRY: 'Survey expiration date',
}

export const IMAGE_PREVIEW_HEIGHT = '160px'
export const FILE_UPLOAD_MAX_SIZE = 1024 * 1024 * 50
export const FILE_UPLOAD_MAX_SIZE_STRING = '40mb'

export const RESET_TYPES = {
  ThemeOptions: 'themeOptions',
}

export const QUESTION_RELEVANCE_DEFAULT_VALUE = '1'

export const ACCESS_MODES = {
  OPEN_TO_ALL: 'O', // it's for filling the survey anonymously
  CLOSED: 'C', // access is expected to happen with tokens only
}

// Prefix used for React Query cache keys when fetching archived survey data.
// Example: 'survey_archived_tokens' for archived data related to the 'tokens' base table.
// This helps differentiate archived data by base table (e.g., 'tokens', 'survey',questions , etc.).
export const SURVEY_ARCHIVES_QUERY_KEY_PREFIX = 'survey_archived_'
export const ARCHIVE_BASE_TABLE_TOKENS = 'tokens'

export const SURVEY_NOT_MODIFIED = 'not changed'
