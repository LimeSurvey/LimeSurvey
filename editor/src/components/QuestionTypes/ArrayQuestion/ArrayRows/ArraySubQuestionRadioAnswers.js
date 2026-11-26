import classNames from 'classnames'
import { FormCheck } from 'react-bootstrap'

import { useToggleQuestionCodes } from 'hooks'

export const ArraySubQuestionRadioAnswers = ({
  subQuestionindex,
  subQuestionsHeight = [],
  arrayAnswersWidth = [],
  dragIconSize = 1,
  index,
  showBackgroundUnderSubQuestion = false,
  qid,
  isFocused,
}) => {
  const { areCodesVisible } = useToggleQuestionCodes()

  return (
    <FormCheck
      onClick={(e) => {
        e.stopPropagation()
      }}
      type="radio"
      className={classNames(
        'mb-0 array-question-item  d-flex align-items-center justify-content-center',
        { 'bg-grey': showBackgroundUnderSubQuestion }
      )}
      style={{
        width: `${arrayAnswersWidth[index] + (isFocused && areCodesVisible ? 80 : dragIconSize * 2)}px`,
        minHeight: (subQuestionsHeight[subQuestionindex] || 50) + 10,
      }}
      name={`array-subquestion-radio-answers-${subQuestionindex}${qid}`}
    />
  )
}
