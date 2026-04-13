// Helper functions for condition operations
import { QUESTION_RELEVANCE_DEFAULT_VALUE } from 'helpers'

import { getConditionTypeInfo } from './getConditionTypeInfo'
import { getAllowedMethods } from './getAllowedMethods'

const conditionTypeInfo = getConditionTypeInfo()
const allowedMethods = getAllowedMethods()

export const getDefaultCondition = () => ({
  qid: '',
  cqid: '',
  cfieldname: '',
  cquestions: '',
  method: '',
  value: null,
  answers: [],
  scenario: '',
  sourceType: conditionTypeInfo.SOURCE.QUESTION,
  targetType: conditionTypeInfo.TARGET.ANSWER_OPTIONS,
  isDeleted: false,
  isUpdated: false,
})

// Checks if a condition has been modified from its original version
export const hasConditionChanged = (
  currentCondition,
  scenarioToPatch,
  question
) => {
  if (!scenarioToPatch) return false

  // Find the original condition in the current scenario
  const scenario = question.scenarios.find(
    (s) => s.scid === scenarioToPatch.scid
  )
  const original = scenario?.conditions.find(
    (c) => c.cid == currentCondition.cid
  )

  // If no original exists or any field differs, return true
  return !original
    ? false
    : original.qid != currentCondition.qid ||
        original.cqid != currentCondition.cqid ||
        original.cfieldname !== currentCondition.cfieldname ||
        original.method !== currentCondition.method ||
        original.value !== currentCondition.value ||
        scenario.scid != currentCondition.scenario
}

// Checks if a condition already exists in the question scenarios (for updating existing scenarios)
export const isDuplicateCondition = (
  condition,
  question,
  scid,
  scenarioToPatch
) => {
  return question.scenarios.some((scenario) =>
    scenario.conditions.some(
      (existing) =>
        existing.cid != condition.cid && // Don't match against self
        existing.qid == condition.qid &&
        existing.cqid == condition.cqid &&
        existing.cfieldname === condition.cfieldname &&
        existing.method === condition.method &&
        existing.value === condition.value &&
        (scenario.scid === scid || scenario.scid === scenarioToPatch?.scid)
    )
  )
}

// for prevent duplicate conditions in the same scenario (for creating new scenarios)
export const isDuplicate = (a, b) =>
  a.qid === b.qid &&
  a.cqid === b.cqid &&
  a.cfieldname === b.cfieldname &&
  a.method === b.method &&
  a.value === b.value &&
  a.sourceType === b.sourceType &&
  a.targetType === b.targetType

export const hasUnsavedChanges = (
  conditions,
  scenarioToPatch,
  scid,
  isUpdateAction
) => {
  // For update actions
  if (isUpdateAction()) {
    const scidChanged = scenarioToPatch.scid !== scid
    const hasConditionChanges = conditions.some(
      (condition) =>
        !condition.cid ||
        condition.isUpdated ||
        condition.isDeleted ||
        (!condition.isDeleted && condition.scenario !== scenarioToPatch.scid)
    )
    return hasConditionChanges || scidChanged
  }

  // For create actions
  return conditions.length > 0 && Boolean(conditions[0].cfieldname)
}

export const getBaseConditionObject = (condition, scenarioId) => ({
  cid: +condition.cid,
  qid: +condition.qid,
  cqid: +condition.cqid,
  cfieldname: condition.cfieldname,
  cquestions: condition.cfieldname,
  method: condition.method,
  scenario: scenarioId,
  value: condition.value,
  answers: [],
  isDeleted: false,
  isUpdated: false,
})

export const determineConditionSourceType = (
  scenarioCondition,
  processedCondition
) => {
  processedCondition.sourceType = /^{TOKEN:([^}]*)}$/.test(
    scenarioCondition.cfieldname
  )
    ? conditionTypeInfo.SOURCE.PARTICIPANT_DATA
    : conditionTypeInfo.SOURCE.QUESTION
}

