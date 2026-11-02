import { STATES } from 'helpers'

import { useQuestionAnswers } from './useQuestionAnswers'

export function useQuestionFiles(
  surveyId,
  questionCode,
  {
    enabled = true,
    field,
    filters = {},
    filterSet = [],
    search = [],
    pageSize,
  } = {}
) {
  const fields = field ? [field] : []
  const {
    items: files,
    data,
    ...rest
  } = useQuestionAnswers(
    surveyId,
    questionCode,
    ({ statisticsService, activeLanguage }) => ({
      queryKey: [
        STATES.SURVEY_RESPONSE_FILES,
        surveyId,
        questionCode,
        activeLanguage,
        field,
        filters,
        filterSet,
        search,
        pageSize,
      ],
      queryFn: ({ pageParam = 0 }) =>
        statisticsService.getQuestionFiles(
          surveyId,
          pageParam,
          pageSize,
          activeLanguage,
          field,
          filters,
          search,
          filterSet
        ),
    }),
    { enabled, fields, pageItems: 'files' }
  )

  const totalResults = data?.pages?.[0]?.pagination?.totalItems ?? null

  return { files, totalResults, ...rest }
}
