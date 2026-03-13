import { Button } from 'react-bootstrap'
import { useParams } from 'react-router-dom'
import { useMemo } from 'react'
import { Draggable } from 'react-beautiful-dnd'
import { PlusLg } from 'react-bootstrap-icons'
import classNames from 'classnames'

import { useAppState, useSurvey } from 'hooks'
import { Entities, hasTempId, L10ns, STATES } from 'helpers'
import { getTooltipMessages } from 'helpers/options'
import { ContentEditor, DragAndDrop, TooltipContainer } from 'components'
import { CloseCircleFillIcon, DragIcon } from 'components/icons'
import { ImageChoice } from 'components/QuestionTypes/ImageChoice'

import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { singleChoiceThemes } from '../singleChoiceThemes'

const imageThemeComponents = [
  getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.theme,
  getQuestionTypeInfo().MULTIPLE_CHOICE_IMAGE_SELECT.theme,
]

export const OptionQuestionEditMode = ({
  question: { questionThemeName, qid } = {},
  handleChildLUpdate,
  isFocused,
  handleChildAdd,
  handleOnChildDragEnd,
  handleChildDelete,
  language,
  _children = [],
  isTitleFocused,
}) => {
  const { surveyId } = useParams()
  const { survey } = useSurvey(surveyId)
  const isSingleChoiceTheme = singleChoiceThemes.includes(questionThemeName)
  const isImageTheme = imageThemeComponents.includes(questionThemeName)

  const childrenInfo = {
    idKey: isSingleChoiceTheme ? 'aid' : 'qid',
    titleKey: isSingleChoiceTheme ? 'answer' : 'question',
    entity: isSingleChoiceTheme ? Entities.answer : Entities.subquestion,
  }

  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)

  const getSubquestionStyle = (draggableStyle) => ({
    userSelect: 'none',
    ...draggableStyle,
  })

  const handleOnDragEnd = (dropResult) => {
    handleOnChildDragEnd(dropResult, _children, childrenInfo.entity)
  }

  const UiComponentToRender = isImageTheme ? ImageChoice : ContentEditor

  const questionHasTempId = useMemo(() => {
    return hasTempId(qid)
  }, [])

  return (
    <div>
      <DragAndDrop
        className="children-parent"
        onDragEnd={handleOnDragEnd}
        droppableId={'droppable'}
      >
        {_children?.map((child, index) => (
          <Draggable
            key={`${child.qid}-choice-${index}`}
            draggableId={`${child.qid}-choice-${index}`}
            index={index}
          >
            {(provided, snapshot) => (
              <div
                ref={provided.innerRef}
                {...provided.draggableProps}
                style={getSubquestionStyle(provided.draggableProps.style)}
                className={classNames('child', {
                  'focus-element': snapshot.isDragging,
                })}
                data-testid="child-option"
              >
                <div
                  onClick={() =>
                    handleChildDelete(
                      child[childrenInfo.idKey],
                      _children,
                      childrenInfo.entity
                    )
                  }
                  data-testid="remove-subquestion-button"
                  className="remove-option-button-parent"
                >
                  <CloseCircleFillIcon
                    className={classNames(
                      'text-danger fill-current cursor-pointer edit-mode-remove-option remove-option-button',
                      {
                        'd-none': !isFocused || isSurveyActive,
                      }
                    )}
                  />
                </div>
                <div style={{ height: 28 }} {...provided.dragHandleProps}>
                  <DragIcon
                    className={classNames('text-secondary fill-current', {
                      'd-none': !isFocused,
                    })}
                  />
                </div>
                {survey.showQNumCode?.showNumber && (
                  <input
                    className="question-code-tag"
                    type="text"
                    value={isSingleChoiceTheme ? child.code : child.title}
                    onChange={(e) =>
                      handleChildLUpdate(
                        e.target.value,
                        index,
                        _children,
                        childrenInfo.entity,
                        false
                      )
                    }
                  />
                )}
                <div
                  data-testid="child-ui-component"
                  className="child-ui-component"
                >
                  <UiComponentToRender
                    placeholder={
                      isSingleChoiceTheme ? 'Answer option' : 'Subquestion'
                    }
                    className="text-secondary choice"
                    value={L10ns({
                      prop: childrenInfo.titleKey,
                      language,
                      l10ns: child.l10ns,
                    })}
                    update={(value) =>
                      handleChildLUpdate(
                        value,
                        index,
                        _children,
                        childrenInfo.entity
                      )
                    }
                    key={`uicomponent-${qid}-${index}-questionmode`}
                    index={index}
                    isFocused={true}
                    idPrefix={isSingleChoiceTheme ? 'a' : 'q'}
                    id={child[childrenInfo.idKey]}
                    // Focus the child if it's a new child and also if the question is not a new question.
                    focus={
                      hasTempId(child[childrenInfo.idKey]) &&
                      !questionHasTempId &&
                      !isTitleFocused
                    }
                  />
                </div>
              </div>
            )}
          </Draggable>
        ))}
      </DragAndDrop>
      <div className="add-child-button-container mt-4">
        <TooltipContainer
          tip={getTooltipMessages().ACTIVE_DISABLED}
          showTip={isSurveyActive}
        >
          <div>
            <Button
              onClick={() => handleChildAdd(_children, childrenInfo.entity)}
              variant={'outline'}
              className={
                'text-primary d-flex add-choice-button align-items-center p-0 gap-2 border-none'
              }
              data-testid="add-child-button"
              disabled={isSurveyActive}
            >
              <PlusLg />
              {isSingleChoiceTheme ? t('Add answer') : t('Add subquestion')}
            </Button>
          </div>
        </TooltipContainer>
      </div>
    </div>
  )
}
