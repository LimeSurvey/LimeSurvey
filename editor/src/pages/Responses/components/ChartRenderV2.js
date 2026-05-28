import { useState } from 'react'
import classNames from 'classnames'
import { Card } from 'react-bootstrap'

import { Collapsible, ToggleButtons } from 'components'

import { ChartHeader } from './Statistics/Components/ChartHeader.js'
import { StatisticsTable } from './Statistics/Components/StatisticsTable.js'
import { BarChart, PieChart } from './ResponsesStatistics/Charts/index.js'

const VIEW = {
  BAR_CHART: 'bar-chart',
  PIE_CHART: 'pie-chart',
  TABLE: 'table',
}

const viewOptions = [
  {
    icon: () => <i className="ri-bar-chart-2-line"></i>,
    value: VIEW.BAR_CHART,
  },
  {
    icon: () => <i className="ri-pie-chart-line"></i>,
    value: VIEW.PIE_CHART,
  },
  {
    icon: () => <i className="ri-table-line"></i>,
    value: VIEW.TABLE,
  },
]

const HIDDEN_CHARTS_STORAGE_KEY = 'responses-statistics-hidden-charts'

const readHiddenCharts = () => {
  try {
    return JSON.parse(localStorage.getItem(HIDDEN_CHARTS_STORAGE_KEY)) || {}
  } catch {
    return {}
  }
}

const writeHiddenCharts = (hidden) => {
  try {
    localStorage.setItem(HIDDEN_CHARTS_STORAGE_KEY, JSON.stringify(hidden))
  } catch {
    // ignore quota / disabled storage
  }
}

const getStorageKey = (surveyId, chartId, index) =>
  `${surveyId ?? 'unknown'}:${chartId ?? `index-${index}`}`

export const ChartRendererV2 = ({
  data,
  index = 0,
  surveyId,
  chartId,
  question = {},
  emptyMessage = null,
}) => {
  const [view, setView] = useState(VIEW.BAR_CHART)
  const storageKey = getStorageKey(surveyId, chartId, index)
  const [isHidden, setIsHidden] = useState(
    () => !!readHiddenCharts()[storageKey]
  )

  const setHidden = (hidden) => {
    const hiddenCharts = readHiddenCharts()
    if (hidden) {
      hiddenCharts[storageKey] = true
    } else {
      delete hiddenCharts[storageKey]
    }
    writeHiddenCharts(hiddenCharts)
    setIsHidden(hidden)
  }

  const actions = [
    isHidden
      ? {
          label: t('Show chart'),
          icon: <i className="ri-eye-line"></i>,
          onClick: () => setHidden(false),
        }
      : {
          label: t('Hide chart'),
          icon: <i className="ri-eye-off-line"></i>,
          onClick: () => setHidden(true),
        },
  ]

  return (
    <Card
      className={classNames('responses-chart-card py-3 gap-24', {
        'responses-chart-card--hidden': isHidden,
      })}
    >
      <ChartHeader
        {...question}
        actions={actions}
      />
      <Collapsible open={!isHidden}>
        <div>
          <div>
            {view === VIEW.BAR_CHART && <BarChart data={data} />}
            {view === VIEW.PIE_CHART && <PieChart data={data} newLabels />}
            {view === VIEW.TABLE && <StatisticsTable data={data} />}
          </div>
          <div className="d-flex justify-content-end mt-3">
            <ToggleButtons
              id={`chart-view-${index}`}
              value={view}
              onChange={setView}
              toggleOptions={viewOptions}
            />
          </div>
        </div>
      </Collapsible>
    </Card>
  )
}
