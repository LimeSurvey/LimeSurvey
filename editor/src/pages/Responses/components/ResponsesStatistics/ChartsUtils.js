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

// Question themes whose answer options are images: the answer label is an
// image path (e.g. /upload/surveys/123/images/x.png) rather than text.
export const IMAGE_THEMES = [
  'image_select-listradio',
  'image_select-multiplechoice',
]

export const isImageTheme = (theme) => IMAGE_THEMES.includes(theme)

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
      <div>{t('Percentage')}: {percentage}</div>
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

export const renderCustomLabel = ({
  cx,
  cy,
  midAngle,
  innerRadius,
  outerRadius,
  percent,
}) => {
  if (percent < 0.05) {
    return null // Don't show labels for slices < 5%
  }

  const RADIAN = Math.PI / 180
  const radius = innerRadius + (outerRadius - innerRadius) * 0.5
  const x = cx + radius * Math.cos(-midAngle * RADIAN)
  const y = cy + radius * Math.sin(-midAngle * RADIAN)

  return (
    <text
      x={x}
      y={y}
      fill="white"
      textAnchor={x > cx ? 'start' : 'end'}
      dominantBaseline="central"
      fontSize="12"
      fontWeight="bold"
    >
      {`${(percent * 100).toFixed(0)}%`}
    </text>
  )
}
