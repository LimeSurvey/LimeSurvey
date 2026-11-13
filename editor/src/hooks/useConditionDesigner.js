import { useRef } from 'react'
import { omit } from 'lodash'

import {
  errorToast,
  QUESTION_RELEVANCE_DEFAULT_VALUE,
  resolveTempIds,
} from 'helpers'

import {
  buildDeleteConditionOperation,
  buildSaveConditionsOperation,
  buildScenarioPayload,
  getConditionTypeInfo,
  getDefaultCondition,
  hasConditionChanged,
  isDuplicate,
  isDuplicateCondition,
  showSuccessMessage,
  syncSurveyQuestionWithScenarios,
  updateQuestionScenarios,
} from '../components/ConditionDesigner/utils'

const conditionTypeInfo = getConditionTypeInfo()

// An answer options condition can hold several comma separated answers: Each
// one is saved as its own condition. The condition keeps its ID for the first
// answer, the other answers become new conditions: One ID can only hold one
// answer. Other values (constants, regular expressions) may contain commas.
const splitConditionValues = (conditions) =>
  conditions.flatMap(({ value, ...rest }) => {
    if (rest.targetType !== conditionTypeInfo.TARGET.ANSWER_OPTIONS) {
      return [{ ...rest, value }]
    }

    return value
      .toString()
      .split(',')
      .map((v, index) => {
        if (index === 0) return { ...rest, value: v }

        return { ...omit(rest, 'cid'), value: v, isUpdated: false }
      })
  })

// Removes the duplicates within the new conditions of the form.
const uniqueConditions = (conditions) =>
  conditions.filter(
    (condition, index, self) =>
      index === self.findIndex((other) => isDuplicate(condition, other))
  )

export const useConditionDesigner = ({
  survey,
  question,
  focused,
  scid,
  scenarioToPatch,
  groupIndex,
  questionIndex,
  conditions,
  addToBuffer,
  setScid,
  setConditions,
  update,
  onNavigateBack,
}) => {
  const originalScenarios = question.scenarios
  const originalScid = useRef(scenarioToPatch ? scid : null)

  // When editing a scenario, flag the conditions that differ from the saved
  // ones. The question may have the real IDs already, the form temp IDs.
  const withUpdatedFlag = (condition) =>
    scenarioToPatch
      ? {
          ...condition,
          isUpdated: hasConditionChanged(
            resolveTempIds(condition),
            scenarioToPatch,
            question
          ),
        }
      : condition

  const syncSurveyQuestion = () =>
    syncSurveyQuestionWithScenarios(
      survey,
      question,
      groupIndex,
      questionIndex,
      update,
      focused
    )

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
    const sorted = [...conditions].sort(
      (a, b) => Number(a.cqid) - Number(b.cqid)
    )
    setConditions([
      ...sorted,
      {
        ...getDefaultCondition(),
        scenario: scid,
      },
    ])
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

  /**
   * Deletes conditions of the edited scenario: Buffers a delete per condition
   * and removes them from the question.
   */
  const deleteConditions = (deletedConditions) => {
    deletedConditions.forEach((condition) =>
      addToBuffer(
        buildDeleteConditionOperation(
          condition.qid,
          scenarioToPatch.scid,
          condition.cid
        )
      )
    )

    question.scenarios = question.scenarios
      .map((scenario) =>
        scenario.scid === scenarioToPatch.scid
          ? {
              ...scenario,
              conditions: scenario.conditions.filter(
                (c) => !deletedConditions.some(({ cid }) => cid == c.cid)
              ),
            }
          : scenario
      )
      .filter((scenario) => scenario.conditions.length > 0)

    if (question.scenarios.length === 0) {
      question.relevance = QUESTION_RELEVANCE_DEFAULT_VALUE
    }

    syncSurveyQuestion()
    focused.scenarios = question.scenarios
    focused.relevance = QUESTION_RELEVANCE_DEFAULT_VALUE
  }

  /**
   * Builds the API scenarios of inserted or updated conditions and applies
   * them to the question. Returns the scenarios to send.
   */
  const applyConditions = (conditionsToSave) => {
    const { scenarios } = buildScenarioPayload(question, conditionsToSave, scid)
    updateQuestionScenarios(scenarios, question, originalScid)
    syncSurveyQuestion()
    return scenarios
  }

  const handleSavingScenario = () => {
    try {
      // The form may still hold temp IDs that were saved while it was open.
      const formConditions = resolveTempIds(conditions)
      const deletedConditions = formConditions.filter((c) => c.isDeleted)
      const keptConditions = splitConditionValues(
        formConditions.filter((c) => !c.isDeleted)
      )
      const savedScenarios = []
      let message = ''

      if (deletedConditions.length) {
        deleteConditions(deletedConditions)
        message = t('Condition deleted successfully')
      }

      const updatedConditions = keptConditions.filter((c) => c.isUpdated)
      if (updatedConditions.length) {
        savedScenarios.push(...applyConditions(updatedConditions))
        message = t('Condition applied successfully')
      }

      // Checked after the updates were applied: An edited condition can turn
      // a new one into a duplicate.
      const newConditions = keptConditions.filter((c) => !c.cid)
      const notYetSavedConditions = newConditions.filter(
        (c) => !isDuplicateCondition(c, question, scid, scenarioToPatch)
      )
      const hasDuplicate = notYetSavedConditions.length !== newConditions.length
      const insertedConditions = uniqueConditions(notYetSavedConditions)

      if (insertedConditions.length) {
        savedScenarios.push(...applyConditions(insertedConditions))
        message = t('Condition applied successfully')
      }

      if (savedScenarios.length) {
        addToBuffer(buildSaveConditionsOperation(question.qid, savedScenarios))
      }

      const hasChanges =
        deletedConditions.length ||
        updatedConditions.length ||
        insertedConditions.length

      if (!hasChanges && hasDuplicate) {
        errorToast(t('Condition already exists'), 'center')
      } else {
        showSuccessMessage(message)
      }

      onNavigateBack()
    } catch (error) {
      question.scenarios = originalScenarios
      focused.scenarios = originalScenarios
      throw new Error(`Scenario save failed: ${error.message}`)
    }
  }

  return {
    addCondition,
    updateCondition,
    removeCondition,
    handleScenarioNameChange,
    handleSavingScenario,
  }
}
