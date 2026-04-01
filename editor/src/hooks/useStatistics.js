import { keepPreviousData, useQuery } from '@tanstack/react-query'
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
    refetch,
  } = useQuery({
    queryKey: [STATES.SURVEY_STATISTICS, surveyId, filters],
    queryFn: () => statisticsService.getSurveyStatistics(surveyId, filters),
    select: (data) => data.statistics,
    placeholderData: keepPreviousData,
  })

  return {
    statistics,
    isFetching,
    refetch,
  }
}
