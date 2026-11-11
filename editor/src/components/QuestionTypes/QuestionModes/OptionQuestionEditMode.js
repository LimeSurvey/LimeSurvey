import { useRef, useState } from 'react'
import { Button } from 'react-bootstrap'
import { useParams } from 'react-router-dom'
import { Draggable } from '@hello-pangea/dnd'
import { PlusLg } from 'react-bootstrap-icons'
import classNames from 'classnames'
import { useAppState, useSurvey, useChildKeyboardShortcuts } from 'hooks'
import {
  Entities,
  getSurveyLanguages,
  hasTempId,
  L10ns,
  labelsToChildren,
  STATES,
} from 'helpers'
import { getTooltipMessages } from 'helpers/options'
import { ContentEditor, DragAndDrop, TooltipContainer } from 'components'
import { CloseCircleFillIcon, DragIcon, UploadIcon } from 'components/icons'
import { LoadLabelSetModal } from 'components/LabelSets'
import { ImageChoice } from 'components/QuestionTypes/ImageChoice'
import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { singleChoiceThemes } from '../singleChoiceThemes'
import { SubquestionCodeInput } from '../subquestionCodeComponents'
import { AssessmentValueInput } from '../AssessmentValueInput'

const imageThemeComponents = [
  getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.theme,
  getQuestionTypeInfo().MULTIPLE_CHOICE_IMAGE_SELECT.theme,
]

export const OptionQuestionEditMode = ({
  question = {},
  handleChildLUpdate,
  isFocused,
  handleChildAdd,
  handleChildrenAppend = () => {},
  handleOnChildDragEnd,
  handleChildDelete,
  language,
  handleChildCodeUpdate,
  handleChildAssessmentValueUpdate,
  _children = [],
  isTitleFocused,
}) => {
  const { questionThemeName, showAssessmentValue } = question
  const { surveyId } = useParams()
  const { survey } = useSurvey(surveyId)
  const [showLoadLabelSetModal, setShowLoadLabelSetModal] = useState(false)
  // A label set arrives asynchronously; append it to the children as they are then
  const latestChildren = useRef(_children)
  latestChildren.current = _children
  const surveyLanguages = getSurveyLanguages(survey)
  const isSingleChoiceTheme = singleChoiceThemes.includes(questionThemeName)
  const isImageTheme = imageThemeComponents.includes(questionThemeName)

  const childrenInfo = {
    idKey: isSingleChoiceTheme ? 'aid' : 'qid',
    titleKey: isSingleChoiceTheme ? 'answer' : 'question',
    entity: isSingleChoiceTheme ? Entities.answer : Entities.subquestion,
  }
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)

  const { getKeyDownHandler } = useChildKeyboardShortcuts({
    children: _children,
    entityType: childrenInfo.entity,
    handleChildAdd,
    handleChildDelete,
    isSurveyActive,
  })

  const getSubquestionStyle = (draggableStyle) => ({
    userSelect: 'none',
    ...draggableStyle,
  })

  const handleOnDragEnd = (dropResult) => {
    handleOnChildDragEnd(dropResult, _children, childrenInfo.entity)
  }

  const UiComponentToRender = isImageTheme ? ImageChoice : ContentEditor

  // isFocused is only true when the user may update the survey
  const canLoadLabelSet = isFocused && !isSurveyActive

  const handleLoadLabelSet = (labels) => {
    if (!canLoadLabelSet) {
      return
    }
    const currentChildren = latestChildren.current ?? []
    handleChildrenAppend(
      currentChildren,
      childrenInfo.entity,
      labelsToChildren(
        labels,
        question,
        currentChildren,
        childrenInfo.entity,
        surveyLanguages
      )
    )
  }

  return (
    <div>
      <DragAndDrop
        className="children-parent edit-mode"
        onDragEnd={handleOnDragEnd}
        droppableId={'droppable'}
      >
        {_children?.map((child, index) => (
          <Draggable
            key={`${child.appKey || child[childrenInfo.idKey]}-choice`}
            draggableId={`${child.appKey || child[childrenInfo.idKey]}-choice`}
            index={index}
          >
            {(provided, snapshot) => (
              <div className="w-100">
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
                  <div
                    className="question-drag-handle"
                    {...provided.dragHandleProps}
                  >
                    <DragIcon
                      className={classNames('text-secondary fill-current', {
                        'd-none': !isFocused,
                      })}
                    />
                  </div>
                  {survey.showQNumCode?.showNumber && (
                    <SubquestionCodeInput
                      isSurveyActive={isSurveyActive}
                      code={isSingleChoiceTheme ? child.code : child.title}
                      onChange={(e) =>
                        handleChildCodeUpdate({
                          newCode: e.target.value,
                          childIndex: index,
                          childArray: _children,
                          entityType: childrenInfo.entity,
                          entityTitleKey: childrenInfo.titleKey,
                        })
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
                      key={`uicomponent-${child.appKey || child[childrenInfo.idKey]}-questionmode`}
                      index={index}
                      isFocused={true}
                      idPrefix={isSingleChoiceTheme ? 'a' : 'q'}
                      id={child[childrenInfo.idKey]}
                      onKeyDown={getKeyDownHandler(
                        child[childrenInfo.idKey],
                        index
                      )}
                      // Focus newly created child as long as question title is not focused.
                      focus={
                        hasTempId(child[childrenInfo.idKey]) && !isTitleFocused
                      }
                      showToolbar={true}
                    />
                  </div>
                  {showAssessmentValue && (
                    <AssessmentValueInput
                      assessmentValue={child.assessmentValue}
                      answerCode={child.code}
                      onChange={(e) =>
                        handleChildAssessmentValueUpdate({
                          newAssessmentValue: e.target.value,
                          childIndex: index,
                          childArray: _children,
                        })
                      }
                    />
                  )}
                </div>
              </div>
            )}
          </Draggable>
        ))}
      </DragAndDrop>
      <div
        className="add-child-button-container d-flex align-items-center mt-4"
        style={{ gap: '20px' }}
      >
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
        {isFocused && (
          <TooltipContainer
            tip={getTooltipMessages().ACTIVE_DISABLED}
            showTip={isSurveyActive}
          >
            <div>
              <Button
                onClick={() => setShowLoadLabelSetModal(true)}
                variant={'outline'}
                className={
                  'text-secondary d-flex add-choice-button align-items-center p-0 gap-2 border-none'
                }
                data-testid="load-label-set-button"
                disabled={isSurveyActive}
              >
                <UploadIcon className="fill-current" width={20} height={20} />
                {t('Load label set')}
              </Button>
            </div>
          </TooltipContainer>
        )}
      </div>
      {isFocused && (
        <LoadLabelSetModal
          show={showLoadLabelSetModal}
          onHide={() => setShowLoadLabelSetModal(false)}
          onLoad={handleLoadLabelSet}
          languages={surveyLanguages}
        />
      )}
    </div>
  )
}
