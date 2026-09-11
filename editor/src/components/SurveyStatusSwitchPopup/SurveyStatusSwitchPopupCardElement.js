import React from 'react'
import classNames from 'classnames'

const SurveyStatusSwitchPopupCardElement = ({
  Icon,
  label,
  isMainElement,
  isDisabled,
}) => {
  return (
    <div
      className={classNames('survey-status-switch-popup-card-element', {
        'survey-status-switch-popup-card-element--primary': isMainElement,
        'survey-status-switch-popup-card-element--secondary': !isMainElement,
        'survey-status-switch-popup-card-element--disabled': isDisabled,
      })}
    >
      {Icon && (
        <div className="survey-status-switch-popup-card-element-icon">
          <Icon size={24} />
        </div>
      )}
      <span className="survey-status-switch-popup-card-element-label">
        {label}
      </span>
    </div>
  )
}

export default SurveyStatusSwitchPopupCardElement
