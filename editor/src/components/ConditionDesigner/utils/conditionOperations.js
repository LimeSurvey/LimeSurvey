/**
 * Builds the buffer operations of the condition designer.
 * The backend handler is OpHandlerQuestionCondition (SurveyPatch).
 */
import { createBufferOperation, Entities, isTempId, Operations } from 'helpers'

import { getApiOperationActions } from './getApiOperationActions'

const actions = getApiOperationActions()

const buildOperation = (id, op, qid, props) => {
  const operation =
    op === Operations.delete
      ? createBufferOperation(id).questionCondition().delete()
      : createBufferOperation(id).questionCondition().update()

  operation.qid = qid
  operation.props = props

  return operation
}

/**
 * Saves (inserts and updates) the conditions of a question.
 * The scenarios come from buildScenarioPayload.
 */
export const buildSaveConditionsOperation = (qid, scenarios) =>
  buildOperation(qid, Operations.update, qid, { qid, scenarios })

/** Deletes one condition of a scenario. */
export const buildDeleteConditionOperation = (qid, scid, cid) =>
  buildOperation(cid, Operations.delete, qid, {
    qid,
    scenarios: [
      { scid, conditions: [{ cid, action: actions.CONDITION.DELETE }] },
    ],
  })

/** Deletes a whole scenario with all its conditions. */
export const buildDeleteScenarioOperation = (qid, scid) =>
  buildOperation(qid, Operations.delete, qid, {
    qid,
    scenarios: [{ scid, action: actions.SCENARIO.DELETE }],
  })

/**
 * Builds the operations that delete a scenario of a question.
 *
 * While saved conditions of the question have pending updates, e.g. moves to
 * another scenario number, the server still has them under their old number,
 * and deletes are sent before updates. A delete by scenario number would then
 * miss a condition moved into the scenario, and delete one moved out of it.
 * The scenario is then deleted condition by condition, as the editor shows it.
 *
 * @param {number|string} qid The question ID.
 * @param {object} scenario The scenario, with its conditions as the editor shows them.
 * @param {Array} bufferOperations The buffered operations.
 * @returns {Array} The delete operations.
 */
export const buildDeleteScenarioOperations = (
  qid,
  scenario,
  bufferOperations
) => {
  const hasPendingUpdates = bufferOperations.some(
    (operation) =>
      operation.entity === Entities.questionCondition &&
      operation.op === Operations.update &&
      (operation.qid ?? operation.props?.qid) == qid &&
      operation.props?.scenarios?.some((item) =>
        item.conditions?.some(
          (condition) =>
            condition.action === actions.CONDITION.UPDATE &&
            !isTempId(condition.cid)
        )
      )
  )

  if (!hasPendingUpdates) {
    return [buildDeleteScenarioOperation(qid, scenario.scid)]
  }

  return (scenario.conditions ?? []).map((condition) =>
    buildDeleteConditionOperation(qid, scenario.scid, condition.cid)
  )
}

/** Replaces the conditions of a question by an ExpressionScript. */
export const buildConditionScriptOperation = (qid, script) =>
  buildOperation(qid, Operations.update, qid, {
    qid,
    action: actions.CONDITION_SCRIPT.UPDATE,
    script,
  })
