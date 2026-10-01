import {
  createBufferOperation,
  QUESTION_RELEVANCE_DEFAULT_VALUE,
} from 'helpers'

import { getApiOperationActions, showSuccessMessage } from '../utils'

export const handleConditionScriptUpdate = (
  script,
  survey,
  questionIndex,
  groupIndex,
  addToBuffer,
  update,
  onNavigateBack = () => {}
) => {
  const question = survey.questionGroups[groupIndex].questions[questionIndex]

  if (
    QUESTION_RELEVANCE_DEFAULT_VALUE !== script.trim() &&
    script.trim() === question.relevance.trim()
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
