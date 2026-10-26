import { keepPreviousData, useInfiniteQuery } from '@tanstack/react-query'
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
 * `fields` are the question's response columns (delivered with the chart data in
 * `meta.question.fields`). The fetch is gated on them so each request only
 * transfers that question's columns instead of every question's.
 *
 * `pageItems` names the page field holding that caller's list (e.g. 'rows',
 * 'comments'); the hook flattens it across the loaded pages into `items`.
 *
 * @param {string|number} surveyId
 * @param {string} questionCode
 * @param {(ctx: {statisticsService: StatisticsService, activeLanguage: string}) => {queryKey: any[], queryFn: Function}} buildQuery
 * @param {{ enabled?: boolean, fields?: string[], pageItems?: string }} [options]
 */
export function useQuestionAnswers(
  surveyId,
  questionCode,
  buildQuery,
  { enabled = true, fields = [], pageItems } = {}
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
    // Require the question's columns so we never fetch the full (un-narrowed)
    // response set; a chart-producing question always has at least one column.
    enabled: enabled && !!questionCode && fields.length > 0,
    placeholderData: keepPreviousData,
  })

  const items = useMemo(
    () =>
      pageItems
        ? (data?.pages || []).flatMap((page) => page?.[pageItems] || [])
        : [],
    [data, pageItems]
  )

  return {
    data,
    items,
    fetchNextPage,
    hasNextPage: !!hasNextPage,
    isLoading,
    isFetching,
    isFetchingNextPage,
  }
}
