import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { useMemo } from 'react'

import { getApiUrl, STATES } from 'helpers'
import { StatisticsService } from 'services'

import { useAppState } from './useAppState'
import useAuth from './useAuth'

/**
 * Response locations of a map question within `bounds` (null = whole world),
 * refetched whenever the bounds or the statistics filters change. The
 * previous points stay on screen while the next viewport loads.
 *
 * Like the other per-question views this reads the survey-responses
 * endpoint, narrowed to the question's column via `fields`.
 */
export function useQuestionLocations(
  surveyId,
  questionCode,
  { enabled = true, fields = [], bounds = null, filters = {}, limit } = {}
) {
  const auth = useAuth()
  const [activeLanguage] = useAppState(STATES.ACTIVE_LANGUAGE)
  const statisticsService = useMemo(
    () => new StatisticsService(auth, surveyId, getApiUrl()),
    [auth, surveyId]
  )

  const { data, isLoading, isFetching, isPlaceholderData } = useQuery({
    queryKey: [
      STATES.SURVEY_RESPONSE_ANSWERS,
      surveyId,
      questionCode,
      activeLanguage,
      'locations',
      filters,
      bounds,
      limit,
    ],
    queryFn: () =>
      statisticsService.getQuestionLocations(
        surveyId,
        questionCode,
        activeLanguage,
        fields,
        filters,
        bounds,
        limit
      ),
    enabled: enabled && !!questionCode && fields.length > 0,
    placeholderData: keepPreviousData,
  })

  return {
    points: data?.points ?? [],
    total: data?.total ?? 0,
    isLoading,
    isFetching,
    isPlaceholderData,
  }
}
