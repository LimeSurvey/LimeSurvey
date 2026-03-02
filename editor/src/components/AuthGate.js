import { useAuth } from 'hooks'
import { useEffect } from 'react'
import { useParams } from 'react-router-dom'

import { URLS } from '../helpers'

// todo: discuss this with kevin .. about making this for authorized users and anonymous users.
export const AuthGate = ({ children }) => {
  const auth = useAuth()
  const { surveyId } = useParams()

  const isLoggedIn = !!auth && !!auth.isLoggedIn

  // React strict-mode can cause useEffect to run multiple times
  // - even when there are no changes in dependencies, to ensure we
  // - only redirect after the final call of the useEffect callback
  // - use setTimeout to delay executing our effect code until
  // - the final call of the useEffect callback
  useEffect(() => {
    const timeoutId = setTimeout(() => {
      if (auth && !isLoggedIn) {
        if (!surveyId) {
          window.location = URLS.ADMIN
        } else {
          window.location = URLS.SURVEY_OVERVIEW + '?surveyid=' + surveyId
        }
      }
    }, 1000)
    return () => clearTimeout(timeoutId)
  }, [auth])

  if (isLoggedIn) {
    return children
  }
}

export default AuthGate
