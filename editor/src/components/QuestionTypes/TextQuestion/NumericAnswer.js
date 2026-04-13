import { Form } from 'react-bootstrap'
import './TextQuestion.scss'
import { filterToNumericOrEmpty } from 'helpers'

export const NumericAnswer = ({ value, onValueChange }) => {
  return (
    <div className={'question-body-content'}>
      <div className="d-flex gap-2 align-items-center justify-content-center">
        <Form.Group className="flex-grow-1">
          <Form.Control
            type={'text'}
            placeholder={st('Enter your answer here.')}
            data-testid="text-question-answer-input"
            defaultValue={value}
            onChange={(event) =>
              onValueChange(filterToNumericOrEmpty(event.target.value))
            }
          />
        </Form.Group>
      </div>
    </div>
  )
}
