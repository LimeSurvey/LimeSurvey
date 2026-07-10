import React from 'react'
import {
  BarChart as RechartsBarChart,
  Bar,
  XAxis,
  YAxis,
  Tooltip,
  ResponsiveContainer,
} from 'recharts'

import {
  getUnionSegments,
  TooltipShell,
  TooltipMetricLines,
  VALUE_TYPE,
} from '../ChartsUtils'
import { StackedLegend } from './StackedLegend'

const BAR_SIZE = 14
const BAR_HEIGHT = 20

// "Price - Importance : 2" plus the shared count/percentage lines.
const DualScaleTooltip = ({ active, payload, row }) => {
  if (!active || !payload?.length) return null
  const item = payload[0]
  const meta = item.payload?.__meta?.[item.dataKey] ?? {}
  return (
    <TooltipShell>
      <div style={{ fontWeight: 600, marginBottom: 4 }}>
        {row.title} - {row.scaleTitle} : {meta.title ?? item.dataKey}
      </div>
      <TooltipMetricLines
        count={meta.count ?? 0}
        percentage={(meta.percentage ?? 0).toFixed(1)}
      />
    </TooltipShell>
  )
}

const ScaleRow = ({ row, isPercentage, domainMax, colorByTitle }) => {
  const segments = row.segments ?? []

  const flat = { statement: row.key ?? row.scaleTitle, __meta: {} }
  segments.forEach((segment) => {
    const percentage = segment.percentage ?? 0
    flat[segment.key] = isPercentage ? percentage : segment.value
    flat.__meta[segment.key] = {
      count: segment.value,
      percentage,
      title: segment.title,
    }
  })

  return (
    <div className="responses-statistics-dual-scale-row">
      <div className="responses-statistics-dual-scale-row-caption">
        {row.scaleTitle}
      </div>
      <div style={{ height: BAR_HEIGHT }}>
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
              content={<DualScaleTooltip row={row} />}
            />
            {segments.map((segment) => (
              <Bar
                key={segment.key}
                dataKey={segment.key}
                stackId="stack"
                fill={colorByTitle[segment.title]}
                barSize={BAR_SIZE}
                isAnimationActive={false}
              />
            ))}
          </RechartsBarChart>
        </ResponsiveContainer>
      </div>
    </div>
  )
}

/**
 * Array dual scale: rows arrive as subquestion × scale pairs (`scaleTitle`
 * distinguishes them). Each subquestion renders as a group — its name on the
 * left, one slim stacked bar per scale on the right — under the shared
 * distribution legend spanning both scales' options.
 */
export const DualScaleStackedBarChart = ({
  data = [],
  valueType = VALUE_TYPE.PERCENTAGE,
}) => {
  const isPercentage = valueType === VALUE_TYPE.PERCENTAGE

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
              />
            ))}
          </div>
        </div>
      ))}
    </div>
  )
}
