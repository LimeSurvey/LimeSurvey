import { useEffect, useState } from 'react'
import ReactContentEditable from 'react-contenteditable'

import { useAppState } from 'hooks'
import {
  RemoveHTMLTagsInString,
  ReplaceQuestionCodesWithAnswers,
} from 'helpers'

export const ContentEditable = ({
  onFocus,
  onBlur,
  disabled,
  handleOnChange,
  contentEditableRef,
  value,
  placeholder,
  replaceVariables,
}) => {
  const [codeToQuestion] = useAppState('codeToQuestion', {})
  const [questionTitle, setQuestionTitle] = useState(value)
  const [isFocused, setIsFocused] = useState(false)
  const [isHovered, setIsHovered] = useState(false)

  const handleFocus = () => {
    setIsFocused(true)
    onFocus()
  }

  const handleBlur = () => {
    setIsFocused(false)
    onBlur()
  }

  const handleMouseEnter = () => {
    setIsHovered(true)
  }

  const handleMouseLeave = () => {
    setIsHovered(false)
  }

  useEffect(() => {
    if (isFocused || !replaceVariables) {
      setQuestionTitle(value)
      return
    }

    const question = ReplaceQuestionCodesWithAnswers(value, codeToQuestion)
    const questionWithoutHTML = RemoveHTMLTagsInString(question)

    setQuestionTitle(questionWithoutHTML ? question : '')
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [value, codeToQuestion, isFocused])

  return (
    <ReactContentEditable
      onMouseEnter={handleMouseEnter}
      onMouseLeave={handleMouseLeave}
      onFocus={handleFocus}
      onBlur={handleBlur}
      innerRef={contentEditableRef}
      className={`content-editable`}
      disabled={disabled}
      html={isFocused || isHovered ? value : questionTitle}
      onChange={({ target: { value } }) => handleOnChange(value)}
      data-placeholder={placeholder}
    />
  )
}
