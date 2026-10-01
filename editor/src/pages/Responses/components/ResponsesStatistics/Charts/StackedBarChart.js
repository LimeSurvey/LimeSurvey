import React from 'react'
import classNames from 'classnames'
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
  getUnionSegments,
  TooltipShell,
  TooltipMetricLines,
  TruncatedTick,
  VALUE_TYPE,
} from '../ChartsUtils'
import { StackedLegend } from './StackedLegend'

const BAR_SIZE = 24
const ROW_GAP = 20
const ROW_HEIGHT = BAR_SIZE + ROW_GAP

const DUAL_BAR_SIZE = 14
const DUAL_BAR_HEIGHT = 20

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

const flattenRow = (row, isPercentage) => {
  const flat = {
    statement: row.title,
    __row: { title: row.title, scaleTitle: row.scaleTitle },
    __meta: {},
  }
  ;(row.segments ?? []).forEach((segment) => {
    const percentage = segment.percentage ?? 0
    flat[segment.key] = isPercentage ? percentage : segment.value
    flat.__meta[segment.key] = {
      count: segment.value,
      percentage,
      title: segment.title,
    }
  })
  return flat
}

// Per-segment tooltip in the shared dark style: "<subquestion> - <scale
// point>" (dual scale: "<subquestion> - <scale> : <point>") plus its count
// and within-row percentage.
const StackedTooltip = ({ active, payload }) => {
  if (!active || !payload?.length) return null
  const item = payload[0]
  const meta = item.payload?.__meta?.[item.dataKey] ?? {}
  const rowInfo = item.payload?.__row ?? {}
  const segmentTitle = meta.title ?? item.dataKey
  const heading = rowInfo.scaleTitle
    ? `${rowInfo.title} - ${rowInfo.scaleTitle} : ${segmentTitle}`
    : [rowInfo.title, segmentTitle].filter(Boolean).join(' - ')
  const percentage = meta.percentage != null ? meta.percentage.toFixed(1) : '0'
  return (
    <TooltipShell>
      <div style={{ fontWeight: 600, marginBottom: 4 }}>{heading}</div>
      <TooltipMetricLines count={meta.count ?? 0} percentage={percentage} />
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
        // Let hovers fall through to the bar so the tooltip stays visible.
        pointerEvents="none"
      >
        {formatValue(value, isPercentage)}
      </text>
    )
  }
  SegmentLabel.displayName = 'StackedSegmentLabel'
  return SegmentLabel
}

// Plain arrays: every row shares the same segments, so all rows render in a
// single vertical chart with the subquestions on the y-axis.
const SingleStackedChart = ({ data, isPercentage }) => {
  const segments = (data[0]?.segments ?? []).map((segment, index) => ({
    key: segment.key,
    title: segment.title,
    color: COLORS[index % COLORS.length],
  }))
  const rows = data.map((row) => flattenRow(row, isPercentage))
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
            content={<StackedTooltip />}
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

const ScaleRow = ({
  row,
  isPercentage,
  domainMax,
  colorByTitle,
  labelPlacement,
}) => {
  const segments = row.segments ?? []
  const flat = flattenRow(row, isPercentage)

  return (
    <div
      className={classNames('responses-statistics-dual-scale-row', {
        'responses-statistics-dual-scale-row--labels-left':
          labelPlacement === 'left',
      })}
    >
      <div className="responses-statistics-dual-scale-row-caption">
        {row.scaleTitle}
      </div>
      <div
        className="responses-statistics-dual-scale-row-chart"
        style={{ height: DUAL_BAR_HEIGHT }}
      >
        <ResponsiveContainer width="100%" height="100%">
          <RechartsBarChart
            data={[flat]}
            layout="vertical"
            margin={{ top: 0, right: 0, left: 0, bottom: 0 }}
          >
            <XAxis
              type="number"
              domain={isPercentage ? [0, 100] : [0, domainMax]}
              hide
            />
            <YAxis type="category" dataKey="statement" hide />
            <Tooltip
              cursor={{ fill: '#eeeff7' }}
              shared={false}
              content={<StackedTooltip />}
            />
            {segments.map((segment) => (
              <Bar
                key={segment.key}
                dataKey={segment.key}
                stackId="stack"
                fill={colorByTitle[segment.title]}
                barSize={DUAL_BAR_SIZE}
                isAnimationActive={false}
              />
            ))}
          </RechartsBarChart>
        </ResponsiveContainer>
      </div>
    </div>
  )
}

const DualScaleChart = ({ data, isPercentage, labelPlacement }) => {
  const unionSegments = getUnionSegments(data)
  const colorByTitle = unionSegments.reduce((acc, segment) => {
    acc[segment.title] = segment.color
    return acc
  }, {})

  // Count mode shares one x-domain so bar lengths stay comparable across rows;
  // `row.value` is the DTO's per-row response total.
  const domainMax = Math.max(1, ...data.map((row) => row.value || 0))

  // Consecutive rows with the same subquestion title form one group
  // (the backend emits them scale A / scale B adjacent).
  const groups = []
  data.forEach((row) => {
    const last = groups[groups.length - 1]
    if (last && last.title === row.title) {
      last.rows.push(row)
    } else {
      groups.push({ title: row.title, rows: [row] })
    }
  })

  return (
    <div className="responses-statistics-dual-scale">
      <StackedLegend segments={unionSegments} />
      {groups.map((group) => (
        <div
          key={group.title}
          className="responses-statistics-dual-scale-group"
        >
          <div className="responses-statistics-dual-scale-group-label">
            {group.title}
          </div>
          <div className="responses-statistics-dual-scale-group-bars">
            {group.rows.map((row) => (
              <ScaleRow
                key={row.key ?? row.scaleTitle}
                row={row}
                isPercentage={isPercentage}
                domainMax={domainMax}
                colorByTitle={colorByTitle}
                labelPlacement={labelPlacement}
              />
            ))}
          </div>
        </div>
      ))}
    </div>
  )
}

/**
 * Stacked distribution bars for array questions.
 *
 * `dualScale` switches from the single chart (one row per subquestion) to the
 * grouped layout for dual-scale arrays (one slim bar per subquestion × scale,
 * grouped under the subquestion's name). `labelPlacement` positions each
 * scale's name in the dual layout: 'left' puts it on the same line as its bar
 * (in a fixed label column), 'top' stacks it above the bar.
 */
export const StackedBarChart = ({
  data = [],
  valueType = VALUE_TYPE.PERCENTAGE,
  dualScale = false,
  labelPlacement = 'left',
}) => {
  const isPercentage = valueType === VALUE_TYPE.PERCENTAGE

  return dualScale ? (
    <DualScaleChart
      data={data}
      isPercentage={isPercentage}
      labelPlacement={labelPlacement}
    />
  ) : (
    <SingleStackedChart data={data} isPercentage={isPercentage} />
  )
}
