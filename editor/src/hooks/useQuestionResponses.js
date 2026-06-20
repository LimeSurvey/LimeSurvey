import { useMemo } from 'react'

import { PAGE_SIZE, useQuestionAnswers } from './useQuestionAnswers'

/**
 * Per-response answers of an Array (Texts) question, pivoted into table rows
 * (one per participant) and columns (one per subquestion cell). Mirrors
 * useQuestionComments but keeps the full answer grid instead of comments.
 */
export function useQuestionResponses(
  surveyId,
  questionCode,
  { enabled = true } = {}
) {
  const { data, ...rest } = useQuestionAnswers(
    surveyId,
    questionCode,
    ({ statisticsService, activeLanguage }) => ({
      queryKey: [
        'survey-response-answers-table',
        surveyId,
        questionCode,
        activeLanguage,
      ],
      queryFn: ({ pageParam = 0 }) =>
        statisticsService.getQuestionResponses(
          surveyId,
          questionCode,
          pageParam,
          PAGE_SIZE,
          activeLanguage
        ),
    }),
    { enabled }
  )

  // Columns are identical across pages, so take them from the first page.
  const columns = data?.pages?.[0]?.columns ?? []
  const rows = useMemo(
    () => (data?.pages || []).flatMap((page) => page.rows || []),
    [data]
  )

  return { columns, rows, ...rest }
}
