import { getConditionTypeInfo } from './getConditionTypeInfo'
import { determineConditionTargetType } from './questionConditionHelpers'

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
