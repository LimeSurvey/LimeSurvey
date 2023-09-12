import { Form } from 'react-bootstrap'

import './TextQuestion.scss'

export const LongTextAnswer = ({ attributes = {}, answer, setAnswer }) => {
  const maxChars = attributes.maximum_chars?.value

  return (
    <Form.Group>
      <Form.Control
        placeholder="Enter your answer here."
        as="textarea"
        rows={4}
        maxLength={maxChars ? maxChars : Infinity}
        data-testid="text-question-answer-input"
        defaultValue={answer}
        onChange={({ target: { value } }) => setAnswer(value)}
      />
    </Form.Group>
  )
}
