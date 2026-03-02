import { useState } from 'react'
import classNames from 'classnames'

import { CloseCircleFillIcon, DragIcon } from 'components/icons'
import { ContentEditor } from 'components/UIComponents'
import { L10ns } from 'helpers'

import { SingleChoiceRadioAnswer } from './SingleChoiceRadioAnswer'
import { SingleChoiceImageAnswer } from './SingleChoiceImageAnswer'
import { SingleChoiceDropdownAnswer } from './SingleChoiceDropdownAnswer'
import { SingleChoiceButtonAnswer } from './SingleChoiceButtonAnswer'
import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { dropdownThemeComponents } from './utils'

export const SingleChoiceAnswer = ({
  answer,
  question: { qid, questionThemeName, answers = [] } = {},
  index,
  handleUpdateAnswer,
  handleFocus = () => {},
  handleBlur = () => {},
  handleRemovingAnswers,
  provided = {},
  isFocused = false,
  isNoAnswer = false,
  showDeleteIcon = true,
  surveyLanguage,
}) => {
  const [answerErrors, setAnswerErrors] = useState({})
  const errors = Object.values(answerErrors)

  const onFocus = () => {
    handleFocus()
  }

  const onBlur = () => {
    handleBlur()
  }

  const showImageAnswer =
    questionThemeName === getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.theme
  const showButtonAnswer =
    !isFocused &&
    questionThemeName === getQuestionTypeInfo().SINGLE_CHOICE_BUTTONS.theme
  const showDropDownAnswer =
    !isFocused && dropdownThemeComponents.includes(questionThemeName)
  const showRadioAnswer =
    !isFocused && !showImageAnswer && !showDropDownAnswer && !showButtonAnswer

  const showContentEditable = isFocused && !showImageAnswer

  const value = L10ns({
    l10ns: answer.l10ns,
    prop: 'answer',
    language: surveyLanguage,
  })

  return (
    <div>
      <div
        className={classNames(
          'answer-item d-flex align-items-center position-relative remove-option-button-parent',
          {
            'py-1 mb-2 ': !showButtonAnswer,
            'mb-1': showButtonAnswer && !isFocused,
          }
        )}
      >
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
        {showRadioAnswer && (
          <SingleChoiceRadioAnswer
            answer={answer}
            qid={qid}
            index={index}
            handleBlur={handleBlur}
            handleFocus={handleFocus}
            surveyLanguage={surveyLanguage}
            value={value}
            isNoAnswer
          />
        )}
        {showButtonAnswer && <SingleChoiceButtonAnswer value={value} />}
        {showImageAnswer && (
          <SingleChoiceImageAnswer
            answer={answer}
            qid={qid}
            index={index}
            isFocused={isFocused}
            isNoAnswer={isNoAnswer}
            value={value}
            update={handleUpdateAnswer}
            answerErrors={answerErrors}
            setAnswerErrors={setAnswerErrors}
          />
        )}
        {showDropDownAnswer && (
          <SingleChoiceDropdownAnswer
            answers={answers}
            value={value}
            surveyLanguage={surveyLanguage}
          />
        )}
        {showContentEditable && (
          <ContentEditor
            onFocus={onFocus}
            onBlur={onBlur}
            placeholder={t('Answer option')}
            className="text-secondary choice"
            value={value}
            disabled={isNoAnswer}
            update={handleUpdateAnswer}
          />
        )}
        {showDeleteIcon && (
          <div
            style={{ left: '-20px' }}
            className="cursor-pointer remove-option-button position-absolute"
            onClick={() => handleRemovingAnswers(answer.aid)}
          >
            <CloseCircleFillIcon
              className={classNames('text-danger fill-current', {
                'd-none disabled': !isFocused,
              })}
            />
          </div>
        )}
      </div>
      {errors.length ? (
        <div className="ms-4 mb-4">{Object.values(errors)}</div>
      ) : (
        ''
      )}
    </div>
  )
}
