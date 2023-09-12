import classNames from 'classnames'
import { FormCheck } from 'react-bootstrap'

export const ArraySubQuestionRadioAnswers = ({
  answers,
  subQuestionindex,
  subQuestionsHeight = [],
  arrayAnswersWidth = [],
  dragIconSize = 1,
  subQuestionId,
  arrayByColumn = false,
}) => {
  const name = `${subQuestionId}-${subQuestionindex}-subQuestionAnswer`

  return (
    <div className="d-flex">
      {answers?.map((answer, index) => {
        return (
          <div
            key={`${answer.aid}-${index}-subQuestionAnswerValue`}
            className={classNames(
              'array-question-item d-flex align-items-center',
              {
                'bg-grey': !(index % 2),
              }
            )}
            style={{ minHeight: subQuestionsHeight[subQuestionindex] }}
          >
            <FormCheck
              type="radio"
              name={
                arrayByColumn
                  ? `${answer.aid}-${index}-subQuestionAnswer`
                  : name
              }
              className="form-check mb-0"
              style={{
                width: `${arrayAnswersWidth[index] + dragIconSize * 2}px`,
              }}
            />
          </div>
        )
      })}
    </div>
  )
}
