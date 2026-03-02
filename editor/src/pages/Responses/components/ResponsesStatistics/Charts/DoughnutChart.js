import React from 'react'
import {
  Legend,
  Cell,
  PieChart as RechartsPieChart,
  Pie,
  ResponsiveContainer,
  Sector,
  Tooltip,
} from 'recharts'

import { COLORS, CustomTooltip } from '../ChartsUtils'
import { CustomLegend } from './CustomLegend'

const renderActiveShape = ({
  cx,
  cy,
  midAngle,
  outerRadius,
  startAngle,
  endAngle,
  fill,
  percent,
  value,
  payload,
}) => {
  const RADIAN = Math.PI / 180
  const sin = Math.sin(-RADIAN * (midAngle ?? 1))
  const cos = Math.cos(-RADIAN * (midAngle ?? 1))
  const sx = (cx ?? 0) + ((outerRadius ?? 0) + 10) * cos
  const sy = (cy ?? 0) + ((outerRadius ?? 0) + 10) * sin
  const mx = (cx ?? 0) + ((outerRadius ?? 0) + 30) * cos
  const my = (cy ?? 0) + ((outerRadius ?? 0) + 30) * sin
  const ex = mx + (cos >= 0 ? 1 : -1) * 22
  const ey = my
  const textAnchor = cos >= 0 ? 'start' : 'end'

  // Use percentage from payload if available, otherwise fall back to Recharts calculated percent
  const displayPercentage = payload?.percentage
    ? parseFloat(payload.percentage).toFixed(2)
    : ((percent ?? 1) * 100).toFixed(2)

  return (
    <g>
      <Sector
        cx={cx}
        cy={cy}
        startAngle={startAngle}
        endAngle={endAngle}
        innerRadius={(outerRadius ?? 0) + 6}
        outerRadius={(outerRadius ?? 0) + 10}
        fill={fill}
      />
      <path
        d={`M${sx},${sy}L${mx},${my}L${ex},${ey}`}
        stroke={fill}
        fill="none"
      />
      <circle cx={ex} cy={ey} r={2} fill={fill} stroke="none" />
      <text
        x={ex + (cos >= 0 ? 1 : -1) * 12}
        y={ey}
        textAnchor={textAnchor}
        className="active-shape-value"
      >{`${value}`}</text>
      <text
        x={ex + (cos >= 0 ? 1 : -1) * 12}
        y={ey}
        dy={18}
        textAnchor={textAnchor}
        className="active-shape-percent-value"
      >
        {`(${displayPercentage}%)`}
      </text>
    </g>
  )
}

export const DoughnutChart = ({ data }) => {
  return (
    <ResponsiveContainer minHeight={500} width="100%" height="100%">
      <RechartsPieChart>
        <Pie
          data={data}
          cx="50%"
          cy="50%"
          outerRadius={150}
          innerRadius={120}
          dataKey="value"
          label={renderActiveShape}
          nameKey="title"
          fill="#8884d8"
        >
          {data.map((_, index) => (
            <Cell
              key={`peie-cell-${index}`}
              fill={COLORS[index % COLORS.length]}
            />
          ))}
        </Pie>
        <Tooltip cursor={{ fill: '#eeeff7' }} content={CustomTooltip} />
        <Legend content={CustomLegend} />
      </RechartsPieChart>
    </ResponsiveContainer>
  )
}
