import { useRef, useState } from 'react'
import classNames from 'classnames'
import { Card } from 'react-bootstrap'

import { Collapsible, ToggleButtons, TooltipContainer } from 'components'
import {
  isRankingQuestion,
  QT_1_ARRAY_DUAL,
  QT_COLON_ARRAY_NUMBERS,
  QT_K_MULTIPLE_NUMERICAL,
  QT_N_NUMERICAL,
  QT_S_SHORT_FREE_TEXT,
  QT_SEMICOLON_ARRAY_TEXT,
  QT_T_LONG_FREE_TEXT,
  QT_U_HUGE_FREE_TEXT,
  QT_VERTICAL_FILE_UPLOAD,
} from 'helpers'
import { ReactComponent as StackedBarIcon } from 'assets/icons/stacked-bar-icon.svg'
import { ReactComponent as RadarIcon } from 'assets/icons/radar-icon.svg'

import { ChartHeader } from './ChartHeader.js'
import { StatisticsTable } from './StatisticsTable.js'
import { ArrayTextTable } from './ArrayTextTable.js'
import { ResponsesGrid } from './ResponsesGrid.js'
import { MultiNumericalGrid } from './MultiNumericalGrid.js'
import { FileUploadTable } from './FileUploadTable.js'
import {
  BarChart,
  PieChart,
  RankingBarChart,
  StackedBarChart,
  GroupedBarChart,
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
  statisticsGraphs,
  VALUE_TYPE,
  wrapTerm,
} from './ChartsUtils.js'
import { QuestionComments } from './QuestionComments.js'
import { CommentsModal } from './CommentsModal.js'
import {
  ChartOrientationContext,
  ChartOrientationMenu,
  createOrientationToggleIcon,
  ORIENTATION,
} from './ChartOrientationMenu.js'

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
    icon: () => <i className="ri-bar-chart-line"></i>,
    orientationIcons: {
      [ORIENTATION.VERTICAL]: () => <i className="ri-bar-chart-line"></i>,
      [ORIENTATION.HORIZONTAL]: () => (
        <i className="ri-bar-chart-horizontal-line"></i>
      ),
    },
    isAvailable: ({
      isArray,
      isArrayText,
      isNumerical,
      isMultiNumerical,
      isFileUpload,
    }) =>
      !isArray &&
      !isArrayText &&
      !isNumerical &&
      !isMultiNumerical &&
      !isFileUpload,
    render: ({
      isRanking,
      data,
      valueType,
      isImage,
      question,
      hasComments,
      onViewComments,
      orientation,
    }) => {
      if (isRanking) {
        return (
          <RankingBarChart
            data={data}
            title={question?.title}
            orientation={orientation}
          />
        )
      }
      return (
        <BarChart
          data={data}
          valueType={valueType}
          isImage={isImage}
          hasComments={hasComments}
          onViewComments={onViewComments}
          orientation={orientation}
        />
      )
    },
  },
  {
    value: VIEW.STACKED_BAR,
    label: () => t('Stacked bar chart'),
    icon: (props) => <StackedBarIcon width="20" height="22" {...props} />,
    // Array numbers shows means, which don't stack — grouped only.
    isAvailable: ({ isArray, isArrayText, isArrayNumbers }) =>
      isArray && !isArrayText && !isArrayNumbers,
    render: ({ data, valueType, isDualScale }) => (
      <StackedBarChart
        data={data}
        valueType={valueType}
        dualScale={isDualScale}
      />
    ),
  },
  {
    value: VIEW.GROUPED_BAR,
    label: () => t('Grouped bar chart'),
    icon: () => <i className="ri-bar-chart-horizontal-line"></i>,
    orientationIcons: {
      [ORIENTATION.VERTICAL]: () => (
        <i className="ri-bar-chart-grouped-line"></i>
      ),
      [ORIENTATION.HORIZONTAL]: () => (
        <i className="ri-bar-chart-horizontal-line"></i>
      ),
    },
    isAvailable: ({ isArray, isArrayText, isDualScale }) =>
      isArray && !isArrayText && !isDualScale,
    render: ({ data, valueType, orientation }) => (
      <GroupedBarChart
        data={getSegmentedCategories(data)}
        valueType={valueType}
        orientation={orientation}
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
    isAvailable: ({
      isRanking,
      isArray,
      isArrayText,
      isNumerical,
      isMultiNumerical,
      isFileUpload,
    }) =>
      !isRanking &&
      !isArray &&
      !isArrayText &&
      !isNumerical &&
      !isMultiNumerical &&
      !isFileUpload,
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
    icon: (props) => <RadarIcon width="16" height="17" {...props} />,
    menuOnly: true,
    isAvailable: ({ isChoice }) => isChoice,
    render: ({ data, isImage, valueType }) => (
      <RadarChart data={data} isImage={isImage} valueType={valueType} />
    ),
  },
  {
    value: VIEW.LINE,
    label: () => t('Line chart'),
    icon: () => <i className="ri-line-chart-line"></i>,
    menuOnly: true,
    isAvailable: ({ isChoice }) => isChoice,
    render: ({ data, isImage, valueType }) => (
      <LineChart data={data} isImage={isImage} valueType={valueType} />
    ),
  },
  {
    value: VIEW.POLAR_AREA,
    label: () => t('Polar area chart'),
    icon: () => <i className="ri-pie-chart-2-line"></i>,
    menuOnly: true,
    isAvailable: ({ isChoice }) => isChoice,
    render: ({ data, isImage, valueType }) => (
      <PolarAreaChart data={data} isImage={isImage} valueType={valueType} />
    ),
  },
  {
    value: VIEW.DOUGHNUT,
    label: () => t('Doughnut chart'),
    icon: () => <i className="ri-donut-chart-line"></i>,
    menuOnly: true,
    isAvailable: ({ isChoice }) => isChoice,
    render: ({ data, isImage }) => (
      <DoughnutChart data={data} isImage={isImage} />
    ),
  },
  {
    value: VIEW.GRID,
    label: () => t('Grid'),
    icon: () => <i className="ri-list-check"></i>,
    isAvailable: ({ isGridable, isMultiNumerical }) =>
      isGridable || isMultiNumerical,
    render: ({ surveyId, question, filters, isNumerical, isMultiNumerical }) =>
      isMultiNumerical ? (
        <MultiNumericalGrid
          surveyId={surveyId}
          questionCode={question?.code}
          fields={question?.fields}
          filters={filters}
        />
      ) : (
        <ResponsesGrid
          surveyId={surveyId}
          questionCode={question?.code}
          title={question?.title}
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
    isAvailable: ({ isGridable, isNumerical, isMultiNumerical }) =>
      !isGridable || isNumerical || isMultiNumerical,
    render: ({
      data,
      isImage,
      isArrayText,
      isText,
      isDualScale,
      isMultiNumerical,
      isFileUpload,
      valueType,
      surveyId,
      question,
      filters,
    }) =>
      isFileUpload ? (
        <FileUploadTable
          surveyId={surveyId}
          questionId={question?.qid}
          questionCode={question?.code}
          fields={question?.fields}
          filters={filters}
        />
      ) : isArrayText || isText || isDualScale || isMultiNumerical ? (
        <ArrayTextTable
          surveyId={surveyId}
          questionCode={question?.code}
          fields={question?.fields}
          filters={filters}
          searchable={isText || isMultiNumerical}
          scaleHeaders={question?.scaleHeaders}
        />
      ) : (
        <StatisticsTable data={data} isImage={isImage} valueType={valueType} />
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

const ORIENTATION_TOGGLE_ICONS = Object.fromEntries(
  VIEWS.filter(({ orientationIcons }) => orientationIcons).map(
    ({ value, orientationIcons }) => [
      value,
      createOrientationToggleIcon(value, orientationIcons),
    ]
  )
)

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

// statistics_graphtype question setting → views it selects, first available wins.
const GRAPH_VIEWS = {
  [statisticsGraphs.BAR_CHART]: [VIEW.BAR_CHART],
  [statisticsGraphs.PIE_CHART]: [VIEW.PIE_CHART],
  [statisticsGraphs.RADAR]: [VIEW.RADAR],
  [statisticsGraphs.LINE]: [VIEW.LINE],
  [statisticsGraphs.POLAR_AREA]: [VIEW.POLAR_AREA],
  [statisticsGraphs.DOUGHNUT_CHART]: [VIEW.DOUGHNUT, VIEW.STACKED_DOUGHNUT],
  [statisticsGraphs.STACKED_BAR]: [VIEW.STACKED_BAR],
  [statisticsGraphs.GROUPED_BAR]: [VIEW.GROUPED_BAR],
  [statisticsGraphs.GRID]: [VIEW.GRID],
  [statisticsGraphs.TABLE]: [VIEW.TABLE],
  [statisticsGraphs.COMMENTS]: [VIEW.COMMENTS],
}

const getDefaultView = (availableViews, viewContext, chartType) => {
  const preferredViews = [
    ...(GRAPH_VIEWS[chartType] ?? []),
    viewContext.isArrayText && VIEW.TABLE,
    viewContext.isArray && !viewContext.isArrayNumbers && VIEW.STACKED_BAR,
    viewContext.isNumerical && VIEW.TABLE,
    viewContext.isMultiNumerical && VIEW.GRID,
    VIEW.BAR_CHART,
    VIEW.TABLE,
  ].filter(Boolean)

  return (
    preferredViews.find((view) =>
      availableViews.some(({ value }) => value === view)
    ) ??
    availableViews[0]?.value ??
    VIEW.TABLE
  )
}

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
  const isMultiNumerical = question?.type === QT_K_MULTIPLE_NUMERICAL
  const isFileUpload = question?.type === QT_VERTICAL_FILE_UPLOAD
  const isGridable =
    isNumerical ||
    [QT_S_SHORT_FREE_TEXT, QT_T_LONG_FREE_TEXT, QT_U_HUGE_FREE_TEXT].includes(
      question?.type
    )
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
  const effectiveValueType =
    isArrayNumbers || isRanking ? VALUE_TYPE.COUNT : valueType
  // No responses for this question when every answer option has a zero count.
  // Array text bypasses this — its table loads its own per-response data.
  const hasResponses =
    isArrayText ||
    isMultiNumerical ||
    isFileUpload ||
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
    isMultiNumerical,
    isFileUpload,
    isGridable,
  }

  const availableViews = VIEWS.filter(
    ({ isAvailable }) => isAvailable?.(viewContext) ?? true
  )
  const [view, setView] = useState(() =>
    getDefaultView(availableViews, viewContext, question?.chartType)
  )
  const [orientation, setOrientation] = useState(
    isRanking || isArray ? ORIENTATION.HORIZONTAL : ORIENTATION.VERTICAL
  )
  const [isOrientationMenuOpen, setIsOrientationMenuOpen] = useState(false)
  const chartToggleRef = useRef(null)
  const orientationChevronRef = useRef(null)
  // `menuOnly` hides a view from the quick toggle (it stays in the meatball
  // menu); it can be a flag or a predicate of the view context.
  const isMenuOnly = ({ menuOnly }) =>
    typeof menuOnly === 'function' ? menuOnly(viewContext) : !!menuOnly
  const toggleOptions = availableViews
    .filter((option) => !isMenuOnly(option))
    .map(({ value, icon }) => ({
      value,
      icon: ORIENTATION_TOGGLE_ICONS[value] ?? icon,
    }))
  const activeView =
    availableViews.find(({ value }) => value === view) ?? availableViews[0]
  const activeOrientationIcons = activeView?.orientationIcons

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
    isMultiNumerical,
    isFileUpload,
    surveyId,
    chartId,
    question,
    hasComments,
    filters,
    orientation,
    onViewComments: setCommentsAnswer,
  }

  const storageKey = getStorageKey(surveyId, chartId, index)
  const [isHidden, setIsHidden] = useState(
    () =>
      readHiddenCharts()[storageKey] ??
      String(question?.chartType) === String(statisticsGraphs.DONT_SHOW)
  )

  // Stored both ways so showing a "Don't show" chart sticks.
  const setHidden = (hidden) => {
    writeHiddenCharts({ ...readHiddenCharts(), [storageKey]: hidden })
    setIsHidden(hidden)
  }

  // Switching view can change the chart height; scroll the card back to the top
  // so the user stays anchored to the chart they're viewing.
  const handleViewChange = (value) => {
    setView(value)
    setIsOrientationMenuOpen(false)
    cardRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }

  const handleOrientationSelect = (value) => {
    setOrientation(value)
    setIsOrientationMenuOpen(false)
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
      <Collapsible open={!isHidden} unmountOnExit>
        {hasResponses ? (
          <>
            {isArrayNumbers && (
              <div className="responses-statistics-chart-subtitle">
                {wrapTerm(
                  t(
                    '%sMean%s of the subquestions (rows) in the concerning answer option (columns)'
                  ),
                  (term) => (
                    <TooltipContainer
                      tip={t(
                        'Each value is the mean of the numbers entered for a subquestion (row) and answer option (column): their sum divided by the number of responses that answered that cell. Unanswered cells are left out.'
                      )}
                    >
                      <span className="responses-statistics-chart-subtitle-term">
                        {term} <i className="ri-information-line"></i>
                      </span>
                    </TooltipContainer>
                  )
                )}
              </div>
            )}
            <div>{activeView?.render(renderContext)}</div>
            {toggleOptions.length > 1 && (
              <div
                ref={chartToggleRef}
                className="responses-statistics-chart-toggle"
              >
                {activeOrientationIcons && isOrientationMenuOpen && (
                  <ChartOrientationMenu
                    orientation={orientation}
                    icons={activeOrientationIcons}
                    onSelect={handleOrientationSelect}
                    onClose={() => setIsOrientationMenuOpen(false)}
                    containerRef={chartToggleRef}
                    chevronRef={orientationChevronRef}
                  />
                )}
                <ChartOrientationContext.Provider
                  value={{
                    orientation,
                    activeView: activeView?.value,
                    isOpen: isOrientationMenuOpen,
                    onToggleMenu: () =>
                      setIsOrientationMenuOpen((isOpen) => !isOpen),
                    chevronRef: orientationChevronRef,
                  }}
                >
                  <ToggleButtons
                    id={`chart-view-${index}`}
                    value={activeView?.value}
                    onChange={handleViewChange}
                    toggleOptions={toggleOptions}
                    theme="quick-access"
                  />
                </ChartOrientationContext.Provider>
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
