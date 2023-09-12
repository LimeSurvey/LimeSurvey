import { Form } from 'react-bootstrap'

import { IsTrue, MINIMUM_INPUT_WIDTH_PERCENT } from 'helpers'
import { ContentEditor } from 'components/UIComponents'

import './TextQuestion.scss'

export const ShortTextAnswer = ({ attributes = {}, answer, setAnswer }) => {
  const maxChars = attributes.maximum_chars?.value
  const numbersOnly = IsTrue(attributes.numbers_only?.value)

  const onNumberKeyDown = (event) => {
    const inputValue = event.target.value
    if (
      (numbersOnly && ['e', 'E'].includes(event.key)) ||
      (maxChars && event.key !== 'Backspace' && inputValue.length >= maxChars)
    ) {
      event.preventDefault()
    }

    if (maxChars && inputValue.length > maxChars) {
      event.target.value = ''
    }
  }

  return (
    <div
      style={{
        width:
          Math.max(
            attributes.text_input_width?.value,
            MINIMUM_INPUT_WIDTH_PERCENT
          ) + '%' || '100%',
      }}
      className={'question-body-content'}
    >
      <div className="d-flex gap-2 align-items-center justify-content-center">
        {attributes.prefix?.value && (
          <ContentEditor disabled={true} value={attributes.prefix?.value} />
        )}
        <Form.Group className="flex-grow-1">
          <Form.Control
            maxLength={maxChars ? maxChars : Infinity}
            type={numbersOnly ? 'number' : 'text'}
            placeholder="Enter your answer here."
            onKeyDown={onNumberKeyDown}
            data-testid="text-question-answer-input"
            defaultValue={answer}
            onChange={({ target: { value } }) => setAnswer(value)}
          />
        </Form.Group>
        {attributes.suffix && (
          <ContentEditor disabled={true} value={attributes.suffix?.value} />
        )}
      </div>
    </div>
  )
}
