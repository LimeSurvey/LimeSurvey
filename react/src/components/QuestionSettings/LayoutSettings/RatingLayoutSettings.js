import { ToggleButtons } from 'components/UIComponents'
import { Form } from 'react-bootstrap'

export const RatingLayoutSettings = ({ question, handleUpdate }) => {
  const handleRatingCounterChange = (event) => {
    const value = event.target.value
    const updatedQuestionAnswers = {
      ...question.answers[0],
      displayCounter: parseInt(value),
    }
    handleUpdate(
      {
        answers: [{ ...updatedQuestionAnswers }],
      },
      false
    )
  }

  const handleRatingTypeChange = (value) => {
    const updatedQuestionAnswers = {
      ...question.answers[0],
      ratingType: value === '1' ? 'star' : 'number',
    }

    handleUpdate(
      {
        answers: [{ ...updatedQuestionAnswers }],
      },
      false
    )
  }

  return (
    <div className="mt-3">
      <Form.Label>Display</Form.Label>
      <div>
        <Form.Select
          aria-label="Default select example"
          onChange={handleRatingCounterChange}
        >
          <option value={5}>5</option>
          <option value={10}>10</option>
          <option value={20}>20</option>
        </Form.Select>
      </div>
      <div className="mt-3">
        <ToggleButtons
          value={question.answers[0].ratingType === 'star' ? '1' : '0'}
          id="star-rating-settings"
          name="star-rating-settings"
          toggleOptions={[
            { name: 'Number', value: '0' },
            { name: 'Star', value: '1' },
          ]}
          onChange={handleRatingTypeChange}
        />
      </div>
    </div>
  )
}
