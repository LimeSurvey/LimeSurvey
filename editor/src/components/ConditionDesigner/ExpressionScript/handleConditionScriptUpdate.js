import { QUESTION_RELEVANCE_DEFAULT_VALUE } from 'helpers'

import { buildConditionScriptOperation, showSuccessMessage } from '../utils'

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

  addToBuffer(buildConditionScriptOperation(question.qid, script.trim()))

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
