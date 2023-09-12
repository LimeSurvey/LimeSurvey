import { useEffect, useState } from 'react'

import { RemoveHTMLTagsInString } from 'helpers'

import { RichEditor } from './RichEditor'
import { ContentEditable } from './ContentEditable'

export const ContentEditor = ({
  value = '',
  placeholder = '',
  className = '',
  disabled = false,
  useRichTextEditor = false,
  update = () => {},
  onFocus = () => {},
  onBlur = () => {},
  onClick = () => {},
  setErrors = () => {},
  style = {},
  contentEditableRef,
  testValidation,
  id,
  focus = false,
  language,
  replaceVariables = false,
}) => {
  // the purpose of using a state variable is to be able to replace the variables without changing the state.
  // changing the state will make us lose the original value.
  const [showToolbar, setShowToolbar] = useState(false)
  const [showRichTextEditor, setShowRichTextEditor] =
    useState(useRichTextEditor)

  const handleOnClick = () => {
    onClick()
  }

  const handleFocus = (e) => {
    onFocus(e)
    setShowToolbar(true)
  }

  const handleBlur = (e) => {
    onBlur(e)
    setShowToolbar(false)

    if (focus && e?.target !== undefined) {
      const errors = getValidationErrors(
        RemoveHTMLTagsInString(e.target.innerHTML)
      )

      setErrors(errors)
    }
  }

  const getValidationErrors = (questionTitle) => {
    if (!testValidation) {
      return []
    }

    const validationErrors = testValidation(questionTitle)

    return validationErrors?.error
  }

  const handleOnChange = (value) => {
    if (setErrors) {
      const errors = getValidationErrors(RemoveHTMLTagsInString(value))
      setErrors(errors)
    }

    if (update) {
      update(value)
    }
  }

  useEffect(() => {
    if (focus && contentEditableRef?.current?.focus) {
      setTimeout(() => {
        contentEditableRef.current.focus()
      }, 0)
    }

    if (!setErrors || focus) {
      return
    }

    const errors = getValidationErrors(RemoveHTMLTagsInString(value))
    setErrors(errors)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  useEffect(() => {
    setShowRichTextEditor(false)
    setTimeout(() => {
      setShowRichTextEditor(true)
    }, 0)
  }, [language])

  return (
    <div
      style={style}
      onClick={handleOnClick}
      className={`content-editor ${className}`}
      id={id}
    >
      {useRichTextEditor && showRichTextEditor ? (
        <RichEditor
          disabled={disabled}
          focus={focus}
          onBlur={handleBlur}
          onFocus={handleFocus}
          handleOnChange={handleOnChange}
          placeholder={placeholder}
          showToolbar={showToolbar}
          value={value}
          replaceVariables={replaceVariables}
        />
      ) : (
        <ContentEditable
          contentEditableRef={contentEditableRef}
          disabled={disabled}
          onBlur={handleBlur}
          onFocus={handleFocus}
          handleOnChange={handleOnChange}
          placeholder={placeholder}
          value={value}
          replaceVariables={replaceVariables}
        />
      )}
    </div>
  )
}
