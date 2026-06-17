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

import { ordinal } from '../ChartsUtils'
import { StatisticsDetailModal } from '../StatisticsDetailModal.js'
import { StatisticsFilterSelect } from '../StatisticsFilterSelect.js'

// One chart row per option; the chart top margin keeps the first bar's name
// label from clipping.
const ROW_HEIGHT = 56
const CHART_TOP_MARGIN = 22

// Reserved width (left of the bars) for the rank badge column.
const BADGE_COLUMN_WIDTH = 52
const BADGE_HEIGHT = 36

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

const ClickHintTooltip = ({ active }) => {
  if (!active) return null
  return (
    <div className="responses-statistics-ranking-tooltip">
      <i className="ri-eye-line" />
      <span>{t('Click to view all rankings for this option')}</span>
    </div>
  )
}

export const RankingBarChart = ({ data = [], title = '' }) => {
  const [selectedKey, setSelectedKey] = useState(null)

  const ranked = [...data]
    .sort((a, b) => (b.value ?? 0) - (a.value ?? 0))
    .map((item, index) => ({ ...item, position: ordinal(index + 1) }))

  const selectedOption = ranked.find((option) => option.key === selectedKey)

  const chartHeight = Math.max(ranked.length, 1) * ROW_HEIGHT + CHART_TOP_MARGIN

  return (
    <div className="responses-statistics-ranking">
      <div className="responses-statistics-ranking-chart">
        <ResponsiveContainer width="100%" height={chartHeight}>
          <RechartsBarChart
            layout="vertical"
            data={ranked}
            margin={{ top: CHART_TOP_MARGIN, right: 90, left: 0, bottom: 0 }}
          >
            <XAxis type="number" hide domain={[0, 'dataMax']} />
            <YAxis
              type="category"
              dataKey="title"
              width={BADGE_COLUMN_WIDTH}
              tickLine={false}
              axisLine={false}
              tick={(props) => <RankBadgeTick {...props} ranked={ranked} />}
            />
            <Tooltip
              cursor={{ fill: '#eeeff7' }}
              content={<ClickHintTooltip />}
            />
            <Bar
              dataKey="value"
              radius={[0, 4, 4, 0]}
              barSize={22}
              onClick={(entry) => setSelectedKey(entry.key)}
            >
              {ranked.map((entry, index) => (
                <Cell key={`ranking-cell-${index}`} fill={entry.fill} />
              ))}
              <LabelList dataKey="title" content={<NameLabel />} />
              <LabelList
                dataKey="value"
                position="right"
                offset={16}
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
        <h2 className="responses-statistics-modal-title">{title}</h2>
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
                  {rank.value} {rank.value === 1 ? t('vote') : t('votes')}
                </span>
              </div>
            ))}
          </div>
        )}
      </StatisticsDetailModal>
    </div>
  )
}
