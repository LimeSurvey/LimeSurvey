import { useMemo } from 'react'

import { SurveyImportService } from 'services'
import useAuth from './useAuth'

export const useSurveyImportService = () => {
  const auth = useAuth()

  return useMemo(
    () => new SurveyImportService(auth),
    [auth.csrfToken, auth.csrfTokenName, auth.surveyImportUrl]
  )
}
