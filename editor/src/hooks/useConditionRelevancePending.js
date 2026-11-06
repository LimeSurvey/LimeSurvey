import { hasPendingConditionLogicOperations, STATES } from 'helpers'

import { useAppState } from './useAppState'
import { useBuffer } from './useBuffer'

/**
 * Question IDs whose condition operations are currently being sent to the server.
 * Not persisted: a reload must never leave a question stuck as pending.
 */
export const useInFlightConditionQids = () =>
  useAppState(STATES.IN_FLIGHT_CONDITION_QIDS, [], {
    meta: { persist: false },
  })

/**
 * Whether the question relevance is waiting for the server, i.e. its condition
 * operations are waiting in the buffer or are being sent to the server.
 * Without scenarios the relevance is always the default one, so there is
 * nothing to wait for (e.g. after deleting the last scenario or condition).
 */
export const useIsConditionRelevancePending = (question) => {
  const { operationsBuffer } = useBuffer()
  const [inFlightConditionQids] = useInFlightConditionQids()
  const qid = question?.qid

  if (qid === undefined || qid === null || !question.scenarios?.length) {
    return false
  }

  return (
    (inFlightConditionQids ?? []).includes(String(qid)) ||
    hasPendingConditionLogicOperations(operationsBuffer.getOperations(), qid)
  )
}
