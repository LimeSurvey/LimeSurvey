import classNames from 'classnames'

import { ContentEditor } from 'components'
import { CloseCircleFillIcon, DragIcon } from 'components/icons'
import { RemoveHTMLTagsInString } from 'helpers'
import { useState } from 'react'

export const ArrayQuestionAnswersTitle = ({
  formFieldText = { value: '' },
  isFocused,
  handleRemovingAnswer,
  dragIconSize,
  provided,
  highestHeight,
  handleAnswerTitleUpdate,
  answer,
  index,
}) => {
  const [answerValue, setAnswerValue] = useState(
    typeof answer.assessmentValue === 'string' &&
      answer.assessmentValue.length > 0 &&
      answer.assessmentValue
  )

  const handleAnswerUpdate = (value) => {
    handleAnswerTitleUpdate(value, index)
    setAnswerValue(RemoveHTMLTagsInString(value) ? value : '')
  }

  return (
    <>
      <div
        className={classNames('d-flex position-relative', {
          'mt-3': isFocused,
        })}
      >
        <div
          className="d-flex align-items-center"
          {...provided.dragHandleProps}
          style={{
            zIndex: 10,
            position: 'absolute',
            top: '20px',
            left: '10px',
            transform: 'translate(-50%, -50%)',
          }}
        >
          {isFocused && <DragIcon className="text-secondary fill-current" />}
        </div>
        <div
          className="cursor-pointer remove-item-button"
          onClick={() => handleRemovingAnswer(index)}
          style={{
            position: 'absolute',
            top: '-10px',
            left: '50%',
            transform: 'translate(-50%, -50%)',
          }}
        >
          <CloseCircleFillIcon
            className={classNames('text-danger fill-current', {
              'd-none disabled': !isFocused,
            })}
          />
        </div>
        <div
          style={{
            minHeight: highestHeight,
            paddingLeft: dragIconSize,
            paddingRight: dragIconSize,
          }}
        >
          <ContentEditor
            className={classNames('text-start array-answer-content-editor')}
            placeholder={`Column ${index + 1}`}
            value={answerValue ? answerValue : formFieldText.value}
            update={(value) => handleAnswerUpdate(value)}
            style={{ width: 'max-content' }}
          />
        </div>
      </div>
    </>
  )
}
