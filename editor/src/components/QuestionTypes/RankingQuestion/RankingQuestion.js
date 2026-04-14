import { Entities } from 'helpers'
import { RankingQuestionSubquestions } from './RankingQuestionSubquestions'

export const RankingQuestion = ({
  question: { subquestions = [] } = {},
  question = {},
  handleUpdate = () => {},
  isFocused,
  handleChildLUpdate,
  handleChildAdd,
  handleChildDelete,
  handleOnChildDragEnd,
  handleChildCodeUpdate,
}) => {
  const handleSubquestionUpdate = (value, index) => {
    handleChildLUpdate(value, index, subquestions, Entities.subquestion)
  }

  const handleOnDragEnd = (dropResult) => {
    handleOnChildDragEnd(dropResult, subquestions, Entities.subquestion)
  }

  const handleRemovingSubquestions = (subquestionId) => {
    handleChildDelete(subquestionId, subquestions, Entities.subquestion)
  }

  return (
    <div data-testid="ranking-question">
      <RankingQuestionSubquestions
        isFocused={isFocused}
        handleChildAdd={handleChildAdd}
        handleSubquestionUpdate={handleSubquestionUpdate}
        handleUpdate={handleUpdate}
        question={question}
        handleRemovingSubquestions={handleRemovingSubquestions}
        handleOnDragEnd={handleOnDragEnd}
        handleChildCodeUpdate={handleChildCodeUpdate}
      />
    </div>
  )
}
