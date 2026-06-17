import { useState } from 'react'
import classNames from 'classnames'
import { Card } from 'react-bootstrap'

import { Collapsible, ToggleButtons } from 'components'
import { isRankingQuestion } from 'helpers'

import { ChartHeader } from './ChartHeader.js'
import { StatisticsTable } from './StatisticsTable.js'
import {
  BarChart,
  PieChart,
  RankingBarChart,
  RadarChart,
  LineChart,
  PolarAreaChart,
  DoughnutChart,
} from './Charts/index.js'
import {
  isImageTheme,
  isCommentQuestionType,
  isChoiceQuestion,
} from './ChartsUtils.js'
import { QuestionComments } from './QuestionComments.js'
import { CommentsModal } from './CommentsModal.js'

const VIEW = {
  BAR_CHART: 'bar-chart',
  PIE_CHART: 'pie-chart',
  TABLE: 'table',
  COMMENTS: 'comments',
  RADAR: 'radar',
  LINE: 'line',
  POLAR_AREA: 'polar-area',
  DOUGHNUT: 'doughnut',
}

const VIEWS = [
  {
    value: VIEW.BAR_CHART,
    label: () => t('Bar chart'),
    icon: () => <i className="ri-bar-chart-2-line"></i>,
    render: ({
      isRanking,
      data,
      valueType,
      isImage,
      question,
      hasComments,
      onViewComments,
    }) =>
      isRanking ? (
        <RankingBarChart data={data} title={question?.title} />
      ) : (
        <BarChart
          data={data}
          valueType={valueType}
          isImage={isImage}
          hasComments={hasComments}
          onViewComments={onViewComments}
        />
      ),
  },
  {
    value: VIEW.PIE_CHART,
    label: () => t('Pie chart'),
    icon: () => <i className="ri-pie-chart-line"></i>,
    isAvailable: ({ isRanking }) => !isRanking,
    render: ({ data, valueType, isImage }) => (
      <PieChart data={data} valueType={valueType} isImage={isImage} />
    ),
  },
  {
    value: VIEW.RADAR,
    label: () => t('Radar chart'),
    icon: () => <i className="ri-radar-line"></i>,
    menuOnly: true,
    isAvailable: ({ isChoice }) => isChoice,
    render: ({ data }) => <RadarChart data={data} />,
  },
  {
    value: VIEW.LINE,
    label: () => t('Line chart'),
    icon: () => <i className="ri-line-chart-line"></i>,
    menuOnly: true,
    isAvailable: ({ isChoice }) => isChoice,
    render: ({ data }) => <LineChart data={data} />,
  },
  {
    value: VIEW.POLAR_AREA,
    label: () => t('Polar area chart'),
    icon: () => <i className="ri-pie-chart-2-line"></i>,
    menuOnly: true,
    isAvailable: ({ isChoice }) => isChoice,
    render: ({ data }) => <PolarAreaChart data={data} />,
  },
  {
    value: VIEW.DOUGHNUT,
    label: () => t('Doughnut chart'),
    icon: () => <i className="ri-donut-chart-line"></i>,
    menuOnly: true,
    isAvailable: ({ isChoice }) => isChoice,
    render: ({ data }) => <DoughnutChart data={data} />,
  },
  {
    value: VIEW.TABLE,
    label: () => t('Table'),
    icon: () => <i className="ri-table-line"></i>,
    render: ({ data, isImage }) => (
      <StatisticsTable data={data} isImage={isImage} />
    ),
  },
  {
    value: VIEW.COMMENTS,
    label: () => t('Comments'),
    icon: () => <i className="ri-message-2-line"></i>,
    isAvailable: ({ hasComments }) => hasComments,
    render: ({ surveyId, question, data, onViewComments }) => (
      <QuestionComments
        surveyId={surveyId}
        questionCode={question?.code}
        answerOptions={data}
        onViewComments={onViewComments}
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
  const [commentsAnswer, setCommentsAnswer] = useState(null)
  const isImage = isImageTheme(question?.themeName)
  const isRanking = isRankingQuestion(question?.themeName)
  const hasComments = isCommentQuestionType(question?.type)
  const isChoice = isChoiceQuestion(question?.type)
  // No responses for this question when every answer option has a zero count.
  const hasResponses =
    (data ?? []).reduce((sum, item) => sum + (item?.value || 0), 0) > 0

  const availableViews = VIEWS.filter(
    ({ isAvailable }) =>
      isAvailable?.({ isRanking, hasComments, isChoice }) ?? true
  )
  const toggleOptions = availableViews
    .filter(({ menuOnly }) => !menuOnly)
    .map(({ value, icon }) => ({ value, icon }))
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
    hasComments,
    onViewComments: setCommentsAnswer,
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
    {
      label: t('All chart options'),
      icon: <i className="ri-add-line"></i>,
      subItems: availableViews.map((option) => ({
        label: option.label(),
        icon: option.icon(),
        active: option.value === activeView?.value,
        onClick: () => setView(option.value),
      })),
    },
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
      className={classNames('responses-statistics-card', {
        'responses-statistics-card--hidden': isHidden,
      })}
    >
      <ChartHeader {...question} actions={actions} />
      <Collapsible open={!isHidden}>
        {hasResponses ? (
          <>
            <div>{activeView?.render(renderContext)}</div>
            <div className="responses-statistics-chart-toggle">
              <ToggleButtons
                id={`chart-view-${index}`}
                value={activeView?.value}
                onChange={setView}
                toggleOptions={toggleOptions}
              />
            </div>
          </>
        ) : (
          <div className="responses-statistics-empty">
            {t('There are no responses for this question yet.')}
          </div>
        )}
      </Collapsible>

      {hasComments && (
        <CommentsModal
          show={commentsAnswer !== null}
          onHide={() => setCommentsAnswer(null)}
          surveyId={surveyId}
          questionCode={question?.code}
          questionTitle={question?.title}
          answerOptions={data}
          initialAnswer={commentsAnswer ?? ''}
        />
      )}
    </Card>
  )
}
