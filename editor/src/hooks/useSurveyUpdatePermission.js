import { useEffect } from 'react'

import { useAppState } from './index'

export function useSurveyUpdatePermission(survey) {
  const [hasSurveyUpdatePermission, setHasSurveyUpdatePermission] = useAppState(
    'sharing_update_permission',
    survey.hasSurveyUpdatePermission
  )

  useEffect(() => {
    setHasSurveyUpdatePermission(survey.hasSurveyUpdatePermission)
  }, [survey, survey.sid, survey.hasSurveyUpdatePermission])

  return hasSurveyUpdatePermission
}
