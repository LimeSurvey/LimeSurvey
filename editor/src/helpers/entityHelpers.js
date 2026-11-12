import { isUndefined } from 'lodash'
import { Entities, EntitiesType } from './Buffer'
import {
  FIELDNAME_TEMP_ID_REGEX,
  NEW_OBJECT_ID_PREFIX,
  QUESTION_RELEVANCE_DEFAULT_VALUE,
} from './constants'
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

// Condition fields that hold an ID, and fields that hold fieldnames. Other
// fields are user values (constants, regular expressions, scripts): Never
// changed, even if they look like an ID.
const CONDITION_ID_FIELDS = ['qid', 'cqid', 'cid']
const CONDITION_FIELDNAME_FIELDS = [
  'cfieldname',
  'cquestions',
  'prevQuestionSGQA',
]

/**
 * Replace temp question and subquestion IDs inside a fieldname.
 *
 * @param {string} fieldname A fieldname, e.g. Qtemp__1_S23 or @Qtemp__1@.
 * @param {Map} idMap The real IDs by temp ID.
 * @returns {string} The fieldname with real IDs.
 */
const replaceTempIdsInFieldname = (fieldname, idMap) =>
  fieldname.includes(NEW_OBJECT_ID_PREFIX)
    ? fieldname.replace(FIELDNAME_TEMP_ID_REGEX, (match, prefix, tempId) =>
        idMap.has(tempId) ? `${prefix}${idMap.get(tempId)}` : match
      )
    : fieldname

/**
 * Replace temp IDs with their real IDs in the ID fields of a condition.
 *
 * @param {object} condition A condition of a scenario or condition operation.
 * @param {Map} idMap The real IDs by temp ID.
 * @returns {object} The condition, a new object if an ID was replaced.
 */
const replaceTempIdsInCondition = (condition, idMap) => {
  const replaced = { ...condition }

  CONDITION_ID_FIELDS.forEach((field) => {
    if (idMap.has(condition[field]))
      replaced[field] = idMap.get(condition[field])
  })
  CONDITION_FIELDNAME_FIELDS.forEach((field) => {
    if (typeof condition[field] === 'string') {
      replaced[field] = replaceTempIdsInFieldname(condition[field], idMap)
    }
  })
  // A value can reference another question: @Q12@.
  if (typeof condition.value === 'string' && /^@.*@$/.test(condition.value)) {
    replaced.value = replaceTempIdsInFieldname(condition.value, idMap)
  }

  return Object.keys(replaced).some(
    (field) => replaced[field] !== condition[field]
  )
    ? replaced
    : condition
}

/**
 * Replace temp IDs in the conditions of scenarios.
 *
 * @param {Array} scenarios Scenarios of a question or condition operation.
 * @param {Map} idMap The real IDs by temp ID.
 * @returns {Array} The scenarios, a new array if an ID was replaced.
 */
const replaceTempIdsInScenarios = (scenarios, idMap) => {
  let changed = false
  const replaced = scenarios.map((scenario) => {
    if (!Array.isArray(scenario.conditions)) return scenario

    const conditions = scenario.conditions.map((condition) =>
      replaceTempIdsInCondition(condition, idMap)
    )
    if (
      conditions.every((condition, i) => condition === scenario.conditions[i])
    ) {
      return scenario
    }
    changed = true
    return { ...scenario, conditions }
  })

  return changed ? replaced : scenarios
}

/**
 * Replace temp question and subquestion IDs in the conditions of all
 * questions and in the buffered condition operations: As cqid of conditions
 * on other questions, and inside fieldnames.
 *
 * @param {object} survey The survey, its changed groups and questions are replaced.
 * @param {OperationsBuffer} operationsBuffer The buffer to update.
 * @param {Map} idMap The real IDs by temp ID.
 * @returns {void}
 */
const replaceTempIdsInConditions = (survey, operationsBuffer, idMap) => {
  let surveyChanged = false
  const questionGroups = survey.questionGroups?.map((group) => {
    let groupChanged = false
    const questions = group.questions?.map((question) => {
      if (!question?.scenarios?.length) return question

      const scenarios = replaceTempIdsInScenarios(question.scenarios, idMap)
      if (scenarios === question.scenarios) return question

      groupChanged = true
      return { ...question, scenarios }
    })

    if (!groupChanged) return group
    surveyChanged = true
    return { ...group, questions }
  })

  if (surveyChanged) {
    survey.questionGroups = questionGroups
  }

  // Read without a copy: The replacement returns new objects for changes.
  const operations = operationsBuffer.operations.map((operation) => {
    if (
      operation.entity !== Entities.questionCondition ||
      !Array.isArray(operation.props?.scenarios)
    ) {
      return operation
    }

    const scenarios = replaceTempIdsInScenarios(
      operation.props.scenarios,
      idMap
    )
    return scenarios === operation.props.scenarios
      ? operation
      : { ...operation, props: { ...operation.props, scenarios } }
  })
  if (
    operations.some(
      (operation, i) => operation !== operationsBuffer.operations[i]
    )
  ) {
    operationsBuffer.setOperations(operations)
  }
}

