import { useEffect, useMemo, useState } from 'react'
import { Button, Card, Form } from 'react-bootstrap'

import { useFeedbackForm, useCookieFeedbackStore } from 'hooks'
import {
  FEEDBACK_TYPES,
  FeedbackEventHandler,
  getFeedbackConfigs,
} from 'helpers'
import { CloseIcon } from 'components/icons'

export const FeedbackPopup = () => {
  const { updateFeedbackData } = useCookieFeedbackStore()
  const { showFeedbackForm, trackInitialLoad, shouldShowFeedback } =
    useFeedbackForm()

  const [isFeedbackPopupVisible, setFeedbackPopupVisible] = useState(false)
  const [currentFeedbackType, setCurrentFeedbackType] = useState(null)

  // check if general feedback popup should be shown
  useEffect(() => {
    trackInitialLoad(FEEDBACK_TYPES.GENERAL)

    const checkAndShowFeedback = () => {
      if (
        shouldShowFeedback(FEEDBACK_TYPES.GENERAL) &&
        !isFeedbackPopupVisible &&
        !currentFeedbackType
      ) {
        setCurrentFeedbackType(FEEDBACK_TYPES.GENERAL)
        setFeedbackPopupVisible(true)
        updateFeedbackData(FEEDBACK_TYPES.GENERAL, {
          previouslyShown: new Date().toISOString(),
        })
      }
    }

    checkAndShowFeedback()

    const intervalId = setInterval(checkAndShowFeedback, 60000) // Check every minute

    return () => clearInterval(intervalId)
  }, [
    isFeedbackPopupVisible,
    currentFeedbackType,
    shouldShowFeedback,
    updateFeedbackData,
  ])

  // Listen for manually triggered feedback events
  useEffect(() => {
    const onFeedbackRequest = (event) => {
      const { feedbackType } = event.detail
      if (!shouldShowFeedback(feedbackType)) return

      setCurrentFeedbackType(feedbackType)
      setFeedbackPopupVisible(true)
    }

    const unsubscribe =
      FeedbackEventHandler.onFeedbackRequested(onFeedbackRequest)
    return unsubscribe
  }, [shouldShowFeedback])

  const currentConfig = useMemo(
    () => getFeedbackConfigs()[currentFeedbackType],
    [currentFeedbackType]
  )

  const handleClose = () => {
    setFeedbackPopupVisible(false)
    setCurrentFeedbackType(null)
  }

  const handleOK = () => {
    showFeedbackForm(currentFeedbackType)
    updateFeedbackData(currentFeedbackType, { feedbackGiven: true })
    handleClose()
  }

  const handleDontAskAgain = (e) => {
    updateFeedbackData(currentFeedbackType, { dontAskAgain: e.target.checked })
  }

  if (!currentFeedbackType) return null

  return (
    <Card
      style={{
        position: 'fixed',
        bottom: 60,
        right: 10,
        zIndex: 1060,
        width: 600,
        display: isFeedbackPopupVisible ? 'block' : 'none',
      }}
    >
      <Card.Body className="relative">
        <div className="d-flex justify-content-between">
          <Card.Title as="h2" className="title">
            {currentConfig.title}
          </Card.Title>
          <div className="feedback-popup-close-button" onClick={handleClose}>
            <CloseIcon
              className="text-black fill-current"
              width={28}
              height={28}
            />
          </div>
        </div>
        <div className="mb-4">{currentConfig.description}</div>
        <div className="feedback-popup-checkbox-container">
          <Form.Check
            label={t("Don't ask again")}
            type="checkbox"
            onClick={handleDontAskAgain}
          />
        </div>
        <div className="d-flex justify-content-end gap-2 mt-2">
          <Button
            variant="outline-dark"
            className="feedback-popup-buttons btn-lg"
            onClick={handleClose}
          >
            {t('Not now')}
          </Button>
          <Button
            variant="primary"
            className="feedback-popup-buttons btn-lg"
            onClick={handleOK}
          >
            {currentConfig.primaryCTA || t('OK')}
          </Button>
        </div>
      </Card.Body>
    </Card>
  )
}
