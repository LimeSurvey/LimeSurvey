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

export const IMAGE_THEMES = [
  'image_select-listradio',
  'image_select-multiplechoice',
]

export const isImageTheme = (theme) => IMAGE_THEMES.includes(theme)

// NoAnswer rows have no image even on image themes; their labels stay text.
export const shouldRenderImage = (isImage, item) =>
  isImage && item?.key !== 'NoAnswer'

const LABEL_IMAGE_SIZE = 40

export const TruncatedTick = ({
  x,
  y,
  payload,
  textAnchor = 'middle',
  dy = 12,
  isImage = false,
}) => {
  const value = payload?.value ?? ''

  if (isImage && value) {
    return (
      <g transform={`translate(${x},${y})`}>
        <image
          href={value}
          x={-LABEL_IMAGE_SIZE / 2}
          y={4}
          width={LABEL_IMAGE_SIZE}
          height={LABEL_IMAGE_SIZE}
          preserveAspectRatio="xMidYMid meet"
        >
          <title>{value}</title>
        </image>
      </g>
    )
  }

  return (
    <g transform={`translate(${x},${y})`}>
      <text
        className="chart-labels"
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

export const CustomTooltip = ({ active, payload }) => {
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
      <div>{count} {t('participants selected this option')}</div>
      <div>{t('Percentage')}: {percentage}%</div>
    </div>
  )
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
