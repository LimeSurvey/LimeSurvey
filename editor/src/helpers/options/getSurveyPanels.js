import { getSurveyPanelConfig } from 'shared/getSurveyPanelConfig'

export const getSurveyPanels = (survey = {}) => {
  return getSurveyPanelConfig(survey)
}

export const getCurrentLayout = (panel) => {
  const settingsPanels = [
    getSurveyPanels().settings.panel,
    getSurveyPanels().presentation.panel,
  ]

  if (settingsPanels.includes(panel)) {
    return LAYOUT_TYPES.Settings
  }

  if (panel === getSurveyPanels().translations.panel) {
    return LAYOUT_TYPES.Translations
  }

  return LAYOUT_TYPES.Survey
}

export const LAYOUT_TYPES = {
  Survey: 'survey',
  Settings: 'settings',
  Translations: 'translations',
}
