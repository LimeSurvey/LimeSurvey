export const CustomTooltip = ({ active, payload }) => {
  if (active && payload && payload.length) {
    return (
      <div className="bg-white p-3 border border-gray-300 rounded shadow-lg">
        <p className="text-blue-600">
          {t('Label')}: {`${payload[0]?.payload?.title}`}
        </p>
        <p>
          {t('Count')}: {payload[0].value}
        </p>
        <p className="text-blue-600">
          {t('Percentage')}: {payload[0].payload.percentage}
        </p>
      </div>
    )
  }
  return null
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

  return newData.map((item, index) => ({
    ...item,
    percentage: ((item.value / (statisticsData.total || 1)) * 100).toFixed(1),
    fill: COLORS[index % COLORS.length],
  }))
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
