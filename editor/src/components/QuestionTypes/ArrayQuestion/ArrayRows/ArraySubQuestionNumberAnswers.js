import classNames from 'classnames'
import { Select } from 'components/UIComponents'
import { useParams } from 'react-router-dom'
import { useSurvey } from '../../../../hooks'

export const ArraySubQuestionNumberAnswers = ({
  index,
  subQuestionindex,
  subQuestionsHeight = [],
  arrayAnswersWidth = [],
  dragIconSize = 1,
  showBackgroundUnderSubQuestion = false,
  isFocused,
}) => {
  const { surveyId } = useParams()
  const { survey } = useSurvey(surveyId)
  const showQNumCode = survey.showQNumCode

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
        width: `${arrayAnswersWidth[index] + (isFocused && showQNumCode?.showNumber ? 80 : dragIconSize * 2)}px`,
      }}
    >
      <span
        onClick={(e) => e.stopPropagation()}
        style={{
          width: `${arrayAnswersWidth[index] + (isFocused && showQNumCode?.showNumber ? 60 : dragIconSize)}px`,
        }}
      >
        <Select options={options} />
      </span>
    </div>
  )
}
