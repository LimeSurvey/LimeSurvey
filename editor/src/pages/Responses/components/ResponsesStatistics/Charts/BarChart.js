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

import { CustomTooltip } from '../ChartsUtils'
import { COLORS } from '../ChartsUtils'

export const BarChart = ({ data }) => {
  return (
    <ResponsiveContainer width="100%" minHeight={500} height="100%">
      <RechartsBarChart dataKey="value" nameKey="title" data={data}>
        <CartesianGrid strokeDasharray="3 3" />
        <XAxis dataKey="title" angle={-45} textAnchor="end" height={80} />
        <YAxis />
        <Tooltip cursor={{ fill: '#eeeff7' }} content={<CustomTooltip />} />
        <Bar maxBarSize={60} dataKey="value" nameKey="title" data={data}>
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
