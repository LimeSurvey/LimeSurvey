import React from 'react'
import {
  Tooltip,
  RadarChart as RechartsRadarChart,
  PolarGrid,
  PolarAngleAxis,
  Radar,
  ResponsiveContainer,
} from 'recharts'
import { COLORS, CustomTooltip } from '../ChartsUtils'

export const RadarChart = ({ data }) => {
  return (
    <ResponsiveContainer width="100%" minHeight={400} height="100%">
      <RechartsRadarChart data={data}>
        <PolarGrid />
        <PolarAngleAxis dataKey="title" />
        <Radar
          name="Experience"
          dataKey="value"
          stroke={COLORS[1]}
          fill={COLORS[1]}
          fillOpacity={0.6}
        />
        <Tooltip content={<CustomTooltip />} />
      </RechartsRadarChart>
    </ResponsiveContainer>
  )
}
