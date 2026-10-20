import React from 'react'
import {
  Tooltip,
  Legend,
  RadialBarChart,
  PolarGrid,
  RadialBar,
  ResponsiveContainer,
} from 'recharts'

import {
  COLORS,
  CustomTooltip,
  getMetricDataKey,
  VALUE_TYPE,
} from '../ChartsUtils'
import { CustomLegend } from './CustomLegend'

export const PolarAreaChart = ({
  data,
  isImage = false,
  valueType = VALUE_TYPE.PERCENTAGE,
}) => {
  const coloredData = data.map((entry, index) => ({
    ...entry,
    fill: entry.fill ?? COLORS[index % COLORS.length],
  }))

  const legendPayload = coloredData.map((entry) => ({
    value: entry.title,
    color: entry.fill,
    type: 'square',
    payload: entry,
  }))

  return (
    <ResponsiveContainer width="100%" minHeight={500} height="100%">
      <RadialBarChart
        cx="50%"
        cy="50%"
        innerRadius="25%"
        outerRadius="90%"
        data={coloredData}
        startAngle={90}
        endAngle={-270}
      >
        <PolarGrid />
        <RadialBar
          background
          dataKey={getMetricDataKey(valueType)}
          nameKey="title"
        />
        <Legend
          content={<CustomLegend isImage={isImage} />}
          payload={legendPayload}
        />
        <Tooltip content={<CustomTooltip />} />
      </RadialBarChart>
    </ResponsiveContainer>
  )
}
