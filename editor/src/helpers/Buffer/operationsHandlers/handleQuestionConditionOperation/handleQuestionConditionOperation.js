import { cloneDeep } from 'lodash'

import {
  Entities,
  hasTempId,
  isConditionTempId,
  Operations,
  TEMP_ID_TOKEN_REGEX,
} from 'helpers'

// The condition actions of the API (see OpHandlerQuestionCondition.php).
export const CONDITION_ACTIONS = {
  CREATE: 'insertCondition',
  UPDATE: 'updateCondition',
  DELETE: 'deleteCondition',
  SCRIPT: 'conditionScript',
  DELETE_SCENARIO: 'deleteScenario',
  DELETE_ALL: 'deleteAllConditions',
}

/**
 * A condition still waiting to be created must stay a create when it is edited
 * again before being sent: The edit arrives as an update of its temp cid, which
 * the server can't apply to a condition it doesn't have yet.
 */
const keepPendingCreate = (createCondition, editCondition) => ({
  ...editCondition,
  action: createCondition.action,
  tempId: createCondition.tempId,
  tempcids: createCondition.tempcids,
  cid: createCondition.cid,
})

// A condition create (insertCondition, carrying its temp ID).
const isPendingCreate = (condition) =>
  Boolean(condition?.tempId) || condition?.action === CONDITION_ACTIONS.CREATE

// An edit (updateCondition) of a condition that only has a temp cid, i.e. whose
// create was not answered by the server yet.
const isEditOfTempCondition = (condition) =>
  !isPendingCreate(condition) && hasTempId(String(condition?.cid ?? ''))

// Condition fields that can embed a question or subquestion temp ID (e.g. the
// cfieldname "Q12_Stemp__34" of an unsaved subquestion).
const SOURCE_FIELDS = ['cfieldname', 'cquestions', 'prevQuestionSGQA']

const getOperationConditions = (op) =>
  (op?.props?.scenarios ?? []).flatMap((scenario) => scenario.conditions ?? [])

/**
 * Whether the condition operation can't be sent yet:
 * - it failed (error), or its own ID is still a temp ID;
 * - it references a question (qid/cqid) that still has a temp ID;
 * - it edits a condition whose create is being sent (temp cid): The server
 *   can't update a condition by its temp ID;
 * - a condition field embeds a temp ID: The server stores these fields as is.
 */
export const isConditionOperationWaiting = (op) => {
  if (op?.entity !== Entities.questionCondition) return false
  if (op.error) return true
  if (op.op !== Operations.create && hasTempId(String(op.id ?? ''))) {
    return true
  }
  if (op.props && (hasTempId(op.props, 'qid') || hasTempId(op.props, 'cqid'))) {
    return true
  }

  return getOperationConditions(op).some(
    (condition) =>
      isEditOfTempCondition(condition) ||
      SOURCE_FIELDS.some((key) => hasTempId(String(condition[key] ?? '')))
  )
}

/**
 * Combines two versions of the same condition (same cid), `incoming` being the
 * newest one. When one of them is a pending create, the result stays a create.
 */
const mergeCondition = (existing, incoming) => {
  if (!existing) return incoming
  if (isPendingCreate(existing) && !isPendingCreate(incoming)) {
    return keepPendingCreate(existing, incoming)
  }
  // An older create restored after a failed request, merged into a newer edit.
  if (isPendingCreate(incoming) && !isPendingCreate(existing)) {
    return keepPendingCreate(incoming, existing)
  }
  return incoming
}

// Drops the scenarios a condition removal left without conditions. Scenario
// deletes (no conditions list at all) are kept.
const withoutEmptyScenarios = (scenarios) =>
  scenarios.filter(
    (scenario) => !scenario.conditions || scenario.conditions.length
  )

/**
 * Merge scenario arrays by combining conditions under matching scids
 * instead of overwriting by array index (which lodash merge does).
 * A condition moved to another scenario is removed from its previous one.
 */
