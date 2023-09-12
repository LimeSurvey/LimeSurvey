import { RandomNumber } from 'helpers'
import { RankingQuestionAnswers } from './RankingQuestionAnswers'

export const RankingQuestion = ({
  question: { answers = [], qid } = {},
  question = {},
  handleUpdate = () => {},
  isFocused,
}) => {
  const handleAddingAnswers = () => {
    const updatedQuestionAnswers = [...answers]
    const lastAnswerId = answers[answers.length - 1]?.aid || 0

    const newAnswer = {
      aid: lastAnswerId + 1 + RandomNumber(),
      qid: qid,
      code: `A${updatedQuestionAnswers.length}`,
      assessmentValue: '',
      sortorder: updatedQuestionAnswers.length,
      scaleId: 0,
    }

    updatedQuestionAnswers.push(newAnswer)
    handleUpdate({ answers: updatedQuestionAnswers })
  }

  return (
    <RankingQuestionAnswers
      isFocused={isFocused}
      handleAddingAnswers={handleAddingAnswers}
      handleUpdate={handleUpdate}
      question={question}
    />
  )
}
