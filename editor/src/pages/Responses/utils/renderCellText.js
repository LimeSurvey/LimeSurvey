import { useLayoutEffect, useRef, useState } from 'react'
import { getQuestionTypeInfo } from 'components'
import {
  isArrayQuestion,
  isRankingQuestion,
  isSingleChoiceQuestion,
  OTHER_CODE,
} from 'helpers'

const ArrayResponseAnswer = ({ answerTitle, comment }) => {
  const titleRef = useRef(null)
  const [minimumWidth, setMinimumWidth] = useState(0)

  useLayoutEffect(() => {
    setMinimumWidth(
      Math.min(40, titleRef.current?.getBoundingClientRect().width || 0)
    )
  }, [answerTitle])

  return (
    <span className="array-response-answer" style={{ minWidth: minimumWidth }}>
      <span
        ref={titleRef}
        className="array-response-answer-measure"
        aria-hidden="true"
      >
        {answerTitle}
      </span>
      {answerTitle}
      {comment && <span>: {comment.value}</span>}
    </span>
  )
}

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
  subquestion1,
  subquestion2,
}) => {
  const isOtherKey = key.endsWith('_Cother') || subquestionTitle == OTHER_CODE

  const otherReplaceText =
    (question.attributes?.other_replace_text?.[baseLanguage] || t('Other')) +
    ': '

  if (!value && !answerTitle && !comment?.value) {
    return <></>
  }

  if (!subquestionTitle && !answerTitle && !comment?.value) {
    return (
      <span>
        {' '}
        {isOtherKey ? otherReplaceText : ''} {value}
      </span>
    )
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
      <span className="array-response-text">
        <span className="array-subquestion">
          {subquestion1 ? subquestion1 : subquestionTitle}
        </span>
        <span className="array-separator">-</span>
        <span className="array-subquestion">{subquestion2}</span>
        <span className="array-separator">:</span>
        <ArrayResponseAnswer answerTitle={answerTitle} comment={comment} />
      </span>
    )
  }

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
