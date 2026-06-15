import { useState } from 'react'
import classNames from 'classnames'
import { Card } from 'react-bootstrap'

import { Collapsible, ToggleButtons } from 'components'
import { ReactComponent as PencilIcon } from 'assets/icons/pencil-icon-white.svg'

import { ChartHeader } from './Statistics/Components/ChartHeader.js'
import { StatisticsTable } from './Statistics/Components/StatisticsTable.js'
import { BarChart, PieChart } from './ResponsesStatistics/Charts/index.js'
import { isImageTheme } from './ResponsesStatistics/ChartsUtils.js'
import { QuestionComments } from './QuestionComments.js'

const VIEW = {
  BAR_CHART: 'bar-chart',
  PIE_CHART: 'pie-chart',
  TABLE: 'table',
  COMMENTS: 'comments',
}

// Question types that store free-text comments in the response table.
const COMMENT_QUESTION_TYPES = ['O', 'P']

const viewOptions = [
  {
    icon: () => <i className="ri-bar-chart-2-line"></i>,
    value: VIEW.BAR_CHART,
  },
  {
    icon: () => <i className="ri-pie-chart-line"></i>,
    value: VIEW.PIE_CHART,
  },
  {
    icon: () => <i className="ri-table-line"></i>,
    value: VIEW.TABLE,
  },
]

const commentsViewOption = {
  icon: () => <PencilIcon className="comments-toggle-icon" />,
  value: VIEW.COMMENTS,
}

const HIDDEN_CHARTS_STORAGE_KEY = 'responses-statistics-hidden-charts'

const readHiddenCharts = () => {
  try {
    return JSON.parse(localStorage.getItem(HIDDEN_CHARTS_STORAGE_KEY)) || {}
  } catch {
    return {}
  }
}

const writeHiddenCharts = (hidden) => {
  try {
    localStorage.setItem(HIDDEN_CHARTS_STORAGE_KEY, JSON.stringify(hidden))
  } catch {
    // ignore quota / disabled storage
  }
}

const getStorageKey = (surveyId, chartId, index) =>
  `${surveyId ?? 'unknown'}:${chartId ?? `index-${index}`}`

export const ChartRendererV2 = ({
  data,
  index = 0,
  surveyId,
  chartId,
  question = {},
  valueType,
}) => {
  const [view, setView] = useState(VIEW.BAR_CHART)
  const isImage = isImageTheme(question?.theme)
  const hasComments = COMMENT_QUESTION_TYPES.includes(question?.type)
  const toggleOptions = hasComments
    ? [...viewOptions, commentsViewOption]
    : viewOptions
  const storageKey = getStorageKey(surveyId, chartId, index)
  const [isHidden, setIsHidden] = useState(
    () => !!readHiddenCharts()[storageKey]
  )

  const setHidden = (hidden) => {
    const hiddenCharts = readHiddenCharts()
    if (hidden) {
      hiddenCharts[storageKey] = true
    } else {
      delete hiddenCharts[storageKey]
    }
    writeHiddenCharts(hiddenCharts)
    setIsHidden(hidden)
  }

  const actions = [
    isHidden
      ? {
          label: t('Show chart'),
          icon: <i className="ri-eye-line"></i>,
          onClick: () => setHidden(false),
        }
      : {
          label: t('Hide chart'),
          icon: <i className="ri-eye-off-line"></i>,
          onClick: () => setHidden(true),
        },
  ]

  return (
    <Card
      className={classNames('responses-chart-card', {
        'responses-chart-card--hidden': isHidden,
      })}
    >
      <ChartHeader
        {...question}
        actions={actions}
      />
      <Collapsible open={!isHidden}>
        <>
          <div>
            {view === VIEW.BAR_CHART && (
              <BarChart data={data} valueType={valueType} isImage={isImage} />
            )}
            {view === VIEW.PIE_CHART && (
              <PieChart data={data} valueType={valueType} isImage={isImage} />
            )}
            {view === VIEW.TABLE && (
              <StatisticsTable
                data={data}
                valueType={valueType}
                isImage={isImage}
              />
            )}
            {view === VIEW.COMMENTS && (
              <QuestionComments
                surveyId={surveyId}
                questionCode={question?.code}
                qid={chartId}
                answerOptions={data}
              />
            )}
          </div>
          <div className="responses-chart-view-toggle">
            <ToggleButtons
              id={`chart-view-${index}`}
              value={view}
              onChange={setView}
              toggleOptions={toggleOptions}
            />
          </div>
        </>
      </Collapsible>
    </Card>
  )
}
