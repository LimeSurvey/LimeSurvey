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
  syncSurveyQuestionWithScenarios,
  isDuplicate,
  replaceQuestionInSurvey,
} from '../components/ConditionDesigner/utils'

export const useConditionDesigner = ({
  question,
  focused,
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

  const handleQuestionUpdate = (scenarios, targetQuestion) => {
    updateQuestionScenarios(scenarios, targetQuestion, originalScid)
    syncSurveyQuestionWithScenarios(targetQuestion, update, focused)
  }

  const handleDeletedConditions = (deletedConditions, targetQuestion) => {
    Object.values(deletedConditions).forEach((condition) => {
      const props = {
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

      const operation = createBufferOperation(condition.cid)
        .questionCondition()
        .delete()

      operation.qid = condition.qid
      operation.props = props
      addToBuffer(operation)

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

      syncSurveyQuestionWithScenarios(targetQuestion, update, focused)
      focused.scenarios = targetQuestion.scenarios
      focused.relevance = QUESTION_RELEVANCE_DEFAULT_VALUE
    })
  }

  const handleSavingScenario = () => {
    // Save against the question as it is in the survey now. This can run from
    // a confirmation popup, and the question captured when it opened can be
    // outdated (e.g. a refresh added a scenario, which the duplicate check
    // must see) or gone (e.g. its group was deleted: nothing to save).
    // Work on a copy, so the cached survey only changes through `update`.
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
    const workingQuestion = { ...currentQuestion }

    try {
      const deletedConditions = conditions.filter((c) => c.isDeleted)
      let message = ''
      let scenarioHasUpdates = false

      conditions.splice(
        0,
        conditions.length,
        ...conditions
          .filter((c) => !c.isDeleted)
          .flatMap(({ value, ...rest }) =>
            value
              .toString()
              .split(',')
              .map((v) => ({ ...rest, value: v }))
          )
      )

      if (deletedConditions.length) {
        handleDeletedConditions(deletedConditions, workingQuestion)
        message = getSuccessMessage('delete')
        scenarioHasUpdates = true
      }

      const apiProps = { qid: workingQuestion.qid, scenarios: [] }
      const updated = conditions.filter((c) => c.isUpdated)

      if (updated.length) {
        const updateProps = buildScenarioPayload(workingQuestion, updated, scid)
        apiProps.scenarios.push(...updateProps.scenarios)
        handleQuestionUpdate(updateProps.scenarios, workingQuestion)
        message = getSuccessMessage('update')
        scenarioHasUpdates = true
      }

      const newConditions = conditions.filter(
        (c) =>
          !c.cid &&
          !isDuplicateCondition(c, workingQuestion, scid, scenarioToPatch)
      )

      const hasDuplicate =
        conditions.filter((c) => !c.cid).length !== newConditions.length

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
          handleQuestionUpdate(createProps.scenarios, workingQuestion)
          message = getSuccessMessage('create')
          setPendingScenarioName(scid)
          scenarioHasUpdates = true
        }
      }

      if (apiProps.scenarios.length > 0) {
        const op = createBufferOperation(workingQuestion.qid)
          .questionCondition()
          .update()
        op.qid = workingQuestion.qid
        op.props = apiProps
        addToBuffer(op)
      }

      if (!scenarioHasUpdates && hasDuplicate) {
        errorToast(t('Condition already exists'), 'center')
        onNavigateBack()
        return
      }

      showSuccessMessage(message)
      onNavigateBack()
    } catch (error) {
      // Write the question back as it was before saving
      replaceQuestionInSurvey(currentQuestion, update)
      focused.scenarios = currentQuestion.scenarios
      throw new Error(`Scenario save failed: ${error.message}`)
    }
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
