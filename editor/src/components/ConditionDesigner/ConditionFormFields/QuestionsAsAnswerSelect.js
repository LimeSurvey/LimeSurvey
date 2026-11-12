import React, { useMemo } from 'react'

import { QuestionsSelect } from './QuestionsSelect'

/**
 * Select of a previous question whose answer the condition compares to.
 * Checkbox fields of multiple choice questions are left out, their "Other"
 * text field is offered.
 *
 * @param {object} props
 * @param {number} props.index The index of the condition in the scenario.
 * @param {object} props.condition The condition being edited.
 * @param {Function} props.updateCondition Updates a property of the condition.
 * @param {object[]} props.previousQuestions The condition questions before the current question.
 * @returns {JSX.Element|null} The question select, or null without questions to offer.
 */
export const QuestionsAsAnswerSelect = ({
  index,
  condition,
  updateCondition,
  previousQuestions,
}) => {
  const updatedQuestions = useMemo(
    () =>
      previousQuestions
        .filter((question) => !question.isCheckboxField)
        .map((question) => ({
          ...question,
          cfieldname: `@${question.cfieldname}@`,
        })),
    [previousQuestions]
  )

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
