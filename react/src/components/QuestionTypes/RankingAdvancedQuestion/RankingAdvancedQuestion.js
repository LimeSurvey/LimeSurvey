import { RandomNumber } from 'helpers'
import { RankingAdvancedQuestionAnswers } from './RankingAdvancedQuestionAnswers'

export const RankingAdvancedQuestion = ({
  question = {},
  handleUpdate = () => {},
  isFocused,
}) => {
  const { firstAnswers, qid } = question
  const handleAddingAnswers = () => {
    const updatedQuestionAnswers =
      Array.isArray(firstAnswers) && firstAnswers.length > 0
        ? [...firstAnswers]
        : []
    const lastAnswerId =
      Array.isArray(firstAnswers) && firstAnswers?.length > 0
        ? firstAnswers[firstAnswers.length - 1]?.aid || 0
        : 0

    const newAnswer = {
      aid: lastAnswerId + 1 + RandomNumber(),
      qid: qid,
      code: `A${updatedQuestionAnswers.length}`,
      assessmentValue: '',
      sortorder: updatedQuestionAnswers.length,
      scaleId: 0,
    }

    updatedQuestionAnswers.push(newAnswer)
    handleUpdate({ firstAnswers: updatedQuestionAnswers })
  }

  return (
    <RankingAdvancedQuestionAnswers
      isFocused={isFocused}
      handleAddingAnswers={handleAddingAnswers}
      handleUpdate={handleUpdate}
      question={question}
    />
  )
}
