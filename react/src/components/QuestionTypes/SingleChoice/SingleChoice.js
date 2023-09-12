import { RandomNumber } from 'helpers'

import { SingleChoiceAnswers } from './SingleChoiceAnswers'

export const SingleChoice = ({
  question: { answers, qid } = {},
  question,
  handleUpdate = () => {},
  isFocused,
}) => {
  const handleUpdateAnswer = (newAnswerValue, index, isComment) => {
    const updatedQuestionAnswers = [...answers]
    if (isComment) {
      updatedQuestionAnswers[index].assessmentComment = newAnswerValue
    } else {
      updatedQuestionAnswers[index].assessmentValue = newAnswerValue
    }

    handleUpdate({ answers: updatedQuestionAnswers })
  }

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

  const handleRemovingAnswers = (answerId) => {
    const updatedQuestionAnswers = answers.filter(
      (answer) => answer.aid !== answerId
    )

    handleUpdate({ answers: updatedQuestionAnswers })
  }

  const handleOnDragEnd = (dropResult) => {
    // dropped outside the list
    if (!dropResult.destination) {
      return
    }

    const updatedQuestionAnswers = reorderQuestionAnswers(
      answers,
      dropResult.source.index,
      dropResult.destination.index
    )

    handleUpdate({ answers: updatedQuestionAnswers })
  }

  const reorderQuestionAnswers = (listRadioAnswers, startIndex, endIndex) => {
    const updatedList = [...listRadioAnswers]
    const [removed] = updatedList.splice(startIndex, 1)
    updatedList.splice(endIndex, 0, removed)

    return updatedList.map((answer, index) => {
      answer.sortorder = index + 1
      return answer
    })
  }

  return (
    <SingleChoiceAnswers
      handleAddingAnswers={handleAddingAnswers}
      handleOnDragEnd={handleOnDragEnd}
      handleRemovingAnswers={handleRemovingAnswers}
      handleUpdateAnswer={handleUpdateAnswer}
      isFocused={isFocused}
      question={question}
      handleUpdate={handleUpdate}
    />
  )
}
