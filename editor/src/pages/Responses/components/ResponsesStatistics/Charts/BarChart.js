import React from 'react'
import {
  BarChart as RechartsBarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Cell,
  ResponsiveContainer,
} from 'recharts'

import {
  COLORS,
  CustomTooltip,
  TruncatedTick,
  getLabelInterval,
  getMetricDataKey,
  shouldRenderImage,
  VALUE_TYPE,
} from '../ChartsUtils'

export const BarChart = ({
  data,
  valueType = VALUE_TYPE.PERCENTAGE,
  isImage = false,
}) => {
  const isPercentage = valueType === VALUE_TYPE.PERCENTAGE
  const dataKey = getMetricDataKey(valueType)

  return (
    <ResponsiveContainer width="100%" minHeight={500} height="100%">
      <RechartsBarChart data={data}>
        <CartesianGrid strokeDasharray="3 3" />
        <XAxis
          dataKey="title"
          height={80}
          interval={getLabelInterval(data.length)}
          tick={(props) => {
            // Resolve the row by its category value (the title) rather than the
            // tick's `index`, which recharts doesn't reliably include in the
            // payload (varies by version / interval skipping).
            const item = isImage
              ? data.find((row) => row.title === props.payload?.value)
              : null
            return (
              <TruncatedTick
                {...props}
                isImage={shouldRenderImage(isImage, item)}
              />
            )
          }}
        />
        <YAxis unit={isPercentage ? '%' : undefined} />
        <Tooltip cursor={{ fill: '#eeeff7' }} content={<CustomTooltip />} />
        <Bar maxBarSize={60} dataKey={dataKey} nameKey="title" data={data}>
          {data.map((_, index) => {
            return (
              <Cell
                key={`bar-chart-${index}`}
                fill={COLORS[index % COLORS.length]}
              />
            )
          })}
        </Bar>
      </RechartsBarChart>
    </ResponsiveContainer>
  )
}
