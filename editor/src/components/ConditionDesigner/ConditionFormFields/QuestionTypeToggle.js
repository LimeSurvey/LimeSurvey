import { useMemo } from 'react'

import { useFocused } from 'hooks'
import { ToggleButtons } from 'components'

import { getConditionTypeInfo } from '../utils'

export const QuestionTypeToggle = ({ index, condition, updateCondition }) => {
  const { groupIndex, questionIndex } = useFocused()
  const isFirstQuestion = groupIndex === 0 && questionIndex === 0
  const conditionTypeInfo = getConditionTypeInfo()

  const handleChange = (selectedValue) => {
    if (selectedValue === condition.sourceType) return
    const updates = {
      sourceType: selectedValue,
      targetType: conditionTypeInfo.TARGET.CONSTANT,
      cqid: '',
      cfieldname: '',
      cquestions: '',
      method: '',
      value: null,
      answers: [],
    }

    Object.keys(updates).forEach((key) =>
      updateCondition(index, key, updates[key])
    )
  }

  // Ensure default selection for the first question
  const defaultSourceType = useMemo(() => {
    return isFirstQuestion
      ? conditionTypeInfo.SOURCE.PARTICIPANT_DATA
      : condition.sourceType || conditionTypeInfo.SOURCE.QUESTION
  }, [isFirstQuestion, condition.sourceType])

  if (
    isFirstQuestion &&
    condition.sourceType !== conditionTypeInfo.SOURCE.PARTICIPANT_DATA
  ) {
    updateCondition(
      index,
      'sourceType',
      conditionTypeInfo.SOURCE.PARTICIPANT_DATA
    )
  }

  return (
    <div className="text-sm mb-3 p-0 advanced-toggle">
      <ToggleButtons
        id={`question-type-toggle-${index}`}
        toggleOptions={[
          {
            name: t('Question'),
            value: conditionTypeInfo.SOURCE.QUESTION,
            disabled: isFirstQuestion,
          },
          {
            name: t('Participant data'),
            value: conditionTypeInfo.SOURCE.PARTICIPANT_DATA,
          },
        ]}
        className="condition-toggle"
        value={defaultSourceType}
        onChange={handleChange}
      />
    </div>
  )
}
