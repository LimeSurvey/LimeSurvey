import React from 'react'
import classNames from 'classnames'
import {
  BarChart as RechartsBarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Cell,
  LabelList,
  Tooltip,
  ResponsiveContainer,
} from 'recharts'

import {
  BAR_MAX_SIZE,
  COLORS,
  CustomTooltip,
  formatMetricValue,
  getBarChartMinWidth,
  getMetricDataKey,
  TooltipShell,
  TruncatedTick,
  VALUE_TYPE,
} from '../ChartsUtils'
import { ORIENTATION } from '../ChartOrientationMenu'
import { StackedLegend } from './StackedLegend'

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
        {t('Minimum/Maximum')}: {stats.min} - {stats.max}
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
const CategoryBarChart = ({ category, valueType, dataKey, domainMax }) => {
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
                formatter={(value) => formatMetricValue(value, valueType)}
                style={{ fontSize: 12, fill: LABEL_COLOR }}
              />
            </Bar>
          </RechartsBarChart>
        </ResponsiveContainer>
      </div>
    </div>
  )
}

const VERTICAL_CHART_HEIGHT = 400

// Vertical layout: one chart with the subquestions along the x-axis and, for
// each, one bar per option (or column), coloured per option as in the legend.
const VerticalGroupedChart = ({ data, valueType, dataKey }) => {
  const options = (data[0]?.options ?? []).map((option, index) => ({
    key: `option-${index}`,
    title: option.title,
    color: COLORS[index % COLORS.length],
  }))
  const rows = data.map((category) => {
    const row = { title: category.title, __options: {} }
    ;(category.options ?? []).forEach((option, index) => {
      row[`option-${index}`] = option[dataKey]
      row.__options[`option-${index}`] = option
    })
    return row
  })
  const chartMinWidth = getBarChartMinWidth(rows.length * options.length)
  const isPercentage = valueType === VALUE_TYPE.PERCENTAGE
  const legendOptions = options.filter(({ title }) => title)

  // The hovered bar's option, in the shape GroupedTooltip expects.
  const renderTooltip = ({ active, payload }) => {
    const item = payload?.[0]
    const option = item?.payload?.__options?.[item.dataKey]
    if (!active || !option) return null
    return (
      <GroupedTooltip
        active={active}
        payload={[{ ...item, payload: option }]}
        categoryTitle={item.payload.title}
      />
    )
  }

  return (
    <div className="responses-statistics-grouped-vertical">
      {legendOptions.length > 0 && (
        <StackedLegend segments={legendOptions} showTitle={false} />
      )}
      <div
        className={classNames('responses-statistics-bar-chart-viewport', {
          'responses-statistics-bar-chart-viewport--scrollable': chartMinWidth,
        })}
        tabIndex={chartMinWidth ? 0 : undefined}
        aria-label={chartMinWidth ? t('Scrollable chart') : undefined}
      >
        <div
          className="responses-statistics-bar-chart"
          style={chartMinWidth ? { minWidth: chartMinWidth } : undefined}
        >
          <ResponsiveContainer width="100%" height={VERTICAL_CHART_HEIGHT}>
            <RechartsBarChart data={rows} margin={{ top: 20, right: 20 }}>
              <CartesianGrid strokeDasharray="3 3" vertical={false} />
              <XAxis
                dataKey="title"
                height={40}
                interval={0}
                tick={<TruncatedTick />}
              />
              <YAxis
                domain={isPercentage ? [0, 100] : [0, 'auto']}
                unit={isPercentage ? '%' : undefined}
              />
              <Tooltip
                cursor={{ fill: '#eeeff7' }}
                shared={false}
                content={renderTooltip}
              />
              {options.map((option) => (
                <Bar
                  key={option.key}
                  dataKey={option.key}
                  name={option.title}
                  fill={option.color}
                  maxBarSize={BAR_MAX_SIZE}
                  isAnimationActive={false}
                >
                  <LabelList
                    dataKey={option.key}
                    position="top"
                    // Like the bar chart, empty bars get no "0" label.
                    formatter={(value) =>
                      value == null || Number(value) === 0
                        ? ''
                        : formatMetricValue(value, valueType)
                    }
                    style={{ fontSize: 12, fill: LABEL_COLOR }}
                  />
                </Bar>
              ))}
            </RechartsBarChart>
          </ResponsiveContainer>
        </div>
      </div>
    </div>
  )
}

export const GroupedBarChart = ({
  data = [],
  valueType = VALUE_TYPE.PERCENTAGE,
  orientation = ORIENTATION.HORIZONTAL,
}) => {
  const isPercentage = valueType === VALUE_TYPE.PERCENTAGE
  const dataKey = getMetricDataKey(valueType)

  // Share one x-domain across every subquestion so bar lengths stay comparable.
  const domainMax = isPercentage
    ? 100
    : data.reduce(
        (max, category) =>
          (category.options ?? []).reduce(
            (categoryMax, option) => Math.max(categoryMax, option.value || 0),
            max
          ),
        1
      )

  if (orientation === ORIENTATION.VERTICAL) {
    return (
      <VerticalGroupedChart
        data={data}
        valueType={valueType}
        dataKey={dataKey}
      />
    )
  }

  return (
    <div className="responses-statistics-bar-chart">
      {data.map((category) => (
        <CategoryBarChart
          key={category.key ?? category.title}
          category={category}
          valueType={valueType}
          dataKey={dataKey}
          domainMax={domainMax}
        />
      ))}
    </div>
  )
}
