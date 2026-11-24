import { useEffect, useRef, useState } from 'react'
import { omit } from 'lodash'
import { useQuery } from '@tanstack/react-query'
import { useBuffer } from './useBuffer'
import {
  Entities,
  SCALE_1,
  SCALE_2,
  STATES,
  createBufferOperation,
  getAnswerExample,
  getNextAnswerCode,
  getQuestionExample,
  getNextSubQuestionCode,
  getLabelSetCodes,
  RandomNumber,
  APP_KEY_PREFIX,
} from 'helpers'
import { reportExtras } from 'appInstrumentation'
import { singleChoiceThemes } from 'components/QuestionTypes'
import {
  applyAttributeChanges,
  getImageOptionDeleteChanges,
  getImageOptionRenameChanges,
  getImageOptionResetChanges,
  getImageSelectThemes,
} from 'components/QuestionTypes/imageOptionSettings'
import { TestValidation } from 'components/Survey/Questions/QuestionCodeSchema'
import { showErrorMessage } from 'components/ConditionDesigner/utils'

export const createChildrenUpdateOperation = (qid, entityType, children) => {
  const props = children.map((child) => omit(child, 'conditiontext'))

  return entityType === Entities.answer
    ? createBufferOperation(qid).answer().update(props)
    : createBufferOperation(qid).subquestion().update(props)
}

/**
 * Handlers for the answers and subquestions of a question: Add, delete, move,
 * change text (or image), code and assessment value.
 *
 * @param {Object} params
 * @param {Object} params.question the question
 * @param {Function} params.handleUpdate saves question changes and focuses
 *   the question
 * @param {Function} [params.handleUpdateWithoutFocus] saves question changes
 *   without focusing the question: Used when a change arrives while the
 *   question isn't focused, e.g. an image upload that finishes after another
 *   question was focused. Without it, handleUpdate is used.
 * @param {boolean} [params.isFocused] whether the question is focused
 * @param {Object} params.surveySettings the survey settings
 * @param {string} params.language the active language
 * @returns {Object} the children and their handlers
 */
