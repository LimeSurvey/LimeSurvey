import { useRef } from 'react'

import {
  createBufferOperation,
  errorToast,
  QUESTION_RELEVANCE_DEFAULT_VALUE,
} from 'helpers'

import {
  buildScenarioPayload,
  getApiOperationActions,
  getCurrentQuestionInfo,
  getDefaultCondition,
  hasConditionChanged,
  isDuplicateCondition,
  showConditionChangedWarning,
  showSuccessMessage,
  updateQuestionScenarios,
  isDuplicate,
  replaceQuestionInSurvey,
} from '../components/ConditionDesigner/utils'

export const useConditionDesigner = ({
  question,
  scid,
  scenarioToPatch,
  conditions,
  addToBuffer,
  setScid,
  setConditions,
  update,
  setPendingScenarioName,
  onNavigateBack,
}) => {
  const originalScid = useRef(scenarioToPatch ? scid : null)

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
      prev.map((cond, i) => {
        if (i !== index) return cond
        const updated = { ...cond, [key]: value }

        if (scenarioToPatch) {
          updated.isUpdated = hasConditionChanged(
            updated,
            scenarioToPatch,
            question
          )
        }

        return updated
      })
    )
  }

  /**
   * Removes the draft's deleted conditions from the working question.
   * @param {Array} deletedConditions Rows marked for deletion.
   * @param {Object} targetQuestion The working copy of the question.
   * @returns {Array} The delete operations to queue.
   */
  const applyDeletedConditions = (deletedConditions, targetQuestion) =>
    deletedConditions.map((condition) => {
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

      // New scenario objects: the scenarios can still be the cached ones
      targetQuestion.scenarios = (targetQuestion.scenarios ?? [])
        .map((sc) =>
          sc.scid === scenarioToPatch.scid
            ? {
                ...sc,
                conditions: sc.conditions.filter((c) => c.cid != condition.cid),
              }
            : sc
        )
        .filter((sc) => sc.conditions.length > 0)

      if (targetQuestion.scenarios.length === 0) {
        targetQuestion.relevance = QUESTION_RELEVANCE_DEFAULT_VALUE
      }

      return operation
    })

  const handleSavingScenario = () => {
    // Save against the question as it is in the survey now. This can run from
    // a confirmation popup, and the question captured when it opened can be
    // outdated (e.g. a refresh added a scenario, which the duplicate check
    // must see) or gone (e.g. its group was deleted: nothing to save).
    const currentQuestion = getCurrentQuestionInfo(question.qid).question
    if (!currentQuestion) return
    // The edited scenario is gone, e.g. a refresh replaced the scenarios with
    // an ExpressionScript condition: saving the draft (incl. its deleted rows)
    // would overwrite that change
    const isEditedScenarioGone =
      scenarioToPatch &&
      !(currentQuestion.scenarios ?? []).some(
        (scenario) => scenario.scid === scenarioToPatch.scid
      )
    if (isEditedScenarioGone) {
      showConditionChangedWarning()
      return
    }

    // Prepare all changes first, without side effects: on a copy of the
    // question, with operations collected instead of queued, and without
    // changing the draft. They are applied only when every step succeeded,
    // so a failed save leaves the queue, the survey and the draft unchanged,
    // and can be retried.
    const workingQuestion = { ...currentQuestion }
    const operations = []
    const originalScidBeforeSave = originalScid.current
    let message = ''
    let scenarioHasUpdates = false
    let hasCreatedConditions = false
    let hasDuplicate = false

    try {
      const deletedConditions = conditions.filter((c) => c.isDeleted)
      // One row per value
      const savedConditions = conditions
        .filter((c) => !c.isDeleted)
        .flatMap(({ value, ...rest }) =>
          value
            .toString()
            .split(',')
            .map((v) => ({ ...rest, value: v }))
        )

      if (deletedConditions.length) {
        operations.push(
          ...applyDeletedConditions(deletedConditions, workingQuestion)
        )
        message = getSuccessMessage('delete')
        scenarioHasUpdates = true
      }

      const apiProps = { qid: workingQuestion.qid, scenarios: [] }
      const updated = savedConditions.filter((c) => c.isUpdated)

      if (updated.length) {
        const updateProps = buildScenarioPayload(workingQuestion, updated, scid)
        apiProps.scenarios.push(...updateProps.scenarios)
        updateQuestionScenarios(
          updateProps.scenarios,
          workingQuestion,
          originalScid
        )
        message = getSuccessMessage('update')
        scenarioHasUpdates = true
      }

      const newConditions = savedConditions.filter(
        (c) =>
          !c.cid &&
          !isDuplicateCondition(c, workingQuestion, scid, scenarioToPatch)
      )

      hasDuplicate =
        savedConditions.filter((c) => !c.cid).length !== newConditions.length

      if (newConditions.length) {
        // Filter out duplicates
        const uniqueConditions = newConditions.filter(
          (cond, index, self) =>
            index === self.findIndex((c) => isDuplicate(cond, c))
        )

        if (uniqueConditions.length) {
          const createProps = buildScenarioPayload(
            workingQuestion,
            uniqueConditions,
            scid
          )
          apiProps.scenarios.push(...createProps.scenarios)
          updateQuestionScenarios(
            createProps.scenarios,
            workingQuestion,
            originalScid
          )
          message = getSuccessMessage('create')
          hasCreatedConditions = true
          scenarioHasUpdates = true
        }
      }

      if (apiProps.scenarios.length > 0) {
        const op = createBufferOperation(workingQuestion.qid)
          .questionCondition()
          .update()
        op.qid = workingQuestion.qid
        op.props = apiProps
        operations.push(op)
      }
    } catch (error) {
      // Nothing was applied. Restore the rename tracking that
      // updateQuestionScenarios advanced, so a retry still renames.
      originalScid.current = originalScidBeforeSave
      throw new Error(`Scenario save failed: ${error.message}`)
    }

    // Every step succeeded: apply the changes
    operations.forEach((operation) => addToBuffer(operation))
    if (operations.length) {
      replaceQuestionInSurvey(workingQuestion, update)
    }
    if (hasCreatedConditions) {
      setPendingScenarioName(scid)
    }

    if (!scenarioHasUpdates && hasDuplicate) {
      errorToast(t('Condition already exists'), 'center')
      onNavigateBack()
      return
    }

    showSuccessMessage(message)
    onNavigateBack()
  }

  const getSuccessMessage = (action) => {
    return action === 'delete'
      ? t('Condition deleted successfully')
      : t('Condition applied successfully')
  }

  const handleScenarioNameChange = (e) => {
    // Keep a cleared input empty: Number('') would turn it into scenario 0.
    // An empty scenario number is invalid, so Apply stays disabled.
    const newScid = e.target.value === '' ? '' : Number(e.target.value)
    if (isNaN(newScid)) return

    setScid(newScid)

    const updateConditionScenario = (cond) => {
      const updatedCondition = { ...cond, scenario: newScid }

      return {
        ...updatedCondition,
        ...(scenarioToPatch && {
          isUpdated: hasConditionChanged(
            updatedCondition,
            scenarioToPatch,
            question
          ),
        }),
      }
    }

    setConditions((prev) => prev.map(updateConditionScenario))
  }

  return {
    addCondition,
    updateCondition,
    removeCondition,
    handleScenarioNameChange,
    handleSavingScenario,
  }
}
