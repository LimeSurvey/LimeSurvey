import { getQuestionTypeInfo } from 'components'
import {
  isArrayQuestion,
  isRankingQuestion,
  isSingleChoiceQuestion,
} from 'helpers'

export const renderCellText = ({
  value,
  comment,
  subquestionTitle,
  answerTitle,
  questionThemeName = '',
  checked,
  index,
}) => {
  if (!value && !answerTitle && !comment?.value) {
    return <></>
  }

  if (!subquestionTitle && !answerTitle && !comment?.value) {
    return <span> {value} </span>
  }

  if (
    questionThemeName === getQuestionTypeInfo().MULTIPLE_NUMERICAL_INPUTS.theme
  ) {
    return (
      <>
        {subquestionTitle}: {Number(value)?.toFixed(2) ?? '0.00'}
      </>
    )
  }

  if (questionThemeName === getQuestionTypeInfo().MULTIPLE_SHORT_TEXTS.theme) {
    return (
      <>
        {subquestionTitle}: {value}
      </>
    )
  }

  if (isArrayQuestion(questionThemeName)) {
    return (
      <>
        {subquestionTitle}: {answerTitle}
        {comment && <span>: {comment.value}</span>}
      </>
    )
  }

  return (
    <span>
      {checked && !isRankingQuestion(questionThemeName) && (
        <i className="ri-check-line text-success"></i>
      )}
      {isRankingQuestion(questionThemeName) && `${index + 1}. `}
      {!isSingleChoiceQuestion(questionThemeName) && `${subquestionTitle}`}
      {isSingleChoiceQuestion(questionThemeName) && answerTitle}
      {comment?.value && (
        <span>
          {answerTitle && ':'} {comment.value}
        </span>
      )}
    </span>
  )
}
