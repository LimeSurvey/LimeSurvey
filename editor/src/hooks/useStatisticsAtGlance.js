import { useQuery } from '@tanstack/react-query'
import { useMemo } from 'react'

import { getApiUrl, STATES } from 'helpers'
import { StatisticsService } from 'services'

import useAuth from './useAuth'

export function useStatisticsAtGlance(surveyId, { enabled = true } = {}) {
  const auth = useAuth()
  const statisticsService = useMemo(
    () => new StatisticsService(auth, surveyId, getApiUrl()),
    [auth, surveyId]
  )

  const {
    data: statistics,
    isFetching,
    refetch,
  } = useQuery({
    queryKey: [STATES.SURVEY_STATISTICS, surveyId],
    queryFn: () => statisticsService.getSurveyStatisticsAtGlance(surveyId),
    select: (data) => data.statistics,
    enabled: enabled && !!surveyId,
  })

  return {
    statistics,
    isFetching,
    refetch,
  }
}
