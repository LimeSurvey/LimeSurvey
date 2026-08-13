export const IMPORT_SURVEY_MAX_FILE_SIZE = 40 * 1024 * 1024

export const IMPORT_SURVEY_FILE_TYPES = {
  'application/octet-stream': ['.lsa', '.lss'],
  'text/plain': ['.txt', '.tsv'],
  'text/tab-separated-values': ['.tsv'],
  'text/xml': ['.lss'],
}

export const IMPORT_SURVEY_GROUP_STRATEGIES = {
  DEFAULT: 'default',
  FROM_SURVEY: 'from_survey',
}

export const getImportSurveyGroupOptions = () => [
  {
    value: IMPORT_SURVEY_GROUP_STRATEGIES.DEFAULT,
    label: t('Default survey group') || 'Default survey group',
  },
  {
    value: IMPORT_SURVEY_GROUP_STRATEGIES.FROM_SURVEY,
    label:
      t('Keep the survey group from the imported file') ||
      'Keep the survey group from the imported file',
  },
]
