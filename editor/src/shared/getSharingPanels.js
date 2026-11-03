import { SurveyStructureIcon } from 'components/icons'
import { SURVEY_MENU_TITLES } from 'helpers'

export const getSharingPanels = () => {
  return {
    sharing: {
      label: t('Share survey'),
      panel: 'sharing',
      icon: SurveyStructureIcon,
      defaultMenu: SURVEY_MENU_TITLES.sharingOverview,
    },
  }
}
