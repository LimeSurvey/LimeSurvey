import { getQuestionTypeInfo } from 'components/QuestionTypes'

export const MAX_LABEL_LENGTH = 18

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

// Chart capabilities are derived from the question-type registry rather than
// re-listing type/theme codes here, so new image-select or comment-bearing
// themes are picked up automatically. The registry's theme codes follow naming
// conventions: image-select themes contain "image_select" and comment-bearing
// themes contain "comment".
const registryEntries = () => Object.values(getQuestionTypeInfo())

// True for themes that render answer images (e.g. image_select-listradio).
export const isImageTheme = (themeName) =>
  registryEntries().some(
    (entry) => entry.theme === themeName && entry.theme.includes('image_select')
  )

// True for question types whose registry theme indicates free-text comments
// (e.g. list_with_comment, multiplechoice_with_comments).
export const isCommentQuestionType = (type) =>
  registryEntries().some(
    (entry) => entry.type === type && entry.theme?.includes('comment')
  )

// NoAnswer rows have no image even on image themes; their labels stay text.
export const shouldRenderImage = (isImage, item) =>
  isImage && item?.key !== 'NoAnswer'

// Cap for bar width, shared with the x-axis image labels so an image label is
// rendered at the same width as the bar it sits under.
export const BAR_MAX_SIZE = 120

export const TruncatedTick = ({
  x,
  y,
  payload,
  textAnchor = 'middle',
  dy = 12,
  isImage = false,
  imageWidth = BAR_MAX_SIZE,
}) => {
  const value = payload?.value ?? ''

  if (isImage && value) {
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

export const CustomTooltip = ({ active, payload, showCommentsHint = false }) => {
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
        {count}
        {' '}
        {count === 1
          ? t('participant selected this option')
          : t('participants selected this option')}
      </div>
      <div>{t('Percentage')}: {percentage}%</div>
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

// Answer-option keys that are not real answers (free-text comment / "other"),
// excluded from the comment answer filter list.
export const NON_ANSWER_KEYS = ['comment', 'other']

// The selectable answer options for the comment filter.
export const getAnswerFilterOptions = (answerOptions = []) =>
  answerOptions.filter(
    (option) => option?.key && !NON_ANSWER_KEYS.includes(option.key)
  )

// Lookup of an answer option by either its title or its key, so a comment's
// sub-question (which may carry either) can resolve back to its option.
export const buildOptionByAnswer = (answerOptions = []) => {
  const map = {}
  answerOptions.forEach((option) => {
    if (option?.title != null) map[option.title] = option
    if (option?.key != null && !(option.key in map)) map[option.key] = option
  })
  return map
}

export const CommentSwatch = ({ fill }) =>
  fill ? (
    <span
      className="responses-statistics-comments-swatch"
      style={{ backgroundColor: fill }}
    />
  ) : null

export const statisticsGraphs = {
  DONT_SHOW: -1,
  BAR_CHART: 0,
  PIE_CHART: 1,
  RADAR: 2,
  LINE: 3,
  POLAR_AREA: 4,
  DOUGHNUT_CHART: 5,
}

// Whether charts display raw response counts or percentages.
export const VALUE_TYPE = {
  COUNT: 'count',
  PERCENTAGE: 'percentage',
}

// Numeric data field that should drive bar/axis sizing for the value type.
export const getMetricDataKey = (valueType) =>
  valueType === VALUE_TYPE.COUNT ? 'value' : 'percentageValue'

// Metric to render for a data row: the raw count or the percentage (one
// decimal, comma separator) depending on the active value type. The percent
// fallback is recharts' 0-1 ratio, used when the row has no percentage.
export const getDisplayMetric = (item, valueType, percentFallback) => {
  if (valueType === VALUE_TYPE.COUNT) return `${item?.value ?? ''}`
  const percentage =
    item?.percentage != null
      ? parseFloat(item.percentage)
      : (percentFallback ?? 0) * 100
  return `${percentage.toFixed(1).replace('.', ',')}%`
}

export const COLORS = [
  '#FFBA68',
  '#FF9AA2',
  '#25003E',
  '#8146F6',
  '#7FF409',
  '#FFE872',
  '#A3C8FF',
]

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
