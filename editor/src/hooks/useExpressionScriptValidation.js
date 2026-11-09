import { useCallback, useMemo } from 'react'

import { getApiUrl, hasTempId } from 'helpers'
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
      // An unsaved question (temp ID) isn't on the server yet, which would
      // answer "Question not found": It's validated once saved. The server
      // can't check the other unsaved questions a script uses either.
      if (
        !surveyId ||
        !questionId ||
        hasTempId(String(questionId)) ||
        !String(expression).trim()
      ) {
        return { valid: true, diagnostics: [] }
      }

      return await service.validate(expression, questionId, signal)
    },
    [questionId, service, surveyId]
  )
}
