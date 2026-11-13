import { useMemo, useRef } from 'react'
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
import { singleChoiceThemes } from '../singleChoiceThemes'
import { SubquestionCodeInput } from '../subquestionCodeComponents'
import { AssessmentValueInput } from '../AssessmentValueInput'
import {
  applyImageSettings,
  IMAGE_LOAD_TIMEOUT,
  loadImageSize,
} from 'components/UIComponents/ImageEditor/imageEditorUtils'
import {
  applyAttributeChanges,
  getImageOptionLanguageMaps,
  getImageOptionSaveChanges,
  getImageOptionSettings,
  getImageSelectThemes,
} from '../imageOptionSettings'

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
  // "Apply to all" waits for the other images to load. Meanwhile, options can
  // be changed, renamed or deleted: The save uses the question as it is then.
  const latestQuestion = useRef(question)
  latestQuestion.current = question
  // handleUpdate spreads the question of the render it comes from: A save that
  // waited must use the one from the latest render, or it reverts the changes
  // made meanwhile (e.g. a deleted option would come back).
  const latestHandleUpdate = useRef(handleUpdate)
  latestHandleUpdate.current = handleUpdate
  const isSingleChoiceTheme = singleChoiceThemes.includes(questionThemeName)
  const isImageTheme = getImageSelectThemes().includes(questionThemeName)

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

  // Image saves run one after another, so that an older "Apply to all"
  // never overwrites a newer save when its images load more slowly.
  const imageSaveQueue = useRef(Promise.resolve())

  const codeKey = isSingleChoiceTheme ? 'code' : 'title'

  /**
   * Returns the answers or subquestions of a question.
   *
   * @param {Object} questionData the question
   * @returns {Object[]} its answers or subquestions
   */
  const getQuestionChildren = (questionData) =>
    (isSingleChoiceTheme ? questionData.answers : questionData.subquestions) ||
    []

  /**
   * Returns the image path of an option in the active language.
   *
   * @param {Object} child the answer or subquestion
   * @returns {string|undefined} the image path
   */
  const getImagePath = (child) =>
    L10ns({ prop: childrenInfo.titleKey, language, l10ns: child.l10ns })

  /**
   * Returns the other languages in which an option has the same image as in
   * the active language: They get the same image settings.
   *
   * @param {Object} child the answer or subquestion
   * @returns {string[]} the language codes
   */
  const getSameImageLanguages = (child) => {
    const imagePath = getImagePath(child)
    return Object.entries(child.l10ns || {})
      .filter(
        ([childLanguage, l10n]) =>
          childLanguage !== language &&
          imagePath &&
          l10n?.[childrenInfo.titleKey] === imagePath
      )
      .map(([childLanguage]) => childLanguage)
  }

  /**
   * Returns the settings of the other options for "Apply to all": Each option
   * with an image gets the frame, zoom and corner radius, with the crop
   * calculated for its own image. An image that doesn't load within
   * IMAGE_LOAD_TIMEOUT is left out, so a stalled request can't hold up the
   * saves queued after this one.
   *
   * @param {string} code the code of the edited option
   * @param {Object} settings the settings of the edited option
   * @returns {Promise<Object>} keyed by option code: the settings, and the
   *   image path they were calculated for
   */
  const getSettingsForOtherOptions = async (code, settings) => {
    const { attributes } = latestQuestion.current
    const otherSettings = {}
    await Promise.all(
      getQuestionChildren(latestQuestion.current)
        .filter((child) => child[codeKey] !== code)
        .map(async (child) => {
          const imagePath = getImagePath(child)
          // Options without a valid image path have nothing to apply to.
          if (!imagePath || /(<([^>]+)>)/i.test(imagePath)) {
            return
          }
          let timeout
          const naturalSize = await Promise.race([
            loadImageSize((process.env.REACT_APP_SITE_URL || '') + imagePath),
            new Promise((resolve) => {
              timeout = setTimeout(() => resolve(null), IMAGE_LOAD_TIMEOUT)
            }),
          ])
          clearTimeout(timeout)
          if (naturalSize) {
            otherSettings[child[codeKey]] = {
              imagePath,
              settings: applyImageSettings(
                settings,
                naturalSize,
                getImageOptionSettings(attributes, child[codeKey], language)
              ),
            }
          }
        })
    )
    return otherSettings
  }

  /**
   * Stores the settings from the image editor of an option in the question
   * attributes, with "Apply to all" also for the other options. Nothing is
   * saved when the option was renamed or deleted, or got another image, while
   * the save waited. Other options whose image was replaced or deleted
   * meanwhile keep their settings. The settings are saved for the active
   * language and for the languages that show the same image.
   *
   * @param {string} code the option code
   * @param {string|undefined} editedImagePath the image path of the option
   *   when its settings were edited
   * @param {Object} imageSettings the settings from ImageEditor's onSave,
   *   including the alt text and applyToAll
   * @returns {Promise<void>}
   */
  const saveImageOption = async (
    code,
    editedImagePath,
    { altText, applyToAll = false, ...settings }
  ) => {
    const otherSettings = applyToAll
      ? await getSettingsForOtherOptions(code, settings)
      : {}
    const currentQuestion = latestQuestion.current
    const currentChildren = getQuestionChildren(currentQuestion)
    const codes = currentChildren.map((child) => child[codeKey])
    const editedChild = currentChildren.find((child) => child[codeKey] === code)
    if (!editedChild || getImagePath(editedChild) !== editedImagePath) {
      return
    }
    // Only for the options that still exist and still have the loaded image.
    const validOtherSettings = {}
    const sameImageLanguages = {}
    currentChildren.forEach((child) => {
      const childCode = child[codeKey]
      const other = otherSettings[childCode]
      if (other && getImagePath(child) === other.imagePath) {
        validOtherSettings[childCode] = other.settings
      }
      if (childCode === code || validOtherSettings[childCode]) {
        sameImageLanguages[childCode] = getSameImageLanguages(child)
      }
    })
    const changes = getImageOptionSaveChanges(currentQuestion.attributes, {
      code,
      settings,
      altText,
      language,
      codes,
      otherSettings: validOtherSettings,
      sameImageLanguages,
    })
    const attributes = applyAttributeChanges(
      currentQuestion.attributes,
      changes
    )
    // The next queued save may run before the question is rendered again.
    latestQuestion.current = { ...currentQuestion, attributes }
    addToBuffer(
      createBufferOperation(currentQuestion.qid)
        .questionAttribute()
        .update(changes)
    )
    latestHandleUpdate.current({ attributes })
  }

  /**
   * Queues the save of an option's image settings, see saveImageOption(). The
   * option's image path is noted now, to check it when the save runs.
   *
   * @param {string} code the option code
   * @param {Object} imageSettings the settings from ImageEditor's onSave
   * @returns {Promise<void>} resolves when this save is done
   */
  const handleImageOptionSave = (code, imageSettings) => {
    const editedChild = getQuestionChildren(latestQuestion.current).find(
      (child) => child[codeKey] === code
    )
    const editedImagePath = editedChild ? getImagePath(editedChild) : undefined
    const save = () => saveImageOption(code, editedImagePath, imageSettings)
    // A failed save doesn't stop the ones after it.
    imageSaveQueue.current = imageSaveQueue.current.then(save, save)
    return imageSaveQueue.current
  }

  // Parsed once per change, not for each option on every render.
  const imageOptionMaps = useMemo(
    () =>
      isImageTheme
        ? getImageOptionLanguageMaps(question.attributes, language)
        : null,
    [isImageTheme, question.attributes, language]
  )

  /**
   * Returns the image settings props of an option for ImageChoice.
   *
   * @param {string} code the option code
   * @returns {Object} the props, empty for themes without images
   */
  const getImageOptionProps = (code) =>
    imageOptionMaps
      ? {
          imageSettings: imageOptionMaps.settings[code] ?? null,
          imageAltText: imageOptionMaps.altTexts[code] ?? '',
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
