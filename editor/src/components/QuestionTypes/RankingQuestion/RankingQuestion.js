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
  validateCode,
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

  const handleLocalCodeUpdate = (value, index) => {
    const validationMessage = validateCode(
      { titleKey: 'answer', items: answers },
      index,
      value
    )
    if (validationMessage === '') {
      handleCodeUpdate(value, index)
    } else {
      showErrorMessage(validationMessage, 'top-center')
    }
  }

  const handleCodeUpdate = (entitiesInfo, value, index) => {
    handleChildLUpdate(
      value,
      index,
      entitiesInfo.items,
      entitiesInfo.entity,
      false
    )
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
        handleLocalCodeUpdate={handleLocalCodeUpdate}
      />
    </div>
  )
}
