import { useRef, useState } from 'react'
import classNames from 'classnames'
import { Card } from 'react-bootstrap'

import { Collapsible, ToggleButtons, TooltipContainer } from 'components'
import {
  isRankingQuestion,
  QT_1_ARRAY_DUAL,
  QT_COLON_ARRAY_NUMBERS,
  QT_N_NUMERICAL,
  QT_S_SHORT_FREE_TEXT,
  QT_SEMICOLON_ARRAY_TEXT,
  QT_T_LONG_FREE_TEXT,
  QT_U_HUGE_FREE_TEXT,
} from 'helpers'
import { ReactComponent as StackedBarIcon } from 'assets/icons/stacked-bar-icon.svg'

import { ChartHeader } from './ChartHeader.js'
import { StatisticsTable } from './StatisticsTable.js'
import { ArrayTextTable } from './ArrayTextTable.js'
import { ResponsesGrid } from './ResponsesGrid.js'
import {
  BarChart,
  PieChart,
  RankingBarChart,
  StackedBarChart,
  GroupedBarChart,
  DualScaleStackedBarChart,
  DualScaleDoughnutChart,
  RadarChart,
  LineChart,
  PolarAreaChart,
  DoughnutChart,
} from './Charts/index.js'
import {
  isImageTheme,
  isCommentQuestionType,
  CHOICE_QUESTION_TYPES,
  TEXT_QUESTION_TYPES,
  getSegmentedCategories,
  VALUE_TYPE,
} from './ChartsUtils.js'
import { QuestionComments } from './QuestionComments.js'
import { CommentsModal } from './CommentsModal.js'

