import { useRef } from 'react'

import classNames from 'classnames'
import { Draggable } from 'react-beautiful-dnd'
import { Button, Form } from 'react-bootstrap'
import { PlusLg } from 'react-bootstrap-icons'

import { useAppState } from 'hooks'
import { STATES, Entities } from 'helpers'
import { getTooltipMessages } from 'helpers/options'
import { DragAndDrop } from 'components/UIComponents'
import { TooltipContainer } from 'components'

import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { MultipleChoiceSubquestion } from './MultipleChoiceSubquestion'

export const MultipleChoice = ({
  question: { subquestions = [], questionThemeName, attributes = {} } = {},
  handleChildLUpdate,
  showCommentsInputs = false,
  isFocused = false,
  language,
  handleChildAdd,
  handleOnChildDragEnd,
  handleChildDelete,
}) => {
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)
  const containersRef = useRef(null)

  const subquestionsRef = useRef(null)
  subquestionsRef.current = subquestions

  const isButtonsTheme =
    questionThemeName === getQuestionTypeInfo().MULTIPLE_CHOICE_BUTTONS.theme

  const getSubquestionStyle = (draggableStyle) => ({
    userSelect: 'none',
    ...draggableStyle,
  })

  const handleOnDragEnd = (dropResult) => {
    handleOnChildDragEnd(dropResult, subquestions, Entities.subquestion)
  }

  const handleRemovingSubquestions = (qid) => {
    handleChildDelete(qid, subquestions, Entities.subquestion)
  }

  return (
    <div className="multiple-choice-question">
      <Form ref={containersRef}>
        <DragAndDrop
          onDragEnd={handleOnDragEnd}
          droppableId={'droppable'}
          className={classNames('sub-questions-list ms-1', {
            'd-flex flex-wrap': !isFocused && isButtonsTheme,
            'flex-column': isButtonsTheme,
          })}
        >
          {subquestions?.map((subQuestion, index) => (
            <Draggable
              key={`${subQuestion.qid}-multiple-choice`}
              draggableId={`${subQuestion.qid}-multiple-choice`}
              index={index}
            >
              {(provided, snapshot) => (
                <div
                  ref={provided.innerRef}
                  {...provided.draggableProps}
                  style={getSubquestionStyle(provided.draggableProps.style)}
                  className={classNames(
                    {
                      'focus-element': snapshot.isDragging,
                      'mb-1': isButtonsTheme && !isFocused,
                    },
                    'question-body-content'
                  )}
                >
                  <MultipleChoiceSubquestion
                    index={index}
                    provided={provided}
                    isFocused={isFocused}
                    subQuestion={subQuestion}
                    handleUpdateSubquestion={(value) =>
                      handleChildLUpdate(
                        value,
                        index,
                        subquestionsRef.current,
                        Entities.subquestion
                      )
                    }
                    handleRemovingSubquestions={handleRemovingSubquestions}
                    showCommentsInputs={showCommentsInputs}
                    questionThemeName={questionThemeName}
                    attributes={attributes}
                    isSurveyActive={isSurveyActive}
                    language={language}
                  />
                </div>
              )}
            </Draggable>
          ))}
        </DragAndDrop>
      </Form>
      <div className="add-sub-question-button">
        <TooltipContainer
          tip={getTooltipMessages().ACTIVE_DISABLED}
          showTip={isSurveyActive}
        >
          <Button
            onClick={() => handleChildAdd(subquestions, Entities.subquestion)}
            variant={'outline'}
            className={classNames(
              'text-primary d-flex add-choice-button align-items-center gap-2 p-0 mt-4 border-none',
              {
                'd-none disabled': !isFocused,
              }
            )}
            data-testid="add-sub-question-button"
            disabled={isSurveyActive}
          >
            <PlusLg /> {t('Add subquestion')}
          </Button>
        </TooltipContainer>
      </div>
    </div>
  )
}
