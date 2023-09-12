import classNames from 'classnames'
import { Input } from 'components/UIComponents'

export const ArraySubQuestionTextAnswers = ({
  answers,
  subQuestionindex,
  subQuestionsHeight = [],
  arrayAnswersWidth = [],
  dragIconSize = 1,
}) => {
  return (
    <div className="d-flex">
      {answers?.map((answer, index) => {
        return (
          <div
            key={`${answer.aid}-${index}-subQuestionAnswerValue`}
            className={classNames(
              'array-question-item d-flex align-items-center pb-2',
              {
                'bg-grey': !(index % 2),
              }
            )}
            style={{ minHeight: subQuestionsHeight[subQuestionindex] }}
          >
            <Input
              name={`${answer.aid}-${index}-subQuestionAnswer`}
              style={{
                width: `${arrayAnswersWidth[index] + dragIconSize * 2}px`,
                padding: '0 10px',
              }}
            />
          </div>
        )
      })}
    </div>
  )
}
