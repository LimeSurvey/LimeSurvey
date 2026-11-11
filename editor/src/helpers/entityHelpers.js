import { isUndefined } from 'lodash'
import { Entities, EntitiesType } from './Buffer'
import { arrayDeleteItem } from './arrayDeleteItem'
import { getEntityInfo } from './getEntityInfo'
import { getTempIdMap, replaceTempIdsInObject } from './replaceTempIdsInObject'
import { replaceValuesInObject } from './replaceValuesInObject'

/**
 * Replace survey branch references.
 *
 * Sometimes we need to modify a value deep within the survey object
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
  const idMap = getTempIdMap(mappings)
  if (!idMap.size) {
    return { survey, operationsBuffer, focused }
  }

  // Update the operations in the buffer once for all the mappings. This is done
  // even for entities no longer in the survey: Their queued operations still
  // need the real ID.
  operationsBuffer.setOperations(
    replaceTempIdsInObject(operationsBuffer.getOperations(), idMap)
  )

  // Groups, questions and subquestions are also referenced elsewhere in the
  // survey (e.g. condition sources, cfieldname): Replace them everywhere in a
  // single pass. Only the changed groups, questions... are copied, so React
  // re-renders them and keeps the others as they are.
  // Conditions too: One pass for all the new conditions of a scenario, not
  // one search of the survey per condition. Their temp IDs ("temp__12_1")
  // can't be mistaken for those of other entities.
  // Answers are replaced one by one below, in the answer only: Their temp IDs
  // have the format of the question ones.
  if (
    entityType === EntitiesType.group ||
    entityType === EntitiesType.question ||
    entityType === EntitiesType.subquestion ||
    entityType === EntitiesType.condition
  ) {
    survey.questionGroups = replaceTempIdsInObject(survey.questionGroups, idMap)

    if (focused) {
      mappings.forEach(({ tempId, id }) => {
        if (focused.qid === tempId) {
          focused.qid = id
        } else if (focused.gid === tempId) {
          focused.gid = id
        }
      })
    }

    return { survey, operationsBuffer, focused }
  }

  mappings.forEach((tempIdMap) => {
    // Retrieve the entity based on type and temporary ID
    let entityInfo = getEntityInfo(tempIdMap.tempId, survey, entityType)

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

    if (entityType === EntitiesType.answer) {
      if (
        !isUndefined(entityInfo.groupIndex) &&
        !isUndefined(entityInfo.questionIndex)
      ) {
        survey.questionGroups[entityInfo.groupIndex].questions[
          entityInfo.questionIndex
        ].answers[entityInfo.answerIndex] = entityInfo[entityType]
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

/**
 * Puts the operations refused by the API back in the buffer, flagged with
 * `error`, and drops the errors of the operations changed again since.
 * Refused condition operations are neither put back nor dropped: Sending a
 * refused change again would only fail again. Their errors are kept, the caller
 * reloads the survey to show the conditions the server has.
 * @param {Array} errors the errors returned by the API
 * @param {OperationsBuffer} readyOperationsBuffer the operations sent to the API
 * @param {OperationsBuffer} operationsBuffer the current buffer, updated in place
 * @returns {Array} the errors left to show
 */
export const processValidationErrorsAndUpdateOperations = (
  errors, // Errors that get from the API.
  readyOperationsBuffer, // The operations that were sent to the API.
  operationsBuffer // Current operations in the buffer.
) => {
  const readyOperations = readyOperationsBuffer.getOperations()

  for (let i = 0; i < errors?.length; i++) {
    const validationError = errors[i]
    if (validationError.entity === Entities.questionCondition) continue

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
