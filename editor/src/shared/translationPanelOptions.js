import { getSiteUrl, SURVEY_MENU_TITLES } from 'helpers'

export const translationsPanelOptions = (surveyId) => [
  {
    labelEditor: t('Quick translations'),
    menu: SURVEY_MENU_TITLES.quickTranslations,
    redirect: getSiteUrl(`/admin/quickTranslation/index?surveyid=${surveyId}`),
  },
]