// Temp IDs the server replaced while a condition designer panel is open, with
// their real IDs. Only recorded while a panel is open, and cleared when one
// opens or closes: Temp IDs are random, so an old one can come back for a new
// entity. Only the entity types the condition designer uses are recorded.
const resolvedTempIds = new Map()
// A function, not a constant: EntitiesType isn't loaded yet when this module
// is, through the helpers import cycle.
const isRecordedEntityType = (entityType) =>
  [
    EntitiesType.question,
    EntitiesType.subquestion,
    EntitiesType.condition,
  ].includes(entityType)

let isRecordingTempIds = false

/**
 * Start or stop recording replaced temp IDs, when a condition designer panel
 * opens or closes. Both forget the IDs recorded so far.
 *
 * @param {boolean} isRecording Whether a panel is open.
 * @returns {void}
 */
export const setRecordingTempIds = (isRecording) => {
  resolvedTempIds.clear()
  isRecordingTempIds = isRecording
}

/**
 * Replace temp IDs the server already replaced with their real IDs, also
 * inside condition fieldnames. For state that still holds the temp IDs,
 * e.g. an open form, when the save finished in the meantime.
 *
 * @param {*} value An ID, a condition, or a list of conditions.
 * @returns {*} The value with real IDs, the same value if nothing changed.
 */
export const resolveTempIds = (value) => {
  if (!resolvedTempIds.size) return value
  if (Array.isArray(value)) {
    return value.map((item) => resolveTempIds(item))
  }
  if (typeof value === 'object' && value !== null) {
    return replaceTempIdsInCondition(value, resolvedTempIds)
  }
  return resolvedTempIds.has(value) ? resolvedTempIds.get(value) : value
}

/**
 * Remove conditions from the questions of the survey, e.g. conditions the
 * server will never save. A question left without conditions gets the
 * default relevance, as when its last condition is deleted.
 *
 * @param {object} survey The survey.
 * @param {Function} shouldRemove Called with each condition.
 * @returns {object} The survey, a new object if a condition was removed.
 */
export const removeSurveyConditions = (survey, shouldRemove) => {
  let surveyChanged = false
  const questionGroups = survey?.questionGroups?.map((group) => {
    let groupChanged = false
    const questions = group.questions?.map((question) => {
      if (!question?.scenarios?.length) return question

      const scenarios = question.scenarios
        .map((scenario) => ({
          ...scenario,
          conditions: (scenario.conditions ?? []).filter(
            (condition) => !shouldRemove(condition)
          ),
        }))
        .filter((scenario) => scenario.conditions.length)
      const conditionCount = (items) =>
        items.reduce((count, item) => count + (item.conditions?.length ?? 0), 0)
      if (conditionCount(scenarios) === conditionCount(question.scenarios)) {
        return question
      }

      groupChanged = true
      return {
        ...question,
        scenarios,
        ...(scenarios.length
          ? {}
          : { relevance: QUESTION_RELEVANCE_DEFAULT_VALUE }),
      }
    })

    if (!groupChanged) return group
    surveyChanged = true
    return { ...group, questions }
  })

  return surveyChanged ? { ...survey, questionGroups } : survey
}

export const replaceTempIdsInSurveyAndBuffer = (
  mappings = [],
  entityType,
  survey,
  operationsBuffer,
  focused = undefined
) => {
  mappings.forEach((tempIdMap) => {
    if (isRecordingTempIds && isRecordedEntityType(entityType)) {
      resolvedTempIds.set(tempIdMap.tempId, tempIdMap.id)
    }

    // Retrieve the entity based on type and temporary ID
    let entityInfo = getEntityInfo(tempIdMap.tempId, survey, entityType)

    if (focused) {
      if (focused.qid === tempIdMap.tempId) {
        focused.qid = tempIdMap.id
      } else if (focused.gid === tempIdMap.tempId) {
        focused.gid = tempIdMap.id
      }
    }

    // Update operations in the buffer with the new ID: Also when the entity
    // is gone locally, e.g. a delete that waited for this ID.
    operationsBuffer.setOperations(
      replaceValuesInObject(
        operationsBuffer.getOperations(),
        tempIdMap.tempId,
        tempIdMap.id
      )
    )

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

  // Conditions reference questions and subquestions, also inside fieldnames.
  if (
    mappings.length &&
    (entityType === EntitiesType.question ||
      entityType === EntitiesType.subquestion)
  ) {
    replaceTempIdsInConditions(
      survey,
      operationsBuffer,
      new Map(mappings.map(({ tempId, id }) => [tempId, id]))
    )
  }

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
