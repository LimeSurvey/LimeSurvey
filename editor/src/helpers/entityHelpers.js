import { isUndefined } from 'lodash'
import { EntitiesType } from './Buffer'
import { arrayDeleteItem } from './arrayDeleteItem'
import { getEntityInfo } from './getEntityInfo'
import { replaceValuesInObject } from './replaceValuesInObject'

/**
 * Replace survey branch references.
 *
 * Some times we need to modify a value deep within the survey object
 * such as when replacing a temp question ID with a new one in a survey.
 * In order to ensure a re-render after such a change, we must replace
 * all parents of the target to ensure re-react re-renders.
 * This function will replace all parents with new objects.
 */
export const replaceSurveyEntityParentRefs = function (
  survey,
  groupIndex,
  questionIndex,
  subquestionIndex,
  answerIndex
) {
  if (isUndefined(groupIndex) || !survey?.questionGroups?.[groupIndex]) return

  const newGroups = [...survey.questionGroups]
  const newGroup = { ...newGroups[groupIndex] }
  newGroups[groupIndex] = newGroup
  survey.questionGroups = newGroups

  if (isUndefined(questionIndex) || !newGroup.questions?.[questionIndex]) return

  const newQuestions = [...newGroup.questions]
  const newQuestion = { ...newQuestions[questionIndex] }
  newQuestions[questionIndex] = newQuestion
  newGroup.questions = newQuestions

  if (!isUndefined(subquestionIndex)) {
    const newSubquestions = [...newQuestion.subquestions]
    newSubquestions[subquestionIndex] = { ...newSubquestions[subquestionIndex] }
    newQuestion.subquestions = newSubquestions
  }

  if (!isUndefined(answerIndex)) {
    const newAnswers = [...newQuestion.answers]
    newAnswers[answerIndex] = { ...newAnswers[answerIndex] }
    newQuestion.answers = newAnswers
  }
}

export const replaceTempIdsInSurveyAndBuffer = (
  mappings = [],
  entityType,
  survey,
  operationsBuffer,
  focused = undefined
) => {
  mappings.forEach((tempIdMap) => {
    // Retrieve the entity based on type and temporary ID
    let entityInfo = getEntityInfo(tempIdMap.tempId, survey, entityType)

    if (focused) {
      if (focused.qid === tempIdMap.tempId) {
        focused.qid = tempIdMap.id
      } else if (focused.gid === tempIdMap.tempId) {
        focused.gid = tempIdMap.id
      }
    }

    if (Object.keys(entityInfo).length === 0) {
      // eslint-disable-next-line no-console
      console.error(
        `Error: Could not find ${entityType} with tempId ${tempIdMap.tempId}`
      )
      return
    }

    // Replace tempId with the new ID in the entity, if applicable
    if (entityInfo[entityType]) {
      entityInfo[entityType] = replaceValuesInObject(
        entityInfo[entityType],
        tempIdMap.tempId,
        tempIdMap.id
      )
    }

    // Update operations in the buffer with the new ID
    operationsBuffer.setOperations(
      replaceValuesInObject(
        operationsBuffer.getOperations(),
        tempIdMap.tempId,
        tempIdMap.id
      )
    )

    if (entityType === EntitiesType.group) {
      if (!isUndefined(entityInfo.groupIndex)) {
        survey.questionGroups[entityInfo.groupIndex] = entityInfo[entityType]
      }
    } else if (entityType === EntitiesType.question) {
      if (
        !isUndefined(entityInfo.groupIndex) &&
        !isUndefined(entityInfo.questionIndex)
      ) {
        survey.questionGroups[entityInfo.groupIndex].questions[
          entityInfo.questionIndex
        ] = entityInfo[entityType]
      }
    } else if (entityType === EntitiesType.subquestion) {
      if (
        !isUndefined(entityInfo.groupIndex) &&
        !isUndefined(entityInfo.questionIndex) &&
        !isUndefined(entityInfo.subquestionIndex)
      ) {
        survey.questionGroups[entityInfo.groupIndex].questions[
          entityInfo.questionIndex
        ].subquestions[entityInfo.subquestionIndex] = entityInfo[entityType]
      }
    } else if (entityType === EntitiesType.answer) {
      if (
        !isUndefined(entityInfo.groupIndex) &&
        !isUndefined(entityInfo.questionIndex)
      ) {
        survey.questionGroups[entityInfo.groupIndex].questions[
          entityInfo.questionIndex
        ].answers[entityInfo.answerIndex] = entityInfo[entityType]
      }
    } else if (entityType === EntitiesType.condition) {
      if (
        !isUndefined(entityInfo.groupIndex) &&
        !isUndefined(entityInfo.questionIndex) &&
        !isUndefined(entityInfo.scenarioIndex) &&
        !isUndefined(entityInfo.conditionIndex)
      ) {
        survey.questionGroups[entityInfo.groupIndex].questions[
          entityInfo.questionIndex
        ].scenarios[entityInfo.scenarioIndex].conditions[
          entityInfo.conditionIndex
        ] = entityInfo[entityType]
      }
    }

    replaceSurveyEntityParentRefs(
      survey,
      entityInfo.groupIndex,
      entityInfo.questionIndex,
      entityInfo.subquestionIndex,
      entityInfo.answerIndex
    )
  })

  return { survey, operationsBuffer, focused }
}

export const updateSurveyEntitiesData = (
  entityType,
  survey,
  operationsBuffer,
  entityDataMap = {}
) => {
  if (!entityDataMap || Object.keys(entityDataMap).length === 0) {
    return { survey, operationsBuffer }
  }

  if (entityType === EntitiesType.question) {
    const { qid, ...restData } = entityDataMap

    if (!qid) return { survey, operationsBuffer }
    let entity = getEntityInfo(entityDataMap.qid, survey, entityType)

    if (!isUndefined(entity.groupIndex) && !isUndefined(entity.questionIndex)) {
      const question =
        survey.questionGroups[entity.groupIndex].questions[entity.questionIndex]

      Object.entries(restData ?? {}).forEach(([key, value]) => {
        question[key] = value
      })

      survey.questionGroups[entity.groupIndex].questions[entity.questionIndex] =
        question
    }
  }

  return { survey, operationsBuffer }
}

export const processValidationErrorsAndUpdateOperations = (
  errors, // Errors that get from the API.
  readyOperationsBuffer, // The operations that were sent to the API.
  operationsBuffer // Current operations in the buffer.
) => {
  const readyOperations = readyOperationsBuffer.getOperations()

  for (let i = 0; i < errors?.length; i++) {
    const validationError = errors[i]
    const sentOperationIndex = readyOperationsBuffer.findIndex(
      validationError.id,
      validationError.op,
      validationError.entity
    )

    const operationIndexInBuffer = operationsBuffer.findIndex(
      validationError.id,
      validationError.op,
      validationError.entity
    )

    // if the user has made some changes to the operation, then we skip and remove that error
    // example: we might get an error if the user tries to update a question title, but while the request was still being processed in the backend
    // the user has made changes to the question title, so we skip the error.
    if (operationIndexInBuffer !== -1) {
      errors = arrayDeleteItem(errors, i)[0]
      continue
    }

    if (readyOperations[sentOperationIndex]) {
      const op = {
        ...readyOperations[sentOperationIndex],
        error: true,
      }
      operationsBuffer.addOperation(op, false, true)
    }
  }

  return errors
}
