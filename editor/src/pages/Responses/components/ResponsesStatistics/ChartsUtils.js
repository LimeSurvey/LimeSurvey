import { getQuestionTypeInfo } from 'components/QuestionTypes'

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

// Single-option ('L','!','O','5','G','Y','I') and multiple-choice ('M','P')
// question types that support the alternative chart renderings.
const CHOICE_QUESTION_TYPES = ['L', '!', 'O', '5', 'G', 'Y', 'I', 'M', 'P']

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

const registryEntries = () => Object.values(getQuestionTypeInfo())

// True for themes that render answer images (e.g. image_select-listradio).
export const isImageTheme = (themeName) =>
  registryEntries().some(
    (entry) => entry.theme === themeName && entry.theme.includes('image_select')
  )

export const isCommentQuestionType = (type) =>
  registryEntries().some(
    (entry) => entry.type === type && entry.theme?.includes('comment')
  )

export const isChoiceQuestion = (type) => CHOICE_QUESTION_TYPES.includes(type)

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

  // Guard with the backing data row, not just `value`: synthetic rows
  // (NoAnswer / other / comment) keep text titles even on image themes, so
  // rendering them as <img> would 404 to a broken-image icon.
  if (shouldRenderImage(isImage, item) && value) {
    // Render the image as HTML inside the SVG so it can keep its natural
    // aspect ratio (width = bar width, height auto) and carry the same bordered
    // look as the legend/table image labels. `overflow: visible` keeps tall
    // (portrait) images from being clipped by the foreignObject box.
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

export const CustomTooltip = ({
  active,
  payload,
  showCommentsHint = false,
}) => {
  if (!active || !payload?.length) return null

  // Read from the data item so the count is correct regardless of which metric
  // (count or percentage) drives the bar/slice size.
  const count = payload[0].payload.value
  const percentage = payload[0].payload.percentage
  return (
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
      {/* Left-pointing tail */}
      <div
        style={{
          position: 'absolute',
          top: '50%',
          right: '100%',
          transform: 'translateY(-50%)',
          width: 0,
          height: 0,
          borderTop: '7px solid transparent',
          borderBottom: '7px solid transparent',
          borderRight: '7px solid #1F1F1F',
        }}
      />
      <div>
        {count}{' '}
        {count === 1
          ? t('participant selected this option')
          : t('participants selected this option')}
      </div>
      <div>
        {t('Percentage')}: {percentage}%
      </div>
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
    </div>
  )
}

export const CommentSwatch = ({ fill }) =>
  fill ? (
    <span
      className="responses-statistics-comments-swatch"
      style={{ backgroundColor: fill }}
    />
  ) : null
