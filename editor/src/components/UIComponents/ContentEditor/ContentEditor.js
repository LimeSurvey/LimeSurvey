import React, { useEffect, useState } from 'react'

import { useAppState } from 'hooks'
import { RemoveHTMLTagsInString, STATES } from 'helpers'
import { getTooltipMessages } from 'helpers/options'
import { TooltipContainer } from 'components'

import { TinyMCE } from './TinyMCE/TinyMCE'
import { ContentEditable } from './ContentEditable'

export const ContentEditor = ({
  value = '',
  placeholder = '',
  className = '',
  update = () => {},
  onFocus = () => {},
  onBlur = () => {},
  onClick = () => {},
  setErrors = () => {},
  onKeyDown = () => {},
  style = {},
  testValidation,
  id,
  focus = false,
  disabled = false,
  replaceVariables = false,
  editorRef,
  noPermissionDisabled = false,
  noAccessDisabled = false,
  showToolTip = true,
  toolTipPlacement = 'top',
  testId = 'content-editor',
  showToolbar = false,
  ...data
}) => {
  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )

  const editorDisabled =
    disabled ||
    (!hasSurveyUpdatePermission && noPermissionDisabled) ||
    noAccessDisabled
  // the purpose of using a state variable is to be able to replace the variables without changing the state.
  // changing the state will make us lose the original value.
  const [valueIsEmpty, setValueIsEmpty] = useState(
    !RemoveHTMLTagsInString(value).trim()
  )

  const toolTip =
    (!hasSurveyUpdatePermission && noPermissionDisabled) || noAccessDisabled
      ? getTooltipMessages().NO_PERMISSION
      : ''

  const handleOnClick = () => {
    onClick()
  }

  const handleFocus = (e) => {
    onFocus(e)
  }

  const handleBlur = (e) => {
    onBlur(e)

    const errors = getValidationErrors(RemoveHTMLTagsInString(value))

    setErrors(errors)
  }

  const getValidationErrors = (questionTitle) => {
    if (!testValidation) {
      return []
    }

    const validationErrors = testValidation(questionTitle)

    return validationErrors?.error
  }

  const handleOnChange = (changeValue) => {
    setValueIsEmpty(!RemoveHTMLTagsInString(value).trim())

    if (setErrors && !focus) {
      const errors = getValidationErrors(RemoveHTMLTagsInString(changeValue))
      setErrors(errors)
    }

    if (changeValue == value) {
      return
    }

    update(changeValue)
  }

  useEffect(() => {
    if (!setErrors || focus) {
      return
    }

    const errors = getValidationErrors(RemoveHTMLTagsInString(value))
    setErrors(errors)
  }, [])

  return (
    <div
      ref={editorRef}
      style={style}
      onClick={handleOnClick}
      className={`content-editor ${className}`}
      id={id}
    >
      <TooltipContainer
        tip={toolTip}
        showTip={editorDisabled && showToolTip}
        placement={toolTipPlacement}
      >
        {showToolbar ? (
          <TinyMCE
            testId={testId}
            disabled={editorDisabled}
            onBlur={handleBlur}
            onFocus={handleFocus}
            handleOnChange={handleOnChange}
            placeholder={placeholder}
            value={value}
            focus={focus}
            showToolbar={showToolbar}
            {...data}
          />
        ) : (
          <ContentEditable
            testId={testId}
            disabled={editorDisabled}
            onBlur={handleBlur}
            onFocus={handleFocus}
            handleOnChange={handleOnChange}
            placeholder={placeholder}
            value={value}
            replaceVariables={replaceVariables}
            focus={focus}
            onKeyDown={onKeyDown}
            showToolbar={showToolbar}
            valueIsEmpty={valueIsEmpty}
          />
        )}
      </TooltipContainer>
    </div>
  )
}

export const contentEditorName = ContentEditor.name
