import {
  createBufferOperation,
  QUESTION_RELEVANCE_DEFAULT_VALUE,
} from 'helpers'

import {
  getApiOperationActions,
  getCurrentQuestionInfo,
  showSuccessMessage,
} from '../utils'

/**
 * Applies an ExpressionScript condition to a question.
 *
 * The question is resolved by its ID against the current survey, not against
 * a survey passed in by the caller: this runs from popups that can stay open
 * while the survey is refreshed, so a survey or indices captured when the popup
 * opened can be outdated, and the question can be gone. Nothing is applied
 * when the question no longer exists.
 * @param {string} script The new condition script.
 * @param {number|string} qid ID of the question to update.
 * @param {Function} addToBuffer Adds the operation to the buffer.
 * @param {Function} update Updates the survey.
 * @param {Function} onNavigateBack Called after the script was applied.
 * @returns {void}
 */
export const handleConditionScriptUpdate = (
  script,
  qid,
  addToBuffer,
  update,
  onNavigateBack = () => {}
) => {
  const { survey, question, groupIndex, questionIndex } =
    getCurrentQuestionInfo(qid)

  if (!question) return

  if (
    QUESTION_RELEVANCE_DEFAULT_VALUE !== script.trim() &&
    script.trim() === (question.relevance ?? '').trim()
  )
    return

  const props = {
    qid: question.qid,
    action: getApiOperationActions().CONDITION_SCRIPT.UPDATE,
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
