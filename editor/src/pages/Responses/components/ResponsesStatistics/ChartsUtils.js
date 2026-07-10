import { getQuestionTypeInfo } from 'components/QuestionTypes'
import {
  QT_5_POINT_CHOICE,
  QT_EXCLAMATION_LIST_DROPDOWN,
  QT_G_GENDER,
  QT_I_LANGUAGE,
  QT_L_LIST,
  QT_M_MULTIPLE_CHOICE,
  QT_O_LIST_WITH_COMMENT,
  QT_P_MULTIPLE_CHOICE_WITH_COMMENTS,
  QT_Q_MULTIPLE_SHORT_TEXT,
  QT_S_SHORT_FREE_TEXT,
  QT_T_LONG_FREE_TEXT,
  QT_U_HUGE_FREE_TEXT,
  QT_Y_YES_NO_RADIO,
} from 'helpers'

export const COLORS = [
  '#FFBA68',
  '#FF9AA2',
  '#25003E',
  '#8146F6',
  '#7FF409',
  '#FFE872',
  '#A3C8FF',
]

export const BAR_MAX_SIZE = 120

export const MAX_LABEL_LENGTH = 18

export const NON_ANSWER_KEYS = ['comment', 'other']

export const CHOICE_QUESTION_TYPES = [
  QT_L_LIST,
  QT_EXCLAMATION_LIST_DROPDOWN,
  QT_O_LIST_WITH_COMMENT,
  QT_5_POINT_CHOICE,
  QT_G_GENDER,
  QT_Y_YES_NO_RADIO,
  QT_I_LANGUAGE,
  QT_M_MULTIPLE_CHOICE,
  QT_P_MULTIPLE_CHOICE_WITH_COMMENTS,
]

export const TEXT_QUESTION_TYPES = [
  QT_S_SHORT_FREE_TEXT,
  QT_T_LONG_FREE_TEXT,
  QT_U_HUGE_FREE_TEXT,
  QT_Q_MULTIPLE_SHORT_TEXT,
]

// Whether charts display raw response counts or percentages.
export const VALUE_TYPE = {
  COUNT: 'count',
  PERCENTAGE: 'percentage',
}

export const statisticsGraphs = {
  DONT_SHOW: -1,
  BAR_CHART: 0,
  PIE_CHART: 1,
  RADAR: 2,
  LINE: 3,
  POLAR_AREA: 4,
  DOUGHNUT_CHART: 5,
}

// The registry's type/theme strings are static (only `title` is translated),
// so derive the lookup sets once on first use instead of rebuilding and
// scanning the whole registry on every render.
let imageThemes = null
let commentTypes = null

const ensureRegistrySets = () => {
  if (imageThemes !== null) return
  const entries = Object.values(getQuestionTypeInfo())
  imageThemes = new Set(
    entries
      .map((entry) => entry.theme)
      .filter((theme) => theme?.includes('image_select'))
  )
  commentTypes = new Set(
    entries
      .filter((entry) => entry.theme?.includes('comment'))
      .map((entry) => entry.type)
  )
}

// True for themes that render answer images (e.g. image_select-listradio).
export const isImageTheme = (themeName) => {
  ensureRegistrySets()
  return imageThemes.has(themeName)
}

export const isCommentQuestionType = (type) => {
  ensureRegistrySets()
  return commentTypes.has(type)
}

export const getUnionSegments = (data = []) => {
  const titles = []
  let noAnswerTitle = null
  data.forEach((row) =>
    (row.segments ?? []).forEach((segment) => {
      if (segment.key === 'NoAnswer') {
        noAnswerTitle = noAnswerTitle ?? segment.title
        return
      }
      if (!titles.includes(segment.title)) {
        titles.push(segment.title)
      }
    })
  )
  if (noAnswerTitle !== null) {
    titles.push(noAnswerTitle)
  }

  return titles.map((title, index) => ({
    title,
    color: COLORS[index % COLORS.length],
  }))
}

export const shouldRenderImage = (isImage, item) =>
  isImage && item?.key !== 'NoAnswer' && !NON_ANSWER_KEYS.includes(item?.key)

export const getLabelInterval = (count) => {
  if (count > 20) return 2
  if (count > 10) return 1
  return 0
}

export const truncateLabel = (value) => {
  const text = String(value ?? '')
  return text.length > MAX_LABEL_LENGTH
    ? `${text.slice(0, MAX_LABEL_LENGTH)}…`
    : text
}

