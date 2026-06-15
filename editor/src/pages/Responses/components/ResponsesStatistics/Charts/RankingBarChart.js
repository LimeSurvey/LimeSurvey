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
  <text className="chart-labels" x={x} y={y - 8}>
    {value}
    <title>{value}</title>
  </text>
)

const ClickHintTooltip = ({ active }) => {
  if (!active) return null
  return (
    <div className="ranking-tooltip">
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
    <div className="ranking-leaderboard">
      <div
        className="ranking-leaderboard-badges"
        style={{ paddingTop: CHART_TOP_MARGIN }}
      >
        {ranked.map((entry, index) => (
          <div
            key={`ranking-badge-${index}`}
            className="ranking-leaderboard-badge-row"
            style={{ height: ROW_HEIGHT }}
          >
            <span className="ranking-leaderboard-badge">{entry.position}</span>
          </div>
        ))}
      </div>

      <div className="ranking-leaderboard-chart">
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
                className="chart-labels"
              />
            </Bar>
          </RechartsBarChart>
        </ResponsiveContainer>
      </div>

      <StatisticsDetailModal
        show={!!selectedKey}
        onHide={() => setSelectedKey(null)}
        modalClassname="ranking-modal"
        title={title}
      >
        <StatisticsFilterSelect
          label={t('See all rankings for:')}
          options={ranked}
          value={selectedKey}
          onChange={setSelectedKey}
        />
        {selectedOption && (
          <table className="ranking-modal-table">
            <thead>
              <tr>
                <th>{t('Ranking')}</th>
                <th>{t('Responses')}</th>
              </tr>
            </thead>
            <tbody>
              {(selectedOption.ranks ?? []).map((rank) => (
                <tr key={`rank-${rank.position}`}>
                  <td>{ordinal(rank.position)}</td>
                  <td>{rank.value}</td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </StatisticsDetailModal>
    </div>
  )
}
