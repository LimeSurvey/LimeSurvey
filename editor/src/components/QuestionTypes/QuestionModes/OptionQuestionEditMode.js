import { useEffect, useMemo, useRef, useState } from 'react'
import { Button } from 'react-bootstrap'
import { Draggable } from '@hello-pangea/dnd'
import { PlusLg } from 'react-bootstrap-icons'
import classNames from 'classnames'
import {
  useAppState,
  useBuffer,
  useChildKeyboardShortcuts,
  useToggleQuestionCodes,
} from 'hooks'
import {
  createBufferOperation,
  Entities,
  hasTempId,
  L10ns,
  STATES,
} from 'helpers'
import { getTooltipMessages } from 'helpers/options'
import { errorToast } from 'helpers/Alert'
import { ContentEditor, DragAndDrop, TooltipContainer } from 'components'
import { CloseCircleFillIcon, DragIcon, UploadIcon } from 'components/icons'
import { LoadLabelSetModal } from 'components/LabelSets'
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
  getImageOptionDefaults,
  getImageOptionDefaultsChanges,
  getImageOptionForImage,
  getImageOptionLanguageMaps,
  getImageOptionSaveChanges,
  getImageOptionSettings,
  getImageSelectThemes,
  withImageFileName,
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
  handleLabelSetLoad = () => {},
  handleOnChildDragEnd,
  handleChildDelete,
  language,
  handleChildCodeUpdate,
  handleChildAssessmentValueUpdate,
  _children = [],
  isTitleFocused,
  surveySettings = {},
}) => {
  const { questionThemeName, showAssessmentValue } = question
  const surveyLanguages = surveySettings.languages ?? []
  const [showLoadLabelSetModal, setShowLoadLabelSetModal] = useState(false)
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
  // Image saves still waiting when the question closes are dropped: The
  // latest handleUpdate would focus the question again and write back the
  // questions of its render. A new edit mode would also start its own queue,
  // and the old save could then overwrite a newer one.
  const isMounted = useRef(true)
  const hasReportedDroppedSave = useRef(false)
  useEffect(() => {
    isMounted.current = true
    return () => {
      isMounted.current = false
    }
  }, [])

  /**
   * Tells whether an image save can still be applied. Once the question has
   * closed, it can't: The user is told once that the changes were not saved.
   *
   * @returns {boolean} whether the save can go on
   */
  const canStillSave = () => {
    if (isMounted.current) {
      return true
    }
    if (!hasReportedDroppedSave.current) {
      hasReportedDroppedSave.current = true
      errorToast(t('Your changes could not be saved.'))
    }
    return false
  }
  const { areCodesVisible } = useToggleQuestionCodes()
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

  // isFocused is only true when the user may update the survey
  const canLoadLabelSet = isFocused && !isSurveyActive
  // The modal calls onLoad from an earlier render once the label set arrives.
  // Meanwhile the question may have lost focus, the survey been activated, or the
  // question changed (e.g. by an autosave), so use the state of the latest render:
  // Its handler appends to the current children and updates the current question
  const loadIntoLatestQuestion = useRef(null)
  loadIntoLatestQuestion.current = (labels) => {
    if (!canLoadLabelSet) {
      return
    }
    handleLabelSetLoad(_children ?? [], childrenInfo.entity, labels)
  }
  const handleLoadLabelSet = (labels) => loadIntoLatestQuestion.current(labels)
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
   * Loads an option image to get its size. An image that doesn't load within
   * IMAGE_LOAD_TIMEOUT counts as not loaded, so a stalled request can't hold
   * up the saves queued after this one.
   *
   * @param {string} imagePath the image path of the option
   * @returns {Promise<{width: number, height: number}|null>} the size, or null
   *   when the image didn't load in time
   */
  const loadOptionImageSize = async (imagePath) => {
    let timeout
    const naturalSize = await Promise.race([
      loadImageSize((process.env.REACT_APP_SITE_URL || '') + imagePath),
      new Promise((resolve) => {
        timeout = setTimeout(() => resolve(null), IMAGE_LOAD_TIMEOUT)
      }),
    ])
    clearTimeout(timeout)
    return naturalSize
  }

  /**
   * Returns the settings of the other options for "Apply to all": Each option
   * with an image gets the frame, zoom and corner radius, with the crop
   * calculated for its own image. An image that doesn't load in time is left
   * out, see loadOptionImageSize().
   *
   * @param {string} code the code of the edited option
   * @param {Object} settings the settings of the edited option
   * @returns {Promise<Object>} keyed by option code: The settings, and the
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
          const naturalSize = await loadOptionImageSize(imagePath)
          if (naturalSize) {
            otherSettings[child[codeKey]] = {
              imagePath,
              settings: applyImageSettings(
                settings,
                naturalSize,
                getImageOptionSettings(
                  attributes,
                  child[codeKey],
                  language,
                  imagePath
                )
              ),
            }
          }
        })
    )
    return otherSettings
  }

  /**
   * Stores image option attribute changes in the latest question and queues
   * them for the API. Without changes, nothing is sent.
   *
   * @param {Object} currentQuestion the latest question
   * @param {Object|null} changes the changed values keyed by attribute and
   *   language
   * @returns {void}
   */
  const storeImageOptionChanges = (currentQuestion, changes) => {
    if (!changes || !Object.keys(changes).length) {
      return
    }
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
   * Stores the settings from the image editor of an option in the question
   * attributes, with "Apply to all" also for the other options. Nothing is
   * saved when the option was renamed or deleted, or got another image, while
   * the save waited, or when the question was closed meanwhile (see
   * canStillSave()). Other options whose image was replaced or deleted
   * meanwhile keep their settings. The settings are saved for the active
   * language and for the languages that show the same image. With "Apply to
   * all", the frame, zoom and corner radius are also kept for images added
   * later. Without it, kept ones are removed.
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
    if (!canStillSave()) {
      return
    }
    const otherSettings = applyToAll
      ? await getSettingsForOtherOptions(code, settings)
      : {}
    if (!canStillSave()) {
      return
    }
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
        validOtherSettings[childCode] = withImageFileName(
          other.settings,
          other.imagePath
        )
      }
      if (childCode === code || validOtherSettings[childCode]) {
        sameImageLanguages[childCode] = getSameImageLanguages(child)
      }
    })
    const hasDefaults = !!getImageOptionDefaults(currentQuestion.attributes)
    storeImageOptionChanges(currentQuestion, {
      ...getImageOptionSaveChanges(currentQuestion.attributes, {
        code,
        settings: withImageFileName(settings, editedImagePath),
        altText,
        language,
        codes,
        otherSettings: validOtherSettings,
        sameImageLanguages,
      }),
      ...(applyToAll || hasDefaults
        ? getImageOptionDefaultsChanges(applyToAll ? settings : null)
        : {}),
    })
  }

  /**
   * Finds an option in the children of a question, by its appKey (new
   * options) or its ID, which stay the same when the code changes.
   *
   * @param {Object[]} children the answers or subquestions
   * @param {{appKey: string|undefined, id: number|string}} optionKey the
   *   appKey and ID of the option
   * @returns {Object|undefined} the option
   */
  const findOption = (children, optionKey) =>
    children.find((child) =>
      optionKey.appKey
        ? child.appKey === optionKey.appKey
        : child[childrenInfo.idKey] === optionKey.id
    )

  /**
   * Gives a new image of an option the frame, zoom and corner radius saved
   * with "Apply to all", centered and with the crop calculated for the image.
   * Nothing is saved when the question has no such settings (anymore), when
   * the image doesn't load in time, when the option was deleted or shows
   * another image meanwhile, or when its image already got settings.
   *
   * @param {{appKey: string|undefined, id: number|string}} optionKey the
   *   appKey and ID of the option
   * @param {string} imagePath the new image path
   * @returns {Promise<void>}
   */
  const applyDefaultsToNewImage = async (optionKey, imagePath) => {
    if (
      !canStillSave() ||
      !getImageOptionDefaults(latestQuestion.current.attributes)
    ) {
      return
    }
    const naturalSize = await loadOptionImageSize(imagePath)
    if (!naturalSize || !canStillSave()) {
      return
    }
    const currentQuestion = latestQuestion.current
    const defaults = getImageOptionDefaults(currentQuestion.attributes)
    const currentChildren = getQuestionChildren(currentQuestion)
    const child = findOption(currentChildren, optionKey)
    if (!defaults || !child || getImagePath(child) !== imagePath) {
      return
    }
    const code = child[codeKey]
    if (
      getImageOptionSettings(
        currentQuestion.attributes,
        code,
        language,
        imagePath
      )
    ) {
      return
    }
    storeImageOptionChanges(
      currentQuestion,
      getImageOptionSaveChanges(currentQuestion.attributes, {
        code,
        settings: withImageFileName(
          applyImageSettings(defaults, naturalSize, null),
          imagePath
        ),
        altText: '',
        language,
        codes: currentChildren.map((option) => option[codeKey]),
        sameImageLanguages: { [code]: getSameImageLanguages(child) },
      })
    )
  }

  // New images that wait for the settings of "Apply to all". They are queued
  // once the question shows them, see the effect below.
  const pendingNewImages = useRef([])

  useEffect(() => {
    if (!pendingNewImages.current.length) {
      return
    }
    const pending = pendingNewImages.current
    pendingNewImages.current = []
    pending.forEach(({ optionKey, imagePath }) => {
      const save = () => applyDefaultsToNewImage(optionKey, imagePath)
      imageSaveQueue.current = imageSaveQueue.current.then(save, save)
    })
  }, [question])

  /**
   * Changes the text of an option, in the image select themes its image. A
   * new image gets the settings of "Apply to all" once the question shows it,
   * see applyDefaultsToNewImage().
   *
   * @param {Object} child the answer or subquestion
   * @param {number} index position of the option
   * @param {string} value the new text or image path
   * @returns {void}
   */
  const handleOptionUpdate = (child, index, value) => {
    if (
      isImageTheme &&
      value &&
      value !== getImagePath(child) &&
      getImageOptionDefaults(latestQuestion.current.attributes)
    ) {
      pendingNewImages.current.push({
        optionKey: { appKey: child.appKey, id: child[childrenInfo.idKey] },
        imagePath: value,
      })
    }
    handleChildLUpdate(value, index, _children, childrenInfo.entity)
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
  // "Apply to all" starts checked while its settings are kept for new images.
  const hasImageOptionDefaults = useMemo(
    () => isImageTheme && !!getImageOptionDefaults(question.attributes),
    [isImageTheme, question.attributes]
  )

  /**
   * Returns the image settings props of an option for ImageChoice. Settings
   * saved for another image of the option are left out. Without permission to
   * edit the question, there is no save: ImageChoice then has no edit button.
   *
   * @param {Object} child the answer or subquestion
   * @returns {Object} the props, empty for themes without images
   */
  const getImageOptionProps = (child) => {
    if (!imageOptionMaps) {
      return {}
    }
    const code = child[codeKey]
    const { settings, altText } = getImageOptionForImage(
      imageOptionMaps,
      code,
      getImagePath(child)
    )
    return {
      imageSettings: settings,
      imageAltText: altText,
      imageApplyToAll: hasImageOptionDefaults,
      onImageSettingsSave: isFocused
        ? (imageSettings) => handleImageOptionSave(code, imageSettings)
        : null,
    }
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
                  {areCodesVisible && (
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
                        isSingleChoiceTheme
                          ? t('Answer option')
                          : t('Subquestion')
                      }
                      className="text-secondary choice"
                      value={L10ns({
                        prop: childrenInfo.titleKey,
                        language,
                        l10ns: child.l10ns,
                      })}
                      update={(value) =>
                        handleOptionUpdate(child, index, value)
                      }
                      key={`uicomponent-${child.appKey || child[childrenInfo.idKey]}-questionmode`}
                      index={index}
                      code={isSingleChoiceTheme ? child.code : child.title}
                      // Images can only be uploaded, deleted and edited with
                      // permission to edit the question: Otherwise they are
                      // only shown.
                      isFocused={isImageTheme ? isFocused : true}
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
                      {...getImageOptionProps(child)}
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
