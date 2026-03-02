import React, { useEffect } from 'react'
import { Toaster } from 'react-hot-toast'

import { Toast } from 'helpers'
import { useErrors } from 'hooks'
import { ExclamationMark, XIcon } from 'components/icons'
import { FeedbackPopup } from 'components/feedback'

import { SurveyLogicProvider } from './SurveyLogicProvider'

export const SurveyWorkspace = ({ children }) => {
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
        leftIcon: <ExclamationMark />,
        rightIcon: <XIcon />,
      })
    }
    clearErrorMessages()
  }, [errorMessages?.length])

  return (
    <SurveyLogicProvider>
      <Toaster />
      <FeedbackPopup />
      {children}
    </SurveyLogicProvider>
  )
}
