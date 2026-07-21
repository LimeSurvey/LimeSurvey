import React, { useEffect, useMemo, useState } from 'react'

import { ToggleButtons } from 'components'
import { useAppState, useStatistics, useSurvey } from 'hooks'
import { STATES } from 'helpers'
import { useIsInViewport } from 'hooks/useInViewport'

import { ResponsesHeader } from '../../ResponsesHeader'
import { TAB_KEYS } from '../../utils'
import { VALUE_TYPE } from './ChartsUtils'
import { StatisticsContainer } from './StatisticsContainer.js'
import { buildQuestionOptions } from './StatisticsFiltersModal/utils'

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
  const [loadMoreRef, isLoadMoreInView] = useIsInViewport(null, {
    initialInView: false,
  })

  // Survey data drives the filter modal's Question / Participant / language
  // options (same source as StatisticsContainer).
  const { survey } = useSurvey(surveyId)
  const [activeLanguage] = useAppState(STATES.ACTIVE_LANGUAGE)
  const questionOptions = useMemo(
    () => buildQuestionOptions(survey, activeLanguage),
    [survey?.questionGroups, activeLanguage]
  )

  // Auto-load the next page of charts while the sentinel below the list is in
  // view. Re-runs when fetching settles, so it chains pages as long as the
  // sentinel stays visible.
  useEffect(() => {
    if (isLoadMoreInView && hasNextPage && !isFetchingNextPage) {
      fetchNextPage()
    }
  }, [isLoadMoreInView, hasNextPage, isFetchingNextPage, fetchNextPage])

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
          survey={survey}
          questionOptions={questionOptions}
        />
        <ToggleButtons
          id="statistics-value-type"
          value={valueType}
          onChange={setValueType}
          toggleOptions={valueTypeOptions}
        />
      </div>
      {renderContent()}
      {/* Sentinel is always mounted so useIsInViewport's observer (set up once
          on a stable ref) reliably tracks it once the charts render. */}
      <div ref={loadMoreRef} className="responses-statistics-load-more">
        {isFetchingNextPage && <span className="loader"></span>}
      </div>
    </>
  )
}
