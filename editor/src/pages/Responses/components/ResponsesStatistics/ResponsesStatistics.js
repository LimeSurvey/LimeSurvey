import React, { useEffect, useState } from 'react'

import { useStatistics } from 'hooks'

import { getDataWithPercentages, statisticsGraphs } from './ChartsUtils'
import { ChartRenderer } from '../ChartRenderer'
import { ChartRendererV2 } from '../ChartRenderV2.js'
import { StatisticsContainer } from '../Statistics/Components/StatisticsContainer.js'

export const ResponsesStatistics = ({
  surveyId,
  filters = {},
  isRightBarOpen = false,
}) => {
  const { statistics, isFetching } = useStatistics(surveyId, filters)
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
        {isFetching && (
          <span
            style={{ width: 48, height: 48 }}
            className="loader mb-4"
          ></span>
        )}
        <h2>
          {isFetching
            ? t('Loading statistics...')
            : t(
                'No responses or compatible data available to display statistics.'
              )}
        </h2>
      </div>
    )
  }

  return <StatisticsContainer statistics={statistics} surveyId={surveyId} />
}
