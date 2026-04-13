import React from 'react'
import {
  Tooltip,
  LineChart as RechartsLineChart,
  CartesianGrid,
  XAxis,
  YAxis,
  Legend,
  Line,
  ResponsiveContainer,
} from 'recharts'
import { CustomTooltip } from '../ChartsUtils'

const CustomizedLabel = ({ x, y, stroke, value }) => {
  return (
    <text x={x} y={y} dy={-4} fill={stroke} fontSize={10} textAnchor="middle">
      {value}
    </text>
  )
}

export const LineChart = ({ data }) => {
  return (
    <ResponsiveContainer width="100%" minHeight={500} height="100%">
      <RechartsLineChart data={data}>
        <CartesianGrid strokeDasharray="3 3" />
        <XAxis dataKey="key" />
        <YAxis />
        <Tooltip content={<CustomTooltip />} />
        <Legend />
        <Line
          type="monotone"
          dataKey="value"
          stroke="#8884d8"
          activeDot={{ r: 8 }}
          label={<CustomizedLabel />}
        />
      </RechartsLineChart>
    </ResponsiveContainer>
  )
}
