import { useMemo, useState } from 'react'
import { Card } from 'react-bootstrap'

import { Button } from 'components'

import { statisticsGraphs } from './ResponsesStatistics'
import {
  BarChart,
  DoughnutChart,
  LineChart,
  PieChart,
  PolarAreaChart,
  RadarChart,
} from './ResponsesStatistics/Charts'

export const ChartRenderer = ({
  statisticsData,
  graphType,
  onGraphChangeCallbackData = () => {},
  title,
  filterZeroValues = false,
  disablePieChart = false,
  disableDoughnutChart = false,
  disableBarChart = false,
  disableLineChart = false,
  disableRadarChart = false,
  emptyMessage = null,
}) => {
  const [chartType, setChartType] = useState(graphType)

  const data = useMemo(() => {
    const shouldFilterZeroValues =
      filterZeroValues ||
      chartType === statisticsGraphs.PIE_CHART ||
      chartType === statisticsGraphs.DOUGHNUT_CHART

    if (shouldFilterZeroValues) {
      return statisticsData.filter((item) => item.value > 0)
    }

    return statisticsData
  }, [statisticsData, filterZeroValues, chartType])

  if (
    chartType === statisticsGraphs.DONT_SHOW ||
    chartType === undefined ||
    chartType === null
  ) {
    return null
  }

  emptyMessage = emptyMessage || t('No data available')

  let ChartComponent

  switch (chartType) {
    case statisticsGraphs.BAR_CHART:
      ChartComponent = BarChart
      break
    case statisticsGraphs.PIE_CHART:
      ChartComponent = PieChart
      break
    case statisticsGraphs.POLAR_AREA:
      ChartComponent = PolarAreaChart
      break
    case statisticsGraphs.RADAR:
      ChartComponent = RadarChart
      break
    case statisticsGraphs.LINE:
      ChartComponent = LineChart
      break
    case statisticsGraphs.DOUGHNUT_CHART:
      ChartComponent = DoughnutChart
      break
    default:
      ChartComponent = BarChart
      break
  }

  const handleGraphChange = (chartType) => {
    setChartType(chartType)
    onGraphChangeCallbackData(onGraphChangeCallbackData)
  }

  return (
    <Card className="responses-chart-card py-3">
      <div className="d-flex w-100 justify-content-center">
        <h2 className="text-xl flex-grow-1 text-center font-semibold mb-4">
          {title}
        </h2>
      </div>

      <div className="chart-wrapper">
        {data.length !== 0 ? (
          <ChartComponent data={data} />
        ) : (
          <div
            className="d-flex justify-content-center align-items-center"
            style={{ height: '500px' }}
          >
            <span className="text-muted">{emptyMessage}</span>
          </div>
        )}
      </div>
      <div className="d-flex mt-3 justify-content-center gap-1">
        {!disableBarChart && (
          <Button
            onClick={() => handleGraphChange(statisticsGraphs.BAR_CHART)}
            variant="outline-dark"
            active={chartType == statisticsGraphs.BAR_CHART}
          >
            {t('Bar chart')} <i className="ri-bar-chart-fill"></i>
          </Button>
        )}
        {!disablePieChart && (
          <Button
            onClick={() => handleGraphChange(statisticsGraphs.PIE_CHART)}
            variant="outline-dark"
            active={chartType == statisticsGraphs.PIE_CHART}
          >
            {t('Pie chart')} <i className="ri-pie-chart-fill"></i>
          </Button>
        )}
        {!disableRadarChart && (
          <Button
            onClick={() => handleGraphChange(statisticsGraphs.RADAR)}
            variant="outline-dark"
            active={chartType == statisticsGraphs.RADAR}
          >
            {t('Radar chart')}
            <i className="ri-webhook-line"></i>
          </Button>
        )}
        {!disableLineChart && (
          <Button
            onClick={() => handleGraphChange(statisticsGraphs.LINE)}
            variant="outline-dark"
            active={chartType == statisticsGraphs.LINE}
          >
            {t('Line chart')} <i className="ri-line-chart-fill"></i>
          </Button>
        )}
        {!disableDoughnutChart && (
          <Button
            onClick={() => handleGraphChange(statisticsGraphs.DOUGHNUT_CHART)}
            variant="outline-dark"
            active={chartType == statisticsGraphs.DOUGHNUT_CHART}
          >
            {t('Doughnut chart')} <i className="ri-donut-chart-line"></i>
          </Button>
        )}
      </div>
    </Card>
  )
}
