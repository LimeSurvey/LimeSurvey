import { SurveyStructureIcon } from 'components/icons'
import { TAB_KEYS } from '../utils'

export const getResponsesPanels = () => {
  return {
    results: {
      label: t('Results'),
      panel: 'results',
      icon: SurveyStructureIcon,
      defaultMenu: panelItemsKeys.overview,
    },
  }
}

export const panelItemsKeys = {
  overview: TAB_KEYS.OVERVIEW,
  list: TAB_KEYS.RESPONSES,
  statistics: TAB_KEYS.STATISTICS,
}

export const panelOptions = () => {
  return {
    [getResponsesPanels().results.panel]: [
      {
        label: t('Results overview'),
        labelEditor: t('Results overview'),
        menu: panelItemsKeys.overview,
      },
      {
        label: t('Responses list'),
        labelEditor: t('Responses list'),
        menu: TAB_KEYS.RESPONSES,
        activeMenus: [TAB_KEYS.RESPONSES, TAB_KEYS.STATISTICS],
      },
    ],
  }
}
