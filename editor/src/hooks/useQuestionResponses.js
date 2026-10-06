import { STATES } from 'helpers'

import { PAGE_SIZE, useQuestionAnswers } from './useQuestionAnswers'

export function useQuestionResponses(
  surveyId,
  questionCode,
  {
    enabled = true,
    fields = [],
    filters = {},
    search = [],
    expandTerm,
    countFiles = false,
  } = {}
) {
  const {
    items: rows,
    data,
    ...rest
  } = useQuestionAnswers(
    surveyId,
    questionCode,
    ({ statisticsService, activeLanguage }) => ({
      queryKey: [
        STATES.SURVEY_RESPONSE_ANSWERS,
        surveyId,
        questionCode,
        activeLanguage,
        filters,
        search,
      ],
      queryFn: ({ pageParam = 0 }) =>
        statisticsService.getQuestionResponses(
          surveyId,
          questionCode,
          pageParam,
          PAGE_SIZE,
          activeLanguage,
          fields,
          filters,
          search,
          expandTerm,
          countFiles
        ),
    }),
    { enabled, fields, pageItems: 'rows' }
  )

  // Columns are identical across pages, so take them from the first page.
  const columns = data?.pages?.[0]?.columns ?? []
  // Matching responses, or matching uploaded files when `countFiles` is set.
  const totalResults = countFiles
    ? (data?.pages?.[0]?.fileCount ?? null)
    : (data?.pages?.[0]?.pagination?.totalItems ?? null)

  return { columns, rows, totalResults, ...rest }
}
