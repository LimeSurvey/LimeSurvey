import React from 'react'

import { queryClient } from 'queryClient'
import {
  Toast,
  getSiteUrl,
  ACCESS_MODES,
  SURVEY_ARCHIVES_QUERY_KEY_PREFIX,
  ARCHIVE_BASE_TABLE_TOKENS,
} from 'helpers'
import { format } from 'util'
import { ExclamationIcon, GlobalIcon, LockIcon } from 'components/icons'

export const SURVEY_ARCHIVED_TOKENS_QUERY_KEY = `${SURVEY_ARCHIVES_QUERY_KEY_PREFIX}${ARCHIVE_BASE_TABLE_TOKENS}`

/**
 * Returns access mode options for the survey
 */
export const getSurveyAccessModeOptions = () => ({
  open: {
    label: t('Anyone with link'),
    key: 'open',
    value: ACCESS_MODES.OPEN_TO_ALL,
    description: t('Anyone with the link to this survey can access.'),
    icon: <GlobalIcon className="me-2" />,
  },
  closed: {
    label: t('Link with access code'),
    key: 'closed',
    value: ACCESS_MODES.CLOSED,
    description: t(
      'Only participants with the link and access code can access.'
    ),
    icon: <LockIcon className="me-2" />,
  },
})

/**
 * Opens the participants page
 */
const navigateToParticipants = (surveyId) => {
  const tokensListCorePath = getSiteUrl(
    `/admin/tokens/sa/index/surveyid/${surveyId}`
  )
  window.open(tokensListCorePath, '_self')
}

const getAccessModeChangeMessage = ({
  survey,
  isSwitchingToClosed,
  isSwitchingToOpen,
  hasAccessTokens,
  hasArchivedAccessTokens,
}) => {
  const surveyId = survey.sid
  const hasAccessTokensTables = survey.hasTokens

  if (
    isSwitchingToClosed &&
    !hasAccessTokensTables &&
    hasArchivedAccessTokens
  ) {
    const message = format(
      t(
        'You do not have participants yet. Create or restore archived participants under %sparticipants%s'
      ),
      '<span class="grape-link">',
      '</span>'
    )

    return (
      <p
        data-testid="toast-no-participants-closed-mode"
        dangerouslySetInnerHTML={{ __html: message }}
        onClick={(e) => {
          if (e.target.classList.contains('grape-link')) {
            navigateToParticipants(surveyId)
          }
        }}
      />
    )
  } else if (isSwitchingToOpen && hasAccessTokens) {
    const message = format(
      t(
        'You still have a participants list for your survey. You can remove it under %sparticipants%s'
      ),
      '<span class="grape-link">',
      '</span>'
    )

    return (
      <p
        data-testid="toast-open-mode-with-participants"
        dangerouslySetInnerHTML={{ __html: message }}
        onClick={(e) => {
          if (e.target.classList.contains('grape-link')) {
            navigateToParticipants(surveyId)
          }
        }}
      />
    )
  }

  return null
}

const checkHasAccessTokens = () => {
  const surveyTokens =
    queryClient.getQueryData([SURVEY_ARCHIVED_TOKENS_QUERY_KEY]) || {}
  return Object.values(surveyTokens).some((token) => {
    const { types = [], timestamp = 0, hastokens = false } = token
    return types.length === 0 && timestamp === 0 && hastokens
  })
}

const checkHasAccessArchivedTokens = () => {
  const surveyArchivedTokens =
    queryClient.getQueryData([SURVEY_ARCHIVED_TOKENS_QUERY_KEY]) || {}
  return Object.values(surveyArchivedTokens).some((token) => {
    const { types = [], count = 0 } = token
    return types.includes(ARCHIVE_BASE_TABLE_TOKENS) && count > 0
  })
}

/**
 * Handles change of survey access mode
 */
export const handleSurveyAccessModeChange = ({
  survey,
  currentSurveyAccessMode,
  newAccessMode,
  onSurveyAccessModeChange,
  setSelectedAccessMode,
  createBufferOperation,
  addToBuffer,
  update,
}) => {
  const isSwitchingToClosed = newAccessMode.value === ACCESS_MODES.CLOSED
  const isSwitchingToOpen = newAccessMode.value === ACCESS_MODES.OPEN_TO_ALL
  const hasAccessTokens = checkHasAccessTokens()
  const hasArchivedAccessTokens = checkHasAccessArchivedTokens()

  if (!newAccessMode || newAccessMode.value === currentSurveyAccessMode) return

  onSurveyAccessModeChange(newAccessMode.value)
  setSelectedAccessMode(newAccessMode.key)

  const operation = createBufferOperation(survey.sid).accessMode().update({
    accessMode: newAccessMode.value,
  })

  addToBuffer(operation)
  survey.access_mode = newAccessMode.value

  const message = getAccessModeChangeMessage({
    survey,
    isSwitchingToClosed,
    isSwitchingToOpen,
    hasAccessTokens,
    hasArchivedAccessTokens,
  })

  if (isSwitchingToClosed && !survey.hasTokens) {
    survey.hasTokens = true
  } else if (isSwitchingToOpen && !hasAccessTokens) {
    survey.hasTokens = false
  }

  update({ ...survey })

  if (message) {
    Toast({
      message,
      'position': 'top-center',
      'className': 'generic-toast yellow-left-mark mt-2',
      'leftIcon': (
        <ExclamationIcon className="fill-current text-white rounded-circle bg-dark me-1" />
      ),
      'data-testid': 'toast-access-mode-changed',
    })
  }
}
