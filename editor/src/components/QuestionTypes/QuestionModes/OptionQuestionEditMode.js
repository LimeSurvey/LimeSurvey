import { Button } from 'react-bootstrap'
import { useParams } from 'react-router-dom'
import { Draggable } from '@hello-pangea/dnd'
import { PlusLg } from 'react-bootstrap-icons'
import classNames from 'classnames'
import {
  useAppState,
  useBuffer,
  useSurvey,
  useChildKeyboardShortcuts,
} from 'hooks'
import {
  createBufferOperation,
  Entities,
  hasTempId,
  L10ns,
  STATES,
} from 'helpers'
import { getTooltipMessages } from 'helpers/options'
import { ContentEditor, DragAndDrop, TooltipContainer } from 'components'
import { CloseCircleFillIcon, DragIcon } from 'components/icons'
import { ImageChoice } from 'components/QuestionTypes/ImageChoice'
import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { singleChoiceThemes } from '../singleChoiceThemes'
import { SubquestionCodeInput } from '../subquestionCodeComponents'
import { AssessmentValueInput } from '../AssessmentValueInput'
import {
  applyImageSettings,
  loadImageSize,
} from 'components/UIComponents/ImageEditor/imageEditorUtils'
import {
  applyAttributeChanges,
  getImageOptionAltText,
  getImageOptionSaveChanges,
  getImageOptionSettings,
} from '../imageOptionSettings'

const imageThemeComponents = [
  getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.theme,
  getQuestionTypeInfo().MULTIPLE_CHOICE_IMAGE_SELECT.theme,
]

/**
 * Edit mode of the choice question types: The options with code, text or
 * image, assessment value, delete button and drag handle.
 *
 * @param {Object} question the question
 * @param {Function} handleUpdate called with question changes
 * @param {Function} handleChildLUpdate called when an option text changes
 * @param {boolean} isFocused whether the user may edit the question
 * @param {Function} handleChildAdd called to add an option
 * @param {Function} handleOnChildDragEnd called when an option was moved
 * @param {Function} handleChildDelete called to delete an option
 * @param {string} language the active language
 * @param {Function} handleChildCodeUpdate called when an option code changes
 * @param {Function} handleChildAssessmentValueUpdate called when an assessment
 *   value changes
 * @param {Object[]} _children the answers or subquestions
 * @param {boolean} isTitleFocused whether the question title has the focus
 * @returns {JSX.Element} the options in edit mode
 */
export const OptionQuestionEditMode = ({
  question = {},
  handleUpdate = () => {},
  handleChildLUpdate,
  isFocused,
  handleChildAdd,
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
  const { addToBuffer } = useBuffer()
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

  const codeKey = isSingleChoiceTheme ? 'code' : 'title'

  /**
   * Returns the settings of the other options for "Apply to all": Each option
   * with an image gets the frame, zoom and corner radius, with the crop
   * calculated for its own image.
   *
   * @param {string} code the code of the edited option
   * @param {Object} settings the settings of the edited option
   * @returns {Promise<Object>} the settings keyed by option code
   */
  const getSettingsForOtherOptions = async (code, settings) => {
    const otherSettings = {}
    await Promise.all(
      _children
        .filter((child) => child[codeKey] !== code)
        .map(async (child) => {
          const imagePath = L10ns({
            prop: childrenInfo.titleKey,
            language,
            l10ns: child.l10ns,
          })
          // Options without a valid image path have nothing to apply to.
          if (!imagePath || /(<([^>]+)>)/i.test(imagePath)) {
            return
          }
          const naturalSize = await loadImageSize(
            (process.env.REACT_APP_SITE_URL || '') + imagePath
          )
          if (naturalSize) {
            otherSettings[child[codeKey]] = applyImageSettings(
              settings,
              naturalSize,
              getImageOptionSettings(question.attributes, child[codeKey])
            )
          }
        })
    )
    return otherSettings
  }

  /**
   * Stores the settings from the image editor of an option in the question
   * attributes, with "Apply to all" also for the other options.
   *
   * @param {string} code the option code
   * @param {Object} imageSettings the settings from ImageEditor's onSave,
   *   including the alt text and applyToAll
   * @returns {Promise<void>}
   */
  const handleImageOptionSave = async (
    code,
    { altText, applyToAll = false, ...settings }
  ) => {
    const otherSettings = applyToAll
      ? await getSettingsForOtherOptions(code, settings)
      : {}
    const changes = getImageOptionSaveChanges(question.attributes, {
      code,
      settings,
      altText,
      language,
      codes: _children.map((child) => child[codeKey]),
      otherSettings,
    })
    addToBuffer(
      createBufferOperation(question.qid).questionAttribute().update(changes)
    )
    handleUpdate({
      attributes: applyAttributeChanges(question.attributes, changes),
    })
  }

  /**
   * Returns the image settings props of an option for ImageChoice.
   *
   * @param {string} code the option code
   * @returns {Object} the props, empty for themes without images
   */
  const getImageOptionProps = (code) =>
    isImageTheme
      ? {
          imageSettings: getImageOptionSettings(question.attributes, code),
          imageAltText: getImageOptionAltText(
            question.attributes,
            code,
            language
          ),
          onImageSettingsSave: (imageSettings) =>
            handleImageOptionSave(code, imageSettings),
        }
      : {}

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
                      code={isSingleChoiceTheme ? child.code : child.title}
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
                      {...getImageOptionProps(
                        isSingleChoiceTheme ? child.code : child.title
                      )}
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
