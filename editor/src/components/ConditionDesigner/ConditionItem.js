import React, { useState } from 'react'

import { Button } from 'components'
import { DeleteIcon, RecoverIcon } from 'components/icons'

import {
  AnswerOptionsSelect,
  AnswerTypeSelect,
  ConstantInput,
  OperatorSelect,
  ParticipantDataSelect,
  QuestionsAsAnswerSelect,
  QuestionsSelect,
  QuestionTypeToggle,
  RegexInput,
} from './ConditionFormFields'
import { getConditionTypeInfo } from './utils'

export const ConditionItem = ({
  index,
  condition,
  pervCondition,
  previousQuestions,
  updateCondition,
  removeCondition,
  focused,
  survey,
  isUpdateAction,
}) => {
  const [isDeleted, setIsDeleted] = useState(condition.isDeleted || false)
  const conditionTypeInfo = getConditionTypeInfo()

  const handleToggleDelete = () => {
    if (condition.cid) {
      const newIsDeleted = !isDeleted
      setIsDeleted(newIsDeleted)
      updateCondition(index, 'isDeleted', newIsDeleted)
    } else {
      removeCondition(index)
    }
  }

  return (
    <div key={`condition-item-${index}`}>
      {index > 0 && (
        <div className="text-center text-muted mb-2">
          <strong>
            {pervCondition.cfieldname === condition.cfieldname
              ? t('OR')
              : t('AND')}
          </strong>
        </div>
      )}

      <div className="d-flex justify-content-between align-items-center mb-2">
        <span className="text-start text-muted fw-bold">
          {t('Display only if')}
        </span>
        <Button
          variant="btn bg-transparent border-0 shadow-none p-0"
          onClick={handleToggleDelete}
        >
          {isUpdateAction && isDeleted ? (
            <RecoverIcon width="18" height="18" className="fill-current" />
          ) : (
            <DeleteIcon className="fill-current" />
          )}
        </Button>
      </div>

      <div className={`${isDeleted ? 'disabled' : ''}`}>
        <QuestionTypeToggle
          index={index}
          condition={condition}
          updateCondition={updateCondition}
          focused={focused}
        />

        <div className="mb-4">
          {condition.sourceType === conditionTypeInfo.SOURCE.QUESTION ? (
            <QuestionsSelect
              index={index}
              condition={condition}
              updateCondition={updateCondition}
              previousQuestions={previousQuestions}
            />
          ) : (
            <ParticipantDataSelect
              index={index}
              condition={condition}
              updateCondition={updateCondition}
              survey={survey}
            />
          )}
        </div>

        {condition.cfieldname !== '' && (
          <div className="mb-4">
            <OperatorSelect
              index={index}
              condition={condition}
              updateCondition={updateCondition}
              survey={survey}
            />
          </div>
        )}

        {condition.cfieldname !== '' && condition.method !== '' && (
          <>
            <div className="mb-3">
              <span className="text-start text-muted fw-bold mb-2 d-block">
                {t('Reference value')}
              </span>
              <AnswerTypeSelect
                index={index}
                condition={condition}
                updateCondition={updateCondition}
                previousQuestions={previousQuestions}
              />
            </div>

            {condition.targetType === conditionTypeInfo.TARGET.CONSTANT && (
              <ConstantInput
                index={index}
                condition={condition}
                updateCondition={updateCondition}
              />
            )}
            {condition.targetType ===
              conditionTypeInfo.TARGET.ANSWER_OPTIONS && (
              <AnswerOptionsSelect
                index={index}
                condition={condition}
                updateCondition={updateCondition}
              />
            )}
            {condition.targetType ===
              conditionTypeInfo.TARGET.ANSWER_OF_OTHER_QUESTION && (
              <QuestionsAsAnswerSelect
                index={index}
                condition={condition}
                updateCondition={updateCondition}
                previousQuestions={previousQuestions}
              />
            )}
            {condition.targetType ===
              conditionTypeInfo.TARGET.PARTICIPANT_DATA && (
              <ParticipantDataSelect
                index={index}
                condition={condition}
                updateCondition={updateCondition}
                type="target"
                survey={survey}
              />
            )}
            {condition.targetType === conditionTypeInfo.TARGET.REGEX && (
              <RegexInput
                index={index}
                condition={condition}
                updateCondition={updateCondition}
              />
            )}
          </>
        )}
      </div>
    </div>
  )
}
