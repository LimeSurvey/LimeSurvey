import React from 'react'
import {
  Tooltip,
  Legend,
  RadialBarChart,
  PolarGrid,
  RadialBar,
  ResponsiveContainer,
} from 'recharts'

import { CustomTooltip } from '../ChartsUtils'

export const PolarAreaChart = ({ data }) => {
  return (
    <ResponsiveContainer width="100%" minHeight={500} height="100%">
      <RadialBarChart
        cx="50%"
        cy="50%"
        innerRadius="25%"
        outerRadius="90%"
        data={data}
        startAngle={90}
        endAngle={-270}
      >
        <PolarGrid />
        <RadialBar background dataKey="value" nameKey="title" />
        <Legend
          iconSize={10}
          layout="vertical"
          verticalAlign="middle"
          align="right"
        />
        <Tooltip content={<CustomTooltip />} />
      </RadialBarChart>
    </ResponsiveContainer>
  )
}
