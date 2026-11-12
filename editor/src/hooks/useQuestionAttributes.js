import { useQuery } from '@tanstack/react-query'
import { useMemo } from 'react'
import { useTranslation } from 'react-i18next'

import { getApiUrl, STATES } from 'helpers'
import { QuestionThemeAttributeService } from 'services'

import useAuth from './useAuth'

export const useQuestionAttributes = () => {
  const auth = useAuth()
  // The captions and options are translated by the backend into the user's language
  const { i18n } = useTranslation()
  const language = i18n?.resolvedLanguage || i18n?.language
  const questionThemeAttributeService = useMemo(
    () => new QuestionThemeAttributeService(auth, getApiUrl()),
    [auth]
  )

  const { data: attributes = {}, isFetching } = useQuery({
    queryKey: [STATES.QUESTION_THEME_ATTRIBUTES, language],
    queryFn: () => questionThemeAttributeService.getAttributes(),
    staleTime: Infinity,
    gcTime: Infinity,
  })

  return { attributes, isFetching }
}
