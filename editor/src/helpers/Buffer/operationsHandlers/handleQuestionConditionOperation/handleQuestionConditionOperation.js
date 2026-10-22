import { cloneDeep } from 'lodash'

import { Entities, hasTempId, Operations } from 'helpers'

/**
 * Merge scenario arrays by combining conditions under matching scids
 * instead of overwriting by array index (which lodash merge does).
 */
const mergeScenarios = (existing = [], incoming = []) => {
  const merged = [...existing]

  incoming.forEach((incomingScenario) => {
    const match = merged.find((s) => s.scid == incomingScenario.scid)

    if (match) {
      // Merge conditions: keep existing ones that are not in incoming, then add incoming
      const existingConditions = match.conditions || []
      const incomingConditions = incomingScenario.conditions || []

      match.conditions = [
        ...existingConditions.filter(
          (c) => !incomingConditions.some((nc) => nc.cid == c.cid)
        ),
        ...incomingConditions,
      ]

      // Merge any other scenario-level props (e.g. action)
      Object.keys(incomingScenario).forEach((key) => {
        if (key !== 'conditions') {
          match[key] = incomingScenario[key]
        }
      })
    } else {
      merged.push(incomingScenario)
    }
  })

  return merged
}

const mergeConditionProps = (currentProps, newProps) => {
  return {
    ...currentProps,
    ...newProps,
    scenarios: mergeScenarios(currentProps?.scenarios, newProps?.scenarios),
  }
}

const getOperationQid = (op) => op?.props?.qid ?? op?.qid

const isConditionOperationOfQuestion = (op, qid) =>
  op?.entity === Entities.questionCondition &&
  String(getOperationQid(op)) === String(qid)

const isConditionScriptOperation = (op) =>
  op?.op === Operations.update && op?.props?.action === 'conditionScript'

// create/update operations carrying scenarios, which can be merged together.
const isScenarioChangeOperation = (op) =>
  (op?.op === Operations.create || op?.op === Operations.update) &&
  !isConditionScriptOperation(op)

// A scenario-level delete lists scenarios without conditions.
const getDeletedScenarioIds = (operation) =>
  (operation.props?.scenarios ?? [])
    .filter((scenario) => !scenario.conditions)
    .map((scenario) => String(scenario.scid))

/**
 * Applies `updateScenarios` to every buffered create/update operation of the
 * question, dropping the operations left without scenarios.
 */
const updateBufferedScenarios = (bufferOperations, qid, updateScenarios) =>
  bufferOperations.reduce((acc, op) => {
    if (
      !isConditionOperationOfQuestion(op, qid) ||
      !isScenarioChangeOperation(op) ||
      !op.props?.scenarios
    ) {
      acc.push(op)
      return acc
    }

    const scenarios = updateScenarios(op.props.scenarios)
    if (scenarios.length > 0) {
      acc.push({ ...op, props: { ...op.props, scenarios } })
    }

    return acc
  }, [])

const removeConditionFromBuffer = (bufferOperations, qid, cid) =>
  updateBufferedScenarios(bufferOperations, qid, (scenarios) =>
    scenarios
      .map((scenario) =>
        scenario.conditions
          ? {
              ...scenario,
              conditions: scenario.conditions.filter((c) => c.cid != cid),
            }
          : scenario
      )
      .filter((scenario) => !scenario.conditions || scenario.conditions.length)
  )

const removeScenariosFromBuffer = (bufferOperations, qid, scids) =>
  updateBufferedScenarios(bufferOperations, qid, (scenarios) =>
    scenarios.filter((scenario) => !scids.includes(String(scenario.scid)))
  )

/**
 * The server applies the condition operations of a question in the order they
 * are sent, and scenario/script/scenario-delete operations all share the same
 * buffer key (id = qid), so the generic (id, op, entity) lookup can't be used:
 * - an expression script replaces every condition, so it drops all the
 *   buffered condition operations of the question;
 * - a scenario change is merged only into the latest operation of the question
 *   when it's a compatible create/update, never across a script or a delete;
 * - deletes strip the deleted scenario/condition from the buffered operations.
 */
export const handleQuestionConditionOperation = (
  _bufferOperations,
  _operation
) => {
  if (!_operation) {
    return {
      bufferOperations: _bufferOperations,
      newOperation: {},
      addToBuffer: false,
    }
  }

  let bufferOperations = cloneDeep(_bufferOperations)
  const operation = cloneDeep(_operation)
  const qid = getOperationQid(operation)

  if (operation.op === Operations.delete) {
    const deletedScenarioIds = getDeletedScenarioIds(operation)
    const isScenarioDelete = deletedScenarioIds.length > 0

    bufferOperations = isScenarioDelete
      ? removeScenariosFromBuffer(bufferOperations, qid, deletedScenarioIds)
      : removeConditionFromBuffer(bufferOperations, qid, operation.id)

    // Nothing to delete on the server when the condition, or the question
    // owning the scenario, only exists locally. Queuing such a delete would
    // also block the buffer: deletes are sent first, but this one could never
    // be sent before the question is created.
    const isLocalOnly = isScenarioDelete
      ? hasTempId(String(qid))
      : hasTempId(String(operation.id))

    return {
      bufferOperations,
      newOperation: isLocalOnly ? {} : operation,
      addToBuffer: !isLocalOnly,
    }
  }

  if (isConditionScriptOperation(operation)) {
    return {
      bufferOperations: bufferOperations.filter(
        (op) => !isConditionOperationOfQuestion(op, qid)
      ),
      newOperation: operation,
      addToBuffer: true,
    }
  }

  let lastIndex = -1
  bufferOperations.forEach((op, index) => {
    if (isConditionOperationOfQuestion(op, qid)) lastIndex = index
  })
  const lastOperation = bufferOperations[lastIndex]

  if (
    lastOperation &&
    lastOperation.op === operation.op &&
    isScenarioChangeOperation(lastOperation)
  ) {
    bufferOperations[lastIndex] = {
      ...lastOperation,
      props: mergeConditionProps(lastOperation.props, operation.props),
    }

    return { bufferOperations, newOperation: {}, addToBuffer: false }
  }

  return { bufferOperations, newOperation: operation, addToBuffer: true }
}
