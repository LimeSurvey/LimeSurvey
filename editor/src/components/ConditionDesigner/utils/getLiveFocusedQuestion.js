import { queryClient } from 'queryClient'
import { getQuestionById, STATES } from 'helpers'

/**
 * The focused question as it is now in the survey, with its position. For
 * handlers that run after the render that created them, e.g. confirm
 * dialogs: Meanwhile a save may have replaced the question's temp ID (the
 * focus is updated with it) or moved it, and the focus indexes can be stale.
 * @returns {{survey?: object, question?: object, groupIndex?: number,
 *   questionIndex?: number}} without `question` when there is none
 */
export const getLiveFocusedQuestion = () => {
  const survey = queryClient.getQueryData([STATES.SURVEY])?.survey
  const qid = queryClient.getQueryData([STATES.FOCUSED_ENTITY])?.focused?.qid
  if (!survey?.questionGroups || qid === undefined) return {}

  return { survey, ...getQuestionById(qid, survey) }
}
