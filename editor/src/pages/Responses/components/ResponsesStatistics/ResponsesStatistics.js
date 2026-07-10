import React, { useState } from 'react'

import { ToggleButtons } from 'components'
import { useStatistics } from 'hooks'
import { useIsInViewport } from 'hooks/useInViewport'

import { ResponsesHeader } from '../../ResponsesHeader'
import { TAB_KEYS } from '../../utils'
import { VALUE_TYPE } from './ChartsUtils'
import { StatisticsContainer } from './StatisticsContainer.js'

const valueTypeOptions = [
  { name: '%', value: VALUE_TYPE.PERCENTAGE },
  { name: '#', value: VALUE_TYPE.COUNT },
]

export const ResponsesStatistics = ({
  surveyId,
  filters = {},
  showFilters,
  setShowFilters,
  setFilters,
}) => {
  const {
    statistics,
    isFetching,
    isFetchingNextPage,
    hasNextPage,
    fetchNextPage,
  } = useStatistics(surveyId, filters)
  const [valueType, setValueType] = useState(VALUE_TYPE.PERCENTAGE)

  const [loadMoreRef] = useIsInViewport(null, {
    initialInView: false,
    rootMargin: '0px 0px 300px 0px',
    onChange: (inView) => {
      if (inView && hasNextPage && !isFetchingNextPage) {
        fetchNextPage()
      }
    },
  })

  const renderContent = () => {
    if (!statistics?.length) {
      return (
        <div className="responses-statistics-loading">
          {isFetching && <span className="loader"></span>}
          <h2>
            {isFetching
              ? t('Loading statistics...')
              : t(
                  'No responses or compatible data available to display statistics.'
                )}
          </h2>
        </div>
      )
    }

    return (
      <StatisticsContainer
        statistics={statistics}
        surveyId={surveyId}
        valueType={valueType}
        filters={filters}
      />
    )
  }

  return (
    <>
      <div className="responses-statistics-toolbar">
        <ResponsesHeader
          setShowFilters={setShowFilters}
          showFilters={showFilters}
          setFilters={setFilters}
          tabKey={TAB_KEYS.STATISTICS}
        />
        <ToggleButtons
          id="statistics-value-type"
          value={valueType}
          onChange={setValueType}
          toggleOptions={valueTypeOptions}
        />
      </div>
      {renderContent()}
      <div ref={loadMoreRef} className="responses-statistics-load-more">
        {isFetchingNextPage && <span className="loader"></span>}
      </div>
    </>
  )
}
