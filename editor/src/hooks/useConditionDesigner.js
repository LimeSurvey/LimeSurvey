import { useRef } from 'react'

import {
  createBufferOperation,
  errorToast,
  QUESTION_RELEVANCE_DEFAULT_VALUE,
} from 'helpers'

import {
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
  setPendingScenarioName,
  onNavigateBack,
}) => {
  const originalScenarios = question.scenarios
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

  const handleQuestionUpdate = (scenarios) => {
    updateQuestionScenarios(scenarios, question, originalScid)
    syncSurveyQuestionWithScenarios(
      survey,
      question,
      groupIndex,
      questionIndex,
      update,
      focused
    )
  }

  const handleDeletedConditions = (deletedConditions) => {
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

      syncSurveyQuestionWithScenarios(
        survey,
        question,
        groupIndex,
        questionIndex,
        update,
        focused
      )
      focused.scenarios = question.scenarios
      focused.relevance = QUESTION_RELEVANCE_DEFAULT_VALUE
    })
  }

  const handleSavingScenario = () => {
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
        handleDeletedConditions(deletedConditions)
        message = getSuccessMessage('delete')
        scenarioHasUpdates = true
      }

      const apiProps = { qid: question.qid, scenarios: [] }
      const updated = conditions.filter((c) => c.isUpdated)

      if (updated.length) {
        const updateProps = buildScenarioPayload(question, updated, scid)
        apiProps.scenarios.push(...updateProps.scenarios)
        handleQuestionUpdate(updateProps.scenarios)
        message = getSuccessMessage('update')
        scenarioHasUpdates = true
      }

      const newConditions = conditions.filter(
        (c) =>
          !c.cid && !isDuplicateCondition(c, question, scid, scenarioToPatch)
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
            question,
            uniqueConditions,
            scid
          )
          apiProps.scenarios.push(...createProps.scenarios)
          handleQuestionUpdate(createProps.scenarios)
          message = getSuccessMessage('create')
          setPendingScenarioName(scid)
          scenarioHasUpdates = true
        }
      }

      if (apiProps.scenarios.length > 0) {
        const op = createBufferOperation(question.qid)
          .questionCondition()
          .update()
        op.qid = question.qid
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
      question.scenarios = originalScenarios
      focused.scenarios = originalScenarios
      throw new Error(`Scenario save failed: ${error.message}`)
    }
  }

  const getSuccessMessage = (action) => {
    return action === 'delete'
      ? t('Condition deleted successfully')
      : t('Condition applied successfully')
  }

  const handleScenarioNameChange = (e) => {
    const newScid = Number(e.target.value)
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