const mergeScenarios = (existing = [], incoming = []) => {
  const incomingConditions = incoming.flatMap((s) => s.conditions ?? [])
  const findExisting = (cid) =>
    existing.flatMap((s) => s.conditions ?? []).find((c) => c.cid == cid)

  const merged = existing.map((scenario) =>
    scenario.conditions
      ? {
          ...scenario,
          conditions: scenario.conditions.filter(
            (c) => !incomingConditions.some((nc) => nc.cid == c.cid)
          ),
        }
      : scenario
  )

  incoming.forEach((incomingScenario) => {
    const conditions = (incomingScenario.conditions ?? []).map((condition) =>
      mergeCondition(findExisting(condition.cid), condition)
    )
    const match = merged.find((s) => s.scid == incomingScenario.scid)

    if (match) {
      // Merge any other scenario-level props (e.g. action)
      Object.keys(incomingScenario).forEach((key) => {
        if (key !== 'conditions') {
          match[key] = incomingScenario[key]
        }
      })
      if (incomingScenario.conditions) {
        match.conditions = [...(match.conditions ?? []), ...conditions]
      }
    } else {
      merged.push(
        incomingScenario.conditions
          ? { ...incomingScenario, conditions }
          : incomingScenario
      )
    }
  })

  return withoutEmptyScenarios(merged)
}

const mergeConditionProps = (currentProps, newProps) => {
  return {
    ...currentProps,
    ...newProps,
    scenarios: mergeScenarios(currentProps?.scenarios, newProps?.scenarios),
  }
}

const getConditionCids = (op) =>
  getOperationConditions(op)
    .filter((condition) => condition.cid != null)
    .map((condition) => condition.cid)

const getOperationQid = (op) => op?.props?.qid ?? op?.qid

const isConditionOperationOfQuestion = (op, qid) =>
  op?.entity === Entities.questionCondition &&
  String(getOperationQid(op)) === String(qid)

const isConditionScriptOperation = (op) =>
  op?.op === Operations.update && op?.props?.action === CONDITION_ACTIONS.SCRIPT

// create/update operations carrying scenarios, which can be merged together.
const isScenarioChangeOperation = (op) =>
  (op?.op === Operations.create || op?.op === Operations.update) &&
  !isConditionScriptOperation(op)

// A scenario-level delete lists scenarios without conditions.
const getDeletedScenarioIds = (operation) =>
  (operation.props?.scenarios ?? [])
    .filter((scenario) => !scenario.conditions)
    .map((scenario) => String(scenario.scid))

// Deletes every condition of the question on the server (its id is the qid).
const isDeleteAllConditionsOperation = (op) =>
  op?.op === Operations.delete &&
  op?.props?.action === CONDITION_ACTIONS.DELETE_ALL

// A condition-level delete: Its id is the cid.
const isConditionDeleteOperation = (op) =>
  op?.entity === Entities.questionCondition &&
  op?.op === Operations.delete &&
  !isDeleteAllConditionsOperation(op) &&
  getDeletedScenarioIds(op).length === 0

const buildDeleteAllConditionsOperation = (qid) => ({
  id: qid,
  qid,
  entity: Entities.questionCondition,
  op: Operations.delete,
  props: { qid, action: CONDITION_ACTIONS.DELETE_ALL },
})

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
    withoutEmptyScenarios(
      scenarios.map((scenario) =>
        scenario.conditions
          ? {
              ...scenario,
              conditions: scenario.conditions.filter((c) => c.cid != cid),
            }
          : scenario
      )
    )
  )

/**
 * Removes the conditions `cids` from the buffered operations of the question,
 * except from `keep`, which is left untouched (same object, so its new index
 * can be found again).
 */
const removeConditionsFromOtherOperations = (
  bufferOperations,
  qid,
  cids,
  keep
) =>
  bufferOperations.flatMap((op) =>
    op === keep
      ? [op]
      : cids.reduce(
          (ops, cid) => removeConditionFromBuffer(ops, qid, cid),
          [op]
        )
  )

/**
 * Where the pending create of the condition `cid` sits in the buffer.
 * @returns {{opIndex: number, scenarioIndex: number, conditionIndex: number}|null}
 */
