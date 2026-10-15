import { format } from 'util'
import React, { useState } from 'react'
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

import { BAR_MAX_SIZE, ordinal } from '../ChartsUtils'
import { htmlToPlainText } from 'helpers'
import { StatisticsDetailModal } from '../StatisticsDetailModal.js'
import { StatisticsFilterSelect } from '../StatisticsFilterSelect.js'
import { ORIENTATION } from '../ChartOrientationMenu'

// One chart row per option; the chart top margin keeps the first bar's name
// label from clipping.
const ROW_HEIGHT = 56
const CHART_TOP_MARGIN = 22

// Reserved width (left of the bars) for the rank badge column.
const BADGE_COLUMN_WIDTH = 52
const BADGE_HEIGHT = 36

// Vertical layout: the rank badge and option name sit under each bar.
const VERTICAL_CHART_HEIGHT = 400
const COLUMN_TICK_WIDTH = 120
const COLUMN_TICK_HEIGHT = 64

// Option name sitting just above its bar.
const NameLabel = ({ x, y, value }) => (
  <text className="responses-statistics-chart-labels" x={x} y={y - 8}>
    {value}
    <title>{value}</title>
  </text>
)

// Rank badge rendered as a YAxis tick so it shares the chart's band scale and
// stays aligned with its bar (a separately-positioned column drifts because
// recharts' band scale doesn't space rows by an exact pixel height).
// Resolve the row by the tick's `index` (its position in `ranked`), not by the
// category value, since answer titles can be duplicated or empty.
const RankBadgeTick = ({ x, y, index, ranked }) => {
  const entry = ranked[index]
  if (!entry) return null
  return (
    <foreignObject
      x={x - BADGE_COLUMN_WIDTH}
      y={y - BADGE_HEIGHT / 2}
      width={BADGE_COLUMN_WIDTH}
      height={BADGE_HEIGHT}
      overflow="visible"
    >
      <div className="responses-statistics-ranking-badge-cell">
        <span className="responses-statistics-ranking-badge">
          {entry.position}
        </span>
      </div>
    </foreignObject>
  )
}

// Rank badge with the option name below it, rendered as an XAxis tick in the
// vertical layout (resolved by `index` for the same reason as RankBadgeTick).
const RankColumnTick = ({ x, y, index, ranked }) => {
  const entry = ranked[index]
  if (!entry) return null
  return (
    <foreignObject
      x={x - COLUMN_TICK_WIDTH / 2}
      y={y + 4}
      width={COLUMN_TICK_WIDTH}
      height={COLUMN_TICK_HEIGHT}
      overflow="visible"
    >
      <div className="responses-statistics-ranking-column-tick">
        <span className="responses-statistics-ranking-badge">
          {entry.position}
        </span>
        <span
          className="responses-statistics-ranking-column-name"
          title={entry.title}
        >
          {entry.title}
        </span>
      </div>
    </foreignObject>
  )
}

const ClickHintTooltip = ({ active }) => {
  if (!active) return null
  return (
    <div className="responses-statistics-ranking-tooltip">
      <i className="ri-eye-line" />
      <span>{t('Click to view all rankings for this option')}</span>
    </div>
  )
}

export const RankingBarChart = ({
  data = [],
  title = '',
  orientation = ORIENTATION.HORIZONTAL,
}) => {
  const [selectedKey, setSelectedKey] = useState(null)
  const isVertical = orientation === ORIENTATION.VERTICAL

  const ranked = [...data]
    .sort((a, b) => (b.value ?? 0) - (a.value ?? 0))
    .map((item, index) => ({ ...item, position: ordinal(index + 1) }))

  const selectedOption = ranked.find((option) => option.key === selectedKey)

  const chartHeight = isVertical
    ? VERTICAL_CHART_HEIGHT
    : Math.max(ranked.length, 1) * ROW_HEIGHT + CHART_TOP_MARGIN

  return (
    <div className="responses-statistics-ranking">
      <div className="responses-statistics-ranking-chart">
        <ResponsiveContainer width="100%" height={chartHeight}>
          <RechartsBarChart
            layout={isVertical ? 'horizontal' : 'vertical'}
            data={ranked}
            margin={
              isVertical
                ? { top: CHART_TOP_MARGIN, right: 20, left: 0, bottom: 0 }
                : { top: CHART_TOP_MARGIN, right: 90, left: 0, bottom: 0 }
            }
          >
            {isVertical ? (
              <>
                <XAxis
                  type="category"
                  dataKey="title"
                  height={COLUMN_TICK_HEIGHT + 8}
                  interval={0}
                  tickLine={false}
                  tick={(props) => (
                    <RankColumnTick {...props} ranked={ranked} />
                  )}
                />
                <YAxis type="number" hide domain={[0, 'dataMax']} />
              </>
            ) : (
              <>
                <XAxis type="number" hide domain={[0, 'dataMax']} />
                <YAxis
                  type="category"
                  dataKey="title"
                  width={BADGE_COLUMN_WIDTH}
                  tickLine={false}
                  axisLine={false}
                  tick={(props) => <RankBadgeTick {...props} ranked={ranked} />}
                />
              </>
            )}
            <Tooltip
              cursor={{ fill: '#eeeff7' }}
              content={<ClickHintTooltip />}
            />
            <Bar
              dataKey="value"
              radius={isVertical ? [4, 4, 0, 0] : [0, 4, 4, 0]}
              barSize={isVertical ? undefined : 22}
              maxBarSize={isVertical ? BAR_MAX_SIZE : undefined}
              onClick={(entry) => setSelectedKey(entry.key)}
            >
              {ranked.map((entry, index) => (
                <Cell key={`ranking-cell-${index}`} fill={entry.fill} />
              ))}
              {!isVertical && (
                <LabelList dataKey="title" content={<NameLabel />} />
              )}
              <LabelList
                dataKey="value"
                position={isVertical ? 'top' : 'right'}
                offset={isVertical ? 8 : 16}
                className="responses-statistics-chart-labels"
              />
            </Bar>
          </RechartsBarChart>
        </ResponsiveContainer>
      </div>

      <StatisticsDetailModal
        show={!!selectedKey}
        onHide={() => setSelectedKey(null)}
        modalClassname="responses-statistics-ranking-modal"
      >
        <h2 className="responses-statistics-modal-title">
          {htmlToPlainText(title)}
        </h2>
        <StatisticsFilterSelect
          label={t('See all rankings for:')}
          options={ranked}
          value={selectedKey}
          onChange={setSelectedKey}
        />
        {selectedOption && (
          <div className="responses-statistics-modal-list">
            {(selectedOption.ranks ?? []).map((rank) => (
              <div
                className="responses-statistics-modal-row"
                key={`rank-${rank.position}`}
              >
                <span className="responses-statistics-ranking-badge">
                  {ordinal(rank.position)}
                </span>
                <span>
                  {rank.value === 1
                    ? format(t('%s vote'), rank.value)
                    : format(t('%s votes'), rank.value)}
                </span>
              </div>
            ))}
          </div>
        )}
      </StatisticsDetailModal>
    </div>
  )
}
