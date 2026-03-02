import { Form } from 'react-bootstrap'

import { filterToNumericOrEmpty, getAttributeValue, isTrue } from 'helpers'

import './TextQuestion.scss'

export const ShortTextAnswer = ({
  attributes = {},
  value = '',
  language = 'en',
  onValueChange,
}) => {
  const maxChars = getAttributeValue(attributes.maximum_chars)
  const numbersOnly = isTrue(
    getAttributeValue(attributes.numbers_only, language)
  )

  /**
   * Keydown event handler for handling maximum character limit
   * @param event
   */
  const onKeyDown = (event) => {
    const inputValue = event.target.value
    if (
      maxChars &&
      event.key !== 'Backspace' &&
      inputValue.length >= maxChars
    ) {
      event.preventDefault()
    }

    if (maxChars && inputValue.length > maxChars) {
      event.target.value = ''
    }
  }

  return (
    <div className={'question-body-content'}>
      <div className="d-flex gap-2 align-items-center justify-content-center">
        <Form.Group className="flex-grow-1">
          <Form.Control
            maxLength={maxChars ? maxChars : Infinity}
            type="text"
            placeholder={st('Enter your answer here.')}
            onKeyDown={onKeyDown}
            data-testid="text-question-answer-input"
            defaultValue={value}
            onChange={({ target: { value } }) => {
              if (numbersOnly) {
                value = filterToNumericOrEmpty(value)
              }

              onValueChange(value)
            }}
          />
        </Form.Group>
      </div>
    </div>
  )
}
