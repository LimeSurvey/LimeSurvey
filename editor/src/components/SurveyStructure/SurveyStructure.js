import { useState, useEffect } from 'react'
import { DragDropContext, Draggable, Droppable } from 'react-beautiful-dnd'
import classNames from 'classnames'
import Button from 'react-bootstrap/Button'
import { useNavigate, useParams } from 'react-router-dom'

import {
  arrayDeleteItem,
  DuplicateQuestionGroup,
  IsElementOnScreen,
  ScrollToElement,
  confirmAlert,
  moveQuestionGroup,
  createBufferOperation,
  getReorganizedQuestionGroups,
  STATES,
  MoveQuestion,
  getReorganizedQuestions,
  InsertElementAndIncrementProperty,
  DuplicateQuestion,
} from 'helpers'
import { useFocused, useAppState, useBuffer, useSurvey } from 'hooks'
import { SideBarHeader } from 'components/SideBar'
import { CloseIcon } from 'components/icons'

import { getQuestionTypeInfo } from '../QuestionTypes'
import { RowPinned } from './RowPinned'
import { RowQuestionGroup } from './RowQuestionGroup'
import { arrayInsertItem } from '../../helpers/arrayInsertItem'

export const SurveyStructure = () => {
  const { surveyId } = useParams()
  const navigate = useNavigate()
  const { setFocused, unFocus, focused } = useFocused()
  const {
    survey: { questionGroups = [], language, showWelcome, sid } = {},
    survey,
    update,
  } = useSurvey(surveyId)

  const { addToBuffer } = useBuffer()

  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )

  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE, false)
  const [isDraggingOutOfGroup, setIsDraggingOutOfGroup] = useState(false)

  useEffect(() => {
    const allGroupsElement = document.querySelector(
      '[data-rbd-droppable-id="all-groups"]'
    )
    if (allGroupsElement) {
      if (isDraggingOutOfGroup || !hasSurveyUpdatePermission) {
        allGroupsElement.style.cursor = 'not-allowed'
      } else {
        allGroupsElement.style.cursor = 'auto'
      }

      return () => {
        allGroupsElement.style.cursor = 'auto'
      }
    }
  }, [isDraggingOutOfGroup, hasSurveyUpdatePermission])

  const handleUpdate = (questionGroups) => {
    survey.questionGroups = questionGroups
    update({ ...survey })
  }

  const handleClose = () => {
    navigate(`/survey/${sid}`)
  }

  const handleUpdateQuestionGroup = (questionGroup, index) => {
    const updatedQuestionGroups = [...questionGroups]
    updatedQuestionGroups[index] = questionGroup

    handleUpdate(updatedQuestionGroups)
  }

  const handleGroupDeletion = (questionGroup, index) => {
    confirmAlert({ icon: 'warning' }).then(({ isConfirmed }) => {
      if (!isConfirmed) {
        return
      }

      const [updatedQuestionGroups] = arrayDeleteItem(questionGroups, index)

      const operation = createBufferOperation(questionGroup.gid)
        .questionGroup()
        .delete()

      unFocus()
      handleUpdate(updatedQuestionGroups)
      addToBuffer(operation)
    })
  }

  const handleOnDragEnd = (dropResult) => {
    setIsDraggingOutOfGroup(false)
    const { source, destination, type } = dropResult
    // Dropped outside the list or no permission
    if (!destination || !hasSurveyUpdatePermission) {
      return
    }

    const currentIndex = dropResult.source.index
    const newIndex = dropResult.destination.index

    // Handle group reordering
    let reordered = reorderGroup(type, currentIndex, newIndex)

    if (!reordered) {
      // Handle moving question within the same group
      reordered = reorderQuestionWithinGroup(
        source,
        destination,
        currentIndex,
        newIndex
      )
    }

    if (!reordered) {
      // Handle moving question between groups
      reorderQuestionToNewGroup(source, destination)
    }
  }

  const onDragUpdate = (update) => {
    const { destination, source } = update
    if (!destination) {
      return
    }
    const isOutOfGroup =
      destination.droppableId !== source.droppableId && isSurveyActive
    setIsDraggingOutOfGroup(isOutOfGroup)
  }

  const reorderGroup = (type, currentIndex, newIndex) => {
    if (type === 'group') {
      const reorderedQuestionGroups = moveQuestionGroup(
        questionGroups,
        currentIndex,
        newIndex
      )

      const operation = createBufferOperation()
        .questionGroupReorder()
        .update(getReorganizedQuestionGroups(reorderedQuestionGroups))
      addToBuffer(operation)
      handleUpdate(reorderedQuestionGroups)
      if (
        focused.gid === reorderedQuestionGroups[currentIndex].gid &&
        focused.qid === undefined
      ) {
        setFocused({ ...reorderedQuestionGroups[newIndex] }, newIndex)
      }

      return true
    }

    return false
  }

  const reorderQuestionWithinGroup = (
    source,
    destination,
    currentIndex,
    newIndex
  ) => {
    if (source.droppableId === destination.droppableId) {
      const groupIndex = source.droppableId.replace('g', '')
      const questions = questionGroups[groupIndex].questions
      const { movedQuestion, reorderedQuestions } = MoveQuestion(
        questions,
        currentIndex,
        newIndex
      )
      const sortOrder = groupIndex + 1
      const props = {
        [movedQuestion.gid]: {
          sortOrder: sortOrder,
          questions: getReorganizedQuestions(reorderedQuestions),
        },
      }
      const operation = createBufferOperation()
        .questionGroupReorder()
        .update(props)
      addToBuffer(operation)
      questionGroups[groupIndex].questions = reorderedQuestions
      handleUpdate(questionGroups)
      setFocused(movedQuestion, groupIndex, newIndex)
      return true
    }
    return false
  }

  const reorderQuestionToNewGroup = (source, destination) => {
    if (!isSurveyActive) {
      const sourceGroupIndex = source.droppableId.replace('g', '')
      const destGroupIndex = destination.droppableId.replace('g', '')
      // remove question from source and add it to destination group
      const [sourceQuestions, [movedQuestion]] = arrayDeleteItem(
        questionGroups[sourceGroupIndex].questions,
        source.index
      )
      const destQuestions = arrayInsertItem(
        questionGroups[destGroupIndex].questions,
        destination.index,
        movedQuestion
      )

      //refresh both affected groups with new updated questions
      questionGroups[sourceGroupIndex].questions = sourceQuestions
      questionGroups[destGroupIndex].questions = destQuestions

      const operation = createBufferOperation()
        .questionGroupReorder()
        .update(getReorganizedQuestionGroups(questionGroups))
      addToBuffer(operation)
      handleUpdate(questionGroups)
      setFocused(movedQuestion, destGroupIndex, destination.index)
    }
  }

  const handleQuestionGroupDuplication = (questionGroup, index) => {
    const duplicatedQuestionGroup = DuplicateQuestionGroup(questionGroup)

    const updatedQuestionGroups = InsertElementAndIncrementProperty(
      questionGroups,
      duplicatedQuestionGroup,
      index + 1,
      'sortOrder'
    )

    const operation = createBufferOperation(duplicatedQuestionGroup.gid)
      .questionGroup()
      .create({
        questionGroup: {
          ...duplicatedQuestionGroup,
          tempId: duplicatedQuestionGroup.gid,
        },
        questionGroupL10n: duplicatedQuestionGroup.l10ns,
      })

    handleUpdate(updatedQuestionGroups)
    addToBuffer(operation)

    duplicatedQuestionGroup.questions.map((question) => {
      const duplicatedQuestion = DuplicateQuestion(question)

      const operation = createBufferOperation(duplicatedQuestion.qid)
        .question()
        .create({
          question: { ...duplicatedQuestion, tempId: duplicatedQuestion.qid },
          questionL10n: { ...duplicatedQuestion.l10ns },
          attributes: { ...(duplicatedQuestion.attributes || {}) },
          answers: { ...(duplicatedQuestion.answers || []) },
          subquestions: { ...(duplicatedQuestion.subquestions || []) },
        })

      addToBuffer(operation)
    })

    const reorderOperation = createBufferOperation()
      .questionGroupReorder()
      .update(getReorganizedQuestionGroups(updatedQuestionGroups))

    addToBuffer(reorderOperation)

    setFocused(duplicatedQuestionGroup, index + 1)
  }

  const getQuestionGroupDragStyle = (draggableStyle) => ({
    userSelect: 'none',
    margin: questionGroups.length > 0 ? `0 0 18px 0` : '',
    ...draggableStyle,
  })

  const scrollToElement = (id) => {
    const element = document.getElementById(id)

    if (!element) {
      return
    }

    const isElementOnScreen = IsElementOnScreen(element)

    if (!isElementOnScreen) {
      ScrollToElement(element)
    }
  }

  return (
    <div
      data-testid="editor-structure-panel"
      className="d-flex"
      style={{ height: '100%' }}
    >
      <div className="survey-structure">
        <div id="survey-menu" className="survey-menu">
          <SideBarHeader className="primary">
            {t('Structure')}
            <Button
              variant="link"
              className="p-0 btn-close-lime"
              data-testid="btn-close-structure"
              onClick={handleClose}
            >
              <CloseIcon className="text-black fill-current" />
            </Button>
          </SideBarHeader>
          <div
            data-testid="survey-structure-header"
            className={classNames({
              'focus-bg-purple text-white':
                focused?.info?.type ===
                getQuestionTypeInfo().WELCOME_SCREEN.type,
            })}
            onClick={() => {
              scrollToElement('survey-header')
              setFocused({ info: getQuestionTypeInfo().WELCOME_SCREEN })
            }}
          >
            <RowPinned title={t('Welcome')} disabled={!showWelcome} />
          </div>
          <DragDropContext
            onDragEnd={handleOnDragEnd}
            onDragUpdate={onDragUpdate}
          >
            <Droppable
              key="all-groups"
              droppableId="all-groups"
              type="group"
              direction="vertical"
            >
              {(provided) => (
                <div {...provided.droppableProps} ref={provided.innerRef}>
                  {questionGroups.map((questionGroup, index) => (
                    <Draggable
                      index={index}
                      key={`questionGroupStructure-${index}-${questionGroup.gid}`}
                      draggableId={`questionGroupStructure-${index}`}
                    >
                      {(provided, snapshot) => (
                        <div
                          data-ordervalue={questionGroup.sortOrder}
                          ref={provided.innerRef}
                          {...provided.draggableProps}
                          style={getQuestionGroupDragStyle(
                            provided.draggableProps.style
                          )}
                          className={classNames({
                            'focus-element': snapshot.isDragging,
                          })}
                        >
                          <RowQuestionGroup
                            provided={provided}
                            questionGroup={questionGroup}
                            language={language}
                            update={(questionGroup) =>
                              handleUpdateQuestionGroup(questionGroup, index)
                            }
                            duplicateGroup={() =>
                              handleQuestionGroupDuplication(
                                questionGroup,
                                index
                              )
                            }
                            deleteGroup={() =>
                              handleGroupDeletion(questionGroup, index)
                            }
                            onTitleClick={() => {
                              scrollToElement(
                                `question-group-${questionGroup.gid}`
                              )
                              setFocused({ ...questionGroup }, index)
                            }}
                            groupIndex={index}
                          />
                        </div>
                      )}
                    </Draggable>
                  ))}
                  {provided.placeholder}
                </div>
              )}
            </Droppable>
          </DragDropContext>
          {questionGroups.length > 0 ? (
            <div
              data-testid="survey-structure-footer"
              className={classNames({
                'focus-bg-purple text-white':
                  focused?.info?.type === getQuestionTypeInfo().END_SCREEN.type,
              })}
              onClick={() => {
                scrollToElement('survey-footer-title')
                setFocused({ info: getQuestionTypeInfo().END_SCREEN })
              }}
            >
              <RowPinned title={t('End')} />
            </div>
          ) : (
            <></>
          )}
        </div>
      </div>
    </div>
  )
}
