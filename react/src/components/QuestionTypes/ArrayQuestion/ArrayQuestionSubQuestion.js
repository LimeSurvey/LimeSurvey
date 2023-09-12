import { useState } from 'react'
import classNames from 'classnames'

import { L10ns } from 'helpers'
import { ContentEditor } from 'components/UIComponents'
import { DragIcon, CloseCircleFillIcon } from 'components/icons'

import { ArraySubQuestionRadioAnswers } from './ArraySubQuestionRadioAnswers'
import { QuestionTypeInfo } from '../QuestionTypeInfo'
import { ArraySubQuestionTextAnswers } from './ArraySubQuestionTextAnswers'
import { ArraySubQuestionNumberAnswers } from './ArraySubQuestionNumberAnswers'

export const ArrayQuestionSubQuestion = ({
  answers,
  questionThemeName,
  language,
  isFocused,
  highestWidth,
  subQuestionsHeight,
  arrayAnswersWidth,
  dragIconSize,
  provided,
  index,
  subQuestion,
  handleUpdateL10ns,
  handleRemovingSubQuestion,
  showContentEditor,
  scaleId,
  formFieldText = { value: '' },
}) => {
  const value = L10ns({
    prop: 'question',
    language,
    l10ns: subQuestion.l10ns,
  })

  const [title, setTitle] = useState(value)

  const handleTitleUpdate = (value) => {
    setTitle(value)
    handleUpdateL10ns({ question: value }, index)
  }

  return (
    <div
      className={classNames(
        'position-relative d-flex align-items-center array-question-item text-secondary',
        { 'ps-5': isFocused }
      )}
    >
      <div
        className="opacity-0 cursor-pointer remove-item-button"
        onClick={() => handleRemovingSubQuestion(index)}
        style={{
          zIndex: 10,
          position: 'absolute',
          top: '50%',
          left: '4px',
          transform: 'translate(-50%, -50%)',
        }}
      >
        <CloseCircleFillIcon
          className={classNames('text-danger fill-current', {
            'd-none disabled': !isFocused || !showContentEditor,
          })}
        />
      </div>
      <div
        {...provided.dragHandleProps}
        className={classNames({
          'd-none disabled d-flex align-items-center':
            !isFocused || !showContentEditor,
        })}
        style={{
          position: 'absolute',
          top: '50%',
          left: '30px',
          transform: 'translate(-50%, -50%)',
        }}
      >
        <DragIcon className="text-secondary fill-current" />
      </div>
      <div
        style={{
          minWidth: showContentEditor ? highestWidth + dragIconSize : '100px',
          maxWidth: !showContentEditor && '100px',
          display: showContentEditor ? 'block' : 'none',
        }}
      >
        <ContentEditor
          placeholder={`Row ${index + 1}`}
          value={title ? title : formFieldText.value}
          update={(value) => handleTitleUpdate(value)}
          className="array-subquestion-content-editor"
          style={{
            width: 'fit-content',
          }}
        />
      </div>
      {questionThemeName === QuestionTypeInfo.ARRAY.theme && (
        <ArraySubQuestionRadioAnswers
          answers={answers}
          arrayAnswersWidth={arrayAnswersWidth}
          dragIconSize={dragIconSize}
          subQuestionindex={index}
          subQuestionsHeight={subQuestionsHeight}
        />
      )}
      {questionThemeName === QuestionTypeInfo.ARRAY_TEXT.theme && (
        <ArraySubQuestionTextAnswers
          answers={answers}
          arrayAnswersWidth={arrayAnswersWidth}
          dragIconSize={dragIconSize}
          subQuestionindex={index}
          subQuestionsHeight={subQuestionsHeight}
        />
      )}
      {questionThemeName === QuestionTypeInfo.ARRAY_NUMBERS.theme && (
        <ArraySubQuestionNumberAnswers
          answers={answers}
          arrayAnswersWidth={arrayAnswersWidth}
          dragIconSize={dragIconSize}
          subQuestionindex={index}
          subQuestionsHeight={subQuestionsHeight}
        />
      )}
      {questionThemeName === QuestionTypeInfo.ARRAY_COLUMN.theme && (
        <ArraySubQuestionRadioAnswers
          answers={answers}
          arrayAnswersWidth={arrayAnswersWidth}
          dragIconSize={dragIconSize}
          subQuestionindex={index}
          subQuestionsHeight={subQuestionsHeight}
          subQuestionId={subQuestion.qid}
          arrayByColumn={true}
        />
      )}
      {questionThemeName === QuestionTypeInfo.ARRAY_DUAL_SCALE.theme && (
        <ArraySubQuestionRadioAnswers
          answers={answers[scaleId]}
          arrayAnswersWidth={arrayAnswersWidth}
          dragIconSize={dragIconSize}
          subQuestionindex={index}
          subQuestionsHeight={subQuestionsHeight}
        />
      )}
    </div>
  )
}
