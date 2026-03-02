import { Form } from 'react-bootstrap'

import { getAttributeValue } from 'helpers'

import './TextQuestion.scss'

export const LongTextAnswer = ({ attributes = {}, value, onValueChange }) => {
  const maxChars = getAttributeValue(attributes.maximum_chars)

  return (
    <Form.Control
      onClick={(e) => {
        e.stopPropagation()
      }}
      placeholder={st('Enter your answer here.')}
      as="textarea"
      type="textarea"
      role="textarea"
      rows={4}
      maxLength={maxChars ? maxChars : Infinity}
      data-testid="text-question-answer-input"
      className="long-text-answer"
      defaultValue={value}
      onChange={(event) => onValueChange(event.target.value)}
    />
  )
}
