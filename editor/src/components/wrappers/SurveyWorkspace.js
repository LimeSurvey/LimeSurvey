import React, { useEffect } from 'react'
import { useParams } from 'react-router-dom'
import { Toaster } from 'react-hot-toast'

import { Toast } from 'helpers'
import { useErrors, useEditorCompatibilityGuard } from 'hooks'
import { ExclamationMark, XIcon } from 'components/icons'
import { FeedbackPopup } from 'components/feedback'

import { SurveyLogicProvider } from './SurveyLogicProvider'

export const SurveyWorkspace = ({ children }) => {
  const { surveyId } = useParams()
  const isIncompatible = useEditorCompatibilityGuard(surveyId)
  const { errorMessages, clearErrorMessages } = useErrors()

  // Display Errors as Toasts
  useEffect(() => {
    // todo: get the errorMessages from the useErrors hook.
    if (!errorMessages?.length) {
      return
    }
    for (const error of errorMessages) {
      Toast({
        message: error,
        leftIcon: <ExclamationMark className="error-fill" />,
        rightIcon: <XIcon />,
      })
    }
    clearErrorMessages()
  }, [errorMessages?.length])

  // Stop rendering as soon as the theme is known to be unsupported, so the
  // survey does not show up in the editor while the redirect is in flight.
  // While the survey is still loading this is false and children render as
  // usual, keeping their own loading state.
  if (isIncompatible) {
    return null
  }

  return (
    <SurveyLogicProvider>
      <Toaster />
      <FeedbackPopup />
      {children}
    </SurveyLogicProvider>
  )
}
