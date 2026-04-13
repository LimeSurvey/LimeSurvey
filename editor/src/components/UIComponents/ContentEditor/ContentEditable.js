import { useEffect, useRef, useState } from 'react'
import ReactContentEditable from 'react-contenteditable'

import { useAppState } from 'hooks'
import {
  RemoveHTMLTagsInString,
  ReplaceQuestionCodesWithAnswers,
  STATES,
} from 'helpers'

export const ContentEditable = ({
  onFocus,
  onBlur,
  disabled,
  handleOnChange,
  value,
  placeholder,
  replaceVariables,
  focus = false,
  onKeyDown,
  testId = '',
}) => {
  const [codeToQuestion] = useAppState(STATES.CODE_TO_QUESTION, {})
  const [questionTitle, setQuestionTitle] = useState(value)
  const [isFocused, setIsFocused] = useState(false)
  const inputRef = useRef(null)

  const onChange = (value) => {
    const parsedValue = RemoveHTMLTagsInString(value, ['br', 'p'])
    setQuestionTitle(parsedValue)
    handleOnChange(parsedValue)
  }

  const handleFocus = () => {
    setIsFocused(true)
    onFocus()
  }

  const handleBlur = () => {
    setIsFocused(false)
    onBlur()
  }

  useEffect(() => {
    if (isFocused) {
      return
    }

    const title = replaceVariables
      ? ReplaceQuestionCodesWithAnswers(value, codeToQuestion)
      : value

    setQuestionTitle(title)
  }, [value, codeToQuestion, isFocused])

  useEffect(() => {
    if (focus && inputRef.current) {
      inputRef.current?.focus()
    }
  }, [inputRef.current])

  return (
    <ReactContentEditable
      onFocus={handleFocus}
      onBlur={handleBlur}
      innerRef={inputRef}
      className={`content-editable`}
      disabled={disabled}
      html={questionTitle?.toString()}
      onChange={({ target: { value } }) => onChange(value)}
      data-placeholder={placeholder}
      autoFocus={focus}
      onKeyDown={onKeyDown}
      data-testid={testId}
    />
  )
}
