import React, { useCallback } from 'react'

import { Button } from 'components/UIComponents'
import { SwalAlert } from 'helpers'

import SurveyStatusSwitchPopupResponsesSelect from './SurveyStatusSwitchPopupResponsesSelect'

const SurveyStatusSwitchPopupFooter = ({
  confirmButtonAction,
  confirmButtonActive,
  responseTableSelectionOptions,
  isResponsesTableSelectDisabled,
}) => {
  const handlePopupCancel = useCallback(() => SwalAlert.close(), [])

  return (
    <div className="survey-status-switch-popup-footer">
      {responseTableSelectionOptions && (
        <SurveyStatusSwitchPopupResponsesSelect
          isDisabled={isResponsesTableSelectDisabled}
          responseTableSelectionOptions={responseTableSelectionOptions}
        />
      )}
      <div className="survey-status-switch-popup-footer-right">
        <Button
          variant="secondary"
          className="survey-status-switch-popup-cancelBtn"
          onClick={handlePopupCancel}
        >
          {t('Cancel')}
        </Button>
        <Button
          className="survey-status-switch-popup-confirmBtn"
          disabled={!confirmButtonActive}
          onClick={confirmButtonAction}
        >
          {t('Continue')}
        </Button>
      </div>
    </div>
  )
}

export default SurveyStatusSwitchPopupFooter
