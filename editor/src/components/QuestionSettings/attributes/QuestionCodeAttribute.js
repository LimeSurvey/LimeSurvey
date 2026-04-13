import { useEffect, useState } from 'react'

import { useAppState, useFocused } from 'hooks'
import { TestValidation } from '../../Survey/Questions/QuestionCodeSchema'
import { STATES } from '../../../helpers'
import { Form, FormControl } from 'react-bootstrap'
import { showErrorMessage } from '../../ConditionDesigner/utils/conditionAlertHelpers'
import { ExclamationMark } from '../../icons'
// note: make it faster using debounce ... read about it.
export const QuestionCodeAttribute = ({ value, update, disabled = false }) => {
  const [codeToQuestion, setCodeToQuestion] = useAppState(
    STATES.CODE_TO_QUESTION,
    {}
  )
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

    let newErrorMessage = ''
    if (value) {
      newErrorMessage = TestValidation(value.toUpperCase()).error
        ? t('Only letters and numbers are allowed.')
        : ''
    }
    if (codeExist) {
      newErrorMessage = t('Question codes must be unique.')
    }

    setErrorMessage(newErrorMessage)
    return newErrorMessage
  }

  const updateCodeToQuestion = (oldValue, newValue) => {
    const newCodeToQuestion = { ...codeToQuestion }
    newCodeToQuestion[newValue] = newCodeToQuestion[oldValue]
    newCodeToQuestion[newValue].question.title = newValue
    delete newCodeToQuestion[oldValue]
    setCodeToQuestion(newCodeToQuestion)
  }

  const handleOnChange = ({ target: { value } }) => {
    const oldValue = inputValue
    setInputValue(value.toUpperCase())
    const currentErrorMessage = validateCode(value)
    if (currentErrorMessage === '') {
      update(value)
      if (oldValue !== value) {
        updateCodeToQuestion(oldValue, value)
      }
    } else {
      showErrorMessage(currentErrorMessage, 'top-center')
    }
  }

  return (
    <div>
      <Form.Group className="d-flex qe-input-group align-content-center align-items-center">
        <div className="ui-label w-50 flex-grow-1 align-items-end">
          {t('Question code')}
          {errorMessage && (
            <ExclamationMark className="m-1 question-code-icon-error" />
          )}
        </div>
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
            disabled={disabled}
          />
        </div>
      </Form.Group>
    </div>
  )
}
