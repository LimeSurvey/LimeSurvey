import { useQuery } from '@tanstack/react-query'
import { useMemo } from 'react'

import { getApiUrl, STATES } from 'helpers'
import { QuestionThemeAttributeService } from 'services'

import useAuth from './useAuth'

export const useQuestionAttributes = () => {
  const auth = useAuth()
  const questionThemeAttributeService = useMemo(
    () => new QuestionThemeAttributeService(auth, getApiUrl()),
    [auth]
  )

  const { data: attributes = {}, isFetching } = useQuery({
    queryKey: [STATES.QUESTION_THEME_ATTRIBUTES],
    queryFn: () => questionThemeAttributeService.getAttributes(),
    staleTime: Infinity,
    cacheTime: Infinity,
  })

  return { attributes, isFetching }
}
