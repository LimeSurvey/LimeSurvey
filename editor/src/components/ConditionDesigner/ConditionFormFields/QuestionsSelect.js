import React, { useMemo } from 'react'

import { useFocused } from 'hooks'
import { Select } from 'components'

import {
  getConditionTypeInfo,
  isUsableFieldname,
  toConditionId,
} from '../utils'

export const QuestionsSelect = ({
  index,
  condition,
  updateCondition,
  previousQuestions,
  valueIdentifier = 'cfieldname', // 'cfieldname' for source(question) and 'value' for target(answer)
  onChange,
}) => {
  const { focused } = useFocused()
  const conditionTypeInfo = getConditionTypeInfo()
  const options = useMemo(
    () =>
      previousQuestions.map(({ cfieldname, title, qid, answers }, key) => ({
        value: cfieldname,
        label: `${key + 1}. ${title.replace(/&nbsp;/g, ' ')}`,
        cqid: qid,
        answers,
        // Not selectable while its field name is unknown (the field names
        // of the survey are still loading): It would be saved without one.
        isDisabled: !isUsableFieldname(cfieldname),
      })),
    [previousQuestions]
  )

  // make the selected option identifier dynamic with the questionIdentifier to make the component reusable
  const selectedOption = useMemo(
    () =>
      options.find(({ value }) => value === condition[valueIdentifier]) || null,
    [options, condition[valueIdentifier], valueIdentifier]
  )

  const defaultHandleChange = ({ value, cqid, answers }) => {
    const updates = {
      qid: toConditionId(focused.qid),
      cqid: toConditionId(cqid),
      cfieldname: value,
      cquestions: value,
      answers: answers,
      targetType:
        answers.length > 0
          ? conditionTypeInfo.TARGET.ANSWER_OPTIONS
          : conditionTypeInfo.TARGET.CONSTANT,
      value: null,
      method: '',
    }

    Object.entries(updates).forEach(([key, val]) =>
      updateCondition(index, key, val)
    )
  }

  // Use custom onChange if provided, otherwise fall back to default
  const handleChange = onChange || defaultHandleChange

  return (
    <Select
      options={options}
      value={selectedOption?.value || null}
      onChange={handleChange}
      placeholder={t('Previous questions')}
      className="mb-3 flex-grow-1"
    />
  )
}
