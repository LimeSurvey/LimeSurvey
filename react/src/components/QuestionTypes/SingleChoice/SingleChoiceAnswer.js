import ContentEditable from 'react-contenteditable'
import classNames from 'classnames'

import { CloseCircleFillIcon, DragIcon } from 'components/icons'

import { SingleChoiceRadioAnswer } from './SingleChoiceRadioAnswer'
import { QuestionTypeInfo } from '../QuestionTypeInfo'
import { SingleChoiceImageAnswer } from './SingleChoiceImageAnswer'
import { SingleChoiceDropdownAnswer } from './SingleChoiceDropdownAnswer'
import { SingleChoiceButtonAnswer } from './SingleChoiceButtonAnswer'

export const SingleChoiceAnswer = ({
  answer,
  question: {
    qid,
    questionThemeName,
    attributes: { form_field_text = {} } = {},
    answers = [],
  } = {},
  index,
  handleUpdateAnswer,
  handleFocus = () => {},
  handleBlur = () => {},
  handleRemovingAnswers,
  provided = {},
  isFocused = false,
  isNoAnswer = false,
}) => {
  const onFocus = () => {
    handleFocus()
  }

  const onBlur = () => {
    handleBlur()
  }

  const showImageAnswer =
    questionThemeName === QuestionTypeInfo.SINGLE_CHOICE_LIST_IMAGE_SELECT.theme
  const showButtonAnswer =
    !isFocused &&
    questionThemeName === QuestionTypeInfo.SINGLE_CHOICE_BUTTONS.theme
  const showDropDownAnswer =
    !isFocused &&
    questionThemeName === QuestionTypeInfo.SINGLE_CHOICE_DROPDOWN.theme
  const showRadioAnswer =
    !isFocused && !showImageAnswer && !showDropDownAnswer && !showButtonAnswer
  const showContentEditable = isFocused && !showImageAnswer

  const value = answer.assessmentValue.toString().length
    ? answer.assessmentValue.toString()
    : form_field_text.value
    ? form_field_text.value
    : ''

  return (
    <div className="answer-item py-2 mb-2 d-flex align-items-center gap-2 position-relative">
      {isFocused ? (
        <div
          {...provided.dragHandleProps}
          className={classNames({
            'disabled opacity-0': !provided.dragHandleProps,
          })}
          style={{ height: '26px' }}
        >
          <DragIcon className="text-secondary fill-current ms-2 me-2" />
        </div>
      ) : (
        <span {...provided.dragHandleProps}></span>
      )}
      <div className="d-flex gap-2 align-items-center w-100">
        <>
          {showRadioAnswer && (
            <SingleChoiceRadioAnswer
              answer={answer}
              qid={qid}
              value={value}
              handleUpdateAnswer={handleUpdateAnswer}
              index={index}
              handleBlur={handleBlur}
              handleFocus={handleFocus}
            />
          )}
          {showButtonAnswer && <SingleChoiceButtonAnswer answer={answer} />}
          {showImageAnswer && (
            <SingleChoiceImageAnswer
              answer={answer}
              qid={qid}
              isFocused={isFocused}
              onChange={(image) => handleUpdateAnswer(image, index)}
              isNoAnswer={isNoAnswer}
              value={value}
            />
          )}
          {showDropDownAnswer && (
            <SingleChoiceDropdownAnswer
              answers={answers}
              handleUpdateAnswer={handleUpdateAnswer}
              value={value}
            />
          )}
          {showContentEditable && (
            <ContentEditable
              onFocus={onFocus}
              onBlur={onBlur}
              data-placeholder="Edit answer"
              className="text-secondary"
              html={value}
              onChange={(e) => handleUpdateAnswer(e.target.value, index)}
            />
          )}
        </>
      </div>
      <div
        style={{ left: '-20px' }}
        className="opacity-0 cursor-pointer remove-option-button position-absolute "
        onClick={() => handleRemovingAnswers(answer.aid)}
      >
        <CloseCircleFillIcon
          className={classNames('text-danger fill-current', {
            'd-none disabled': !isFocused,
          })}
        />
      </div>
    </div>
  )
}