// 1 -> "1st", 2 -> "2nd", 3 -> "3rd", 11 -> "11th", ...
export const ordinal = (n) => {
  const rem100 = n % 100
  if (rem100 >= 11 && rem100 <= 13) return `${n}th`
  switch (n % 10) {
    case 1:
      return `${n}st`
    case 2:
      return `${n}nd`
    case 3:
      return `${n}rd`
    default:
      return `${n}th`
  }
}

export const getMetricDataKey = (valueType) =>
  valueType === VALUE_TYPE.COUNT ? 'value' : 'percentageValue'

export const getDisplayMetric = (item, valueType, percentFallback) => {
  if (valueType === VALUE_TYPE.COUNT) return `${item?.value ?? ''}`
  const percentage =
    item?.percentage != null
      ? parseFloat(item.percentage)
      : (percentFallback ?? 0) * 100
  return `${percentage.toFixed(1).replace('.', ',')}%`
}

// The selectable answer options for the comment filter.
export const getAnswerFilterOptions = (answerOptions = []) =>
  answerOptions.filter(
    (option) => option?.key && !NON_ANSWER_KEYS.includes(option.key)
  )

export const buildOptionByAnswer = (answerOptions = []) => {
  const map = {}
  answerOptions.forEach((option) => {
    if (option?.title != null) map[option.title] = option
    if (option?.key != null && !(option.key in map)) map[option.key] = option
  })
  return map
}

export const getDataWithPercentages = (statisticsData) => {
  if (!statisticsData?.data) return []

  const newData = [...statisticsData.data]

  return newData.map((item, index) => {
    const percentageValue = (item.value / (statisticsData.total || 1)) * 100
    return {
      ...item,
      percentage: percentageValue.toFixed(1),
      percentageValue,
      fill: COLORS[index % COLORS.length],
    }
  })
}

export const getSegmentedCategories = (data = []) =>
  data.map((row) => ({
    key: row.key ?? row.title,
    title: row.title,
    options: (row.segments ?? []).map((segment, index) => {
      const percentageValue = segment.percentage ?? 0
      return {
        ...segment,
        percentage: percentageValue.toFixed(1),
        percentageValue,
        fill: COLORS[index % COLORS.length],
      }
    }),
  }))

export const TruncatedTick = ({
  x,
  y,
  payload,
  textAnchor = 'middle',
  dy = 12,
  isImage = false,
  item = null,
  imageWidth = BAR_MAX_SIZE,
}) => {
  const value = payload?.value ?? ''

  if (shouldRenderImage(isImage, item) && value) {
    return (
      <g transform={`translate(${x},${y})`}>
        <foreignObject
          x={-imageWidth / 2}
          y={4}
          width={imageWidth}
          height={imageWidth}
          overflow="visible"
        >
          <img
            src={value}
            alt={value}
            title={value}
            className="responses-statistics-bar-image"
          />
        </foreignObject>
      </g>
    )
  }

  return (
    <g transform={`translate(${x},${y})`}>
      <text
        className="responses-statistics-chart-labels"
        x={0}
        y={0}
        dy={dy}
        textAnchor={textAnchor}
      >
        {truncateLabel(value)}
        <title>{value}</title>
      </text>
    </g>
  )
}

export const TooltipShell = ({ children }) => (
  <div
    style={{
      position: 'relative',
      background: '#1F1F1F',
      color: '#FFFFFF',
      fontSize: 14,
      lineHeight: 1.5,
      padding: '12px 16px',
      borderRadius: 8,
      boxShadow: '0 4px 14px rgba(0, 0, 0, 0.18)',
      whiteSpace: 'nowrap',
    }}
  >
    <div className="responses-statistics-tooltip-tail" />
    {children}
  </div>
)

export const TooltipMetricLines = ({ count, percentage }) => (
  <>
    <div>
      {count}{' '}
      {count === 1
        ? t('participant selected this option')
        : t('participants selected this option')}
    </div>
    <div>
      {t('Percentage')}: {percentage}%
    </div>
  </>
)

export const CustomTooltip = ({
  active,
  payload,
  showCommentsHint = false,
}) => {
  if (!active || !payload?.length) return null

  const count = payload[0].payload.value
  const percentage = payload[0].payload.percentage
  return (
    <TooltipShell>
      <TooltipMetricLines count={count} percentage={percentage} />
      {showCommentsHint && (
        <div
          style={{
            marginTop: 8,
            display: 'flex',
            alignItems: 'center',
            gap: 6,
            opacity: 0.85,
          }}
        >
          <i className="ri-eye-line" />
          <span>{t('Click to view comments')}</span>
        </div>
      )}
    </TooltipShell>
  )
}

export const CommentSwatch = ({ fill }) =>
  fill ? (
    <span
      className="responses-statistics-comments-swatch"
      style={{ backgroundColor: fill }}
    />
  ) : null
