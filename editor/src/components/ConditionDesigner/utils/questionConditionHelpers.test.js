import { getConditionTypeInfo } from './getConditionTypeInfo'
import {
  determineConditionTargetType,
  toConditionQid,
} from './questionConditionHelpers'

describe('determineConditionTargetType', () => {
  const previousQuestions = [
    { cfieldname: 'Q12', answers: [] },
    { cfieldname: 'Qtemp__5_Stemp__6', answers: [] },
  ]

  it.each(['@Q12@', '@Qtemp__5_Stemp__6@'])(
    'detects the answer of another question %s',
    (value) => {
      const processedCondition = {}
      determineConditionTargetType(
        { method: '==', value },
        processedCondition,
        previousQuestions
      )

      expect(processedCondition.targetType).toBe(
        getConditionTypeInfo().TARGET.ANSWER_OF_OTHER_QUESTION
      )
    }
  )
})

describe('toConditionQid', () => {
  it('turns a saved question ID into a number', () => {
    expect(toConditionQid(123)).toBe(123)
    expect(toConditionQid('123')).toBe(123)
  })

  it('keeps the temp ID of an unsaved question', () => {
    expect(toConditionQid('temp__4821')).toBe('temp__4821')
  })
})
