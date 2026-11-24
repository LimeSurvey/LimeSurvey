import { useEffect } from 'react'

import { closeConditionPopups } from '../components/ConditionDesigner/utils'

/**
 * Closes the condition popups (see CONDITION_POPUP_CLASS) in one place: when
 * the focused question changes, when it is missing from the survey, and when
 * the component using this hook unmounts.
 *
 * The popups are SweetAlert modals outside of the React tree, so without this
 * they stay open after their question's panel is gone, and their confirm
 * actions would run for a question that no longer exists.
 * @param {number|string} qid ID of the focused question.
 * @param {boolean} isQuestionMissing Whether the question is no longer in the survey.
 * @returns {void}
 */
export const useCloseConditionPopups = (qid, isQuestionMissing) => {
  // The cleanup runs when the qid changes and on unmount
  useEffect(() => () => closeConditionPopups(), [qid])

  useEffect(() => {
    if (isQuestionMissing) {
      closeConditionPopups()
    }
  }, [isQuestionMissing])
}
