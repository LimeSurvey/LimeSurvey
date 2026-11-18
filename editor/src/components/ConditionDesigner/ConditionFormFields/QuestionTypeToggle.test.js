import { render } from '@testing-library/react'

import { getConditionTypeInfo, getDefaultCondition } from '../utils'
import { QuestionTypeToggle } from './QuestionTypeToggle'

const conditionTypeInfo = getConditionTypeInfo()

// A condition that uses a question as its source
const questionSourcedCondition = {
  ...getDefaultCondition(),
  qid: 11,
  cqid: 5,
  cfieldname: '1X1X5',
  cquestions: 'Q1',
  method: '==',
  value: 'A1',
  sourceType: conditionTypeInfo.SOURCE.QUESTION,
}

const renderToggle = (isFirstQuestion) => {
  const updateCondition = jest.fn()
  render(
    <QuestionTypeToggle
      index={0}
      condition={questionSourcedCondition}
      updateCondition={updateCondition}
      isFirstQuestion={isFirstQuestion}
    />
  )
  return updateCondition
}

describe('QuestionTypeToggle', () => {
  it('clears the question fields when it forces participant data on the first question', () => {
    const updateCondition = renderToggle(true)

    const updates = Object.fromEntries(
      updateCondition.mock.calls.map(([, key, value]) => [key, value])
    )
    // The question code must not be saved as a participant attribute name
    expect(updates).toEqual({
      sourceType: conditionTypeInfo.SOURCE.PARTICIPANT_DATA,
      targetType: conditionTypeInfo.TARGET.CONSTANT,
      cqid: '',
      cfieldname: '',
      cquestions: '',
      method: '',
      value: null,
      answers: [],
    })
  })

  it('keeps the condition of a question that is not the first one', () => {
    const updateCondition = renderToggle(false)

    expect(updateCondition).not.toHaveBeenCalled()
  })
})
