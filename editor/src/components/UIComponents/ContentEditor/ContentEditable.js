import { useEffect, useRef, useState } from 'react'
import ReactContentEditable from 'react-contenteditable'

import { useAppState } from 'hooks'
import {
  RemoveHTMLTagsInString,
  removePlaceholderBadges,
  ReplaceQuestionCodesWithAnswers,
  STATES,
  wrapPlaceholdersInBadges,
} from 'helpers'
import { PluginSlot } from 'plugins/PluginSlot'
import { PLUGIN_SLOTS } from 'plugins/slots'

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
    const parsedValue = RemoveHTMLTagsInString(removePlaceholderBadges(value), [
      'br',
      'p',
    ])
    setQuestionTitle(parsedValue)
    handleOnChange(parsedValue)
  }

  const handleFocus = () => {
    setIsFocused(true)
    // Show the raw placeholder text while editing, so the badges don't get in the way.
    setQuestionTitle(removePlaceholderBadges(questionTitle?.toString() ?? ''))
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

    setQuestionTitle(wrapPlaceholdersInBadges(title?.toString() ?? ''))
  }, [value, codeToQuestion, isFocused])

  useEffect(() => {
    if (focus && inputRef.current) {
      inputRef.current?.focus()
    }
  }, [focus])

  return (
    <>
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
      <PluginSlot
        slotName={PLUGIN_SLOTS.CONTENT_EDITOR}
        value={value}
        onChange={onChange}
        focused={isFocused}
      />
    </>
  )
}