const VIEW = {
  BAR_CHART: 'bar-chart',
  STACKED_BAR: 'stacked-bar',
  GROUPED_BAR: 'grouped-bar',
  STACKED_DOUGHNUT: 'stacked-doughnut',
  PIE_CHART: 'pie-chart',
  GRID: 'grid',
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
    isAvailable: ({ isArray, isArrayText, isNumerical }) =>
      !isArray && !isArrayText && !isNumerical,
    render: ({
      isRanking,
      data,
      valueType,
      isImage,
      question,
      hasComments,
      onViewComments,
    }) => {
      if (isRanking) {
        return <RankingBarChart data={data} title={question?.title} />
      }
      return (
        <BarChart
          data={data}
          valueType={valueType}
          isImage={isImage}
          hasComments={hasComments}
          onViewComments={onViewComments}
        />
      )
    },
  },
  {
    value: VIEW.STACKED_BAR,
    label: () => t('Stacked bar chart'),
    icon: (props) => <StackedBarIcon width="20" height="22" {...props} />,
    // Array numbers shows means, which don't stack — grouped only.
    isAvailable: ({ isArray, isArrayNumbers }) => isArray && !isArrayNumbers,
    render: ({ data, valueType, isDualScale }) =>
      isDualScale ? (
        <DualScaleStackedBarChart data={data} valueType={valueType} />
      ) : (
        <StackedBarChart data={data} valueType={valueType} />
      ),
  },
  {
    value: VIEW.GROUPED_BAR,
    label: () => t('Grouped bar chart'),
    icon: () => <i className="ri-bar-chart-2-line"></i>,
    isAvailable: ({ isArray, isDualScale }) => isArray && !isDualScale,
    render: ({ data, valueType }) => (
      <GroupedBarChart
        data={getSegmentedCategories(data)}
        valueType={valueType}
      />
    ),
  },
  {
    value: VIEW.STACKED_DOUGHNUT,
    label: () => t('Doughnut chart'),
    icon: () => <i className="ri-donut-chart-line"></i>,
    isAvailable: ({ isDualScale }) => isDualScale,
    render: ({ data }) => <DualScaleDoughnutChart data={data} />,
  },
  {
    value: VIEW.PIE_CHART,
    label: () => t('Pie chart'),
    icon: () => <i className="ri-pie-chart-line"></i>,
    isAvailable: ({ isRanking, isArray, isArrayText, isNumerical }) =>
      !isRanking && !isArray && !isArrayText && !isNumerical,
    // Comment types keep only Bar/Table/Comments in the quick toggle; pie moves
    // to the meatball menu.
    menuOnly: ({ hasComments }) => hasComments,
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
    value: VIEW.GRID,
    label: () => t('Grid'),
    icon: () => <i className="ri-layout-grid-line"></i>,
    isAvailable: ({ isGridable }) => isGridable,
    render: ({ surveyId, question, filters, isNumerical }) => (
      <ResponsesGrid
        surveyId={surveyId}
        questionCode={question?.code}
        fields={question?.fields}
        filters={filters}
        twoColumns={isNumerical}
      />
    ),
  },
  {
    value: VIEW.TABLE,
    label: () => t('Table'),
    icon: () => <i className="ri-table-line"></i>,
    isAvailable: ({ isGridable, isNumerical }) => !isGridable || isNumerical,
    render: ({
      data,
      isImage,
      isArrayText,
      isText,
      surveyId,
      question,
      filters,
    }) =>
      isArrayText || isText ? (
        <ArrayTextTable
          surveyId={surveyId}
          questionCode={question?.code}
          fields={question?.fields}
          filters={filters}
          searchable={isText}
        />
      ) : (
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
        fields={question?.fields}
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
  filters = {},
}) => {
  const isNumerical = question?.type === QT_N_NUMERICAL
  const isGridable =
    isNumerical ||
    [QT_S_SHORT_FREE_TEXT, QT_T_LONG_FREE_TEXT, QT_U_HUGE_FREE_TEXT].includes(
      question?.type
    )
  const [view, setView] = useState(isGridable ? VIEW.GRID : VIEW.BAR_CHART)
  const [commentsAnswer, setCommentsAnswer] = useState(null)
  const cardRef = useRef(null)
  const isImage = isImageTheme(question?.themeName)
  const isRanking = isRankingQuestion(question?.themeName)
  const hasComments = isCommentQuestionType(question?.type)
  const isChoice = CHOICE_QUESTION_TYPES.includes(question?.type)
  const isArray = (data ?? []).some((item) => Array.isArray(item?.segments))
  // Array (Texts) has no chart: it fetches and renders its own responses table.
  const isArrayText = question?.type === QT_SEMICOLON_ARRAY_TEXT
  const isText = TEXT_QUESTION_TYPES.includes(question?.type)
  const isArrayNumbers = question?.type === QT_COLON_ARRAY_NUMBERS
  const isDualScale = question?.type === QT_1_ARRAY_DUAL
  const effectiveValueType = isArrayNumbers ? VALUE_TYPE.COUNT : valueType
  // No responses for this question when every answer option has a zero count.
  // Array text bypasses this — its table loads its own per-response data.
  const hasResponses =
    isArrayText ||
    (data ?? []).reduce((sum, item) => sum + (item?.value || 0), 0) > 0

  const viewContext = {
    isRanking,
    hasComments,
    isChoice,
    isArray,
    isArrayText,
    isArrayNumbers,
    isDualScale,
    isNumerical,
    isGridable,
  }

  const availableViews = VIEWS.filter(
    ({ isAvailable }) => isAvailable?.(viewContext) ?? true
  )
  // `menuOnly` hides a view from the quick toggle (it stays in the meatball
  // menu); it can be a flag or a predicate of the view context.
  const isMenuOnly = ({ menuOnly }) =>
    typeof menuOnly === 'function' ? menuOnly(viewContext) : !!menuOnly
  const toggleOptions = availableViews
    .filter((option) => !isMenuOnly(option))
    .map(({ value, icon }) => ({ value, icon }))
  const activeView =
    availableViews.find(({ value }) => value === view) ?? availableViews[0]

  const renderContext = {
    data,
    valueType: effectiveValueType,
    isImage,
    isRanking,
    isArray,
    isArrayText,
    isText,
    isDualScale,
    isNumerical,
    surveyId,
    chartId,
    question,
    hasComments,
    filters,
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

  // Switching view can change the chart height; scroll the card back to the top
  // so the user stays anchored to the chart they're viewing.
  const handleViewChange = (value) => {
    setView(value)
    cardRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }

  const actions = [
    {
      label: t('All chart options'),
      icon: <i className="ri-add-line"></i>,
      subItems: availableViews.map((option) => ({
        label: option.label(),
        icon: option.icon(),
        active: option.value === activeView?.value,
        onClick: () => handleViewChange(option.value),
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
      ref={cardRef}
      className={classNames('responses-statistics-card', {
        'responses-statistics-card--hidden': isHidden,
      })}
    >
      <ChartHeader {...question} actions={actions} />
      <Collapsible open={!isHidden}>
        {hasResponses ? (
          <>
            {isArrayNumbers && (
              <div className="responses-statistics-chart-subtitle">
                <TooltipContainer
                  tip={t(
                    'Each value is the mean of the numbers entered for a subquestion (row) and answer option (column): their sum divided by the number of responses that answered that cell. Unanswered cells are left out.'
                  )}
                >
                  <span className="responses-statistics-chart-subtitle-term">
                    {t('Mean')} <i className="ri-information-line"></i>
                  </span>
                </TooltipContainer>{' '}
                {t(
                  'of the subquestions (rows) in the concerning answer option (columns)'
                )}
              </div>
            )}
            <div>{activeView?.render(renderContext)}</div>
            {toggleOptions.length > 1 && (
              <div className="responses-statistics-chart-toggle">
                <ToggleButtons
                  id={`chart-view-${index}`}
                  value={activeView?.value}
                  onChange={handleViewChange}
                  toggleOptions={toggleOptions}
                />
              </div>
            )}
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
          questionType={question?.type}
          questionTitle={question?.title}
          fields={question?.fields}
          answerOptions={data}
          initialAnswer={commentsAnswer ?? ''}
        />
      )}
    </Card>
  )
}