const findPendingCreate = (bufferOperations, qid, cid) => {
  for (let opIndex = 0; opIndex < bufferOperations.length; opIndex++) {
    const op = bufferOperations[opIndex]
    if (
      !isConditionOperationOfQuestion(op, qid) ||
      !isScenarioChangeOperation(op)
    )
      continue

    const scenarios = op.props?.scenarios ?? []
    for (
      let scenarioIndex = 0;
      scenarioIndex < scenarios.length;
      scenarioIndex++
    ) {
      const conditionIndex = (
        scenarios[scenarioIndex].conditions ?? []
      ).findIndex((c) => c.cid == cid && isPendingCreate(c))
      if (conditionIndex !== -1)
        return { opIndex, scenarioIndex, conditionIndex }
    }
  }

  return null
}

/**
 * Replaces the pending create of the condition with its edited version, in
 * place (the create keeps its position in the buffer), moving it to the edited
 * scenario when the scenario number changed.
 * Mutates `bufferOperations`. Returns false when no pending create was found.
 */
const foldEditIntoPendingCreate = (bufferOperations, qid, edit, scid) => {
  const location = findPendingCreate(bufferOperations, qid, edit.cid)
  if (!location) return false

  const op = bufferOperations[location.opIndex]
  const scenarios = cloneDeep(op.props.scenarios)
  const [create] = scenarios[location.scenarioIndex].conditions.splice(
    location.conditionIndex,
    1
  )
  const updated = keepPendingCreate(create, edit)

  const target = scenarios.find((s) => s.scid == scid && s.conditions)
  if (target) {
    target.conditions.push(updated)
  } else {
    scenarios.push({ scid, conditions: [updated] })
  }

  bufferOperations[location.opIndex] = {
    ...op,
    props: { ...op.props, scenarios: withoutEmptyScenarios(scenarios) },
  }

  return true
}

/**
 * Edits of conditions whose create is still buffered are applied to that
 * create, wherever it is in the buffer: The server can't update a condition
 * it doesn't have yet. Returns the operation without these edits, or null
 * when nothing is left in it.
 */
const foldEditsIntoPendingCreates = (bufferOperations, qid, operation) => {
  const scenarios = (operation.props?.scenarios ?? []).map((scenario) =>
    scenario.conditions
      ? {
          ...scenario,
          conditions: scenario.conditions.filter(
            (condition) =>
              !isEditOfTempCondition(condition) ||
              !foldEditIntoPendingCreate(
                bufferOperations,
                qid,
                condition,
                scenario.scid
              )
          ),
        }
      : scenario
  )

  const remaining = withoutEmptyScenarios(scenarios)
  return remaining.length
    ? { ...operation, props: { ...operation.props, scenarios: remaining } }
    : null
}

/**
 * Whether the create of the condition is still waiting in the buffer, i.e.
 * the condition was never sent to the server. A buffered edit doesn't count:
 * the create of that condition may already be on its way to the server.
 */
export const isConditionInBuffer = (bufferOperations, qid, cid) =>
  findPendingCreate(bufferOperations, qid, cid) !== null

const removeScenariosFromBuffer = (bufferOperations, qid, scids) =>
  updateBufferedScenarios(bufferOperations, qid, (scenarios) =>
    scenarios.filter((scenario) => !scids.includes(String(scenario.scid)))
  )

/**
 * Removes from an operation put back after a failed request what the user
 * changed again since it was sent, as the restored values are older:
 * - the scenarios deleted since then (otherwise they would be created again
 *   on the server, after their delete);
 * - the conditions deleted since then;
 * - the edits of conditions edited again since then.
 * Restored creates edited since then are kept: The buffered edit (of their
 * temp cid) is applied to them when merged.
 * Returns null when nothing is left.
 */
