import { useMemo } from 'react'
import { useQuery } from '@tanstack/react-query'

import { getApiUrl, STATES } from 'helpers'
import { SurveyLogicService } from 'services'

import useAuth from './useAuth'

/**
 * Fetches the survey logic overview HTML for a survey, group or question.
 *
 * @param {{ sid: number|string, gid?: number|string, qid?: number|string,
 *   language?: string, enabled?: boolean }} params
 */
export const useSurveyLogic = ({ sid, gid, qid, language, enabled = true }) => {
  const auth = useAuth()

  const surveyLogicService = useMemo(
    () => new SurveyLogicService(auth, sid, getApiUrl()),
    [auth, sid]
  )

  const { data, isFetching, isError, refetch } = useQuery({
    queryKey: [STATES.SURVEY_LOGIC, sid, gid, qid, language],
    queryFn: ({ signal }) =>
      surveyLogicService.getSurveyLogic(sid, { gid, qid, language }, signal),
    select: (response) => response?.surveyLogic,
    enabled: !!enabled && !!sid,
    staleTime: 0,
  })

  return {
    surveyLogic: data,
    isFetching,
    isError,
    refetch,
  }
}
