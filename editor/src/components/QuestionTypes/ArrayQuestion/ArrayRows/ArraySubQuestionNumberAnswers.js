import classNames from 'classnames'
import { Select } from 'components/UIComponents'
import { useToggleQuestionCodes } from '../../../../hooks'

export const ArraySubQuestionNumberAnswers = ({
  index,
  subQuestionindex,
  subQuestionsHeight = [],
  arrayAnswersWidth = [],
  dragIconSize = 1,
  showBackgroundUnderSubQuestion = false,
  isFocused,
}) => {
  const { areCodesVisible } = useToggleQuestionCodes()

  const options = [
    { value: '...', label: '...' },
    ...Array(10)
      .fill('-')
      .map((_, idx) => ({
        label: `${idx + 1}`,
        value: `${idx + 1}`,
      })),
  ]

  return (
    <div
      className={classNames(
        'array-question-item d-flex align-items-center justify-content-center py-1',
        {
          'bg-grey': showBackgroundUnderSubQuestion,
        }
      )}
      style={{
        minHeight: (subQuestionsHeight[subQuestionindex] || 0) + 10,
        width: `${arrayAnswersWidth[index] + (isFocused && areCodesVisible ? 80 : dragIconSize * 2)}px`,
      }}
    >
      <span
        onClick={(e) => e.stopPropagation()}
        style={{
          width: `${arrayAnswersWidth[index] + (isFocused && areCodesVisible ? 60 : dragIconSize)}px`,
        }}
      >
        <Select options={options} />
      </span>
    </div>
  )
}
