import classNames from 'classnames'

import { Input } from 'components/UIComponents'
import { useToggleQuestionCodes } from 'hooks'

export const ArraySubQuestionTextAnswers = ({
  subQuestionindex,
  subQuestionsHeight = [],
  arrayAnswersWidth = [],
  dragIconSize = 1,
  index,
  showBackgroundUnderSubQuestion = false,
  isFocused,
}) => {
  const { areCodesVisible } = useToggleQuestionCodes()

  return (
    <div
      className={classNames(
        'array-question-item d-flex align-items-center py-1',
        {
          'bg-grey': showBackgroundUnderSubQuestion,
        }
      )}
      style={{
        minHeight: (subQuestionsHeight[subQuestionindex] || 50) + 10,
        cursor: 'not-allowed',
      }}
    >
      <Input
        disabled
        style={{
          width: `${arrayAnswersWidth[index] + (isFocused && areCodesVisible ? 80 : dragIconSize * 2)}px`,
          padding: '0 10px',
        }}
        className="disabled"
      />
    </div>
  )
}
