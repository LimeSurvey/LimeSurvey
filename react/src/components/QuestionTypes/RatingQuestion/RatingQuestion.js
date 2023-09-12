import { useState } from 'react'
import { Form } from 'react-bootstrap'
import classNames from 'classnames'
import { StarIcon } from 'components/icons'

export const RatingQuestion = ({ question, handleUpdate }) => {
  const answers = question.answers[0]

  const [hoveredValue, setHoveredValue] = useState(-1)

  const handleOnMouseHover = (idx) => {
    setHoveredValue(idx)
  }
  const handleOnClick = (idx) => {
    const updatedQuestionAnswers = { ...answers, value: idx }

    handleUpdate({
      answers: [updatedQuestionAnswers],
    })
  }
  return (
    <div className="start-rating-question">
      <Form className="py-2">
        <div className="flex-wrap question-body-content d-flex">
          {Array(answers.displayCounter)
            .fill('-')
            .map((_, idx) => (
              <div
                onMouseOver={() => handleOnMouseHover(idx)}
                onMouseLeave={() => handleOnMouseHover(-1)}
                onClick={() => handleOnClick(idx)}
                className={classNames('me-3')}
                key={`${question.qid}-rating-question-${idx}`}
              >
                {answers.ratingType === 'star' ? (
                  <StarIcon
                    className={`fill-current cursor-pointer ${
                      idx <= answers.value || idx <= hoveredValue
                        ? 'text-primary'
                        : 'text-secondary'
                    }`}
                  />
                ) : (
                  <p
                    className={`text-center star-rating-counter cursor-pointer ${
                      idx <= answers.value || idx <= hoveredValue
                        ? 'text-primary'
                        : 'text-secondary'
                    }`}
                  >
                    {idx + 1}
                  </p>
                )}
              </div>
            ))}
        </div>
      </Form>
    </div>
  )
}
