import { useCallback } from 'react'
import { useCookies } from 'react-cookie'

import { cookieExpiresInterval, getFeedbackConfigs } from 'helpers'

export const useCookieFeedbackStore = () => {
  const feedbackConfigs = getFeedbackConfigs()
  const cookieKeys = Object.values(feedbackConfigs).map(
    (config) => config.cookieKey
  )
  const [cookies, setCookie] = useCookies(cookieKeys)

  const getFeedbackData = useCallback(
    (feedbackType) => {
      const config = feedbackConfigs[feedbackType]
      return (
        cookies[config.cookieKey] ?? {
          initialLoad: null,
          previouslyShown: null,
          dontAskAgain: false,
          feedbackGiven: false,
        }
      )
    },
    [cookies]
  )

  const setFeedbackData = useCallback(
    (feedbackType, setDataCallback) => {
      const config = feedbackConfigs[feedbackType]
      const currentData = getFeedbackData(feedbackType)
      setCookie(config.cookieKey, setDataCallback(currentData), {
        path: '/',
        expires: new Date(Date.now() + cookieExpiresInterval),
      })
    },
    [getFeedbackData, setCookie]
  )

  const updateFeedbackData = useCallback(
    (feedbackType, values) => {
      setFeedbackData(feedbackType, (prevState) => ({
        ...prevState,
        ...values,
      }))
    },
    [setFeedbackData]
  )

  return {
    getFeedbackData,
    updateFeedbackData,
  }
}
