import { getConditionTypeInfo } from './getConditionTypeInfo'
import {
  determineConditionTargetType,
  toConditionId,
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

describe('toConditionId', () => {
  it('turns a saved entity ID into a number', () => {
    expect(toConditionId(123)).toBe(123)
    expect(toConditionId('123')).toBe(123)
  })

  it('keeps the temp ID of an unsaved entity', () => {
    expect(toConditionId('temp__4821')).toBe('temp__4821')
    expect(toConditionId('temp__4821_1')).toBe('temp__4821_1')
  })

  it('keeps empty and non-numeric values as they are', () => {
    expect(toConditionId('')).toBe('')
    expect(toConditionId(null)).toBeNull()
    expect(toConditionId(undefined)).toBeUndefined()
    expect(toConditionId('abc')).toBe('abc')
  })
})
