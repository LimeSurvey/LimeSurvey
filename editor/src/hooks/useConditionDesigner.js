import { useRef, useState } from 'react'

import {
  createBufferOperation,
  errorToast,
  getQuestionById,
  QUESTION_RELEVANCE_DEFAULT_VALUE,
} from 'helpers'

import {
  buildScenarioConditionsForUpdate,
  buildScenarioPayload,
  getApiOperationActions,
  getDefaultCondition,
  hasConditionChanged,
  isDuplicateCondition,
  showSuccessMessage,
  updateQuestionScenarios,
  syncSurveyQuestionWithScenarios,
  isDuplicate,
} from '../components/ConditionDesigner/utils'
import { useBuffer } from './useBuffer'
import { useFocused } from './useFocused'

const getSuccessMessage = (action) =>
  action === 'delete'
    ? t('Condition deleted successfully')
    : t('Condition applied successfully')

// Splits multi-value conditions ("A1,A2") into one condition per value.
const expandConditionValues = (conditions) =>
  conditions.flatMap(({ value, ...rest }) =>
    value
      .toString()
      .split(',')
      .map((v) => ({ ...rest, value: v }))
  )

/**
 * Manages the scenario being edited in the Condition Designer.
 *
 * @param {object} params
 * @param {object} params.survey
 * @param {Function} params.update survey updater
 * @param {object} params.question the question the conditions belong to
 * @param {object|null} params.scenarioToPatch scenario being edited, null when creating
 * @param {Array} params.previousQuestions questions available as condition sources
 */
export const useConditionDesigner = ({
  survey,
  update,
  question,
  scenarioToPatch = null,
  previousQuestions = [],
}) => {
  const { focused } = useFocused()
  const { addToBuffer } = useBuffer()

  const [scid, setScid] = useState(scenarioToPatch ? scenarioToPatch.scid : 1)
  const [conditions, setConditions] = useState(() =>
    scenarioToPatch
      ? buildScenarioConditionsForUpdate(scenarioToPatch, previousQuestions)
      : [getDefaultCondition()]
  )
  const originalScid = useRef(scenarioToPatch ? scid : null)

  const withUpdatedFlag = (condition) =>
    scenarioToPatch
      ? {
          ...condition,
          isUpdated: hasConditionChanged(condition, scenarioToPatch, question),
        }
      : condition

  const syncQuestion = () => {
    // Resolve the position from the survey, focus indexes can be stale.
    const { groupIndex, questionIndex } = getQuestionById(question.qid, survey)
    if (groupIndex === undefined) return

    syncSurveyQuestionWithScenarios(
      survey,
      question,
      groupIndex,
      questionIndex,
      update,
      focused
    )
  }

  const removeCondition = (index) => {
    if (conditions[index].cid) {
      setConditions((prev) =>
        prev.map((cond, i) =>
          i === index ? { ...cond, isDeleted: true } : cond
        )
      )
    } else {
      setConditions((prev) => {
        const updated = prev.filter((_, i) => i !== index)
        return updated.length === 0 ? [getDefaultCondition()] : updated
      })
    }
  }

  const addCondition = () => {
    const sorted = [...conditions].sort((a, b) =>
      String(a.cqid).localeCompare(String(b.cqid), undefined, { numeric: true })
    )
    setConditions([...sorted, { ...getDefaultCondition(), scenario: scid }])
  }

  const updateCondition = (index, key, value) => {
    setConditions((prev) =>
      prev.map((cond, i) =>
        i === index ? withUpdatedFlag({ ...cond, [key]: value }) : cond
      )
    )
  }

  const handleScenarioNameChange = (e) => {
    const newScid = Number(e.target.value)
    if (isNaN(newScid)) return

    setScid(newScid)
    setConditions((prev) =>
      prev.map((cond) => withUpdatedFlag({ ...cond, scenario: newScid }))
    )
  }

  const applyScenarios = (scenarios) => {
    updateQuestionScenarios(scenarios, question, originalScid)
    syncQuestion()
  }

  const deleteConditions = (deletedConditions) => {
    deletedConditions.forEach((condition) => {
      const operation = createBufferOperation(condition.cid)
        .questionCondition()
        .delete()
      operation.qid = condition.qid
      operation.props = {
        qid: condition.qid,
        scenarios: [
          {
            scid: scenarioToPatch.scid,
            conditions: [
              {
                cid: condition.cid,
                action: getApiOperationActions().CONDITION.DELETE,
              },
            ],
          },
        ],
      }
      addToBuffer(operation)

      question.scenarios = question.scenarios
        .map((sc) => {
          if (sc.scid === scenarioToPatch.scid) {
            sc.conditions = sc.conditions.filter((c) => c.cid != condition.cid)
          }
          return sc
        })
        .filter((sc) => sc.conditions.length > 0)

      if (question.scenarios.length === 0) {
        question.relevance = QUESTION_RELEVANCE_DEFAULT_VALUE
      }

      syncQuestion()
      focused.scenarios = question.scenarios
      focused.relevance = QUESTION_RELEVANCE_DEFAULT_VALUE
    })
  }

  /**
   * Saves the scenario locally and queues the API operations.
   * @returns {{ saved: boolean, hasDuplicate: boolean, message: string }}
   */
  const saveScenario = () => {
    const originalScenarios = question.scenarios

    try {
      const deletedConditions = conditions.filter((c) => c.isDeleted)
      const activeConditions = expandConditionValues(
        conditions.filter((c) => !c.isDeleted)
      )
      const apiProps = { qid: question.qid, scenarios: [] }
      let message = ''

      if (deletedConditions.length) {
        deleteConditions(deletedConditions)
        message = getSuccessMessage('delete')
      }

      const updatedConditions = activeConditions.filter((c) => c.isUpdated)
      if (updatedConditions.length) {
        const { scenarios } = buildScenarioPayload(
          question,
          updatedConditions,
          scid
        )
        apiProps.scenarios.push(...scenarios)
        applyScenarios(scenarios)
        message = getSuccessMessage('update')
      }

      const unsavedConditions = activeConditions.filter((c) => !c.cid)
      const newConditions = unsavedConditions.filter(
        (c) => !isDuplicateCondition(c, question, scid, scenarioToPatch)
      )
      const hasDuplicate = unsavedConditions.length !== newConditions.length
      const uniqueNewConditions = newConditions.filter(
        (cond, index, self) =>
          index === self.findIndex((c) => isDuplicate(cond, c))
      )

      if (uniqueNewConditions.length) {
        const { scenarios } = buildScenarioPayload(
          question,
          uniqueNewConditions,
          scid
        )
        apiProps.scenarios.push(...scenarios)
        applyScenarios(scenarios)
        message = getSuccessMessage('create')
      }

      if (apiProps.scenarios.length > 0) {
        const operation = createBufferOperation(question.qid)
          .questionCondition()
          .update()
        operation.qid = question.qid
        operation.props = apiProps
        addToBuffer(operation)
      }

      return { saved: Boolean(message), hasDuplicate, message }
    } catch (error) {
      question.scenarios = originalScenarios
      focused.scenarios = originalScenarios
      throw new Error(`Scenario save failed: ${error.message}`)
    }
  }

  const handleSavingScenario = (onDone = () => {}) => {
    const { saved, hasDuplicate, message } = saveScenario()

    if (!saved && hasDuplicate) {
      errorToast(t('Condition already exists'), 'center')
    } else {
      showSuccessMessage(message)
    }
    onDone()
  }

  return {
    scid,
    conditions,
    addCondition,
    updateCondition,
    removeCondition,
    handleScenarioNameChange,
    saveScenario,
    handleSavingScenario,
  }
}
