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

// One chart row per option. Must match the badge row height / chart top margin
// in the SCSS-driven layout so the rank badges line up with the bars.
const ROW_HEIGHT = 56
const CHART_TOP_MARGIN = 22

// Option name sitting just above its bar.
const NameLabel = ({ x, y, value }) => (
  <text className="responses-statistics-chart-labels" x={x} y={y - 8}>
    {value}
    <title>{value}</title>
  </text>
)

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
      <div
        className="responses-statistics-ranking-badges"
        style={{ paddingTop: CHART_TOP_MARGIN }}
      >
        {ranked.map((entry, index) => (
          <div
            key={`ranking-badge-${index}`}
            className="responses-statistics-ranking-badge-row"
            style={{ height: ROW_HEIGHT }}
          >
            <span className="responses-statistics-ranking-badge">{entry.position}</span>
          </div>
        ))}
      </div>

      <div className="responses-statistics-ranking-chart">
        <ResponsiveContainer width="100%" height={chartHeight}>
          <RechartsBarChart
            layout="vertical"
            data={ranked}
            margin={{ top: CHART_TOP_MARGIN, right: 90, left: 12, bottom: 0 }}
          >
            <XAxis type="number" hide domain={[0, 'dataMax']} />
            <YAxis
              type="category"
              dataKey="title"
              hide
              tick={false}
              axisLine={false}
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
        title={title}
      >
        <StatisticsFilterSelect
          label={t('See all rankings for:')}
          options={ranked}
          value={selectedKey}
          onChange={setSelectedKey}
        />
        {selectedOption && (
          <div className="responses-statistics-ranking-rows">
            {(selectedOption.ranks ?? []).map((rank) => (
              <div className="responses-statistics-ranking-row" key={`rank-${rank.position}`}>
                <span className="responses-statistics-ranking-badge">
                  {ordinal(rank.position)}
                </span>
                <span className="responses-statistics-ranking-row-value">
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
