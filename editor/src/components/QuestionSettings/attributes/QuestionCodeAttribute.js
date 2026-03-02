import { useEffect, useState } from 'react'

import { useAppState, useFocused } from 'hooks'
import { TestValidation } from '../../Survey/Questions/QuestionCodeSchema'
import { STATES } from '../../../helpers'
import { Form, FormControl } from 'react-bootstrap'

// note: make it faster using debounce ... read about it.
export const QuestionCodeAttribute = ({ value, update }) => {
  const [codeToQuestion] = useAppState(STATES.CODE_TO_QUESTION, {})
  const [errorMessage, setErrorMessage] = useState('')
  const [inputValue, setInputValue] = useState(value)
  const { focused } = useFocused()

  useEffect(() => {
    setInputValue(value)
    validateCode(value)
  }, [focused?.qid])

  const validateCode = (value) => {
    const { question } =
      codeToQuestion && codeToQuestion[value]
        ? codeToQuestion[value]
        : { question: null }

    const questionIsNotFocused = question?.title !== focused?.title
    const codeExist = question && questionIsNotFocused

    if (value) {
      setErrorMessage(
        TestValidation(value.toUpperCase()).error
          ? t('Only letters and numbers are allowed.')
          : ''
      )
    }

    if (codeExist) {
      setErrorMessage(t('Question codes must be unique.'))
    }
  }

  const handleOnChange = ({ target: { value } }) => {
    setInputValue(value.toUpperCase())
    validateCode(value)
    if (errorMessage === '') {
      update(value)
    }
  }

  return (
    <div>
      <Form.Group className="d-flex qe-input-group align-content-center align-items-center">
        <div className="ui-label w-50 flex-grow-1">{t('Question code')}</div>
        <div>
          <FormControl
            key={`${focused?.qid}-question-code-attribute`}
            onChange={handleOnChange}
            value={inputValue}
            errorMessage={errorMessage}
            dataTestId="question-code"
            labelText="Question code"
            labelClass="text-nowrap d-block m-1"
            inputClass="d-block w-100"
            className="d-flex justify-content-between align-items-center w-100"
            noPermissionDisabled={true}
            update={update}
          />
        </div>
      </Form.Group>
      {errorMessage && (
        <div className={'text-nowrap d-block m-1'}>
          <Form.Text
            style={{ fontSize: '14px', fontWeight: '500' }}
            className="text-danger"
          >
            {errorMessage}
          </Form.Text>
        </div>
      )}
    </div>
  )
}