const withoutSupersededChanges = (bufferOperations, qid, operation) => {
  const questionOperations = bufferOperations.filter((op) =>
    isConditionOperationOfQuestion(op, qid)
  )
  const deletes = questionOperations.filter((op) => op.op === Operations.delete)
  const deletedScids = deletes.flatMap(getDeletedScenarioIds)
  const deletedCids = deletes
    .filter(isConditionDeleteOperation)
    .map((op) => String(op.id))
  const editedCids = questionOperations
    .filter(isScenarioChangeOperation)
    .flatMap(getOperationConditions)
    .filter((condition) => !isPendingCreate(condition))
    .map((condition) => String(condition.cid))

  const isSuperseded = (condition) =>
    deletedCids.includes(String(condition.cid)) ||
    (!isPendingCreate(condition) && editedCids.includes(String(condition.cid)))

  const scenarios = withoutEmptyScenarios(
    (operation.props?.scenarios ?? [])
      .filter((scenario) => !deletedScids.includes(String(scenario.scid)))
      .map((scenario) =>
        scenario.conditions
          ? {
              ...scenario,
              conditions: scenario.conditions.filter((c) => !isSuperseded(c)),
            }
          : scenario
      )
  )

  return scenarios.length
    ? { ...operation, props: { ...operation.props, scenarios } }
    : null
}

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
    const isConditionCreateBuffered =
      !isScenarioDelete &&
      isConditionInBuffer(bufferOperations, qid, operation.id)

    bufferOperations = isScenarioDelete
      ? removeScenariosFromBuffer(bufferOperations, qid, deletedScenarioIds)
      : removeConditionFromBuffer(bufferOperations, qid, operation.id).filter(
          // An older delete of the same condition (e.g. restored after a
          // failed request) is replaced by this one, not sent twice.
          (op) =>
            !(
              isConditionDeleteOperation(op) &&
              String(op.id) === String(operation.id)
            )
        )

    // Nothing to delete on the server when the condition, or the question
    // owning the scenario, only exists locally. Queuing such a delete would
    // also block the buffer: Deletes are sent first, but this one could never
    // be sent before the question is created.
    // A temp condition whose create is no longer buffered is being sent: Its
    // delete waits in the buffer until the real cid replaces the temp one
    // (replaceTempIdsInSurveyAndBuffer), or is dropped with the condition when
    // the create failed (removeOrphanConditionDeletes).
    const isLocalOnly = isScenarioDelete
      ? hasTempId(String(qid))
      : hasTempId(String(operation.id)) && isConditionCreateBuffered

    return {
      bufferOperations,
      newOperation: isLocalOnly ? {} : operation,
      addToBuffer: !isLocalOnly,
    }
  }

  if (isConditionScriptOperation(operation)) {
    const newerOperations = bufferOperations.filter((op) =>
      isConditionOperationOfQuestion(op, qid)
    )

    // A script put back after a failed request (error) is older than the
    // buffered operations of the question:
    // - a newer script replaces it: It's discarded;
    // - newer scenario changes were made on top of it (the script cleared the
    //   conditions in the editor), so they stay. The server still has the
    //   conditions the script should have deleted: Delete them first.
    if (operation.error && newerOperations.length) {
      const hasNewerScript = newerOperations.some(isConditionScriptOperation)
      const hasReset = newerOperations.some(isDeleteAllConditionsOperation)
      if (!hasNewerScript && !hasReset) {
        bufferOperations.splice(
          bufferOperations.indexOf(newerOperations[0]),
          0,
          buildDeleteAllConditionsOperation(qid)
        )
      }

      return { bufferOperations, newOperation: {}, addToBuffer: false }
    }

    return {
      bufferOperations: bufferOperations.filter(
        (op) => !isConditionOperationOfQuestion(op, qid)
      ),
      newOperation: operation,
      addToBuffer: true,
    }
  }

  // An operation put back after a failed request (error) is older than the
  // buffered ones: What they changed since then wins.
  const isRestored = Boolean(operation.error)
  const currentOperation = isRestored
    ? withoutSupersededChanges(bufferOperations, qid, operation)
    : operation
  if (!currentOperation) {
    return { bufferOperations, newOperation: {}, addToBuffer: false }
  }

  const remainingOperation = foldEditsIntoPendingCreates(
    bufferOperations,
    qid,
    currentOperation
  )
  if (!remainingOperation) {
    return { bufferOperations, newOperation: {}, addToBuffer: false }
  }

  let lastIndex = -1
  bufferOperations.forEach((op, index) => {
    if (isConditionOperationOfQuestion(op, qid)) lastIndex = index
  })
  const lastOperation = bufferOperations[lastIndex]

  // A restored operation keeps being merged into the latest operation, as the
  // older side: The newer buffered values win (a restored create stays a
  // create, see mergeCondition).
  // Otherwise only merge operations that can be sent at the same time: Merged
  // into an operation waiting for a temp ID (or failed), a change using only
  // real IDs would wait with it, and the other way round.
  if (
    lastOperation &&
    lastOperation.op === remainingOperation.op &&
    isScenarioChangeOperation(lastOperation) &&
    (isRestored ||
      isConditionOperationWaiting(lastOperation) ===
        isConditionOperationWaiting(remainingOperation))
  ) {
    // The older versions of its conditions in the other operations could be
    // sent after the merged one and overwrite the new values: Drop them.
    // A restored operation is the older side, it removes nothing.
    if (!isRestored) {
      bufferOperations = removeConditionsFromOtherOperations(
        bufferOperations,
        qid,
        getConditionCids(remainingOperation),
        lastOperation
      )
      lastIndex = bufferOperations.indexOf(lastOperation)
    }

    bufferOperations[lastIndex] = {
      ...lastOperation,
      props: isRestored
        ? mergeConditionProps(remainingOperation.props, lastOperation.props)
        : mergeConditionProps(lastOperation.props, remainingOperation.props),
    }

    return { bufferOperations, newOperation: {}, addToBuffer: false }
  }

  // The new operation may be sent before older ones still waiting: Drop the
  // older versions of its conditions, so they can't overwrite the new values.
  if (!isRestored) {
    getConditionCids(remainingOperation).forEach((cid) => {
      bufferOperations = removeConditionFromBuffer(bufferOperations, qid, cid)
    })
  }

  return {
    bufferOperations,
    newOperation: remainingOperation,
    addToBuffer: true,
  }
}

