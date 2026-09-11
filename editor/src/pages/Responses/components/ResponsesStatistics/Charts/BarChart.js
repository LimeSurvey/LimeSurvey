import React from 'react'
import classNames from 'classnames'
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
  BAR_MAX_SIZE,
  COLORS,
  CustomTooltip,
  TruncatedTick,
  getLabelInterval,
  getMetricDataKey,
  VALUE_TYPE,
} from '../ChartsUtils'

export const BarChart = ({
  data,
  valueType = VALUE_TYPE.PERCENTAGE,
  isImage = false,
  hasComments = false,
  onViewComments,
}) => {
  const isPercentage = valueType === VALUE_TYPE.PERCENTAGE
  const dataKey = getMetricDataKey(valueType)

  return (
    <div
      className={classNames('responses-statistics-bar-chart', {
        'responses-statistics-bar-chart--clickable': hasComments,
      })}
    >
      <ResponsiveContainer width="100%" height={400}>
        <RechartsBarChart data={data}>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis
            dataKey="title"
            // Text ticks are a single truncated line (~one line tall); image
            // labels are rendered at the bar width with auto height, so only they
            // need the extra room. An oversized text-axis height would otherwise
            // reserve an empty band at the bottom of the chart.
            height={isImage ? BAR_MAX_SIZE + 24 : 40}
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
                  isImage={isImage}
                  item={item}
                  imageWidth={BAR_MAX_SIZE}
                />
              )
            }}
          />
          <YAxis unit={isPercentage ? '%' : undefined} />
          <Tooltip
            cursor={{ fill: '#eeeff7' }}
            content={<CustomTooltip showCommentsHint={hasComments} />}
          />
          <Bar
            maxBarSize={BAR_MAX_SIZE}
            dataKey={dataKey}
            nameKey="title"
            data={data}
            onClick={
              hasComments ? (entry) => onViewComments?.(entry?.key) : undefined
            }
          >
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
    </div>
  )
}