export const useQuestionChildren = ({
  question,
  handleUpdate,
  handleUpdateWithoutFocus,
  isFocused = true,
  surveySettings,
  language,
}) => {
  const { addToBuffer } = useBuffer()
  const [children, setChildren] = useState([])
  // The latest question and update function: An image upload calls
  // handleChildLUpdate from the render where it started. The image option
  // attributes and the question changed meanwhile must not be overwritten.
  // Once another question is focused, the upload must not focus this one again.
  const latestQuestion = useRef(question)
  latestQuestion.current = question
  const latestHandleUpdate = useRef(handleUpdate)
  latestHandleUpdate.current =
    isFocused || !handleUpdateWithoutFocus
      ? handleUpdate
      : handleUpdateWithoutFocus
  // The children of the latest render, see getLatestChildArray().
  const latestChildren = useRef(children)
  latestChildren.current = children

  const isSingleChoiceTheme = singleChoiceThemes.includes(
    question.questionThemeName
  )

  // Use useQuery directly to avoid circular dependencies
  const { data: activeLanguage = language } = useQuery({
    queryKey: ['appState', STATES.ACTIVE_LANGUAGE],
    queryFn: () => language,
    staleTime: Infinity,
    cacheTime: Infinity,
    meta: {
      persist: true,
    },
  })

  const filterAndSortChildren = (childArray, props, newChild) => {
    let firstScaleChildren = []
    let secondScaleChildren = []
    let updatedChildren = [...childArray]

    if (props.scaleId !== undefined) {
      firstScaleChildren = updatedChildren.filter(
        (child) => child.scaleId === SCALE_1
      )
      secondScaleChildren = updatedChildren.filter(
        (child) => child.scaleId === SCALE_2
      )

      updatedChildren =
        props.scaleId === SCALE_1 ? firstScaleChildren : secondScaleChildren
    }

    if (newChild) {
      if (props.insertAfterIndex !== undefined) {
        updatedChildren.splice(props.insertAfterIndex + 1, 0, newChild)
      } else {
        updatedChildren.push(newChild)
      }
    }

    updatedChildren =
      props.scaleId !== undefined
        ? [...firstScaleChildren, ...secondScaleChildren]
        : updatedChildren

    return updatedChildren.map((child, index) => ({
      ...child,
      sortOrder: index,
    }))
  }

  /**
   * Create a new, empty answer or subquestion of this question in all survey languages.
   */
  const createChild = (entityType, code, props = {}) => {
    const languages = surveySettings?.languages ?? []
    const newChild =
      entityType === Entities.answer
        ? getAnswerExample({
            qid: question.qid,
            code,
            languages,
            ...props,
          })
        : getQuestionExample({
            gid: question.gid,
            scaleId: question.scaleId,
            parentQid: question.qid,
            languages,
            title: code,
            ...props,
          })

    newChild.appKey = `${entityType}-${APP_KEY_PREFIX}${RandomNumber()}`
    return newChild
  }

  const handleChildAdd = (childArray = [], entityType, props = {}) => {
    const childKey = entityType === Entities.answer ? 'answers' : 'subquestions'
    const scaleChildren =
      props.scaleId !== undefined
        ? childArray.filter((child) => child.scaleId === props.scaleId)
        : childArray
    const code =
      entityType === Entities.answer
        ? getNextAnswerCode({ ...question, answers: scaleChildren }, null)
        : getNextSubQuestionCode(
            { ...question, subquestions: scaleChildren },
            null
          )

    const newChild = createChild(entityType, code, props)
    const updatedChildren = filterAndSortChildren(childArray, props, newChild)

    addToBuffer(
      createChildrenUpdateOperation(question.qid, entityType, updatedChildren)
    )
    handleUpdate({ [childKey]: updatedChildren })
  }

  /**
   * Append the labels of a label set as new answers or subquestions.
   */
  const handleLabelSetLoad = (childArray = [], entityType, labels = []) => {
    if (!labels.length) {
      return
    }
    const isAnswer = entityType === Entities.answer
    const childKey = isAnswer ? 'answers' : 'subquestions'
    const textKey = isAnswer ? 'answer' : 'question'
    const codes = getLabelSetCodes(labels, question, childArray, entityType)

    const newChildren = labels.map((label, index) => {
      const child = createChild(entityType, codes[index])
      if (isAnswer) {
        child.assessmentValue = Number(label.assessmentValue) || 0
      }
      Object.keys(child.l10ns).forEach((language) => {
        child.l10ns[language][textKey] = label.l10ns?.[language] ?? ''
      })
      return child
    })

    const updatedChildren = [...childArray, ...newChildren].map(
      (child, index) => ({
        ...child,
        sortOrder: index,
      })
    )

    addToBuffer(
      createChildrenUpdateOperation(question.qid, entityType, updatedChildren)
    )
    handleUpdate({ [childKey]: updatedChildren })
  }

  /**
   * Saves changed image option attributes along with a children update, so
   * both end up in one question update. The changes must be built from the
   * attributes of the latest question. They are only built for the image
   * select themes: Other questions skip parsing the attributes on every
   * change.
   *
   * @param {Function} buildChanges returns the changed values keyed by
   *   attribute and language, or null when there are none
   * @returns {Object} the question changes for the attributes, or an empty
   *   object
   */
  const saveImageOptionChanges = (buildChanges) => {
    if (!getImageSelectThemes().includes(question.questionThemeName)) {
      return {}
    }
    const attributeChanges = buildChanges()
    if (!attributeChanges) {
      return {}
    }
    addToBuffer(
      createBufferOperation(question.qid)
        .questionAttribute()
        .update(attributeChanges)
    )
    return {
      attributes: applyAttributeChanges(
        latestQuestion.current.attributes,
        attributeChanges
      ),
    }
  }

  /**
   * Returns the code an answer or subquestion has in the question itself. The
   * children passed to the handlers are copied from the question one render
   * later, so while typing fast they can still have an older code. The image
   * option attributes always match the latest question.
   *
   * @param {Object} child the answer or subquestion
   * @param {string} entityType Entities.answer or Entities.subquestion
   * @returns {string|undefined} the code
   */
  const getCurrentChildCode = (child, entityType) => {
    const isAnswer = entityType === Entities.answer
    const idKey = isAnswer ? 'aid' : 'qid'
    const codeKey = isAnswer ? 'code' : 'title'
    const currentQuestion = latestQuestion.current
    const questionChildren =
      (isAnswer ? currentQuestion.answers : currentQuestion.subquestions) || []
    // With an appKey, only the appKey counts: New options get random
    // temporary IDs, which can be the same for two of them. Without a match,
    // the child's own code is used.
    const currentChild = child.appKey
      ? questionChildren.find(
          (questionChild) => questionChild.appKey === child.appKey
        )
      : questionChildren.find(
          (questionChild) => questionChild[idKey] === child[idKey]
        )
    return (currentChild ?? child)[codeKey]
  }

  /**
   * Deletes an answer or subquestion.
   *
   * @param {number|string} childId ID of the answer or subquestion
   * @param {Object[]} childArray the current answers or subquestions
   * @param {string} entityType Entities.answer or Entities.subquestion
   * @returns {void}
   */
  const handleChildDelete = (childId, childArray = [], entityType) => {
    const childKey = entityType === Entities.answer ? 'answers' : 'subquestions'
    const idKey = entityType === Entities.answer ? 'aid' : 'qid'

    const deletedChild = (childArray || []).find(
      (child) => child[idKey] === childId
    )
    const updatedChildren = (childArray || []).filter(
      (child) => child[idKey] !== childId
    )

    addToBuffer(
      createChildrenUpdateOperation(question.qid, entityType, updatedChildren)
    )
    // Image select themes: Drop the image settings of the deleted option.
    const attributeUpdate = deletedChild
      ? saveImageOptionChanges(() =>
          getImageOptionDeleteChanges(
            latestQuestion.current.attributes,
            getCurrentChildCode(deletedChild, entityType)
          )
        )
      : {}
    handleUpdate({ [childKey]: updatedChildren, ...attributeUpdate })
  }

  const handleOnChildDragEnd = (
    dropResult,
    childArray = [],
    entityType,
    props = {}
  ) => {
    // dropped outside the list
    if (!dropResult.destination) {
      return
    }
    const childKey = entityType === Entities.answer ? 'answers' : 'subquestions'

    let firstScaleChildren = []
    let secondScaleChildren = []
    let updatedChildren = [...childArray]

    if (props.scaleId !== undefined) {
      firstScaleChildren = updatedChildren.filter(
        (child) => child.scaleId === SCALE_1
      )
      secondScaleChildren = updatedChildren.filter(
        (child) => child.scaleId === SCALE_2
      )

      updatedChildren =
        props.scaleId === SCALE_1 ? firstScaleChildren : secondScaleChildren
    }

    const startIndex = dropResult.source.index
    const endIndex = dropResult.destination.index

    const [removed] = updatedChildren.splice(startIndex, 1)
    updatedChildren.splice(endIndex, 0, removed)

    // merge firstScale and secondScale into one array
    updatedChildren =
      props.scaleId !== undefined
        ? [...firstScaleChildren, ...secondScaleChildren]
        : updatedChildren

    // update the sortOrder (for answers and subQuestions the sortOrder starts from 0)
    updatedChildren = updatedChildren.map((child, index) => {
      return { ...child, sortOrder: index }
    })

    addToBuffer(
      createChildrenUpdateOperation(question.qid, entityType, updatedChildren)
    )
    handleUpdate({ [childKey]: updatedChildren })
  }

  /**
   * Returns the children and position to update. Image uploads finish with
   * the children of the render where they started: Options renamed, added or
   * deleted meanwhile would be reverted. So in the image select themes, an
   * older array is replaced by the latest one, with the option found by its
   * appKey or ID. Other question types pass their own arrays and keep them.
   *
   * @param {number} childIndex position of the answer or subquestion
   * @param {Object[]} childArray the answers or subquestions passed in
   * @param {string} entityType Entities.answer or Entities.subquestion
   * @returns {{childArray: Object[], childIndex: number}|null} the children
   *   and position to use, or null when the option no longer exists
   */
  const getLatestChildArray = (childIndex, childArray, entityType) => {
    const currentChildren = latestChildren.current
    if (
      !getImageSelectThemes().includes(
        latestQuestion.current.questionThemeName
      ) ||
      childArray === currentChildren ||
      !childArray[childIndex]
    ) {
      return { childArray, childIndex }
    }
    const child = childArray[childIndex]
    const idKey = entityType === Entities.answer ? 'aid' : 'qid'
    const latestIndex = currentChildren.findIndex((currentChild) =>
      child.appKey
        ? currentChild.appKey === child.appKey
        : currentChild[idKey] === child[idKey]
    )
    return latestIndex === -1
      ? null
      : { childArray: currentChildren, childIndex: latestIndex }
  }

  /**
   * Changes the text of an answer or subquestion in the active language. In
   * the image select themes, the text is the image path. An older array of
   * children is updated in the latest one, see getLatestChildArray().
   *
   * @param {string} newValue the new text or image path
   * @param {number} childIndex position of the answer or subquestion
   * @param {Object[]} childArray the current answers or subquestions
   * @param {string} entityType Entities.answer or Entities.subquestion
   * @returns {void}
   */
  const handleChildLUpdate = (
    newValue = '',
    passedChildIndex,
    passedChildArray = [],
    entityType
  ) => {
    const latest = getLatestChildArray(
      passedChildIndex,
      passedChildArray,
      entityType
    )
    if (!latest) {
      // The option was deleted while its image was uploading.
      return
    }
    const { childArray, childIndex } = latest
    const updatedChildren = [...childArray]
    const l10nsKey = entityType === Entities.answer ? 'answer' : 'question'
    const childKey = entityType === Entities.answer ? 'answers' : 'subquestions'

    if (updatedChildren[childIndex] === undefined) {
      reportExtras({
        extraData: {
          questionThemeName: question.questionThemeName,
          updatedEntities: updatedChildren,
          updateKey: childKey,
          index: childIndex,
          question,
        },
        message: `Error while updating l10n in ${question.questionThemeName} - unable to find item`,
      })
      return
    }

    const l10ns = updatedChildren[childIndex]['l10ns'] || {}
    const imageChanged = l10ns[activeLanguage]?.[l10nsKey] !== newValue

    updatedChildren[childIndex] = {
      ...updatedChildren[childIndex],
      l10ns: {
        ...l10ns,
        [activeLanguage]: {
          ...l10ns[activeLanguage],
          [l10nsKey]: newValue,
          language: activeLanguage,
        },
      },
    }

    addToBuffer(
      createChildrenUpdateOperation(question.qid, entityType, updatedChildren)
    )
    // Image select themes: A new or removed image starts without settings.
    const attributeUpdate = imageChanged
      ? saveImageOptionChanges(() =>
          getImageOptionResetChanges(
            latestQuestion.current.attributes,
            getCurrentChildCode(updatedChildren[childIndex], entityType),
            activeLanguage
          )
        )
      : {}
    // Also after an upload: Only the changed children and attributes are
    // applied, to the latest question.
    latestHandleUpdate.current({
      [childKey]: updatedChildren,
      ...attributeUpdate,
    })
  }

  /**
   * Changes the code of an answer or subquestion, if the new code is valid.
   *
   * @param {Object} params
   * @param {string} params.newCode the new code
   * @param {number} params.childIndex position of the answer or subquestion
   * @param {Object[]} params.childArray the current answers or subquestions
   * @param {string} params.entityType Entities.answer or Entities.subquestion
   * @param {string} params.entityTitleKey 'answer' or 'question'
   * @returns {void}
   */
  const handleChildCodeUpdate = ({
    newCode = '',
    childIndex,
    childArray = [],
    entityType,
    entityTitleKey,
  }) => {
    const validationMessage = validateCode({
      entityTitleKey,
      entityItems: childArray,
      childIndex,
      newCode,
    })

    if (validationMessage !== '') {
      showErrorMessage(validationMessage, 'top-center')
      return
    }

    const updatedChildren = [...childArray]
    const childKey = entityType === Entities.answer ? 'answers' : 'subquestions'
    const codeKey = entityType === Entities.answer ? 'code' : 'title'

    if (updatedChildren[childIndex] === undefined) {
      reportExtras({
        extraData: {
          questionThemeName: question.questionThemeName,
          updatedEntities: updatedChildren,
          updateKey: childKey,
          index: childIndex,
          question,
        },
        message: `Error while updating code in ${question.questionThemeName} - unable to find item`,
      })
      return
    }

    const oldCode = getCurrentChildCode(updatedChildren[childIndex], entityType)
    updatedChildren[childIndex] = {
      ...updatedChildren[childIndex],
      [codeKey]: newCode,
    }

    addToBuffer(
      createChildrenUpdateOperation(question.qid, entityType, updatedChildren)
    )
    // Image select themes: The image settings are keyed by code, move them.
    const attributeUpdate = saveImageOptionChanges(() =>
      getImageOptionRenameChanges(
        latestQuestion.current.attributes,
        oldCode,
        newCode
      )
    )
    handleUpdate({ [childKey]: updatedChildren, ...attributeUpdate })
  }

  const handleChildAssessmentValueUpdate = ({
    newAssessmentValue = '',
    childIndex,
    childArray = [],
  }) => {
    const updatedChildren = [...childArray]

    if (updatedChildren[childIndex] === undefined) {
      reportExtras({
        extraData: {
          questionThemeName: question.questionThemeName,
          updatedEntities: updatedChildren,
          updateKey: 'answers',
          index: childIndex,
          question,
        },
        message: `Error while updating assessment value in ${question.questionThemeName} - unable to find item`,
      })
      return
    }

    updatedChildren[childIndex] = {
      ...updatedChildren[childIndex],
      assessmentValue: newAssessmentValue,
    }

    // Mirrors backend rule: numerical, integerOnly, allowEmpty (Yii CNumberValidator)
    const assessmentValueString = String(newAssessmentValue ?? '')
    const isValidAssessmentValue =
      assessmentValueString.trim() === '' ||
      /^\s*[+-]?\d+\s*$/.test(assessmentValueString)

    if (isValidAssessmentValue) {
      // An empty field is stored as 0 (DB column is NOT NULL DEFAULT 0), so
      // send that explicitly. The UI keeps "" while the user is still typing.
      const childrenToSave = updatedChildren.map((child, index) =>
        index === childIndex && assessmentValueString.trim() === ''
          ? { ...child, assessmentValue: 0 }
          : child
      )

      const operation = createBufferOperation(question.qid)
        .answer()
        .update([...childrenToSave])

      addToBuffer(operation)
    }

    handleUpdate({ answers: updatedChildren })
  }

  const validateCode = ({
    newCode,
    childIndex,
    entityItems,
    entityTitleKey,
  }) => {
    const titleKey = entityTitleKey === 'answer' ? 'code' : 'title'
    const newCodeIndex = entityItems
      ? entityItems.findIndex(
          (item) => item[titleKey]?.toUpperCase() === newCode.toUpperCase()
        )
      : -1

    const codeExist = newCodeIndex !== childIndex && newCodeIndex !== -1

    let newErrorMessage = ''

    if (newCode) {
      newErrorMessage = TestValidation(newCode).error
        ? t('Only letters and numbers are allowed.')
        : ''
    }

    if (codeExist) {
      if (entityTitleKey === 'answer') {
        newErrorMessage = t('Answer codes must be unique.')
      } else {
        newErrorMessage = t('Subquestion codes must be unique.')
      }
    }

    return newErrorMessage
  }

  useEffect(() => {
    const children = isSingleChoiceTheme
      ? question.answers
      : question.subquestions

    setChildren(children)
  }, [question.answers, question.subquestions, language, isSingleChoiceTheme])

  // useEffect(() => {
  //   const children = isSingleChoiceTheme
  //     ? (question.answers ?? [])
  //     : (question.subquestions ?? [])

  //   const childKey = isSingleChoiceTheme ? 'answers' : 'subquestions'

  //   const operation =
  //     childKey === 'answers'
  //       ? createBufferOperation(question.qid)
  //           .answer()
  //           .update([...children])
  //       : createBufferOperation(question.qid)
  //           .subquestion()
  //           .update([...children])

  //   addToBuffer(operation)
  //   handleUpdate({ [childKey]: children })
  //   setChildren(children)
  // }, [question.questionThemeName, isSingleChoiceTheme, question.qid, question.answers, question.subquestions, addToBuffer, handleUpdate])

  return {
    children,
    handleChildAdd,
    handleLabelSetLoad,
    handleChildDelete,
    handleOnChildDragEnd,
    handleChildLUpdate,
    handleChildCodeUpdate,
    handleChildAssessmentValueUpdate,
    activeLanguage,
  }
}
