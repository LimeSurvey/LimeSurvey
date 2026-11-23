import React from 'react'

import { FeedbackIcon } from 'components/icons'
import { TooltipContainer } from 'components'
import { useFeedbackForm } from 'hooks'

export const FeedbackButton = () => {
  const { showFeedbackForm } = useFeedbackForm()

  return (
    <TooltipContainer
      offset={[0, 20]}
      placement="top"
      tip={t('Help us with your feedback')}
    >
      <button
        type="button"
        className="d-block p-0 border-0 bg-transparent"
        aria-label={t('Help us with your feedback')}
        onClick={() => showFeedbackForm()}
      >
        <FeedbackIcon />
      </button>
    </TooltipContainer>
  )
}

export default FeedbackButton
