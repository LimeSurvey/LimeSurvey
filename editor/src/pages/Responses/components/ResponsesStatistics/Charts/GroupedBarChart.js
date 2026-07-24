import React from 'react'
import {
  BarChart as RechartsBarChart,
  Bar,
  XAxis,
  YAxis,
  Cell,
  LabelList,
  Tooltip,
  ResponsiveContainer,
} from 'recharts'

import {
  COLORS,
  CustomTooltip,
  getMetricDataKey,
  TooltipShell,
  VALUE_TYPE,
} from '../ChartsUtils'

const GroupedTooltip = ({ active, payload, categoryTitle }) => {
  if (!active || !payload?.length) return null
  const option = payload[0]?.payload ?? {}
  const stats = option.stats
  if (!stats) {
    return <CustomTooltip active={active} payload={payload} />
  }
  return (
    <TooltipShell>
      <div style={{ fontWeight: 600, marginBottom: 4 }}>
        {categoryTitle} - {option.title}
      </div>
      <div>
        {t('Mean')}: {stats.mean}
      </div>
      <div>
        {t('Median')}: {stats.median}
      </div>
      <div>
        {t('Min/Max')}: {stats.min} - {stats.max}
      </div>
    </TooltipShell>
  )
}

// Each bar gets a label above it + the bar itself, so the row is taller than a
// plain bar; height sizes the small chart to its option count.
const ROW_HEIGHT = 46
const CHART_PADDING = 16
// Secondary text colour ($g-700) for the per-bar labels and values.
const LABEL_COLOR = '#6e748c'

const formatMetric = (value, isPercentage) =>
  isPercentage ? `${Math.round(value)}%` : `${value}`

// Column/option name rendered just above its bar (recharts passes the bar's
// top-left x/y), matching the responses chart layout.
const renderTopLabel = ({ x, y, value }) => {
  if (x == null || y == null) return null
  return (
    <text x={x} y={y - 6} fill={LABEL_COLOR} fontSize={12} textAnchor="start">
      {value}
    </text>
  )
}

// One subquestion: its name on the left, then a slim horizontal bar per option
// (or column), each labelled above with its name and to the right with its
// value, and coloured per option.
const CategoryBarChart = ({ category, isPercentage, dataKey, domainMax }) => {
  const { title, options } = category
  const height = options.length * ROW_HEIGHT + CHART_PADDING

  return (
    <div className="responses-statistics-bar-chart__category">
      <div className="responses-statistics-bar-chart__category-label">
        {title}
      </div>
      <div
        className="responses-statistics-bar-chart__category-chart"
        style={{ height }}
      >
        <ResponsiveContainer width="100%" height="100%">
          <RechartsBarChart
            data={options}
            layout="vertical"
            margin={{ top: 16, right: 40, bottom: 0, left: 0 }}
            barCategoryGap={10}
          >
            <XAxis type="number" hide domain={[0, domainMax]} />
            <YAxis type="category" dataKey="title" hide />
            <Tooltip
              cursor={{ fill: '#eeeff7' }}
              content={<GroupedTooltip categoryTitle={title} />}
            />
            <Bar dataKey={dataKey} barSize={16} isAnimationActive={false}>
              {options.map((_, index) => (
                <Cell
                  key={`bar-cell-${index}`}
                  fill={COLORS[index % COLORS.length]}
                />
              ))}
              <LabelList dataKey="title" content={renderTopLabel} />
              <LabelList
                dataKey={dataKey}
                position="right"
                formatter={(value) => formatMetric(value, isPercentage)}
                style={{ fontSize: 12, fill: LABEL_COLOR }}
              />
            </Bar>
          </RechartsBarChart>
        </ResponsiveContainer>
      </div>
    </div>
  )
}

export const GroupedBarChart = ({
  data = [],
  valueType = VALUE_TYPE.PERCENTAGE,
}) => {
  const isPercentage = valueType === VALUE_TYPE.PERCENTAGE
  const dataKey = getMetricDataKey(valueType)

  // Share one x-domain across every subquestion so bar lengths stay comparable.
  const domainMax = isPercentage
    ? 100
    : Math.max(
        1,
        ...data.flatMap((category) =>
          (category.options ?? []).map((option) => option.value || 0)
        )
      )

  return (
    <div className="responses-statistics-bar-chart">
      {data.map((category) => (
        <CategoryBarChart
          key={category.key ?? category.title}
          category={category}
          isPercentage={isPercentage}
          dataKey={dataKey}
          domainMax={domainMax}
        />
      ))}
    </div>
  )
}
