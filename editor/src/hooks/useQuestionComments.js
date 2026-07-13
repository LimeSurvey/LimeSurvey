import { useInfiniteQuery } from '@tanstack/react-query'
import { useMemo } from 'react'

import { getApiUrl, STATES } from 'helpers'
import { StatisticsService } from 'services'

import { useAppState } from './useAppState'
import useAuth from './useAuth'

const PAGE_SIZE = 15

export function useQuestionComments(
  surveyId,
  questionCode,
  { enabled = true } = {}
) {
  const auth = useAuth()
  const [activeLanguage] = useAppState(STATES.ACTIVE_LANGUAGE)

  const statisticsService = useMemo(
    () => new StatisticsService(auth, surveyId, getApiUrl()),
    [auth, surveyId]
  )

  const {
    data,
    fetchNextPage,
    hasNextPage,
    isFetching,
    isFetchingNextPage,
    isLoading,
  } = useInfiniteQuery({
    queryKey: [
      STATES.SURVEY_RESPONSE_COMMENTS,
      surveyId,
      questionCode,
      activeLanguage,
    ],
    queryFn: ({ pageParam = 0 }) =>
      statisticsService.getQuestionComments(
        surveyId,
        questionCode,
        pageParam,
        PAGE_SIZE,
        activeLanguage
      ),
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

  const comments = useMemo(
    () => (data?.pages || []).flatMap((page) => page.comments || []),
    [data]
  )

  return {
    comments,
    fetchNextPage,
    hasNextPage: !!hasNextPage,
    isLoading,
    isFetching,
    isFetchingNextPage,
  }
}
