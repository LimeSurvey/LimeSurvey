import React from 'react'

import { htmlPopup, SwalAlert, Toast } from 'helpers'
import { CheckIcon, ExclamationIcon, ExclamationMark } from 'components/icons'

import {
  ConditionOverwriteConfirmation,
  ConditionScriptOverwriteConfirmation,
  DeleteScenarioOverlay,
  ResetAllConditionsOverlay,
  UnsavedChangesOverlay,
} from '../Overlays'

export const showSuccessMessage = (
  message = t('Condition applied successfully')
) => {
  Toast({
    message: message,
    position: 'bottom-center',
    className: 'generic-toast green-left-mark',
    leftIcon: (
      <CheckIcon
        className={`fill-current text-white rounded-circle bg-dark me-1`}
      />
    ),
  })
}

export const showWarningMessage = (message, position = 'bottom-right') => {
  if (!message) return

  Toast({
    message: message,
    position: position,
    className: 'generic-toast yellow-left-mark',
    leftIcon: <ExclamationIcon />,
  })
}

export const showErrorMessage = (message, position = 'bottom-right') => {
  if (!message) return

  Toast({
    message: message,
    position: position,
    className: 'generic-toast error-left-mark',
    leftIcon: <ExclamationMark className="mb-1 error-fill" />,
  })
}

/**
 * Class that marks every condition popup. The popups are SweetAlert modals
 * outside of the React tree, so they stay open when the panel that opened them
 * unmounts. QuestionSettings closes them in one place (closeConditionPopups)
 * when the focused question changes, is gone, or its panel unmounts.
 */
export const CONDITION_POPUP_CLASS = 'condition-popup'

/**
 * Opens a condition popup. Open every condition popup through this function,
 * so it gets CONDITION_POPUP_CLASS and is closed with the question's panel.
 * @param {Object} options htmlPopup options.
 * @returns {Promise} The SweetAlert result.
 */
export const showConditionPopup = (options) =>
  htmlPopup({
    title: '',
    showCloseButton: true,
    width: '807px',
    ...options,
    popupClass: CONDITION_POPUP_CLASS,
  })

/**
 * Closes the visible popup if it is a condition popup, including one that
 * replaced another (e.g. a confirmation opened from the editor popup).
 * Other popups are left open.
 * @returns {void}
 */
export const closeConditionPopups = () => {
  // getPopup() only returns an element while a popup is shown
  if (SwalAlert.getPopup()?.classList.contains(CONDITION_POPUP_CLASS)) {
    SwalAlert.close()
  }
}

export const showUnsavedChangesOverlay = (onIgnoreChanges, message = null) => {
  showConditionPopup({
    html: (
      <UnsavedChangesOverlay
        onIgnoreChanges={() => {
          onIgnoreChanges()
        }}
        message={message}
      />
    ),
  })
}

export const showDeleteScenarioOverlay = (scenarioId, onConfirmDelete) => {
  showConditionPopup({
    html: (
      <DeleteScenarioOverlay
        scenarioId={scenarioId}
        onConfirmDelete={() => {
          onConfirmDelete(scenarioId)
        }}
      />
    ),
  })
}

export const showResetAllConditionsOverlay = ({ onConfirmDelete }) => {
  showConditionPopup({
    html: <ResetAllConditionsOverlay onConfirmDelete={onConfirmDelete} />,
  })
}

export const showConditionOverwriteConfirmationOverlay = ({
  onConfirm,
  onCancel = () => {},
}) => {
  showConditionPopup({
    html: (
      <ConditionOverwriteConfirmation
        onConfirmChanges={onConfirm}
        onCancelChanges={onCancel}
      />
    ),
  })
}

export const showConditionScriptOverwriteConfirmationOverlay = ({
  script,
  onConfirm,
  onCancel = () => {},
}) => {
  showConditionPopup({
    html: (
      <ConditionScriptOverwriteConfirmation
        script={script}
        onConfirmChanges={onConfirm}
        onCancelChanges={onCancel}
      />
    ),
  })
}
