import { hasPendingConditionLogicOperations, STATES } from 'helpers'

import { isConditionOperationWaiting } from 'helpers/Buffer/operationsHandlers'

import { useAppState } from './useAppState'
import { useBufferOperations } from './useBuffer'

/**
 * Question IDs whose condition operations are currently being sent to the server.
 * Not persisted: A reload must never leave a question stuck as pending.
 */
export const useInFlightConditionQids = () =>
  useAppState(STATES.IN_FLIGHT_CONDITION_QIDS, [], {
    meta: { persist: false },
  })

/**
 * Whether the question relevance is waiting for the server, i.e. its condition
 * operations are waiting in the buffer to be sent, or are being sent.
 * Without scenarios the relevance is always the default one, so there is
 * nothing to wait for (e.g. after deleting the last scenario or condition).
 * Operations waiting for a temp ID don't count (e.g. a source question whose
 * create was refused): They may wait for a long time, and the server
 * relevance of the saved conditions is applied meanwhile. Applying an
 * expression script replaces them.
 */
export const useIsConditionRelevancePending = (question) => {
  // The raw buffer: OperationsBuffer would deep copy it on every render.
  const operations = useBufferOperations()
  const [inFlightConditionQids] = useInFlightConditionQids()
  const qid = question?.qid

  if (qid === undefined || qid === null || !question.scenarios?.length) {
    return false
  }

  return (
    (inFlightConditionQids ?? []).includes(String(qid)) ||
    hasPendingConditionLogicOperations(
      (operations ?? []).filter((op) => !isConditionOperationWaiting(op)),
      qid
    )
  )
}
