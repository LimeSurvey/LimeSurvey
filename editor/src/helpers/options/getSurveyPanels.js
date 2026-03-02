import {
  SurveyMenuIcon,
  SurveyPermissionsIcon,
  SurveyPresentationIcon,
  SurveySettingsIcon,
  SurveyStructureIcon,
  SurveyTranslationIcon,
} from 'components/icons'
import getSiteUrl from '../getSiteUrl'
import { SURVEY_MENU_TITLES } from '../constants'

export const getSurveyPanels = () => {
  return {
    structure: {
      label: t('Structure'),
      panel: 'structure',
      icon: SurveyStructureIcon,
    },
    settings: {
      label: t('Settings'),
      panel: 'settings',
      defaultMenu: SURVEY_MENU_TITLES.generalSettings,
      icon: SurveySettingsIcon,
    },
    presentation: {
      label: t('Presentation'),
      panel: 'presentation',
      defaultMenu: SURVEY_MENU_TITLES.themeOptions,
      icon: SurveyPresentationIcon,
    },
    menu: {
      label: t('Menu'),
      panel: 'menu',
      icon: SurveyMenuIcon,
      getRedirectUrl: (surveyId) =>
        getSiteUrl(`/admin/tokens/sa/index/surveyid/${surveyId}`),
    },
    permissions: {
      label: t('Permissions'),
      panel: 'permissions',
      icon: SurveyPermissionsIcon,
      getRedirectUrl: (surveyId) =>
        getSiteUrl('/surveyPermissions/index?surveyid=' + surveyId),
    },
    quickTranslation: {
      label: t('Quick translation'),
      panel: 'quickTranslation',
      disabled: (survey) => survey?.languages?.length <= 1,
      disabledMessage:
        t('Quick translation') +
        ': ' +
        t(
          'Currently there are no additional languages configured for this survey.'
        ),
      icon: SurveyTranslationIcon,
      getRedirectUrl: (surveyId) =>
        getSiteUrl(`/quickTranslation/index?surveyid=${surveyId}`),
    },
  }
}

export const getCurrentLayout = (panel) => {
  const settingsPanels = [
    getSurveyPanels().settings.panel,
    getSurveyPanels().presentation.panel,
  ]

  if (settingsPanels.includes(panel)) {
    return LAYOUT_TYPES.Settings
  }

  return LAYOUT_TYPES.Survey
}

export const LAYOUT_TYPES = {
  Survey: 'survey',
  Settings: 'settings',
}
