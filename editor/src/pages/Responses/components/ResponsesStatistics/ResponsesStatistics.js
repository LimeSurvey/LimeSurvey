import React, { useEffect, useState } from 'react'

import { useStatistics } from 'hooks'

import { getDataWithPercentages, statisticsGraphs } from './ChartsUtils'
import { ChartRenderer } from '../ChartRenderer'

export const ResponsesStatistics = ({
  surveyId,
  filters = {},
  isRightBarOpen = false,
}) => {
  const { statistics } = useStatistics(surveyId, filters)
  const [selectedCharts, setSelectedCharts] = useState([])
  const [formattedStatistics, setFormattedStatistics] = useState(null)

  useEffect(() => {
    const formattedStatistics = []
    if (!statistics?.length) {
      return
    }

    const questionsChartTypes = statistics.map((statisticsData) => {
      const questionAttributes = statisticsData?.meta?.question?.attributes
      const dataFormatted = getDataWithPercentages(statisticsData)
      formattedStatistics.push(dataFormatted)

      if (!questionAttributes) {
        return statisticsGraphs.DONT_SHOW
      }

      return (
        // default to bar chart if no graph type is set
        // chart validation is done on the backend whether chart should be shown or not
        questionAttributes.statistics_graphtype || statisticsGraphs.BAR_CHART
      )
    })

    setFormattedStatistics(formattedStatistics)
    setSelectedCharts(questionsChartTypes)
  }, [statistics])

  if (!statistics?.length || !formattedStatistics || !selectedCharts.length) {
    return (
      <div
        style={{ height: '100vh' }}
        className="d-flex flex-column justify-content-center align-items-center"
      >
        <span style={{ width: 48, height: 48 }} className="loader mb-4"></span>
        <h1>{t('Loading statistics...')}</h1>
      </div>
    )
  }

  return (
    <div className="responses-statistics-body">
      <div className="responses-charts row">
        {statistics.map((_, index) => {
          if (
            selectedCharts[index] === statisticsGraphs.DONT_SHOW ||
            selectedCharts[index] === undefined ||
            selectedCharts[index] === null
          ) {
            return null
          }

          return (
            <div
              className={`${!isRightBarOpen ? 'col-xxl-6' : ''} col-12 mb-2`}
              key={`responses-charts-${index}`}
            >
              <ChartRenderer
                statisticsData={formattedStatistics[index]}
                graphType={selectedCharts[index]}
                title={statistics[index]?.title}
              />
            </div>
          )
        })}
      </div>
    </div>
  )
}
