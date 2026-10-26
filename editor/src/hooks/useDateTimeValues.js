import { useQuery } from '@tanstack/react-query'
import { useMemo } from 'react'

import { getApiUrl, STATES } from 'helpers'
import { StatisticsService } from 'services'

import useAuth from './useAuth'

/**
 * Answers of a Date/time question grouped by day and time, loaded once per
 * filter set and then sliced on the client for the day and range views.
 */
export function useDateTimeValues(
  surveyId,
  qid,
  filters,
  { enabled = true } = {}
) {
  const auth = useAuth()
  const statisticsService = useMemo(
    () => new StatisticsService(auth, surveyId, getApiUrl()),
    [auth, surveyId]
  )

  const { data, isLoading, isError } = useQuery({
    queryKey: [STATES.SURVEY_STATISTICS_DATE_VALUES, surveyId, qid, filters],
    queryFn: () => statisticsService.getDateTimeValues(surveyId, qid, filters),
    enabled: enabled && !!surveyId && !!qid,
  })

  return { values: data, isLoading, isError }
}
