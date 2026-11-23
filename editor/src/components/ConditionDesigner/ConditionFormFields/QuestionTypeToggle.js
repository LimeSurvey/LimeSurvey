import { useLayoutEffect, useMemo } from 'react'

import { ToggleButtons } from 'components'

import { getConditionTypeInfo } from '../utils'

/**
 * @param {boolean} isFirstQuestion Whether the question is the first one in the
 *   survey. Comes from the question's position resolved from the survey, not from
 *   the focus cache, which can still hold old indices right after a reorder.
 */
export const QuestionTypeToggle = ({
  index,
  condition,
  updateCondition,
  isFirstQuestion = false,
}) => {
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

  // The first question has no previous questions, so only participant data
  // can be used as the source. Use the same reset as switching the toggle by
  // hand: a condition that was question-sourced (e.g. the question moved to
  // the first position) must not keep its question fields, or its question
  // code in `cfieldname` would be saved as a participant attribute name.
  // Done in a layout effect: updateCondition sets the parent's state, which
  // must not happen while rendering, and a layout effect applies it before
  // paint, so the question select never flashes.
  useLayoutEffect(() => {
    if (
      isFirstQuestion &&
      condition.sourceType !== conditionTypeInfo.SOURCE.PARTICIPANT_DATA
    ) {
      handleChange(conditionTypeInfo.SOURCE.PARTICIPANT_DATA)
    }
  }, [isFirstQuestion, condition.sourceType])

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
