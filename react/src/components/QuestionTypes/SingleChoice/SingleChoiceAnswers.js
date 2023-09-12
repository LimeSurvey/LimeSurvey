import classNames from 'classnames'
import { FormControl } from 'react-bootstrap'
import { Button } from 'react-bootstrap'
import { PlusLg } from 'react-bootstrap-icons'
import { Draggable } from 'react-beautiful-dnd'

import { DragAndDrop } from 'components/UIComponents'

import { SingleChoiceAnswer } from './SingleChoiceAnswer'
import { QuestionTypeInfo } from '../QuestionTypeInfo'

export const SingleChoiceAnswers = ({
  question: { answers } = {},
  question,
  handleUpdate = () => {},
  isFocused,
  handleUpdateAnswer,
  handleAddingAnswers,
  handleOnDragEnd,
  handleRemovingAnswers,
}) => {
  const noAnswer = {
    assessmentValue: 'No answer.',
    code: '',
    qid: question.qid,
    scaleId: 0,
    sortorder: answers.length,
  }

  const getAnswerStyle = (draggableStyle) => ({
    userSelect: 'none',
    margin: `0 0 8px 0`,
    ...draggableStyle,
  })

  // every answer will show a drop down list of options, but we only need to show one dropdown so we only get one answer.
  const showDropDownAnswers =
    !isFocused && question.type === QuestionTypeInfo.SINGLE_CHOICE_DROPDOWN.type
  const showSingleComment =
    question.type === QuestionTypeInfo.LIST_RADIO_WITH_COMMENT.type
  answers = showDropDownAnswers && answers.length ? [answers[0]] : answers

  return (
    <>
      <div className="single-choice-answers d-flex justify-content-between">
        <div>
          <DragAndDrop
            onDragEnd={handleOnDragEnd}
            droppableId={'droppable'}
            className={classNames('answers-list')}
          >
            {answers.map((answer, index) => (
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
                  >
                    <SingleChoiceAnswer
                      provided={provided}
                      answer={answer}
                      index={index}
                      isFocused={isFocused}
                      handleUpdateAnswer={handleUpdateAnswer}
                      handleRemovingAnswers={handleRemovingAnswers}
                      question={question}
                    />
                  </div>
                )}
              </Draggable>
            ))}
          </DragAndDrop>
          {!showDropDownAnswers &&
            !isFocused &&
            question.mandatory === 'off' && (
              <div key={`${noAnswer.qid}-${noAnswer.aid}`}>
                <div className={classNames('question-body-content')}>
                  <SingleChoiceAnswer
                    isFocused={isFocused}
                    answer={noAnswer}
                    index={answers.length}
                    handleRemovingAnswers={handleRemovingAnswers}
                    handleUpdateAnswer={handleUpdateAnswer}
                    hideDeleteButton={true}
                    question={question}
                    isNoAnswer={true}
                  />
                </div>
              </div>
            )}
        </div>
        {showSingleComment && (
          <div className="w-50">
            <FormControl
              placeholder="Enter your answer here"
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
      <div>
        <Button
          onClick={handleAddingAnswers}
          variant={'outline'}
          className={classNames(
            'text-primary add-radio-question-answer-button px-0 mt-2',
            {
              'd-none disabled': !isFocused,
            }
          )}
          data-testid="single-choice-add-answer-button"
        >
          <PlusLg /> Add choice
        </Button>
      </div>
    </>
  )
}
