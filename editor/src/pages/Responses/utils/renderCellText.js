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
  key = '',
  question = {},
  baseLanguage,
}) => {
  const isOtherKey = key.endsWith('_Cother')

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

  if (isArrayQuestion(questionThemeName)) {
    return (
      <>
        {subquestionTitle}: {answerTitle}
        {comment && <span>: {comment.value}</span>}
      </>
    )
  }

  const otherReplaceText =
    (question.attributes?.other_replace_text?.[baseLanguage] || t('Other')) +
    ': '

  return (
    <span>
      {checked && !isRankingQuestion(questionThemeName) && (
        <i className="ri-check-line text-success"></i>
      )}
      {isRankingQuestion(questionThemeName) && `${index + 1}. `}
      {isSingleChoiceQuestion(questionThemeName)
        ? `${isOtherKey ? otherReplaceText : ''} ${answerTitle}`
        : `${isOtherKey ? otherReplaceText : ''} ${subquestionTitle}`}
      {comment?.value && (
        <span>
          {answerTitle && ':'} {comment.value}
        </span>
      )}
    </span>
  )
}