/**
 * Drops from the buffered condition operations those matching `isOperationRemoved`,
 * and the conditions matching `isConditionRemoved` from the other create/update
 * operations (dropping the scenarios and operations left empty). Operations
 * without such conditions are kept as is.
 */
const filterConditionOperations = (
  bufferOperations,
  isOperationRemoved,
  isConditionRemoved
) =>
  bufferOperations.reduce((acc, op) => {
    if (op.entity !== Entities.questionCondition) {
      acc.push(op)
      return acc
    }

    if (isOperationRemoved(op)) return acc

    if (
      !isScenarioChangeOperation(op) ||
      !op.props?.scenarios ||
      !getOperationConditions(op).some(isConditionRemoved)
    ) {
      acc.push(op)
      return acc
    }

    const scenarios = withoutEmptyScenarios(
      op.props.scenarios.map((scenario) =>
        scenario.conditions
          ? {
              ...scenario,
              conditions: scenario.conditions.filter(
                (c) => !isConditionRemoved(c)
              ),
            }
          : scenario
      )
    )
    if (scenarios.length) {
      acc.push({ ...op, props: { ...op.props, scenarios } })
    }

    return acc
  }, [])

// Question, subquestion or group temp IDs found in `value` (condition temp IDs,
// "temp__12_1", are skipped: They are not condition sources).
const getEntityTempIds = (value) =>
  (String(value ?? '').match(TEMP_ID_TOKEN_REGEX) ?? []).filter(
    (token) => !isConditionTempId(token)
  )

/**
 * Whether the condition uses as source a question, subquestion or group temp ID
 * that isn't in `knownTempIds`, i.e. an unsaved entity that was deleted: That
 * temp ID will never be replaced.
 * @param {object} condition
 * @param {Set<string>} knownTempIds
 */
export const usesUnknownTempId = (condition, knownTempIds) =>
  ['cqid', ...SOURCE_FIELDS].some((key) =>
    getEntityTempIds(condition?.[key]).some((id) => !knownTempIds.has(id))
  )

/**
 * Remove the condition operations waiting for a temp ID that will never be
 * replaced (see usesUnknownTempId): Those of a question with an unknown temp
 * ID, and the conditions using an unknown temp ID as source. Without this,
 * these operations would wait forever, and every later change of the question
 * conditions merged into them would never be sent either.
 * @param {Array} bufferOperations
 * @param {Set<string>} knownTempIds temp IDs of the entities still existing
 */
