import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { useCallback, useMemo } from 'react'
import { useTranslation } from 'react-i18next'

import { getApiUrl, STATES } from 'helpers'
import { getDefaultQuestionTypes } from 'helpers/getDefaultQuestionTypes'
import { QuestionTypeService } from 'services'

import useAuth from './useAuth'

/**
 * Returns the question types of the editor (react/questiontypes.xml merged with the installed question themes):
 * The categories of the question type selector, and every question theme keyed by theme name.
 * Until the first response, and when the request fails, the question types of a default installation
 * are returned, so that questions can always be added.
 * @returns {{
 *   categories: {name: string, title: string, icon: string}[],
 *   questionTypes: Object<string, {name: string, type: string, title: string, category: ?string, sortOrder: ?number, devOnly: boolean, visible: boolean}>,
 *   getTitle: (themeName: string, fallback?: string) => string,
 *   isFetching: boolean,
 * }}
 */
export const useQuestionTypes = () => {
  const auth = useAuth()
  // The titles are translated by the backend into the user's language
  const { i18n } = useTranslation()
  const language = i18n?.resolvedLanguage || i18n?.language
  const isDemoMode =
    process.env.REACT_APP_DEMO_MODE === 'true' ||
    process.env.STORYBOOK_DEV === 'true'
  const questionTypeService = useMemo(
    () => new QuestionTypeService(auth, getApiUrl()),
    [auth]
  )

  const { data, isFetching } = useQuery({
    queryKey: [STATES.QUESTION_TYPES, language],
    queryFn: () => questionTypeService.getQuestionTypes(),
    // Demo mode and storybook have no backend: They use the default question types below
    enabled: !isDemoMode,
    // After a language change, keep the previous titles until the translated ones arrive
    placeholderData: keepPreviousData,
    staleTime: Infinity,
    gcTime: Infinity,
  })

  const { categories, questionTypes } = useMemo(
    () =>
      data?.categories && data?.questionTypes
        ? data
        : getDefaultQuestionTypes(language),
    [data, language]
  )

  const getTitle = useCallback(
    (themeName, fallback = '') => questionTypes[themeName]?.title ?? fallback,
    [questionTypes]
  )

  return { categories, questionTypes, getTitle, isFetching }
}
