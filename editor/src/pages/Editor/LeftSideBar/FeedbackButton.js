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
      <span className="cursor-pointer" onClick={() => showFeedbackForm()}>
        <FeedbackIcon />
      </span>
    </TooltipContainer>
  )
}

export default FeedbackButton
