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
    setPermissions(null)

    if (!auth?.token) {
      setLoading(false)
      return
    }

    // Only set loading to true when we actually need to fetch
    setLoading(true)
    userService
      .getUserPermissions()
      .then(({ permissions: { global, survey } }) => {
        setPermissions({ global, survey })
      })
      .catch(() => {
        setLoading(false)
      })
  }, [auth?.token])

  useEffect(() => {
    if (!permissions) {
      // Clear derived flags when permissions are reset
      setHasSurveyReadPermission(false)
      setHasSurveyUpdatePermission(false)
      setHasResponsesReadPermission(false)
      setHasResponsesUpdatePermission(false)

      // If we don't have auth token and no permissions, we're done loading
      if (!auth?.token) {
        setLoading(false)
      }

      return
    }

    const { global, survey: surveyPermissions } = permissions
    const isSuperAdmin =
      global?.superadmin?.read === true || global?.superadmin?.read === 1

    // Check if user is survey owner (has any permissions for this survey)
    const surveySpecificPermissions = surveyPermissions?.[surveyId]
    const isSurveyOwnerValue = !!surveySpecificPermissions

    // Get global permissions
    const globalSurveyRead =
      global?.surveys?.read === true || global?.surveys?.read === 1
    const globalSurveyUpdate =
      global?.surveys?.update === true || global?.surveys?.update === 1

    // Get survey-specific permissions (these have priority over global)
    const surveyRead =
      surveySpecificPermissions?.survey?.read === true ||
      surveySpecificPermissions?.survey?.read === 1
    const surveyUpdate =
      surveySpecificPermissions?.survey?.update === true ||
      surveySpecificPermissions?.survey?.update === 1
    const responsesRead =
      surveySpecificPermissions?.responses?.read === true ||
      surveySpecificPermissions?.responses?.read === 1
    const responsesUpdate =
      surveySpecificPermissions?.responses?.update === true ||
      surveySpecificPermissions?.responses?.update === 1
    const statisticsRead =
      surveySpecificPermissions?.statistics?.read === true ||
      surveySpecificPermissions?.statistics?.read === 1

    // Survey permissions: superadmin OR (survey-specific if exists, else global)
    // Local survey permissions have priority over global permissions
    const hasSurveyReadPermissionValue =
      isSuperAdmin || (isSurveyOwnerValue ? surveyRead : globalSurveyRead)
    const hasSurveyUpdatePermissionValue =
      isSuperAdmin || (isSurveyOwnerValue ? surveyUpdate : globalSurveyUpdate)

    // Response permissions: superadmin OR survey-specific OR global survey update
    // If you can update responses, you can also read them
    // Statistics.read also grants access to responses page (for viewing statistics)
    // Global survey update permission also grants response update permission
    const hasResponsesReadPermissionValue =
      isSuperAdmin ||
      responsesRead ||
      responsesUpdate ||
      statisticsRead ||
      globalSurveyUpdate
    const hasResponsesUpdatePermissionValue =
      isSuperAdmin || responsesUpdate || globalSurveyUpdate

    setHasSurveyReadPermission(hasSurveyReadPermissionValue)
    setHasSurveyUpdatePermission(hasSurveyUpdatePermissionValue)
    setHasResponsesReadPermission(hasResponsesReadPermissionValue)
    setHasResponsesUpdatePermission(hasResponsesUpdatePermissionValue)

    // Always set loading to false after processing permissions
    setLoading(false)
  }, [
    permissions,
    surveyId,
    auth?.token,
    setHasSurveyReadPermission,
    setHasSurveyUpdatePermission,
    setHasResponsesReadPermission,
    setHasResponsesUpdatePermission,
  ])

  if (loading) {
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

  // Check if we're on the responses route
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
      {t(`You don't have permissions to enter this page.`)}
    </h1>
  )
}
