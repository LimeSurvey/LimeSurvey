import { useState } from 'react'
import classNames from 'classnames'
import { Card } from 'react-bootstrap'

import { Collapsible, ToggleButtons } from 'components'

import { ChartHeader } from './ChartHeader.js'
import { StatisticsTable } from './StatisticsTable.js'
import { BarChart, PieChart, RankingBarChart } from './Charts/index.js'
import { isImageTheme } from './ChartsUtils.js'
import { QuestionComments } from './QuestionComments.js'

const VIEW = {
  BAR_CHART: 'bar-chart',
  PIE_CHART: 'pie-chart',
  TABLE: 'table',
  COMMENTS: 'comments',
}

// Question types that store free-text comments in the response table.
const COMMENT_QUESTION_TYPES = ['O', 'P']

// Ranking questions render as a horizontal bar chart of rank positions.
const RANKING_QUESTION_TYPE = 'R'

const VIEWS = [
  {
    value: VIEW.BAR_CHART,
    icon: () => <i className="ri-bar-chart-2-line"></i>,
    render: ({ isRanking, data, valueType, isImage, question }) =>
      isRanking ? (
        <RankingBarChart data={data} title={question?.title} />
      ) : (
        <BarChart data={data} valueType={valueType} isImage={isImage} />
      ),
  },
  {
    value: VIEW.PIE_CHART,
    icon: () => <i className="ri-pie-chart-line"></i>,
    isAvailable: ({ isRanking }) => !isRanking,
    render: ({ data, valueType, isImage }) => (
      <PieChart data={data} valueType={valueType} isImage={isImage} />
    ),
  },
  {
    value: VIEW.TABLE,
    icon: () => <i className="ri-table-line"></i>,
    render: ({ data, isImage }) => (
      <StatisticsTable data={data} isImage={isImage} />
    ),
  },
  {
    value: VIEW.COMMENTS,
    icon: () => <i className="ri-message-2-line"></i>,
    isAvailable: ({ hasComments }) => hasComments,
    render: ({ surveyId, question, chartId, data }) => (
      <QuestionComments
        surveyId={surveyId}
        questionCode={question?.code}
        qid={chartId}
        answerOptions={data}
        questionTitle={question?.title}
      />
    ),
  },
]

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
  const isImage = isImageTheme(question?.themeName)
  const isRanking = question?.type === RANKING_QUESTION_TYPE
  const hasComments = COMMENT_QUESTION_TYPES.includes(question?.type)

  const availableViews = VIEWS.filter(
    ({ isAvailable }) => isAvailable?.({ isRanking, hasComments }) ?? true
  )
  const toggleOptions = availableViews.map(({ value, icon }) => ({
    value,
    icon,
  }))
  // Fall back to the first available view if the active one isn't offered.
  const activeView =
    availableViews.find(({ value }) => value === view) ?? availableViews[0]

  const renderContext = {
    data,
    valueType,
    isImage,
    isRanking,
    surveyId,
    chartId,
    question,
  }

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
          <div>{activeView?.render(renderContext)}</div>
          <div className="responses-chart-view-toggle">
            <ToggleButtons
              id={`chart-view-${index}`}
              value={activeView?.value}
              onChange={setView}
              toggleOptions={toggleOptions}
            />
          </div>
        </>
      </Collapsible>
    </Card>
  )
}
