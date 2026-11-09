import { useState } from 'react'

import { isResolvedTempId } from 'helpers'

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
  const [panels, setPanels] = useState({ qid, key: qid })

  if (panels.qid !== qid) {
    const isQidResolution = isResolvedTempId(panels.qid, qid)
    setPanels({ qid, key: isQidResolution ? panels.key : qid })
    if (isQidResolution) onQidResolved()
  }

  return panels.key
}
