import { useMemo, useCallback } from 'react'

import { dayJsHelper, getFeedbackConfigs, FEEDBACK_TYPES } from 'helpers'
import { useAuth, useCookieFeedbackStore } from 'hooks'

export const useFeedbackForm = () => {
  const { userId } = useAuth()
  const { getFeedbackData, updateFeedbackData } = useCookieFeedbackStore()

  const feedbackConfigs = useMemo(() => getFeedbackConfigs(), [])

  const urlParams = useMemo(() => {
    return new URLSearchParams({
      domain: window.location.hostname,
      userId,
    })
  }, [userId])

  const trackInitialLoad = useCallback(
    (feedbackType) => {
      const data = getFeedbackData(feedbackType)
      if (!data.initialLoad) {
        updateFeedbackData(feedbackType, {
          initialLoad: dayJsHelper().toISOString(),
        })
      }
    },
    [getFeedbackData, updateFeedbackData]
  )

  const shouldShowFeedback = useCallback(
    (feedbackType) => {
      const config = feedbackConfigs[feedbackType]
      const isAutomaticTrigger = config.triggerType === 'automatic'
      const data = getFeedbackData(feedbackType)

      if (!data || data.feedbackGiven || data.dontAskAgain) return false

      if (isAutomaticTrigger) {
        if (!data.initialLoad) return false

        const initialLoad = dayJsHelper(data.initialLoad)
        const delayThreshold = dayJsHelper().subtract(
          config.delayMins,
          'minute'
        )
        if (initialLoad > delayThreshold) return false

        if (!data.previouslyShown) return true

        const lastShown = dayJsHelper(data.previouslyShown)
        const intervalThreshold = dayJsHelper().subtract(
          config.intervalMinutes,
          'minute'
        )
        return lastShown < intervalThreshold
      }

      return true
    },
    [getFeedbackData]
  )

  const showFeedbackForm = useCallback(
    (feedbackType = FEEDBACK_TYPES.GENERAL) => {
      const config = feedbackConfigs[feedbackType]
      window.open(
        `https://survey.limesurvey.org/${config.surveyId}?${urlParams.toString()}`,
        '_blank'
      )
    },
    [urlParams]
  )

  return {
    trackInitialLoad,
    shouldShowFeedback,
    showFeedbackForm,
  }
}