export const determineConditionTargetType = (
  condition,
  processedCondition,
  previousQuestions
) => {
  if (condition.method === allowedMethods.REGEX) {
    processedCondition.targetType = conditionTypeInfo.TARGET.REGEX
    return
  }

  if (/^{TOKEN:([^}]*)}$/.test(condition.value)) {
    processedCondition.targetType = conditionTypeInfo.TARGET.PARTICIPANT_DATA
    return
  }

  /**
   * Fieldnames start with a Q, referring to the question
   * then digits follow, representing the qid of the main question
   * then comes either the subquestion, that is, _S or a ranking answer,
   * that is, _R. Then come some digits, representing the subquestion/answer
   * subquestions may be chained, like Q123_S456_S789
   * and some subquestions have # followed by digits
   */
  if (/^@(Q\d+(?:_[SR]\d+)*(?:#\d+)?)@$/.test(condition.value)) {
    const matchingQuestion = previousQuestions.find((q) => {
      return '@' + q.cfieldname + '@' === condition.value
    })
    if (matchingQuestion) {
      processedCondition.targetType =
        conditionTypeInfo.TARGET.ANSWER_OF_OTHER_QUESTION
      return
    }
  }

  const matchingQuestion = previousQuestions.find((q) => {
    const matchingAnswer = q.answers.find(
      (a) => a.cfieldname === processedCondition.cfieldname
    )
    return matchingAnswer !== undefined
  })

  if (matchingQuestion) {
    const matchingAnswer = matchingQuestion.answers.find((answer) => {
      return answer.value.toString() === condition.value.toString()
    })

    if (matchingAnswer) {
      processedCondition.targetType = conditionTypeInfo.TARGET.ANSWER_OPTIONS
    }

    if (matchingQuestion.answers?.length > 0)
      processedCondition.answers = matchingQuestion.answers
  }

  if (!processedCondition.targetType)
    processedCondition.targetType = conditionTypeInfo.TARGET.CONSTANT
}

export const updateQuestionScenarios = (scenarios, question, originalScid) => {
  const [newScenario] = scenarios
  const { scid, conditions: newConditions } = newScenario
  const exists = question.scenarios.some((s) => s.scid === scid)

  if (exists) {
    question.scenarios = question.scenarios.map((sc) =>
      sc.scid === scid
        ? {
            ...sc,
            conditions: [
              ...sc.conditions.filter(
                (c) => !newConditions.some((nc) => nc.cid == c.cid)
              ),
              ...newConditions,
            ].filter(
              (c, idx, arr) => arr.findIndex((cc) => cc.cid == c.cid) === idx
            ),
          }
        : sc
    )
  } else if (newConditions.length) {
    question.scenarios = [...question.scenarios, newScenario]
  }

  if (originalScid.current !== scid) {
    question.scenarios = question.scenarios.filter(
      (sc) => sc.scid !== originalScid.current
    )
  }

  question.scenarios = question.scenarios.filter(
    (sc, idx, arr) => arr.findIndex((s) => s.scid === sc.scid) === idx
  )

  originalScid.current = scid
}

export const syncSurveyQuestionWithScenarios = (
  survey,
  question,
  groupIndex,
  questionIndex,
  update,
  focused
) => {
  const updatedGroups = [...survey.questionGroups]

  updatedGroups[groupIndex].questions[questionIndex] = question
  update({ questionGroups: updatedGroups })

  if (Array.isArray(question?.scenarios) && question.scenarios.length > 0) {
    focused.scenarios = [...question.scenarios].sort((a, b) => a.scid - b.scid)
  }
}

export const isValidRelevanceValue = (newRelevance, question) => {
  return (
    newRelevance.trim() === '' ||
    newRelevance === QUESTION_RELEVANCE_DEFAULT_VALUE ||
    question.relevance.trim() === newRelevance.trim()
  )
}

export const hasQuestionCondition = (question) => {
  const hasScenarios = question?.scenarios?.length > 0
  const hasCustomRelevance =
    typeof question?.relevance === 'string' &&
    question.relevance.length > 0 &&
    question.relevance !== QUESTION_RELEVANCE_DEFAULT_VALUE

  return hasScenarios || hasCustomRelevance
}
