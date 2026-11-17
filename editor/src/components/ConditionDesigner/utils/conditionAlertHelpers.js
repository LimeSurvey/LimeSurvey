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

export const showUnsavedChangesOverlay = (onIgnoreChanges, message = null) => {
  htmlPopup({
    html: (
      <UnsavedChangesOverlay
        onIgnoreChanges={() => {
          onIgnoreChanges()
        }}
        message={message}
      />
    ),
    title: '',
    showCloseButton: true,
    width: '807px',
  })
}

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

/**
 * Class that marks the condition popups opened for a question. The popups are
 * SweetAlert modals outside of the React tree, so they stay open when the
 * question's panel unmounts; the class lets the panel close only its own.
 * @param {number|string} qid Question ID.
 * @returns {string}
 */
export const getConditionPopupClass = (qid) => `condition-popup-qid-${qid}`

/**
 * Closes the visible popup if it was opened for the given question, including
 * a popup that replaced another one (e.g. a confirmation opened from the editor).
 * Popups opened for anything else are left open.
 * @param {number|string} qid Question ID.
 * @returns {void}
 */
export const closeConditionPopups = (qid) => {
  // getPopup() only returns an element while a popup is shown
  if (SwalAlert.getPopup()?.classList.contains(getConditionPopupClass(qid))) {
    SwalAlert.close()
  }
}

export const showResetAllConditionsOverlay = ({
  onConfirmDelete,
  popupClass = '',
}) => {
  htmlPopup({
    html: <ResetAllConditionsOverlay onConfirmDelete={onConfirmDelete} />,
    title: '',
    showCloseButton: true,
    width: '807px',
    popupClass,
  })
}

export const showConditionOverwriteConfirmationOverlay = ({
  onConfirm,
  onCancel = () => {},
  popupClass = '',
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
    popupClass,
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
