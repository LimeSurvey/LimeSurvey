import { useState } from 'react'

import { getResolvedTempIdsVersion, isResolvedTempId } from 'helpers'

/**
 * Key of the condition panels (Condition Designer, ExpressionScript) of the
 * focused question.
 * It stays the same when the question's temp ID is replaced with its real ID,
 * so the open panels and their unsaved changes are kept; it changes when
 * another question is focused, which closes and resets them.
 * @param {number|string} qid the focused question ID
 * @param {function} onQidResolved called (during render) when `qid` is the
 *   real ID of the previous temp ID
 * @returns {number|string}
 */
export const useConditionPanelsKey = (qid, onQidResolved = () => {}) => {
  // `version`: The temp ID mappings known when `qid` was focused. An older
  // mapping of the same temp ID is about another question.
  const [panels, setPanels] = useState(() => ({
    qid,
    key: qid,
    version: getResolvedTempIdsVersion(),
  }))

  if (panels.qid !== qid) {
    const isQidResolution = isResolvedTempId(panels.qid, qid, panels.version)
    setPanels({
      qid,
      key: isQidResolution ? panels.key : qid,
      version: getResolvedTempIdsVersion(),
    })
    if (isQidResolution) onQidResolved()
  }

  return panels.key
}
