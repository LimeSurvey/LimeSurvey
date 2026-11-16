import React, { useCallback, useState } from 'react'
import classNames from 'classnames'

import { SwalAlert } from 'helpers'

import { SURVEY_STATUS_SWITCH_TYPES } from './SurveyStatusSwitchConfig'
import SurveyStatusSwitchPopupHeader from './SurveyStatusSwitchPopupHeader'
import SurveyStatusSwitchPopupCard from './SurveyStatusSwitchPopupCard'
import SurveyStatusSwitchPopupFooter from './SurveyStatusSwitchPopupFooter'

const SurveyStatusSwitchPopup = ({
  title,
  getCards,
  onConfirm,
  responseTableSelectionOptions,
  surveyIsExpired = false,
  navigateToPublication = () => {},
}) => {
  const [choice, setChoice] = useState(null)

  const onNavigateToPublicationClick = useCallback(() => {
    navigateToPublication()
    SwalAlert.close()
  }, [navigateToPublication])

  const cards = getCards({
    selectedChoice: choice,
    navigateToPublication: onNavigateToPublicationClick,
  })

  const handleAction = useCallback(() => {
    if (onConfirm && choice) {
      onConfirm(choice)
      SwalAlert.close()
    }
  }, [choice, onConfirm])

  const isCardDisabled = useCallback(
    (card) =>
      card.choice === SURVEY_STATUS_SWITCH_TYPES.PAUSE && surveyIsExpired,
    [surveyIsExpired]
  )

  const handleChoiceSelection = (card) => {
    const isDisabled = isCardDisabled(card)
    if (isDisabled) {
      return
    }
    setChoice(card.choice)
  }
  return (
    <div className="survey-status-switch-popup-container">
      <SurveyStatusSwitchPopupHeader titleElement={title} />
      <div className="survey-status-switch-popup-content">
        {cards.map((card, index) => (
          <div
            key={index}
            className={classNames('survey-status-switch-popup-card', {
              'survey-status-switch-popup-card--selected':
                choice === card.choice,
              'survey-status-switch-popup-card--unselected':
                choice !== card.choice,
              'cursor-pointer': !isCardDisabled(card),
            })}
            onClick={() => handleChoiceSelection(card)}
          >
            <SurveyStatusSwitchPopupCard
              elements={card.elements}
              isDisabled={isCardDisabled(card)}
            />
          </div>
        ))}
      </div>
      <SurveyStatusSwitchPopupFooter
        isResponsesTableSelectDisabled={
          choice !== SURVEY_STATUS_SWITCH_TYPES.KEEP_RESPONSES
        }
        responseTableSelectionOptions={responseTableSelectionOptions}
        confirmButtonActive={choice !== null}
        confirmButtonAction={handleAction}
      />
    </div>
  )
}

export default SurveyStatusSwitchPopup
