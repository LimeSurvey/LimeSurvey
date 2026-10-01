import React from 'react'

import { TAB_KEYS } from '../utils'
import { StatisticsFilters } from '../components/ResponsesStatistics/StatisticsFilters'

export const RightSideBar = ({
  tabKey,
  filters,
  setFilters,
  showStatisticsFilters,
  setShowStatisticsFilters,
}) => {
  if (tabKey === TAB_KEYS.STATISTICS && showStatisticsFilters) {
    return (
      <StatisticsFilters
        setShowFilters={setShowStatisticsFilters}
        filters={filters}
        setFilters={setFilters}
      />
    )
  }

  return null
}
