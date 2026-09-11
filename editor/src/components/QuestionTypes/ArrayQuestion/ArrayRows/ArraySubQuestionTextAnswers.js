import { useParams } from 'react-router-dom'
import classNames from 'classnames'

import { Input } from 'components/UIComponents'
import { useSurvey } from 'hooks'

export const ArraySubQuestionTextAnswers = ({
  subQuestionindex,
  subQuestionsHeight = [],
  arrayAnswersWidth = [],
  dragIconSize = 1,
  index,
  showBackgroundUnderSubQuestion = false,
  isFocused,
}) => {
  const { surveyId } = useParams()
  const { survey } = useSurvey(surveyId)

  const showQNumCode = survey.showQNumCode

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
          width: `${arrayAnswersWidth[index] + (isFocused && showQNumCode?.showNumber ? 80 : dragIconSize * 2)}px`,
          padding: '0 10px',
        }}
        className="disabled"
      />
    </div>
  )
}
