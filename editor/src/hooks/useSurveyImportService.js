import { useMemo } from 'react'

import { getApiUrl } from 'helpers'
import { SurveyImportService } from 'services'
import useAuth from './useAuth'

export const useSurveyImportService = () => {
  const auth = useAuth()

  return useMemo(() => new SurveyImportService(auth, getApiUrl()), [auth.token])
}
