import { SURVEY_MENU_TITLES } from '@core/helpers'
import { getSiteUrl } from 'helpers'

export const translationsPanelOptions = (surveyId) => [
  {
    labelEditor: t('Quick translations'),
    menu: SURVEY_MENU_TITLES.quickTranslations,
    redirect: getSiteUrl(`/quickTranslation/index?surveyid=${surveyId}`),
  },
]
