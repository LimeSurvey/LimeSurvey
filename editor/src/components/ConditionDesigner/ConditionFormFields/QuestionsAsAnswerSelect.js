import React from 'react'

import { QuestionsSelect } from './QuestionsSelect'

export const QuestionsAsAnswerSelect = ({
  index,
  condition,
  updateCondition,
  previousQuestions,
}) => {
  // Remove questions with type M and P from the previous questions array
  //update cfield by adding @@ as prefix and suffix
  const updatedQuestions = previousQuestions
    .filter((question) => question.type !== 'M' && question.type !== 'P')
    .map((question) => ({
      ...question,
      cfieldname: `@${question.cfieldname}@`,
    }))

  if (updatedQuestions.length === 0) {
    return null
  }

  return (
    <QuestionsSelect
      index={index}
      condition={condition}
      updateCondition={() => {}}
      previousQuestions={updatedQuestions}
      valueIdentifier={'value'}
      onChange={({ value }) => {
        updateCondition(index, 'value', value)
      }}
    />
  )
}
