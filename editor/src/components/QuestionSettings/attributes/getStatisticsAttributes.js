import { ToggleButtons, Select } from 'components/UIComponents'
import { getOnOffOptions } from 'helpers/options'
import {
  getStatisticsGraphsForType,
  statisticsGraphs,
} from '../../../pages/Responses/components/ResponsesStatistics/ChartsUtils'

const getGraphOptions = () => [
  { label: t('Bar chart'), value: statisticsGraphs.BAR_CHART },
  { label: t('Pie chart'), value: statisticsGraphs.PIE_CHART },
  { label: t('Radar chart'), value: statisticsGraphs.RADAR },
  { label: t('Line chart'), value: statisticsGraphs.LINE },
  { label: t('Polar chart'), value: statisticsGraphs.POLAR_AREA },
  { label: t('Doughnut chart'), value: statisticsGraphs.DOUGHNUT_CHART },
  { label: t('Stacked bar chart'), value: statisticsGraphs.STACKED_BAR },
  { label: t('Grouped bar chart'), value: statisticsGraphs.GROUPED_BAR },
  { label: t('Grid'), value: statisticsGraphs.GRID },
  { label: t('Table'), value: statisticsGraphs.TABLE },
  { label: t('Comments'), value: statisticsGraphs.COMMENTS },
]

/**
 * Returns the statistics attribute definitions.
 * @returns {Object<string, Object>} Attribute definitions.
 */
export const getStatisticsAttributes = () => ({
  SHOW_IN_STATISTICS: {
    component: Select,
    attributePath: 'attributes.statistics_graphtype',
    getOptions: ({ question }) => {
      const graphs = getStatisticsGraphsForType(question?.type)
      return [
        { label: t('Default'), value: '' },
        { label: t("Don't show"), value: String(statisticsGraphs.DONT_SHOW) },
        ...getGraphOptions()
          .filter(({ value }) => graphs.includes(value))
          .map(({ label, value }) => ({ label, value: String(value) })),
      ]
    },
    props: {
      labelText: t('Show in statistics'),
      dataTestId: 'display-chart',
      value: '',
      // Saved charts the type can't show fall back to Default, so show that.
      defaultValue: '',
    },
  },
  SHOW_IN_PUBLIC_STATISTICS: {
    component: ToggleButtons,
    attributePath: 'attributes.public_statistics',
    props: {
      labelText: t('Show in public statistics'),
      id: 'show-in-pubic-statistics',
      toggleOptions: getOnOffOptions(),
      defaultValue: '0',
    },
  },
  DISPLAY_MAP: {
    component: ToggleButtons,
    attributePath: 'attributes.statistics_showmap',
    props: {
      labelText: t('Display map'),
      id: 'display-map',
      toggleOptions: getOnOffOptions(),
      defaultValue: '1',
    },
  },
})
