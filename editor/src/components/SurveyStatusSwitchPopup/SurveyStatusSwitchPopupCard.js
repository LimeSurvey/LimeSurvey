import React from 'react'

import SurveyStatusSwitchPopupCardElement from './SurveyStatusSwitchPopupCardElement'

const SurveyStatusSwitchPopupCard = ({ elements = [], isDisabled }) => {
  const elementsToShow = elements.filter((element) => {
    if (!isDisabled) {
      return !element.onlyDisabled
    }
    return true
  })

  return (
    <>
      {elementsToShow.map((element, index) => (
        <div key={index}>
          <SurveyStatusSwitchPopupCardElement
            Icon={element.Icon}
            label={element.label}
            isMainElement={element.isMainElement}
            isDisabled={isDisabled && !element.onlyDisabled}
          />
        </div>
      ))}
    </>
  )
}

export default SurveyStatusSwitchPopupCard
