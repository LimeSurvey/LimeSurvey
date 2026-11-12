import { useQuery } from '@tanstack/react-query'
import { useMemo } from 'react'

import { getApiUrl, STATES } from 'helpers'
import { StatisticsService } from 'services'

import { useAppState } from './useAppState'
import useAuth from './useAuth'

/**
 * Answers of a Date/time question grouped by day and time. `from` and `to`
 * ('YYYY-MM-DD') load only those days; without them every day is loaded.
 * `decrypt` is needed for an encrypted question.
 */
export function useDateTimeValues(
  surveyId,
  qid,
  filters,
  { enabled = true, filterSet = [], decrypt = false, from, to } = {}
) {
  const auth = useAuth()
  const [activeLanguage] = useAppState(STATES.ACTIVE_LANGUAGE)
  const statisticsService = useMemo(
    () => new StatisticsService(auth, surveyId, getApiUrl()),
    [auth, surveyId]
  )

  const { data, isLoading, isError } = useQuery({
    queryKey: [
      STATES.SURVEY_STATISTICS_DATE_VALUES,
      surveyId,
      qid,
      filters,
      filterSet,
      decrypt,
      activeLanguage,
      from,
      to,
    ],
    queryFn: () =>
      statisticsService.getDateTimeValues(
        surveyId,
        qid,
        filters,
        filterSet,
        decrypt,
        activeLanguage,
        { from, to }
      ),
    enabled: enabled && !!surveyId && !!qid,
    retry: false,
  })

  return { values: data, isLoading, isError }
}
