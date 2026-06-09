import { useState } from 'react'
import { Direction } from 'react-range'
import classNames from 'classnames'

import { CloseCircleFillIcon, DragIcon } from 'components/icons'
import { ContentEditor } from 'components/UIComponents'
import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { L10ns, getAttributeValue } from 'helpers'

import { MultipleChoiceButtonSubquestion } from './MultipleChoiceButtonSubquestion'
import { MultipleChoiceCheckboxSubquestion } from './MultipleChoiceCheckboxSubquestion'
import { MultipleChoiceImageSubquestion } from './MultipleChoiceImageSubquestion'
import { MultipleChoiceShortTextSubquestion } from './MultipleChoiceShortTextSubquestion'
import { MultipleChoiceNumericalSubquestion } from './MultipleChoiceNumericalSubquestion'

export const MultipleChoiceSubquestion = ({
  index,
  subQuestion,
  questionThemeName,
  handleUpdateSubquestion,
  handleRemovingSubquestions,
  provided = {},
  isFocused = false,
  language,
  isSurveyActive,
  attributes: {
    slider_layout,
    sliderOrientation = Direction.Right,
    form_field_text = {},
    commented_checkbox = {},
  } = {},
}) => {
  const [subquestionErrors, setSubquestionErrors] = useState({})
  const useSlider = getAttributeValue(slider_layout, language)

  const errors = Object.values(subquestionErrors)

  const showImageSubquestion =
    questionThemeName ===
    getQuestionTypeInfo().MULTIPLE_CHOICE_IMAGE_SELECT.theme
  const showButtonSubquestion =
    !isFocused &&
    questionThemeName === getQuestionTypeInfo().MULTIPLE_CHOICE_BUTTONS.theme
  const showShortTextSubquestion =
    !isFocused &&
    questionThemeName === getQuestionTypeInfo().MULTIPLE_SHORT_TEXTS.theme
  const showNumericalSubquestion =
    !isFocused &&
    questionThemeName === getQuestionTypeInfo().MULTIPLE_NUMERICAL_INPUTS.theme
  const showCheckboxSubquestion =
    !isFocused &&
    (questionThemeName === getQuestionTypeInfo().MULTIPLE_CHOICE.theme ||
      questionThemeName ===
        getQuestionTypeInfo().MULTIPLE_CHOICE_WITH_COMMENTS.theme)

  const showContentEditable = isFocused && !showImageSubquestion

  const l10nsValue = L10ns({
    prop: 'question',
    language,
    l10ns: subQuestion.l10ns,
  })

  const value = l10nsValue
    ? l10nsValue
    : form_field_text.value
      ? form_field_text.value
      : ''

  return (
    <div>
      <div
        data-testid="multiple-choice-subquestion"
        className={classNames(
          'answer-item d-flex align-items-center position-relative remove-option-button-parent',
          { ' w-100': !isFocused }
        )}
      >
        <div
          className="cursor-pointer position-absolute remove-option-button"
          onClick={() => handleRemovingSubquestions(subQuestion.qid)}
          style={{ left: -24 }}
          data-testid="remove-subquestion-button"
        >
          <CloseCircleFillIcon
            className={classNames('text-danger fill-current', {
              'd-none': !isFocused || isSurveyActive,
            })}
          />
        </div>
        <div {...provided.dragHandleProps}>
          <DragIcon
            className={classNames('text-secondary fill-current', {
              'd-none': !isFocused,
            })}
          />
        </div>
        {showImageSubquestion && (
          <MultipleChoiceImageSubquestion
            subQuestion={subQuestion}
            isFocused={isFocused}
            value={value}
            index={index}
            update={handleUpdateSubquestion}
            subquestionErrors={subquestionErrors}
            setSubquestionErrors={setSubquestionErrors}
          />
        )}
        {showButtonSubquestion && (
          <MultipleChoiceButtonSubquestion value={value} />
        )}
        {showCheckboxSubquestion && (
          <MultipleChoiceCheckboxSubquestion
            subQuestion={subQuestion}
            index={index}
            questionThemeName={questionThemeName}
            value={value}
            commentedCheckbox={commented_checkbox?.['']}
          />
        )}
        {showShortTextSubquestion && (
          <MultipleChoiceShortTextSubquestion
            subQuestion={subQuestion}
            index={index}
            questionThemeName={questionThemeName}
            value={value}
          />
        )}
        {showNumericalSubquestion && (
          <MultipleChoiceNumericalSubquestion
            subQuestion={subQuestion}
            index={index}
            questionThemeName={questionThemeName}
            useSlider={useSlider}
            orientation={sliderOrientation}
            value={value}
            isFocused={isFocused}
          />
        )}
        <div className="d-flex gap-2 multiple-choice-subquestion-content-editor align-items-center position-relative remove-option-button-parent">
          {showContentEditable && (
            <ContentEditor
              placeholder={t('Subquestion')}
              className="text-secondary my-1 choice"
              testId="choice-content-editor"
              value={value}
              update={(value) => handleUpdateSubquestion(value, index)}
            />
          )}
        </div>
      </div>
      {errors.length ? (
        <div className="ms-4 mb-4">{Object.values(errors)}</div>
      ) : (
        ''
      )}
    </div>
  )
}