export const removeConditionOpsWithUnknownTempIds = (
  bufferOperations = [],
  knownTempIds
) =>
  filterConditionOperations(
    bufferOperations,
    (op) => {
      const qid = String(getOperationQid(op) ?? '')
      return hasTempId(qid) && !knownTempIds.has(qid)
    },
    (c) => usesUnknownTempId(c, knownTempIds)
  )

/**
 * The question, subquestion and group temp IDs the conditions use, in the
 * buffered condition operations and in the survey. A cheap check before
 * getKnownTempIds: Usually there are none.
 * @param {Array} bufferOperations
 * @param {object} survey
 * @returns {Set<string>}
 */
export const getConditionTempIds = (bufferOperations = [], survey) => {
  const ids = new Set()
  const add = (value) => getEntityTempIds(value).forEach((id) => ids.add(id))
  const addCondition = (condition) =>
    ['cqid', ...SOURCE_FIELDS].forEach((key) => add(condition?.[key]))

  bufferOperations
    .filter((op) => op.entity === Entities.questionCondition)
    .forEach((op) => {
      add(getOperationQid(op))
      getOperationConditions(op).forEach(addCondition)
    })
  ;(survey?.questionGroups ?? []).forEach((group) =>
    (group.questions ?? []).forEach((question) =>
      (question.scenarios ?? []).forEach((scenario) =>
        (scenario.conditions ?? []).forEach(addCondition)
      )
    )
  )

  return ids
}

/**
 * The temp IDs of the groups, questions, subquestions and answers still
 * existing: In the survey, or in the buffered (non condition) operations.
 * With `wantedIds` (e.g. getConditionTempIds), only these are looked for, and
 * the search stops once they are all found: The buffer, serialized operation
 * by operation, is then usually not read at all, the unsaved entities used by
 * the conditions being in the survey.
 * @param {object} survey
 * @param {Array} bufferOperations
 * @param {Set<string>} [wantedIds] the temp IDs to look for (default: all)
 * @returns {Set<string>}
 */
export const getKnownTempIds = (survey, bufferOperations = [], wantedIds) => {
  const ids = new Set()
  const isWanted = (id) => !wantedIds || wantedIds.has(id)
  const isDone = () => Boolean(wantedIds) && ids.size === wantedIds.size
  const add = (value) =>
    getEntityTempIds(value).forEach((id) => isWanted(id) && ids.add(id))

  if (wantedIds?.size === 0) return ids

  for (const group of survey?.questionGroups ?? []) {
    add(group.gid)
    for (const question of group.questions ?? []) {
      add(question.qid)
      ;(question.subquestions ?? []).forEach((sq) => add(sq.qid))
      ;(question.answers ?? []).forEach((answer) => add(answer.aid))
      if (isDone()) return ids
    }
  }

  for (const op of bufferOperations) {
    if (op.entity === Entities.questionCondition) continue
    add(JSON.stringify(op))
    if (isDone()) return ids
  }

  return ids
}

/**
 * Drops the condition deletes still pointing at a temp cid, along with the
 * buffered create and edits of these conditions.
 * Such a delete waits for the real cid of a condition whose create was being
 * sent. Call it once that request ended, after its ID mappings were applied
 * (or when it failed): A temp cid left then belongs to a create that failed,
 * the condition doesn't exist on the server and the delete could never be
 * sent. While waiting, the delete blocks nothing else: A request is running.
 * Returns `bufferOperations` itself when there is nothing to drop.
 * @param {Array} bufferOperations
 * @returns {Array}
 */
export const removeOrphanConditionDeletes = (bufferOperations = []) =>
  bufferOperations
    .filter((op) => isConditionDeleteOperation(op) && hasTempId(String(op.id)))
    .reduce(
      (operations, deleteOp) =>
        removeConditionFromBuffer(
          operations.filter((op) => op !== deleteOp),
          getOperationQid(deleteOp),
          deleteOp.id
        ),
      bufferOperations
    )
