import { useMemo } from 'react'

import { PAGE_SIZE, useQuestionAnswers } from './useQuestionAnswers'

export function useQuestionResponses(
  surveyId,
  questionCode,
  { enabled = true, fields = [], filters = {}, search = [] } = {}
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
          search
        ),
    }),
    { enabled, fields }
  )

  // Columns are identical across pages, so take them from the first page.
  const columns = data?.pages?.[0]?.columns ?? []
  const rows = useMemo(
    () => (data?.pages || []).flatMap((page) => page.rows || []),
    [data]
  )
  // Total matching responses reported by the backend's pagination meta.
  const totalResults = data?.pages?.[0]?.pagination?.totalItems ?? null

  return { columns, rows, totalResults, ...rest }
}
