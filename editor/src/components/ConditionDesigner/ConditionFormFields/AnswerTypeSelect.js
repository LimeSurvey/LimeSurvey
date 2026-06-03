import { useCallback, useMemo } from 'react'

import { Select } from 'components'

import { getAllowedMethods, getConditionTypeInfo } from '../utils'

export const AnswerTypeSelect = ({
  index,
  condition,
  updateCondition,
  previousQuestions,
}) => {
  const conditionTypeInfo = getConditionTypeInfo()
  const allowedMethods = getAllowedMethods()

  const handleChange = useCallback(
    (selected) => {
      const newValue = selected?.value || ''
      updateCondition(index, 'targetType', newValue)
      if (newValue === conditionTypeInfo.TARGET.REGEX)
        updateCondition(index, 'method', allowedMethods.REGEX)
      updateCondition(
        index,
        'value',
        condition.targetType === newValue ? condition.value : null
      )
    },
    [condition.targetType, condition.value]
  )

  const selectOptions = useMemo(() => {
    const options = []
    if (condition.method !== allowedMethods.REGEX) {
      if (condition.answers?.length > 0) {
        options.push({
          label: t('Select answer option(s)'),
          value: conditionTypeInfo.TARGET.ANSWER_OPTIONS,
        })
      }
      options.push({
        label: t('Free value (constant)'),
        value: 'Constant',
      })
      if (previousQuestions.length > 0) {
        options.push({
          label: t('Compare to answer of other question'),
          value: conditionTypeInfo.TARGET.ANSWER_OF_OTHER_QUESTION,
        })
      }
      options.push({
        label: t('Participant data'),
        value: conditionTypeInfo.TARGET.PARTICIPANT_DATA,
      })
    }

    options.push({
      label: t('RegExp'),
      value: conditionTypeInfo.TARGET.REGEX,
      isDisabled: condition.method !== allowedMethods.REGEX,
    })

    return options
  }, [condition.answers, condition.method, condition.cfieldname])

  const selectedOption = selectOptions.find(
    (option) => option.value === condition.targetType
  )

  return (
    <div className="mb-3">
      <Select
        options={selectOptions}
        value={selectedOption?.value || null}
        onChange={handleChange}
      />
    </div>
  )
}
