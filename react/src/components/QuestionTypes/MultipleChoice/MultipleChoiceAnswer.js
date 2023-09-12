import ContentEditable from 'react-contenteditable'
import classNames from 'classnames'

import { CloseCircleFillIcon, DragIcon } from 'components/icons'
import { QuestionTypeInfo } from '../QuestionTypeInfo'

import { MultipleChoiceButtonAnswer } from './MultipleChoiceButtonAnswer'
import { MultipleChoiceCheckboxAnswer } from './MultipleChoiceCheckboxAnswer'
import { MultipleChoiceImageAnswer } from './MultipleChoiceImageAnswer'
import { MultipleChoiceShortTextsAnswer } from './MultipleChoiceShortTextsAnswer'
import { MultipleChoiceNumericalAnswer } from './MultipleChoiceNumericalAnswer'
import { Direction } from 'react-range'

export const MultipleChoiceAnswer = ({
  answer,
  questionThemeName,
  index,
  handleUpdateAnswer,
  handleFocus,
  handleBlur,
  provided = {},
  handleRemovingAnswers,
  isFocused = false,
  highestWidth,
  attributes: {
    useSlider = false,
    sliderOrientation = Direction.Right,
    form_field_text = {},
  } = {},
}) => {
  const onFocus = () => {
    handleFocus()
  }

  const onBlur = () => {
    handleBlur()
  }

  const showImageAnswer =
    questionThemeName === QuestionTypeInfo.MULTIPLE_CHOICE_IMAGE_SELECT.theme
  const showButtonsAnswer =
    !isFocused &&
    questionThemeName === QuestionTypeInfo.MULTIPLE_CHOICE_BUTTONS.theme
  const showShortTextsAnswer =
    !isFocused &&
    questionThemeName === QuestionTypeInfo.MULTIPLE_SHORT_TEXTS.theme
  const showNumericalsAnswer =
    !isFocused &&
    questionThemeName === QuestionTypeInfo.MULTIPLE_NUMERICAL_INPUTS.theme
  const showCheckboxesAnswer =
    !isFocused && questionThemeName === QuestionTypeInfo.MULTIPLE_CHOICE.theme
  const showContentEditable = isFocused && !showImageAnswer

  const value = answer.assessmentValue.toString().length
    ? answer.assessmentValue.toString()
    : form_field_text.value
    ? form_field_text.value
    : ''

  return (
    <div className="d-flex multiple-choice-question-answer align-items-center position-relative">
      <div>
        {isFocused ? (
          <div
            {...provided.dragHandleProps}
            className={classNames({
              'disabled opacity-0': !provided.dragHandleProps,
            })}
          >
            <DragIcon className="text-secondary fill-current me-2" />
          </div>
        ) : (
          <span {...provided.dragHandleProps}></span>
        )}
      </div>
      {showImageAnswer && (
        <MultipleChoiceImageAnswer
          answer={answer}
          isFocused={isFocused}
          onChange={(image) => handleUpdateAnswer(image, index)}
          value={value}
        />
      )}
      {showButtonsAnswer && <MultipleChoiceButtonAnswer answer={answer} />}
      {showCheckboxesAnswer && (
        <MultipleChoiceCheckboxAnswer
          answer={answer}
          index={index}
          handleUpdateAnswer={handleUpdateAnswer}
          questionThemeName={questionThemeName}
          handleBlur={handleBlur}
          handleFocus={handleFocus}
          highestWidth={highestWidth}
          value={value}
        />
      )}
      {showShortTextsAnswer && (
        <MultipleChoiceShortTextsAnswer
          answer={answer}
          index={index}
          handleUpdateAnswer={handleUpdateAnswer}
          questionThemeName={questionThemeName}
          handleBlur={handleBlur}
          handleFocus={handleFocus}
          highestWidth={highestWidth}
          value={value}
        />
      )}
      {showNumericalsAnswer && (
        <MultipleChoiceNumericalAnswer
          answer={answer}
          index={index}
          handleUpdateAnswer={handleUpdateAnswer}
          questionThemeName={questionThemeName}
          handleBlur={handleBlur}
          handleFocus={handleFocus}
          highestWidth={highestWidth}
          useSlider={useSlider}
          orientation={sliderOrientation}
          value={value}
        />
      )}
      {showContentEditable && (
        <>
          <ContentEditable
            onFocus={onFocus}
            onBlur={onBlur}
            data-placeholder="Edit answer"
            className="text-secondary"
            html={value}
            onChange={(e) => handleUpdateAnswer(e.target.value, index)}
          />
        </>
      )}
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
