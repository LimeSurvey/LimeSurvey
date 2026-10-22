import { Entities } from './Buffer/Entities'

const CONDITION_SCRIPT_ACTION = 'conditionScript'

/**
 * Whether the operation changes the question conditions (scenarios),
 * which means the server will regenerate the question relevance.
 * Expression script operations set the relevance directly, so they are excluded.
 */
export const isConditionLogicOperation = (operation) =>
  operation?.entity === Entities.questionCondition &&
  operation?.props?.action !== CONDITION_SCRIPT_ACTION

export const getConditionLogicQids = (operations = []) => [
  ...new Set(
    (operations ?? [])
      .filter((operation) => isConditionLogicOperation(operation))
      .filter((operation) => !operation.error)
      .map((operation) => operation.props?.qid ?? operation.qid)
      .filter((qid) => qid !== undefined && qid !== null && qid !== '')
      .map(String)
  ),
]

export const hasPendingConditionLogicOperations = (operations, qid) =>
  getConditionLogicQids(operations).includes(String(qid))
