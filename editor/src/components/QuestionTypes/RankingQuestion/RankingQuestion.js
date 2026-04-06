import { Entities } from 'helpers'
import { RankingQuestionAnswers } from './RankingQuestionAnswers'
import { showErrorMessage } from 'components/ConditionDesigner/utils/conditionAlertHelpers'

export const RankingQuestion = ({
  question: { answers = [] } = {},
  question = {},
  handleUpdate = () => {},
  isFocused,
  handleChildLUpdate,
  handleChildAdd,
  handleChildDelete,
  handleOnChildDragEnd,
  handleChildCodeUpdate,
}) => {
  const handleAnswerUpdate = (value, index) => {
    handleChildLUpdate(value, index, answers, Entities.answer)
  }

  const handleOnDragEnd = (dropResult) => {
    handleOnChildDragEnd(dropResult, answers, Entities.answer)
  }

  const handleRemovingAnswers = (answerId) => {
    handleChildDelete(answerId, answers, Entities.answer)
  }

  return (
    <div data-testid="ranking-question">
      <RankingQuestionAnswers
        isFocused={isFocused}
        handleChildAdd={handleChildAdd}
        handleAnswerUpdate={handleAnswerUpdate}
        handleUpdate={handleUpdate}
        question={question}
        handleRemovingAnswers={handleRemovingAnswers}
        handleOnDragEnd={handleOnDragEnd}
        handleChildCodeUpdate={handleChildCodeUpdate}
      />
    </div>
  )
}
