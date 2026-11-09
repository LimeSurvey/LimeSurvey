import { cloneDeep, isEqual } from 'lodash'

import { getQuestionById } from './getQuestionById'

/**
 * The focus data (STATES.FOCUSED_ENTITY) with the focused question refreshed
 * from `survey`: its data and its position. The focus is a copy of the
 * question, so it doesn't see the changes made to the survey.
 * Returns null when there is nothing to refresh: no focused question, the
 * question isn't in the survey, or nothing changed (the focus object must then
 * be kept, its identity drives effects, e.g. QuestionGroup re-expanding the
 * group of the focused question).
 *
 * @param {object} focusData { focused, groupIndex, questionIndex, ... }
 * @param {object} survey
 * @returns {object|null}
 */
export const getRefreshedFocus = (focusData, survey) => {
  if (!focusData?.focused?.qid) return null

  const { question, groupIndex, questionIndex } = getQuestionById(
    focusData.focused.qid,
    survey
  )
  if (!question) return null

  const isUnchanged =
    isEqual(focusData.focused, question) &&
    focusData.groupIndex === groupIndex &&
    focusData.questionIndex === questionIndex

  return isUnchanged
    ? null
    : { ...focusData, focused: cloneDeep(question), groupIndex, questionIndex }
}
