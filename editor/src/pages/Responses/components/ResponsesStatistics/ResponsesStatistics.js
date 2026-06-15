import React, { useEffect, useState } from 'react'

import { ToggleButtons } from 'components'
import { useStatistics } from 'hooks'

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
  const [loadMoreNode, setLoadMoreNode] = useState(null)

  // Load the next page of charts when the sentinel below the chart list
  // scrolls into view. The observer is recreated after each page (statistics
  // dependency) so it fires again immediately if the sentinel is still
  // visible after the new charts render.
  useEffect(() => {
    if (!loadMoreNode || !hasNextPage || isFetchingNextPage) return

    const observer = new IntersectionObserver(
      ([entry]) => entry.isIntersecting && fetchNextPage(),
      { rootMargin: '300px' }
    )
    observer.observe(loadMoreNode)
    return () => observer.disconnect()
  }, [loadMoreNode, hasNextPage, isFetchingNextPage, fetchNextPage, statistics])

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
      <>
        <StatisticsContainer
          statistics={statistics}
          surveyId={surveyId}
          valueType={valueType}
        />
        <div ref={setLoadMoreNode} className="responses-statistics-load-more">
          {isFetchingNextPage && <span className="loader"></span>}
        </div>
      </>
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
          theme="lime"
        />
      </div>
      {renderContent()}
    </>
  )
}
