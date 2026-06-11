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

import { COLORS, CustomTooltip, VALUE_TYPE } from '../ChartsUtils'
import { CustomLegend } from './CustomLegend'

const LABEL_IMAGE_SIZE = 40

const RADIAN = Math.PI / 180

const renderActiveShapeOld = ({
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
  const sin = Math.sin(-RADIAN * (midAngle ?? 1))
  const cos = Math.cos(-RADIAN * (midAngle ?? 1))
  const sx = (cx ?? 0) + ((outerRadius ?? 0) + 10) * cos
  const sy = (cy ?? 0) + ((outerRadius ?? 0) + 10) * sin
  const mx = (cx ?? 0) + ((outerRadius ?? 0) + 30) * cos
  const my = (cy ?? 0) + ((outerRadius ?? 0) + 30) * sin
  const ex = mx + (cos >= 0 ? 1 : -1) * 22
  const ey = my
  const textAnchor = cos >= 0 ? 'start' : 'end'

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

const renderActiveShapeNew = ({
  cx,
  cy,
  midAngle,
  outerRadius,
  fill,
  percent,
  payload,
  name,
  value,
  valueType = VALUE_TYPE.PERCENTAGE,
  isImage = false,
}) => {
  const cos = Math.cos(-RADIAN * (midAngle ?? 1))
  const sin = Math.sin(-RADIAN * (midAngle ?? 1))
  const isRight = cos >= 0

  const r = outerRadius ?? 0
  const sx = (cx ?? 0) + r * cos
  const sy = (cy ?? 0) + r * sin
  const mx = (cx ?? 0) + (r + 20) * cos
  const my = (cy ?? 0) + (r + 20) * sin
  const ex = mx + (isRight ? 26 : -26)
  const ey = my
  const tx = ex + (isRight ? 10 : -10)
  const anchor = isRight ? 'start' : 'end'

  const id = payload?.id
  const isOther = payload?.isOther
  const displayPercentage = payload?.percentage
    ? parseFloat(payload.percentage).toFixed(1).replace('.', ',')
    : ((percent ?? 0) * 100).toFixed(1).replace('.', ',')
  const displayMetric =
    valueType === VALUE_TYPE.COUNT
      ? `${value ?? payload?.value ?? ''}`
      : `${displayPercentage}%`

  return (
    <g>
      <path
        d={`M${sx},${sy} L${mx},${my} L${ex},${ey}`}
        stroke={fill}
        strokeWidth={1.5}
        fill="none"
        strokeDasharray={isOther ? '3 3' : undefined}
      />
      <circle cx={ex} cy={ey} r={4} fill={fill} />

      {id && (
        <text
          x={tx}
          y={ey - 22}
          textAnchor={anchor}
          className="active-shape-id"
        >
          <tspan>{id}</tspan>
          {isOther && <tspan fontStyle="italic"> - Other</tspan>}
        </text>
      )}

      {isImage ? (
        <image
          href={name}
          x={isRight ? tx : tx - LABEL_IMAGE_SIZE}
          y={(id ? ey - 2 : ey - 8) - LABEL_IMAGE_SIZE / 2}
          width={LABEL_IMAGE_SIZE}
          height={LABEL_IMAGE_SIZE}
          preserveAspectRatio="xMidYMid meet"
        >
          <title>{name}</title>
        </image>
      ) : (
        <text
          x={tx}
          y={id ? ey - 2 : ey - 8}
          textAnchor={anchor}
          className="active-shape-name"
        >
          {name}
        </text>
      )}

      <text
        x={tx}
        y={id ? ey + 18 : ey + 12}
        textAnchor={anchor}
        className="active-shape-percent-value"
      >
        {displayMetric}
      </text>
    </g>
  )
}

export const PieChart = ({
  data,
  newLabels = true,
  valueType = VALUE_TYPE.PERCENTAGE,
  isImage = false,
}) => {
  const renderLabel = (props) =>
    (newLabels ? renderActiveShapeNew : renderActiveShapeOld)({
      ...props,
      valueType,
      isImage,
    })

  return (
    <ResponsiveContainer minHeight={500} width="100%" height="100%">
      <RechartsPieChart
        margin={
          newLabels ? { top: 60, right: 160, bottom: 60, left: 160 } : undefined
        }
      >
        <Pie
          data={data}
          cx="50%"
          cy="50%"
          dataKey="value"
          nameKey="title"
          label={renderLabel}
          labelLine={newLabels ? false : undefined}
          outerRadius={newLabels ? '60%' : undefined}
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
        <Legend
          content={(legendProps) => (
            <CustomLegend {...legendProps} isImage={isImage} />
          )}
        />
      </RechartsPieChart>
    </ResponsiveContainer>
  )
}
