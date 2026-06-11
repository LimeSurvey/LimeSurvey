import React, { useEffect, useState } from 'react'

import { ToggleButtons } from 'components'
import { useStatistics } from 'hooks'

import {
  getDataWithPercentages,
  statisticsGraphs,
  VALUE_TYPE,
} from './ChartsUtils'
import { StatisticsContainer } from '../Statistics/Components/StatisticsContainer.js'

const valueTypeOptions = [
  { name: t('Percentage'), value: VALUE_TYPE.PERCENTAGE },
  { name: t('Response count'), value: VALUE_TYPE.COUNT },
]

export const ResponsesStatistics = ({
  surveyId,
  filters = {},
  isRightBarOpen = false,
}) => {
  const { statistics, isFetching } = useStatistics(surveyId, filters)
  const [selectedCharts, setSelectedCharts] = useState([])
  const [formattedStatistics, setFormattedStatistics] = useState(null)
  const [valueType, setValueType] = useState(VALUE_TYPE.PERCENTAGE)

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

  return (
    <>
      <div className="responses-statistics-toolbar d-flex justify-content-end mb-3">
        <ToggleButtons
          id="statistics-value-type"
          value={valueType}
          onChange={setValueType}
          toggleOptions={valueTypeOptions}
          theme="lime"
        />
      </div>
      <StatisticsContainer
        statistics={statistics}
        surveyId={surveyId}
        valueType={valueType}
      />
    </>
  )
}
