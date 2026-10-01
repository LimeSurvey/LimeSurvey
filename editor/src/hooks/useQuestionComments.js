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
      queryKey: [
        STATES.SURVEY_RESPONSE_COMMENTS,
        surveyId,
        questionCode,
        activeLanguage,
        selectedAnswer,
        selectedField,
        fields,
        questionType,
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
