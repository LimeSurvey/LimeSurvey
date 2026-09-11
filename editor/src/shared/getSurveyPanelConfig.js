import {
  SurveyMenuIcon,
  SurveyPermissionsIcon,
  SurveyPresentationIcon,
  SurveySettingsIcon,
  SurveyStructureIcon,
  SurveyTranslationIcon,
} from 'components/icons'
import { getSiteUrl, SURVEY_MENU_TITLES } from 'helpers'

export const getSurveyPanelConfig = () => {
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
    translations: {
      label: t('Translations'),
      panel: 'translations',
      defaultMenu: SURVEY_MENU_TITLES.quickTranslations,
      disabled: (survey) => survey?.languages?.length <= 1,
      disabledMessage:
        t('Translations') +
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
