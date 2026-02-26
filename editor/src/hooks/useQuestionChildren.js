import { useEffect, useState } from 'react'
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
} from 'helpers'
import { singleChoiceThemes } from 'components/QuestionTypes'
import { reportExtras } from 'appInstrumentation'

export const useQuestionChildren = ({
  question,
  handleUpdate,
  surveySettings,
  language,
}) => {
  const { addToBuffer } = useBuffer()
  const [children, setChildren] = useState([])

  const isSingleChoiceTheme = singleChoiceThemes.includes(
    question.questionThemeName
  )

  // Use useQuery directly to avoid circular dependencies
  const { data: codeToQuestion = {} } = useQuery({
    queryKey: ['appState', STATES.CODE_TO_QUESTION],
    queryFn: () => ({}),
    staleTime: Infinity,
    cacheTime: Infinity,
    meta: {
      persist: true,
    },
  })

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
      updatedChildren.push(newChild)
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

  const handleChildAdd = (childArray = [], entityType, props = {}) => {
    const childKey = entityType === Entities.answer ? 'answers' : 'subquestions'

    const newChild =
      entityType === Entities.answer
        ? getAnswerExample({
            qid: question.qid,
            code: getNextAnswerCode(codeToQuestion, question.qid, 0),
            languages: surveySettings.languages,
            ...props,
          })
        : getQuestionExample({
            gid: question.gid,
            scaleId: question.scaleId,
            parentQid: question.qid,
            languages: surveySettings.languages,
            title: getNextSubQuestionCode(codeToQuestion, question.qid, null),
            ...props,
          })

    const updatedChildren = filterAndSortChildren(childArray, props, newChild)

    const operation =
      entityType === Entities.answer
        ? createBufferOperation(question.qid)
            .answer()
            .update([...updatedChildren])
        : createBufferOperation(question.qid)
            .subquestion()
            .update([...updatedChildren])

    addToBuffer(operation)
    handleUpdate({ [childKey]: updatedChildren })
  }

  const handleChildDelete = (childId, childArray = [], entityType) => {
    const childKey = entityType === Entities.answer ? 'answers' : 'subquestions'
    const idKey = entityType === Entities.answer ? 'aid' : 'qid'

    const updatedChildren = (childArray || []).filter(
      (child) => child[idKey] !== childId
    )

    const operation =
      entityType === Entities.answer
        ? createBufferOperation(question.qid)
            .answer()
            .update([...updatedChildren])
        : createBufferOperation(question.qid)
            .subquestion()
            .update([...updatedChildren])

    addToBuffer(operation)
    handleUpdate({ [childKey]: updatedChildren })
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

    const operation =
      entityType === Entities.answer
        ? createBufferOperation(question.qid)
            .answer()
            .update([...updatedChildren])
        : createBufferOperation(question.qid)
            .subquestion()
            .update([...updatedChildren])

    addToBuffer(operation)
    handleUpdate({ [childKey]: updatedChildren })
  }

  const handleChildLUpdate = (
    newValue = '',
    childIndex,
    childArray = [],
    entityType,
    isL10nsUpdate = true
  ) => {
    const updatedChildren = [...childArray]
    const l10nsKey = entityType === Entities.answer ? 'answer' : 'question'
    const childKey = entityType === Entities.answer ? 'answers' : 'subquestions'
    const idKey = entityType === Entities.answer ? 'code' : 'title'

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

    if (isL10nsUpdate) {
      const l10ns = updatedChildren[childIndex]['l10ns'] || {}

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
    } else {
      updatedChildren[childIndex] = {
        ...updatedChildren[childIndex],
        [idKey]: newValue,
      }
    }

    const operation =
      entityType === Entities.answer
        ? createBufferOperation(question.qid)
            .answer()
            .update([...updatedChildren])
        : createBufferOperation(question.qid)
            .subquestion()
            .update([...updatedChildren])

    addToBuffer(operation)
    handleUpdate({ [childKey]: updatedChildren })
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
    handleChildDelete,
    handleOnChildDragEnd,
    handleChildLUpdate,
    activeLanguage,
  }
}
