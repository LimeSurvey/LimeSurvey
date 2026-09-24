import { useCallback, useMemo } from 'react'

import { getApiUrl } from 'helpers'
import { ExpressionScriptValidationService } from 'services'

import useAuth from './useAuth'

export const useExpressionScriptValidation = (surveyId, questionId) => {
  const auth = useAuth()
  const service = useMemo(
    () => new ExpressionScriptValidationService(auth, surveyId, getApiUrl()),
    [auth, surveyId]
  )

  return useCallback(
    async (expression, signal) => {
      if (!surveyId || !questionId || !String(expression).trim()) {
        return { valid: true, diagnostics: [] }
      }

      return await service.validate(expression, questionId, signal)
    },
    [questionId, service, surveyId]
  )
}
