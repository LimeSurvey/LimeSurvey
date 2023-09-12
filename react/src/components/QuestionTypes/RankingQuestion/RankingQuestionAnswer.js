import { useState } from 'react'
import classNames from 'classnames'
import { isString } from 'lodash'

import { EditableImage } from 'components/EditableImage/EditableImage'
import { ContentEditor, DropZone } from 'components/UIComponents'
import { CloseCircleFillIcon, DragIcon } from 'components/icons'

// Todo: handle switching between image and text using attributes.
export const RankingQuestionAnswer = ({
  answer: { assessmentValue = '', aid } = {},
  onChange = () => {},
  isFocused,
  provided = {},
  handleRemovingAnswers,
}) => {
  const [answerValue, setAnswerValue] = useState(assessmentValue)

  const handleAnswerUpdate = (value) => {
    onChange(value)
    setAnswerValue(value)
  }

  return (
    <div className="d-flex answer-item align-items-center position-relative">
      <div
        style={{ left: '-20px' }}
        className="opacity-0 cursor-pointer remove-option-button position-absolute "
        onClick={() => handleRemovingAnswers(aid)}
      >
        <CloseCircleFillIcon
          className={classNames('text-danger fill-current', {
            'd-none': !isFocused,
          })}
        />
      </div>
      <div
        {...provided.dragHandleProps}
        className={classNames({
          'disabled opacity-0': !provided.dragHandleProps,
        })}
      >
        <DragIcon className="text-secondary fill-current me-2" />
      </div>
      <div className="d-flex align-items-center gap-3">
        {!answerValue?.preview && isString(answerValue) && (
          <ContentEditor
            update={handleAnswerUpdate}
            value={answerValue}
            placeholder="Add text here..."
          />
        )}
        {isFocused && !answerValue && <div>OR</div>}
        {isFocused && !answerValue.length && !answerValue.preview && (
          <DropZone
            onReaderResult={(result) => handleAnswerUpdate(result)}
            image={answerValue.preview}
          />
        )}
        {answerValue?.preview && (
          <EditableImage
            update={handleAnswerUpdate}
            imageSrc={answerValue}
            width={'200px'}
            showControllers={isFocused}
            handleRemoveImage={() => handleAnswerUpdate('')}
          />
        )}
      </div>
    </div>
  )
}
