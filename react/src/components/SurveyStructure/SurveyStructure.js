import { Draggable } from 'react-beautiful-dnd'
import { useState } from 'react'
import classNames from 'classnames'
import Button from 'react-bootstrap/Button'

import {
  DeleteItemFromArray,
  DuplicateQuestionGroup,
  IsElementOnScreen,
  ScrollToElement,
  ConfirmAlert,
  MoveQuestionGroup,
} from 'helpers'
import { useFocused, useAppState } from 'hooks'

import { SideBarHeader } from 'components/SideBar'
import { DragAndDrop } from 'components'
import { CloseIcon } from 'components/icons'
import { QuestionTypeInfo } from '../QuestionTypes'

import { RowPinned } from './RowPinned'
import { RowQuestionGroup } from './RowQuestionGroup'

export const SurveyStructure = ({
  survey: { questionGroups = [], language, showQuestionCode },
  update,
  surveyId,
}) => {
  const [isReorderingQuestionGroups, setIsReorderingQuestionGroups] =
    useState(false)

  const { focused = {}, setFocused, unFocus } = useFocused()

  const [, setEditorStructurePanelOpen] = useAppState(
    'editorStructurePanelOpen',
    true
  )

  const handleUpdateQuestionGroup = (questionGroup, index) => {
    const updatedQuestionGroups = [...questionGroups]
    updatedQuestionGroups[index] = questionGroup

    update(updatedQuestionGroups)
  }

  const handleGroupDeletion = (index) => {
    ConfirmAlert({ icon: 'warning' }).then(({ isConfirmed }) => {
      if (!isConfirmed) {
        return
      }

      const updatedQuestionGroups = DeleteItemFromArray(questionGroups, index)

      update(updatedQuestionGroups)

      if (!focused.qid && focused.gid === questionGroups[index].gid) {
        unFocus()
      }
    })
  }

  const handleQuestionGroupDuplication = (questionGroup, index) => {
    ++index
    const { duplicatedQuestionGroup, updatedQuestionGroups } =
      DuplicateQuestionGroup(questionGroup, questionGroups, index)

    update(updatedQuestionGroups)
    setFocused(duplicatedQuestionGroup, index + 1)
  }

  const getQuestionGroupDragStyle = (draggableStyle) => ({
    userSelect: 'none',
    margin: questionGroups.length > 0 ? `0 0 18px 0` : '',
    ...draggableStyle,
  })

  const handleOnDragEnd = (dropResult) => {
    setIsReorderingQuestionGroups(false)

    // dropped outside the list
    if (!dropResult.destination) {
      return
    }

    const currentIndex = dropResult.source.index
    const newIndex = dropResult.destination.index

    const { movedQuestionGroup, reorderedQuestionGroups } = MoveQuestionGroup(
      questionGroups,
      currentIndex,
      newIndex
    )

    update(reorderedQuestionGroups)
    setFocused(movedQuestionGroup, newIndex)
  }

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
    <div className="d-flex" style={{ height: '100%' }}>
      <div
        className="survey-structure px-2"
        style={{ overflowY: 'auto', width: '290px' }}
      >
        <SideBarHeader className="primary">
          Structure
          <Button
            variant="link"
            className="p-0 btn-close-lime"
            onClick={() => setEditorStructurePanelOpen(false)}
          >
            <CloseIcon className="text-black fill-current" />
          </Button>
        </SideBarHeader>
        <div
          data-testid="survey-structure-header"
          onClick={() => {
            scrollToElement('survey-header-title')
            setFocused({ info: QuestionTypeInfo.WELCOME_SCREEN })
          }}
        >
          <RowPinned title="Welcome" />
        </div>
        <DragAndDrop
          onDragStart={() => setIsReorderingQuestionGroups(true)}
          onDragEnd={handleOnDragEnd}
          droppableId={'droppable'}
          className={classNames('', {
            'focus-element': isReorderingQuestionGroups,
          })}
        >
          {questionGroups.map((questionGroup, index) => {
            return (
              <Draggable
                key={`questionGroupStructure-${questionGroup.gid}`}
                draggableId={`questionGroupStructure-${questionGroup.gid}`}
                index={index}
              >
                {(provided, snapshot) => (
                  <div
                    data-testid="survey-structure-question-group"
                    data-ordervalue={questionGroup.groupOrder}
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
                        handleQuestionGroupDuplication(questionGroup, index)
                      }
                      deleteGroup={() => handleGroupDeletion(index)}
                      onTitleClick={() =>
                        setFocused({ ...questionGroup }, index)
                      }
                      groupIndex={index}
                      showQuestionCode={showQuestionCode}
                    />
                  </div>
                )}
              </Draggable>
            )
          })}
        </DragAndDrop>
        {questionGroups.length > 0 ? (
          <div
            data-testid="survey-structure-footer"
            onClick={() => {
              scrollToElement('survey-footer-title')
              setFocused({ info: QuestionTypeInfo.END_SCREEN })
            }}
          >
            <RowPinned title="End" />
          </div>
        ) : (
          <></>
        )}
      </div>
    </div>
  )
}
