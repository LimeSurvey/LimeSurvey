import { keepPreviousData, useInfiniteQuery } from '@tanstack/react-query'
import { useMemo, useRef } from 'react'

import { getApiUrl, STATES } from 'helpers'
import { StatisticsService } from 'services'

import { useAppState } from './useAppState'
import useAuth from './useAuth'

const isApiError = (payload) =>
  typeof payload === 'object' && payload !== null && 'httpStatus' in payload

/**
 * Fetch the survey's charts, one page at a time.
 *
 * @param {string|number} surveyId Survey to query.
 * @param {Object} filters Sidebar filters sent to the API.
 * @param {Array} filterSet Condition designer filter, in the shape the API accepts.
 * @returns {Object} Charts, the error a rejected filter came back with, and the
 *   paging controls. A rejected filter keeps the charts already on screen rather
 *   than emptying the page.
 */
export function useStatistics(surveyId, filters, filterSet) {
  const auth = useAuth()
  const [activeLanguage] = useAppState(STATES.ACTIVE_LANGUAGE)
  const statisticsService = useMemo(
    () => new StatisticsService(auth, surveyId, getApiUrl()),
    [auth]
  )

  const {
    data: pages,
    isFetching,
    isFetchingNextPage,
    isPlaceholderData,
    hasNextPage,
    fetchNextPage,
    refetch,
  } = useInfiniteQuery({
    queryKey: [
      STATES.SURVEY_STATISTICS,
      surveyId,
      activeLanguage,
      filters,
      filterSet,
    ],
    queryFn: ({ pageParam }) =>
      statisticsService.getSurveyStatistics(
        surveyId,
        filters,
        pageParam,
        undefined,
        activeLanguage,
        filterSet
      ),
    initialPageParam: 0,
    getNextPageParam: (lastPage) =>
      lastPage?.pagination?.hasMore ? lastPage.pagination.page + 1 : undefined,
    select: (data) => data.pages,
    placeholderData: keepPreviousData,
  })

  const error = (pages ?? []).find(isApiError) ?? null
  const statistics = useMemo(
    () => (pages ?? []).flatMap((page) => page?.statistics ?? []),
    [pages]
  )

  const lastStatistics = useRef({ surveyId: null, statistics: [] })
  if (!error && !isPlaceholderData) {
    lastStatistics.current = { surveyId, statistics }
  }

  const previous =
    lastStatistics.current.surveyId === surveyId
      ? lastStatistics.current.statistics
      : []

  return {
    statistics: error ? previous : statistics,
    error,
    isFetching,
    isFetchingNextPage,
    hasNextPage,
    fetchNextPage,
    refetch,
  }
}
