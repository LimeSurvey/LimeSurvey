import classNames from 'classnames'

import { getAttributeValue, isTrue, RandomNumber } from 'helpers'
import { useAppState } from 'hooks'
import { STATES } from 'helpers'

import { QuestionBodyPreview, QuestionHeaderPreview } from './'
import { useState } from 'react'

export const QuestionPreview = ({
  language,
  surveySettings = {},
  valueInfo,
  onSave,
  onCancel,
  disableUpdatingResponses: disableUpdatingResponsesProp,
}) => {
  // Get permissions from global state if not provided via prop
  const [hasResponsesUpdatePermission] = useAppState(
    STATES.HAS_RESPONSES_UPDATE_PERMISSION,
    false
  )
  const [hasResponsesReadPermission] = useAppState(
    STATES.HAS_RESPONSES_READ_PERMISSION,
    false
  )

  // Determine if responses can be updated
  // If prop is explicitly provided, use it; otherwise check permissions
  const disableUpdatingResponses =
    disableUpdatingResponsesProp !== undefined
      ? disableUpdatingResponsesProp
      : !hasResponsesUpdatePermission

  // Determine if responses can be viewed
  const canViewResponses =
    hasResponsesReadPermission || hasResponsesUpdatePermission
  const { question, questionNumber } = valueInfo
  // We are using uncontrolled inputs to changing the key will re-render the component and we will get the initial value.
  const [key, setKey] = useState(RandomNumber(1, 9999999999))

  const handleOnCancel = () => {
    setKey(RandomNumber(1, 9999999999))
    onCancel()
  }

  // If user has no read or update permissions, don't render the question
  if (!canViewResponses) {
    return (
      <div className="question-preview question position-relative">
        <div className="alert alert-warning">
          {t("You don't have permission to view this response.")}
        </div>
      </div>
    )
  }

  return (
    <div
      key={`question-preview-${key}`}
      id={`${question.qid}-question`}
      className={classNames(
        'question-preview question position-relative',
        getAttributeValue(question?.attributes?.cssclass),
        {
          'opacity-25': isTrue(
            getAttributeValue(question?.attributes?.hide_question)
          ),
        }
      )}
    >
      <div
        className={classNames('w-100', {
          'w-50': question?.attributes?.image?.preview,
        })}
        data-testid="question-container"
      >
        <div>
          <QuestionHeaderPreview
            language={language}
            question={question}
            questionNumber={questionNumber}
          />
        </div>
        <div className="question-body-container">
          <QuestionBodyPreview
            surveySettings={surveySettings}
            language={language}
            question={question}
            valueInfo={valueInfo}
            onSave={onSave}
            onCancel={handleOnCancel}
            disableUpdatingResponses={disableUpdatingResponses}
          />
        </div>
      </div>
    </div>
  )
}
