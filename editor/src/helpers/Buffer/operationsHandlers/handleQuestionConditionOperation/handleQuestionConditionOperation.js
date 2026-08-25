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

/**
 * Remove a specific condition (by cid) from all buffered questionCondition
 * create/update operations for the given question (qid). Cleans up empty
 * scenarios and removes the whole buffer entry if it becomes empty.
 */
const removeConditionFromBuffer = (bufferOperations, qid, cid) => {
  return bufferOperations.reduce((acc, op) => {
    if (op.entity !== Entities.questionCondition || !op.props?.scenarios) {
      acc.push(op)
      return acc
    }

    // Only touch ops that belong to the same question
    if (op.props.qid != qid) {
      acc.push(op)
      return acc
    }

    const updatedScenarios = op.props.scenarios
      .map((scenario) => {
        if (!scenario.conditions) return scenario
        return {
          ...scenario,
          conditions: scenario.conditions.filter((c) => c.cid != cid),
        }
      })
      .filter(
        (scenario) => !scenario.conditions || scenario.conditions.length > 0
      )

    // Drop the whole buffer entry if no scenarios remain
    if (updatedScenarios.length > 0) {
      acc.push({ ...op, props: { ...op.props, scenarios: updatedScenarios } })
    }

    return acc
  }, [])
}

export const handleQuestionConditionOperation = (
  _bufferOperations,
  _operation,
  _currentOperation
) => {
  if (!_operation) {
    return {
      bufferOperations: _bufferOperations,
      newOperation: {},
      addToBuffer: false,
    }
  }

  let bufferOperations = cloneDeep(_bufferOperations)
  let currentOperation = cloneDeep(_currentOperation)
  const operation = cloneDeep(_operation) || {}

  const conditionHasATempId = hasTempId(operation.id)

  if (operation.op === Operations.delete) {
    const qid = operation.props?.qid
    const cid = operation.id

    // Strip the deleted condition from any buffered create/update ops
    bufferOperations = removeConditionFromBuffer(bufferOperations, qid, cid)

    // Also remove full-match ops (scenario-level deletes where id === qid)
    let updatedBufferOperations = bufferOperations.filter((op) => {
      return !(
        op.id === operation.id && op.entity === Entities.questionCondition
      )
    })

    return {
      bufferOperations: updatedBufferOperations,
      newOperation: conditionHasATempId ? {} : operation,
      addToBuffer: !conditionHasATempId,
    }
  }

  const isConditionScriptUpdate =
    operation.op === Operations.update &&
    operation.props?.action === 'conditionScript'

  // When applying an expression script, remove all existing condition
  // operations (create / update) for the same question from the buffer.
  // The expression script replaces every scenario, so those buffered
  // operations are no longer valid.
  if (isConditionScriptUpdate) {
    bufferOperations = bufferOperations.filter((op) => {
      return !(
        op.id === operation.id && op.entity === Entities.questionCondition
      )
    })

    // When a builder condition (create or scenario-based update) is added and
    // the buffer already contains an expression script update for the same
    // question, the expression script is no longer valid — remove it.
    const currentIsConditionScript =
      currentOperation?.op === Operations.update &&
      currentOperation?.props?.action === 'conditionScript'

    if (currentIsConditionScript) {
      bufferOperations = bufferOperations.filter((op) => {
        return !(
          op.id === operation.id && op.entity === Entities.questionCondition
        )
      })

      return {
        bufferOperations,
        newOperation: operation,
        addToBuffer: true,
      }
    }

    // When updating a condition but no currentOperation was found (e.g. the
    // buffer only has a CREATE op for the same qid), merge the updated
    // condition into that existing create op instead of adding a duplicate.
    if (
      !currentOperation &&
      operation.op === Operations.update &&
      operation.props?.scenarios
    ) {
      const existingCreateIndex = bufferOperations.findIndex(
        (op) =>
          op.id == operation.id &&
          op.entity === Entities.questionCondition &&
          op.op === Operations.create
      )

      if (existingCreateIndex !== -1) {
        const existingCreate = bufferOperations[existingCreateIndex]
        const mergedProps = mergeConditionProps(
          existingCreate.props,
          operation.props
        )
        bufferOperations[existingCreateIndex] = {
          ...existingCreate,
          props: mergedProps,
        }

        return {
          bufferOperations,
          newOperation: {},
          addToBuffer: false,
        }
      }
    }
  }

  // Handle create or update operation
  const newOperation = currentOperation
    ? {
        ...currentOperation,
        props: mergeConditionProps(currentOperation.props, operation.props),
      }
    : operation

  return {
    bufferOperations,
    newOperation,
    addToBuffer: !currentOperation,
  }
}
