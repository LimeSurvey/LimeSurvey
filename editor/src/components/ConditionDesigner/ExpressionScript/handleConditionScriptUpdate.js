import {
  createBufferOperation,
  QUESTION_RELEVANCE_DEFAULT_VALUE,
} from 'helpers'

import { CONDITION_ACTIONS } from 'helpers/Buffer/operationsHandlers'

import { getLiveFocusedQuestion, showSuccessMessage } from '../utils'

/**
 * Sets the expression script of the focused question (its relevance), which
 * replaces all its conditions, and queues the operation.
 * The question is looked up now (getLiveFocusedQuestion): The caller can be a
 * confirm dialog opened before a save replaced the question's temp ID.
 *
 * @param {string} script the new expression script
 * @param {Function} addToBuffer
 * @param {Function} update survey updater
 * @param {Function} [onNavigateBack] called once the script was applied
 * @returns {void}
 */
export const handleConditionScriptUpdate = (
  script,
  addToBuffer,
  update,
  onNavigateBack = () => {}
) => {
  const { survey, question, groupIndex, questionIndex } =
    getLiveFocusedQuestion()
  if (!question) return

  if (
    QUESTION_RELEVANCE_DEFAULT_VALUE !== script.trim() &&
    script.trim() === question.relevance.trim()
  )
    return

  const props = {
    qid: question.qid,
    action: CONDITION_ACTIONS.SCRIPT,
    script: script.trim(),
  }

  const operation = createBufferOperation(question.qid)
    .questionCondition()
    .update()

  operation.qid = question.qid
  operation.props = props
  addToBuffer(operation)

  question.relevance = script
  question.scenarios = []

  const updatedQuestionGroups = [...survey.questionGroups]
  updatedQuestionGroups[groupIndex].questions[questionIndex] = question

  update({
    questionGroups: updatedQuestionGroups,
  })

  onNavigateBack()

  // prevent notification when resting the expression script
  if (script.trim() !== QUESTION_RELEVANCE_DEFAULT_VALUE) showSuccessMessage()
}
