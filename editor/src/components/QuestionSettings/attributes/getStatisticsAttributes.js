import { ToggleButtons, Select } from 'components/UIComponents'
import { getOnOffOptions } from 'helpers/options'
import { statisticsGraphs } from '../../../pages/Responses/components/ResponsesStatistics/ChartsUtils'

export const getStatisticsAttributes = () => ({
  SHOW_IN_STATISTICS: {
    component: Select,
    attributePath: 'attributes.statistics_graphtype',
    props: {
      labelText: t('Show in statistics'),
      dataTestId: 'display-chart',
      options: [
        {
          label: t("Don't show"),
          value: statisticsGraphs.DONT_SHOW,
        },
        {
          label: t('Bar chart'),
          value: statisticsGraphs.BAR_CHART,
        },
        {
          label: t('Pie chart'),
          value: statisticsGraphs.PIE_CHART,
        },
        {
          label: t('Radar'),
          value: statisticsGraphs.RADAR,
        },
        {
          label: t('Line'),
          value: statisticsGraphs.LINE,
        },
        {
          label: t('Polar area'),
          value: statisticsGraphs.POLAR_AREA,
        },
      ],
      value: '0',
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
})
