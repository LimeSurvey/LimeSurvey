import React from 'react'

import { htmlPopup, Toast } from 'helpers'
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
 * @param {Function} onIgnoreChanges called when the user discards the changes
 * @param {string|null} message
 * @param {object} options
 * @param {string} [options.ignoreButtonText] label of the discard button
 * @param {...object} [options.popupOptions] extra htmlPopup options
 * @returns {Promise} resolved when the popup is closed
 */
export const showUnsavedChangesOverlay = (
  onIgnoreChanges,
  message = null,
  { ignoreButtonText = null, ...popupOptions } = {}
) =>
  htmlPopup({
    html: (
      <UnsavedChangesOverlay
        onIgnoreChanges={() => {
          onIgnoreChanges()
        }}
        message={message}
        ignoreButtonText={ignoreButtonText}
      />
    ),
    title: '',
    showCloseButton: true,
    width: '807px',
    ...popupOptions,
  })

export const showDeleteScenarioOverlay = (scenarioId, onConfirmDelete) => {
  htmlPopup({
    html: (
      <DeleteScenarioOverlay
        scenarioId={scenarioId}
        onConfirmDelete={() => {
          onConfirmDelete(scenarioId)
        }}
      />
    ),
    title: '',
    showCloseButton: true,
    width: '807px',
  })
}

export const showResetAllConditionsOverlay = ({ onConfirmDelete }) => {
  htmlPopup({
    html: <ResetAllConditionsOverlay onConfirmDelete={onConfirmDelete} />,
    title: '',
    showCloseButton: true,
    width: '807px',
  })
}

export const showConditionOverwriteConfirmationOverlay = ({
  onConfirm,
  onCancel = () => {},
}) => {
  htmlPopup({
    html: (
      <ConditionOverwriteConfirmation
        onConfirmChanges={onConfirm}
        onCancelChanges={onCancel}
      />
    ),
    title: '',
    showCloseButton: true,
    width: '807px',
  })
}

export const showConditionScriptOverwriteConfirmationOverlay = ({
  script,
  onConfirm,
  onCancel = () => {},
}) => {
  htmlPopup({
    html: (
      <ConditionScriptOverwriteConfirmation
        script={script}
        onConfirmChanges={onConfirm}
        onCancelChanges={onCancel}
      />
    ),
    title: '',
    showCloseButton: true,
    width: '807px',
  })
}
