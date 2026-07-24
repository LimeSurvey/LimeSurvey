import { useMemo } from 'react'

import { STATES } from 'helpers'

import { PAGE_SIZE, useQuestionAnswers } from './useQuestionAnswers'

export function useQuestionComments(
  surveyId,
  questionCode,
  {
    enabled = true,
    selectedAnswer = '',
    selectedField = '',
    fields = [],
    questionType,
  } = {}
) {
  const { data, ...rest } = useQuestionAnswers(
    surveyId,
    questionCode,
    ({ statisticsService, activeLanguage }) => ({
      // selectedAnswer is part of the key so changing the filter refetches from
      // page 0 instead of filtering the in-memory list.
      queryKey: [
        STATES.SURVEY_RESPONSE_COMMENTS,
        surveyId,
        questionCode,
        activeLanguage,
        selectedAnswer,
      ],
      queryFn: ({ pageParam = 0 }) =>
        statisticsService.getQuestionComments(
          surveyId,
          questionCode,
          pageParam,
          PAGE_SIZE,
          activeLanguage,
          selectedAnswer,
          fields,
          questionType,
          selectedField
        ),
    }),
    { enabled, fields }
  )

  const comments = useMemo(
    () => (data?.pages || []).flatMap((page) => page.comments || []),
    [data]
  )

  return { comments, ...rest }
}
