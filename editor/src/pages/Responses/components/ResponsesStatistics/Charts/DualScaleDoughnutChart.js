import React from 'react'
import {
  PieChart as RechartsPieChart,
  Pie,
  Cell,
  Tooltip,
  ResponsiveContainer,
} from 'recharts'

import {
  getUnionSegments,
  TooltipShell,
  TooltipMetricLines,
} from '../ChartsUtils'
import { StackedLegend } from './StackedLegend'

const DOUGHNUT_SIZE = 220

// "Price - Importance : 5" plus the shared count/percentage lines.
const DoughnutTooltip = ({ active, payload, row }) => {
  if (!active || !payload?.length) return null
  const segment = payload[0]?.payload ?? {}
  return (
    <TooltipShell>
      <div style={{ fontWeight: 600, marginBottom: 4 }}>
        {row.title} - {row.scaleTitle} : {segment.title}
      </div>
      <TooltipMetricLines
        count={segment.value ?? 0}
        percentage={(segment.percentage ?? 0).toFixed(1)}
      />
    </TooltipShell>
  )
}

// One subquestion × scale ring with the subquestion and scale name centered
// in the hole.
const DoughnutCell = ({ row, colorByTitle }) => {
  const slices = (row.segments ?? []).map((segment) => ({
    ...segment,
    percentage: segment.percentage ?? 0,
  }))

  return (
    <div className="responses-statistics-dual-scale-doughnut-cell">
      <div
        className="responses-statistics-dual-scale-doughnut-chart"
        style={{ width: DOUGHNUT_SIZE, height: DOUGHNUT_SIZE }}
      >
        <ResponsiveContainer width="100%" height="100%">
          <RechartsPieChart>
            <Pie
              data={slices}
              dataKey="value"
              nameKey="title"
              innerRadius="62%"
              outerRadius="88%"
              startAngle={90}
              endAngle={-270}
              isAnimationActive={false}
            >
              {slices.map((segment) => (
                <Cell
                  key={segment.key}
                  fill={colorByTitle[segment.title]}
                  stroke="#fff"
                />
              ))}
            </Pie>
            <Tooltip content={<DoughnutTooltip row={row} />} />
          </RechartsPieChart>
        </ResponsiveContainer>
        <div className="responses-statistics-dual-scale-doughnut-center">
          <div className="responses-statistics-dual-scale-doughnut-title">
            {row.title}
          </div>
          <div className="responses-statistics-dual-scale-doughnut-scale">
            {row.scaleTitle}
          </div>
        </div>
      </div>
    </div>
  )
}

/**
 * Array dual scale doughnut view: one ring per subquestion × scale row, laid
 * out two per line (scale A next to scale B, matching the row order the
 * backend emits), under the shared distribution legend.
 */
export const DualScaleDoughnutChart = ({ data = [] }) => {
  const unionSegments = getUnionSegments(data)
  const colorByTitle = unionSegments.reduce((acc, segment) => {
    acc[segment.title] = segment.color
    return acc
  }, {})

  return (
    <div className="responses-statistics-dual-scale">
      <StackedLegend segments={unionSegments} />
      <div className="responses-statistics-dual-scale-doughnut-grid">
        {data.map((row) => (
          <DoughnutCell
            key={row.key ?? `${row.title}-${row.scaleTitle}`}
            row={row}
            colorByTitle={colorByTitle}
          />
        ))}
      </div>
    </div>
  )
}
