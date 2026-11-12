import React, { useEffect, useMemo, useCallback } from 'react'

import { getQuestionTypeInfo, Select } from 'components'
import { getQuestionById } from 'helpers'
import { getAllowedMethods, getConditionTypeInfo } from '../utils'

/**
 * Comparison operator select of a condition. Checkbox fields of multiple
 * choice questions only allow "equals" and "Not equal to", their "Other"
 * text field allows all operators.
 *
 * @param {object} props
 * @param {number} props.index The index of the condition in the scenario.
 * @param {object} props.condition The condition being edited.
 * @param {Function} props.updateCondition Updates a property of the condition.
 * @param {object} props.survey The survey, used to find the source question.
 * @param {object[]} props.previousQuestions The condition questions before the current question.
 * @returns {JSX.Element} The operator select.
 */
export const OperatorSelect = ({
  index,
  condition,
  updateCondition,
  survey,
  previousQuestions = [],
}) => {
  const OPERATOR_OPTIONS = [
    { value: '<', label: t('Less than') },
    { value: '<=', label: t('Less than or equal to') },
    { value: '==', label: t('equals') },
    { value: '!=', label: t('Not equal to') },
    { value: '>=', label: t('Greater than or equal to') },
    { value: '>', label: t('Greater than') },
    { value: 'RX', label: t('Regular expression') },
  ]
  const conditionTypeInfo = getConditionTypeInfo()
  const allowedMethods = getAllowedMethods()
  const questionTypeInfo = getQuestionTypeInfo()

  useEffect(() => {
    if (
      condition.targetType === conditionTypeInfo.TARGET.REGEX &&
      condition.method !== allowedMethods.REGEX
    ) {
      updateCondition(index, 'method', allowedMethods.REGEX)
    }
  }, [condition.targetType, condition.method, index, updateCondition])

  const selectedValue = useMemo(
    () =>
      OPERATOR_OPTIONS.find((option) => option.value === condition.method) ||
      null,
    [condition.method]
  )

  const handleChange = useCallback(
    (selected) => {
      const newMethod = selected?.value || null
      const newTargetType =
        newMethod === allowedMethods.REGEX
          ? conditionTypeInfo.TARGET.REGEX
          : condition.answers.length > 0
            ? conditionTypeInfo.TARGET.ANSWER_OPTIONS
            : conditionTypeInfo.TARGET.CONSTANT

      updateCondition(index, 'method', newMethod)
      if (newMethod === allowedMethods.REGEX)
        updateCondition(index, 'value', null)

      if (
        !condition.targetType ||
        condition.targetType === conditionTypeInfo.TARGET.REGEX ||
        newMethod === allowedMethods.REGEX
      ) {
        updateCondition(index, 'targetType', newTargetType)
      }
    },
    [condition.answers.length, condition.targetType]
  )

  const isMultipleChoiceType = (type) =>
    type === questionTypeInfo.MULTIPLE_CHOICE.type ||
    type === questionTypeInfo.MULTIPLE_CHOICE_WITH_COMMENTS.type

  const filteredOptions = useMemo(() => {
    if (condition.sourceType === conditionTypeInfo.SOURCE.QUESTION) {
      const selectedField = previousQuestions.find(
        (question) => question.cfieldname === condition.cfieldname
      )
      // Fields not listed (yet) fall back to the type of their question in the survey
      const isCheckboxField = selectedField
        ? !!selectedField.isCheckboxField
        : isMultipleChoiceType(
            getQuestionById(+condition.cqid, survey).question?.type
          )
      if (isCheckboxField) {
        return OPERATOR_OPTIONS.map((option) => ({
          ...option,
          isDisabled: ![
            allowedMethods.EQUAL,
            allowedMethods.NOT_EQUAL,
          ].includes(option.value),
        }))
      }
    }

    return [...OPERATOR_OPTIONS]
  }, [
    condition.sourceType,
    condition.cqid,
    condition.cfieldname,
    previousQuestions,
    survey,
  ])

  return (
    <Select
      options={filteredOptions}
      value={selectedValue?.value || null}
      onChange={handleChange}
      placeholder={t('Comparison operator')}
      className="mb-3"
    />
  )
}
