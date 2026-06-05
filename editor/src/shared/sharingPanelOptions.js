import { getSiteUrl, SURVEY_MENU_TITLES } from 'helpers'

import { getSharingPanels } from './getSharingPanels'

export const sharingOptions = (surveyId) => [
  {
    labelEditor: t('Sharing overview'),
    menu: SURVEY_MENU_TITLES.sharingOverview,
  },
  {
    labelEditor: t('Participant lists'),
    menu: SURVEY_MENU_TITLES.participantsList,
    redirect: getSiteUrl(`/admin/tokens/sa/index/surveyid/${surveyId}`),
  },
]

export const getSharingPanelOptions = (surveyId) => {
  const sharingPanels = getSharingPanels()
  return {
    [sharingPanels.sharing?.panel]: sharingOptions(surveyId),
  }
}
