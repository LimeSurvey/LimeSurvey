import { keepPreviousData, useInfiniteQuery } from '@tanstack/react-query'
import { useMemo } from 'react'

import { getApiUrl, STATES } from 'helpers'
import { StatisticsService } from 'services'

import useAuth from './useAuth'

export function useStatistics(surveyId, filters) {
  const auth = useAuth()
  const statisticsService = useMemo(
    () => new StatisticsService(auth, surveyId, getApiUrl()),
    [auth]
  )

  const {
    data: statistics,
    isFetching,
    isFetchingNextPage,
    hasNextPage,
    fetchNextPage,
    refetch,
  } = useInfiniteQuery({
    queryKey: [STATES.SURVEY_STATISTICS, surveyId, filters],
    queryFn: ({ pageParam }) =>
      statisticsService.getSurveyStatistics(surveyId, filters, pageParam),
    initialPageParam: 0,
    getNextPageParam: (lastPage) =>
      lastPage?.pagination?.hasMore ? lastPage.pagination.page + 1 : undefined,
    select: (data) => data.pages.flatMap((page) => page?.statistics ?? []),
    placeholderData: keepPreviousData,
  })

  return {
    statistics,
    isFetching,
    isFetchingNextPage,
    hasNextPage,
    fetchNextPage,
    refetch,
  }
}
