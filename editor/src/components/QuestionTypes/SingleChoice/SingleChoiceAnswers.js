import classNames from 'classnames'
import { FormControl } from 'react-bootstrap'
import { Button } from 'react-bootstrap'
import { PlusLg } from 'react-bootstrap-icons'
import { Draggable } from 'react-beautiful-dnd'

import { DragAndDrop } from 'components/UIComponents'
import {
  getAnswerExample,
  getNextAnswerCode,
  isTrue,
  getNoAnswerLabel,
} from 'helpers'

import { SingleChoiceAnswer } from './SingleChoiceAnswer'
import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { format } from 'util'
import { dropdownThemeComponents } from './utils'

export const SingleChoiceAnswers = ({
  question: { answers = [], qid } = {},
  question,
  handleUpdate = () => {},
  isFocused,
  handleUpdateAnswer,
  handleChildAdd,
  handleOnDragEnd,
  handleRemovingAnswers,
  handleUpdateNoAnswerAttribute,
  surveyLanguage,
  surveySettings,
}) => {
  // every answer will show a drop down list of options, but we only need to show one dropdown so we only get one answer.
  const isDropDownType = dropdownThemeComponents.includes(
    question.questionThemeName
  )
  const showDropDownAnswers = !isFocused && isDropDownType
  const showSingleComment =
    question.type ===
    getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO_WITH_COMMENT.type
  const showNoAnswer =
    !isDropDownType &&
    !isTrue(question.mandatory) &&
    surveySettings.showNoAnswer
  let answerValues = showDropDownAnswers ? [answers[0] || {}] : answers

  const noAnswer = getAnswerExample({
    qid: qid,
    sortOrder: answers.length + 1,
    code: getNextAnswerCode(null, null, answers.length),
    languages: surveySettings.languages,
    languageValue: getNoAnswerLabel(true),
  })

  const getAnswerStyle = (draggableStyle) => ({
    userSelect: 'none',
    ...draggableStyle,
  })

  return (
    <>
      <div className="single-choice-answers">
        <DragAndDrop
          onDragEnd={handleOnDragEnd}
          droppableId={'droppable'}
          className={classNames('answers-list d-flex flex-wrap flex-column')}
        >
          {answerValues?.map((answer, index) => (
            <Draggable
              key={`${answer.qid}-${answer.aid}`}
              draggableId={`${answer.qid}-${answer.aid}`}
              index={index}
            >
              {(provided, snapshot) => (
                <div
                  ref={provided.innerRef}
                  {...provided.draggableProps}
                  style={getAnswerStyle(provided.draggableProps.style)}
                  className={classNames(
                    {
                      'focus-element': snapshot.isDragging,
                    },
                    'question-body-content'
                  )}
                  data-testid="single-choice-subquestion"
                >
                  <SingleChoiceAnswer
                    provided={provided}
                    answer={answer}
                    index={index}
                    isFocused={isFocused}
                    handleUpdateAnswer={(value) =>
                      handleUpdateAnswer(value, index)
                    }
                    handleRemovingAnswers={handleRemovingAnswers}
                    question={question}
                    surveyLanguage={surveyLanguage}
                  />
                </div>
              )}
            </Draggable>
          ))}
        </DragAndDrop>
        {showNoAnswer && (
          <div className={classNames('question-body-content')}>
            <SingleChoiceAnswer
              isFocused={isFocused}
              answer={noAnswer}
              index={answers.length}
              handleRemovingAnswers={handleRemovingAnswers}
              handleUpdateAnswer={(value) =>
                handleUpdateNoAnswerAttribute(value)
              }
              hideDeleteButton={true}
              question={question}
              isNoAnswer={true}
              showDeleteIcon={false}
            />
          </div>
        )}
        {showSingleComment && (
          <div className="w-50">
            <FormControl
              placeholder={st('Enter your comment here')}
              as="textarea"
              rows={6}
              maxLength={Infinity}
              data-testid="text-question-answer-input"
              value={question?.comment}
              onChange={({ target: { value } }) => {
                handleUpdate({ comment: value })
              }}
            />
          </div>
        )}
      </div>
      {showDropDownAnswers && (
        <div className="added-choice-number">
          {answers.length === 1
            ? t('1 answer option')
            : format(t('%d answer options'), answers.length)}
        </div>
      )}
      <div>
        <Button
          onClick={handleChildAdd}
          variant={'outline'}
          className={classNames('text-primary add-choice-button px-0 mt-2', {
            'd-none disabled': !isFocused,
          })}
          data-testid="add-sub-question-button"
        >
          <PlusLg /> {t('Add answer')}
        </Button>
      </div>
    </>
  )
}
