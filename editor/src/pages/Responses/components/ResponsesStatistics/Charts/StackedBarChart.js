import React from 'react'
import {
  BarChart as RechartsBarChart,
  Bar,
  XAxis,
  YAxis,
  Tooltip,
  LabelList,
  ResponsiveContainer,
} from 'recharts'

import {
  COLORS,
  TooltipShell,
  TooltipMetricLines,
  TruncatedTick,
  VALUE_TYPE,
} from '../ChartsUtils'
import { StackedLegend } from './StackedLegend'

// Each row band is BAR_SIZE + ROW_GAP tall, so adjacent bars sit ROW_GAP apart
// and the first/last bars keep a ROW_GAP/2 padding inside the chart.
const BAR_SIZE = 24
const ROW_GAP = 20
const ROW_HEIGHT = BAR_SIZE + ROW_GAP

// Pick a readable label colour for a given segment fill (white on dark fills).
const isDarkFill = (hex) => {
  const c = String(hex || '').replace('#', '')
  if (c.length < 6) return false
  const r = parseInt(c.slice(0, 2), 16)
  const g = parseInt(c.slice(2, 4), 16)
  const b = parseInt(c.slice(4, 6), 16)
  return 0.299 * r + 0.587 * g + 0.114 * b < 140
}

const formatValue = (value, isPercentage) =>
  isPercentage ? `${Math.round(value)}%` : `${value}`

// Per-segment tooltip in the shared dark style: the hovered scale point's
// title plus its count and within-subquestion percentage.
const StackedTooltip = ({ active, payload, titleByKey = {} }) => {
  if (!active || !payload?.length) return null
  const item = payload[0]
  const meta = item.payload?.__meta?.[item.dataKey] ?? {}
  const count = meta.count ?? 0
  const percentage = meta.percentage != null ? meta.percentage.toFixed(1) : '0'
  return (
    <TooltipShell>
      <div style={{ fontWeight: 600, marginBottom: 4 }}>
        {titleByKey[item.dataKey] ?? item.dataKey}
      </div>
      <TooltipMetricLines count={count} percentage={percentage} />
    </TooltipShell>
  )
}

// Label centered in each segment; hidden on segments too narrow to fit text.
const renderSegmentLabel = (segment, isPercentage) => {
  const SegmentLabel = ({ x, y, width, height, value }) => {
    if (!value || width < 24) return null
    return (
      <text
        x={x + width / 2}
        y={y + height / 2}
        fill={isDarkFill(segment.color) ? '#fff' : '#1d2b33'}
        fontSize={12}
        fontWeight={600}
        textAnchor="middle"
        dominantBaseline="central"
      >
        {formatValue(value, isPercentage)}
      </text>
    )
  }
  SegmentLabel.displayName = 'StackedSegmentLabel'
  return SegmentLabel
}

export const StackedBarChart = ({
  data = [],
  valueType = VALUE_TYPE.PERCENTAGE,
}) => {
  const isPercentage = valueType === VALUE_TYPE.PERCENTAGE

  // Segments are consistent across rows; derive their order/labels from the
  // first row and assign a stable colour per segment.
  const segments = (data[0]?.segments ?? []).map((segment, index) => ({
    key: segment.key,
    title: segment.title,
    color: COLORS[index % COLORS.length],
  }))
  const titleByKey = segments.reduce((acc, segment) => {
    acc[segment.key] = segment.title
    return acc
  }, {})

  // Flatten each row to { statement, <segmentKey>: value } for recharts, where
  // the value is either the raw count or the DTO's within-row percentage.
  const rows = data.map((row) => {
    // `__meta` carries the raw count + percentage per segment so the tooltip can
    // show both regardless of which metric drives the bar widths.
    const flat = { statement: row.title, __meta: {} }
    ;(row.segments ?? []).forEach((segment) => {
      const percentage = segment.percentage ?? 0
      flat[segment.key] = isPercentage ? percentage : segment.value
      flat.__meta[segment.key] = { count: segment.value, percentage }
    })
    return flat
  })

  const chartHeight = Math.max(rows.length, 1) * ROW_HEIGHT

  return (
    <div className="responses-statistics-stacked">
      <StackedLegend segments={segments} />
      <ResponsiveContainer width="100%" height={chartHeight}>
        <RechartsBarChart
          data={rows}
          layout="vertical"
          margin={{ top: 0, right: 16, left: 8, bottom: 0 }}
        >
          <XAxis
            type="number"
            domain={isPercentage ? [0, 100] : [0, 'dataMax']}
            hide
          />
          <YAxis
            type="category"
            dataKey="statement"
            width={180}
            tickLine={false}
            axisLine={false}
            tick={<TruncatedTick textAnchor="end" dy={4} />}
          />
          <Tooltip
            cursor={{ fill: '#eeeff7' }}
            shared={false}
            content={<StackedTooltip titleByKey={titleByKey} />}
          />
          {segments.map((segment) => (
            <Bar
              key={segment.key}
              dataKey={segment.key}
              stackId="stack"
              fill={segment.color}
              barSize={BAR_SIZE}
            >
              <LabelList
                dataKey={segment.key}
                content={renderSegmentLabel(segment, isPercentage)}
              />
            </Bar>
          ))}
        </RechartsBarChart>
      </ResponsiveContainer>
    </div>
  )
}
