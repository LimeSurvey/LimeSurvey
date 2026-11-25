import { renderWithQueryClient } from 'tests/testUtils'

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

// Not the first row, to check that the reset targets the toggle's row
const ROW_INDEX = 2

const renderToggle = (isFirstQuestion) => {
  const updateCondition = jest.fn()
  renderWithQueryClient(
    <QuestionTypeToggle
      index={ROW_INDEX}
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

    expect(
      updateCondition.mock.calls.every(([index]) => index === ROW_INDEX)
    ).toBe(true)
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
