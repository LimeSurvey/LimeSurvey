import classNames from 'classnames'
import { Select } from 'components/UIComponents'

export const ArraySubQuestionNumberAnswers = ({
  answers,
  subQuestionindex,
  subQuestionsHeight = [],
  arrayAnswersWidth = [],
  dragIconSize = 1,
}) => {
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
    <div className="d-flex">
      {answers?.map((answer, index) => {
        return (
          <div
            key={`${answer.aid}-${index}-subQuestionAnswerValue`}
            className={classNames(
              'array-question-item d-flex align-items-center justify-content-center pb-2',
              {
                'bg-grey': !(index % 2),
              }
            )}
            style={{
              minHeight: subQuestionsHeight[subQuestionindex],
              width: `${arrayAnswersWidth[index] + dragIconSize * 2}px`,
              paddingLeft: '5px',
              paddingRight: '5px',
            }}
          >
            <Select
              style={{
                width: `${arrayAnswersWidth[index] + dragIconSize}px`,
              }}
              options={options}
              className="pointer-events-non"
            />
          </div>
        )
      })}
    </div>
  )
}
