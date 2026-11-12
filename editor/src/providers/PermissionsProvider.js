import { useEffect, useState } from 'react'
import { useParams, useLocation } from 'react-router-dom'

import { useAppState, useAuth, useUserService } from 'hooks'
import { STATES } from 'helpers'

export const PermissionsProvider = ({ children }) => {
  const auth = useAuth()
  const { surveyId } = useParams()
  const { pathname } = useLocation()
  const userService = useUserService()
  const [hasSurveyReadPermission, setHasSurveyReadPermission] = useState(false)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [hasSurveyUpdatePermission, setHasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION,
    false
  )
  const [hasResponsesReadPermission, setHasResponsesReadPermission] =
    useAppState(STATES.HAS_RESPONSES_READ_PERMISSION, false)
  const [hasResponsesUpdatePermission, setHasResponsesUpdatePermission] =
    useAppState(STATES.HAS_RESPONSES_UPDATE_PERMISSION, false)
  const [permissions, setPermissions] = useAppState(
    STATES.USER_PERMISSIONS,
    null
  )

  // Fetch permissions if we have auth token but no permissions yet
  useEffect(() => {
    const controller = new AbortController()
    setPermissions(null)
    setError(null)
    userService
      .getUserPermissions(surveyId, controller.signal)
      .then(({ permissions: { global, survey, effective } }) => {
        if (controller.signal.aborted) {
          return
        }
        setPermissions({ global, survey, effective })
      })
      .catch(() => {
        if (controller.signal.aborted) {
          return
        }
        setError(
          t('Failed to load permissions. Please try again or contact support.')
        )
        setLoading(false)
      })

    return () => controller.abort()
  }, [auth?.token, surveyId])

  useEffect(() => {
    if (!permissions) {
      // Clear derived flags when permissions are reset
      setHasSurveyReadPermission(false)
      setHasSurveyUpdatePermission(false)
      setHasResponsesReadPermission(false)
      setHasResponsesUpdatePermission(false)

      return
    }

    // Resolved by the backend, incl. survey group inheritance
    const { effective } = permissions
    setHasSurveyReadPermission(!!effective?.surveyRead)
    setHasSurveyUpdatePermission(!!effective?.surveyUpdate)
    setHasResponsesReadPermission(!!effective?.responsesRead)
    setHasResponsesUpdatePermission(!!effective?.responsesUpdate)

    setLoading(false)
  }, [
    permissions,
    setHasSurveyReadPermission,
    setHasSurveyUpdatePermission,
    setHasResponsesReadPermission,
    setHasResponsesUpdatePermission,
  ])

  if (loading || !permissions) {
    return (
      <>
        <div className="d-flex vh-100 flex-column justify-content-center align-items-center">
          <span
            style={{ width: 48, height: 48 }}
            className="loader mb-4"
          ></span>
          <h1>{t('Checking permissions...')}</h1>
        </div>
      </>
    )
  }

  if (error) {
    return (
      <div className="d-flex vh-100 flex-column justify-content-center align-items-center text-danger">
        <h1>{error}</h1>
      </div>
    )
  }

  // Check if we're on the responses route th
  const isResponsesRoute = pathname.startsWith('/responses/')

  if (
    isResponsesRoute &&
    (hasResponsesReadPermission || hasResponsesUpdatePermission)
  ) {
    return children
  }

  if (
    hasSurveyReadPermission ||
    hasSurveyUpdatePermission ||
    surveyId === '-1'
  ) {
    return children
  }

  return (
    <h1 className="d-flex vh-100 justify-content-center align-items-center">
      {t(`You don't have permission to access this page.`)}
    </h1>
  )
}
