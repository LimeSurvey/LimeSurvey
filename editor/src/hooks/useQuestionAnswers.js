import { useInfiniteQuery } from '@tanstack/react-query'
import { useMemo } from 'react'

import { getApiUrl, STATES } from 'helpers'
import { StatisticsService } from 'services'

import { useAppState } from './useAppState'
import useAuth from './useAuth'

export const PAGE_SIZE = 15

/**
 * Shared scaffolding for the question-answer infinite queries
 * (useQuestionComments, useQuestionResponses): it wires up auth, the active
 * language, the memoized StatisticsService and the page-by-page pagination,
 * leaving each caller to supply only its query key, fetch call and result shape.
 *
 * @param {string|number} surveyId
 * @param {string} questionCode
 * @param {(ctx: {statisticsService: StatisticsService, activeLanguage: string}) => {queryKey: any[], queryFn: Function}} buildQuery
 * @param {{ enabled?: boolean }} [options]
 */
export function useQuestionAnswers(
  surveyId,
  questionCode,
  buildQuery,
  { enabled = true } = {}
) {
  const auth = useAuth()
  const [activeLanguage] = useAppState(STATES.ACTIVE_LANGUAGE)

  const statisticsService = useMemo(
    () => new StatisticsService(auth, surveyId, getApiUrl()),
    [auth, surveyId]
  )

  const { queryKey, queryFn } = buildQuery({
    statisticsService,
    activeLanguage,
  })

  const {
    data,
    fetchNextPage,
    hasNextPage,
    isFetching,
    isFetchingNextPage,
    isLoading,
  } = useInfiniteQuery({
    queryKey,
    queryFn,
    initialPageParam: 0,
    getNextPageParam: (lastPage) => {
      const pagination = lastPage?.pagination
      if (!pagination) {
        return undefined
      }
      const nextPage = pagination.currentPage + 1
      return nextPage < pagination.totalPages ? nextPage : undefined
    },
    enabled: enabled && !!questionCode,
  })

  return {
    data,
    fetchNextPage,
    hasNextPage: !!hasNextPage,
    isLoading,
    isFetching,
    isFetchingNextPage,
  }
}
