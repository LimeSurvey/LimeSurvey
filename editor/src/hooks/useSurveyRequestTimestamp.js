import { useCallback, useEffect } from 'react'

import { queryClient } from 'queryClient'
import { getCurrentUtcTimestamp, STATES } from 'helpers'

export const useSurveyRequestTimestamp = () => {
  const getSurveyRequestTimestamp = useCallback((surveyId) => {
    const timestamps =
      queryClient.getQueryData([STATES.SURVEY_REQUEST_UTC_TIMESTAMP]) || {}
    return timestamps[surveyId] || null
  }, [])

  const setSurveyRequestTimestamp = useCallback((surveyId) => {
    queryClient.setQueryData(
      [STATES.SURVEY_REQUEST_UTC_TIMESTAMP],
      (old = {}) => ({
        ...old,
        [surveyId]: getCurrentUtcTimestamp(),
      })
    )
    return getCurrentUtcTimestamp()
  }, [])

  useEffect(() => {
    const handleBeforeUnload = () => {
      queryClient.removeQueries({
        queryKey: [STATES.SURVEY_REQUEST_UTC_TIMESTAMP],
      })
    }

    window.addEventListener('beforeunload', handleBeforeUnload)
    return () => {
      window.removeEventListener('beforeunload', handleBeforeUnload)
    }
  }, [])

  return {
    getSurveyRequestTimestamp,
    setSurveyRequestTimestamp,
  }
}
