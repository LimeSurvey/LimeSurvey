import { useCallback } from 'react'

import { SwalAlert } from 'helpers'
import { CloseIcon, QuestionMarkIcon } from 'components/icons'
import { Button } from 'components/UIComponents'

// TODO: add see details action logic
const SurveyStatusSwitchPopupHeader = ({ titleElement }) => {
  const handlePopupClose = useCallback(() => SwalAlert.close(), [])

  return (
    <div className="survey-status-switch-popup-header">
      <p className="survey-status-switch-popup-title">{titleElement}</p>
      <div className="survey-status-switch-popup-header-right">
        <Button
          className="survey-status-switch-popup-tooltip invisible"
          variant="link"
          style={{ padding: 0 }}
        >
          <div className="survey-status-switch-popup-tooltip-icon">
            <QuestionMarkIcon />
          </div>
          <p className="survey-status-switch-popup-tooltip-text">
            {t('See details')}
          </p>
        </Button>
        <Button
          variant="link"
          style={{ padding: 0 }}
          onClick={handlePopupClose}
        >
          <CloseIcon className="text-black fill-current" />
        </Button>
      </div>
    </div>
  )
}

export default SurveyStatusSwitchPopupHeader
